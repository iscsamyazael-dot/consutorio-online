<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // El Administrador ve absolutamente todo en el sistema
        Gate::before(function ($user, $ability) {
            if ($user->rol === 'admin') {
                return true;
            }
        });

        // Compuerta para los módulos compartidos (Asistente y Médico)
        Gate::define('rol-asistente-medico', function ($user) {
            return in_array($user->rol, ['asistente', 'medico']);
        });

        // Compuerta para los módulos exclusivos del Médico
        Gate::define('rol-medico', function ($user) {
            return $user->rol === 'medico';
        });
    }
}
