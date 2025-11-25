<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Montacargas extends Model
{
    use HasFactory;

    protected $table = 'montacargas';

    protected $fillable = [
        'codigo',
        'modelo',
        'capacidad_carga',
        'capacidad_bateria_necesaria',
        'estado',
        'bateria_id', // ¡AGREGADO! Necesario para la asignación
    ];

    // --- RELACIONES ---

    /**
     * Relación: Un Montacargas tiene UNA batería asignada actualmente.
     * La clave foránea 'bateria_id' está en la tabla 'montacargas'.
     */
    public function bateria(): BelongsTo
    {
        return $this->belongsTo(Bateria::class, 'bateria_id');
    }
    
    /**
     * Un Montacargas tiene muchos Registros de Uso.
     */
    public function registrosUso(): HasMany
    {
        return $this->hasMany(RegistroUso::class, 'montacargas_id');
    }

    /**
     * Un Montacargas puede estar en muchos Planes de Rotación.
     */
    public function planRotacion(): HasMany
    {
        return $this->hasMany(PlanRotacion::class, 'montacargas_id');
    }
}