<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanRotacion extends Model
{
    use HasFactory;

    protected $table = 'plan_rotacion';

    protected $fillable = [
        'bateria_id',
        'montacargas_id',
        'inicio_asignacion',
        'fin_estimado',
        'motivo',
    ];

    /**
     * Los atributos que deberían ser casteados a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'inicio_asignacion' => 'datetime',
        'fin_estimado' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // --- RELACIONES ---

    /**
     * Un Plan de Rotación pertenece a una Batería.
     */
    public function bateria(): BelongsTo
    {
        return $this->belongsTo(Bateria::class, 'bateria_id');
    }

    /**
     * Un Plan de Rotación pertenece a un Montacargas.
     */
    public function montacargas(): BelongsTo
    {
        return $this->belongsTo(Montacargas::class, 'montacargas_id');
    }
}