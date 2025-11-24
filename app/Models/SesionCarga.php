<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SesionCarga extends Model
{
    use HasFactory;

    protected $table = 'sesiones_carga';

    protected $fillable = [
        'bateria_id',
        'inicio_carga',
        'fin_carga',
        'energia_agregada',
        'tipo_cargador',
    ];

    /**
     * Los atributos que deberían ser casteados a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'inicio_carga' => 'datetime',
        'fin_carga' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // --- RELACIONES ---

    /**
     * Una Sesión de Carga pertenece a una Batería.
     */
    public function bateria(): BelongsTo
    {
        return $this->belongsTo(Bateria::class, 'bateria_id');
    }
}