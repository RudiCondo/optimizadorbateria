<?php

namespace App\Http\Controllers;

use App\Models\Bateria;
use App\Models\Montacargas;
use App\Models\RegistroUso;
use App\Models\SesionCarga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReporteController extends Controller
{
    /**
     * Reporte consolidado del estado actual de todos los activos.
     */
    public function estadoActual()
    {
        // 1. Resumen de Baterías
        $baterias = Bateria::with('montacargasActual')->get();
        $bateriaSummary = $baterias->groupBy('estado')->map(function ($items, $key) {
            return $items->count();
        });

        // 2. Resumen de Montacargas
        $montacargas = Montacargas::with('bateria')->get();
        $montacargasSummary = $montacargas->groupBy('estado')->map(function ($items, $key) {
            return $items->count();
        });

        // 3. Montacargas sin batería
        $montacargasSinBateria = $montacargas->whereNull('bateria_id')->count();

        // 4. Detalle de la flota
        $detalleFlota = $baterias->map(function ($b) {
            $porcentajeCapacidad = ($b->capacidad_actual / $b->capacidad_total) * 100;
            return [
                'codigo_bateria' => $b->codigo,
                'estado' => $b->estado,
                'capacidad_actual_pct' => round($porcentajeCapacidad, 2),
                'asignado_a_montacargas' => $b->montacargasActual->codigo ?? 'N/A',
                'proxima_accion' => $b->estado === 'En Uso' ? 'Monitorear' : ($b->estado === 'Cargando' ? 'Esperar Carga' : 'Asignar'),
            ];
        });
        
        return response()->json([
            'status' => 'success',
            'fecha_reporte' => Carbon::now(),
            'resumen_baterias' => $bateriaSummary,
            'resumen_montacargas' => $montacargasSummary,
            'montacargas_sin_bateria' => $montacargasSinBateria,
            'detalle_flota' => $detalleFlota,
        ]);
    }

    /**
     * Reporte sobre la durabilidad y ciclos de vida de las baterías.
     */
    public function usoHistorico(Request $request)
    {
        // Agregación de datos de uso por batería
        $usoAgregado = RegistroUso::select('bateria_id')
            ->selectRaw('COUNT(*) as total_ciclos')
            ->selectRaw('SUM(horas_uso) as total_horas_uso')
            ->selectRaw('AVG(consumo_estimado) as consumo_promedio_por_ciclo')
            ->groupBy('bateria_id')
            ->with('bateria')
            ->get();

        $reporteDetalle = $usoAgregado->map(function ($item) {
            $bateria = $item->bateria;
            $consumoPromedioPct = ($item->consumo_promedio_por_ciclo / $bateria->capacidad_total) * 100;
            
            return [
                'codigo_bateria' => $bateria->codigo,
                'capacidad_total' => $bateria->capacidad_total,
                'capacidad_actual' => $bateria->capacidad_actual,
                'total_ciclos' => (int) $item->total_ciclos,
                'total_horas_uso' => round($item->total_horas_uso, 2),
                'consumo_promedio_por_ciclo' => round($item->consumo_promedio_por_ciclo, 2),
                'consumo_promedio_pct' => round($consumoPromedioPct, 2) . '%',
            ];
        });

        return response()->json([
            'status' => 'success',
            'reporte_titulo' => 'Análisis de Durabilidad y Ciclos de Uso',
            'datos' => $reporteDetalle,
        ]);
    }

    /**
     * Reporte que mide la eficiencia de los cargadores y las sesiones de carga.
     */
    public function eficienciaCarga(Request $request)
    {
        // Agregación de datos de carga por batería (o por cargador si tuviéramos tabla de cargadores)
        $cargaAgregada = SesionCarga::select('bateria_id')
            ->selectRaw('COUNT(*) as total_sesiones')
            ->selectRaw('SUM(capacidad_ganada) as capacidad_total_ganada')
            ->selectRaw('SUM(horas_carga) as total_horas_carga')
            ->selectRaw('AVG(capacidad_ganada) as capacidad_ganada_promedio')
            ->groupBy('bateria_id')
            ->with('bateria')
            ->get();

        $reporteDetalle = $cargaAgregada->map(function ($item) {
            $bateria = $item->bateria;
            $tasaCarga = ($item->capacidad_total_ganada > 0 && $item->total_horas_carga > 0)
                ? ($item->capacidad_total_ganada / $item->total_horas_carga)
                : 0;

            return [
                'codigo_bateria' => $bateria->codigo,
                'total_sesiones' => (int) $item->total_sesiones,
                'capacidad_total_ganada' => round($item->capacidad_total_ganada, 2),
                'total_horas_carga' => round($item->total_horas_carga, 2),
                'tasa_carga_promedio' => round($tasaCarga, 2) . ' unid/hr',
                'capacidad_ganada_promedio_por_sesion' => round($item->capacidad_ganada_promedio, 2),
            ];
        });

        return response()->json([
            'status' => 'success',
            'reporte_titulo' => 'Análisis de Eficiencia de Carga',
            'datos' => $reporteDetalle,
        ]);
    }
}