<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\inventario;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;

class MovimientoInventarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        // ======================================
        // VALIDAR TIPO DE MOVIMIENTO
        // ======================================
        // NUEVO: antes, si tipo_movimiento no era 'entrada'/'salida'/'ajuste',
        // $stockNuevo quedaba sin definir y rompía el create()/update() de abajo.
        if (!in_array($request->tipo_movimiento, ['entrada', 'salida', 'ajuste'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de movimiento inválido. Debe ser entrada, salida o ajuste.'
            ], 422);
        }

        if (!$request->cantidad || $request->cantidad <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'La cantidad debe ser mayor a cero.'
            ], 422);
        }

        // ======================================
        // BUSCAR EL INVENTARIO DEL MEDICAMENTO
        // ======================================
        $inventario = Inventario::where(
            'medicamento_id',
            $request->medicamento_id
        )->first();
        // Si no existe inventario mostramos error
        if (!$inventario) {
            return response()->json([
                'success' => false,
                'message' => 'No existe inventario para este medicamento'
            ], 404);
        }
        // ======================================
        // GUARDAR EL STOCK ACTUAL
        // ======================================
        $stockAnterior = $inventario->stock_actual;

        // ======================================
        // CALCULAR EL NUEVO STOCK
        // ======================================
        if ($request->tipo_movimiento == 'entrada') {
            // Ejemplo:
            // 100 + 50 = 150
            $stockNuevo = $stockAnterior + $request->cantidad;
        }
        if ($request->tipo_movimiento == 'salida') {
            // Ejemplo:
            // 100 - 20 = 80
            $stockNuevo = $stockAnterior - $request->cantidad;
        }
        if ($request->tipo_movimiento == 'ajuste') {
            // Ajuste significa:
            // "el stock real es este"
            $stockNuevo = $request->cantidad;
        }
        // ======================================
        // GUARDAR MOVIMIENTO EN HISTORIAL
        // ======================================
        $movimiento = MovimientoInventario::create([
            'medicamento_id' => $request->medicamento_id,
            'tipo_movimiento' => $request->tipo_movimiento,
            'cantidad' => $request->cantidad,
            // Guardamos para auditoría
            'stock_anterior' => $stockAnterior,
            'stock_nuevo' => $stockNuevo,
            // Datos adicionales
            'lote' => $request->lote,
            'fecha_caducidad' => $request->fecha_caducidad,
            'costo_unitario' => $request->costo_unitario,
            'proveedor' => $request->proveedor,
            'motivo_movimiento' => $request->motivo_movimiento,
            'referencia_documento' => $request->referencia_documento,
            'observaciones' => $request->observaciones,
            'fecha_movimiento' => $request->fecha_movimiento
        ]);

        // ======================================
        // ACTUALIZAR INVENTARIO
        // ======================================
        $inventario->update([
            // Actualizamos el nuevo stock
            'stock_actual' => $stockNuevo,
            // Configuramos el stock minimo
            'stock_minimo' => $request->stock_minimo,
            // Actualizamos ubicación
            'ubicacion' => $request->ubicacion,
            // NUEVO: sincronizamos la fecha de caducidad del lote más
            // reciente en inventario, que es de donde lee resumen() para
            // la tarjeta KPI "Próximos a Caducar". Antes solo quedaba
            // guardada en el historial de movimientos y el KPI nunca
            // se enteraba de un lote nuevo.
            'fecha_caducidad' => $request->fecha_caducidad ?? $inventario->fecha_caducidad,
        ]);

        // ======================================
        // RESPUESTA
        // ======================================
        return response()->json([
            'success' => true,
            'message' => 'Movimiento registrado correctamente',
            'data' => $movimiento
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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