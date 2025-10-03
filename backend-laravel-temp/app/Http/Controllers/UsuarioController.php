<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\CodigoVerificacionMail;
use Illuminate\Support\Facades\Log;

use MongoDB\BSON\ObjectId;

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
    $usuario = Usuario::find($id);
    if (!$usuario) {
        return response()->json(['error' => 'Usuario no encontrado'], 404);
    }

    // Campos opcionales
    if ($request->filled('usuario')) {
        $usuario->usuario = $request->usuario;
    }
    if ($request->filled('nombre')) {
        $usuario->nombre = $request->nombre;
    }
    if ($request->filled('telefono')) {
        $usuario->telefono = $request->telefono;
    }

    // Password solo si viene
    if ($request->filled('password')) {
        $usuario->password = bcrypt($request->password);
    }

    // Perfiles
    if ($request->filled('perfiles')) {
        $perfiles = $request->input('perfiles');
        $usuario->perfiles = is_string($perfiles) ? json_decode($perfiles, true) : $perfiles;
    }

    // Foto de perfil
    if ($request->hasFile('foto_perfil')) {
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
        }
    }

    $usuario->save();

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

        // Enviar código de verificación por correo
        public function enviarCodigoVerificacion($id)
        {
            $usuario = Usuario::find($id);
    
            if (!$usuario) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }
    
            // Generar un código de 6 caracteres alfanumérico
            $codigo = Str::upper(Str::random(6));
    
            // Guardar el código en remember_token
            $usuario->remember_token = $codigo;
            $usuario->save();
    
            // Enviar correo
            Mail::to($usuario->usuario)->send(new CodigoVerificacionMail($codigo, $usuario->nombre));
    
            return response()->json(['mensaje' => 'Correo enviado correctamente']);
        }
    
        public function verificarCodigoEmail(Request $request, $id)
        {
            Log::info('--- Inicio verificarCodigoEmail ---', ['id' => $id, 'request' => $request->all()]);
        
            try {
                $idMongo = new \MongoDB\BSON\ObjectId($id);
                $usuario = Usuario::where('_id', $idMongo)->first();
        
                if (!$usuario) {
                    Log::warning('Usuario no encontrado', ['id' => $id]);
                    return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
                }
        
                if ($usuario->remember_token !== $request->codigo) {
                    Log::warning('Código incorrecto', [
                        'codigo_enviado' => $request->codigo,
                        'codigo_guardado' => $usuario->remember_token
                    ]);
                    return response()->json(['mensaje' => 'Código incorrecto'], 403);
                }
        
                $usuario->email_verified_at = now();
                $usuario->remember_token = null;
                $usuario->save();
        
                Log::info('Correo verificado correctamente', ['usuario_id' => $usuario->_id]);
        
                return response()->json([
                    'mensaje' => 'Correo verificado correctamente',
                    'email_verified_at' => $usuario->email_verified_at
                ]);
        
            } catch (\Exception $e) {
                Log::error('Error en verificarCodigoEmail', ['error' => $e->getMessage()]);
                return response()->json(['mensaje' => 'Error interno', 'error' => $e->getMessage()], 500);
            }
        }
        
}
