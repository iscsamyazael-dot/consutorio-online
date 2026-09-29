<?php

namespace App\Http\Controllers\ClinicaTrabajo;

use App\Http\Controllers\Controller;
use App\Models\ClinicaTrabajo\ValoracionNutricion;
use App\Http\Requests\ClinicaTrabajo\StoreNutricionRequest;
use App\Http\Requests\ClinicaTrabajo\UpdateNutricionRequest;
use App\Services\ClinicaTrabajo\NutricionService;
use Illuminate\Http\JsonResponse;

class ValoracionNutricionController extends Controller
{
    public function __construct(
        private NutricionService $nutricionService
    ) {}

    /**
     * GET /clinica-trabajo/nutricion
     * Lista todas las valoraciones nutricionales con filtros
     */
    public function index(): JsonResponse
    {
        $query = ValoracionNutricion::query();

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
            ->with(['paciente', 'empresa', 'puesto', 'nutriologo', 'alimentos'])
            ->paginate(15);

        return response()->json($valoraciones);
    }

    /**
     * GET /clinica-trabajo/nutricion/{id}
     * Obtiene 1 valoración con todas sus relaciones
     */
    public function show($id): JsonResponse
    {
        $valoracion = ValoracionNutricion::with([
            'paciente',
            'empresa',
            'puesto',
            'nutriologo',
            'alimentos'
        ])->findOrFail($id);

        return response()->json($valoracion);
    }

    /**
     * POST /clinica-trabajo/nutricion
     * Crea nueva valoración nutricional
     */
    public function store(StoreNutricionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $valoracion = $this->nutricionService->crear(
            $validated,
            $request->user()->id
        );

        return response()->json([
            'message' => 'Valoración creada exitosamente',
            'data' => $valoracion->load(['paciente', 'alimentos'])
        ], 201);
    }

    /**
     * PUT /clinica-trabajo/nutricion/{id}
     * Actualiza valoración existente
     */
    public function update($id, UpdateNutricionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $valoracion = $this->nutricionService->actualizar(
            $id,
            $validated
        );

        return response()->json([
            'message' => 'Valoración actualizada',
            'data' => $valoracion->load(['paciente', 'alimentos'])
        ]);
    }

    /**
     * DELETE /clinica-trabajo/nutricion/{id}
     * Elimina valoración
     */
    public function destroy($id): JsonResponse
    {
        ValoracionNutricion::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Valoración eliminada'
        ]);
    }

    /**
     * GET /clinica-trabajo/nutricion/{id}/imprimir
     */
    public function imprimir($id)
    {
        $valoracion = ValoracionNutricion::with([
            'paciente', 'empresa', 'nutriologo', 'alimentos'
        ])->findOrFail($id);

        return response()->json([
            'folio' => $valoracion->folio,
            'paciente' => $valoracion->paciente->nombre,
            'empresa' => $valoracion->empresa->razon_social,
            'fecha' => $valoracion->fecha_valoracion,
            'tipo' => $valoracion->tipo_evaluacion,
            'aptitud' => $valoracion->aptitud,
            'imc' => $valoracion->imc,
            'imc_clasificacion' => $valoracion->imc_clasificacion,
            'alimentos' => $valoracion->alimentos->pluck('alimento', 'frecuencia_texto')->toArray(),
        ]);
    }
}