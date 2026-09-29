<?php

namespace App\Http\Controllers\ClinicaTrabajo;

use App\Http\Controllers\Controller;
use App\Models\ClinicaTrabajo\ValoracionErgonomica;
use App\Http\Requests\ClinicaTrabajo\StoreErgonomicaRequest;
use App\Http\Requests\ClinicaTrabajo\UpdateErgonomicaRequest;
use App\Services\ClinicaTrabajo\ErgonomiaService;
use Illuminate\Http\JsonResponse;

class ValoracionErgonomicaController extends Controller
{
    public function __construct(
        private ErgonomiaService $ergonomiaService
    ) {}

    /**
     * GET /clinica-trabajo/ergonomia
     * Lista todas las valoraciones ergonómicas con filtros
     */
    public function index(): JsonResponse
    {
        $query = ValoracionErgonomica::query();

        if (request('empresa_id')) {
            $query->where('empresa_cliente_id', request('empresa_id'));
        }
        if (request('tipo')) {
            $query->where('tipo_evaluacion', request('tipo'));
        }
        if (request('fecha_desde') && request('fecha_hasta')) {
            $query->whereBetween('fecha_valoracion', [
                request('fecha_desde'),
                request('fecha_hasta')
            ]);
        }

        $valoraciones = $query
            ->with(['paciente', 'empresa', 'puesto', 'ergonomo', 'riesgosErgonomicos'])
            ->paginate(15);

        return response()->json($valoraciones);
    }

    /**
     * GET /clinica-trabajo/ergonomia/{id}
     * Obtiene 1 valoración con todas sus relaciones
     */
    public function show($id): JsonResponse
    {
        $valoracion = ValoracionErgonomica::with([
            'paciente',
            'empresa',
            'puesto',
            'ergonomo',
            'riesgosErgonomicos'
        ])->findOrFail($id);

        return response()->json($valoracion);
    }

    /**
     * POST /clinica-trabajo/ergonomia
     * Crea nueva valoración ergonómica
     */
    public function store(StoreErgonomicaRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $valoracion = $this->ergonomiaService->crear(
            $validated,
            $request->user()->id
        );

        return response()->json([
            'message' => 'Valoración creada exitosamente',
            'data' => $valoracion->load(['paciente', 'empresa', 'puesto', 'riesgosErgonomicos'])
        ], 201);
    }

    /**
     * PUT /clinica-trabajo/ergonomia/{id}
     * Actualiza valoración existente
     */
    public function update($id, UpdateErgonomicaRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $valoracion = $this->ergonomiaService->actualizar(
            $id,
            $validated
        );

        return response()->json([
            'message' => 'Valoración actualizada',
            'data' => $valoracion->load(['paciente', 'empresa', 'puesto', 'riesgosErgonomicos'])
        ]);
    }

    /**
     * DELETE /clinica-trabajo/ergonomia/{id}
     * Elimina valoración
     */
    public function destroy($id): JsonResponse
    {
        ValoracionErgonomica::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Valoración eliminada'
        ]);
    }

    /**
     * GET /clinica-trabajo/ergonomia/{id}/imprimir
     */
    public function imprimir($id)
    {
        $valoracion = ValoracionErgonomica::with([
            'paciente', 'empresa', 'puesto', 'ergonomo', 'riesgosErgonomicos'
        ])->findOrFail($id);

        return response()->json([
            'folio' => $valoracion->folio,
            'paciente' => $valoracion->paciente->nombre,
            'empresa' => $valoracion->empresa->razon_social,
            'fecha' => $valoracion->fecha_valoracion,
            'tipo' => $valoracion->tipo_evaluacion,
            'instrumento_utilizado' => $valoracion->instrumento_utilizado,
            'rula_puntuacion' => $valoracion->rula_puntuacion,
            'rula_nivel_riesgo' => $valoracion->rula_nivel_riesgo,
            'reba_puntuacion' => $valoracion->reba_puntuacion,
            'reba_nivel_accion' => $valoracion->reba_nivel_accion,
            'niosh_indice_levantamiento' => $valoracion->niosh_indice_levantamiento,
            'niosh_es_seguro' => $valoracion->niosh_es_seguro,
            'posturas_forzadas' => $valoracion->posturas_forzadas,
            'movimientos_repetitivos' => $valoracion->movimientos_repetitivos,
            'manipulacion_cargas' => $valoracion->manipulacion_cargas,
            'requiere_seguimiento' => $valoracion->seguimiento_requerido,
        ]);
    }
}