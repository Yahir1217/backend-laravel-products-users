<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Bitacora extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'bitacora';  // 👈 nombre real de la colección
    protected $table = 'bitacora';       // 👈 evita pluralización automática

    protected $fillable = [
        'coleccion',
        'accion',
        'antes',
        'despues',
        'usuario_responsable',
        'fecha',
    ];

    protected static function booted()
    {
        static::creating(function ($bitacora) {
            if (empty($bitacora->fecha)) {
                $bitacora->fecha = now();
            }
        });
    }
}
