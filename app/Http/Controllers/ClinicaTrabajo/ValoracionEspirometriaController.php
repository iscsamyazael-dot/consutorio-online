<?php

namespace App\Http\Controllers\ClinicaTrabajo;

use App\Http\Controllers\Controller;
use App\Models\ClinicaTrabajo\ValoracionEspirometria;
use App\Http\Requests\ClinicaTrabajo\StoreEspirometriaRequest;
use App\Http\Requests\ClinicaTrabajo\UpdateEspirometriaRequest;
use Illuminate\Http\JsonResponse;

class ValoracionEspirometriaController extends Controller
{
    /**
     * GET /clinica-trabajo/espirometria
     * Lista todas las valoraciones espirométricas con filtros
     */
    public function index(): JsonResponse
    {
        $query = ValoracionEspirometria::query();

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
            ->with(['paciente', 'empresa', 'puesto', 'medico'])
            ->paginate(15);

        return response()->json($valoraciones);
    }

    /**
     * GET /clinica-trabajo/espirometria/{id}
     * Obtiene 1 valoración con todas sus relaciones
     */
    public function show($id): JsonResponse
    {
        $valoracion = ValoracionEspirometria::with([
            'paciente',
            'empresa',
            'puesto',
            'medico'
        ])->findOrFail($id);

        return response()->json($valoracion);
    }

    /**
     * POST /clinica-trabajo/espirometria
     * Crea nueva valoración espirométrica
     */
    public function store(StoreEspirometriaRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $valoracion = ValoracionEspirometria::create(array_merge($validated, [
            'medico_id' => $request->user()->id
        ]));

        return response()->json([
            'message' => 'Valoración espirométrica creada exitosamente',
            'data' => $valoracion->load(['paciente', 'empresa', 'puesto', 'medico'])
        ], 201);
    }

    /**
     * PUT /clinica-trabajo/espirometria/{id}
     * Actualiza valoración existente
     */
    public function update($id, UpdateEspirometriaRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $valoracion = ValoracionEspirometria::findOrFail($id);
        $valoracion->update($validated);

        return response()->json([
            'message' => 'Valoración espirométrica actualizada',
            'data' => $valoracion->load(['paciente', 'empresa', 'puesto', 'medico'])
        ]);
    }

    /**
     * DELETE /clinica-trabajo/espirometria/{id}
     * Elimina valoración
     */
    public function destroy($id): JsonResponse
    {
        ValoracionEspirometria::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Valoración espirométrica eliminada'
        ]);
    }

    /**
     * GET /clinica-trabajo/espirometria/{id}/imprimir
     */
    public function imprimir($id)
    {
        $valoracion = ValoracionEspirometria::with([
            'paciente', 'empresa', 'medico', 'puesto'
        ])->findOrFail($id);

        return response()->json([
            'folio' => $valoracion->folio,
            'paciente' => $valoracion->paciente->nombre,
            'empresa' => $valoracion->empresa->razon_social,
            'fecha' => $valoracion->fecha_valoracion,
            'tipo' => $valoracion->tipo_evaluacion,
            'aptitud' => $valoracion->aptitud,
            'imc' => $valoracion->imc,
            'calidad_sesion' => $valoracion->calidad_sesion,
            'interpretacion_sistema' => $valoracion->interpretacion_sistema,
            'diagnostico_medico' => $valoracion->diagnostico_medico,
            'recomendaciones' => $valoracion->recomendaciones,
            'parametros' => [
                'fvc_l' => $valoracion->fvc_l,
                'fev1_l' => $valoracion->fev1_l,
                'fev1_fvc_ratio' => $valoracion->fev1_fvc_ratio,
                'fef25_75' => $valoracion->fef25_75,
                'pef_l_s' => $valoracion->pef_l_s,
                'fet_s' => $valoracion->fet_s,
                'fev1_porcentaje_predicho' => $valoracion->fev1_porcentaje_predicho,
                'fvc_porcentaje_predicho' => $valoracion->fvc_porcentaje_predicho,
            ],
        ]);
    }
}