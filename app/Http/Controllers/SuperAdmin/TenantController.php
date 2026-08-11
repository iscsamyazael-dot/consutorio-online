<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

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
        $cliente=Tenant::create([
            'folio'=> $request->folio,
            'nombre_consultorio'=> $request->nombre_consultorio,
            'db_name'=> $request->db_name,
            'dominio_correo'=> $request->dominio_correo,
            'estatus'=> $request->estatus,
        ]);
        return response()->json([
        'success' => true,
        'message' => 'cliente creado correctamente',
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
