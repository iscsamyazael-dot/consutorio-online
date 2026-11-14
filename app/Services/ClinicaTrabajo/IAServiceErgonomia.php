<?php

namespace App\Services\ClinicaTrabajo;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IAServiceErgonomia
{
    private const MODELO = 'gemini-3.5-flash-lite';
    private const MAX_CARACTERES_DATOS = 15000;
    private const MAX_MENSAJES_HISTORIAL = 10;

    /*
     * POLÍTICAS CONFIGURABLES: cambia el valor aquí, sin tocar la lógica.
     *
     * POLITICA_APTITUD:
     *   'A' conservadora: nunca propone No apto. Riesgo alto o hallazgo marcado => Apto con restricciones.
     *   'B' escalonada:   riesgo alto Y hallazgo marcado => No apto (sugerido).
     *                     Uno de los dos => Apto con restricciones.
     *   'C' solo instrumentos: los hallazgos no cambian la aptitud.
     *   'D' solo hallazgos:    el instrumento no cambia la aptitud.
     *
     * NIOSH_CRITERIO:
     *   'es_seguro' usa el checkbox niosh_es_seguro.
     *   'peso'      compara peso real contra recomendado; si faltan, usa el checkbox.
     *
     * EXAMEN_NO_REALIZADO:
     *   'advertencia' propone la aptitud con una advertencia.
     *   'no_proponer' no propone aptitud hasta que el examen se marque como realizado.
     */
    private const POLITICA_APTITUD = 'A';
    private const NIOSH_CRITERIO = 'es_seguro';
    private const EXAMEN_NO_REALIZADO = 'advertencia';

    /** Regiones del examen médico. En el Vue la clave es hallazgo_<region>. */
    private const REGIONES = [
        'cervical', 'dorsal', 'lumbar',
        'hombro_derecho', 'hombro_izquierdo',
        'codo_derecho', 'codo_izquierdo',
        'muneca_derecha', 'muneca_izquierda',
        'cadera_derecha', 'cadera_izquierda',
        'rodilla_derecha', 'rodilla_izquierda',
        'tobillo_derecho', 'tobillo_izquierdo',
    ];

    /** Valores de derivación que acepta el select de ExamenMedico.vue (vacío = sin derivación). */
    private const DERIVACIONES = ['traumatologia', 'neurologia', 'reumatologia', 'fisioterapia', 'psicologia', 'otro'];

    /**
     * Campos que el chat puede proponer, por sección del Vue.
     * Aptitud, restricciones y recomendaciones de dictamen no están aquí: los propone recomendar().
     */
    private const CAMPOS_CHAT = [
        'antecedentes' => [
            'hf_obesidad', 'hf_diabetes', 'hf_hipertension', 'hf_cancer', 'hf_nefropatia', 'hf_otros',
            'am_obesidad', 'am_diabetes', 'am_hipertension', 'am_dislipidemias', 'am_cardiovasculares',
            'am_renales', 'am_hepaticos', 'am_musculo_esqueleticos', 'am_urologicos', 'am_infecciosos', 'am_otros',
            'cirugias', 'cirugias_especifique', 'consumo_medicamentos', 'alergias',
        ],
        'habitos' => [
            'tabaquismo', 'alcoholismo', 'varia_consumo_estres', 'varia_consumo_tristeza',
            'escala_fagerstrm', 'escala_audit', 'frecuencia_consumo_alcohol',
            'refiere_diarrea', 'refiere_estrenimiento', 'refiere_gastritis', 'refiere_ulcera',
            'refiere_nauseas', 'refiere_reflujo', 'refiere_vomitos', 'refiere_colitis',
        ],
        'factoresLaborales' => [
            'tension_emocional', 'alta_responsabilidad', 'carga_excesiva_trabajo', 'turno_rotativo',
            'turno_nocturno', 'trabajo_repetitivo', 'condiciones_entorno', 'carga_trabajo',
            'falta_control_trabajo', 'jornadas_trabajo', 'interferencia_trabajo_familia',
            'relaciones_interpersonales', 'violencia_laboral', 'acontecimientos_traumaticos',
        ],
        'examenMedico' => [
            'examen_medico_realizado', 'observaciones_examen', 'diagnostico_ergonomico',
            'recomendaciones_examen', 'restricciones_laborales', 'derivacion_especialista',
            'hallazgos_propuestos',
        ],
        'hallazgos' => [
            'posturas_forzadas', 'movimientos_repetitivos', 'manipulacion_cargas', 'carga_maxima_manipulada_kg',
        ],
    ];

    /**
     * Chat: propone campos a partir de lo que el médico dicta o escribe.
     * No guarda nada: el médico revisa y aplica.
     *
     * @param array $contexto  Formulario completo (el mismo que usa el Vue).
     * @param array $historial Mensajes previos: [{sender: 'user'|'ia', text: '...'}].
     *
     * @return array{respuesta: string, campos_completados: array, advertencias: array}
     */
    public function consultar(string $mensaje, array $contexto = [], array $historial = []): array
    {
        set_time_limit(300);

        $data = $this->llamarGemini($this->construirPromptChat($mensaje, $contexto, $historial), 'chat');

        if ($data === null) {
            return $this->respuestaFallida();
        }

        $advertencias = [];
        $campos = $this->sanitizarCamposChat($data['campos'] ?? [], $advertencias);

        return [
            'respuesta' => is_string($data['respuesta'] ?? null) ? $data['respuesta'] : 'Sin respuesta.',
            'campos_completados' => $campos,
            'advertencias' => $advertencias,
        ];
    }

    /**
     * Propone aptitud y los campos del examen médico.
     * La aptitud sale de criteriosObjetivos(); el modelo redacta y sugiere el examen.
     * El médico valida, modifica o completa antes de guardar.
     *
     * @param array $valoracion Columnas tipadas de valoraciones_ergonomicas (o el formulario completo).
     * @param array $datos      Contenido de datos_json (formulario completo, con nombres del Vue).
     *
     * @return array{respuesta: string, campos_completados: array, advertencias: array}
     */
    public function proponerDictamen(array $valoracion, array $datos): array
    {
        set_time_limit(300);

        $criterios = $this->criteriosObjetivos($valoracion, $datos);

        if ($criterios['aptitud'] === null) {
            return [
                'respuesta' => 'Faltan datos para proponer la aptitud. Registra un instrumento (RULA, REBA o NIOSH) y marca el examen médico como realizado.',
                'campos_completados' => [],
                'advertencias' => $criterios['advertencias'],
            ];
        }

        $data = $this->llamarGemini($this->construirPrompt($valoracion, $criterios, $datos), 'dictamen');

        if ($data === null) {
            return [
                'respuesta' => 'No pude generar la propuesta. Intenta de nuevo.',
                'campos_completados' => ['aptitud' => $criterios['aptitud']],
                'advertencias' => $criterios['advertencias'],
            ];
        }

        $campos = $this->sanitizarCampos(is_array($data['campos'] ?? null) ? $data['campos'] : []);

        $advertencias = array_merge(
            $criterios['advertencias'],
            $this->sanitizarAdvertencias($data['advertencias'] ?? [])
        );

        // Los hallazgos sugeridos no cambian la aptitud hasta que el médico los confirme.
        $sugeridos = array_map(fn ($k) => str_replace('hallazgo_', '', $k), array_keys($campos['hallazgos']));
        $noCuentan = array_diff($sugeridos, $this->regionesConHallazgo($datos));

        if ($noCuentan !== []) {
            $advertencias[] = 'La IA sugiere hallazgos en ' . implode(', ', $noCuentan)
                . '. No cambian la aptitud hasta que los confirmes.';
        }

        return [
            'respuesta' => $this->texto($data['respuesta'] ?? null) ?? 'Propuesta lista para tu revisión.',
            'campos_completados' => $this->armarCampos($criterios['aptitud'], $campos),
            'advertencias' => array_values(array_unique($advertencias)),
        ];
    }

    // =========================================================
    // CHAT
    // =========================================================

    private function construirPromptChat(string $mensaje, array $contexto, array $historial): string
    {
        $camposPermitidos = json_encode(self::CAMPOS_CHAT, JSON_UNESCAPED_UNICODE);
        $regionesJson = json_encode(self::REGIONES, JSON_UNESCAPED_UNICODE);
        $contextoJson = mb_substr(
            json_encode($contexto, JSON_UNESCAPED_UNICODE) ?: '{}',
            0,
            self::MAX_CARACTERES_DATOS
        );
        $historialTexto = $this->bloqueHistorial($historial);

        return <<<TXT
Eres el asistente del módulo de ergonomía ocupacional de Medicina del Trabajo.
Ayudas al médico a completar la valoración mientras dicta o escribe.

REGLAS
- Solo propones valores de los campos permitidos listados abajo. Cualquier otro campo se descarta.
- Solo propones lo que el médico dictó o escribió en este mensaje o en el historial. No inventes datos.
- No propongas aptitud ni dictamen: los decide el médico con los criterios objetivos.
- hallazgos_propuestos: solo regiones de esta lista: {$regionesJson}. Inclúyelas únicamente si el médico describe un hallazgo en esa región.
- Si falta información necesaria, pídela en "respuesta".
- Responde SOLO con JSON válido con esta forma:
{"respuesta": "texto para el médico", "campos": {"habitos": {"tabaquismo": true}}}
- Si no hay campos que proponer, usa "campos": {}.

CAMPOS PERMITIDOS
{$camposPermitidos}

FICHA ACTUAL (solo referencia)
{$contextoJson}

HISTORIAL RECIENTE
{$historialTexto}

MENSAJE DEL MÉDICO
{$mensaje}
TXT;
    }

    private function bloqueHistorial(array $historial): string
    {
        $recientes = array_slice($historial, -self::MAX_MENSAJES_HISTORIAL);

        if (!$recientes) {
            return 'Sin historial.';
        }

        return collect($recientes)
            ->map(fn ($m) => (($m['sender'] ?? '') === 'user' ? 'Médico' : 'IA') . ': ' . ($m['text'] ?? ''))
            ->implode("\n");
    }

    /** Deja solo campos de la lista blanca, agrupados por sección, y valida derivación y hallazgos. */
    private function sanitizarCamposChat(mixed $campos, array &$advertencias): array
    {
        if (!is_array($campos)) {
            return [];
        }

        $limpios = [];

        foreach ($campos as $seccion => $propuestos) {
            if (!is_array($propuestos)) {
                continue;
            }

            $permitidos = self::CAMPOS_CHAT[$seccion] ?? [];

            foreach ($propuestos as $campo => $valor) {
                if (!in_array($campo, $permitidos, true)) {
                    $advertencias[] = "Campo no permitido descartado: {$seccion}.{$campo}";
                    continue;
                }

                if ($valor === null || $valor === '' || $valor === []) {
                    continue;
                }

                if ($campo === 'hallazgos_propuestos') {
                    $mapa = $this->sanitizarHallazgos($valor);
                    if ($mapa !== []) {
                        $limpios[$seccion]['hallazgos'] = $mapa;
                    }
                    continue;
                }

                if ($campo === 'derivacion_especialista' && $this->derivacion($valor) === null) {
                    $advertencias[] = "Derivación no válida descartada: {$valor}";
                    continue;
                }

                $limpios[$seccion][$campo] = $valor;
            }
        }

        return $limpios;
    }

    private function respuestaFallida(): array
    {
        return [
            'respuesta' => 'No pude procesar tu solicitud en este momento. Intenta de nuevo.',
            'campos_completados' => [],
            'advertencias' => ['La IA no respondió.'],
        ];
    }

    // =========================================================
    // CRITERIOS OBJETIVOS (la aptitud sale de aquí, no del modelo)
    // =========================================================

    private function criteriosObjetivos(array $v, array $datos): array
    {
        $advertencias = [];

        $rula = $this->normalizar($v['rula_nivel_riesgo'] ?? null);
        $reba = $this->normalizar($v['reba_nivel_accion'] ?? null);
        $indiceNiosh = $this->normalizar($v['niosh_indice_levantamiento'] ?? null);

        if ($rula === null && $reba === null && $indiceNiosh === null) {
            return [
                'aptitud' => null,
                'motivos' => [],
                'advertencias' => ['No hay puntuaciones de RULA, REBA ni NIOSH.'],
            ];
        }

        $examenRealizado = filter_var($datos['examenMedico']['examen_medico_realizado'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!$examenRealizado && self::EXAMEN_NO_REALIZADO === 'no_proponer') {
            return [
                'aptitud' => null,
                'motivos' => [],
                'advertencias' => ['Marca "Examen médico realizado" para proponer la aptitud.'],
            ];
        }

        if (!$examenRealizado) {
            $advertencias[] = 'No se realizó examen médico; la aptitud se basa solo en el instrumento.';
        }

        $riesgoInstrumento = [];

        if ($rula === 'alto') {
            $riesgoInstrumento[] = 'RULA en nivel alto.';
        }
        if ($reba === 'cambios_inmediatos') {
            $riesgoInstrumento[] = 'REBA indica cambios inmediatos.';
        }
        if ($indiceNiosh !== null && $this->nioshNoSeguro($v)) {
            $riesgoInstrumento[] = 'NIOSH: el levantamiento no es seguro.';
        }

        $hallazgos = $this->regionesConHallazgo($datos);
        $hayRiesgo = $riesgoInstrumento !== [];
        $hayHallazgo = $hallazgos !== [];

        switch (self::POLITICA_APTITUD) {
            case 'B':
                if ($hayRiesgo && $hayHallazgo) {
                    $aptitud = 'No apto';
                } elseif ($hayRiesgo || $hayHallazgo) {
                    $aptitud = 'Apto con restricciones';
                } else {
                    $aptitud = 'Apto';
                }
                break;

            case 'C':
                $aptitud = $hayRiesgo ? 'Apto con restricciones' : 'Apto';
                if ($hayHallazgo) {
                    $advertencias[] = 'Política C: los hallazgos marcados no cambian la aptitud.';
                }
                break;

            case 'D':
                $aptitud = $hayHallazgo ? 'Apto con restricciones' : 'Apto';
                if ($hayRiesgo) {
                    $advertencias[] = 'Política D: el riesgo del instrumento no cambia la aptitud.';
                }
                break;

            default: // 'A'
                $aptitud = ($hayRiesgo || $hayHallazgo) ? 'Apto con restricciones' : 'Apto';
        }

        $motivos = $riesgoInstrumento;

        if ($hayHallazgo) {
            $motivos[] = 'Hallazgos marcados por el médico en: '
                . implode(', ', array_map(fn ($r) => str_replace('_', ' ', $r), $hallazgos)) . '.';
        }

        return [
            'aptitud' => $aptitud,
            'motivos' => $motivos,
            'advertencias' => $advertencias,
        ];
    }

    /** Evalúa NIOSH según NIOSH_CRITERIO. */
    private function nioshNoSeguro(array $v): bool
    {
        if (self::NIOSH_CRITERIO === 'peso') {
            $real = $this->normalizar($v['niosh_peso_real_kg'] ?? null);
            $recomendado = $this->normalizar($v['niosh_peso_recomendado_kg'] ?? null);

            if ($real !== null && $recomendado !== null) {
                return (float) $real > (float) $recomendado;
            }
        }

        return !filter_var($v['niosh_es_seguro'] ?? true, FILTER_VALIDATE_BOOLEAN);
    }

    /** Regiones con hallazgo marcado por el médico, leídas de cualquier nivel de datos_json. */
    private function regionesConHallazgo(array $datos): array
    {
        $regiones = [];

        $iterador = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($datos));

        foreach ($iterador as $clave => $valor) {
            if (is_string($clave) && str_starts_with($clave, 'hallazgo_') && in_array($valor, [true, 1, '1', 'si'], true)) {
                $regiones[] = substr($clave, strlen('hallazgo_'));
            }
        }

        return array_values(array_unique($regiones));
    }

    // =========================================================
    // PROMPT DEL DICTAMEN
    // =========================================================

    private function construirPrompt(array $valoracion, array $criterios, array $datos): string
    {
        $contexto = array_diff_key($valoracion, array_flip(['datos_json', 'created_at', 'updated_at', 'folio']));
        $contextoJson = json_encode($contexto, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '{}';

        $criteriosJson = json_encode($criterios, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '{}';

        $datosJson = mb_substr(
            json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '{}',
            0,
            self::MAX_CARACTERES_DATOS
        );

        $regionesJson = json_encode(self::REGIONES, JSON_UNESCAPED_UNICODE);
        $derivacionesJson = json_encode(array_merge([''], self::DERIVACIONES), JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
Eres asistente de un médico del trabajo. Redactas la propuesta de los campos de una valoración de ergonomía ocupacional. El médico revisará, modificará o completará todo antes de guardar.

Reglas generales:
- La aptitud ya fue decidida por criterios objetivos. No la cambies ni la presentes como decisión tuya.
- No inventes datos. Usa solo lo que aparece en la valoración, los criterios y el formulario. Si falta información importante, indícalo en "advertencias".
- Redacta en español, tono clínico, sin prometer resultados.
- observaciones_examen es texto escrito por el médico. No lo devuelvas ni lo reescribas.

Reglas por campo:
- examen_medico_realizado: true si observaciones_examen o los hallazgos describen una exploración. false si no hay nada.
- hallazgos_propuestos: solo regiones de esta lista: {$regionesJson}. Inclúyelas únicamente si observaciones_examen describe un hallazgo en esa región. Nunca las deduzcas de RULA, REBA o NIOSH.
- diagnostico_ergonomico: solo si observaciones_examen describe hallazgos. Usa lenguaje de sospecha ("compatible con", "sugestivo de"). Nunca un diagnóstico definitivo. Si no hay hallazgos, null.
- restricciones: si la aptitud es "Apto", null. Si es "Apto con restricciones" o "No apto", describe restricciones concretas ligadas a los motivos de los criterios.
- recomendaciones: medidas ergonómicas ligadas a los instrumentos y a los motivos (ajustes de altura de trabajo, pausas, ayudas mecánicas, rotación). Evita recomendaciones genéricas.
- restricciones_laborales: limitaciones de carga, postura o movimientos ligadas a la aptitud. null si la aptitud es "Apto".
- recomendaciones_examen: acciones clínicas o de seguimiento. No repitas textualmente recomendaciones.
- derivacion_especialista: uno de estos valores: {$derivacionesJson}. Usa "" si no hay motivo para derivar.

Valoración (columnas tipadas):
{$contextoJson}

Criterios objetivos:
{$criteriosJson}

Datos del formulario:
{$datosJson}

Responde solo JSON con esta forma:
{
  "respuesta": "resumen breve para el médico",
  "campos": {
    "examen_medico_realizado": true,
    "hallazgos_propuestos": ["cervical"],
    "diagnostico_ergonomico": "texto o null",
    "restricciones": "texto o null",
    "recomendaciones": "texto o null",
    "restricciones_laborales": "texto o null",
    "recomendaciones_examen": "texto o null",
    "derivacion_especialista": ""
  },
  "advertencias": ["texto"]
}
PROMPT;
    }

    // =========================================================
    // ARMADO DE LA RESPUESTA (nombres exactos del Vue)
    // =========================================================

    /**
     * Devuelve solo los campos con valor. Así una propuesta vacía nunca
     * sobrescribe lo que el médico ya escribió.
     */
    private function armarCampos(string $aptitud, array $campos): array
    {
        $examen = array_filter([
            'examen_medico_realizado' => $campos['examen_medico_realizado'],
            'diagnostico_ergonomico' => $campos['diagnostico_ergonomico'],
            'recomendaciones_examen' => $campos['recomendaciones_examen'],
            'restricciones_laborales' => $campos['restricciones_laborales'],
            'derivacion_especialista' => $campos['derivacion_especialista'],
            'hallazgos' => $campos['hallazgos'] ?: null,
        ], fn ($valor) => $valor !== null);

        return array_filter([
            'aptitud' => $aptitud,
            'restricciones' => $campos['restricciones'],
            'recomendaciones' => $campos['recomendaciones'],
            'seguimiento_requerido' => $aptitud !== 'Apto',
            'examenMedico' => $examen ?: null,
        ], fn ($valor) => $valor !== null);
    }

    // =========================================================
    // CLIENTE DE GEMINI (mismo patrón que IAServiceEspirometria)
    // =========================================================

    private function llamarGemini(string $prompt, string $origen): ?array
    {
        try {
            $apiKey = env('GEMINI_API_KEY');
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . self::MODELO . ':generateContent?key=' . $apiKey;

            $response = $this->conReintentos($url, [['text' => $prompt]]);

            if (!$response->successful()) {
                Log::error('Error HTTP al consultar IA de ergonomía (' . $origen . ')', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            return $this->decodificarJson($response->json('candidates.0.content.parts.0.text'), $origen);
        } catch (\Throwable $e) {
            Log::error("Excepción en IAServiceErgonomia ({$origen}): " . $e->getMessage());
            return null;
        }
    }

    /** Reintenta ante errores transitorios (429, 500, 503) con espera exponencial. */
    private function conReintentos(string $url, array $partes, int $maxIntentos = 3)
    {
        $espera = 2;

        for ($intento = 1; ; $intento++) {
            $response = Http::timeout(120)->post($url, [
                'contents' => [['role' => 'user', 'parts' => $partes]],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'responseMimeType' => 'application/json',
                ],
            ]);

            $transitorio = in_array($response->status(), [429, 500, 503], true);

            if ($response->successful() || !$transitorio || $intento >= $maxIntentos) {
                return $response;
            }

            Log::warning("Gemini respondió {$response->status()}, reintentando en {$espera}s ({$intento}/{$maxIntentos})");
            sleep($espera);
            $espera *= 2;
        }
    }

    private function decodificarJson(?string $contenido, string $origen): ?array
    {
        if ($contenido === null || trim($contenido) === '') {
            Log::error("IAServiceErgonomia: respuesta vacía ({$origen})");
            return null;
        }

        $limpio = trim(preg_replace('/```(?:json)?/', '', $contenido));
        $data = json_decode($limpio, true);

        if (!is_array($data)) {
            Log::warning("IAServiceErgonomia: JSON inválido ({$origen})");
            return null;
        }

        return $data;
    }

    // =========================================================
    // SANITIZACIÓN DE LA RESPUESTA DEL DICTAMEN
    // =========================================================

    private function sanitizarCampos(array $campos): array
    {
        return [
            'examen_medico_realizado' => filter_var($campos['examen_medico_realizado'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'hallazgos' => $this->sanitizarHallazgos($campos['hallazgos_propuestos'] ?? []),
            'diagnostico_ergonomico' => $this->texto($campos['diagnostico_ergonomico'] ?? null),
            'restricciones' => $this->texto($campos['restricciones'] ?? null),
            'recomendaciones' => $this->texto($campos['recomendaciones'] ?? null),
            'restricciones_laborales' => $this->texto($campos['restricciones_laborales'] ?? null),
            'recomendaciones_examen' => $this->texto($campos['recomendaciones_examen'] ?? null),
            'derivacion_especialista' => $this->derivacion($campos['derivacion_especialista'] ?? null),
        ];
    }

    /** Solo devuelve regiones válidas, marcadas como true. Nunca manda false: no borra marcas del médico. */
    private function sanitizarHallazgos(mixed $propuestos): array
    {
        if (!is_array($propuestos)) {
            return [];
        }

        $mapa = [];

        foreach (array_intersect(self::REGIONES, array_map('strval', $propuestos)) as $region) {
            $mapa['hallazgo_' . $region] = true;
        }

        return $mapa;
    }

    private function derivacion(mixed $valor): ?string
    {
        return (is_string($valor) && in_array($valor, self::DERIVACIONES, true)) ? $valor : null;
    }

    private function sanitizarAdvertencias(mixed $advertencias): array
    {
        if (!is_array($advertencias)) {
            return [];
        }

        return array_slice(array_values(array_filter(array_map([$this, 'texto'], $advertencias))), 0, 10);
    }

    private function texto(mixed $valor): ?string
    {
        if (!is_string($valor)) {
            return null;
        }

        $texto = trim($valor);

        return $texto === '' ? null : mb_substr($texto, 0, 5000);
    }

    private function normalizar(mixed $valor): mixed
    {
        return $valor === '' ? null : $valor;
    }
}