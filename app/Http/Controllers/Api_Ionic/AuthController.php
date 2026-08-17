<?php

namespace App\Http\Controllers\Api_Ionic;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller 
{
     /**
     * Iniciar sesión
     */
    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $usuario = User::where('email', $credenciales['email'])->first();

        if (!$usuario || !Hash::check($credenciales['password'], $usuario->password)) {
            return response()->json([
                'message' => 'Usuario o Contraseña no válidos'
            ], 401);
        }

        if (!$usuario->activo) {
            return response()->json([
                'message' => 'El usuario se encuentra inactivo.'
            ], 403);
        }

        $token = $usuario->createToken('ionic-app')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso.',
            'token' => $token,
            'usuario' => [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
                'rol' => $usuario->rol,
            ],
        ], 200);
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.'
        ]);
    }

    /**
     * Obtener usuario autenticado
     */
    public function user(Request $request)
    {
        return response()->json([
            'usuario' => $request->user()
        ]);
    }
    
    //Función para actualizar la contraseña//
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        $usuario = $request->user();

        if (!Hash::check($request->current_password, $usuario->password)) {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta.'
            ], 422);
        }

        $usuario->password = Hash::make($request->new_password);
        $usuario->save();

        return response()->json([
            'message' => 'Contraseña actualizada correctamente.'
        ], 200);
    }
}
