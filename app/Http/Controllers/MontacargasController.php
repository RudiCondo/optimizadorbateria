<?php

namespace App\Http\Controllers;

use App\Models\Montacargas; // Asumimos que el modelo Montacargas existe
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MontacargasController extends Controller
{
    /**
     * Display a listing of the resource.
     * Lista todos los montacargas con su batería asociada.
     */
    public function index()
    {
        // Usamos eager loading para cargar la relación 'bateria'
        $montacargas = Montacargas::with('bateria')->get(); 
        
        return response()->json([
            'status' => 'success',
            'montacargas' => $montacargas
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * Almacena un nuevo montacargas.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|max:50|unique:montacargas,codigo',
            'modelo' => 'required|string|max:100',
            'capacidad_carga' => 'required|numeric|min:0',
            'capacidad_bateria_necesaria' => 'required|numeric|min:1',
            'estado' => 'required|in:Operativo,Inactivo,Mantenimiento',
            // bateria_id es opcional al crear, pero si se envía debe existir en la tabla 'baterias'
            'bateria_id' => 'nullable|exists:baterias,id', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $montacargas = Montacargas::create($validator->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Montacargas creado correctamente.',
            'montacargas' => $montacargas
        ], 201);
    }

    /**
     * Display the specified resource.
     * Muestra un montacargas específico, incluyendo su batería.
     */
    public function show(Montacargas $montacarga)
    {
        // Carga la relación de batería antes de devolver
        $montacarga->load('bateria'); 

        return response()->json([
            'status' => 'success',
            'montacargas' => $montacarga
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Actualiza un montacargas específico.
     */
    public function update(Request $request, Montacargas $montacarga)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'sometimes|string|max:50|unique:montacargas,codigo,' . $montacarga->id,
            'modelo' => 'sometimes|string|max:100',
            'capacidad_carga' => 'sometimes|numeric|min:0',
            'capacidad_bateria_necesaria' => 'sometimes|numeric|min:1',
            'estado' => 'sometimes|in:Operativo,Inactivo,Mantenimiento',
            'bateria_id' => 'nullable|exists:baterias,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $montacarga->update($validator->validated());

        // Recarga para incluir la batería si fue actualizada o asignada
        $montacarga->load('bateria'); 

        return response()->json([
            'status' => 'success',
            'message' => 'Montacargas actualizado correctamente.',
            'montacargas' => $montacarga
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * Elimina un montacargas.
     */
    public function destroy(Montacargas $montacarga)
    {
        // Considera lógica adicional aquí si hay registros de uso o planes de rotación asociados
        // Por ahora, solo elimina.
        $montacarga->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Montacargas eliminado correctamente.'
        ], 200);
    }
}