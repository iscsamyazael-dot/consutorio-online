<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\EnsureOnboardingCompleted;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        
        then: function () {
            // Registramos las rutas del panel de super-admin,
            // separadas del resto de la app
            Route::middleware('web')->group(base_path('routes/superadmin.php'));
        },

    )
    ->withMiddleware(function (Middleware $middleware) {
        
        $middleware->validateCsrfTokens(except: [
            'api/ionic/*',
        ]);
        
        // Puedes asignarle un alias para usarlo en tus rutas
        $middleware->alias([
            'tenant' => IdentifyTenant::class,
            'onboarding.check' => EnsureOnboardingCompleted::class,
        ]);

        // Aplica el middleware a TODAS las rutas del grupo web
        $middleware->web(append: [
            IdentifyTenant::class,
        ]);

        // $middleware->alias([
        //     'rol' => \App\Http\Middleware\CheckRole::class, 
        // ]);

         // Define el orden de ejecución exacto
        $middleware->priority([
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\IdentifyTenant::class,
            \Illuminate\Auth\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();