<?php

namespace App\Http\Controllers\ClinicaTrabajo;

use Illuminate\Http\Request;
use App\Services\IAClinicaTrabajoService;
use App\Services\ClinicaTrabajo\LectorEspirometriaExcel;
use App\Http\Controllers\Controller;
use App\Models\ClinicaTrabajo\ValoracionEspirometria;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ValoracionEspirometriaController extends Controller
{
    /**
     * GET /clinica-trabajo/espirometria
     * Lista todas las valoraciones espirométricas con filtros
     */
    public function index(Request $request): JsonResponse
    {
        $query = ValoracionEspirometria::query();

        if ($request->filled('empresa_id')) {
            $query->where('empresa_cliente_id', $request->input('empresa_id'));
        }
        if ($request->filled('tipo')) {
            $query->where('tipo_evaluacion', $request->input('tipo'));
        }
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $query->whereBetween('fecha_valoracion', [
                $request->input('fecha_desde'),
                $request->input('fecha_hasta'),
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
            'medico',
        ])->findOrFail($id);

        return response()->json($valoracion);
    }

    /**
     * POST /clinica-trabajo/espirometria
     * Crea nueva valoración espirométrica
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->reglas(true));

        $valoracion = ValoracionEspirometria::create(array_merge($validated, [
            'medico_id' => $request->user()->id,
        ]));

        return response()->json([
            'message' => 'Valoración espirométrica creada exitosamente',
            'data' => $valoracion->load(['paciente', 'empresa', 'puesto', 'medico']),
        ], 201);
    }

    /**
     * PUT /clinica-trabajo/espirometria/{id}
     * Actualiza valoración existente
     */
    public function update($id, Request $request): JsonResponse
    {
        $validated = $request->validate($this->reglas(false));

        $valoracion = ValoracionEspirometria::findOrFail($id);
        $valoracion->update($validated);

        return response()->json([
            'message' => 'Valoración espirométrica actualizada',
            'data' => $valoracion->load(['paciente', 'empresa', 'puesto', 'medico']),
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
            'message' => 'Valoración espirométrica eliminada',
        ]);
    }

    /**
     * GET /clinica-trabajo/espirometria/{id}/imprimir
     * Devuelve los datos del reporte: predichos, LLN, puntuación Z, porcentajes,
     * varianzas, calidad (sesión, FEV1 y FVC) y maniobras.
     */
    public function imprimir($id): JsonResponse
    {
        $valoracion = ValoracionEspirometria::with([
            'paciente', 'empresa', 'medico', 'puesto',
        ])->findOrFail($id);

        return response()->json([
            'folio' => $valoracion->folio,
            'paciente' => $valoracion->paciente?->nombre,
            'empresa' => $valoracion->empresa?->razon_social,
            'fecha' => $valoracion->fecha_valoracion,
            'tipo' => $valoracion->tipo_evaluacion,
            'aptitud' => $valoracion->aptitud,
            'imc' => $valoracion->imc,
            'interpretacion_sistema' => $valoracion->interpretacion_sistema,
            'diagnostico_medico' => $valoracion->diagnostico_medico,
            'recomendaciones' => $valoracion->recomendaciones,
            'calidad' => [
                'sesion' => $valoracion->calidad_sesion,
                'fev1' => $valoracion->calidad_fev1,
                'fvc' => $valoracion->calidad_fvc,
            ],
            'varianza' => [
                'fev1_var_l' => $valoracion->fev1_var_l,
                'fvc_var_l' => $valoracion->fvc_var_l,
            ],
            'parametros' => [
                'fvc_l' => $valoracion->fvc_l,
                'fev1_l' => $valoracion->fev1_l,
                'fev1_fvc_ratio' => $valoracion->fev1_fvc_ratio,
                'fef25_75' => $valoracion->fef25_75,
                'pef_l_s' => $valoracion->pef_l_s,
                'fet_s' => $valoracion->fet_s,
                'fev1_porcentaje_predicho' => $valoracion->fev1_porcentaje_predicho,
                'fvc_porcentaje_predicho' => $valoracion->fvc_porcentaje_predicho,
                'fev1_fvc_porcentaje_predicho' => $valoracion->fev1_fvc_porcentaje_predicho,
                'fivc_porcentaje_predicho' => $valoracion->fivc_porcentaje_predicho,
            ],
            'predichos' => [
                'fvc' => ['pred' => $valoracion->fvc_pred, 'lln' => $valoracion->fvc_lln, 'z' => $valoracion->fvc_z],
                'fev1' => ['pred' => $valoracion->fev1_pred, 'lln' => $valoracion->fev1_lln, 'z' => $valoracion->fev1_z],
                'fev1_fvc' => ['pred' => $valoracion->fev1_fvc_pred, 'lln' => $valoracion->fev1_fvc_lln, 'z' => $valoracion->fev1_fvc_z],
                'fef25_75' => ['pred' => $valoracion->fef25_75_pred, 'lln' => $valoracion->fef25_75_lln, 'z' => $valoracion->fef25_75_z],
                'pef' => ['pred' => $valoracion->pef_pred, 'lln' => $valoracion->pef_lln, 'z' => $valoracion->pef_z],
                'fivc' => ['pred' => $valoracion->fivc_pred, 'lln' => $valoracion->fivc_lln, 'z' => $valoracion->fivc_z],
            ],
            'maniobras' => $valoracion->maniobras,
        ]);
    }

    /**
     * POST /clinica-trabajo/espirometria/importar
     * Recibe el informe ndd y devuelve los datos estructurados para el formulario.
     *
     * Según el tipo real del archivo (se detecta por su firma, no solo por la extensión):
     *  - PDF   => IAClinicaTrabajoService::analizarEspirometria (flujo existente, sin cambios).
     *  - Excel => LectorEspirometriaExcel (lectura directa de celdas, sin IA).
     */
    public function importarArchivo(
        Request $request,
        IAClinicaTrabajoService $iaService,
        LectorEspirometriaExcel $lectorExcel
    ): JsonResponse {
        $request->validate([
            'archivo' => 'required|file|max:10240|extensions:pdf,xls,xlsx', // Máximo 10MB
        ]);

        $archivo = $request->file('archivo');
        $rutaCompleta = $archivo->getRealPath();
        $mime = $archivo->getMimeType();

        $tipo = $this->detectarTipoArchivo($rutaCompleta);

        try {
            $datosExtraidos = match ($tipo) {
                'pdf' => $iaService->analizarEspirometria($rutaCompleta, $mime),
                'excel' => $lectorExcel->leer($rutaCompleta),
                default => null,
            };
        } catch (\Throwable $e) {
            Log::error('Error al leer el informe de espirometría', [
                'tipo' => $tipo,
                'error' => $e->getMessage(),
            ]);
            $datosExtraidos = null;
        }

        if (!$datosExtraidos) {
            return response()->json([
                'success' => false,
                'message' => $tipo === 'excel'
                    ? 'No se pudo leer la tabla del Excel. Verifica que sea un informe de espirometría ndd.'
                    : 'No se pudieron extraer los datos del informe. Verifica que sea un PDF de espirometría ndd con texto legible.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Datos extraídos exitosamente',
            'data' => $datosExtraidos,
        ]);
    }

    /**
     * Reglas de validación para crear (store) y actualizar (update).
     * Las reglas de los campos existentes deben conservarse; aquí van las obligatorias
     * según la tabla y los campos nuevos de espirometría.
     */
    private function reglas(bool $esCreacion): array
    {
        $requerido = $esCreacion ? 'required' : 'sometimes|required';
        $numero = 'nullable|numeric';
        $calidad = 'nullable|in:A,B,C,D,E,F,U';

        return [
            // Obligatorios en la tabla
            'paciente_id' => $requerido . '|integer|exists:pacientes_clinica_trabajo,id',
            'empresa_cliente_id' => $requerido . '|integer|exists:empresas_cliente,id',
            'puesto_trabajo_id' => 'nullable|integer|exists:puestos_trabajo,id',
            'tipo_evaluacion' => $requerido . '|in:ingreso,periodica,seguimiento,extraordinaria',
            'fecha_valoracion' => $requerido . '|date',
            'nombre' => $requerido . '|string|max:255',
            'aptitud' => $requerido . '|in:Apto,Apto con restricciones,No apto',

            // Predicho y LLN por parámetro
            'fvc_pred' => $numero, 'fvc_lln' => $numero,
            'fev1_pred' => $numero, 'fev1_lln' => $numero,
            'fev1_fvc_pred' => $numero, 'fev1_fvc_lln' => $numero,
            'fef25_75_pred' => $numero, 'fef25_75_lln' => $numero,
            'pef_pred' => $numero, 'pef_lln' => $numero,
            'fivc_pred' => $numero, 'fivc_lln' => $numero,

            // Porcentajes predichos
            'fev1_fvc_porcentaje_predicho' => 'nullable|integer|min:0',
            'fivc_porcentaje_predicho' => 'nullable|integer|min:0',

            // Puntuación Z (solo cuando el informe la trae)
            'fvc_z' => $numero, 'fev1_z' => $numero,
            'fev1_fvc_z' => $numero, 'fef25_75_z' => $numero,
            'pef_z' => $numero, 'fivc_z' => $numero,

            // Calidad
            'calidad_sesion' => $calidad,
            'calidad_fev1' => $calidad,
            'calidad_fvc' => $calidad,

            // Maniobras (JSON)
            'maniobras' => 'nullable|array',
            'maniobras.*.numero' => 'nullable|integer',
        ];
    }

    /**
     * Identifica el tipo de archivo por sus primeros bytes (firma).
     * Devuelve 'pdf', 'excel' o null si no es un formato soportado.
     */
    private function detectarTipoArchivo(string $ruta): ?string
    {
        $manejador = @fopen($ruta, 'rb');
        if ($manejador === false) {
            return null;
        }

        $cabecera = fread($manejador, 8);
        fclose($manejador);

        return match (true) {
            str_starts_with($cabecera, '%PDF') => 'pdf',
            str_starts_with($cabecera, "\xD0\xCF\x11\xE0") => 'excel', // .xls (formato binario antiguo)
            str_starts_with($cabecera, "PK\x03\x04") => 'excel',       // .xlsx (contenedor ZIP)
            default => null,
        };
    }
}