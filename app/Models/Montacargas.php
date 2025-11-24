<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Montacargas extends Model
{
    use HasFactory;

    protected $table = 'montacargas';

    protected $fillable = [
        'codigo',
        'modelo',
        'estado',
    ];

    // --- RELACIONES ---

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