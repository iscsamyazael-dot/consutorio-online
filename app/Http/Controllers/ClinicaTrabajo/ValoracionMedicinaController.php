<?php

namespace App\Http\Controllers\ClinicaTrabajo;

use App\Http\Controllers\Controller;
use App\Models\ClinicaTrabajo\ValoracionOcupacional;
use App\Models\Paciente;
use App\Http\Requests\ClinicaTrabajo\StoreMedicinaRequest;
use App\Http\Requests\ClinicaTrabajo\UpdateMedicinaRequest;
use App\Services\ClinicaTrabajo\MedicinaService;
use App\Services\IAClinicaTrabajoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValoracionMedicinaController extends Controller
{
    public function __construct(
        private MedicinaService $medicinaService
    ) {}

    /**
     * GET /clinica/medicina
     * Lista todas las valoraciones médicas/ocupacionales con filtros
     */
    public function index(): JsonResponse
    {
        $query = ValoracionOcupacional::query();

        // Filtros opcionales
        if (request('empresa_id')) {
            $query->where('empresa_cliente_id', request('empresa_id'));
        }
        if (request('tipo')) {
            $query->where('tipo', request('tipo'));
        }
        if (request('fecha_desde') && request('fecha_hasta')) {
            $query->whereBetween('fecha_valoracion', [
                request('fecha_desde'),
                request('fecha_hasta')
            ]);
        }
        if (request('aptitud')) {
            $query->where('dictamen', request('aptitud'));
        }

        $valoraciones = $query
            ->with(['paciente', 'empresaCliente', 'puestoTrabajo', 'medico'])
            ->paginate(15);

        return response()->json($valoraciones);
    }

    /**
     * GET /clinica/medicina/pacientes/buscar
     * Busca pacientes para autocomplete
     */
    public function buscarPacientes(Request $request): JsonResponse
    {
        $buscar = trim($request->get('q') ?? $request->get('buscar') ?? '');

        if (empty($buscar)) {
            return response()->json([]);
        }

        $match = strlen($buscar) < 3 ? "{$buscar}%" : "%{$buscar}%";

        $pacientes = Paciente::select('id', 'paciente_id', 'nombre', 'apellido_paterno', 'apellido_materno', 'fecha_nacimiento', 'sexo')
            ->where('paciente_id', 'like', $match)
            ->orWhere('nombre', 'like', $match)
            ->orWhere('apellido_paterno', 'like', $match)
            ->orWhere('apellido_materno', 'like', $match)
            ->limit(10)
            ->get()
            ->map(function ($paciente) {
                $paciente->nombre_completo = trim("{$paciente->nombre} {$paciente->apellido_paterno} {$paciente->apellido_materno}");
                $paciente->edad = $paciente->fecha_nacimiento
                    ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age
                    : null;
                $paciente->edad_formateada = $paciente->fecha_nacimiento
                    ? "{$paciente->edad} años"
                    : 'Edad no registrada';
                return $paciente;
            });

        return response()->json($pacientes);
    }

    /**
     * POST /clinica/medicina/ficha-completa
     * Guarda la ficha médica ocupacional completa
     */
    public function guardarFichaCompleta(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'datos_puesto' => 'required|array',
            'exposicion_riesgos' => 'nullable|array',
            'antecedentes' => 'nullable|array',
            'clinico_examen' => 'nullable|array',
            'aptitud' => 'nullable|string',
            'conversacion' => 'nullable|array',
        ]);

        $valoracion = $this->medicinaService->crearFichaCompleta(
            $validated,
            $request->user()->id
        );

        return response()->json([
            'message' => 'Ficha médica guardada correctamente',
            'data' => $valoracion->load(['paciente', 'empresaCliente', 'puestoTrabajo', 'medico'])
        ], 201);
    }

    /**
     * POST /clinica/medicina/consultar-ia
     * Consulta a la IA con el contexto de la ficha
     */
    public function consultarIA(Request $request, IAClinicaTrabajoService $ia): JsonResponse
    {
        $validated = $request->validate([
            'mensaje' => 'required|string|min:1|max:40000',
            'paciente_id' => 'required|exists:pacientes,id',
            'contexto' => 'nullable|array',
        ]);

        $resultado = $ia->consultarMedicinaOcupacional(
            $validated['mensaje'],
            $validated['paciente_id'],
            $validated['contexto'] ?? []
        );

        if ($resultado === null) {
            return response()->json([
                'success' => false,
                'message' => 'La IA no pudo procesar tu solicitud. Intenta de nuevo.'
            ], 502);
        }

        return response()->json([
            'success' => true,
            'data' => $resultado,
        ]);
    }

    /**
     * GET /clinica/medicina/{id}
     * Obtiene 1 valoración con todas sus relaciones
     */
    public function show($id): JsonResponse
    {
        $valoracion = ValoracionOcupacional::with([
            'paciente',
            'empresaCliente',
            'puestoTrabajo',
            'medico',
            'estudiosParaclinicos',
            'normasAplicadas',
            'seguimientos'
        ])->findOrFail($id);

        return response()->json($valoracion);
    }

    /**
     * POST /clinica/medicina
     * Crea nueva valoración médica
     */
    public function store(StoreMedicinaRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $valoracion = $this->medicinaService->crear(
            $validated,
            $request->user()->id
        );

        return response()->json([
            'message' => 'Valoración médica creada exitosamente',
            'data' => $valoracion->load(['paciente', 'empresaCliente', 'puestoTrabajo', 'medico'])
        ], 201);
    }

    /**
     * PUT /clinica/medicina/{id}
     * Actualiza valoración existente
     */
    public function update($id, UpdateMedicinaRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $valoracion = $this->medicinaService->actualizar(
            $id,
            $validated
        );

        return response()->json([
            'message' => 'Valoración médica actualizada',
            'data' => $valoracion->load(['paciente', 'empresaCliente', 'puestoTrabajo', 'medico'])
        ]);
    }

    /**
     * DELETE /clinica/medicina/{id}
     * Elimina valoración
     */
    public function destroy($id): JsonResponse
    {
        ValoracionOcupacional::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Valoración médica eliminada'
        ]);
    }

    /**
     * GET /clinica/medicina/{id}/imprimir
     * Genera reporte en PDF / HTML
     */
    public function imprimir($id)
    {
        $valoracion = ValoracionOcupacional::with([
            'paciente', 'empresaCliente', 'puestoTrabajo', 'medico',
            'estudiosParaclinicos', 'normasAplicadas'
        ])->findOrFail($id);

        return response()->json([
            'folio' => $valoracion->folio,
            'paciente' => $valoracion->paciente->nombre,
            'empresa' => $valoracion->empresaCliente->razon_social,
            'puesto' => $valoracion->puesto_nombre_snapshot,
            'fecha' => $valoracion->fecha_valoracion,
            'tipo' => $valoracion->tipo,
            'aptitud' => $valoracion->dictamen,
            'signos_vitales' => [
                'ta' => $valoracion->ta_sistolica . '/' . $valoracion->ta_diastolica,
                'fc' => $valoracion->fc,
                'fr' => $valoracion->fr,
                'spo2' => $valoracion->spo2,
            ],
            'antropometria' => [
                'estatura' => $valoracion->estatura_cm,
                'peso' => $valoracion->peso_kg,
                'imc' => $valoracion->imc,
                'imc_clasificacion' => $valoracion->imc_clasificacion,
            ],
            'antecedentes' => $valoracion->antecedentes_patologicos_activos,
            'exploracion' => $valoracion->exploracion_fisica_funcional,
            'estudios' => $valoracion->estudios_paraclinicos_resumen,
            'analisis' => $valoracion->analisis_correlacion_riesgos,
            'dictamen' => $valoracion->dictamen,
            'restricciones' => $valoracion->restricciones,
            'justificacion' => $valoracion->dictamen_justificacion,
            'plan' => $valoracion->plan_intervencion,
            'vigencia_hasta' => $valoracion->vigencia_hasta,
        ]);
    }
    public function vista()
    {
        return view('clinica-trabajo.Medicina');
    }
}