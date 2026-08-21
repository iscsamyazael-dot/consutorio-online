<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CodigoEmparejamientoDispositivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DispositivoController extends Controller
{
    /**
     * POST /dispositivos/generar-codigo
     * Protegida por auth + can:acceso-general. La sesión del admin
     * ya trae el tenant resuelto (config('database.connections.mysql.database')
     * ya apunta a su base), así que el registro completo se guarda
     * normalmente ahí. Además, se guarda un mapeo mínimo en central
     * para que emparejar() (sin sesión) pueda encontrar el tenant.
     */
    public function generarCodigoEmparejamiento(Request $request)
    {
        $validated = $request->validate([
            'nombre_dispositivo' => 'required|string|max:100',
            'tipo'                => 'required|in:kiosco,tv',
        ]);

        // Evita colisiones con un código ya activo, tanto en el tenant
        // actual como en central (donde conviven códigos de TODOS los tenants)
        do {
            $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (
            DB::connection('central')->table('resolucion_codigos_dispositivo')
                ->where('codigo', $codigo)
                ->where('expira_en', '>', now())
                ->exists()
        );

        $expiraEn = now()->addMinutes(10);

        // 1. Registro completo en la base del TENANT actual (conexión 'mysql',
        //    ya resuelta por la sesión del admin logueado)
        $registro = CodigoEmparejamientoDispositivo::create([
            'codigo'              => $codigo,
            'nombre_dispositivo'  => $validated['nombre_dispositivo'],
            'tipo'                => $validated['tipo'],
            'expira_en'           => $expiraEn,
        ]);

        // 2. Mapeo mínimo en CENTRAL, para que emparejar() sepa a qué
        //    tenant pertenece este código sin tener sesión
        DB::connection('central')->table('resolucion_codigos_dispositivo')->insert([
            'codigo'    => $codigo,
            'tenant_db' => config('database.connections.mysql.database'),
            'expira_en' => $expiraEn,
        ]);

        return response()->json([
            'codigo'    => $registro->codigo,
            'expira_en' => $registro->expira_en,
        ]);
    }

    /**
     * POST /dispositivos/emparejar
     * Pública (sin auth) — la llama la tablet/TV una sola vez.
     * No hay tenant resuelto todavía: primero se busca en central.
     */
    public function emparejar(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|size:6',
        ]);

        // 1. Buscar en CENTRAL a qué tenant pertenece este código
        $mapeo = DB::connection('central')->table('resolucion_codigos_dispositivo')
            ->where('codigo', $validated['codigo'])
            ->where('expira_en', '>', now())
            ->first();

        if (!$mapeo) {
            return response()->json(['success' => false, 'motivo' => 'codigo_invalido_o_expirado'], 404);
        }

        // 2. Cambiar la conexión 'mysql' al tenant resuelto
        Config::set('database.connections.mysql.database', $mapeo->tenant_db);
        DB::purge('mysql');
        DB::reconnect('mysql');

        // 3. Ya en la base del tenant, buscar el registro completo y validar que no esté usado
        $registro = CodigoEmparejamientoDispositivo::where('codigo', $validated['codigo'])
            ->whereNull('usado_en')
            ->where('expira_en', '>', now())
            ->first();

        if (!$registro) {
            return response()->json(['success' => false, 'motivo' => 'codigo_invalido_o_expirado'], 404);
        }

        // 4. Crear (o reusar) el usuario-dispositivo, EN LA BASE DEL TENANT
        $usuarioDispositivo = User::firstOrCreate(
            ['name' => $registro->nombre_dispositivo, 'rol' => 'dispositivo'],
            [
                'email'    => Str::slug($registro->nombre_dispositivo) . '-' . Str::random(6) . '@dispositivo.interno',
                'password' => bcrypt(Str::random(40)),
            ]
        );

        // Revoca tokens previos de este dispositivo antes de emitir uno nuevo
        $usuarioDispositivo->tokens()->delete();

        $tokenCompleto = $usuarioDispositivo->createToken($registro->nombre_dispositivo)->plainTextToken;
        // $tokenCompleto tiene formato "{id}|{texto_plano}" — separamos el id
        $tokenId = (int) strtok($tokenCompleto, '|');

        // 5. Mapeo mínimo del token en CENTRAL, para que IdentifyTenant
        //    resuelva el tenant en cada request futura del dispositivo
        DB::connection('central')->table('resolucion_tokens_dispositivo')->insert([
            'token_id'  => $tokenId,
            'tenant_db' => $mapeo->tenant_db,
        ]);

        // 6. Marcar el código como usado, en la base del tenant
        $registro->update(['usado_en' => now()]);

        return response()->json([
            'success' => true,
            'token'   => $tokenCompleto,
            'tipo'    => $registro->tipo,
        ]);
    }
}