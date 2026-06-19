<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Si el usuario ni siquiera ha iniciado sesión, mandarlo al login
        if (!auth()->check()) {
            return redirect('/login');
        }

        // ⭐ NUEVO: Si es Administrador, ¡déjalo pasar a donde quiera!
        if (auth()->user()->rol === 'admin') {
            return $next($request);
        }

        // 2. Si el rol del usuario NO coincide con el rol permitido, bloquearlo
        if (auth()->user()->rol !== $role) {
            abort(403, 'No tienes autorización para acceder a esta sección.');
        }

        return $next($request);
    }
}