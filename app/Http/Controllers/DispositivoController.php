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
     * GET /dispositivos
     * Lista los dispositivos (usuarios rol=dispositivo) del tenant actual.
     * NOTA: no se puede devolver el token en texto plano aquí — Sanctum
     * solo guarda el hash. Solo mandamos metadata (fecha de creación,
     * último uso) para que el frontend decida si mostrar "conectado".
     */
    public function index()
    {
        $dispositivos = User::where('rol', 'dispositivo')
            ->with(['tokens' => function ($q) {
                $q->latest();
            }])
            ->get()
            ->map(function ($user) {
                $tokenActivo = $user->tokens->first();

                return [
                    'id'                 => $user->id,
                    'nombre_dispositivo' => $user->name,
                    'tipo'               => $user->tipo,
                    'vinculado_en'       => $tokenActivo?->created_at,
                    'ultima_conexion'    => $tokenActivo?->last_used_at,
                    // "activo" ahora significa "está vinculado" (tiene un
                    // token vigente), no "usó el token en los últimos 5
                    // min". Antes dependía de last_used_at, así que un
                    // kiosco recién emparejado (o simplemente inactivo por
                    // un rato) se veía como "Sin conexión" aunque siguiera
                    // perfectamente vinculado. 'ultima_conexion' se deja
                    // como dato informativo aparte, no como lo que define
                    // el badge.
                    'activo'             => (bool) $tokenActivo,
                    'tiene_token'        => (bool) $tokenActivo,
                ];
            });

        return response()->json(['dispositivos' => $dispositivos]);
    }

    /**
     * POST /dispositivos/generar-codigo
     * (sin cambios respecto a tu versión original)
     */
    public function generarCodigoEmparejamiento(Request $request)
    {
        $validated = $request->validate([
            'nombre_dispositivo' => 'required|string|max:100',
            'tipo'                => 'required|in:kiosco,tv',
        ]);

        do {
            $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (
            DB::connection('central')->table('resolucion_codigos_dispositivo')
                ->where('codigo', $codigo)
                ->where('expira_en', '>', now())
                ->exists()
        );

        $expiraEn = now()->addMinutes(10);

        $registro = CodigoEmparejamientoDispositivo::create([
            'codigo'              => $codigo,
            'nombre_dispositivo'  => $validated['nombre_dispositivo'],
            'tipo'                => $validated['tipo'],
            'expira_en'           => $expiraEn,
        ]);

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
     * (mismo flujo original, con UN cambio: ahora persiste `tipo` en el
     * User, y actualiza `tipo` también si el usuario ya existía — por si
     * se re-emparejó con un tipo distinto)
     */
    public function emparejar(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|size:6',
        ]);

        $mapeo = DB::connection('central')->table('resolucion_codigos_dispositivo')
            ->where('codigo', $validated['codigo'])
            ->where('expira_en', '>', now())
            ->first();

        if (!$mapeo) {
            return response()->json(['success' => false, 'motivo' => 'codigo_invalido_o_expirado'], 404);
        }

        Config::set('database.connections.mysql.database', $mapeo->tenant_db);
        DB::purge('mysql');
        DB::reconnect('mysql');

        $resultado = DB::transaction(function () use ($validated) {
            $registro = CodigoEmparejamientoDispositivo::where('codigo', $validated['codigo'])
                ->where('expira_en', '>', now())
                ->lockForUpdate()
                ->first();

            if (!$registro || $registro->usado_en) {
                return null;
            }

            $usuarioDispositivo = User::firstOrCreate(
                ['name' => $registro->nombre_dispositivo, 'rol' => 'dispositivo'],
                [
                    'tipo'     => $registro->tipo,
                    'email'    => Str::slug($registro->nombre_dispositivo) . '-' . Str::random(6) . '@dispositivo.interno',
                    'password' => bcrypt(Str::random(40)),
                ]
            );

            // Si el usuario ya existía (re-emparejamiento), aseguramos que
            // el tipo quede actualizado con el del código usado.
            if ($usuarioDispositivo->tipo !== $registro->tipo) {
                $usuarioDispositivo->update(['tipo' => $registro->tipo]);
            }

            $usuarioDispositivo->tokens()->delete();

            $tokenCompleto = $usuarioDispositivo->createToken($registro->nombre_dispositivo)->plainTextToken;
            $tokenId = (int) strtok($tokenCompleto, '|');

            $registro->update(['usado_en' => now()]);

            return [
                'tokenCompleto' => $tokenCompleto,
                'tokenId'       => $tokenId,
                'tipo'          => $registro->tipo,
            ];
        });

        if (!$resultado) {
            return response()->json(['success' => false, 'motivo' => 'codigo_invalido_o_expirado'], 404);
        }

        DB::connection('central')->table('resolucion_tokens_dispositivo')->insert([
            'token_id'  => $resultado['tokenId'],
            'tenant_db' => $mapeo->tenant_db,
        ]);

        return response()->json([
            'success' => true,
            'token'   => $resultado['tokenCompleto'],
            'tipo'    => $resultado['tipo'],
        ]);
    }

    /**
     * POST /dispositivos/{dispositivo}/regenerar-token
     * Revoca el/los token(s) actuales de este dispositivo y crea uno
     * nuevo. El token en texto plano SOLO se puede ver en esta respuesta;
     * no queda guardado en ningún lado después (mismo límite de Sanctum
     * que ya aplicaba en emparejar()).
     */
    public function regenerarToken($id)
    {   
        $dispositivo = User::where('rol', 'dispositivo')->findOrFail($id);

        if ($dispositivo->rol !== 'dispositivo') {
            abort(404);
        }

        $resultado = DB::transaction(function () use ($dispositivo) {
            $idsViejos = $dispositivo->tokens()->pluck('id');

            if ($idsViejos->isNotEmpty()) {
                DB::connection('central')->table('resolucion_tokens_dispositivo')
                    ->whereIn('token_id', $idsViejos)
                    ->delete();
            }

            $dispositivo->tokens()->delete();

            $tokenCompleto = $dispositivo->createToken($dispositivo->name)->plainTextToken;
            $tokenId = (int) strtok($tokenCompleto, '|');

            return ['tokenCompleto' => $tokenCompleto, 'tokenId' => $tokenId];
        });

        DB::connection('central')->table('resolucion_tokens_dispositivo')->insert([
            'token_id'  => $resultado['tokenId'],
            'tenant_db' => config('database.connections.mysql.database'),
        ]);

        return response()->json([
            'success' => true,
            'token'   => $resultado['tokenCompleto'],
        ]);
    }

    /**
     * DELETE /dispositivos/{dispositivo}
     * Desvincula el dispositivo: borra sus tokens (local y mapeo en
     * central) y el usuario mismo.
     */
    public function destroy($id)
    {   
        $dispositivo = User::where('rol', 'dispositivo')->findOrFail($id);
        
        if ($dispositivo->rol !== 'dispositivo') {
            abort(404);
        }

        DB::transaction(function () use ($dispositivo) {
            $idsTokens = $dispositivo->tokens()->pluck('id');

            if ($idsTokens->isNotEmpty()) {
                DB::connection('central')->table('resolucion_tokens_dispositivo')
                    ->whereIn('token_id', $idsTokens)
                    ->delete();
            }

            $dispositivo->tokens()->delete();
            $dispositivo->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Dispositivo desvinculado correctamente',
        ]);
    }

    /**
     * GET /dispositivos/verificar-codigo/{codigo}
     * Usado por AgregaDispositivo.vue mientras el código está activo en
     * pantalla, para detectar en tiempo real (via polling) el momento en
     * que el dispositivo termina de vincularse, sin esperar a que el
     * usuario recargue o a que el contador expire.
     */
    public function verificarCodigo($codigo)
    {
        $registro = CodigoEmparejamientoDispositivo::where('codigo', $codigo)->first();

        if (!$registro) {
            return response()->json(['vinculado' => false, 'existe' => false]);
        }
        return response()->json([
            'existe'    => true,
            'vinculado' => (bool) $registro->usado_en,
            'nombre_dispositivo' => $registro->nombre_dispositivo,
        ]);
    }
}