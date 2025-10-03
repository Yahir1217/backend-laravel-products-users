<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Bitacora;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index()
    {
        return response()->json(Producto::all());
    }

    public function show($id)
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }
        return response()->json($producto);
    }

    public function store(Request $request)
    {
        $data = $request->only(['nombre', 'marca', 'precio']);
        $producto = Producto::create($data);

        // Registrar bitácora
        Bitacora::create([
            'coleccion' => 'productos',
            'accion' => 'insert',
            'antes' => null,
            'despues' => $producto->toArray(),
            'usuario_responsable' => 'Desconocido'
        ]);

        return response()->json($producto, 201);
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        $antes = $producto->toArray(); // Guardar estado antes
        $data = $request->only(['nombre', 'marca', 'precio']);
        $producto->update($data);

        // Registrar bitácora
        Bitacora::create([
            'coleccion' => 'productos',
            'accion' => 'update',
            'antes' => $antes,
            'despues' => $producto->toArray(),
            'usuario_responsable' => 'Desconocido'
        ]);

        return response()->json($producto);
    }

    public function destroy($id)
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        $antes = $producto->toArray(); // Guardar estado antes
        $producto->delete();

        // Registrar bitácora
        Bitacora::create([
            'coleccion' => 'productos',
            'accion' => 'delete',
            'antes' => $antes,
            'despues' => null,
            'usuario_responsable' => 'Desconocido'
        ]);

        return response()->json(['message' => 'Producto eliminado']);
    }
}
