<?php

namespace App\Http\Controllers;

use App\Models\Perfil;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    // Listar todos los perfiles
    public function index()
    {
        return response()->json(Perfil::all());
    }

    // Obtener un perfil por ID
    public function show($id)
    {
        $perfil = Perfil::find($id); // ✅ Igual que UsuarioController
        if (!$perfil) {
            return response()->json(['error' => 'Perfil no encontrado'], 404);
        }
        return response()->json($perfil);
    }

    // Crear un perfil
    public function store(Request $request)
    {
        $data = $request->only(['nombre', 'secciones']);
        $perfil = Perfil::create($data);
        return response()->json($perfil, 201);
    }

    // Actualizar un perfil
    public function update(Request $request, $id)
    {
        $perfil = Perfil::find($id);
        if (!$perfil) {
            return response()->json(['error' => 'Perfil no encontrado'], 404);
        }

        $data = $request->only(['nombre', 'secciones']);
        $perfil->update($data);
        return response()->json($perfil);
    }

    // Eliminar un perfil
    public function destroy($id)
    {
        $perfil = Perfil::find($id);
        if (!$perfil) {
            return response()->json(['error' => 'Perfil no encontrado'], 404);
        }

        $perfil->delete();
        return response()->json(['message' => 'Perfil eliminado']);
    }
}
