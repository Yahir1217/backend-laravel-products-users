<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

class UsuarioController extends Controller
{
    // Listar todos los usuarios
    public function index()
    {
        return response()->json(Usuario::all());
    }

    // Obtener un usuario por ID
    public function show($id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }
        return response()->json($usuario);
    }

    // Crear usuario
    public function store(Request $request)
    {
        // Validaciones obligatorias solo para crear
        $request->validate([
            'usuario' => 'required|email|unique:usuarios,usuario',
            'nombre' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $data = $request->only(['usuario','nombre','password']);
        $data['password'] = bcrypt($data['password']);
        // telefono, foto_perfil y perfiles se agregan después opcionalmente en edit
        $usuario = Usuario::create($data);

        return response()->json($usuario, 201);
    }

// Actualizar usuario con logs para depuración
public function update(Request $request, $id)
{
    \Log::info('--- Inicio update usuario ---', ['id' => $id]);
    \Log::info('Archivos recibidos:', $request->allFiles());

    $usuario = Usuario::find($id);
    if (!$usuario) {
        \Log::error('Usuario no encontrado', ['id' => $id]);
        return response()->json(['error' => 'Usuario no encontrado'], 404);
    }

    // Campos opcionales
    if ($request->filled('usuario')) {
        $usuario->usuario = $request->usuario;
        \Log::info('Campo usuario recibido', ['usuario' => $request->usuario]);
    }
    if ($request->filled('nombre')) {
        $usuario->nombre = $request->nombre;
        \Log::info('Campo nombre recibido', ['nombre' => $request->nombre]);
    }
    if ($request->filled('telefono')) {
        $usuario->telefono = $request->telefono;
        \Log::info('Campo telefono recibido', ['telefono' => $request->telefono]);
    }

    // Password solo si viene
    if ($request->filled('password')) {
        $usuario->password = bcrypt($request->password);
        \Log::info('Campo password recibido y encriptado');
    }

    // Perfiles
    if ($request->filled('perfiles')) {
        $perfiles = $request->input('perfiles');
        if (is_string($perfiles)) {
            $usuario->perfiles = json_decode($perfiles, true);
        } else {
            $usuario->perfiles = $perfiles;
        }
        \Log::info('Campo perfiles recibido', ['perfiles' => $usuario->perfiles]);
    }

    // Foto de perfil
    if ($request->hasFile('foto_perfil')) {
        \Log::info('Archivo foto_perfil recibido', ['archivo' => $request->file('foto_perfil')->getClientOriginalName()]);

        $archivo = $request->file('foto_perfil');
        $carpeta = 'sistema_productos_usuarios/usuarios/' . $usuario->usuario;

        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud.cloud_name'),
                'api_key'    => config('cloudinary.cloud.api_key'),
                'api_secret' => config('cloudinary.cloud.api_secret'),
            ],
            'url' => ['secure' => true],
        ]);
        

        $uploadResult = $cloudinary->uploadApi()->upload($archivo->getRealPath(), [
            'folder' => $carpeta,
            'overwrite' => true,
            'resource_type' => 'image'
        ]);

        if (isset($uploadResult['secure_url'])) {
            $usuario->foto_perfil = $uploadResult['secure_url'];
            \Log::info('Imagen subida a Cloudinary', ['url' => $uploadResult['secure_url']]);
        } else {
            \Log::error('No se obtuvo URL segura de Cloudinary', ['uploadResult' => $uploadResult]);
        }
    } else {
        \Log::info('No se recibió archivo foto_perfil');
    }

    $usuario->save();
    \Log::info('Usuario actualizado correctamente', ['usuario' => $usuario]);

    return response()->json($usuario);
}


    // Eliminar usuario
    public function destroy($id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }
        $usuario->delete();
        return response()->json(['message' => 'Usuario eliminado']);
    }
}
