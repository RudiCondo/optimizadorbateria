<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject; // Importar la interfaz JWT

class Usuario extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    // Nombre de la tabla en la base de datos
    protected $table = 'usuarios';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'email',
        'password',
        'rol', // Agregamos el campo rol
    ];

    /**
     * Los atributos que deben ser ocultados para la serialización.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Define las relaciones de Eloquent.
     * (Por ahora no tenemos relaciones definidas aquí, pero se pueden agregar más adelante).
     */
     // Ejemplo de relación: Un usuario puede tener muchos planes de rotación asociados
    // public function planesRotacion(): HasMany
    // {
    //     return $this->hasMany(PlanRotacion::class, 'usuario_id');
    // }


    // --- Métodos requeridos por la interfaz JWTSubject ---

    /**
     * Obtiene el identificador que será almacenado en el 'subject' del JWT.
     * Es la clave primaria del modelo (el ID).
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Retorna un array con cualquier claim personalizado que se deba agregar al JWT.
     * Aquí agregamos el rol del usuario, que es muy útil para la autorización.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'rol' => $this->rol,
        ];
    }
}