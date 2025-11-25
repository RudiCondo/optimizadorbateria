<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BateriaController;
use App\Http\Controllers\MontacargasController;
use App\Http\Controllers\RegistroUsoController;
use App\Http\Controllers\SesionCargaController;
use App\Http\Controllers\PlanRotacionController;
use App\Http\Controllers\ReporteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Este archivo define todas las rutas de la API para el proyecto Backend Olimpo.
|
*/

// --- 1. RUTAS DE AUTENTICACIÓN (JWT) ---
// Rutas agrupadas bajo el prefijo 'auth'
Route::group([
    'middleware' => 'api', // Utiliza el guard 'api' de JWT
    'prefix' => 'auth'
], function ($router) {

    // Rutas públicas (no requieren token)
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Rutas protegidas (requieren token 'auth:api')
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
});


// --- 2. RUTAS DE LA APLICACIÓN (PROTEGIDAS por auth:api) ---
// Todas las rutas de negocio requieren que el usuario esté autenticado
Route::middleware('auth:api')->group(function () {
    
    // GESTIÓN DE BATERÍAS (CRUD y Filtros)
    // Se utiliza Route::resource para crear las 5 rutas CRUD estándar
    Route::resource('baterias', BateriaController::class)->except(['create', 'edit']);
    // Ruta especial para filtrar por estado
    Route::get('baterias/estado/{estado}', [BateriaController::class, 'filterByStatus']);

    
    // GESTIÓN DE MONTACARGAS (CRUD)
    Route::resource('montacargas', MontacargasController::class)->except(['create', 'edit']);


    // REGISTROS DE TELEMETRÍA (Uso y Carga)
    
    // Registros de Uso
    Route::prefix('registros-uso')->group(function () {
        Route::get('/', [RegistroUsoController::class, 'index']); // Listar todos
        Route::get('{id}', [RegistroUsoController::class, 'show']); // Ver uno específico
        Route::post('iniciar', [RegistroUsoController::class, 'iniciar']);
        Route::post('{id}/finalizar', [RegistroUsoController::class, 'finalizar']);
    });
    
    // Sesiones de Carga
    Route::prefix('sesiones-carga')->group(function () {
        Route::get('/', [SesionCargaController::class, 'index']); // Listar todos
        Route::get('{id}', [SesionCargaController::class, 'show']); // Ver uno específico
        Route::post('iniciar', [SesionCargaController::class, 'iniciar']);
        Route::post('{id}/finalizar', [SesionCargaController::class, 'finalizar']);
    });


    // PLAN DE ROTACIÓN Y OPTIMIZACIÓN (Core del Sistema)
    Route::prefix('rotacion')->group(function () {
        // Lógica para obtener la mejor batería y montacargas disponibles
        Route::get('sugerir-bateria/{montacargas_id}', [PlanRotacionController::class, 'sugerirBateria']);
        
        // Asignación manual o confirmación de la sugerencia
        Route::post('asignar', [PlanRotacionController::class, 'asignar']);
        
        // Gestión de planes activos
        Route::resource('planes', PlanRotacionController::class)->except(['create', 'edit', 'store']);
    });


    // REPORTES Y ANALÍTICAS
    Route::prefix('reportes')->group(function () {
        Route::get('estado-actual', [ReporteController::class, 'estadoActual']);
        Route::get('uso-baterias-historico', [ReporteController::class, 'usoHistorico']);
        Route::get('eficiencia-carga', [ReporteController::class, 'eficienciaCarga']);
    });

});