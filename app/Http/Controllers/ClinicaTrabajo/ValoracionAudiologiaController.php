<?php

namespace App\Http\Controllers\ClinicaTrabajo;

use App\Http\Controllers\Controller;
use App\Models\ClinicaTrabajo\ValoracionAudiologia;
use App\Http\Requests\ClinicaTrabajo\StoreAudiologiaRequest;
use App\Http\Requests\ClinicaTrabajo\UpdateAudiologiaRequest;
use App\Services\ClinicaTrabajo\AudiologiaService;
use Illuminate\Http\JsonResponse;

class ValoracionAudiologiaController extends Controller
{
    public function __construct(
        private AudiologiaService $audiologiaService
    ) {}

    /**
     * GET /clinica-trabajo/audiologia
     * Lista todas las valoraciones audiológicas con filtros
     */
    public function index(): JsonResponse
    {
        $query = ValoracionAudiologia::query();

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
            ->with(['paciente', 'empresa', 'puesto', 'audiologo'])
            ->paginate(15);

        return response()->json($valoraciones);
    }

    /**
     * GET /clinica-trabajo/audiologia/{id}
     * Obtiene 1 valoración con todas sus relaciones
     */
    public function show($id): JsonResponse
    {
        $valoracion = ValoracionAudiologia::with([
            'paciente',
            'empresa',
            'puesto',
            'audiologo'
        ])->findOrFail($id);

        return response()->json($valoracion);
    }

    /**
     * POST /clinica-trabajo/audiologia
     * Crea nueva valoración audiológica
     */
    public function store(StoreAudiologiaRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $valoracion = $this->audiologiaService->crear(
            $validated,
            $request->user()->id
        );

        return response()->json([
            'message' => 'Valoración creada exitosamente',
            'data' => $valoracion->load(['paciente', 'empresa', 'puesto'])
        ], 201);
    }

    /**
     * PUT /clinica-trabajo/audiologia/{id}
     * Actualiza valoración existente
     */
    public function update($id, UpdateAudiologiaRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $valoracion = $this->audiologiaService->actualizar(
            $id,
            $validated
        );

        return response()->json([
            'message' => 'Valoración actualizada',
            'data' => $valoracion->load(['paciente', 'empresa', 'puesto'])
        ]);
    }

    /**
     * DELETE /clinica-trabajo/audiologia/{id}
     * Elimina valoración
     */
    public function destroy($id): JsonResponse
    {
        ValoracionAudiologia::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Valoración eliminada'
        ]);
    }

    /**
     * GET /clinica-trabajo/audiologia/{id}/imprimir
     */
    public function imprimir($id)
    {
        $valoracion = ValoracionAudiologia::with([
            'paciente', 'empresa', 'puesto', 'audiologo'
        ])->findOrFail($id);

        return response()->json([
            'folio' => $valoracion->folio,
            'paciente' => $valoracion->paciente->nombre,
            'empresa' => $valoracion->empresa->razon_social,
            'fecha' => $valoracion->fecha_valoracion,
            'tipo' => $valoracion->tipo_evaluacion,
            'aptitud' => $valoracion->aptitud,
            'tipo_hipoacusia' => $valoracion->tipo_hipoacusia,
            'pta_promedio_tonal_puro_der' => $valoracion->pta_promedio_tonal_puro_der,
            'pta_promedio_tonal_puro_izq' => $valoracion->pta_promedio_tonal_puro_izq,
            'requiere_seguimiento_audiometrico' => $valoracion->requiere_seguimiento_audiometrico,
            'uso_protector_auditivo' => $valoracion->uso_protector_auditivo,
        ]);
    }
}