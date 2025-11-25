<?php

namespace App\Http\Controllers;

use App\Models\Bateria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BateriaController extends Controller
{
    /**
     * Display a listing of the resource.
     * Lista todas las baterías.
     */
    public function index()
    {
        $baterias = Bateria::all();
        return response()->json([
            'status' => 'success',
            'baterias' => $baterias
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * Almacena una nueva batería.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|max:50|unique:baterias,codigo',
            'capacidad_total' => 'required|numeric|min:0',
            'capacidad_actual' => 'required|numeric|lte:capacidad_total',
            'estado' => 'required|in:Disponible,En Uso,Cargando,Mantenimiento',
            'ultima_maintenance' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $bateria = Bateria::create($validator->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Batería creada correctamente.',
            'bateria' => $bateria
        ], 201);
    }

    /**
     * Display the specified resource.
     * Muestra una batería específica.
     */
    public function show(Bateria $bateria)
    {
        return response()->json([
            'status' => 'success',
            'bateria' => $bateria
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Actualiza una batería específica.
     */
    public function update(Request $request, Bateria $bateria)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'sometimes|string|max:50|unique:baterias,codigo,' . $bateria->id,
            'capacidad_total' => 'sometimes|numeric|min:0',
            'capacidad_actual' => 'sometimes|numeric|lte:capacidad_total',
            'estado' => 'sometimes|in:Disponible,En Uso,Cargando,Mantenimiento',
            'ultima_maintenance' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $bateria->update($validator->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Batería actualizada correctamente.',
            'bateria' => $bateria
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * Elimina una batería.
     */
    public function destroy(Bateria $bateria)
    {
        $bateria->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Batería eliminada correctamente.'
        ], 200);
    }

    // --- MÉTODOS ESPECIALES ---

    /**
     * Filtra baterías por estado.
     */
    public function filterByStatus($estado)
    {
        $baterias = Bateria::where('estado', $estado)->get();

        return response()->json([
            'status' => 'success',
            'estado_buscado' => $estado,
            'baterias' => $baterias
        ]);
    }
}