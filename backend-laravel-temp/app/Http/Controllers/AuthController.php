<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Perfil;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class AuthController extends Controller
{
    // Login
    public function login(Request $request)
    {
        $request->validate([
            'usuario' => 'required|email',
            'password' => 'required|string',
        ]);

        $usuario = Usuario::where('usuario', $request->usuario)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return response()->json(['error' => 'Credenciales incorrectas'], 401);
        }

        // Obtener los perfiles asociados al usuario
        $perfilCodigos = [];
        if (!empty($usuario->perfiles)) {
            // suponiendo que $usuario->perfiles está en JSON ["ADMIN","PROD"]
            $perfilCodigos = is_array($usuario->perfiles)
                ? $usuario->perfiles
                : json_decode($usuario->perfiles, true);
        }

        $perfiles = Perfil::whereIn('codigo_perfil', $perfilCodigos)
            ->get(['codigo_perfil', 'nombre', 'secciones']); // incluir secciones

        // Generar token simple (puedes usar JWT luego)
        $token = base64_encode($usuario->codigo_usuario . '|' . now());

        return response()->json([
            'usuario' => [
                'id' => $usuario->codigo_usuario,
                'nombre' => $usuario->nombre,
                'foto_perfil' => $usuario->foto_perfil,
                'email' => $usuario->usuario
            ],
            'perfiles' => $perfiles, // aquí van los perfiles con sus secciones
            'token' => $token
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        return response()->json(['message' => 'Sesión cerrada']);
    }
}
