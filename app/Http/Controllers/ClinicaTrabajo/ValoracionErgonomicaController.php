<?php

namespace App\Http\Controllers\ClinicaTrabajo;

use App\Http\Controllers\Controller;
use App\Models\ClinicaTrabajo\PuestoTrabajo;
use App\Models\ClinicaTrabajo\ValoracionErgonomica;
use App\Models\Medico;
use App\Services\ClinicaTrabajo\IAServiceErgonomia;
use App\Models\ClinicaTrabajo\ErgonomiaIaMensaje;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ValoracionErgonomicaController extends Controller
{
    /** Relaciones que se cargan en las respuestas. */
    private const RELACIONES = ['paciente', 'empresa', 'puesto', 'medico'];

    private const TIPOS = ['ingreso', 'periodica', 'seguimiento', 'extraordinaria', 'reingreso'];
    private const INSTRUMENTOS = ['RULA', 'REBA', 'NIOSH', 'OTRO'];
    private const APTITUDES = ['Apto', 'Apto con restricciones', 'No apto'];
    private const NIVELES_RULA = ['negligible', 'bajo', 'moderado', 'alto'];
    private const NIVELES_REBA = ['aceptable', 'investigar', 'cambios_pronto', 'cambios_inmediatos'];

    /**
     * GET /ErgonomiaValoracion
     * Lista paginada con filtros opcionales.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ValoracionErgonomica::query();

        if ($request->filled('empresa_id')) {
            $query->where('empresa_cliente_id', $request->input('empresa_id'));
        }
        if ($request->filled('paciente_id')) {
            $query->where('paciente_id', $request->input('paciente_id'));
        }
        if ($request->filled('tipo')) {
            $query->where('tipo_evaluacion', $request->input('tipo'));
        }
        if ($request->filled('aptitud')) {
            $query->where('aptitud', $request->input('aptitud'));
        }
        if ($request->filled('instrumento')) {
            $query->where('instrumento_utilizado', $request->input('instrumento'));
        }
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $query->whereBetween('fecha_valoracion', [
                $request->input('fecha_desde'),
                $request->input('fecha_hasta'),
            ]);
        }

        $valoraciones = $query
            ->with(self::RELACIONES)
            ->orderByDesc('fecha_valoracion')
            ->orderByDesc('id')
            ->paginate(15);

        return response()->json($valoraciones);
    }

    /**
     * GET /ErgonomiaValoracion/create
     * Folio sugerido para el formulario nuevo (sin reservarlo).
     */
    public function create(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'folio' => $this->siguienteFolio(),
        ]);
    }

    /**
     * POST /ErgonomiaValoracion
     * El médico se toma del usuario autenticado (medicos.user_id) y el folio lo genera el backend.
     * datos_json guarda el formulario completo.
     */
    public function store(Request $request): JsonResponse
    {
        $medico = $this->medicoAutenticado($request);
        if (!$medico) {
            return $this->sinMedico();
        }

        $validated = $request->validate($this->reglas(true));

        if (!$this->puestoPerteneceAEmpresa($validated['puesto_trabajo_id'], $validated['empresa_cliente_id'])) {
            return $this->puestoInvalido();
        }

        if ($request->boolean('usa_ia') && !$request->boolean('confirma_dictamen_ia')) {
            return response()->json([
                'message' => 'Confirma que revisaste el dictamen sugerido por la IA.',
            ], 422);
        }

        $valoracion = $this->crearConFolio($validated, [
            'medico_id' => $medico->id,
            'user_id' => $request->user()->id,
            'datos_json' => json_encode($request->all(), JSON_UNESCAPED_UNICODE),
        ]);

        if ($request->boolean('usa_ia') && $request->filled('session_uuid')) {
            ErgonomiaIaMensaje::where('session_uuid', $request->input('session_uuid'))
                ->update([
                    'valoracion_id' => $valoracion->id,
                    'aceptado_medico' => true,
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Valoración ergonómica creada exitosamente',
            'id' => $valoracion->id,
            'folio' => $valoracion->folio,
            'data' => $valoracion->load(self::RELACIONES),
        ], 201);
    }

    /**
     * GET /ErgonomiaValoracion/{id}
     */
    public function show(string $id): JsonResponse
    {
        $valoracion = ValoracionErgonomica::with(self::RELACIONES)->findOrFail($id);

        $data = $valoracion->toArray();
        $data['datos'] = json_decode($valoracion->datos_json, true);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * PUT|PATCH /ErgonomiaValoracion/{id}
     * No cambia el folio ni el médico que la creó.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $valoracion = ValoracionErgonomica::findOrFail($id);
        $validated = $request->validate($this->reglas(false));

        $empresaId = $validated['empresa_cliente_id'] ?? $valoracion->empresa_cliente_id;
        $puestoId = $validated['puesto_trabajo_id'] ?? $valoracion->puesto_trabajo_id;

        if (!$this->puestoPerteneceAEmpresa($puestoId, $empresaId)) {
            return $this->puestoInvalido();
        }

        $validated['datos_json'] = json_encode($request->all(), JSON_UNESCAPED_UNICODE);
        $valoracion->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Valoración ergonómica actualizada',
            'id' => $valoracion->id,
            'data' => $valoracion->fresh()->load(self::RELACIONES),
        ]);
    }

    /**
     * DELETE /ErgonomiaValoracion/{id}
     * Borrado físico (la tabla no tiene deleted_at).
     */
    public function destroy(string $id): JsonResponse
    {
        $valoracion = ValoracionErgonomica::findOrFail($id);
        $valoracion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Valoración ergonómica eliminada',
        ]);
    }

    /**
     * GET /ErgonomiaValoracion/{id}/imprimir
     * Reporte PDF con los datos del formulario guardados en datos_json.
     */
    public function imprimir(string $id)
    {
        $valoracion = ValoracionErgonomica::with(self::RELACIONES)->findOrFail($id);

        $medico = $valoracion->medico;
        $medico?->load('configuraciones.ubicacion');

        $pdf = Pdf::loadView('pdf.ClinicaTrabajo.Ergonomia.reporteergonomia', [
            'v' => $valoracion,
            'datos' => json_decode($valoracion->datos_json, true) ?? [],
            'medico' => $medico?->usuario,
            'cedula' => $medico?->cedula_profesional,
            'ubicacion' => $medico?->configuraciones->first()?->ubicacion,
            'logoPath' => $this->logoPath(),
        ])->setPaper('letter');

        return $pdf->stream('ergonomia-' . $valoracion->folio . '.pdf');
    }

    // ---------------------------------------------------------------
    // Validación
    // ---------------------------------------------------------------
    
        /**
     * POST /ergonomia-consultarIA
     * modo "chat": conversación que propone campos del formulario.
     * modo "recomendar": propuesta de aptitud y examen médico, guardada en ergonomia_ia_mensajes.
     */
    public function consultarIA(Request $request, IAServiceErgonomia $ia): JsonResponse
    {
        $medico = $this->medicoAutenticado($request);
        if (!$medico) {
            return $this->sinMedico();
        }

        $validated = $request->validate([
            'modo' => 'nullable|in:chat,recomendar',
            'mensaje' => 'required_if:modo,chat|nullable|string|max:4000',
            'contexto' => 'nullable|array',
            'historial' => 'nullable|array',
            'valoracion_id' => 'nullable|integer|exists:valoraciones_ergonomicas,id',
            'session_uuid' => 'nullable|string|max:100',
        ]);

        $modo = $validated['modo'] ?? 'chat';
        $contexto = $validated['contexto'] ?? [];

        if ($modo === 'recomendar') {
            // El formulario trae las columnas tipadas en la raíz; por eso se pasa igual en ambos argumentos.
            $resultado = $ia->proponerDictamen($contexto, $contexto);
            $sessionUuid = $validated['session_uuid'] ?? (string) Str::uuid();

            $mensaje = ErgonomiaIaMensaje::create([
                'valoracion_id' => $validated['valoracion_id'] ?? null,
                'session_uuid' => $sessionUuid,
                'medico_id' => $medico->id,
                'tipo_usuario' => 'ia',
                'mensaje' => $resultado['respuesta'],
                'campos_propuestos' => $resultado['campos_completados'],
                'aceptado_medico' => false,
            ]);

            return response()->json([
                'respuesta' => $resultado['respuesta'],
                'campos_completados' => $resultado['campos_completados'],
                'advertencias' => $resultado['advertencias'],
                'session_uuid' => $sessionUuid,
                'mensaje_id' => $mensaje->id,
            ]);
        }

        $resultado = $ia->consultar($validated['mensaje'], $contexto, $validated['historial'] ?? []);

        return response()->json([
            'success' => true,
            'respuesta' => $resultado['respuesta'],
            'campos_completados' => $resultado['campos_completados'],
            'advertencias' => $resultado['advertencias'],
        ]);
    }

    /**
     * Reglas para crear (store) y actualizar (update).
     * Los campos coinciden con las columnas de valoraciones_ergonomicas.
     */
    private function reglas(bool $esCreacion): array
    {
        $requerido = $esCreacion ? 'required' : 'sometimes|required';
        $texto = 'nullable|string';
        $numero = 'nullable|numeric|between:0,999.99';
        $booleano = 'sometimes|boolean';

        return [
            // Obligatorios en la tabla
            'paciente_id' => $requerido . '|integer|exists:pacientes_clinica_trabajo,id',
            'empresa_cliente_id' => $requerido . '|integer|exists:empresas_cliente,id',
            'puesto_trabajo_id' => $requerido . '|integer|exists:puestos_trabajo,id',
            'tipo_evaluacion' => $requerido . '|in:' . implode(',', self::TIPOS),
            'fecha_valoracion' => $requerido . '|date',
            'instrumento_utilizado' => $requerido . '|in:' . implode(',', self::INSTRUMENTOS),
            'aptitud' => $requerido . '|in:' . implode(',', self::APTITUDES),

            // Datos generales
            'hora_valoracion' => 'nullable|date_format:H:i',
            'justificacion_instrumento' => $texto,
            'fecha_evaluacion_puesto' => 'nullable|date',

            // RULA
            'rula_puntuacion' => 'nullable|integer|between:1,7',
            'rula_nivel_riesgo' => 'nullable|in:' . implode(',', self::NIVELES_RULA),
            'rula_areas_evaluadas' => $texto,

            // REBA
            'reba_puntuacion' => 'nullable|integer|between:1,15',
            'reba_nivel_accion' => 'nullable|in:' . implode(',', self::NIVELES_REBA),
            'reba_areas_evaluadas' => $texto,

            // NIOSH
            'niosh_indice_levantamiento' => $numero,
            'niosh_es_seguro' => 'nullable|boolean',
            'niosh_peso_real_kg' => $numero,
            'niosh_peso_recomendado_kg' => $numero,
            'niosh_variables_evaluadas' => $texto,

            // Hallazgos
            'posturas_forzadas' => $booleano,
            'movimientos_repetitivos' => $booleano,
            'manipulacion_cargas' => $booleano,
            'tiempo_permanencia_posicion' => 'nullable|string|max:100',
            'frecuencia_movimiento' => 'nullable|string|max:100',
            'carga_maxima_manipulada_kg' => $numero,

            // Examen médico
            'examen_medico_realizado' => $booleano,
            'exploracion_osteomuscular_hallazgos' => $texto,
            'diagnostico_relacionado' => $texto,
            'restricciones_laborales' => $texto,
            'derivacion_especialista' => $texto,
            'acciones_recomendadas' => $texto,
            'medidas_control_prioritarias' => $texto,

            // Dictamen y seguimiento
            'restricciones_descripcion' => $texto,
            'seguimiento_requerido' => $booleano,
            'plazo_proximo_seguimiento' => 'nullable|date',

            // Notas
            'notas_ergonomo' => $texto,
            'condiciones_trabajo_descripcion' => $texto,
            'recomendaciones_puesto' => $texto,
        ];
    }

    // ---------------------------------------------------------------
    // Auxiliares
    // ---------------------------------------------------------------

    /** Médico activo asociado al usuario autenticado (medicos.user_id -> users.id). */
    private function medicoAutenticado(Request $request): ?Medico
    {
        return Medico::where('user_id', $request->user()->id)
            ->where('activo', 1)
            ->first();
    }

    private function sinMedico(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Tu usuario no está asociado a un médico activo. Pide al administrador que lo vincule.',
        ], 403);
    }

    private function puestoInvalido(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'El puesto no pertenece a la empresa indicada.',
        ], 422);
    }

    private function puestoPerteneceAEmpresa(?int $puestoId, ?int $empresaId): bool
    {
        if (!$puestoId || !$empresaId) {
            return true;
        }

        return PuestoTrabajo::where('id', $puestoId)
            ->where('empresa_cliente_id', $empresaId)
            ->exists();
    }

    /**
     * Crea la valoración con folio ERG-AAAA-NNNN. Si dos guardados chocan
     * en el mismo folio (índice único), reintenta hasta 3 veces.
     */

    /** Siguiente folio del año. Debe llamarse dentro de una transacción. */
    private function crearConFolio(array $validated, array $extra): ValoracionErgonomica
    {
        return DB::transaction(function () use ($validated, $extra) {
            // Folio temporal único; se reemplaza en cuanto se conoce el ID
            $valoracion = ValoracionErgonomica::create(array_merge($validated, $extra, [
                'folio' => 'TMP' . Str::random(8),
            ]));

            $valoracion->folio = $this->formatearFolio($valoracion->id);
            $valoracion->save();

            return $valoracion;
        });
    }

    private function formatearFolio(int $id): string
    {
        return 'ERG-' . now()->year . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
    }

    // Solo para mostrar el folio sugerido en el formulario nuevo; el definitivo se asigna al guardar
    private function siguienteFolio(): string
    {
        $siguienteId = DB::selectOne(
            "SELECT AUTO_INCREMENT AS id FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'valoraciones_ergonomicas'"
        )->id;

        return $this->formatearFolio((int) $siguienteId);
    }

    private function logoPath(): ?string
    {
        $logo = public_path('images/logo.png');

        return file_exists($logo) ? $logo : null;
    }
}