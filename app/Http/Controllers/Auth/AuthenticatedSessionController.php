<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Intenta autenticar al usuario (Valida credenciales, email y password)
        $request->authenticate();

        // 2. Regenera la sesión por seguridad
        $request->session()->regenerate();

        // 3. Obtenemos el usuario que acaba de logearse
        $user = $request->user();

        // 4. Redirección según el rol guardado en la base de datos
        switch ($user->rol) { 
            case 'admin':
                return redirect()->to('/admin');
                
            case 'medico':
                return redirect()->to('/medico');
                
            case 'asistente':
                return redirect()->to('/asistente');

            default:
                // Ruta por defecto por si acaso (ej. un usuario común o el dashboard estándar)
                return redirect()->intended(route('dashboard', absolute: false));
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
