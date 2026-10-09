<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Modulo;
use App\Models\TenantModulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index()
    {
        return Tenant::with('modulos')->get();
    }

    public function totalClientes()
    {
        return Tenant::query()->count('nombre_consultorio');
    }

    public function totalActivos()
    {
        return Tenant::query()
            ->where('estatus', 'activo')
            ->count('nombre_consultorio');
    }

    public function totalSuspendidos()
    {
        return Tenant::query()
            ->where('estatus', 'suspendido')
            ->count('nombre_consultorio');
    }

    public function store(Request $request)
    {
        // 1. Generar folio
        $year = date('Y');
        $ultimoTenant = Tenant::latest('id')->first();
        $siguienteId = $ultimoTenant ? $ultimoTenant->id + 1 : 1;
        $folio = 'CONSULTORIO-' . $year . '-' . str_pad($siguienteId, 3, '0', STR_PAD_LEFT);

        $dbName = $request->db_name;
        
        // 2. Crear Tenant
        $cliente = Tenant::create([
            'folio' => $folio,
            'nombre_consultorio' => $request->nombre_consultorio,
            'db_name' => $dbName,
            'dominio_correo' => $request->dominio_correo,
            'estatus' => $request->estatus,
        ]);

        // 3. Asignar Módulos en la base central
        $modulosIds = $request->input('modulos', []);
        $adminId = Auth::guard('super_admin')->id();

        foreach ($modulosIds as $moduloId) {
            TenantModulo::create([
                'tenant_id' => $cliente->id,
                'modulo_id' => $moduloId,
                'activo' => 1,
                'activado_por' => $adminId,
                'fecha_activacion' => now(),
            ]);
        }

        try {
            // 4. Crear base de datos física
            \Log::info("Intentando crear BD: " . $dbName);
            DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            
            $dbTemplate = 'consultorio_online';
            $tablas = DB::select("SHOW TABLES FROM `$dbTemplate`");
            $propertyKey = "Tables_in_" . $dbTemplate;
            
            DB::statement("SET FOREIGN_KEY_CHECKS = 0;");
            foreach ($tablas as $tablaObj) {
                $tabla = $tablaObj->$propertyKey;
                DB::statement("CREATE TABLE `$dbName`.`$tabla` LIKE `$dbTemplate`.`$tabla`;");
            }
            DB::statement("SET FOREIGN_KEY_CHECKS = 1;");

            // 5. Crear usuario administrador por defecto
            $dominioCorreo = $request->dominio_correo;
            $emailAdmin = "admin@" . $dominioCorreo;
            
            $adminUserId = DB::table($dbName . '.users')->insertGetId([
                'name' => 'Administrador ' . $request->nombre_consultorio,
                'email' => $emailAdmin,
                'password' => bcrypt('password123'),
                'rol' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 6. Asignar módulos al admin en usuario_modulos (BD del tenant)
            $modulos = Modulo::whereIn('id', $modulosIds)->get();
            
            foreach ($modulos as $modulo) {
                DB::table($dbName . '.usuario_modulos')->insert([
                    'user_id' => $adminUserId,
                    'modulo_codigo' => $modulo->clave,
                    'activo' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

        } catch (\Exception $e) {
            // Rollback si falla
            $cliente->modulos()->detach();
            $cliente->delete();
            \Log::error("ERROR CRÍTICO AL CREAR BD: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la base de datos física: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cliente y base de datos creados correctamente',
            'data' => ['cliente' => $cliente->load('modulos')]
        ]);
    }

    public function show($id)
    {
        $tenant = Tenant::with('modulos')->find($id);
        
        if (!$tenant) {
            return response()->json(['message' => 'Inquilino no encontrado'], 404);
        }
        
        return response()->json($tenant);
    }

    public function update(Request $request, string $id)
    {
        $tenant = Tenant::findOrFail($id);
        
        $tenant->update([
            'nombre_consultorio' => $request->nombre_consultorio,
            'estatus' => $request->estatus,
        ]);

        // 1. Actualizar módulos en la base central
        $modulosIds = $request->input('modulos', []);
        $adminId = Auth::guard('super_admin')->id();

        // Desactivar todos los módulos actuales
        TenantModulo::where('tenant_id', $tenant->id)->update(['activo' => 0]);

        // Activar/Crear los seleccionados
        foreach ($modulosIds as $moduloId) {
            TenantModulo::updateOrCreate(
                ['tenant_id' => $tenant->id, 'modulo_id' => $moduloId],
                [
                    'activo' => 1,
                    'activado_por' => $adminId,
                    'fecha_activacion' => now(),
                ]
            );
        }

        // 2. Actualizar usuario_modulos en la BD del tenant para todos los admins
        try {
            $dbName = $tenant->db_name;
            $modulos = Modulo::whereIn('id', $modulosIds)->get();
            
            // Desactivar todos los módulos para todos los usuarios admin
            DB::table($dbName . '.usuario_modulos')
                ->whereIn('user_id', function($query) use ($dbName) {
                    $query->select('id')
                          ->from($dbName . '.users')
                          ->where('rol', 'admin');
                })
                ->update(['activo' => 0]);

            // Activar los módulos seleccionados para todos los admins
            $adminIds = DB::table($dbName . '.users')
                ->where('rol', 'admin')
                ->pluck('id');

            foreach ($adminIds as $adminUserId) {
                foreach ($modulos as $modulo) {
                    DB::table($dbName . '.usuario_modulos')->updateOrCreate(
                        [
                            'user_id' => $adminUserId,
                            'modulo_codigo' => $modulo->clave,
                        ],
                        [
                            'activo' => 1,
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            \Log::error("Error al actualizar usuario_modulos: " . $e->getMessage());
            // No fallamos la actualización del tenant si esto falla
        }

        return response()->json([
            'success' => true,
            'message' => 'Tenant actualizado correctamente',
            'data' => $tenant->fresh()->load('modulos')
        ]);
    }

    public function getModulos()
    {
        $modulos = Modulo::orderBy('nombre')->get(['id', 'clave', 'nombre', 'descripcion']);
        return response()->json($modulos);
    }

    public function destroy(Tenant $tenant)
    {
        //
    }
}