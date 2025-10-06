<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class UsuarioPerfil extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'usuario_perfil';

    protected $fillable = [
        'usuario_id', // código_usuario del usuario
        'perfil_id',  // codigo_perfil del perfil
    ];

    public $timestamps = false;
}
