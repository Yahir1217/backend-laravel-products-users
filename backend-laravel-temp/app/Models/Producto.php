<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Producto extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'productos';

    protected $fillable = [
        'codigo_producto',
        'nombre',
        'marca',
        'precio',
        'fecha_creacion',
    ];

    protected static function booted()
    {
        static::creating(function ($producto) {
            if (empty($producto->codigo_producto)) {
                $producto->codigo_producto = 'PROD' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
            $producto->fecha_creacion = now();
        });
    }
}
