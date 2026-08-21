<?php

namespace App\Providers;

use App\Models\Empresa;
use Illuminate\Support\Facades\Config;
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
        $this->aplicarBrandingEmpresa();

         // El Administrador ve absolutamente todo en el sistema
        Gate::before(function ($user, $ability) {
            if ($user && $user->rol === 'admin') {
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

        // Esto define un permiso llamado 'acceso-general' que es válido si el usuario tiene cualquiera de los 3 roles
        Gate::define('acceso-general', function ($user) {
            return in_array($user->rol, ['admin', 'medico', 'asistente']);
        });

        Gate::define('acceso-medico-admin', function ($user) {
            return in_array($user->rol, ['medico', 'admin']);
        }); 
    }

    /**
     * Sobreescribe el logo y el nombre que muestra AdminLTE en el sidebar
     * (config/adminlte.php: 'logo' y 'logo_img') con los datos capturados
     * en el onboarding (tabla configuracion_empresa). Si todavía no existe
     * ningún registro (o la tabla no existe, p.ej. antes de correr el SQL
     * manual), se deja el valor por defecto del paquete tal cual.
     */
    private function aplicarBrandingEmpresa(): void
    {
        try {
            $empresa = Empresa::first();
        } catch (\Throwable $e) {
            // Evita tronar comandos de artisan (migrate, etc.) si la tabla
            // configuracion_empresa aún no existe en ese momento.
            return;
        }

        if (! $empresa) {
            return;
        }

        Config::set('adminlte.logo', e($empresa->nombre_empresa));

        if ($empresa->logo_url) {
            Config::set('adminlte.logo_img', $empresa->logo_url);
        }
    }
}