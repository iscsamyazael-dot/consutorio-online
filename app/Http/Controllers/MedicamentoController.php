<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Medicamento;
use App\Models\inventario;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
     * Resumen de estadísticas para las tarjetas del dashboard de medicamentos:
     * Stock Total, Medicamentos Críticos, Próximos a Caducar y Sin Existencia.
     */
    public function resumen()
    {
        $medicamentos = Medicamento::with('inventario')->get();

        $totalMedicamentos = Medicamento::count();
        $criticos = 0;
        $sinExistencia = 0;
        $proximosCaducar = 0;

        $hoy = now();
        $limiteCaducidad = now()->addDays(30);

        foreach ($medicamentos as $medicamento) {
            $inv = $medicamento->inventario;
            if (!$inv) continue;


            if ($inv->stock_actual == 0) {
                $sinExistencia++;
            } elseif ($inv->stock_actual <= $inv->stock_minimo) {
                $criticos++;
            }

            if ($inv->fecha_caducidad) {
                $fechaCad = Carbon::parse($inv->fecha_caducidad);
                if ($fechaCad->between($hoy, $limiteCaducidad)) {
                    $proximosCaducar++;
                }
            }
        }

        return response()->json([
        'total_medicamentos' => $totalMedicamentos,
        'criticos' => $criticos,
        'proximos_caducar' => $proximosCaducar,
        'sin_existencia' => $sinExistencia,
    ]);
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
        'ubicacion' => null,
        'fecha_caducidad' => $request->fecha_caducidad ?? null
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