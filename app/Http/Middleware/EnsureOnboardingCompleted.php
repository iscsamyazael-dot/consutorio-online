<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    /**
     * Si el usuario autenticado es admin y todavia no completo el wizard de
     * bienvenida, lo redirige a /onboarding en vez de dejarlo pasar.
     * No afecta a otros roles (médicos, pacientes, etc.).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && $user->rol === 'admin'
            && ! $user->onboarding_completado
            && ! $request->routeIs('onboarding.*')
        ) {
            return redirect()->route('onboarding.index');
        }

        return $next($request);
    }
}