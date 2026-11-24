<?php

namespace App\Services\ClinicaTrabajo;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IAServiceFichaOcupacional
{
    private const MODELO = 'gemini-3.5-flash-lite';
    private const MAX_CARACTERES_BARRIDO = 30000;
    private const APTITUDES = ['apto', 'apto_con_restricciones', 'no_apto'];
    private const SEGUIMIENTOS = ['3m', '6m', '12m', 'extraordinaria'];

    /** Claves que no son datos clínicos y no entran al barrido. */
    private const EXCLUIR_BARRIDO = ['paciente_id', 'conversacion', 'aptitud', 'aptitud_hoja'];

    /** Datos de identidad de datos_puesto que no se envían a la IA (la edad ya va en DATOS CALCULADOS). */
    private const IDENTIDAD_BARRIDO = [
        'nombre', 'apellido_paterno', 'apellido_materno', 'fecha_nacimiento',
        'telefono', 'celular', 'lugar_nacimiento', 'tipo_sanguineo',
        'emergencia_nombre', 'emergencia_telefono', 'emergencia_relacion',
    ];

    /**
     * Pipeline automático: diagnóstico -> aptitud -> recomendaciones y seguimiento -> dictamen final.
     * Cada paso usa los resultados validados de los anteriores. Si un paso falla, la cadena se detiene.
     * No guarda nada: el médico revisa y aplica.
     *
     * @return array{respuesta: ?string, campos_completados: array, advertencias: array}
     */
    public function proponer(array $datos): array
    {
        set_time_limit(1200);

        $pasos = [];

        $diagnostico = $this->proponerDiagnostico($datos);
        $pasos[] = $diagnostico;
        if (!isset($diagnostico['campos']['diagnostico'])) {
            return $this->combinar($pasos);
        }

        $aptitud = $this->proponerAptitud($datos, $diagnostico['campos']);
        $pasos[] = $aptitud;
        if ($aptitud['campos'] === []) {
            return $this->combinar($pasos);
        }

        $recomendaciones = $this->proponerRecomendaciones($datos, $diagnostico['campos'], $aptitud['campos']);
        $pasos[] = $recomendaciones;
        if ($recomendaciones['campos'] === []) {
            return $this->combinar($pasos);
        }

        $pasos[] = $this->proponerDictamenFinal(
            $datos,
            $diagnostico['campos'],
            $aptitud['campos'],
            $recomendaciones['campos']
        );

        return $this->combinar($pasos);
    }

    /** Paso 1: diagnóstico clínico (hoja 4.1). */
    public function proponerDiagnostico(array $datos): array
    {
        $ctx = $this->contexto($datos);
        $data = $this->llamarGemini($this->promptDiagnostico($ctx), 'diagnostico');

        if ($data === null) {
            return $this->fallo('el diagnóstico');
        }

        $propuestos = is_array($data['diagnosticos'] ?? null) ? $data['diagnosticos'] : [];
        [$validos, $motivos] = $this->validarDiagnosticos($propuestos, $ctx['barrido'], $ctx['calculos']);

        $diagnosticoTexto = $validos === [] ? null : implode('; ', array_map(
            fn ($d) => $d['certeza'] === 'sugestivo' ? $d['diagnostico'] . ' (sugestivo)' : $d['diagnostico'],
            $validos
        ));

        return [
            'respuesta' => $this->texto($data['respuesta'] ?? null) ?? 'Diagnóstico propuesto para tu revisión.',
            'campos' => array_filter([
                'diagnostico' => $this->texto($diagnosticoTexto),
                'cie11_termino' => $this->texto($data['cie11_termino'] ?? null, 120),
                'hallazgos_relevantes_aptitud' => $this->texto($data['hallazgos_relevantes_aptitud'] ?? null),
            ], fn ($v) => $v !== null),
            'advertencias' => array_merge($this->advertencias($data['advertencias'] ?? []), $motivos),
        ];
    }

    /** Paso 2: aptitud laboral y restricciones (hoja 4.2), a partir del diagnóstico. */
    public function proponerAptitud(array $datos, array $diagnostico): array
    {
        $ctx = $this->contexto($datos);
        $data = $this->llamarGemini($this->promptAptitud($ctx, $diagnostico), 'aptitud');

        if ($data === null) {
            return $this->fallo('la aptitud');
        }

        $c = is_array($data['campos'] ?? null) ? $data['campos'] : [];
        $aptitud = in_array($c['aptitud'] ?? null, self::APTITUDES, true) ? $c['aptitud'] : null;

        if ($aptitud === null) {
            return [
                'respuesta' => 'La IA no devolvió una aptitud válida. Selecciónala manualmente.',
                'campos' => [],
                'advertencias' => ['La IA no devolvió una aptitud válida.'],
            ];
        }

        $restricciones = $aptitud === 'apto' ? null : $this->texto($c['restricciones'] ?? null);
        $advertencias = $this->advertencias($data['advertencias'] ?? []);

        if ($aptitud === 'apto_con_restricciones' && $restricciones === null) {
            $advertencias[] = 'Falta redactar las restricciones para la aptitud con restricciones.';
        }

        return [
            'respuesta' => $this->texto($data['respuesta'] ?? null) ?? 'Aptitud propuesta para tu revisión.',
            'campos' => array_filter([
                'aptitud' => $aptitud,
                'restricciones' => $restricciones,
            ], fn ($v) => $v !== null),
            'advertencias' => $advertencias,
        ];
    }

    /** Paso 3: recomendaciones, derivación y seguimiento (hoja 4.3), a partir de diagnóstico y aptitud. */
    public function proponerRecomendaciones(array $datos, array $diagnostico, array $aptitud): array
    {
        $ctx = $this->contexto($datos);
        $data = $this->llamarGemini($this->promptRecomendaciones($ctx, $diagnostico, $aptitud), 'recomendaciones');

        if ($data === null) {
            return $this->fallo('las recomendaciones y el seguimiento');
        }

        $c = is_array($data['campos'] ?? null) ? $data['campos'] : [];
        $seguimiento = in_array($c['proximo_seguimiento'] ?? null, self::SEGUIMIENTOS, true) ? $c['proximo_seguimiento'] : null;
        $advertencias = $this->advertencias($data['advertencias'] ?? []);

        if ($seguimiento === null) {
            $advertencias[] = 'La IA no devolvió un seguimiento válido. Selecciónalo manualmente.';
        }

        return [
            'respuesta' => $this->texto($data['respuesta'] ?? null) ?? 'Recomendaciones propuestas para tu revisión.',
            'campos' => array_filter([
                'recomendaciones' => $this->texto($c['recomendaciones'] ?? null),
                'plan_intervencion' => $this->texto($c['plan_intervencion'] ?? null),
                'derivaciones_especialista' => $this->texto($c['derivaciones_especialista'] ?? null),
                'proximo_seguimiento' => $seguimiento,
            ], fn ($v) => $v !== null),
            'advertencias' => $advertencias,
        ];
    }

    /** Paso 4: conclusión formal y observaciones (hoja 4.4), a partir de los pasos anteriores. */
    public function proponerDictamenFinal(array $datos, array $diagnostico, array $aptitud, array $recomendaciones): array
    {
        $ctx = $this->contexto($datos);
        $data = $this->llamarGemini(
            $this->promptDictamen($ctx, $diagnostico, $aptitud, $recomendaciones),
            'dictamen'
        );

        if ($data === null) {
            return $this->fallo('el dictamen final');
        }

        $c = is_array($data['campos'] ?? null) ? $data['campos'] : [];

        return [
            'respuesta' => $this->texto($data['respuesta'] ?? null) ?? 'Dictamen propuesto para tu revisión.',
            'campos' => array_filter([
                'dictamen_final' => $this->texto($c['dictamen_final'] ?? null),
                'observaciones' => $this->texto($c['observaciones'] ?? null) ?? 'Sin observaciones adicionales.',
            ], fn ($v) => $v !== null),
            'advertencias' => $this->advertencias($data['advertencias'] ?? []),
        ];
    }

    /** Une los resultados de los pasos ejecutados. */
    private function combinar(array $pasos): array
    {
        $campos = [];
        $advertencias = [];
        $respuesta = null;

        foreach ($pasos as $paso) {
            $campos = array_merge($campos, $paso['campos']);
            $advertencias = array_merge($advertencias, $paso['advertencias']);
            $respuesta = $paso['respuesta'];
        }

        return [
            'respuesta' => $respuesta,
            'campos_completados' => $campos,
            'advertencias' => array_values(array_unique($advertencias)),
        ];
    }

    // =========================================================
    // CONTEXTO: datos calculados + barrido completo
    // =========================================================

    private function contexto(array $datos): array
    {
        return [
            'calculos' => $this->calculos($datos),
            'barrido' => $this->barrido($datos),
        ];
    }

    /** Datos que se derivan con reglas fijas, no con la IA. */
    private function calculos(array $datos): array
    {
        $dp = $datos['datos_puesto'] ?? [];
        $ce = $datos['clinico_examen'] ?? [];

        $evaluacion = $this->fecha($dp['fecha_evaluacion'] ?? null) ?? Carbon::now();
        $nacimiento = $this->fecha($dp['fecha_nacimiento'] ?? null);
        $edad = $nacimiento ? (int) $nacimiento->diffInYears($evaluacion, true) : null;

        $peso = $this->num($ce['antropometria']['peso'] ?? null);
        $talla = $this->num($ce['antropometria']['talla'] ?? null);
        $imc = ($peso && $talla) ? round($peso / (($talla / 100) ** 2), 1) : null;

        $ta = ($ce['signos_vitales']['ta'] ?? '') ?: ($dp['signos_vitales']['presion_arterial'] ?? null);
        [$sistolica, $diastolica] = $this->parsePresion($ta);

        $alertas = [];

        $spo2 = $this->num($ce['signos_vitales']['saturacion'] ?? null) ?? $this->num($dp['signos_vitales']['saturacion_oxigeno'] ?? null);
        $temp = $this->num($ce['signos_vitales']['temperatura'] ?? null) ?? $this->num($dp['signos_vitales']['temperatura'] ?? null);
        $fc = $this->num($ce['signos_vitales']['fc'] ?? null) ?? $this->num($dp['signos_vitales']['frecuencia_cardiaca'] ?? null);
        $glucosa = $this->num($ce['signos_vitales']['glucosa'] ?? null) ?? $this->num($dp['signos_vitales']['glucosa'] ?? null);

        if ($spo2 !== null && $spo2 < 95) {
            $alertas[] = 'SpO2 menor a 95 %.';
        }
        if ($temp !== null && $temp >= 38) {
            $alertas[] = 'Temperatura igual o mayor a 38 °C.';
        }
        if ($fc !== null && ($fc > 100 || $fc < 50)) {
            $alertas[] = 'Frecuencia cardiaca fuera de 50 a 100 lpm.';
        }
        if ($glucosa !== null && $glucosa >= 126) {
            $alertas[] = 'Glucosa igual o mayor a 126 mg/dL (compatible con diabetes si se confirma en ayuno).';
        } elseif ($glucosa !== null && $glucosa >= 100) {
            $alertas[] = 'Glucosa entre 100 y 125 mg/dL (alterada si fue en ayuno).';
        }

        return [
            'edad_anos' => $edad,
            'genero' => $dp['genero'] ?? null,
            'imc' => $imc,
            'imc_clasificacion' => $imc === null ? null : $this->clasificarImc($imc),
            'presion_arterial' => ($sistolica && $diastolica) ? "{$sistolica}/{$diastolica} mmHg" : null,
            'presion_clasificacion' => $this->clasificarPresion($sistolica, $diastolica),
            'alertas' => $alertas,
        ];
    }

    /**
     * Recorre toda la ficha y devuelve una línea por dato capturado (ruta: valor).
     * Se omiten los valores vacíos, nulos y los booleanos en false; el resto se envía tal como está.
     */
    private function barrido(array $datos): string
    {
        $base = array_diff_key($datos, array_flip(self::EXCLUIR_BARRIDO));
        if (isset($base['datos_puesto']) && is_array($base['datos_puesto'])) {
            $base['datos_puesto'] = array_diff_key($base['datos_puesto'], array_flip(self::IDENTIDAD_BARRIDO));
        }

        $lineas = [];

        $this->recorrer($base, '', $lineas);

        $completo = implode("\n", $lineas);

        if (mb_strlen($completo) > self::MAX_CARACTERES_BARRIDO) {
            Log::warning('IAServiceFichaOcupacional: barrido truncado a ' . self::MAX_CARACTERES_BARRIDO . ' caracteres');
        }

        return mb_substr($completo, 0, self::MAX_CARACTERES_BARRIDO);
    }

    private function recorrer(mixed $valor, string $ruta, array &$lineas): void
    {
        if (is_array($valor)) {
            foreach ($valor as $clave => $hijo) {
                $this->recorrer($hijo, $ruta === '' ? (string) $clave : "{$ruta}.{$clave}", $lineas);
            }
            return;
        }

        if ($valor === null || $valor === '' || $valor === false) {
            return;
        }

        $lineas[] = $ruta . ': ' . (is_bool($valor) ? 'sí' : (string) $valor);
    }

    // =========================================================
    // PROMPTS
    // =========================================================

    private function promptDiagnostico(array $ctx): string
    {
        $calculos = $this->json($ctx['calculos']);
        $barrido = $ctx['barrido'];

        return <<<PROMPT
            Eres asistente de un médico del trabajo. Propones el diagnóstico clínico de una valoración de medicina ocupacional. El médico revisará y validará todo antes de guardar.

            Reglas:
            - Revisa TODO el barrido: antecedentes, heredofamiliares, no patológicos, gineco/urológico, vacunas, alergias, comorbilidades, signos vitales, antropometría, todos los sistemas del examen, pruebas complementarias, exposición y puesto. No omitas ningún dato positivo.
            - Considera la edad ({$ctx['calculos']['edad_anos']} años) y el sexo al interpretar cada hallazgo. Si un hallazgo puede ser un cambio esperable por la edad, indícalo en advertencias.
            - Usa los datos calculados como referencia y confírmalos con el barrido. Usa imc_clasificacion y presion_clasificacion tal cual, sin reclasificarlas.
            - No inventes datos ni cifras. Si falta un dato clínico importante, indícalo en advertencias.
            - Solo propón un diagnóstico si lo respalda al menos un dato del barrido o de los datos calculados. En "evidencia" incluye las rutas exactas de esos datos (por ejemplo "clinico_examen.antropometria.peso" o "calculos.imc").
            - Una cifra tensional elevada en una sola lectura NO es hipertensión arterial. Descríbela como hallazgo, no como diagnóstico, salvo que el barrido tenga un antecedente personal diagnosticado (ruta antecedentes.patologicos.*).
            - certeza: "confirmado" si el dato es concluyente; "sugestivo" si solo es compatible, y usa lenguaje de sospecha ("compatible con", "sugestivo de") en ese caso.
            - cie11_termino: término corto del diagnóstico principal, en español, para buscar en CIE-11 (por ejemplo "Sobrepeso"). No escribas código ni título oficial.
            - hallazgos_relevantes_aptitud: hallazgos que impactan la aptitud laboral (funcionales, de exposición o de limitación en el puesto).
            - No propongas aptitud, restricciones ni recomendaciones en este paso.

            DATOS CALCULADOS:
            {$calculos}

            BARRIDO DE LA FICHA (cada línea es un dato capturado):
            {$barrido}

            Responde solo JSON con esta forma:
            {"respuesta": "resumen breve para el médico", "diagnosticos": [{"diagnostico": "texto", "certeza": "confirmado o sugestivo", "evidencia": ["ruta.del.dato"]}], "cie11_termino": "texto", "hallazgos_relevantes_aptitud": "texto", "advertencias": ["texto"]}
            PROMPT;
    }

     /**
     * Descarta diagnósticos sin respaldo verificable en la ficha.
     * Devuelve [diagnósticos válidos, motivos de descarte].
     */
    private function validarDiagnosticos(array $propuestos, string $barrido, array $calculos): array
    {
        $validos = [];
        $motivos = [];

        foreach ($propuestos as $p) {
            if (!is_array($p)) {
                continue;
            }
            $nombre = $this->texto($p['diagnostico'] ?? null, 200);
            if ($nombre === null) {
                continue;
            }

            $certeza = ($p['certeza'] ?? '') === 'sugestivo' ? 'sugestivo' : 'confirmado';
            $evidencia = is_array($p['evidencia'] ?? null) ? $p['evidencia'] : [];

            $rutas = array_values(array_filter(
                array_map(fn ($r) => is_string($r) ? trim($r) : '', $evidencia),
                fn ($r) => $r !== '' && $this->rutaExiste($r, $barrido, $calculos)
            ));

            if ($rutas === []) {
                $motivos[] = "Diagnóstico descartado por falta de respaldo en la ficha: {$nombre}.";
                continue;
            }

            $tieneAntecedente = collect($rutas)->contains(fn ($r) => str_starts_with($r, 'antecedentes.patologicos.'));
            if (preg_match('/hipertensi/i', $nombre) && !$tieneAntecedente) {
                $motivos[] = "Diagnóstico descartado: la hipertensión requiere antecedente personal diagnosticado: {$nombre}.";
                continue;
            }

            $validos[] = ['diagnostico' => $nombre, 'certeza' => $certeza];
        }

        return [$validos, $motivos];
    }

    /** Verifica que la ruta citada exista en el barrido o en los datos calculados. */
    private function rutaExiste(string $ruta, string $barrido, array $calculos): bool
    {
        if (str_starts_with($ruta, 'calculos.')) {
            return ($calculos[substr($ruta, strlen('calculos.'))] ?? null) !== null;
        }

        return str_contains("\n" . $barrido . "\n", "\n{$ruta}: ");
    }

    private function promptAptitud(array $ctx, array $diagnostico): string
    {
        $calculos = $this->json($ctx['calculos']);
        $barrido = $ctx['barrido'];
        $diagnosticoJson = $this->json($diagnostico);

        return <<<PROMPT
            Eres asistente de un médico del trabajo. Propones la aptitud laboral de una valoración de medicina ocupacional. El médico revisará y validará todo antes de guardar.

            Reglas:
            - Decide la aptitud a partir del DIAGNÓSTICO CLÍNICO validado y de la exposición y las condiciones del puesto del barrido.
            - aptitud: "apto" si el diagnóstico no limita el puesto; "apto_con_restricciones" si hay una limitación controlable con medidas concretas; "no_apto" si el diagnóstico impide desempeñar el puesto actual con riesgo para el trabajador.
            - restricciones: obligatorias si la aptitud es "apto_con_restricciones". Describe limitaciones concretas (carga, postura, altura, turnos, exposiciones) ligadas al diagnóstico y a los riesgos del puesto. Si la aptitud es "apto" o "no_apto", cadena vacía.
            - Considera la edad del paciente ({$ctx['calculos']['edad_anos']} años) en el riesgo del puesto.
            - No propongas recomendaciones, derivaciones ni seguimiento en este paso.
            - No inventes datos. Si falta información importante, indícalo en advertencias.

            DATOS CALCULADOS:
            {$calculos}

            DIAGNÓSTICO CLÍNICO VALIDADO:
            {$diagnosticoJson}

            BARRIDO DE LA FICHA:
            {$barrido}

            Responde solo JSON con esta forma:
            {"respuesta": "resumen breve para el médico", "campos": {"aptitud": "apto", "restricciones": ""}, "advertencias": ["texto"]}
            PROMPT;
    }

    private function promptRecomendaciones(array $ctx, array $diagnostico, array $aptitud): string
    {
        $calculos = $this->json($ctx['calculos']);
        $barrido = $ctx['barrido'];
        $diagnosticoJson = $this->json($diagnostico);
        $aptitudJson = $this->json($aptitud);

        return <<<PROMPT
            Eres asistente de un médico del trabajo. Propones las recomendaciones médicas y el seguimiento de una valoración de medicina ocupacional. El médico revisará y validará todo antes de guardar.

            Reglas:
            - Las recomendaciones deben responder al DIAGNÓSTICO CLÍNICO y a la APTITUD ya validados. No los cambies.
            - recomendaciones: medidas médicas y de control concretas, ligadas al diagnóstico y a la aptitud. Evita recomendaciones genéricas.
            - derivaciones_especialista: especialidad y motivo, solo si el diagnóstico lo justifica; si no, cadena vacía.
            - plan_intervencion: acciones de seguimiento concretas, con responsable y plazo, que den continuidad a las recomendaciones (por ejemplo, revisiones de control, verificación del cumplimiento de restricciones o seguimiento de estudios pendientes). No repita textualmente las recomendaciones.
            - proximo_seguimiento: "3m", "6m", "12m" o "extraordinaria". Coherente con la aptitud: "no_apto" o un hallazgo que requiera atención pronta, "3m" o "extraordinaria"; "apto_con_restricciones", "3m" o "6m"; "apto" sin hallazgos relevantes, "12m".
            - Considera la edad del paciente ({$ctx['calculos']['edad_anos']} años).
            - No inventes datos. Si falta información importante, indícalo en advertencias.

            DATOS CALCULADOS:
            {$calculos}

            DIAGNÓSTICO CLÍNICO VALIDADO:
            {$diagnosticoJson}

            APTITUD VALIDADA:
            {$aptitudJson}

            BARRIDO DE LA FICHA:
            {$barrido}

            Responde solo JSON con esta forma:
            {"respuesta": "resumen breve para el médico", "campos": {"recomendaciones": "texto", "plan_intervencion": "texto", "derivaciones_especialista": "", "proximo_seguimiento": "12m"}, "advertencias": ["texto"]}
            PROMPT;
    }

    private function promptDictamen(array $ctx, array $diagnostico, array $aptitud, array $recomendaciones): string
    {
        $calculos = $this->json($ctx['calculos']);
        $barrido = $ctx['barrido'];
        $diagnosticoJson = $this->json($diagnostico);
        $aptitudJson = $this->json($aptitud);
        $recomendacionesJson = $this->json($recomendaciones);

        return <<<PROMPT
            Eres asistente de un médico del trabajo. Redactas la conclusión formal del dictamen de una valoración de medicina ocupacional. El médico revisará y validará todo antes de guardar.

            Reglas:
            - Diagnóstico, aptitud, recomendaciones y seguimiento ya fueron validados en los pasos anteriores. No los cambies.
            - dictamen_final: conclusión formal que integre, en este orden, el diagnóstico clínico, la aptitud, las restricciones y las recomendaciones con su seguimiento. Tono clínico, en español, sin prometer resultados. Es parte del registro médico oficial.
            - observaciones: siempre llénalo con notas útiles para el expediente que no estén ya en la conclusión: seguimiento específico, consideraciones de la exposición o del puesto, datos pendientes por obtener y advertencias clínicas relevantes. No repitas lo que ya dice dictamen_final. Si de verdad no hay nada que agregar, escribe "Sin observaciones adicionales."
            - No inventes datos. Si falta información importante, indícalo en advertencias.

            DATOS CALCULADOS:
            {$calculos}

            DIAGNÓSTICO CLÍNICO:
            {$diagnosticoJson}

            APTITUD Y RESTRICCIONES:
            {$aptitudJson}

            RECOMENDACIONES Y SEGUIMIENTO:
            {$recomendacionesJson}

            BARRIDO DE LA FICHA:
            {$barrido}

            Responde solo JSON con esta forma:
            {"respuesta": "resumen breve para el médico", "campos": {"dictamen_final": "texto", "observaciones": ""}, "advertencias": ["texto"]}
            PROMPT;
    }

    // =========================================================
    // CLIENTE DE GEMINI (mismo patrón que IAServiceErgonomia)
    // =========================================================

    private function llamarGemini(string $prompt, string $paso): ?array
    {
        try {
            $apiKey = env('GEMINI_API_KEY');
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . self::MODELO . ':generateContent';

            $response = $this->conReintentos($url, $apiKey, [['text' => $prompt]]);

            if (!$response->successful()) {
                Log::error("Error HTTP al consultar IA de ficha ocupacional ({$paso})", [
                    'status' => $response->status(),
                ]);
                return null;
            }

            return $this->decodificarJson($response->json('candidates.0.content.parts.0.text'), $paso);
        } catch (\Throwable $e) {
            Log::error("Excepción en IAServiceFichaOcupacional ({$paso}): " . $e->getMessage());
            return null;
        }
    }

    /** Reintenta ante errores transitorios (429, 500, 503) con espera exponencial. */
    private function conReintentos(string $url, string $apiKey, array $partes, int $maxIntentos = 2)
    {
        $espera = 2;

        for ($intento = 1; ; $intento++) {
                $response = Http::timeout(120)->withHeaders(['x-goog-api-key' => $apiKey])->post($url, [
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

    private function decodificarJson(?string $contenido, string $paso): ?array
    {
        if ($contenido === null || trim($contenido) === '') {
            Log::error("IAServiceFichaOcupacional: respuesta vacía ({$paso})");
            return null;
        }

        $limpio = trim(preg_replace('/```(?:json)?/', '', $contenido));
        $data = json_decode($limpio, true);

        if (!is_array($data)) {
            Log::warning("IAServiceFichaOcupacional: JSON inválido ({$paso})");
            return null;
        }

        return $data;
    }

    // =========================================================
    // UTILIDADES
    // =========================================================

    private function fallo(string $paso): array
    {
        return [
            'respuesta' => "No pude generar la propuesta de {$paso}. Intenta de nuevo.",
            'campos' => [],
            'advertencias' => ["La IA no respondió en {$paso}."],
        ];
    }

    private function advertencias(mixed $advertencias): array
    {
        if (!is_array($advertencias)) {
            return [];
        }

        return array_slice(array_values(array_filter(array_map(fn ($a) => $this->texto($a, 500), $advertencias))), 0, 10);
    }

    private function parsePresion(mixed $ta): array
    {
        if (!is_string($ta) || !preg_match('/(\d{2,3})\s*\/\s*(\d{2,3})/', $ta, $m)) {
            return [null, null];
        }

        return [(int) $m[1], (int) $m[2]];
    }

    /** Clasificación de referencia (ACC/AHA 2017), con lectura única. */
    private function clasificarPresion(?int $sistolica, ?int $diastolica): ?string
    {
        if ($sistolica === null || $diastolica === null) {
            return null;
        }

        return match (true) {
            $sistolica > 180 || $diastolica > 120 => 'Crisis hipertensiva (confirmar urgencia)',
            $sistolica >= 140 || $diastolica >= 90 => 'Cifra en rango de hipertensión etapa 2 (lectura única; no es diagnóstico)',
            $sistolica >= 130 || $diastolica >= 80 => 'Cifra en rango de hipertensión etapa 1 (lectura única; no es diagnóstico)',
            $sistolica >= 120 => 'Elevada',
            default => 'Normal',
        };
    }

    private function clasificarImc(float $imc): string
    {
        return match (true) {
            $imc < 18.5 => 'Bajo peso',
            $imc < 25 => 'Normal',
            $imc < 30 => 'Sobrepeso',
            $imc < 35 => 'Obesidad I',
            $imc < 40 => 'Obesidad II',
            default => 'Obesidad III',
        };
    }

    private function fecha(mixed $valor): ?Carbon
    {
        if (!is_string($valor) || trim($valor) === '') {
            return null;
        }

        try {
            return Carbon::parse($valor);
        } catch (\Throwable) {
            return null;
        }
    }

    private function num(mixed $valor): ?float
    {
        if ($valor === null || $valor === '' || !is_numeric($valor)) {
            return null;
        }

        return (float) $valor;
    }

    private function json(mixed $valor): string
    {
        return json_encode($valor, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '{}';
    }

    private function texto(mixed $valor, int $max = 5000): ?string
    {
        if (!is_string($valor)) {
            return null;
        }

        $texto = trim($valor);

        return $texto === '' ? null : mb_substr($texto, 0, $max);
    }
}