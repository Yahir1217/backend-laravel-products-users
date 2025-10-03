<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

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

        // Aquí puedes generar un token JWT si quieres
        $token = base64_encode($usuario->codigo_usuario . '|' . now());

        return response()->json([
            'usuario' => $usuario,
            'token' => $token
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        // Si usas JWT real, aquí invalidarías el token
        return response()->json(['message' => 'Sesión cerrada']);
    }
}
