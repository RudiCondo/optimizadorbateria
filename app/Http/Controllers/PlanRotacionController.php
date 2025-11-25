<?php

namespace App\Http\Controllers;

use App\Models\Montacargas;
use App\Models\Bateria;
use App\Models\PlanRotacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PlanRotacionController extends Controller
{
    /**
     * Lógica de Optimización: Sugiere la mejor batería disponible 
     * para un montacargas específico, buscando la que maximice la eficiencia.
     */
    public function sugerirBateria(Montacargas $montacargas)
    {
        // 1. Obtener todas las baterías disponibles para la rotación
        // Disponibles = Estado 'Disponible' y capacidad actual >= a la capacidad requerida del montacargas
        
        $bateriasCandidatas = Bateria::where('estado', 'Disponible')
            ->where('capacidad_actual', '>=', $montacargas->capacidad_bateria_necesaria * 0.8) // Filtro base: al menos 80% de lo requerido
            ->get();

        if ($bateriasCandidatas->isEmpty()) {
            return response()->json([
                'status' => 'info',
                'message' => 'No se encontraron baterías disponibles que cumplan los requisitos de capacidad para este montacargas.',
            ], 404);
        }

        // 2. Aplicar lógica de optimización: 
        // Criterios de desempate:
        // A. Mayor capacidad actual (prioridad máxima)
        // B. Menos usos recientes (promover rotación equitativa, asumiendo que Bateria tiene un campo 'ciclos_recientes' o similar)

        $mejorBateria = $bateriasCandidatas->sortByDesc(function ($bateria) {
            // Factor 1: Capacidad actual (el más importante)
            $capacidadFactor = $bateria->capacidad_actual;
            
            // Factor 2: Antigüedad de la última asignación (favorecer las que llevan más tiempo "disponibles")
            // Si la batería tiene el campo 'updated_at' reflejando cuándo quedó disponible, se usa.
            // Para simplificar, priorizaremos la capacidad y luego el ID como desempate simple.
            return $capacidadFactor; 
        })->first();

        // 3. Crear un plan de rotación temporal (Sugerencia)
        $sugerencia = [
            'montacargas_id' => $montacargas->id,
            'bateria_sugerida_id' => $mejorBateria->id,
            'codigo_bateria' => $mejorBateria->codigo,
            'capacidad_sugerida' => $mejorBateria->capacidad_actual,
            'motivo_sugerencia' => 'Mejor batería disponible con la mayor capacidad actual (' . $mejorBateria->capacidad_actual . ')',
            'fecha_sugerencia' => Carbon::now()->toDateTimeString()
        ];
        
        return response()->json([
            'status' => 'success',
            'message' => 'Sugerencia de rotación generada con éxito.',
            'sugerencia' => $sugerencia,
            'montacargas_requiere' => $montacargas->capacidad_bateria_necesaria,
        ]);
    }

    /**
     * Asigna formalmente la batería a un montacargas y actualiza los estados.
     * Esto debería ser llamado después de que el operador aprueba la sugerencia o elige manualmente.
     */
    public function asignar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'montacargas_id' => 'required|exists:montacargas,id',
            'bateria_id' => 'required|exists:baterias,id',
            'motivo_asignacion' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $datos = $validator->validated();
        $montacargas = Montacargas::find($datos['montacargas_id']);
        $bateria = Bateria::find($datos['bateria_id']);
        
        // 1. Validaciones finales
        if ($bateria->estado !== 'Disponible') {
            return response()->json([
                'status' => 'error',
                'message' => 'La batería seleccionada no está en estado "Disponible". Estado actual: ' . $bateria->estado
            ], 409);
        }

        // 2. Registrar el Plan de Rotación (Histórico)
        $plan = PlanRotacion::create([
            'montacargas_id' => $datos['montacargas_id'],
            'bateria_id' => $datos['bateria_id'],
            'fecha_asignacion' => Carbon::now(),
            'capacidad_al_asignar' => $bateria->capacidad_actual,
            'motivo' => $datos['motivo_asignacion'] ?? 'Asignación por sugerencia del sistema.',
            'estado_plan' => 'Activo' // Marcamos como activo hasta que se retire
        ]);
        
        // 3. Actualizar estados de los activos
        // Actualizar el Montacargas (asignarle la nueva batería)
        $montacargas->update(['bateria_id' => $bateria->id]);
        
        // Actualizar la Batería (cambiar estado a En Uso)
        $bateria->update(['estado' => 'En Uso']);

        return response()->json([
            'status' => 'success',
            'message' => 'Rotación completada. La batería ' . $bateria->codigo . ' ha sido asignada al montacargas ' . $montacargas->codigo . '.',
            'plan_rotacion' => $plan->load('montacargas', 'bateria')
        ], 201);
    }
    
    // Rutas de recurso para listar y ver planes de rotación
    
    public function index()
    {
        $planes = PlanRotacion::with(['montacargas', 'bateria'])
            ->orderBy('fecha_asignacion', 'desc')
            ->get();
            
        return response()->json(['status' => 'success', 'planes' => $planes]);
    }
    
    public function show(PlanRotacion $plan)
    {
        $plan->load(['montacargas', 'bateria']);
        return response()->json(['status' => 'success', 'plan' => $plan]);
    }

    /**
     * Método para finalizar o cancelar un plan de rotación (por si el RegistroUso falla).
     */
    public function destroy(PlanRotacion $plan)
    {
        // Esto solo debería usarse si el flujo de RegistroUso/finalizar falla,
        // ya que el estado se actualiza en RegistroUsoController::finalizar
        if ($plan->estado_plan === 'Finalizado') {
            return response()->json([
                'status' => 'warning',
                'message' => 'El plan ya estaba marcado como finalizado.'
            ], 409);
        }

        $plan->update([
            'estado_plan' => 'Cancelado',
            'fecha_fin' => Carbon::now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Plan de rotación cancelado exitosamente.'
        ]);
    }
}