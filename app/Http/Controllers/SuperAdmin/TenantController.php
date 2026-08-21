<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Tenant::all();
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {   
        // 1. Generar el folio dinámico (Ej: CONSULTORIO-2026-001)
        $year = date('Y');
        $ultimoTenant = Tenant::latest('id')->first();
        $siguienteId = $ultimoTenant ? $ultimoTenant->id + 1 : 1;
        $folio = 'CONSULTORIO-' . $year . '-' . str_pad($siguienteId, 3, '0', STR_PAD_LEFT);

        // 2. Validar o asegurar que tenemos el nombre de la base de datos
        $dbName = $request->db_name;

        $cliente=Tenant::create([
            'folio'=> $folio,
            'nombre_consultorio'=> $request->nombre_consultorio,
            'db_name'=> $dbName,
            'dominio_correo'=> $request->dominio_correo,
            'estatus'=> $request->estatus,
        ]);
        
        try {
            // Log para saber qué nombre estamos intentando crear
            \Log::info("Intentando crear BD: " . $dbName);
            // 3. CREACIÓN DE LA BASE DE DATOS FÍSICA (Multi-tenant limpio)
            // Nota: Asegúrate de escapar o validar que el nombre de la BD sea seguro contra SQL Injection
            DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            
            // 4. Nombre de tu base de datos de prueba que sirve como plantilla maestra
            // (Asegúrate de cambiar 'consultorio_online' por el nombre real de tu BD de pruebas si es diferente)
            $dbTemplate = 'consultorio_online';
            
            // Obtenemos todas las tablas de la base de datos de prueba
            $tablas = DB::select("SHOW TABLES FROM `$dbTemplate`");
            $propertyKey = "Tables_in_" . $dbTemplate;

            // Desactivamos temporalmente las llaves foráneas para evitar conflictos al replicar la estructura
            DB::statement("SET FOREIGN_KEY_CHECKS = 0;");

            foreach ($tablas as $tablaObj) {
                $tabla = $tablaObj->$propertyKey;
                
                // Replicamos puramente la estructura limpia de cada tabla (CREATE TABLE ... LIKE ...)
                DB::statement("CREATE TABLE `$dbName`.`$tabla` LIKE `$dbTemplate`.`$tabla`;");
            }

            // Reactivamos las llaves foráneas
            DB::statement("SET FOREIGN_KEY_CHECKS = 1;");
           
            // ==========================================
            // INSERTAR USUARIO ADMINISTRADOR POR DEFECTO
            // ==========================================
            $dominioCorreo = $request->dominio_correo; 
            $emailAdmin = "admin@" . $dominioCorreo; 

            DB::table($dbName . '.users')->insert([
                'name' => 'Administrador ' . $request->nombre_consultorio,
                'email' => $emailAdmin,
                'password' => bcrypt('password123'), // Contraseña temporal inicial
                'rol'=> 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // ==========================================

        } catch (\Exception $e) {
            // Si ocurre un error, puedes hacer un rollback eliminando el tenant creado
             $cliente->delete();
            // Si falla la creación de la BD física, puedes decidir borrar el registro o reportarlo
            // $cliente->delete();
            // ESTO ES LO QUE NOS VA A DECIR LA VERDAD
            \Log::error("ERROR CRÍTICO AL CREAR BD: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la base de datos física: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
        'success' => true,
        'message' => 'Cliente y base de datos creados correctamente',
        'data' => [
            'cliente' => $cliente
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Buscamos explícitamente por el id (o puedes cambiar 'id' por el campo que uses)
        $tenant = Tenant::find($id);
        // Si no lo encuentra, puedes retornar un 404 limpio
        if (!$tenant) {
            return response()->json(['message' => 'Inquilino no encontrado'], 404);
        }
        return response()->json($tenant);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant -> update([
            'nombre_consultorio' => $request->nombre_consultorio,
            'estatus' => $request->estatus,
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Tenant actualizado correctamente',
            'data'    => $tenant->fresh()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant)
    {
        //
    }
}
