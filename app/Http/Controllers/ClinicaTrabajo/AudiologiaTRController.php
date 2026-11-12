<?php

namespace App\Http\Controllers\ClinicaTrabajo;

use App\Http\Controllers\Controller;
use App\Models\ClinicaTrabajo\FichaAudiologiaTR;
use App\Models\ClinicaTrabajo\PuestoTrabajo;
use App\Models\Medico;
use App\Models\Empresa;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ClinicaTrabajo\PacientesClinicaTrabajo;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\ClinicaTrabajo\IAServiceAudiologiaTR;

class AudiologiaTRController extends Controller
{
    /** Aptitudes válidas para el select de la hoja 4 */
    private const APTITUDES = ['Apto', 'Apto con restricciones', 'No apto'];

    /** Relaciones que se cargan en las respuestas */
    private const RELACIONES = ['paciente', 'empresa', 'puesto', 'medico'];

    // =========================================================
    // RESOURCE: index, create, store, show, edit, update, destroy
    // =========================================================

    /**
     * GET /clinica/audiologia/tr
     * Listar fichas TR (API con paginación)
     */
    public function index(Request $request): JsonResponse
    {
        $fichas = FichaAudiologiaTR::query()
            ->when($request->filled('paciente_id'), fn ($q) => $q->where('paciente_id', $request->input('paciente_id')))
            ->when($request->filled('filtro'), function ($q) use ($request) {
                $filtro = $request->input('filtro');
                $q->where('folio', 'like', "%{$filtro}%")
                  ->orWhereHas('paciente', fn ($q2) => $q2->where('nombre', 'like', "%{$filtro}%"))
                  ->orWhereHas('empresa', fn ($q2) => $q2->where('razon_social', 'like', "%{$filtro}%"));
            })
            ->with(self::RELACIONES)
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($fichas);
    }

    /**
     * GET /clinica/audiologia/tr/create
     * Devuelve el folio SUGERIDO para el formulario nuevo.
     * (El folio definitivo se genera en store con lockForUpdate)
     */
    public function create(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'folio' => $this->siguienteFolio(),
        ]);
    }

    /**
     * POST /clinica/audiologia/tr
     * Guardar nueva ficha TR con folio TR-AAAA-NNNN
     */
    public function store(Request $request): JsonResponse
    {
        $medico = $this->medicoAutenticado($request);
        if (!$medico) {
            return $this->sinMedico();
        }

        $validated = $request->validate([
            'paciente_id' => ['required', 'integer', 'exists:pacientes_clinica_trabajo,id'],
            'empresa_cliente_id' => ['required', 'integer', 'exists:empresas_cliente,id'],
            'puesto_trabajo_id' => ['required', 'integer', 'exists:puestos_trabajo,id'],
            'hoja4.aptitud' => ['required', Rule::in(self::APTITUDES)],
        ]);

        if (!$this->puestoPerteneceAEmpresa($validated['puesto_trabajo_id'], $validated['empresa_cliente_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'El puesto no pertenece a la empresa indicada.',
            ], 422);
        }

        $payload = $this->prepararPayload($request->all(), $validated['paciente_id'], null);

        $columnas = array_merge($this->columnasDesdeHojas($payload), [
            'paciente_id' => $validated['paciente_id'],
            'empresa_cliente_id' => $validated['empresa_cliente_id'],
            'puesto_trabajo_id' => $validated['puesto_trabajo_id'],
        ]);

        $ficha = $this->crearConFolio($columnas, $payload, $medico->id);

        return response()->json([
            'success' => true,
            'message' => 'Examen TR guardado correctamente',
            'folio' => $ficha->folio,
            'id' => $ficha->id,
        ], 201);
    }

    /**
     * GET /clinica/audiologia/tr/{id}
     * Mostrar una ficha TR
     */
    public function show(string $id): JsonResponse
    {
        $ficha = FichaAudiologiaTR::with(self::RELACIONES)->findOrFail($id);
        $data = $ficha->toArray();
        $data['datos'] = json_decode($ficha->datos_json, true);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * GET /clinica/audiologia/tr/{id}/edit
     * Mismos datos que show, para cargar el formulario de edición
     */
    public function edit(string $id): JsonResponse
    {
        return $this->show($id);
    }

    /**
     * PUT|PATCH /clinica/audiologia/tr/{id}
     * Actualizar ficha TR (preserva folio y médico original)
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $ficha = FichaAudiologiaTR::findOrFail($id);

        $request->validate([
            'hoja4.aptitud' => ['required', Rule::in(self::APTITUDES)],
        ]);

        $payload = $this->prepararPayload($request->all(), $ficha->paciente_id, $ficha->id);
        $anterior = json_decode($ficha->datos_json, true) ?: [];

        $ficha->fill(array_merge(
            $this->columnasDesdeHojas($payload),
            array_intersect_key($payload, array_flip(['paciente_id', 'empresa_cliente_id', 'puesto_trabajo_id']))
        ));

        // El folio y el médico que creó la ficha NO cambian al actualizar
        $ficha->datos_json = json_encode(array_merge($payload, [
            'folio' => $ficha->folio,
            'medico_id' => $anterior['medico_id'] ?? null,
            'tipo_evaluacion' => 'examen_tr_2026',
        ]));

        $ficha->save();

        return response()->json([
            'success' => true,
            'message' => 'Examen TR actualizado correctamente',
        ]);
    }

    /**
     * DELETE /clinica/audiologia/tr/{id}
     * Eliminar ficha TR
     */
    public function destroy(string $id): JsonResponse
    {
        $ficha = FichaAudiologiaTR::findOrFail($id);
        $ficha->delete();

        return response()->json([
            'success' => true,
            'message' => 'Examen TR eliminado correctamente',
        ]);
    }

    // =========================================================
    // MÉTODOS ADICIONALES (fuera del resource)
    // =========================================================

    /**
     * GET /TrAudiologia-imprimir/{id}
     * Exportar ficha TR a PDF
     */
    public function imprimir(string $id)
    {
        $ficha = FichaAudiologiaTR::with(self::RELACIONES)->findOrFail($id);
        $datos = json_decode($ficha->datos_json ?? '{}', true) ?: [];

        $medico = Medico::with(['usuario', 'configuraciones.ubicacion'])
            ->where('user_id', $ficha->user_id)
            ->first();

        $consultorio = $this->datosConsultorio($medico);

        $pdf = Pdf::loadView('pdf.ClinicaTrabajo.Audiologia.reporteaudiologiatr', [
            'ficha' => $ficha,
            'datos' => $datos,
            'medico' => $medico?->usuario,
            'cedula' => $medico?->cedula_profesional,
            'ubicacion' => $consultorio['ubicacion'],
            'logoPath' => $consultorio['logoPath'],
        ])->setPaper('letter');

        return $pdf->stream('examen-tr-' . $ficha->folio . '.pdf');
    }

    /**
     * POST /TrAudiologia-consultarIA
     * Consultar IA para la ficha TR
     */
    public function consultarIA(Request $request, IAServiceAudiologiaTR $iaService): JsonResponse
    {
        $medico = $this->medicoAutenticado($request);
        if (!$medico) {
            return $this->sinMedico();
        }

        $validated = $request->validate([
            'mensaje' => 'required_if:modo,chat|nullable|string|max:4000',
            'paciente_id' => 'nullable|integer|exists:pacientes_clinica_trabajo,id',
            'contexto' => 'nullable|array',
            'historial' => 'nullable|array',
            'modo' => 'nullable|in:chat,recomendar',
        ]);

        $modo = $validated['modo'] ?? 'chat';
        $paciente = isset($validated['paciente_id'])
            ? PacientesClinicaTrabajo::find($validated['paciente_id'])
            : null;

        $resultado = $modo === 'recomendar'
            ? $iaService->recomendar($validated['contexto'] ?? [], $paciente)
            : $iaService->consultar(
                $validated['mensaje'],
                $validated['contexto'] ?? [],
                $validated['historial'] ?? [],
                $paciente,
            );

        return response()->json([
            'success' => true,
            'respuesta' => $resultado['respuesta'],
            'campos_completados' => $resultado['campos_completados'],
            'advertencias' => $resultado['advertencias'],
        ]);
    }

    // =========================================================
    // MÉTODOS PRIVADOS — GENERACIÓN DE FOLIO
    // =========================================================

    /**
     * Genera el siguiente folio TR-AAAA-NNNN.
     * Con $bloquear = true debe llamarse dentro de una transacción.
     */
    private function siguienteFolio(bool $bloquear = false): string
    {
        $prefijo = 'TR-' . now()->year . '-';
        $consulta = FichaAudiologiaTR::where('folio', 'like', $prefijo . '%')
            ->orderByDesc('folio');

        if ($bloquear) {
            $consulta->lockForUpdate();
        }

        $ultimo = $consulta->value('folio');
        $siguiente = $ultimo ? ((int) substr($ultimo, strlen($prefijo))) + 1 : 1;

        return $prefijo . str_pad((string) $siguiente, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Crea la ficha con folio TR-AAAA-NNNN.
     * Si dos guardados chocan en el folio (índice único, error 1062), reintenta hasta 3 veces.
     */
    private function crearConFolio(array $columnas, array $payload, int $medicoId): FichaAudiologiaTR
    {
        for ($intento = 1; ; $intento++) {
            try {
                return DB::transaction(function () use ($columnas, $payload, $medicoId) {
                    $folio = $this->siguienteFolio(true);

                    $ficha = new FichaAudiologiaTR(array_merge($columnas, [
                        'folio' => $folio,
                        'user_id' => Auth::id(),
                    ]));

                    $ficha->datos_json = json_encode(array_merge($payload, [
                        'folio' => $folio,
                        'medico_id' => $medicoId,
                        'tipo_evaluacion' => 'examen_tr_2026',
                    ]));

                    $ficha->save();
                    return $ficha;
                });
            } catch (QueryException $e) {
                if (($e->errorInfo[1] ?? null) !== 1062 || $intento >= 3) {
                    throw $e;
                }
            }
        }
    }

    // =========================================================
    // MÉTODOS PRIVADOS — UTILIDADES
    // =========================================================

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

    private function puestoPerteneceAEmpresa(int $puestoId, int $empresaId): bool
    {
        return PuestoTrabajo::where('id', $puestoId)
            ->where('empresa_cliente_id', $empresaId)
            ->exists();
    }

    /**
     * Pasa las hojas a columnas de la tabla. El JSON completo sigue en datos_json.
     */
    private function columnasDesdeHojas(array $payload): array
    {
        $h1 = $payload['hoja1'] ?? [];
        $h3 = $payload['hoja3'] ?? [];
        $h4 = $payload['hoja4'] ?? [];

        // Extraer riesgos seleccionados de hoja1
        $riesgosSeleccionados = [];
        if (isset($h1['tipo_trabajo_riesgo']) && is_array($h1['tipo_trabajo_riesgo'])) {
            foreach ($h1['tipo_trabajo_riesgo'] as $clave => $activo) {
                if ($activo) {
                    $riesgosSeleccionados[] = $clave;
                }
            }
        }

        return [
            'tipo_trabajo_riesgo' => implode(', ', $riesgosSeleccionados),
            'riesgos' => $riesgosSeleccionados,
            'antecedentes_salud' => $payload['hoja2'] ?? [],
            'peso' => $h3['peso_kg'] ?? null,
            'estatura' => $h3['estatura_m'] ?? null,
            'imc' => $h3['imc'] ?? null,
            'presion_arterial' => ($h3['presion_sistolica'] ?? '') . '/' . ($h3['presion_diastolica'] ?? ''),
            'frecuencia_cardiaca' => $h3['frecuencia_cardiaca'] ?? null,
            'exploracion_fisica' => $h3,
            'ecg' => $h4['ecg']['resultado'] ?? null,
            'rx_torax' => $h4['rx_torax']['resultado'] ?? null,
            'evaluacion_visual' => $h4['evaluacion_visual'] ?? [],
            'audiometria' => $h4['audiometria'] ?? [],
            'espirometria' => $h4['espirometria'] ?? [],
            'diagnosticos' => $h4['diagnosticos'] ?? [],
            'enfermedad_profesional' => $h4['enfermedad_profesional'] ?? false,
            'diagnostico_principal' => $h4['diagnosticos'][0] ?? null,
            'aptitud' => $h4['aptitud'] ?? '',
            'observaciones' => $h4['observaciones'] ?? null,
            'anos_empresa' => $h1['antiguedad_empresa_anos'] ?? null,
            'meses_empresa' => $h1['antiguedad_empresa_meses'] ?? null,
            'anos_puesto' => $h1['antiguedad_puesto_anos'] ?? null,
            'meses_puesto' => $h1['antiguedad_puesto_meses'] ?? null,
        ];
    }

    /**
     * Prepara el payload antes de guardar (ej: calcular IMC)
     */
    private function prepararPayload(array $payload, ?int $pacienteId, ?int $fichaId): array
    {
        $h3 = $payload['hoja3'] ?? [];
        if (isset($h3['peso_kg']) && isset($h3['estatura_m']) && $h3['estatura_m'] > 0) {
            $h3['imc'] = round($h3['peso_kg'] / ($h3['estatura_m'] * $h3['estatura_m']), 2);
        }
        $payload['hoja3'] = $h3;

        return $payload;
    }

    /**
     * Datos del consultorio: sucursal del médico, o empresa si no hay sucursal, y logo.
     */
    private function datosConsultorio(?Medico $medico): array
    {
        $ubicacion = null;
        if ($medico) {
            $medico->load('configuraciones.ubicacion');
            $ubicacion = $medico->configuraciones->first()?->ubicacion;
        }

        if (!$ubicacion) {
            $empresa = Empresa::first();
            if ($empresa) {
                $ubicacion = (object) [
                    'nombre' => $empresa->nombre_empresa,
                    'direccion' => $empresa->direccion,
                    'telefono' => $empresa->telefono,
                    'imagen' => $empresa->logo_url,
                ];
            }
        }

        $logoRelativo = Empresa::first()?->logo_url ?: $ubicacion?->imagen;
        $logoPath = null;
        if (!empty($logoRelativo)) {
            $valor = ltrim($logoRelativo, '/');
            $candidata = str_starts_with($valor, 'personalisarperfil/')
                ? public_path($valor)
                : public_path('personalisarperfil/' . $valor);
            $logoPath = file_exists($candidata) ? $candidata : null;
        }

        if (!$logoPath) {
            $generico = public_path('images/logo.png');
            $logoPath = file_exists($generico) ? $generico : null;
        }

        return ['ubicacion' => $ubicacion, 'logoPath' => $logoPath];
    }
}