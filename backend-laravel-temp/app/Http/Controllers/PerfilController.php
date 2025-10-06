<?php

namespace App\Http\Controllers;

use App\Models\Perfil;
use App\Models\Bitacora;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    // Listar todos los perfiles
    public function index()
    {
        return response()->json(Perfil::all());
    }

    // Obtener un perfil por codigo_perfil
    public function show($codigo_perfil)
    {
        $perfil = Perfil::where('codigo_perfil', $codigo_perfil)->first();
        if (!$perfil) {
            return response()->json(['error' => 'Perfil no encontrado'], 404);
        }
        return response()->json($perfil);
    }

    // Crear un perfil
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'secciones' => 'nullable|array'
        ]);

        $data = $request->only(['codigo_perfil', 'nombre', 'secciones']);
        $perfil = Perfil::create($data);

        // Registrar bitácora
        Bitacora::create([
            'coleccion' => 'perfiles',
            'accion' => 'insert',
            'antes' => null,
            'despues' => $perfil->toArray(),
            'usuario_responsable' => 'Desconocido'
        ]);

        return response()->json($perfil, 201);
    }

    // Actualizar un perfil
    public function update(Request $request, $codigo_perfil)
    {
        $perfil = Perfil::where('codigo_perfil', $codigo_perfil)->first();
        if (!$perfil) {
            return response()->json(['error' => 'Perfil no encontrado'], 404);
        }

        $antes = $perfil->toArray();
        $data = $request->only(['nombre', 'secciones']);
        $perfil->update($data);

        // Registrar bitácora
        Bitacora::create([
            'coleccion' => 'perfiles',
            'accion' => 'update',
            'antes' => $antes,
            'despues' => $perfil->toArray(),
            'usuario_responsable' => 'Desconocido'
        ]);

        return response()->json($perfil);
    }

    // Eliminar un perfil
    public function destroy($codigo_perfil)
    {
        $perfil = Perfil::where('codigo_perfil', $codigo_perfil)->first();
        if (!$perfil) {
            return response()->json(['error' => 'Perfil no encontrado'], 404);
        }

        $antes = $perfil->toArray();
        $perfil->delete();

        // Registrar bitácora
        Bitacora::create([
            'coleccion' => 'perfiles',
            'accion' => 'delete',
            'antes' => $antes,
            'despues' => null,
            'usuario_responsable' => 'Desconocido'
        ]);

        return response()->json(['message' => 'Perfil eliminado']);
    }
}
