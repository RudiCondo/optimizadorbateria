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
        Schema::table('montacargas', function (Blueprint $table) {
            // Clave foránea que referencia a la tabla 'baterias'
            // Es nullable porque un montacargas puede no tener batería asignada.
            $table->foreignId('bateria_id')
                  ->nullable()
                  ->constrained('baterias') // Asume que tu tabla de baterías se llama 'baterias'
                  ->onDelete('set null'); // Si la batería se elimina, el campo se pone a NULL
            
            // Si también olvidaste estos campos en la migración original, agrégalos aquí:
            $table->unsignedDecimal('capacidad_carga', 8, 2)->after('modelo')->default(0);
            $table->unsignedDecimal('capacidad_bateria_necesaria', 8, 2)->after('capacidad_carga')->default(1);
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::table('montacargas', function (Blueprint $table) {
            // Asegúrate de eliminar la clave foránea antes de eliminar la columna
            $table->dropForeign(['bateria_id']); 
            $table->dropColumn('bateria_id');
            $table->dropColumn('capacidad_carga');
            $table->dropColumn('capacidad_bateria_necesaria');
        });
    }
};