<?php

namespace App\Providers;

use App\Models\Empresa;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
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

         // El Administrador ve absolutamente todo en el sistema...
        // EXCEPTO los gates de módulo ('modulo-*'): esos dependen de si el
        // tenant tiene el módulo activo (tenant_modulos) y de si a ESTE
        // usuario se le asignó (usuario_modulos), no del rol. Si un admin
        // de un tenant que no pagó Medicina del Trabajo pudiera verla solo
        // por ser admin, el control de módulos por tenant no serviría de nada.
        Gate::before(function ($user, $ability) {
            if ($user && $user->rol === 'admin' && !str_starts_with($ability, 'modulo-')) {
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

        // Compuerta para el módulo de Medicina del Trabajo.
        // Tres condiciones, las 3 deben cumplirse:
        //   1) El usuario tiene un rol clínico (mismo criterio que el resto
        //      de módulos médicos; ajustar si Medicina del Trabajo debe
        //      permitir también a 'asistente', por ejemplo).
        //   2) El TENANT tiene el módulo activo (tenant_modulos en la
        //      central, cacheado en sesión por IdentifyTenant).
        //   3) Este USUARIO en particular tiene el módulo asignado
        //      (usuario_modulos, en la base del propio tenant).
        Gate::define('modulo-medicina-trabajo', function ($user) {
            if (!in_array($user->rol, ['medico', 'admin'])) {
                return false;
            }

            if (!in_array('medicina_trabajo', session('tenant_modulos', []))) {
                return false;
            }

            return DB::table('usuario_modulos')
                ->where('user_id', $user->id)
                ->where('modulo_codigo', 'medicina_trabajo')
                ->where('activo', 1)
                ->exists();
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