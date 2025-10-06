<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Perfil;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'usuario' => 'required|email',
            'password' => 'required|string',
        ]);

        // Obtener usuario
        $usuario = Usuario::where('usuario', $request->usuario)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return response()->json(['error' => 'Credenciales incorrectas'], 401);
        }

        // IDs de perfiles del usuario
        $perfilIds = is_array($usuario->perfiles) ? $usuario->perfiles : [];

        // Todos los perfiles existentes
        $todosLosPerfiles = Perfil::all(['_id', 'codigo_perfil', 'nombre', 'secciones']);

        // Filtrar perfiles del usuario
        $perfiles = $todosLosPerfiles->filter(function ($perfil) use ($perfilIds) {
            return in_array((string)$perfil->_id, $perfilIds);
        })->values();

        // Token simple
        $token = base64_encode($usuario->codigo_usuario . '|' . now());

        return response()->json([
            'usuario' => [
                'id' => $usuario->codigo_usuario,
                'nombre' => $usuario->nombre,
                'foto_perfil' => $usuario->foto_perfil,
                'email' => $usuario->usuario,
                'email_verified_at' => $usuario->email_verified_at // <-- agregado
            ],
            'perfiles' => $perfiles,
            'token' => $token
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        return response()->json(['message' => 'Sesión cerrada']);
    }
}
