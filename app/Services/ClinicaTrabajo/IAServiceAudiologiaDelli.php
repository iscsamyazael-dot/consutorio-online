<?php

namespace App\Services\ClinicaTrabajo;

use App\Models\ClinicaTrabajo\PacientesClinicaTrabajo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * IA del submódulo DELLI (audiología).
 * La IA solo PROPONE campos. El médico revisa y guarda.
 * Modos: chat (conversación) y recomendar (interpretación y aptitud a partir de la ficha).
 */
class IAServiceAudiologiaDelli
{
    private const MODELO = 'gemini-3.5-flash-lite';
    private const MAX_MENSAJES_HISTORIAL = 10;

    /** Valores exactos que usa el select de aptitud del componente de audiometría. */
    private const APTITUDES = ['Apto', 'Apto con restricciones', 'No apto'];

    /** Valores exactos que usa el select de tipo de hipoacusia del componente. */
    private const TIPOS_HIPOACUSIA = ['Normal', 'Neurosensorial', 'Conductiva', 'Mixta'];

    /**
     * Campos que la IA puede proponer, agrupados por hoja.
     * Los valores numéricos de audiometría (frecuencias, PTA, clasificación) NO están aquí.
     */
    private const CAMPOS_PERMITIDOS = [
        'hoja2' => [
            'riesgo_auditivo', 'duracion_exposicion', 'medidas_control', 'uso_proteccion',
            'tipo_protector', 'cigarrillos_dia', 'tabaquismo', 'disolventes', 'metales',
            'gases', 'sales', 'toxicomanias', 'antibioticos', 'diureticos', 'salicilatos',
            'armas_fuego_caneria', 'niveles_altos_musica', 'lugares_ruidosos_discoteca',
            'automovilismo_motociclismo', 'otros_pasatiempos_ruido',
        ],
        'hoja3' => [
            'historia_familiar_perdida', 'parentesco', 'observaciones_familia',
            'alergias', 'alergias_especificar', 'covid19', 'covid19_obs', 'resfriado',
            'resfriado_obs', 'otitis', 'otitis_obs', 'descripcion_hipoacusia',
            'tiempo_evolucion', 'otalgia_der', 'otalgia_izq', 'otorrea_der', 'otorrea_izq',
            'prurito_der', 'prurito_izq', 'otodinia_der', 'otodinia_izq', 'otorragia_der',
            'otorragia_izq', 'autofonia_der', 'autofonia_izq', 'acufenos', 'congestion',
            'algiacusia', 'sarampion', 'hipertension', 'paperas', 'meningitis', 'diabetes',
            'uso_aparat', 'ninguna_otitis', 'vertigo', 'paracusias_willis', 'paracusias_weber',
            'paracusias_negado', 'paracusias_hipo', 'paracusias_hiper', 'fatiga',
            'sensibilidad', 'examen_otoscopico', 'patron',
        ],
        'hoja4' => [
            'exposicion_14horas', 'tipo_audiometria', 'fecha_ultima_calibracion',
            'valoracion', 'interpretacion', 'tipo_hipoacusia', 'restricciones',
            'recomendaciones', 'aptitud',
        ],
    ];

    /** Campos de hoja 4 que genera el modo recomendar. */
    private const CAMPOS_RECOMENDACION = [
        'valoracion', 'interpretacion', 'tipo_hipoacusia', 'restricciones', 'aptitud', 'recomendaciones',
    ];

    /** Frecuencias donde se evalúa la vía ósea. */
    private const FRECUENCIAS_OSEA = [250, 500, 1000, 2000, 3000, 4000];

    /** Frecuencias válidas de la tabla de audiometría. */
    private const FRECUENCIAS_VALIDAS = [125, 250, 500, 1000, 2000, 3000, 4000, 6000, 8000];

    /** PTA del oído peor por encima de este valor (dB HL) es el mínimo para proponer "No apto". */
    private const PTA_MINIMO_NO_APTO = 40;

    /**
     * @return array{respuesta: string, campos_completados: array, advertencias: array}
     */
    public function consultar(
        string $mensaje,
        array $contexto = [],
        array $historial = [],
        ?PacientesClinicaTrabajo $paciente = null
    ): array {
        set_time_limit(300);

        $prompt = $this->construirPrompt($mensaje, $contexto, $historial, $paciente);
        $data = $this->llamarGemini($prompt);

        if ($data === null) {
            return $this->respuestaFallida();
        }

        $advertencias = [];
        $campos = $this->sanitizarCampos($data['campos'] ?? [], $advertencias);

        return [
            'respuesta' => is_string($data['respuesta'] ?? null) ? $data['respuesta'] : 'Sin respuesta.',
            'campos_completados' => $campos,
            'advertencias' => $advertencias,
        ];
    }

    /**
     * Propone valoración, interpretación, tipo de hipoacusia, restricciones y aptitud.
     * No guarda nada: el frontend aplica los campos y el médico los revisa.
     *
     * @return array{respuesta: string, campos_completados: array, advertencias: array}
     */
    public function recomendar(array $contexto, ?PacientesClinicaTrabajo $paciente = null): array
    {
        set_time_limit(300);

        $hallazgos = $this->calcularHallazgos($contexto);
        $prompt = $this->construirPromptRecomendacion($contexto, $paciente, $hallazgos);
        $data = $this->llamarGemini($prompt);

        if ($data === null) {
            return $this->respuestaFallida();
        }

        $advertencias = [];
        $campos = $this->sanitizarCampos($data['campos'] ?? [], $advertencias);

        $hoja4 = array_intersect_key($campos['hoja4'] ?? [], array_flip(self::CAMPOS_RECOMENDACION));

        $hoja4['recomendaciones'] = $this->normalizarRecomendaciones(
            $hoja4['recomendaciones'] ?? [],
            $hoja4['restricciones'] ?? null
        );
        if (!$hoja4['recomendaciones']) {
            unset($hoja4['recomendaciones']);
        }

        // Sin vía ósea no se puede separar conductiva de neurosensorial
        if ($hallazgos['tipo_hipoacusia'] !== null) {
            if (isset($hoja4['tipo_hipoacusia']) && $hoja4['tipo_hipoacusia'] !== $hallazgos['tipo_hipoacusia']) {
                $advertencias[] = "Tipo de hipoacusia corregido a {$hallazgos['tipo_hipoacusia']} según el cálculo.";
            }
            $hoja4['tipo_hipoacusia'] = $hallazgos['tipo_hipoacusia'];
        } else {
            unset($hoja4['tipo_hipoacusia']);
            if (($contexto['hoja4']['clasificacion'] ?? null) !== 'NORMAL') {
                $advertencias[] = 'Sin vía ósea suficiente: no se propone el tipo de hipoacusia.';
            }
        }
        // Regla dura: audiometría normal => Apto sin restricciones
        if (($contexto['hoja4']['clasificacion'] ?? null) === 'NORMAL') {
            $hoja4['aptitud'] = 'Apto';
            $hoja4['restricciones'] = '';
            $hoja4['tipo_hipoacusia'] = 'Normal';
        }
        $this->validarAptitud($hoja4, $contexto, $advertencias);
        $this->validarMuesca($hoja4, $hallazgos, $advertencias);

        // Banderas rojas: si la IA no las menciona, el servidor lo agrega
        $sintomas = $contexto['hoja3']['sintomas'] ?? [];
        $antecedentes = $contexto['hoja3']['antecedentes'] ?? [];

        $tieneBandera = ($sintomas['otorrea_der'] ?? false) === true
            || ($sintomas['otorrea_izq'] ?? false) === true
            || ($sintomas['otorragia_der'] ?? false) === true
            || ($sintomas['otorragia_izq'] ?? false) === true
            || ($antecedentes['vertigo'] ?? false) === true;

        if ($tieneBandera && isset($hoja4['valoracion'])
            && !str_contains(mb_strtolower($hoja4['valoracion']), 'otorrinolaring')) {
            $hoja4['valoracion'] .= ' Por la presencia de otorrea, otorragia o vértigo se recomienda valoración por otorrinolaringología.';
            $advertencias[] = 'Hay bandera roja (otorrea, otorragia o vértigo): revisa que la valoración incluya la recomendación de otorrinolaringología.';
        }

        $campos = $hoja4 ? ['hoja4' => $hoja4] : [];

        foreach ($this->numerosNoAnclados($hoja4, $contexto) as $numero) {
            $advertencias[] = "Revisa el número {$numero}: no aparece en la ficha.";
        }

        foreach ($this->frecuenciasInvalidas($hoja4) as $f) {
            $advertencias[] = "Frecuencia {$f} Hz no existe en la tabla: revisa el texto.";
        }

        return [
            'respuesta' => 'Recomendaciones generadas. Revísalas antes de guardar.',
            'campos_completados' => $campos,
            'advertencias' => $advertencias,
        ];
    }

    /**
     * Calcula en PHP los hechos audiométricos por oído: muesca, brecha aéreo-ósea,
     * vía ósea elevada y tipo de hipoacusia. La IA los recibe como dato, no los deduce.
     *
     * Criterios:
     * - Muesca: 3000, 4000 o 6000 Hz al menos 10 dB peor que 2000 Hz y que 8000 Hz (mismo oído).
     * - Brecha significativa: 15 dB o más en al menos dos frecuencias contiguas de vía ósea.
     * - Vía ósea elevada: algún valor de vía ósea mayor de 20 dB HL.
     * - Tipo: brecha con vía ósea normal = Conductiva; brecha con vía ósea elevada = Mixta;
     *   sin brecha, PTA mayor de 20 dB = Neurosensorial; sin brecha, PTA de 20 dB o menos = Normal.
     *
     * @return array{oidos: array, tipo_hipoacusia: ?string}
     */
    private function calcularHallazgos(array $contexto): array
    {
        $h4 = $contexto['hoja4'] ?? [];
        $oidos = [];
        $tipos = [];

        foreach (['der', 'izq'] as $oido) {
            $aerea = fn (int $f) => $this->valorNumerico($h4, "audiometria_{$f}_{$oido}");
            $osea = fn (int $f) => $this->valorNumerico($h4, "via_osea_{$f}_{$oido}");

            $muesca = [];
            $a2000 = $aerea(2000);
            $a8000 = $aerea(8000);
            if ($a2000 !== null && $a8000 !== null) {
                foreach ([3000, 4000, 6000] as $f) {
                    $v = $aerea($f);
                    if ($v !== null && $v - $a2000 >= 10 && $v - $a8000 >= 10) {
                        $muesca[] = $f;
                    }
                }
            }

            $brecha = [];
            $oseaElevada = false;
            $tieneOsea = false;
            foreach (self::FRECUENCIAS_OSEA as $f) {
                $o = $osea($f);
                if ($o === null) {
                    continue;
                }
                $tieneOsea = true;
                if ($o > 20) {
                    $oseaElevada = true;
                }
                $a = $aerea($f);
                if ($a !== null && $a - $o >= 15) {
                    $brecha[] = $f;
                }
            }
            $brechaSignificativa = $this->hayFrecuenciasContiguas($brecha);

            $pta = $this->valorNumerico($h4, "pta_{$oido}");
            $tipo = null;
            if ($tieneOsea) {
                if ($brechaSignificativa) {
                    $tipo = $oseaElevada ? 'Mixta' : 'Conductiva';
                } elseif ($pta !== null) {
                    $tipo = $pta > 20 ? 'Neurosensorial' : 'Normal';
                }
            }

            $oidos[$oido] = [
                'muesca_frecuencias' => $muesca,
                'brecha_frecuencias' => $brecha,
                'via_osea_elevada' => $oseaElevada,
                'tipo' => $tipo,
            ];

            if ($tipo !== null) {
                $tipos[] = $tipo;
            }
        }

        $unicos = array_unique($tipos);
        if (!$unicos) {
            $tipoGeneral = null;
        } elseif (in_array('Mixta', $unicos, true)
            || (in_array('Conductiva', $unicos, true) && in_array('Neurosensorial', $unicos, true))) {
            $tipoGeneral = 'Mixta';
        } elseif (in_array('Neurosensorial', $unicos, true)) {
            $tipoGeneral = 'Neurosensorial';
        } elseif (in_array('Conductiva', $unicos, true)) {
            $tipoGeneral = 'Conductiva';
        } else {
            $tipoGeneral = 'Normal';
        }

        return ['oidos' => $oidos, 'tipo_hipoacusia' => $tipoGeneral];
    }

    private function valorNumerico(array $h4, string $clave): ?float
    {
        $valor = $h4[$clave] ?? null;

        return ($valor === null || $valor === '' || !is_numeric($valor)) ? null : (float) $valor;
    }

    /** Verdadero si hay al menos dos frecuencias seguidas en la tabla de vía ósea. */
    private function hayFrecuenciasContiguas(array $frecuencias): bool
    {
        $indices = array_map(fn ($f) => array_search($f, self::FRECUENCIAS_OSEA, true), $frecuencias);
        sort($indices);

        for ($i = 1; $i < count($indices); $i++) {
            if ($indices[$i] - $indices[$i - 1] === 1) {
                return true;
            }
        }

        return false;
    }

    /** "No apto" solo si el PTA del oído peor supera el mínimo; si no, Apto con restricciones. */
    private function validarAptitud(array &$hoja4, array $contexto, array &$advertencias): void
    {
        if (($hoja4['aptitud'] ?? null) !== 'No apto') {
            return;
        }

        $h4 = $contexto['hoja4'] ?? [];
        $der = $this->valorNumerico($h4, 'pta_der');
        $izq = $this->valorNumerico($h4, 'pta_izq');

        if ($der === null || $izq === null) {
            $hoja4['aptitud'] = 'Apto con restricciones';
            $advertencias[] = 'No apto no se acepta sin PTA de ambos oídos; se propone Apto con restricciones.';
        } elseif (max($der, $izq) <= self::PTA_MINIMO_NO_APTO) {
            $hoja4['aptitud'] = 'Apto con restricciones';
            $advertencias[] = 'No apto requiere PTA mayor de 40 dB; se propone Apto con restricciones.';
        }
    }

    /** Si el texto menciona muesca pero el cálculo no la confirma, lo avisa. No borra el texto. */
    private function validarMuesca(array $hoja4, array $hallazgos, array &$advertencias): void
    {
        foreach ($hallazgos['oidos'] as $datos) {
            if (!empty($datos['muesca_frecuencias'])) {
                return;
            }
        }

        $texto = mb_strtolower(implode(' ', array_filter(
            [$hoja4['valoracion'] ?? '', $hoja4['interpretacion'] ?? '', $hoja4['restricciones'] ?? ''],
            'is_string'
        )));

        if (str_contains($texto, 'muesca')) {
            $advertencias[] = 'La IA mencionó muesca, pero el cálculo no la confirma. Revísalo antes de guardar.';
        }
    }
    
    /** Frecuencias escritas en el texto (seguidas de "Hz") que no existen en la tabla. */
    private function frecuenciasInvalidas(array $hoja4): array
    {
        $texto = implode(' ', array_filter(
            [$hoja4['valoracion'] ?? '', $hoja4['interpretacion'] ?? '', $hoja4['restricciones'] ?? ''],
            'is_string'
        ));

        preg_match_all('/(\d+)\s*Hz/u', $texto, $coincidencias);

        $invalidas = [];
        foreach (array_unique($coincidencias[1]) as $f) {
            if (!in_array((int) $f, self::FRECUENCIAS_VALIDAS, true)) {
                $invalidas[] = $f;
            }
        }

        return $invalidas;
    }

    private function respuestaFallida(): array
    {
        return [
            'respuesta' => 'No pude procesar tu solicitud en este momento. Intenta de nuevo.',
            'campos_completados' => [],
            'advertencias' => ['La IA no respondió.'],
        ];
    }

    private function construirPrompt(string $mensaje, array $contexto, array $historial, ?PacientesClinicaTrabajo $paciente): string
    {
        $camposPermitidos = json_encode(self::CAMPOS_PERMITIDOS, JSON_UNESCAPED_UNICODE);
        $contextoJson = mb_substr(json_encode($contexto, JSON_UNESCAPED_UNICODE) ?: '{}', 0, 12000);
        $datosPaciente = $this->bloquePaciente($paciente);
        $historialTexto = $this->bloqueHistorial($historial);

        return <<<TXT
        Eres el asistente del módulo de audiología DELLI de Medicina del Trabajo.
        Ayudas al médico a completar la Historia Clínica Audiológica.

        REGLAS
        - Solo propones valores de los campos permitidos listados abajo. Cualquier otro campo se descarta.
        - Solo propones lo que el médico dictó o escribió en este mensaje o en el historial. No inventes datos.
        - No calculas ni propones valores de audiometría (frecuencias, PTA, clasificación).
        - Si falta información necesaria, pídela en "respuesta".
        - "aptitud" debe ser exactamente Apto, Apto con restricciones o No apto.
        - "tipo_hipoacusia" debe ser exactamente Normal, Neurosensorial, Conductiva o Mixta.
        - Responde SOLO con JSON válido con esta forma:
        {"respuesta": "texto para el médico", "campos": {"hoja2": {"riesgo_auditivo": "..."}}}
        - Si no hay campos que proponer, usa "campos": {}.

        CAMPOS PERMITIDOS
        {$camposPermitidos}

        DATOS DEL PACIENTE (solo referencia, no los modifiques)
        {$datosPaciente}

        FICHA ACTUAL (solo referencia)
        {$contextoJson}

        HISTORIAL RECIENTE
        {$historialTexto}

        MENSAJE DEL MÉDICO
        {$mensaje}
        TXT;
    }

    private function construirPromptRecomendacion(array $contexto, ?PacientesClinicaTrabajo $paciente, array $hallazgos): string
    {
        $contextoJson = mb_substr(json_encode($contexto, JSON_UNESCAPED_UNICODE) ?: '{}', 0, 12000);
        $hallazgosJson = json_encode($hallazgos, JSON_UNESCAPED_UNICODE) ?: '{}';
        $datosPaciente = $this->bloquePaciente($paciente);

        return <<<TXT
        Eres el asistente del módulo de audiología DELLI de Medicina del Trabajo.
        Con la ficha audiológica de abajo, propón la valoración, la interpretación, el tipo de hipoacusia, las restricciones laborales y la aptitud para el médico y las recomendaciones.
        
        RECOMENDACIONES:
        - Propón en "recomendaciones" las recomendaciones clínicas y de seguridad y salud en el trabajo que este caso necesite. Basa cada una solo en los hallazgos de la ficha: audiometría, hoja 2, hoja 3 y las restricciones propuestas.
        - No te limites a un catálogo. Propón lo que corresponda al caso. Cada recomendación debe ser una acción concreta y verificable: qué hacer, quién, y en qué plazo si aplica.
        - No repitas lo que ya está en las restricciones ni en las recomendaciones fijas (protección auditiva en exposición ≥85 dB(A) y audiometría de control anual).
        - Si hay bandera roja (otorrea, otorragia, vértigo) o asimetría audiométrica relevante, incluye valoración por otorrinolaringología, salvo que ya esté en las restricciones.
        - Analiza las frecuencias de vía aérea (125 a 8000 Hz), incluidas 125 y 250 Hz, para describir el patrón (plano, descendente o con muesca). Nombra frecuencias solo cuando haya una caída relevante. Nunca listes umbrales frecuencia por frecuencia.
        - Usa lenguaje clínico claro, sin términos internos del sistema como "hallazgos calculados".
        - Ordena las recomendaciones por prioridad. El médico especialista las validará, editará o eliminará.
        - Si la clasificación global es NORMAL, la aptitud es "Apto", "restricciones" va vacío y la valoración no habla de aptitud condicionada, aunque existan antecedentes o hallazgos de vigilancia; esos factores se mencionan solo como recomendación.
        - Proporcionalidad: si la clasificación global es NORMAL, las recomendaciones son de prevención primaria y salud general (protección auditiva en exposición, hábitos de protección extralaboral, audiometría de control de rutina). No propongas medidas correctivas, seguimiento clínico estrecho ni lenguaje punitivo u obligatorio. Si hay hipoacusia, la gravedad de las recomendaciones debe corresponder al grado detectado.

        REGLAS
        - En "interpretacion", redacta un párrafo clínico en prosa que describa el estado auditivo bilateral, la simetría y las tendencias (por ejemplo: "umbrales dentro de la normalidad bilateral" o "conservación de frecuencias agudas"). No listes los decibeles de cada frecuencia ni por oído: esos datos ya están en la tabla.
        - Usa las cuatro hojas como un solo cuadro clínico: datos laborales (hoja 1), exposición (hoja 2), antecedentes y síntomas (hoja 3) y audiometría (hoja 4).
        - Basa cada propuesta en la ficha. No inventes antecedentes, síntomas, exposiciones ni resultados.
        - Analiza todas las frecuencias de vía aérea (125 a 8000 Hz), incluidas 125 y 250 Hz, aunque la clasificación use el PTA de 500 a 3000 Hz. Describe el patrón (descendente, con muesca o plano) y las frecuencias donde está la caída.
        - Al describir la muesca, nombra solo las frecuencias donde está la caída (3000, 4000 y 6000 Hz) y usa 8000 Hz solo como referencia.
        - Escribe las frecuencias en Hz (por ejemplo, 4000 Hz) y los umbrales en dB HL (por ejemplo, 35 dB HL). Nunca escribas umbrales en Hz ni frecuencias en kHz.
        - Las únicas frecuencias válidas son 125, 250, 500, 1000, 2000, 3000, 4000, 6000 y 8000 Hz. Nunca escribas otra frecuencia.
        - En "interpretacion", lista cada frecuencia una sola vez, con el valor exacto de la ficha y asociada a su frecuencia correcta. No uses aclaraciones como "(sic)".
        - La muesca, la brecha aéreo-ósea, la vía ósea elevada y el tipo de hipoacusia ya están calculados en HALLAZGOS CALCULADOS. Describe lo que indican; no los recalcules ni los contradigas.
        - Menciona muesca solo si HALLAZGOS CALCULADOS la registra en algún oído. Si la lista está vacía, no la menciones.
        - "tipo_hipoacusia" debe ser exactamente el valor de HALLAZGOS CALCULADOS (tipo_hipoacusia). Si es null, no lo propongas.
        - No calcules PTA ni clasificación nuevos.
        - Si hay hallazgos de vigilancia (muesca en agudos, exposición a ruido muy alto, protección "Nunca" o acúfenos referidos), no propongas "Apto" sin restricciones y explica cada hallazgo en la restricción.
        - Si no hay hallazgos de vigilancia, no los menciones.
        - Las restricciones laborales se basan en factores de trabajo. La exposición extralaboral (discotecas, automovilismo, armas de fuego, música alta) se menciona como recomendación de cuidado, no como restricción.
        - Si el riesgo auditivo de la hoja 2 es Bajo, no hay hallazgos de vigilancia y no hay otorrea, otorragia ni vértigo, no propongas restricción de exposición a áreas de ruido. Menciona la protección y las medidas de control como recomendación.
        - Propón restricción temporal de exposición a ruido y agentes ototóxicos solo si el riesgo auditivo es Medio o Alto, si hay hallazgos de vigilancia, o si hay otorrea, otorragia o vértigo. La restricción dura hasta la valoración por otorrinolaringología.
        - Escribe siempre con acentos, tildes y la ñ del español correctamente.
        - No menciones exposición, protección, acúfenos ni otros antecedentes si no aparecen en la ficha.
        - En "valoracion", escribe como máximo tres frases. No repitas cifra por cifra los umbrales. Explica la relación entre el umbral y los hallazgos, para que el médico vea por qué la aptitud no depende solo del PTA.
        - En "restricciones", indica el motivo usando esos hallazgos y, cuando corresponda, los datos de las hojas 1 a 3.
        - Banderas rojas: si la ficha tiene vértigo, otorrea, otorragia, asimetría entre oídos, paperas o meningitis, o si el tiempo de evolución describe inicio súbito, menciónalo en "valoracion" y recomienda valoración por otorrinolaringología.
        - Otodinia (dolor al manipular el conducto), sobre todo junto con otorrea, sugiere afección del conducto o del oído medio. Menciónala en la interpretación y sugiere revisar el conducto en la exploración; no la trates como bandera roja por sí sola.
        - Contradicciones: si las hojas se contradicen (por ejemplo, "niega sintomatología" marcado junto con síntomas, o protección "Siempre" sin registro de medidas), señálalo en "valoracion" y no elijas una versión.
        - Cualquier número que cites debe aparecer en la ficha.
        - "tipo_hipoacusia" debe ser exactamente Normal, Neurosensorial, Conductiva o Mixta.
        - "aptitud" debe ser exactamente Apto, Apto con restricciones o No apto.
        - No propongas "No apto" salvo que el PTA del oído peor sea mayor de 40 dB. En los demás casos usa "Apto con restricciones".
        - Responde SOLO con JSON válido con esta forma:
        {"campos": {"hoja4": {"valoracion": "...", "interpretacion": "...", "tipo_hipoacusia": "...", "restricciones": "...", "aptitud": "...","recomendaciones": ["...", "..."]}}}

        HALLAZGOS CALCULADOS (no los modifiques)
        {$hallazgosJson}

        DATOS DEL PACIENTE (referencia)
        {$datosPaciente}

        FICHA AUDIOLÓGICA
        {$contextoJson}
        TXT;
    }

    private function bloquePaciente(?PacientesClinicaTrabajo $paciente): string
    {
        // TODO antes de producción: quitar el nombre y enviar solo datos no identificables.
        if (!$paciente) {
            return 'No disponible.';
        }

        return 'Nombre: ' . ($paciente->nombre ?? 'N/D');
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

    /**
     * Llamada a Gemini con reintentos ante 429, 500 y 503 (2s, 4s).
     */
    private function llamarGemini(string $prompt): ?array
    {
        try {
            $apiKey = env('GEMINI_API_KEY');
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . self::MODELO . ':generateContent?key=' . $apiKey;

            $response = null;
            $espera = 2;

            for ($intento = 1; $intento <= 3; $intento++) {
                $response = Http::timeout(120)->post($url, [
                    'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'responseMimeType' => 'application/json',
                    ],
                ]);

                if ($response->successful()) {
                    break;
                }

                if (!in_array($response->status(), [429, 500, 503], true) || $intento === 3) {
                    break;
                }

                sleep($espera);
                $espera *= 2;
            }

            if (!$response || !$response->successful()) {
                Log::error('IAServiceAudiologiaDelli: error HTTP', ['status' => $response?->status()]);
                return null;
            }

            return $this->decodificar($response->json('candidates.0.content.parts.0.text'));
        } catch (\Throwable $e) {
            Log::error('IAServiceAudiologiaDelli: ' . $e->getMessage());
            return null;
        }
    }

    private function decodificar(?string $texto): ?array
    {
        if ($texto === null || trim($texto) === '') {
            return null;
        }

        $data = json_decode($texto, true);
        if (is_array($data)) {
            return $data;
        }

        // Si el modelo agregó texto alrededor del JSON, toma desde la primera { hasta la última }
        $inicio = strpos($texto, '{');
        $fin = strrpos($texto, '}');
        if ($inicio === false || $fin === false || $fin <= $inicio) {
            return null;
        }

        $data = json_decode(substr($texto, $inicio, $fin - $inicio + 1), true);

        return is_array($data) ? $data : null;
    }

    /**
     * Deja solo campos permitidos, descarta vacíos (una respuesta vacía nunca sobrescribe)
     * y valida aptitud y tipo de hipoacusia. Devuelve los campos agrupados por hoja.
     */
    private function sanitizarCampos(mixed $campos, array &$advertencias): array
    {
        if (!is_array($campos)) {
            return [];
        }

        $limpios = [];

        foreach ($campos as $hoja => $propuestos) {
            if (!is_array($propuestos)) {
                continue;
            }

            foreach ($propuestos as $campo => $valor) {
                $permitidos = self::CAMPOS_PERMITIDOS[$hoja] ?? [];

                if (!in_array($campo, $permitidos, true)) {
                    $advertencias[] = "Campo no permitido descartado: {$hoja}.{$campo}";
                    continue;
                }

                if ($valor === null || $valor === '' || $valor === []) {
                    continue;
                }

                if ($campo === 'aptitud' && !in_array($valor, self::APTITUDES, true)) {
                    $advertencias[] = "Aptitud no válida descartada: {$valor}";
                    continue;
                }

                if ($campo === 'tipo_hipoacusia' && !in_array($valor, self::TIPOS_HIPOACUSIA, true)) {
                    $advertencias[] = "Tipo de hipoacusia no válido descartado: {$valor}";
                    continue;
                }

                $limpios[$hoja][$campo] = $valor;
            }
        }

        return $limpios;
    }

    /**
     * Números que la IA cita en el texto y que no aparecen en la ficha.
     * Solo genera advertencias; no borra el texto.
     */
    private function numerosNoAnclados(array $hoja4, array $contexto): array
    {
        $texto = implode(' ', array_filter(
            [$hoja4['valoracion'] ?? '', $hoja4['interpretacion'] ?? '', $hoja4['restricciones'] ?? ''],
            'is_string'
        ));

        preg_match_all('/\d+(?:[.,]\d+)?/', $texto, $coincidencias);

        $fichaTexto = json_encode($contexto, JSON_UNESCAPED_UNICODE) ?: '';
        $noEncontrados = [];

        foreach (array_unique($coincidencias[0]) as $numero) {
            $normalizado = str_replace(',', '.', $numero);

            if (!str_contains($fichaTexto, $numero) && !str_contains($fichaTexto, $normalizado)) {
                $noEncontrados[] = $numero;
            }
        }

        return $noEncontrados;
    }

    /**
    * Convierte la propuesta en una lista de textos limpios.
    * Si las restricciones ya mencionan otorrinolaringología, quita esa recomendación para no repetirla.
    */
    private function normalizarRecomendaciones(mixed $valor, mixed $restricciones): array
    {
        $lista = is_string($valor) ? [$valor] : (is_array($valor) ? $valor : []);
        $orlEnRestricciones = is_string($restricciones)
            && str_contains(mb_strtolower($restricciones), 'otorrinolaring');

        $limpias = [];
        foreach ($lista as $texto) {
            if (!is_string($texto)) {
                continue;
            }
            $texto = trim($texto);
            if ($texto === '') {
                continue;
            }
            if ($orlEnRestricciones && str_contains(mb_strtolower($texto), 'otorrinolaring')) {
                continue;
            }
            $limpias[] = $texto;
        }

        return $limpias;
    }
}