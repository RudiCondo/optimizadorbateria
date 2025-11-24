<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroUso extends Model
{
    use HasFactory;

    protected $table = 'registros_uso';

    protected $fillable = [
        'bateria_id',
        'montacargas_id',
        'inicio_uso',
        'fin_uso',
        'energia_consumida',
    ];

    /**
     * Los atributos que deberían ser casteados a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'inicio_uso' => 'datetime',
        'fin_uso' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // --- RELACIONES ---

    /**
     * Un Registro de Uso pertenece a una Batería.
     */
    public function bateria(): BelongsTo
    {
        return $this->belongsTo(Bateria::class, 'bateria_id');
    }

    /**
     * Un Registro de Uso pertenece a un Montacargas.
     */
    public function montacargas(): BelongsTo
    {
        return $this->belongsTo(Montacargas::class, 'montacargas_id');
    }
}