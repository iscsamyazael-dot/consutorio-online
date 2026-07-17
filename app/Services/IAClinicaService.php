<?php

namespace App\Services;

use App\Models\SintomaDetectado;
use App\Models\EvaluacionIA;
use App\Models\AlertaClinica;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use thiagoalessio\TesseractOCR\TesseractOCR;

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

        // Si la IA no detectó alertas explícitas, igual reflejamos el
        // nivel de riesgo calculado (por defecto "bajo") como una alerta
        // informativa, para que el panel de Alertas Clínicas no quede vacío.
        if (empty($alertasDetectadas)) {
            $alertaBaja = [
                'tipo' => 'otro',
                'titulo' => 'Riesgo clínico bajo',
                'descripcion' => $data['diagnostico'] ?? 'Sin hallazgos de riesgo relevantes.',
                'nivel' => $nivelRiesgo,
            ];

            AlertaClinica::create([
                'consulta_id' => $consulta->id,
                'consulta_folio' => $consulta->folio,
                'session_uuid' => $consulta->session_uuid,
                'paciente_id' => $consulta->paciente_id,
                'tipo_alerta' => $alertaBaja['tipo'],
                'titulo' => $alertaBaja['titulo'],
                'descripcion' => $alertaBaja['descripcion'],
                'nivel' => $alertaBaja['nivel'],
                'nivel_riesgo' => $alertaBaja['nivel'],
                'estado' => 'pendiente',
                'generada_por_ia' => 1,
                'requiere_atencion' => 0,
                'fecha_alerta' => now()
            ]);

            $alertasDetectadas = [$alertaBaja];
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

    /*
    |--------------------------------------------------------------------------
    | LECTURA DE ARCHIVOS ADJUNTOS (PDF, Word, Imágenes)
    |--------------------------------------------------------------------------
    */

    /**
     * Extrae texto plano de un archivo subido (pdf, docx/doc, imagen).
     * Para imágenes usa OCR (Tesseract) ya que la API de DeepSeek es
     * solo de texto y no acepta imágenes directamente.
     */
    public function extraerTextoDeArchivo(string $rutaCompleta, string $mime): string
    {
        try {
            // --- PDF ---
            if ($mime === 'application/pdf') {
                $parser = new PdfParser();
                $pdf = $parser->parseFile($rutaCompleta);
                return trim($pdf->getText());
            }

            // --- Word (.docx / .doc) ---
            if (in_array($mime, [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/msword'
            ])) {
                $phpWord = WordIOFactory::load($rutaCompleta);
                $texto = '';
                foreach ($phpWord->getSections() as $section) {
                    foreach ($section->getElements() as $element) {
                        if (method_exists($element, 'getText')) {
                            $texto .= $element->getText() . "\n";
                        } elseif (method_exists($element, 'getElements')) {
                            // Párrafos con formato mixto (negritas, etc.)
                            foreach ($element->getElements() as $sub) {
                                if (method_exists($sub, 'getText')) {
                                    $texto .= $sub->getText();
                                }
                            }
                            $texto .= "\n";
                        }
                    }
                }
                return trim($texto);
            }

            // --- Imágenes (OCR) ---
            if (str_starts_with($mime, 'image/')) {
                return trim(
                    (new TesseractOCR($rutaCompleta))
                        ->executable('C:\Users\Usuario\Desktop\llave de ultrafarmacia\tesseract.exe')
                        ->lang('spa', 'eng')
                        ->run()
                );
            }

            Log::warning('Tipo de archivo no soportado para extracción', ['mime' => $mime]);
            return '';

        } catch (\Throwable $e) {
            // \Throwable (no solo \Exception) para atrapar también
            // "Class not found" si falta instalar algún paquete de
            // Composer (smalot/pdfparser, phpoffice/phpword, tesseract_ocr).
            Log::error('Error extrayendo texto de archivo: ' . $e->getMessage(), [
                'ruta' => $rutaCompleta,
                'mime' => $mime,
                'clase_error' => get_class($e)
            ]);
            return '';
        }
    }

    /**
     * Extrae el texto de un archivo adjunto y lo analiza con el mismo
     * pipeline clínico que se usa para transcripciones de voz/texto.
     */
    public function analizarArchivoAdjunto(string $rutaCompleta, string $mime, $consulta)
    {
        $textoExtraido = $this->extraerTextoDeArchivo($rutaCompleta, $mime);

        if (empty($textoExtraido)) {
            return [
                'texto_extraido' => '',
                'resultado' => null,
                'error' => 'No se pudo extraer texto legible del archivo.',
            ];
        }

        $resultado = $this->analizarTranscripcion($textoExtraido, $consulta);

        return [
            'texto_extraido' => $textoExtraido,
            'resultado' => $resultado,
            'error' => null,
        ];
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
    public function sugerirMedicamentoLibre(array $sintomas, array $especialidadesDisponibles = [])
    {
        $textoSintomas = implode(', ', $sintomas);

        $textoEspecialidades = !empty($especialidadesDisponibles)
            ? implode(', ', $especialidadesDisponibles)
            : 'No disponible (no se proporcionó catálogo, usa tu mejor criterio clínico general)';

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

        CATÁLOGO REAL DE ESPECIALIDADES DISPONIBLES EN EL SISTEMA:

        $textoEspecialidades

        REGLA OBLIGATORIA:

        - Si el catálogo anterior tiene especialidades listadas, DEBES elegir
          el campo \"especialidad\" copiando EXACTAMENTE uno de esos nombres,
          tal cual aparece escrito. No inventes ni modifiques el nombre.
        - Si ninguna especialidad del catálogo es clínicamente adecuada para
          los síntomas del paciente, usa \"Medicina general\" si está en el
          catálogo, o el nombre más cercano disponible.
        - Solo si el catálogo dice 'No disponible', sugiere libremente la
          especialidad médica más apropiada según los ejemplos de abajo.

        COHERENCIA CLÍNICA OBLIGATORIA (ejemplos de referencia, no exhaustivos):

        - Síntomas digestivos (dolor abdominal, diarrea, náuseas, vómito,
          colon irritado, gastritis, reflujo) -> Gastroenterología o Medicina
          General. NUNCA Odontología/Dentista salvo que el síntoma sea
          dental, de encías o de boca explícitamente.
        - Síntomas óseos/articulares/musculares o traumatismos -> Traumatología.
        - Síntomas respiratorios -> Neumología o Urgencias si es grave.
        - Síntomas cardíacos (dolor torácico, palpitaciones) -> Cardiología.
        - Síntomas neurológicos (convulsiones, pérdida de fuerza, alteración
          de conciencia) -> Neurología o Urgencias si es grave.
        - Síntomas ginecológicos -> Ginecología.
        - Síntomas en menores de edad -> Pediatría.
        - Síntomas de piel -> Dermatología.
        - Síntomas de oído/nariz/garganta -> Otorrinolaringología.
        - Síntomas oculares -> Oftalmología.
        - Síntomas urinarios -> Urología.
        - Ansiedad, depresión, crisis emocional -> Psiquiatría o Psicología.
        - Si el motivo de consulta no calza claramente con ninguna
          especialidad específica -> Medicina General.

        La especialidad elegida SIEMPRE debe tener relación médica directa
        y evidente con los síntomas reportados. Antes de responder, verifica
        que tu elección sea coherente; si no lo es, corrígela.

        Ejemplos genéricos de especialidades (solo como referencia si el
        catálogo no está disponible):

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

        Gastroenterología

        Medicina General

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
                    'model' => 'deepseek-v4-flash',
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
        $prompt = "

Actúa como un asistente médico inteligente especializado en análisis clínico.

Este análisis pertenece a un sistema de expediente médico digital.
Tu función es apoyar al médico identificando síntomas, posibles condiciones,
riesgos y alertas clínicas.

IMPORTANTE:

- No reemplazas al médico.
- No emites diagnósticos definitivos.
- Los diagnósticos son únicamente probabilidades clínicas.
- No inventes información que no esté en la nota médica.
- Si un dato no aparece responde 'No disponible'.


NOTA MÉDICA DEL PACIENTE:

$texto


====================================
FILTRADO DE TRANSCRIPCIÓN
====================================

El texto anterior puede provenir de dos fuentes distintas:

1. Una transcripción automática de audio (relato hablado del paciente).
2. Un documento adjunto ya elaborado por un profesional (por ejemplo,
   un reporte de radiología, laboratorio, o un estudio de imagen), que
   contiene secciones como 'HALLAZGOS', 'IMPRESIÓN DIAGNÓSTICA',
   'RESULTADOS' o similares, en vez de un relato de síntomas.

Si el texto es una TRANSCRIPCIÓN DE AUDIO, ignora por completo:

- Risas o expresiones como 'jaja', 'jeje', '(risas)'.
- Ruido ambiental descrito o transcrito por error: motos, carros, claxon,
  bocinas, sirenas, tráfico, música de fondo.
- Muletillas y relleno verbal: 'este', 'o sea', 'eh', 'mmm', 'like', 'ajá'.
- Interjecciones sin contenido clínico: 'ok', 'perfecto', 'gracias', 'okay doc'.
- Conversación de cortesía o small talk no relacionado con el motivo de consulta
  (saludos, despedidas, comentarios sobre el clima, bromas).
- Fragmentos inaudibles o mal transcritos que no tengan sentido clínico.
- Repeticiones producto de tartamudeo o corrección en el habla (quédate solo
  con la versión final de la frase).

NO uses estos elementos como síntomas, antecedentes ni parte del análisis.
NO los menciones en el JSON de salida.

Si el texto es un REPORTE DE ESTUDIO YA ELABORADO (radiología, laboratorio,
etc.) que no contiene síntomas narrados por el paciente sino hallazgos
clínicos/imagenológicos e impresión diagnóstica:

- Trata cada hallazgo relevante de la sección de HALLAZGOS/RESULTADOS y
  cada conclusión de la IMPRESIÓN DIAGNÓSTICA como si fuera un elemento
  del arreglo 'sintomas' (por ejemplo: 'Aumento del espacio articular
  acromiohumeral', 'Artrosis acromioclavicular'). NO dejes el arreglo
  'sintomas' vacío solo porque el texto no menciona síntomas narrados;
  en este caso los hallazgos del estudio SON el equivalente clínico.
- Ignora datos administrativos que no aportan valor clínico: nombre del
  médico referente, cédula profesional, firma, fecha del documento,
  encabezados de la clínica, frases de cierre ('Atentamente', etc.).
- El campo 'diagnostico' debe reflejar la impresión diagnóstica del
  estudio tal como fue reportada (sin inventar), aclarando que es un
  hallazgo de estudio y no una conclusión clínica integral del médico
  tratante.

Solo analiza el contenido que tenga relevancia clínica real.


====================================
ANÁLISIS DE INFORMACIÓN
====================================

Extrae:

- Síntomas actuales (o hallazgos del estudio, según el caso descrito arriba).
- Duración de síntomas si existe.
- Intensidad si está disponible.
- Zona anatómica afectada.
- Antecedentes relevantes.
- Medicamentos mencionados.
- Alergias mencionadas.
- Factores de riesgo.


====================================
SÍNTOMAS
====================================

Identifica todos los síntomas (o hallazgos clínicos, si el texto es un
reporte de estudio) encontrados.

Sé específico.

Ejemplo (relato de paciente):

Incorrecto:
[
'Dolor'
]

Correcto:
[
'Dolor abdominal inferior',
'Náuseas',
'Fiebre'
]

Ejemplo (reporte de estudio de imagen):

Correcto:
[
'Aumento del espacio articular acromiohumeral',
'Artrosis acromioclavicular'
]

El arreglo 'sintomas' NUNCA debe quedar vacío si el texto contiene
información clínica real, sea narrada por el paciente o reportada en
un estudio.


====================================
DIAGNÓSTICO PROBABLE
====================================

Genera una condición probable basada únicamente en los síntomas o,
si aplica, en la impresión diagnóstica del estudio adjunto.

Ejemplo:

'Gastritis aguda probable'

No uses términos demasiado generales como:

'Malestar'
'Problema digestivo'
'Dolencia'


====================================
RIESGO CLÍNICO
====================================

Evalúa señales de alarma:

- Dificultad respiratoria.
- Dolor torácico.
- Sangrado.
- Pérdida de conciencia.
- Convulsiones.
- Fiebre persistente.
- Alteración neurológica.
- Dolor intenso.
- Traumatismos importantes.


====================================
ALERTAS
====================================

Genera alertas únicamente si existe información real.

Tipos permitidos:

alergia
gravedad
respiratoria
cardiaca
neurologica
otro


Nivel:

alto
medio
bajo


Nunca inventes alertas.


====================================
RECOMENDACIÓN
====================================

Genera una recomendación concreta relacionada con los síntomas o
hallazgos del estudio.

Debe ser útil para el médico.

Evita frases genéricas como:

'Tomar líquidos'
'Descansar'


====================================
FORMATO DE RESPUESTA
====================================


Devuelve únicamente JSON válido:


{
    \"sintomas\": [
        \"\"
    ],

    \"diagnostico\": \"\",

    \"recomendacion\": \"\",

    \"confianza\": 0,

    \"nivel_riesgo\": \"bajo\",

    \"alertas\": [
        {
            \"tipo\": \"\",
            \"titulo\": \"\",
            \"descripcion\": \"\",
            \"nivel\": \"\"
        }
    ]
}


REGLAS FINALES:

- No escribas texto fuera del JSON.
- No uses Markdown.
- No inventes datos.
- Diferencia datos reales de interpretaciones.
- Ignora ruido de transcripción (risas, tráfico, muletillas, small talk) solo cuando el texto sea una transcripción de audio, como se indicó arriba.
- Si el texto es un reporte de estudio ya elaborado, no dejes 'sintomas' vacío: usa los hallazgos/impresión diagnóstica como se indicó arriba.
- Prioriza seguridad del paciente.

";

        try {
            $response = Http::withToken(config('services.ai.key'))
                ->timeout(20)
                ->post('https://api.deepseek.com/chat/completions', [
                    'model' => 'deepseek-v4-flash',
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