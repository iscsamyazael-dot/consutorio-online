<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Medicamento;
use App\Models\inventario;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;

class MedicamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       return Medicamento:: with('inventario','ultimoMovimiento')->get();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $medicamento = Medicamento::create([
        'codigo' => $request->codigo,
        'nombre' => $request->nombre,
        'nombre_generico' => $request->nombre_generico,
        'presentacion' => $request->presentacion,
        'concentracion' => $request->concentracion,
        'via_administracion' => $request->via_administracion,
        'descripcion' => $request->descripcion,
        'indicaciones' => $request->indicaciones,
        'contraindicaciones' => $request->contraindicaciones,
        'efectos_secundarios' => $request->efectos_secundarios,
        'precio' => $request->precio,
        'requiere_receta' => $request->requiere_receta,
        'activo' => $request->activo,
         ]);
       

        $Inventario = inventario::create([
        'medicamento_id' => $medicamento ->id,
        'stock_actual' => 0,
        'stock_minimo' => 0,
        'ubicacion' => null
        ]);

        return response()->json([
        'success' => true,
        'message' => 'Medicamento e inventario creados correctamente',
        'data' => [
            'medicamento' => $medicamento,
            'inventario' => $Inventario]
        ]);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Medicamento::with([
        'inventario',
        'movimientosInventario'
        ])->find($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
