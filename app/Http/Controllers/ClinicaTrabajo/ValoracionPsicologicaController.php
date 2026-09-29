<?php

namespace App\Http\Controllers\ClinicaTrabajo;

use App\Http\Controllers\Controller;
use App\Models\ClinicaTrabajo\ValoracionPsicologica;
use App\Http\Requests\ClinicaTrabajo\StorePsicologicaRequest;
use App\Http\Requests\ClinicaTrabajo\UpdatePsicologicaRequest;
use App\Services\ClinicaTrabajo\PsicologiaService;
use Illuminate\Http\JsonResponse;

class ValoracionPsicologicaController extends Controller
{
    public function __construct(
        private PsicologiaService $psicologiaService
    ) {}

    /**
     * GET /clinica-trabajo/psicologia
     * Lista todas las valoraciones psicológicas con filtros
     */
    public function index(): JsonResponse
    {
        $query = ValoracionPsicologica::query();

        // Filtros opcionales
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
            ->with(['paciente', 'empresa', 'puesto', 'psicologo', 'factoresPsicosociales'])
            ->paginate(15);

        return response()->json($valoraciones);
    }

    /**
     * GET /clinica-trabajo/psicologia/{id}
     * Obtiene 1 valoración con todas sus relaciones
     */
    public function show($id): JsonResponse
    {
        $valoracion = ValoracionPsicologica::with([
            'paciente',
            'empresa',
            'puesto',
            'psicologo',
            'factoresPsicosociales'
        ])->findOrFail($id);

        return response()->json($valoracion);
    }

    /**
     * POST /clinica-trabajo/psicologia
     * Crea nueva valoración psicológica
     */
    public function store(StorePsicologicaRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $valoracion = $this->psicologiaService->crear(
            $validated,
            $request->user()->id
        );

        return response()->json([
            'message' => 'Valoración creada exitosamente',
            'data' => $valoracion->load(['paciente', 'factoresPsicosociales'])
        ], 201);
    }

    /**
     * PUT /clinica-trabajo/psicologia/{id}
     * Actualiza valoración existente
     */
    public function update($id, UpdatePsicologicaRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $valoracion = $this->psicologiaService->actualizar(
            $id,
            $validated
        );

        return response()->json([
            'message' => 'Valoración actualizada',
            'data' => $valoracion->load(['paciente', 'factoresPsicosociales'])
        ]);
    }

    /**
     * DELETE /clinica-trabajo/psicologia/{id}
     * Elimina valoración
     */
    public function destroy($id): JsonResponse
    {
        ValoracionPsicologica::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Valoración eliminada'
        ]);
    }

    /**
     * GET /clinica-trabajo/psicologia/{id}/imprimir
     * Genera reporte en PDF / HTML
     */
    public function imprimir($id)
    {
        $valoracion = ValoracionPsicologica::with([
            'paciente', 'empresa', 'psicologo', 'factoresPsicosociales'
        ])->findOrFail($id);

        return response()->json([
            'folio' => $valoracion->folio,
            'paciente' => $valoracion->paciente->nombre,
            'empresa' => $valoracion->empresa->razon_social,
            'fecha' => $valoracion->fecha_valoracion,
            'tipo' => $valoracion->tipo_evaluacion,
            'aptitud' => $valoracion->aptitud,
            'relacionado_con_trabajo' => $valoracion->relacionado_con_trabajo,
            'requiere_seguimiento' => $valoracion->requiere_seguimiento,
            'plazo_proximo_seguimiento' => $valoracion->plazo_proximo_seguimiento,
            'diagnostico_clinico' => $valoracion->diagnostico_clinico,
            'codigo_cie11' => $valoracion->codigo_cie11,
            'restricciones' => $valoracion->restricciones,
            'recomendaciones' => $valoracion->recomendaciones,
            'factores' => $valoracion->factoresPsicosociales->pluck('factor', 'nivel_severidad')->toArray(),
        ]);
    }
}