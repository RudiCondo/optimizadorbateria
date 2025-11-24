<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bateria extends Model
{
    use HasFactory;

    protected $table = 'baterias';

    protected $fillable = [
        'codigo',
        'capacidad_total',
        'capacidad_actual',
        'estado',
        'ultima_maintenance',
    ];

    /**
     * Los atributos que deberían ser casteados a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'ultima_maintenance' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // --- RELACIONES ---

    /**
     * Una Batería tiene muchos Registros de Uso.
     */
    public function registrosUso(): HasMany
    {
        return $this->hasMany(RegistroUso::class, 'bateria_id');
    }

    /**
     * Una Batería tiene muchas Sesiones de Carga.
     */
    public function sesionesCarga(): HasMany
    {
        return $this->hasMany(SesionCarga::class, 'bateria_id');
    }

    /**
     * Una Batería puede estar en muchos Planes de Rotación.
     */
    public function planRotacion(): HasMany
    {
        return $this->hasMany(PlanRotacion::class, 'bateria_id');
    }
}