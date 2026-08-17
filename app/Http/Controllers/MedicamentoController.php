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
     * Predicción de reabastecimiento por medicamento.
     *
     * Calcula, a partir del historial de salidas de los últimos
     * $diasHistorial días, un consumo diario promedio. Con ese dato
     * estima cuántos días de stock quedan y cuánto conviene pedir
     * para cubrir $diasCobertura días hacia adelante.
     *
     * No usa IA/ML todavía: es un cálculo estadístico simple (media
     * de consumo), pensado como base sólida antes de meter un modelo
     * más sofisticado si hace falta.
     */
    public function prediccion()
    {
        $diasHistorial = 30;   // ventana de análisis de consumo
        $diasCobertura = 30;   // días de stock que queremos tener disponibles

        $fechaInicio = now()->subDays($diasHistorial);

        $medicamentos = Medicamento::with('inventario')->get();
        $resultado = [];

        foreach ($medicamentos as $medicamento) {
            $inv = $medicamento->inventario;
            if (!$inv) continue;

            $totalSalidas = MovimientoInventario::where('medicamento_id', $medicamento->id)
                ->where('tipo_movimiento', 'salida')
                ->where('fecha_movimiento', '>=', $fechaInicio)
                ->sum('cantidad');

            $consumoDiario = $totalSalidas > 0
                ? round($totalSalidas / $diasHistorial, 2)
                : 0;

            // null = sin consumo reciente registrado, no se puede estimar
            $diasRestantes = $consumoDiario > 0
                ? floor($inv->stock_actual / $consumoDiario)
                : null;

            $cantidadSugerida = $consumoDiario > 0
                ? max(0, ceil(($consumoDiario * $diasCobertura) - $inv->stock_actual))
                : 0;

            $resultado[] = [
                'medicamento_id'           => $medicamento->id,
                'codigo'                   => $medicamento->codigo,
                'nombre'                   => $medicamento->nombre,
                'concentracion'            => $medicamento->concentracion,
                'stock_actual'             => $inv->stock_actual,
                'stock_minimo'             => $inv->stock_minimo,
                'consumo_diario_promedio'  => $consumoDiario,
                'dias_restantes_estimados' => $diasRestantes,
                'cantidad_sugerida_pedir'  => $cantidadSugerida,
            ];
        }

        // Primero los que se agotan más pronto; los sin consumo reciente al final
        usort($resultado, function ($a, $b) {
            if ($a['dias_restantes_estimados'] === null) return 1;
            if ($b['dias_restantes_estimados'] === null) return -1;
            return $a['dias_restantes_estimados'] <=> $b['dias_restantes_estimados'];
        });

        return response()->json($resultado);
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
     *
     * NOTA: antes este método estaba vacío, por eso el botón "Editar" del
     * frontend parecía no hacer nada: el PUT respondía 200 pero no guardaba.
     */
    public function update(Request $request, string $id)
    {
        $medicamento = Medicamento::with('inventario')->findOrFail($id);

        $medicamento->update([
            'codigo'                => $request->codigo,
            'nombre'                => $request->nombre,
            'nombre_generico'       => $request->nombre_generico,
            'presentacion'          => $request->presentacion,
            'concentracion'         => $request->concentracion,
            'via_administracion'    => $request->via_administracion,
            'descripcion'           => $request->descripcion,
            'indicaciones'          => $request->indicaciones,
            'contraindicaciones'    => $request->contraindicaciones,
            'efectos_secundarios'   => $request->efectos_secundarios,
            'precio'                => $request->precio,
            'requiere_receta'       => $request->requiere_receta,
            'activo'                => $request->activo,
        ]);

        // El formulario de edición del frontend también permite ajustar el
        // stock mínimo y la fecha de caducidad, que viven en la tabla de
        // inventario (no en medicamentos). Viajan como
        // { ..., inventario: { stock_minimo: ..., fecha_caducidad: ... } }
        // en el payload.
        if ($medicamento->inventario) {
            $datosInventario = [];

            if ($request->has('inventario.stock_minimo')) {
                $datosInventario['stock_minimo'] = $request->input('inventario.stock_minimo');
            }

            if ($request->has('inventario.fecha_caducidad')) {
                // El input type="date" manda "" cuando se deja vacío; lo
                // convertimos a null para no romper la columna date/datetime.
                $fechaCaducidad = $request->input('inventario.fecha_caducidad');
                $datosInventario['fecha_caducidad'] = $fechaCaducidad !== '' ? $fechaCaducidad : null;
            }

            if (!empty($datosInventario)) {
                $medicamento->inventario->update($datosInventario);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Medicamento actualizado correctamente',
            'data' => $medicamento->fresh(['inventario', 'ultimoMovimiento']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * NOTA: antes este método estaba vacío, por eso el botón "Eliminar" del
     * frontend parecía no hacer nada: el DELETE respondía 200 pero no borraba.
     */
    public function destroy(string $id)
    {
        $medicamento = Medicamento::findOrFail($id);

        // Se borran primero los registros relacionados para evitar errores
        // de llave foránea (inventario y su historial de movimientos).
        $medicamento->movimientosInventario()->delete();
        $medicamento->inventario()->delete();
        $medicamento->delete();

        return response()->json([
            'success' => true,
            'message' => 'Medicamento eliminado correctamente',
        ]);
    }
}