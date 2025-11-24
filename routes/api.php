<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController; // Importar el controlador de autenticación

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí es donde puedes registrar rutas API para tu aplicación. Estas
| rutas son cargadas por el RouteServiceProvider dentro de un grupo
| que se le asigna el middleware "api" (definido en config/auth.php para JWT).
|
*/

// --- RUTAS DE AUTENTICACIÓN (JWT) ---
// Agrupamos todas las rutas de autenticación bajo el prefijo 'auth'
Route::group([
    'middleware' => 'api',
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


// --- RUTAS DE LA APLICACIÓN OPTIMIZADOR DE CARGA ---
// Todas las rutas de negocio (CRUD, lógica de optimización) deben estar protegidas.
Route::middleware('auth:api')->group(function () {
    
    // Aquí irán las rutas para Montacargas, Baterías, Registros de Uso, etc.
    
    // EJEMPLO:
    // Route::resource('baterias', BateriaController::class); 
    // Route::post('rotacion/optimizar', [RotacionController::class, 'optimizar']);

});