<?php

namespace App\Services;

use App\Models\SintomaDetectado;
use App\Models\EvaluacionIA;
use App\Models\AlertaClinica;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IAClinicaService
{
    public function analizarTranscripcion($texto, $consulta)
    {
        $data = $this->consultarIA($texto);

        if (!is_array($data) || !isset($data['sintomas'], $data['diagnostico'])) {
            Log::error('IA devolvió respuesta inválida', ['respuesta' => $data]);
            return [
                'diagnostico_probable' => 'No se pudo determinar',
                'nivel_riesgo' => 'desconocido',
                'recomendaciones' => ['No se pudo completar el análisis. Intenta nuevamente.'],
                'alertas' => [],
            ];
        }

        foreach ($data['sintomas'] as $sintoma) {
            SintomaDetectado::updateOrCreate(
                ['consulta_id' => $consulta->id, 'nombre_sintoma' => $sintoma],
                ['consulta_folio' => $consulta->folio, 'session_uuid' => $consulta->session_uuid, 'origen' => 'ia']
            );
        }

        EvaluacionIA::create([
            'consulta_id' => $consulta->id,
            'consulta_folio' => $consulta->folio,
            'session_uuid' => $consulta->session_uuid,
            'sintomas_detectados' => implode(', ', $data['sintomas']),
            'diagnostico_probable' => $data['diagnostico'],
            'recomendacion' => $data['recomendacion'],
            'confianza' => $data['confianza']
        ]);

        $alertasDetectadas = $data['alertas'] ?? [];
        $nivelRiesgo = $this->calcularNivelRiesgo($alertasDetectadas);

        foreach ($alertasDetectadas as $alerta) {
            if (empty($alerta['titulo'])) continue;

            AlertaClinica::create([
                'consulta_id' => $consulta->id,
                'consulta_folio' => $consulta->folio,
                'session_uuid' => $consulta->session_uuid,
                'paciente_id' => $consulta->paciente_id,
                'tipo_alerta' => $alerta['tipo'] ?? 'otro',
                'titulo' => $alerta['titulo'],
                'descripcion' => $alerta['descripcion'] ?? '',
                'nivel' => $alerta['nivel'] ?? 'bajo',
                'nivel_riesgo' => $alerta['nivel'] ?? 'bajo',
                'estado' => 'pendiente',
                'generada_por_ia' => 1,
                'requiere_atencion' => 1,
                'fecha_alerta' => now()
            ]);
        }

        return [
            'diagnostico_probable' => $data['diagnostico'],
            'nivel_riesgo' => $nivelRiesgo,
            'recomendaciones' => [$data['recomendacion']],
            'confianza' => $data['confianza'] ?? null,
            'sintomas' => $data['sintomas'],
            'alertas' => $alertasDetectadas,
        ];
    }

    private function calcularNivelRiesgo(array $alertas)
    {
        $orden = ['bajo' => 1, 'medio' => 2, 'alto' => 3];
        $maxNivel = 'bajo';

        foreach ($alertas as $alerta) {
            $nivel = $alerta['nivel'] ?? 'bajo';
            if (($orden[$nivel] ?? 1) > ($orden[$maxNivel] ?? 1)) {
                $maxNivel = $nivel;
            }
        }

        return $maxNivel;
    }

    /**
     * Sugerencia de la IA con triage clínico completo (fases 1-8).
     *
     * IMPORTANTE: esto es apoyo de decisión para el médico, NUNCA un
     * diagnóstico definitivo ni una dosis/marca específica. El nivel de
     * triage y el tipo (receta_inteligente|derivacion) determinan el
     * flujo que sigue el frontend, y siempre debe mostrarse marcado
     * como "sugerencia de IA, no verificada" en la interfaz.
     *
     * Estructura del JSON devuelto:
     * {
     *   triage: { nivel, justificacion, signos_alarma[] },
     *   diagnosticos_probables: [ ... hasta 3 ... ],
     *   tipo: "receta_inteligente" | "derivacion",
     *   palabras_clave_busqueda: [ ... ],
     *   medicamentos_sugeridos: [ { nombre, descripcion } ... ],
     *   especialidad: "",
     *   motivo_derivacion: "",
     *   requiere_urgencias: bool,
     *   justificacion: ""
     * }
     */
    public function sugerirMedicamentoLibre(array $sintomas)
    {
        $textoSintomas = implode(', ', $sintomas);

        $prompt = "

        Eres un asistente clínico de Inteligencia Artificial utilizado EXCLUSIVAMENTE como apoyo para médicos durante consultas PRESENCIALES.

        IMPORTANTE:

        - No sustituyes el criterio del médico.
        - No emites diagnósticos definitivos.
        - Tus respuestas son únicamente sugerencias clínicas.
        - Basa todas tus recomendaciones en evidencia médica general.
        - Analiza únicamente la información proporcionada por el paciente.

        El paciente refiere los siguientes síntomas:

        $textoSintomas

        =========================================================
        FASE 1 - EXTRACCIÓN DE INFORMACIÓN
        =========================================================

        Identifica:

        - Motivo de consulta.
        - Mecanismo de lesión (si existe).
        - Síntomas.
        - Región anatómica afectada.
        - Tiempo de evolución (si fue mencionado).
        - Intensidad del dolor (si fue mencionada).
        - Antecedentes importantes mencionados.
        - Alergias mencionadas.
        - Medicamentos actuales (si fueron mencionados).

        =========================================================
        FASE 2 - DETECCIÓN DE SIGNOS DE ALARMA
        =========================================================

        Busca si existen uno o más signos de alarma.

        Considera signos de alarma como:

        • accidente automovilístico
        • accidente en motocicleta
        • caída desde altura
        • traumatismo de alta energía
        • deformidad visible
        • incapacidad para mover una extremidad
        • pérdida de fuerza
        • pérdida de sensibilidad
        • dolor intenso
        • inflamación rápida
        • hemorragia
        • dificultad respiratoria
        • dolor torácico
        • alteración del estado de conciencia
        • convulsiones
        • fiebre persistente mayor de 39°C
        • rigidez de cuello
        • vómitos persistentes
        • sospecha de fractura
        • sospecha de luxación
        • sospecha de hemorragia interna

        =========================================================
        FASE 3 - TRIAGE
        =========================================================

        Clasifica al paciente en UN SOLO nivel.

        VERDE
        Paciente estable.
        No existen signos de alarma.

        AMARILLO
        Requiere valoración médica prioritaria.
        Existe riesgo moderado.

        NARANJA
        Existe lesión importante.
        Requiere atención urgente.

        ROJO
        Emergencia médica.
        Requiere atención inmediata.

        Justifica siempre el nivel de triage.

        =========================================================
        FASE 4 - DIAGNÓSTICOS PROBABLES
        =========================================================

        Genera máximo tres diagnósticos probables.

        Ordénalos desde el más probable.

        Nunca afirmes que el diagnóstico es definitivo.

        =========================================================
        FASE 5 - DECISIÓN CLÍNICA
        =========================================================

        Debes decidir únicamente entre:

        - receta_inteligente

        o

        - derivacion

        Reglas:

        Si el TRIAGE es:

        VERDE

        Puedes sugerir Receta Inteligente.

        AMARILLO

        Puedes sugerir Receta Inteligente únicamente si no existen signos importantes de alarma.

        NARANJA

        No sugieras receta.

        Genera únicamente Derivación.

        ROJO

        No sugieras receta.

        Genera únicamente Derivación.

        =========================================================
        FASE 6 - RECETA INTELIGENTE
        =========================================================

        Si el resultado es \"receta_inteligente\":

        NO indiques dosis.

        NO indiques frecuencia.

        NO indiques duración.

        NO indiques marcas comerciales.

        Devuelve únicamente palabras clave para buscar medicamentos.

        El sistema realizará una búsqueda en la tabla:

        medicamentos

        utilizando principalmente los campos:

        - medicamentos.nombre
        - medicamentos.nombre_generico
        - medicamentos.descripcion
        - medicamentos.indicaciones

        IMPORTANTE:

        Si el sistema encuentra medicamentos:

        Utiliza únicamente esos medicamentos.

        No inventes medicamentos.

        Si el sistema NO encuentra medicamentos:

        Sugiere hasta tres medicamentos genéricos ampliamente utilizados.

        Para cada uno proporciona una breve descripción.

        No indiques dosis.

        No indiques frecuencia.

        No indiques duración.

        Indica que son sugerencias generales debido a que no existen medicamentos relacionados dentro del inventario.

        =========================================================
        FASE 7 - DERIVACIÓN
        =========================================================

        Si el resultado es \"derivacion\":

        Selecciona la especialidad médica más adecuada.

        El sistema consultará la tabla:

        especialidades

        Si la especialidad existe:

        Utiliza únicamente esa especialidad.

        Si NO existe:

        Sugiere la especialidad médica más adecuada.

        Explica brevemente el motivo.

        Ejemplos:

        Traumatología y Ortopedia

        Urgencias

        Cardiología

        Neurología

        Dermatología

        Ginecología

        Pediatría

        Otorrinolaringología

        Oftalmología

        Urología

        Neumología

        Psiquiatría

        =========================================================
        FASE 8 - RESPUESTA
        =========================================================

        Devuelve EXCLUSIVAMENTE el siguiente JSON.

        {

        \"triage\":{
        \"nivel\":\"VERDE|AMARILLO|NARANJA|ROJO\",
        \"justificacion\":\"texto\",
        \"signos_alarma\":[]
        },

        \"diagnosticos_probables\":[
        \"\",
        \"\",
        \"\"
        ],

        \"tipo\":\"receta_inteligente|derivacion\",

        \"palabras_clave_busqueda\":[
        \"\",
        \"\",
        \"\"
        ],

        \"medicamentos_sugeridos\":[
        {
        \"nombre\":\"\",
        \"descripcion\":\"\"
        }
        ],

        \"especialidad\":\"\",

        \"motivo_derivacion\":\"\",

        \"requiere_urgencias\":true,

        \"justificacion\":\"\"

        }

        =========================================================
        REGLAS IMPORTANTES
        =========================================================

        - Responde únicamente con JSON.
        - No escribas explicaciones fuera del JSON.
        - No uses Markdown.
        - No inventes medicamentos si el sistema encontró medicamentos.
        - No inventes especialidades si el sistema encontró una especialidad.
        - Si no existen medicamentos en la base de datos, proporciona sugerencias generales con una breve descripción.
        - Si no existe la especialidad en la base de datos, sugiere la especialidad médica más apropiada.
        - Prioriza siempre la seguridad del paciente.
        - Ante signos de alarma importantes, prioriza la derivación sobre la receta inteligente.
        - El diagnóstico siempre es probable y nunca definitivo.

        ";

        try {
            $response = Http::withToken(config('services.ai.key'))
                ->timeout(20)
                ->post('https://api.deepseek.com/chat/completions', [
                    'model' => 'deepseek-chat',
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'response_format' => ['type' => 'json_object']
                ]);

            if (!$response->successful()) {
                Log::error('Error HTTP al consultar sugerencia libre IA', ['status' => $response->status()]);
                return null;
            }

            return json_decode($response->json('choices.0.message.content'), true);

        } catch (\Exception $e) {
            Log::error('Excepción al consultar sugerencia libre IA: ' . $e->getMessage());
            return null;
        }
    }

    private function consultarIA($texto)
    {
        $prompt = "Eres un asistente clínico. Analiza esta nota médica del paciente: '$texto'.

        Sé ESPECÍFICO, no genérico:
        - En 'diagnostico', nombra la condición probable concreta según los síntomas descritos
          (ej: 'Gastritis aguda probable' en vez de solo 'Malestar digestivo').
        - En 'recomendacion', da una acción concreta y accionable relacionada a los síntomas
          exactos mencionados (ej: 'Evitar alimentos irritantes y controlar en 48h si persiste
          el dolor' en vez de 'Descansar y beber líquidos').
        - Evita respuestas genéricas que servirían para cualquier síntoma.

        Presta especial atención a menciones explícitas de:
        - Alergias a medicamentos, alimentos o sustancias
        - Señales de gravedad o urgencia (dificultad para respirar, dolor de pecho intenso,
          sangrado, pérdida de conciencia, fiebre muy alta persistente)

        Devuelve estrictamente un JSON con esta forma exacta, sin texto adicional:
        {
            \"sintomas\": [\"lista\", \"de\", \"sintomas\"],
            \"diagnostico\": \"diagnostico especifico basado en los sintomas descritos\",
            \"recomendacion\": \"accion concreta y especifica a los sintomas, no generica\",
            \"confianza\": 90,
            \"alertas\": [
                {
                    \"tipo\": \"alergia\" o \"gravedad\" o \"respiratoria\" o \"cardiaca\" o \"otro\",
                    \"titulo\": \"texto corto tipo badge, ej: Alergia a penicilina\",
                    \"descripcion\": \"explicación breve de la alerta\",
                    \"nivel\": \"alto\" o \"medio\" o \"bajo\"
                }
            ]
        }

        Si no se detecta ninguna alergia ni señal de gravedad, \"alertas\" debe ser un array vacío [].
        No inventes alergias ni gravedad que el paciente no haya mencionado explícitamente.";

        try {
            $response = Http::withToken(config('services.ai.key'))
                ->timeout(20)
                ->post('https://api.deepseek.com/chat/completions', [
                    'model' => 'deepseek-chat',
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'response_format' => ['type' => 'json_object']
                ]);

            if (!$response->successful()) {
                Log::error('Error HTTP al consultar IA', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            return json_decode($response->json('choices.0.message.content'), true);

        } catch (\Exception $e) {
            Log::error('Excepción al consultar IA: ' . $e->getMessage());
            return null;
        }
    }
}