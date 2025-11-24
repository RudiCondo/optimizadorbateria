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
        // Renombramos la tabla por defecto 'users' a 'usuarios'
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('email', 150)->unique();
            $table->string('password');
            // Rol con valor por defecto 'operador'
            $table->string('rol', 50)->default('operador'); // admin / operador / tecnico
            $table->rememberToken(); // Útil para sesiones web o tokens de sesión
            $table->timestamps(); // Crea las columnas created_at y updated_at
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};