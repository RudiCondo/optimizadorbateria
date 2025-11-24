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
        Schema::create('sesiones_carga', function (Blueprint $table) {
            $table->id();
            // Clave foránea con restricción de eliminación en cascada
            $table->foreignId('bateria_id')->constrained('baterias')->onDelete('cascade');
            
            $table->timestamp('inicio_carga');
            $table->timestamp('fin_carga')->nullable();
            $table->integer('energia_agregada')->nullable()->comment('Ah cargados');
            // Tipo de cargador por defecto 'estandar'
            $table->string('tipo_cargador', 50)->default('estandar'); // estandar / rapido
            
            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesiones_carga');
    }
};