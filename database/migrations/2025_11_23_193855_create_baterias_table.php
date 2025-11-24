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
        Schema::create('baterias', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique(); // Ej: BAT-1001
            $table->integer('capacidad_total')->comment('en Ah');
            $table->integer('capacidad_actual')->comment('en Ah');
            // Estado por defecto 'disponible'
            $table->string('estado', 20)->default('disponible'); // disponible, cargando, en_uso, mantenimiento
            $table->date('ultima_maintenance')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('baterias');
    }
};