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
        'valoracion', 'interpretacion', 'tipo_hipoacusia', 'restricciones', 'aptitud',
    ];

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

        $prompt = $this->construirPromptRecomendacion($contexto, $paciente);
        $data = $this->llamarGemini($prompt);

        if ($data === null) {
            return $this->respuestaFallida();
        }

        $advertencias = [];
        $campos = $this->sanitizarCampos($data['campos'] ?? [], $advertencias);

        $hoja4 = array_intersect_key($campos['hoja4'] ?? [], array_flip(self::CAMPOS_RECOMENDACION));

        // Sin vía ósea no se puede separar conductiva de neurosensorial
        if (!$this->tieneViaOsea($contexto)) {
            unset($hoja4['tipo_hipoacusia']);
            $advertencias[] = 'Sin vía ósea: no se propone el tipo de hipoacusia.';
        }

        $campos = $hoja4 ? ['hoja4' => $hoja4] : [];

        foreach ($this->numerosNoAnclados($hoja4, $contexto) as $numero) {
            $advertencias[] = "Revisa el número {$numero}: no aparece en la ficha.";
        }

        return [
            'respuesta' => 'Recomendaciones generadas. Revísalas antes de guardar.',
            'campos_completados' => $campos,
            'advertencias' => $advertencias,
        ];
    }

    private function tieneViaOsea(array $contexto): bool
    {
        foreach (($contexto['hoja4'] ?? []) as $campo => $valor) {
            if (str_starts_with($campo, 'via_osea_') && $valor !== null && $valor !== '') {
                return true;
            }
        }

        return false;
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

    private function construirPromptRecomendacion(array $contexto, ?PacientesClinicaTrabajo $paciente): string
    {
        $contextoJson = mb_substr(json_encode($contexto, JSON_UNESCAPED_UNICODE) ?: '{}', 0, 12000);
        $datosPaciente = $this->bloquePaciente($paciente);

        return <<<TXT
        Eres el asistente del módulo de audiología DELLI de Medicina del Trabajo.
        Con la ficha audiológica de abajo, propón la valoración, la interpretación, el tipo de hipoacusia, las restricciones laborales y la aptitud para el médico.

        REGLAS
        - Basa cada propuesta en los datos de la ficha. No inventes antecedentes, síntomas ni resultados.
        - Analiza todas las frecuencias de vía aérea (125 a 8000 Hz), incluidas 125 y 250 Hz, aunque la clasificación use el PTA de 500 a 3000 Hz. Describe el patrón (descendente, con muesca o plano) y las frecuencias donde está la caída.
        - Si la ficha tiene vía ósea, compara cada frecuencia con la vía aérea. Una brecha aéreo-ósea significativa sugiere componente conductivo o mixto. Si no hay vía ósea, indícalo en la interpretación y no afirmes que la hipoacusia es neurosensorial.
        - No calcules PTA ni clasificación nuevos.
        - No menciones exposición a ruido, uso de protección, acúfenos ni otros antecedentes si no aparecen en la ficha.
        - En "valoracion", escribe como máximo tres frases. No repitas cifra por cifra los umbrales, porque ya están en la tabla; apóyate en los hallazgos de vigilancia.
        - Si hay hallazgos de vigilancia, la aptitud no debe ser "Apto" sin restricciones.
        - Si la ficha tiene hallazgos de vigilancia (muesca en agudos, cambio respecto a la audiometría inicial, exposición a ruido muy alto o protección auditiva "Nunca"), no propongas "Apto" sin restricciones.
        - En "valoracion", explica la relación entre el umbral y los hallazgos, para que el médico vea por qué la aptitud no depende solo del PTA.
        - En "restricciones", indica el motivo de la restricción usando esos hallazgos.
        - Cualquier número que cites debe aparecer en la ficha.
        - "tipo_hipoacusia" debe ser exactamente Normal, Neurosensorial, Conductiva o Mixta.
        - "aptitud" debe ser exactamente Apto, Apto con restricciones o No apto.
        - Responde SOLO con JSON válido con esta forma:
        {"campos": {"hoja4": {"valoracion": "...", "interpretacion": "...", "tipo_hipoacusia": "...", "restricciones": "...", "aptitud": "..."}}}

        DATOS DEL PACIENTE (referencia)
        {$datosPaciente}

        FICHA AUDIOLÓGICA
        {$contextoJson}
        TXT;
    }

    private function bloquePaciente(?PacientesClinicaTrabajo $paciente): string
    {
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
}