<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Usuario extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'usuarios';

    protected $fillable = [
        'codigo_usuario',
        'usuario',
        'nombre',
        'telefono',
        'foto_perfil',
        'password',
        'fecha_creacion',
        'perfiles', // array de IDs de perfiles
    ];

    protected static function booted()
    {
        static::creating(function ($usuario) {
            if (empty($usuario->codigo_usuario)) {
                $usuario->codigo_usuario = 'USR' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
            $usuario->fecha_creacion = now();
        });
    }

    protected $hidden = ['password'];

    // 🔹 Relación con perfiles (renombrada para no chocar con el campo)
    public function perfilesRelacion()
    {
        return $this->belongsToMany(Perfil::class, 'usuario_perfil', 'usuario_id', 'perfil_id');
    }
}
