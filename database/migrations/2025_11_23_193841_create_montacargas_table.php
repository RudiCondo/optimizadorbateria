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
        Schema::create('montacargas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique(); // Ej: MKG-01
            $table->string('modelo', 100)->nullable();
            // Estado por defecto 'activo'
            $table->string('estado', 50)->default('activo'); // activo, mantenimiento, inactivo
            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('montacargas');
    }
};