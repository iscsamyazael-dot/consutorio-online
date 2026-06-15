<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;


class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]); 
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }


    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed'
        ]);

        $user = Auth::user();
            // Verifica que la contraseña actual sea correcta
        if (!Hash::check(
            $request->current_password,
            $user->password
        )) {

            return response()->json([
                'message' => 'La contraseña actual es incorrecta.'
            ], 422);
        }

            // Actualiza la contraseña del usuario
        $user->password = Hash::make(
            $request->password
        );
            // Guarda la fecha del último cambio de contraseña
        $user->password_changed_at = now();

            // Guarda los cambios en la base de datos
        $user->save();

        return response()->json([
            'message' => 'Contraseña actualizada correctamente.'
        ]);
    }


    

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

        public function obtenerPerfil()
    {
        // Obtiene los datos del usuario autenticado
       return response()->json(Auth::user());
    }
}
