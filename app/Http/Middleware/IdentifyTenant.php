<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
      $dbName = null;
        // 0. Dispositivo (kiosco / TV) autenticado por token Sanctum.
        //    Se resuelve ANTES que email/sesión porque el dispositivo
        //    nunca manda esos datos, y porque auth:sanctum (que corre
        //    después en el pipeline) necesita que 'mysql' ya apunte al
        //    tenant correcto para poder validar el token contra
        //    personal_access_tokens de esa base.
        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            $tokenId = strtok($bearerToken, '|'); // solo la parte antes del "|"

            if ($tokenId) {
                $mapeo = DB::connection('central')->table('resolucion_tokens_dispositivo')
                    ->where('token_id', $tokenId)
                    ->first();

                if ($mapeo) {
                    $dbName = $mapeo->tenant_db;
                }
            }
        }
        // 1. Si el usuario está enviando su correo en el login
        if (!$dbName && $request->filled('email')) {
            $parts = explode('@', $request->input('email'));
            if (count($parts) >= 2) {
                // Buscamos dinámicamente en la central según el dominio del correo
                $tenant = DB::connection('central')->table('tenants')
                    ->where('dominio_correo', $parts[1])
                    ->where('estatus', 'activo')
                    ->first();

                if ($tenant) {
                    $dbName = $tenant->db_name;
                    // Guardamos el tenant dinámico en la sesión
                    session(['tenant_db' => $dbName]);
                }
            }
        }

        // 2. Si ya hay una sesión activa de un tenant previo, lo recuperamos dinámicamente
        if (!$dbName && session()->has('tenant_db')) {
            $dbName = session('tenant_db');
        }

        // 3. Si tenemos un nombre de base de datos dinámico, configuramos la conexión 'tenant' al vuelo
        if ($dbName) {
            Config::set('database.connections.mysql.database', $dbName);
            DB::purge('mysql');
            DB::reconnect('mysql');

            
           
            // LOG TEMPORAL 2 - consulta real a MySQL, no metadata
            $real = DB::connection('mysql')->select('select database() as db');
            Log::debug('Conexion real tras reconnect (query)', [
                'db_real_query' => $real[0]->db ?? null,
            ]);
        }

             // LOG TEMPORAL
        Log::debug('IdentifyTenant debug', [
            'email' => $request->input('email'),
            'dbName' => $dbName,
            'session_tenant_db' => session('tenant_db'),
            'config_mysql_db' => config('database.connections.mysql.database'),
        ]);

        return $next($request);
    
    }

}
