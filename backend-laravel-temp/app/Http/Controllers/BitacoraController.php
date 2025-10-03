<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use Illuminate\Http\Request;
use MongoDB\BSON\ObjectId;

class BitacoraController extends Controller
{
    // Listar todos los registros de bitácora
    public function index()
    {
        return response()->json(Bitacora::all());
    }

    // Obtener un registro por ID (opcional)
    public function show($id)
    {
        $registro = Bitacora::where('_id', new ObjectId($id))->first();

        if (!$registro) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }
        return response()->json($registro);
    }

    // Crear un registro de bitácora
    public function store(Request $request)
    {
        $data = $request->only(['coleccion', 'accion', 'antes', 'despues', 'usuario_responsable']);
        $bitacora = Bitacora::create($data);
        return response()->json($bitacora, 201);
    }
}
