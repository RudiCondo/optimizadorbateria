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
        Schema::create('registros_uso', function (Blueprint $table) {
            $table->id();
            // Claves foráneas con restricción de eliminación en cascada
            $table->foreignId('bateria_id')->constrained('baterias')->onDelete('cascade');
            $table->foreignId('montacargas_id')->constrained('montacargas')->onDelete('cascade');
            
            $table->timestamp('inicio_uso');
            $table->timestamp('fin_uso')->nullable();
            $table->integer('energia_consumida')->nullable()->comment('Ah consumidos');
            
            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros_uso');
    }
};