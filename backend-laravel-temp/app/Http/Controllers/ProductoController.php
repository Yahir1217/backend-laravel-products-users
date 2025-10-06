<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Bitacora;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    // Listar todos los productos
    public function index()
    {
        return response()->json(Producto::all());
    }

    // Obtener un producto por codigo_producto
    public function show($codigo_producto)
    {
        $producto = Producto::where('codigo_producto', $codigo_producto)->first();
        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }
        return response()->json($producto);
    }

    // Crear un producto
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'marca' => 'nullable|string',
            'precio' => 'nullable|numeric'
        ]); 

        $data = $request->only(['codigo_producto', 'nombre', 'marca', 'precio']);
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

    // Actualizar un producto
    public function update(Request $request, $codigo_producto)
    {
        $producto = Producto::where('codigo_producto', $codigo_producto)->first();
        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        $antes = $producto->toArray();

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

    // Eliminar un producto
    public function destroy($codigo_producto)
    {
        $producto = Producto::where('codigo_producto', $codigo_producto)->first();
        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        $antes = $producto->toArray();
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
