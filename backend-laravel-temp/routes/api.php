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
    Route::get('/', [UsuarioController::class, 'index']);          // Listar
    Route::get('/{id}', [UsuarioController::class, 'show']);      // Obtener uno
    Route::post('/', [UsuarioController::class, 'store']);        // Crear
    Route::post('/{id}', [UsuarioController::class, 'update']);    // Actualizar
    Route::delete('/{id}', [UsuarioController::class, 'destroy']); // Eliminar
    Route::post('/{codigo_usuario}/update-password', [UsuarioController::class, 'actualizarPassword']);

});

Route::prefix('productos')->group(function () {
    Route::get('/', [ProductoController::class, 'index']);
    Route::get('/{id}', [ProductoController::class, 'show']);
    Route::post('/', [ProductoController::class, 'store']);
    Route::put('/{id}', [ProductoController::class, 'update']);
    Route::delete('/{id}', [ProductoController::class, 'destroy']);
});

Route::prefix('perfiles')->group(function () {
    Route::get('/', [PerfilController::class, 'index']);
    Route::get('/{id}', [PerfilController::class, 'show']);
    Route::post('/', [PerfilController::class, 'store']);
    Route::put('/{id}', [PerfilController::class, 'update']);
    Route::delete('/{id}', [PerfilController::class, 'destroy']);
});

Route::prefix('bitacora')->group(function () {
    Route::get('/', [BitacoraController::class, 'index']);
    Route::post('/', [BitacoraController::class, 'store']);
});

Route::get('/usuarios/{id}/enviar-codigo', [UsuarioController::class, 'enviarCodigoVerificacion']);
Route::post('/usuarios/{id}/verificar-codigo', [UsuarioController::class, 'verificarCodigoEmail']);
Route::get('/usuarios/codigo/{codigo_usuario}', [UsuarioController::class, 'buscarPorCodigo']);



