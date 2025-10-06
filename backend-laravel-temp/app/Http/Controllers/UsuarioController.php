<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Perfil;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Cloudinary\Cloudinary;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\CodigoVerificacionMail;
use Illuminate\Support\Facades\Log;

class UsuarioController extends Controller
{
    // Listar todos los usuarios con sus perfiles
    public function index()
    {
        $usuarios = Usuario::all();

        $usuariosConPerfiles = $usuarios->map(function ($usuario) {
            $perfilIds = is_array($usuario->perfiles) ? $usuario->perfiles : [];

            $perfiles = Perfil::whereIn('_id', $perfilIds)
                ->get(['_id', 'codigo_perfil', 'nombre', 'secciones']);

            return [
                'id' => $usuario->codigo_usuario,
                'nombre' => $usuario->nombre,
                'usuario' => $usuario->usuario,
                'telefono' => $usuario->telefono,
                'foto_perfil' => $usuario->foto_perfil,
                'perfiles' => $perfiles
            ];
        });

        return response()->json($usuariosConPerfiles);
    }

    // Obtener un usuario por codigo_usuario con sus perfiles
    public function show($codigo_usuario)
    {
        $usuario = Usuario::where('codigo_usuario', $codigo_usuario)->first();

        if (!$usuario) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $perfilIds = is_array($usuario->perfiles) ? $usuario->perfiles : [];

        $perfiles = Perfil::whereIn('_id', $perfilIds)
            ->get(['_id', 'codigo_perfil', 'nombre', 'secciones']);

        return response()->json([
            'id' => $usuario->codigo_usuario,
            'nombre' => $usuario->nombre,
            'usuario' => $usuario->usuario,
            'telefono' => $usuario->telefono,
            'foto_perfil' => $usuario->foto_perfil,
            'email_verified_at' => $usuario->email_verified_at,
            'perfiles' => $perfiles
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'usuario' => 'required|email|unique:usuarios,usuario',
            'nombre' => 'required|string',
        ]);
    
        $data = $request->only(['usuario', 'nombre', 'telefono']);
    
        // Perfiles opcionales
        if ($request->filled('perfiles')) {
            $perfiles = $request->input('perfiles');
            $data['perfiles'] = is_string($perfiles) ? json_decode($perfiles, true) : $perfiles;
        } else {
            $data['perfiles'] = [];
        }
    
        // Si viene password se usa, sino se asigna '123456'
        $data['password'] = bcrypt($request->input('password', '123456'));
    
        $usuario = Usuario::create($data);
    
        // Bitácora
        Bitacora::create([
            'coleccion' => 'usuarios',
            'accion' => 'insert',
            'antes' => null,
            'despues' => $usuario->toArray(),
            'usuario_responsable' => 'Desconocido'
        ]);
    
        return response()->json($usuario, 201);
    }
    

   // Actualizar usuario
   public function update(Request $request, $codigo_usuario)
   {
       Log::info("Llegó petición a update usuario CODIGO: {$codigo_usuario}", [
           'request' => $request->all(),
           'has_file' => $request->hasFile('foto_perfil')
       ]);

       $usuario = Usuario::where('codigo_usuario', $codigo_usuario)->first();
       if (!$usuario) return response()->json(['error' => 'Usuario no encontrado'], 404);

       $antes = $usuario->toArray();

       if ($request->filled('usuario')) $usuario->usuario = $request->usuario;
       if ($request->filled('nombre')) $usuario->nombre = $request->nombre;
       if ($request->filled('telefono')) $usuario->telefono = $request->telefono;
       if ($request->filled('password')) $usuario->password = bcrypt($request->password);
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

           if (isset($uploadResult['secure_url'])) $usuario->foto_perfil = $uploadResult['secure_url'];
       }

       $usuario->save();

       // Bitácora
       Bitacora::create([
           'coleccion' => 'usuarios',
           'accion' => 'update',
           'antes' => $antes,
           'despues' => $usuario->toArray(),
           'usuario_responsable' => 'Desconocido'
       ]);

       return response()->json($usuario);
   }

    // Eliminar usuario
    public function destroy($codigo_usuario)
    {
        $usuario = Usuario::where('codigo_usuario', $codigo_usuario)->first();
        if (!$usuario) return response()->json(['error' => 'Usuario no encontrado'], 404);

        $antes = $usuario->toArray();
        $usuario->delete();

        Bitacora::create([
            'coleccion' => 'usuarios',
            'accion' => 'delete',
            'antes' => $antes,
            'despues' => null,
            'usuario_responsable' => 'Desconocido'
        ]);

        return response()->json(['message' => 'Usuario eliminado']);
    }

    // Enviar código de verificación
    public function enviarCodigoVerificacion($codigo_usuario)
    {
        $usuario = Usuario::where('codigo_usuario', $codigo_usuario)->first();
        if (!$usuario) return response()->json(['error' => 'Usuario no encontrado'], 404);

        $codigo = Str::upper(Str::random(6));
        $usuario->remember_token = $codigo;
        $usuario->save();

        Mail::to($usuario->usuario)->send(new CodigoVerificacionMail($codigo, $usuario->nombre));

        return response()->json(['mensaje' => 'Correo enviado correctamente']);
    }

    // Verificar código de email
    public function verificarCodigoEmail(Request $request, $codigo_usuario)
    {
        Log::info('--- Inicio verificarCodigoEmail ---', [
            'codigo_usuario' => $codigo_usuario,
            'request' => $request->all()
        ]);

        try {
            $usuario = Usuario::where('codigo_usuario', $codigo_usuario)->first();
            if (!$usuario) return response()->json(['mensaje' => 'Usuario no encontrado'], 404);

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

            Log::info('Correo verificado correctamente', ['codigo_usuario' => $codigo_usuario]);

            return response()->json([
                'mensaje' => 'Correo verificado correctamente',
                'email_verified_at' => $usuario->email_verified_at
            ]);

        } catch (\Exception $e) {
            Log::error('Error en verificarCodigoEmail', ['error' => $e->getMessage()]);
            return response()->json(['mensaje' => 'Error interno', 'error' => $e->getMessage()], 500);
        }
    }


    // Actualizar contraseña
    public function actualizarPassword(Request $request, $codigo_usuario)
    {
        $request->validate(['password' => 'required|string|min:6']);

        $usuario = Usuario::where('codigo_usuario', $codigo_usuario)->first();
        if (!$usuario) return response()->json(['error' => 'Usuario no encontrado'], 404);

        $antes = $usuario->toArray();
        $usuario->password = bcrypt($request->password);
        $usuario->save();

        Bitacora::create([
            'coleccion' => 'usuarios',
            'accion' => 'update_password',
            'antes' => $antes,
            'despues' => $usuario->toArray(),
            'usuario_responsable' => 'Desconocido'
        ]);

        return response()->json(['mensaje' => 'Contraseña actualizada correctamente', 'usuario' => $usuario]);
    }
}
