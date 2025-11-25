<?php

namespace App\Http\Controllers;

use App\Models\RegistroUso;
use App\Models\Montacargas;
use App\Models\Bateria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RegistroUsoController extends Controller
{
    /**
     * Display a listing of the resource.
     * Lista todos los registros de uso.
     */
    public function index()
    {
        // Carga las relaciones con montacargas y batería para contexto
        $registros = RegistroUso::with(['montacargas', 'bateria'])->orderBy('fecha_inicio', 'desc')->get();
        
        return response()->json([
            'status' => 'success',
            'registros_uso' => $registros
        ]);
    }

    /**
     * Display the specified resource.
     * Muestra un registro de uso específico.
     */
    public function show(RegistroUso $registroUso)
    {
        $registroUso->load(['montacargas', 'bateria']);
        
        return response()->json([
            'status' => 'success',
            'registro_uso' => $registroUso
        ]);
    }

    /**
     * Inicia un nuevo registro de uso.
     * Se llama cuando se instala una batería en un montacargas.
     */
    public function iniciar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'montacargas_id' => 'required|exists:montacargas,id',
            'bateria_id' => 'required|exists:baterias,id',
            'capacidad_inicial' => 'required|numeric|min:0', // Capacidad de la batería al inicio
            'observaciones' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $datos = $validator->validated();

        // 1. Verificar el estado de los activos
        $bateria = Bateria::find($datos['bateria_id']);
        $montacargas = Montacargas::find($datos['montacargas_id']);

        if ($bateria->estado !== 'Disponible') {
            return response()->json([
                'status' => 'error',
                'message' => 'La batería no está disponible para su uso. Estado actual: ' . $bateria->estado
            ], 409); // 409 Conflict
        }
        
        // 2. Crear el registro de uso
        $registro = RegistroUso::create([
            'montacargas_id' => $datos['montacargas_id'],
            'bateria_id' => $datos['bateria_id'],
            'fecha_inicio' => Carbon::now(),
            'capacidad_inicial' => $datos['capacidad_inicial'],
            // Los campos de finalización se dejan nulos
            'observaciones' => $datos['observaciones']
        ]);

        // 3. Actualizar el estado de la batería y la asignación en el montacargas
        $bateria->update(['estado' => 'En Uso']);
        $montacargas->update(['bateria_id' => $bateria->id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Uso de batería iniciado correctamente.',
            'registro' => $registro
        ], 201);
    }

    /**
     * Finaliza un registro de uso existente.
     * Se llama cuando se retira la batería del montacargas.
     */
    public function finalizar(Request $request, RegistroUso $registroUso)
    {
        // 1. Validar si ya está finalizado
        if ($registroUso->fecha_fin !== null) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Este registro de uso ya fue finalizado previamente.'
            ], 409); 
        }

        // 2. Validar los datos de finalización
        $validator = Validator::make($request->all(), [
            'capacidad_final' => 'required|numeric|min:0|lte:' . $registroUso->capacidad_inicial,
            'horas_uso' => 'required|numeric|min:0', // Horas reales registradas o calculadas
            'observaciones_fin' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $datos = $validator->validated();

        // Calcular el consumo y actualizar el registro
        $consumo = $registroUso->capacidad_inicial - $datos['capacidad_final'];

        $registroUso->update([
            'fecha_fin' => Carbon::now(),
            'capacidad_final' => $datos['capacidad_final'],
            'consumo_estimado' => $consumo,
            'horas_uso' => $datos['horas_uso'],
            'observaciones_fin' => $datos['observaciones_fin']
        ]);

        // 3. Actualizar el estado de la Batería y el Montacargas
        $bateria = Bateria::find($registroUso->bateria_id);
        $montacargas = Montacargas::find($registroUso->montacargas_id);

        // La batería pasa a 'Cargando' o 'Mantenimiento', dependiendo del estado de capacidad final
        $nuevoEstadoBateria = ($datos['capacidad_final'] < 20) ? 'Cargando' : 'Disponible';

        $bateria->update([
            'estado' => $nuevoEstadoBateria,
            'capacidad_actual' => $datos['capacidad_final']
        ]);
        
        // El montacargas queda sin batería
        $montacargas->update(['bateria_id' => null]);

        return response()->json([
            'status' => 'success',
            'message' => 'Uso de batería finalizado correctamente. Batería actualizada a ' . $nuevoEstadoBateria,
            'registro' => $registroUso
        ]);
    }
}