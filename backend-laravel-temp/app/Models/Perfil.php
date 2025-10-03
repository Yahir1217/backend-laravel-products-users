<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Perfil extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'perfiles';  // 👈 fuerza la colección
    protected $table = 'perfiles';       // 👈 esto evita que Eloquent pluralice raro

    protected $fillable = [
        'codigo_perfil',
        'nombre',
        'fecha_creacion',
        'secciones',
    ];

    protected static function booted()
    {
        static::creating(function ($perfil) {
            if (empty($perfil->codigo_perfil)) {
                $perfil->codigo_perfil = 'PERF' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
            $perfil->fecha_creacion = now();
        });
    }
}

