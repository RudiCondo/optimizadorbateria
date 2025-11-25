<?php

namespace App\Http\Controllers;

use App\Models\SesionCarga;
use App\Models\Bateria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SesionCargaController extends Controller
{
    /**
     * Display a listing of the resource.
     * Lista todas las sesiones de carga.
     */
    public function index()
    {
        // Carga la relación con la batería para contexto
        $sesiones = SesionCarga::with('bateria')->orderBy('fecha_inicio', 'desc')->get();
        
        return response()->json([
            'status' => 'success',
            'sesiones_carga' => $sesiones
        ]);
    }

    /**
     * Display the specified resource.
     * Muestra una sesión de carga específica.
     */
    public function show(SesionCarga $sesionCarga)
    {
        $sesionCarga->load('bateria');
        
        return response()->json([
            'status' => 'success',
            'sesion_carga' => $sesionCarga
        ]);
    }

    /**
     * Inicia una nueva sesión de carga.
     * Se llama cuando se conecta la batería a un cargador.
     */
    public function iniciar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bateria_id' => 'required|exists:baterias,id',
            'capacidad_inicial' => 'required|numeric|min:0', // Capacidad de la batería al inicio de la carga
            'cargador_id' => 'nullable|string|max:50', // Identificador del cargador (si aplica)
            'observaciones' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $datos = $validator->validated();
        $bateria = Bateria::find($datos['bateria_id']);

        // 1. Verificar el estado de la batería
        // Solo puede iniciar carga si está 'Cargando', 'Disponible' (carga de mantenimiento) o 'Mantenimiento'
        if ($bateria->estado === 'En Uso') {
            return response()->json([
                'status' => 'error',
                'message' => 'La batería está actualmente en uso y no puede iniciar la carga.'
            ], 409); // 409 Conflict
        }
        
        // 2. Crear el registro de carga
        $sesion = SesionCarga::create([
            'bateria_id' => $datos['bateria_id'],
            'fecha_inicio' => Carbon::now(),
            'capacidad_inicial' => $datos['capacidad_inicial'],
            'cargador_id' => $datos['cargador_id'],
            'observaciones' => $datos['observaciones']
        ]);

        // 3. Actualizar el estado de la batería a 'Cargando'
        // Esto previene que se asigne a un montacargas mientras se carga
        $bateria->update([
            'estado' => 'Cargando',
            'capacidad_actual' => $datos['capacidad_inicial'] // Aseguramos que la capacidad actual refleje la inicial
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Sesión de carga iniciada correctamente.',
            'sesion' => $sesion
        ], 201);
    }

    /**
     * Finaliza una sesión de carga existente.
     * Se llama cuando la batería se desconecta o alcanza el 100%.
     */
    public function finalizar(Request $request, SesionCarga $sesionCarga)
    {
        // 1. Validar si ya está finalizada
        if ($sesionCarga->fecha_fin !== null) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Esta sesión de carga ya fue finalizada previamente.'
            ], 409); 
        }

        // 2. Validar los datos de finalización
        $bateria = Bateria::find($sesionCarga->bateria_id);
        
        $validator = Validator::make($request->all(), [
            // Capacidad final debe ser mayor que la inicial y no superar la capacidad total de la batería
            'capacidad_final' => 'required|numeric|gte:' . $sesionCarga->capacidad_inicial . '|lte:' . $bateria->capacidad_total,
            'observaciones_fin' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $datos = $validator->validated();
        
        // 3. Calcular métricas y actualizar el registro
        $capacidadGanada = $datos['capacidad_final'] - $sesionCarga->capacidad_inicial;
        $horasCarga = $sesionCarga->fecha_inicio->diffInHours(Carbon::now()); // Calcular duración

        $sesionCarga->update([
            'fecha_fin' => Carbon::now(),
            'capacidad_final' => $datos['capacidad_final'],
            'capacidad_ganada' => $capacidadGanada,
            'horas_carga' => $horasCarga > 0 ? $horasCarga : 1, // Aseguramos al menos 1 hora para evitar división por cero en reportes
            'observaciones_fin' => $datos['observaciones_fin']
        ]);
        
        // 4. Actualizar el estado de la Batería
        $bateria->update([
            'estado' => 'Disponible', // Vuelve a estar disponible para uso
            'capacidad_actual' => $datos['capacidad_final']
        ]);
        
        // 5. Devolver respuesta
        return response()->json([
            'status' => 'success',
            'message' => 'Sesión de carga finalizada. Batería lista para uso.',
            'sesion' => $sesionCarga
        ]);
    }
}