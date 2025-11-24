<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('plan_rotacion', function (Blueprint $table) {
            $table->id();
            // Claves foráneas con restricción de eliminación en cascada
            $table->foreignId('bateria_id')->constrained('baterias')->onDelete('cascade');
            $table->foreignId('montacargas_id')->constrained('montacargas')->onDelete('cascade');
            
            $table->timestamp('inicio_asignacion');
            $table->timestamp('fin_estimado')->nullable();
            $table->string('motivo', 255)->nullable(); // Ej: "Nivel bajo", "Carga requerida"
            
            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_rotacion');
    }
};