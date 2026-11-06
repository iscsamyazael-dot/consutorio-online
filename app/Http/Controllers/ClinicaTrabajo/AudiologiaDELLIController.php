<?php

namespace App\Http\Controllers\ClinicaTrabajo;

use App\Http\Controllers\Controller;
use App\Services\ClinicaTrabajo\GraficasAudiologia;
use App\Models\ClinicaTrabajo\FichaAudiologiaDelli;
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
use App\Services\ClinicaTrabajo\IAServiceAudiologiaDelli;


class AudiologiaDELLIController extends Controller
{
    /** PENDIENTE: confirmar que coincide con las opciones del select de la hoja 4. */
    private const APTITUDES = ['Apto', 'Apto con restricciones', 'No apto'];

    /** Campos de la hoja 3 que van a antecedentes_medicos (el JSON completo queda en datos_json). */
    private const CAMPOS_ANTECEDENTES = [
        'sarampion', 'hipertension', 'paperas', 'meningitis', 'diabetes', 'covid19',
        'resfriado', 'otitis', 'alergias', 'uso_aparat', 'ninguna_otitis',
        'historia_familiar_perdida', 'parentesco',
    ];

    /** Campos de la hoja 3 que van a sintomas_auditivos (el JSON completo queda en datos_json). */
    private const CAMPOS_SINTOMAS = [
        'acufenos', 'algiacusia', 'autofonia_der', 'autofonia_izq', 'fatiga', 'sensibilidad',
        'vertigo', 'otalgia_der', 'otalgia_izq', 'otorrea_der', 'otorrea_izq', 'prurito_der',
        'prurito_izq', 'otodinia_der', 'otodinia_izq', 'otorragia_der', 'otorragia_izq',
        'paracusias_willis', 'paracusias_weber', 'paracusias_negado', 'paracusias_hipo',
        'paracusias_hiper', 'congestion', 'descripcion_hipoacusia', 'tiempo_evolucion',
    ];

    /** Relaciones que se cargan en las respuestas (igual que espirometría). */
    private const RELACIONES = [
        'paciente', 'puestoTrabajo', 'medico'
    ];

    /** GET /clinica/audiologia/delli */
    public function index(Request $request): JsonResponse
    {
        $fichas = FichaAudiologiaDelli::query()
            ->when($request->filled('paciente_id'), fn ($q) => $q->where('paciente_id', $request->input('paciente_id')))
            ->when($request->filled('filtro'), fn ($q) => $q->where('folio', 'like', '%' . $request->input('filtro') . '%'))
            ->with(['paciente', 'empresa', 'puesto', 'medico']) // ✅ CARGA LAS RELACIONES
            ->orderByDesc('created_at')
            ->paginate(15); // ✅ PAGINACIÓN (igual que espirometría)

        // Laravel formatea esto automáticamente como: { current_page, data: [...], links: [...] }
        return response()->json($fichas);
    }

    /** GET /clinica/audiologia/delli/create: folio sugerido para el formulario nuevo. */
    public function create(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'folio' => $this->siguienteFolio(),
        ]);
    }

    /** POST /clinica/audiologia/delli */
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
            'message' => 'Ficha guardada correctamente',
            'folio' => $ficha->folio,
            'id' => $ficha->id,
        ], 201);
    }

    /** GET /clinica/audiologia/delli/{id} */
    public function show(string $id): JsonResponse
    {
        $ficha = FichaAudiologiaDelli::findOrFail($id);

        $data = $ficha->toArray();
        $data['datos'] = json_decode($ficha->datos_json, true);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /** GET /clinica/audiologia/delli/{id}/edit: mismos datos que show, para cargar el formulario. */
    public function edit(string $id): JsonResponse
    {
        return $this->show($id);
    }

    /** PUT|PATCH /clinica/audiologia/delli/{id} */
    public function update(Request $request, string $id): JsonResponse
    {
        $ficha = FichaAudiologiaDelli::findOrFail($id);

        $request->validate([
            'hoja4.aptitud' => ['required', Rule::in(self::APTITUDES)],
        ]);

        $payload = $this->prepararPayload($request->all(), $ficha->paciente_id, $ficha->id);
        $anterior = json_decode($ficha->datos_json, true) ?: [];

        $ficha->fill(array_merge(
            $this->columnasDesdeHojas($payload),
            array_intersect_key($payload, array_flip(['paciente_id', 'empresa_cliente_id', 'puesto_trabajo_id']))
        ));

        // El folio y el médico que creó la ficha no cambian al actualizar
        $ficha->datos_json = json_encode(array_merge($payload, [
            'folio' => $ficha->folio,
            'medico_id' => $anterior['medico_id'] ?? null,
            'tipo_evaluacion' => 'delli_2026',
        ]));

        $ficha->save();

        return response()->json([
            'success' => true,
            'message' => 'Ficha actualizada correctamente',
        ]);
    }

    /** DELETE /clinica/audiologia/delli/{id} */
    public function destroy(string $id): JsonResponse
    {
        $ficha = FichaAudiologiaDelli::findOrFail($id);
        $ficha->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ficha eliminada correctamente',
        ]);
    }

   
    /**
    * GET /DelliAudiologia-imprimir/{id}
    */
    public function imprimir(string $id, GraficasAudiologia $graficas)
    {
        $ficha = FichaAudiologiaDelli::findOrFail($id);

        $datos = json_decode($ficha->datos_json ?? '{}', true) ?: [];
        $audio = $ficha->audiometria ?? [];

        $umbrales = $this->umbralesAudiometria($audio);
        $columnas = $this->columnasTabla($umbrales);

        // La ficha guarda user_id, no medico_id
        $medico = Medico::with(['usuario', 'configuraciones.ubicacion'])
            ->where('user_id', $ficha->user_id)
            ->first();
        $consultorio = $this->datosConsultorio($medico);

        $pdf = Pdf::loadView('pdf.ClinicaTrabajo.Audiologia.reporteaudiologiahistoriaclinica', [
            'ficha' => $ficha,
            'datos' => $datos,
            'columnas' => $columnas,
            'tabla' => $this->filasTabla($umbrales, $columnas),
            'pta' => [
                'derecho' => $audio['pta_der'] ?? null,
                'izquierdo' => $audio['pta_izq'] ?? null,
            ],
            'graficas' => [
                'audiograma' => $graficas->audiograma($umbrales),
            ],
            'medico' => $medico?->usuario,
            'cedula' => $medico?->cedula_profesional,
            'ubicacion' => $consultorio['ubicacion'],
            'logoPath' => $consultorio['logoPath'],
        ])->setPaper('letter');

        return $pdf->stream('audiometria-' . $ficha->folio . '.pdf');
    }

    // ===== PRIVADOS =====

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
     * Pasa las hojas a columnas de consulta. El JSON completo sigue en datos_json.
     * PENDIENTE de unificación: epp_auditivo (hoja 2) y clasificacion (hoja 4, hoy una sola; debe ser por oído).
     */
    private function columnasDesdeHojas(array $payload): array
    {
        $h1 = $payload['hoja1'] ?? [];
        $h2 = $payload['hoja2'] ?? [];
        $h3 = $payload['hoja3'] ?? [];
        $h4 = $payload['hoja4'] ?? [];

        return [
            'anos_empresa' => $h1['antiguedad_empresa_anos'] ?? null,
            'meses_empresa' => $h1['antiguedad_empresa_meses'] ?? null,
            'anos_puesto' => $h1['antiguedad_puesto_anos'] ?? null,
            'meses_puesto' => $h1['antiguedad_puesto_meses'] ?? null,
            'riesgo_auditivo' => $h2['riesgo_auditivo'] ?? null,
            'epp_auditivo' => $h2['tipo_protector'] ?? null,
            'ototoxicos_quimicos' => $h2['agentes_ototoxicos'] ?? [],
            'antecedentes_medicos' => array_intersect_key($h3, array_flip(self::CAMPOS_ANTECEDENTES)),
            'sintomas_auditivos' => array_intersect_key($h3, array_flip(self::CAMPOS_SINTOMAS)),
            'audiometria' => $h4,
            'pta_promedio' => $h4['pta_promedio'] ?? null,
            'clasificacion' => $h4['clasificacion'] ?? null,
            'aptitud' => $h4['aptitud'] ?? '',
            'observaciones' => $h4['interpretacion'] ?? null,
        ];
    }

    /**
     * Crea la ficha con folio AUD-AAAA-NNNN. Si dos guardados chocan en el folio
     * (índice único, error 1062), reintenta hasta 3 veces.
     */
    private function crearConFolio(array $columnas, array $payload, int $medicoId): FichaAudiologiaDelli
    {
        for ($intento = 1; ; $intento++) {
            try {
                return DB::transaction(function () use ($columnas, $payload, $medicoId) {
                    $folio = $this->siguienteFolio(true);

                    $ficha = new FichaAudiologiaDelli(array_merge($columnas, [
                        'folio' => $folio,
                        'user_id' => Auth::id(),
                    ]));

                    $ficha->datos_json = json_encode(array_merge($payload, [
                        'folio' => $folio,
                        'medico_id' => $medicoId,
                        'tipo_evaluacion' => 'delli_2026',
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

        /** Frecuencias que entran al PTA, igual que en el componente. */
    private const FRECUENCIAS_PTA = ['500', '1000', '2000', '3000'];

    private const CLASIFICACIONES = ['NORMAL', 'HIPOACUSIA LEVE', 'HIPOACUSIA MODERADA', 'HIPOACUSIA SEVERA', 'HIPOACUSIA PROFUNDA'];

    private function prepararPayload(array $payload, ?int $pacienteId, ?int $fichaId): array
    {
        $h4 = $this->recalcularHoja4($payload['hoja4'] ?? []);
        $h4['alertas_sts'] = $this->calcularAlertasSts($h4, $pacienteId, $fichaId);

        // Regla dura al guardar: audiometría normal => Apto sin restricciones
        if (($h4['clasificacion'] ?? null) === 'NORMAL') {
            $h4['aptitud'] = 'Apto';
            $h4['restricciones'] = '';
            $h4['tipo_hipoacusia'] = 'Normal';
        }

        $payload['hoja4'] = $h4;

        return $payload;
    }

    /** Promedio de 500, 1000, 2000 y 3000 Hz por oído. Devuelve null si no hay datos. */
    private function promedioPta(array $h4, string $oido): ?int
    {
        $valores = [];

        foreach (self::FRECUENCIAS_PTA as $f) {
            $valor = $h4["audiometria_{$f}_{$oido}"] ?? null;
            if ($valor !== null && $valor !== '') {
                $valores[] = (float) $valor;
            }
        }

        return $valores ? (int) round(array_sum($valores) / count($valores)) : null;
    }

    private function clasificarPta(?int $pta): string
    {
        if ($pta === null) {
            return '';
        }

        return match (true) {
            $pta <= 20 => 'NORMAL',
            $pta <= 40 => 'HIPOACUSIA LEVE',
            $pta <= 60 => 'HIPOACUSIA MODERADA',
            $pta <= 90 => 'HIPOACUSIA SEVERA',
            default => 'HIPOACUSIA PROFUNDA',
        };
    }

    /** Recalcula PTA y clasificación por oído. La clasificación general es la peor de las dos. */
    private function recalcularHoja4(array $h4): array
    {
        $der = $this->promedioPta($h4, 'der');
        $izq = $this->promedioPta($h4, 'izq');

        $promedio = ($der !== null && $izq !== null)
            ? (int) round(($der + $izq) / 2)
            : ($der ?? $izq);

        $clasDer = $this->clasificarPta($der);
        $clasIzq = $this->clasificarPta($izq);

        $orden = array_flip(self::CLASIFICACIONES);
        $peor = '';
        foreach ([$clasDer, $clasIzq] as $clas) {
            if ($clas !== '' && ($peor === '' || $orden[$clas] > $orden[$peor])) {
                $peor = $clas;
            }
        }

        return array_merge($h4, [
            'pta_der' => $der ?? 0,
            'pta_izq' => $izq ?? 0,
            'pta_promedio' => $promedio ?? 0,
            'clasificacion_der' => $clasDer,
            'clasificacion_izq' => $clasIzq,
            'clasificacion' => $peor,
        ]);
    }

    /** Siguiente folio del año: AUD-AAAA-NNNN. Con $bloquear = true debe llamarse dentro de una transacción. */
    private function siguienteFolio(bool $bloquear = false): string
    {
        $prefijo = 'AUD-' . now()->year . '-';

        $consulta = FichaAudiologiaDelli::where('folio', 'like', $prefijo . '%')
            ->orderByDesc('folio');

        if ($bloquear) {
            $consulta->lockForUpdate();
        }

        $ultimo = $consulta->value('folio');

        $siguiente = $ultimo ? ((int) substr($ultimo, strlen($prefijo))) + 1 : 1;

        return $prefijo . str_pad((string) $siguiente, 4, '0', STR_PAD_LEFT);
    }

        /** POST /DelliAudiologia-consultarIA */
    public function consultarIA(Request $request, IAServiceAudiologiaDelli $iaService): JsonResponse
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

        /** STS de OSHA: cambio promedio ≥ 10 dB en 2000, 3000 y 4000 Hz frente a la audiometría inicial. */
    private function calcularAlertasSts(array $h4, ?int $pacienteId, ?int $fichaId): array
    {
        if (!$pacienteId) {
            return [];
        }

        $basal = $this->fichaBasal($pacienteId, $fichaId);
        if (!$basal) {
            return [];
        }

        $alertas = [];
        foreach (['der' => 'derecho', 'izq' => 'izquierdo'] as $sufijo => $nombre) {
            $cambio = $this->cambioPromedioAgudos($h4, $basal, $sufijo);
            if ($cambio !== null && $cambio >= 10) {
                $alertas[] = "STS en oído {$nombre}: cambio promedio de {$cambio} dB en 2000, 3000 y 4000 Hz frente a la audiometría inicial.";
            }
        }

        return $alertas;
    }

    /** Audiometría inicial del paciente: la primera ficha con tipo "Inicial", distinta de la actual. */
    private function fichaBasal(int $pacienteId, ?int $fichaId): ?array
    {
        $fichas = FichaAudiologiaDelli::where('paciente_id', $pacienteId)
            ->when($fichaId, fn ($q) => $q->where('id', '!=', $fichaId))
            ->orderBy('created_at')
            ->get();

        foreach ($fichas as $ficha) {
            $datos = json_decode($ficha->datos_json, true) ?: [];
            if (($datos['hoja4']['tipo_audiometria'] ?? null) === 'Inicial') {
                return $datos['hoja4'];
            }
        }

        return null;
    }

    private function promedioAgudos(array $h4, string $sufijo): ?float
    {
        $valores = [];

        foreach (['2000', '3000', '4000'] as $f) {
            $valor = $h4["audiometria_{$f}_{$sufijo}"] ?? null;
            if ($valor === null || $valor === '') {
                return null;
            }
            $valores[] = (float) $valor;
        }

        return array_sum($valores) / 3;
    }

    private function cambioPromedioAgudos(array $actual, array $basal, string $sufijo): ?float
    {
        $a = $this->promedioAgudos($actual, $sufijo);
        $b = $this->promedioAgudos($basal, $sufijo);

        return ($a === null || $b === null) ? null : round($a - $b, 1);
    }

  
   
    /**
    * Convierte el arreglo plano de `audiometria` al formato de GraficasAudiologia y de la tabla.
    * audiometria_{f}_{der|izq}  -> aérea (125 a 8000 Hz)
    * via_osea_{f}_{der|izq}     -> ósea (250 a 4000 Hz)
    * enmascarado_{f}_{der|izq}  -> enmascarado (250 a 4000 Hz)
    */
    private function umbralesAudiometria(array $audiometria): array
    {
        $umbrales = [];

        foreach (['der' => 'derecho', 'izq' => 'izquierdo'] as $sufijo => $oido) {
            $umbrales[$oido] = ['aerea' => [], 'osea' => [], 'enmascarado' => []];

            foreach ([125, 250, 500, 1000, 2000, 3000, 4000, 6000, 8000] as $f) {
                $umbrales[$oido]['aerea'][$f] = $audiometria["audiometria_{$f}_{$sufijo}"] ?? null;
            }
            foreach ([250, 500, 1000, 2000, 3000, 4000] as $f) {
                $umbrales[$oido]['osea'][$f] = $audiometria["via_osea_{$f}_{$sufijo}"] ?? null;
                $umbrales[$oido]['enmascarado'][$f] = $audiometria["enmascarado_{$f}_{$sufijo}"] ?? null;
            }
        }

        return $umbrales;
    }

    /** true si el arreglo tiene al menos un valor no vacío. */
    private function hayValores(array $valores): bool
    {
        foreach ($valores as $valor) {
            if ($valor !== null && $valor !== '') {
                return true;
            }
        }
        return false;
    }

    /**
    * Columnas de la tabla. Aérea siempre; ósea y enmascarado solo si hay datos en algún oído.
    * El orden agrupa por oído: Derecho (aérea, ósea, enmascarado) e Izquierdo (lo mismo).
    */
    private function columnasTabla(array $umbrales): array
    {
        $mostrar = [
            'aerea' => true,
            'osea' => $this->hayValores(array_merge($umbrales['derecho']['osea'], $umbrales['izquierdo']['osea'])),
            'enmascarado' => $this->hayValores(array_merge($umbrales['derecho']['enmascarado'], $umbrales['izquierdo']['enmascarado'])),
        ];

        $vias = [
            'aerea' => ['etiqueta' => 'aérea', 'simbolo' => ['derecho' => 'O', 'izquierdo' => 'X']],
            'osea' => ['etiqueta' => 'ósea', 'simbolo' => ['derecho' => '<', 'izquierdo' => '>']],
            'enmascarado' => ['etiqueta' => 'enmascarado', 'simbolo' => ['derecho' => 'Δ', 'izquierdo' => '□']],
        ];

        $columnas = [];
        foreach (['derecho' => 'Derecho', 'izquierdo' => 'Izquierdo'] as $oido => $nombre) {
            foreach ($vias as $via => $config) {
                if (!$mostrar[$via]) {
                    continue;
                }
                $columnas[] = [
                    'clave' => $oido . '_' . $via,
                    'oido' => $oido,
                    'titulo' => $nombre . ' ' . $config['etiqueta'] . ' (' . $config['simbolo'][$oido] . ')',
                ];
            }
        }

        return $columnas;
    }

    /** Filas de la tabla de umbrales, una por frecuencia. */
    private function filasTabla(array $umbrales, array $columnas): array
    {
        $filas = [];
        foreach ([125, 250, 500, 1000, 2000, 3000, 4000, 6000, 8000] as $frecuencia) {
            $valores = [];
            foreach ($columnas as $columna) {
                [$oido, $via] = explode('_', $columna['clave']);
                $valores[$columna['clave']] = $umbrales[$oido][$via][$frecuencia] ?? null;
            }
            $filas[] = ['frecuencia' => $frecuencia, 'valores' => $valores];
        }
        return $filas;
    }

    /**
     * Datos del consultorio: sucursal del médico, o empresa si no hay sucursal,
     * y logo (igual que espirometría).
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