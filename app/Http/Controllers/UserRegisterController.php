<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UserRegisterController extends Controller
{   

    

    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'rol'      => ['required', 'in:admin,medico,recepcion,farmacia'],
        ], [
            'name.required'     => 'El nombre completo es obligatorio.',
            'email.required'    => 'El correo electrónico es obligatorio.',
            'email.email'       => 'El formato del correo no es válido.',
            'email.unique'      => 'Este correo ya está registrado en el sistema.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed'=> 'Las contraseñas no coinciden.',
            'password.min'      => 'La contraseña debe tener mínimo 8 caracteres.',
            'rol.required'      => 'Debes seleccionar un nivel de acceso.',
            'rol.in'            => 'El rol seleccionado no es válido.',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'rol'      => $validated['rol'],
            'activo'   => 1,
            'onboarding_completado' => 1,        
        ]);

        return response()->json([
            'message' => "Usuario {$user->name} registrado exitosamente.",
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'rol'   => $user->rol,
            ],
        ], 201);
    }

}