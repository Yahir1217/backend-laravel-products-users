<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::prefix('usuarios')->group(function () {
    Route::get('/', [UsuarioController::class, 'index']);                          // Listar todos
    Route::get('/{codigo_usuario}', [UsuarioController::class, 'show']);          // Obtener uno
    Route::post('/', [UsuarioController::class, 'store']);                         // Crear
    Route::post('/{codigo_usuario}', [UsuarioController::class, 'update']);       // Actualizar
    Route::delete('/{codigo_usuario}', [UsuarioController::class, 'destroy']);    // Eliminar
    Route::post('/{codigo_usuario}/update-password', [UsuarioController::class, 'actualizarPassword']); // Actualizar contraseña

    Route::get('/{codigo_usuario}/enviar-codigo', [UsuarioController::class, 'enviarCodigoVerificacion']);
    Route::post('/{codigo_usuario}/verificar-codigo', [UsuarioController::class, 'verificarCodigoEmail']);
});

Route::prefix('productos')->group(function () {
    Route::get('/', [ProductoController::class, 'index']);
    Route::get('/{codigo_producto}', [ProductoController::class, 'show']);
    Route::post('/', [ProductoController::class, 'store']);
    Route::put('/{codigo_producto}', [ProductoController::class, 'update']);
    Route::delete('/{codigo_producto}', [ProductoController::class, 'destroy']);
    
});


Route::prefix('perfiles')->group(function () {
    Route::get('/', [PerfilController::class, 'index']);
    Route::get('/{codigo_perfil}', [PerfilController::class, 'show']);
    Route::post('/', [PerfilController::class, 'store']);
    Route::put('/{codigo_perfil}', [PerfilController::class, 'update']);
    Route::delete('/{codigo_perfil}', [PerfilController::class, 'destroy']);
});


Route::prefix('bitacora')->group(function () {
    Route::get('/', [BitacoraController::class, 'index']);
    Route::post('/', [BitacoraController::class, 'store']);
});





