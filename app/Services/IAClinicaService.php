<?php

namespace App\Services;
use App\Models\ConsultaTranscripcion;
use App\Services\Terminologia\DiccionarioMedico;
use App\Models\SintomaDetectado;
use App\Models\EvaluacionIA;
use App\Models\AlertaClinica;
use App\Models\EventoIA;
use App\Models\NotaPsoapp;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use thiagoalessio\TesseractOCR\TesseractOCR;


class IAClinicaService
{
    /*ranscripción médica
     */
    public function analizarTranscripcion(
        $texto,
        $consulta,
        array $historial = [],
        $ultimaNota = null
    ) {
        $data = $this->consultarIA(
            $texto,
            $historial,
            $ultimaNota
        );

        if (!is_array($data) || !isset($data['sintomas'], $data['diagnostico'])) {
            Log::error('IA devolvió respuesta inválida', ['respuesta' => $data]);
            return [
                'diagnostico_probable' => 'No se pudo determinar',
                'nivel_riesgo' => 'desconocido',
                'recomendaciones' => ['No se pudo completar el análisis. Intenta nuevamente.'],
                'alertas' => [],
                'nota_psoapp' => null,
            ];
        }

        // Guardar síntomas
        if (!empty($data['sintomas'])) {
            foreach ($data['sintomas'] as $sintoma) {
                SintomaDetectado::updateOrCreate(
                    ['consulta_id' => $consulta->id, 'nombre_sintoma' => $sintoma],
                    [
                        'consulta_folio' => $consulta->folio,
                        'session_uuid' => $consulta->session_uuid,
                        'origen' => 'ia'
                    ]
                );
            }
        }

        $notaPsoapp = $data['nota_psoapp'] ?? null;

        $evaluacion = EvaluacionIA::create([
            'consulta_id' => $consulta->id,
            'consulta_folio' => $consulta->folio,
            'session_uuid' => $consulta->session_uuid,
            'sintomas_detectados' => implode(', ', $data['sintomas'] ?? []),
            'diagnostico_probable' => $data['diagnostico'] ?? 'No disponible',
            'recomendacion' => $data['recomendacion'] ?? 'Sin recomendación',
            'confianza' => $data['confianza'] ?? 0
        ]);

        // Guardar nota PSOAPP
        if (is_array($notaPsoapp) && !empty($notaPsoapp)) {
            NotaPsoapp::create([
                'evaluacion_ia_id' => $evaluacion->id,
                'consulta_id' => $consulta->id,
                'consulta_folio' => $consulta->folio,
                'session_uuid' => $consulta->session_uuid,
                'presentacion' => $notaPsoapp['presentacion'] ?? null,
                'subjetivo' => $notaPsoapp['subjetivo'] ?? null,
                'objetivo' => $notaPsoapp['objetivo'] ?? null,
                'analisis' => $notaPsoapp['analisis'] ?? null,
                'plan' => $notaPsoapp['plan'] ?? null,
                'pronostico' => $notaPsoapp['pronostico'] ?? null,
            ]);
        }

        EventoIA::create([
            'consulta_id' => $consulta->id,
            'tipo_evento' => 'recomendaciones',
            'descripcion' => 'Recomendaciones médicas generadas por IA',
            'resultado' => $data['recomendacion'] ?? 'Sin recomendación',
            'created_at' => now(),
        ]);

        $alertasDetectadas = $data['alertas'] ?? [];
        $nivelRiesgo = $this->calcularNivelRiesgo($alertasDetectadas);

        // Procesar alertas
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
                'requiere_atencion' => ($alerta['nivel'] ?? 'bajo') !== 'bajo',
                'fecha_alerta' => now()
            ]);
        }

        // Alerta por defecto si no hay alertas
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
            'diagnostico_probable' => $data['diagnostico'] ?? 'No determinado',
            'nivel_riesgo' => $nivelRiesgo,
            'recomendaciones' => [$data['recomendacion'] ?? 'Sin recomendación'],
            'confianza' => $data['confianza'] ?? null,
            'sintomas' => $data['sintomas'] ?? [],
            'alertas' => $alertasDetectadas,
            'nota_psoapp' => $notaPsoapp,
        ];
    }

    /**
     * Arma el bloque de texto con el historial de consultas anteriores y la
     * última nota PSOAPP, para dar continuidad clínica al prompt. Si no hay
     * nada de eso disponible, devuelve un texto vacío (no agrega ruido al
     * prompt cuando es la primera consulta del paciente).
     */
    private function bloqueContextoPrevio(string $historialTexto, string $notaAnteriorTexto): string
    {
        if (trim($historialTexto) === '' && trim($notaAnteriorTexto) === '') {
            return '';
        }

        $bloque = "\n        CONTEXTO PREVIO DEL EXPEDIENTE (para dar continuidad, no para copiar tal cual):\n";
        $bloque .= "        Este contexto es SOLO para que evalúes evolución/continuidad del padecimiento.\n";
        $bloque .= "        No lo repitas como si fuera parte de la consulta actual, y no inventes que\n";
        $bloque .= "        algo mencionado antes sigue vigente si el registro actual no lo confirma.\n";

        if (trim($historialTexto) !== '') {
            $bloque .= "\n        HISTORIAL DE CONSULTAS ANTERIORES:\n{$historialTexto}\n";
        }

        if (trim($notaAnteriorTexto) !== '') {
            $bloque .= "\n        ÚLTIMA NOTA PSOAPP REGISTRADA:{$notaAnteriorTexto}\n";
        }

        return $bloque;
    }

    /**
     * Calcula el nivel de riesgo
     */
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
     * Extrae texto de archivos
     */
    public function extraerTextoDeArchivo(string $rutaCompleta, string $mime): string
    {
        try {
            if (!file_exists($rutaCompleta)) {
                Log::error('Archivo no encontrado', ['ruta' => $rutaCompleta]);
                return '';
            }

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
                        ->executable(config('services.tesseract.path', 'C:\Users\Usuario\Desktop\llave de ultrafarmacia\tesseract.exe'))
                        ->lang('spa', 'eng')
                        ->run()
                );
            }

            Log::warning('Tipo de archivo no soportado', ['mime' => $mime]);
            return '';

        } catch (\Throwable $e) {
            Log::error('Error extrayendo texto de archivo', [
                'error' => $e->getMessage(),
                'ruta' => $rutaCompleta,
                'mime' => $mime
            ]);
            return '';
        }
    }

    /**
     * Analiza un archivo adjunto
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
     * Sugerencia de medicamentos con triage completo - LENGUAJE MÉDICO PROFESIONAL
     */
    public function sugerirMedicamentoLibre(array $sintomas, array $especialidadesDisponibles = [])
    {
        $textoSintomas = implode(', ', $sintomas);

        $textoEspecialidades = !empty($especialidadesDisponibles)
            ? implode(', ', $especialidadesDisponibles)
            : 'No disponible (no se proporcionó catálogo, usa tu mejor criterio clínico general)';

        // Vocabulario de referencia (síntoma coloquial -> término médico),
        // extraído del Manual de Terminología Médica.
        $vocabularioSintomas = DiccionarioMedico::textoReferencia();

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
        FASE 4 - DIAGNÓSTICOS PROBABLES (LENGUAJE MÉDICO PROFESIONAL)
        =========================================================

        Genera máximo tres diagnósticos probables utilizando TERMINOLOGÍA MÉDICA PRECISA.

        Para nombrar correctamente el síntoma o hallazgo referido por el paciente
        (antes de razonar el diagnóstico probable), usa este vocabulario de referencia
        (síntoma coloquial -> término médico):
        $vocabularioSintomas

        Ordénalos desde el más probable al menos probable.

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

    /**
     * Consulta a la IA para análisis clínico - LENGUAJE MÉDICO PROFESIONAL
     */
    private function consultarIA(
        $texto,
        array $historial = [],
        $ultimaNota = null
    ) {
        $historialTexto = '';

        if (!empty($historial)) {
            $historialTexto = implode("\n\n--- REGISTRO ANTERIOR ---\n\n", $historial);
        }

        $notaAnteriorTexto = '';

        if ($ultimaNota) {
            $notaAnteriorTexto = "

PRESENTACIÓN ANTERIOR:
{$ultimaNota->presentacion}

SUBJETIVO ANTERIOR:
{$ultimaNota->subjetivo}

OBJETIVO ANTERIOR:
{$ultimaNota->objetivo}

ANÁLISIS ANTERIOR:
{$ultimaNota->analisis}

PLAN ANTERIOR:
{$ultimaNota->plan}

PRONÓSTICO ANTERIOR:
{$ultimaNota->pronostico}
";
        }

        // Vocabulario de referencia (síntoma coloquial -> término médico),
        // extraído del Manual de Terminología Médica.
        $vocabularioSintomas = DiccionarioMedico::textoReferencia();

        $prompt = "

        Eres un asistente clínico de Inteligencia Artificial utilizado EXCLUSIVAMENTE como apoyo
        para el médico dentro de un sistema de expediente médico digital. Tu función es identificar
        síntomas, condiciones probables, riesgos y alertas clínicas, y organizar la información en
        una nota de evolución en formato PSOAPP (Presentación, Subjetivo, Objetivo, Análisis, Plan,
        Pronóstico), tal como se documentaría en un expediente clínico real.

        IMPORTANTE:

        - No reemplazas al médico ni sustituyes su criterio clínico.
        - No emites diagnósticos definitivos, únicamente probabilidades clínicas.
        - No inventes información que no esté en la nota médica.
        - Si un dato no aparece, escribe 'No disponible' o 'Sin datos suficientes en la
          transcripción' en el apartado correspondiente. Nunca lo rellenes con información inventada.
        - UTILIZA TERMINOLOGÍA MÉDICA PRECISA Y PROFESIONAL en TODAS tus respuestas.
        - Los diagnósticos y hallazgos deben expresarse con nombres técnicos de enfermedades.

        Vocabulario de referencia para nombrar correctamente cada síntoma
        (síntoma coloquial -> término médico):
        $vocabularioSintomas
        {$this->bloqueContextoPrevio($historialTexto, $notaAnteriorTexto)}
        NOTA MÉDICA DEL PACIENTE (registro actual):

        $texto

        =========================================================
        FASE 1 - CLASIFICACIÓN Y FILTRADO DE LA FUENTE
        =========================================================

        El texto anterior puede provenir de dos fuentes distintas. Identifica cuál es antes de
        continuar:

        A) TRANSCRIPCIÓN AUTOMÁTICA DE AUDIO (relato hablado del paciente).

        Si el texto es de este tipo, ignora por completo y NO los menciones en el JSON de salida:

        • Risas o expresiones como 'jaja', 'jeje', '(risas)'.
        • Ruido ambiental transcrito por error: motos, carros, claxon, bocinas, sirenas, tráfico,
          música de fondo.
        • Muletillas y relleno verbal: 'este', 'o sea', 'eh', 'mmm', 'like', 'ajá'.
        • Interjecciones sin contenido clínico: 'ok', 'perfecto', 'gracias', 'okay doc'.
        • Conversación de cortesía o small talk no relacionado con el motivo de consulta.
        • Fragmentos inaudibles o mal transcritos sin sentido clínico.
        • Repeticiones por tartamudeo o autocorrección (quédate solo con la versión final).

        B) REPORTE DE ESTUDIO YA ELABORADO (radiología, laboratorio, etc.), con secciones como
        'HALLAZGOS', 'IMPRESIÓN DIAGNÓSTICA', 'RESULTADOS' o similares, en vez de un relato de
        síntomas.

        Si el texto es de este tipo:

        - Trata cada hallazgo relevante de HALLAZGOS/RESULTADOS y cada conclusión de la IMPRESIÓN
          DIAGNÓSTICA como equivalente clínico de un síntoma. NO dejes el arreglo 'sintomas' vacío
          solo porque no hay síntomas narrados; en este caso los hallazgos del estudio SON el
          equivalente clínico.
        - Ignora datos administrativos sin valor clínico: nombre del médico referente, cédula
          profesional, firma, fecha del documento, encabezados de la clínica, frases de cierre.
        - El campo 'diagnostico' debe reflejar la impresión diagnóstica del estudio tal como fue
          reportada (sin inventar), aclarando que es un hallazgo de estudio y no una conclusión
          clínica integral del médico tratante.

        Solo analiza el contenido que tenga relevancia clínica real.

        =========================================================
        FASE 2 - EXTRACCIÓN DE INFORMACIÓN CLÍNICA
        =========================================================

        Extrae del texto ya filtrado:

        - Síntomas actuales (o hallazgos del estudio, según el caso de la Fase 1).
        - Duración de síntomas si existe.
        - Intensidad si está disponible.
        - Zona anatómica afectada.
        - Antecedentes relevantes.
        - Medicamentos mencionados.
        - Alergias mencionadas.
        - Factores de riesgo.

        =========================================================
        FASE 3 - SÍNTOMAS / HALLAZGOS - TERMINOLOGÍA MÉDICA
        =========================================================

        Identifica todos los síntomas (o hallazgos clínicos, si el texto es un reporte de estudio)
        encontrados. UTILIZA NOMBRES TÉCNICOS Y PRECISOS para cada uno, apoyándote en el
        vocabulario de referencia indicado al inicio del prompt.

        Ejemplo (relato de paciente):

        Incorrecto: [ 'Dolor' ]

        Correcto:
        [
        'Dolor abdominal agudo en hipocondrio derecho',
        'Náuseas',
        'Pirexia de 38.5°C',
        'Vómito'
        ]

        Ejemplo (reporte de estudio de imagen):

        Correcto:
        [
        'Aumento del espacio articular acromiohumeral',
        'Artrosis acromioclavicular',
        'Derrame articular',
        'Edema de partes blandas'
        ]

        El arreglo 'sintomas' NUNCA debe quedar vacío si el texto contiene información clínica real.

        =========================================================
        FASE 4 - DIAGNÓSTICO PROBABLE - NOMBRES TÉCNICOS
        =========================================================

        Genera una condición probable basada únicamente en los síntomas o, si aplica, en la
        impresión diagnóstica del estudio adjunto. UTILIZA EL NOMBRE TÉCNICO COMPLETO de la
        entidad clínica.

        REGLA CRÍTICA - NO INVENTES ETIOLOGÍA NI AGENTE CAUSAL:

        El nombre técnico de la condición NO es lo mismo que su causa específica. Puedes nombrar
        la entidad clínica (ej. 'Gastritis aguda'), pero NUNCA le agregues un agente causal,
        microorganismo o etiología específica (ej. 'por Helicobacter pylori', 'estreptocócica',
        'viral', 'bacteriana') a menos que ese agente venga EXPLÍCITAMENTE confirmado en el texto
        (por ejemplo, un resultado de laboratorio o cultivo ya reportado). Si el texto es solo el
        relato de síntomas del paciente, sin estudios que confirmen el agente causal, omite la
        etiología por completo.

        Incorrecto (etiología inventada, sin respaldo en el texto):
        'Gastritis aguda por Helicobacter pylori'
        'Faringoamigdalitis aguda estreptocócica'

        Correcto (misma entidad clínica, sin inventar el agente causal):
        'Gastritis aguda'
        'Faringoamigdalitis aguda'

        Correcto (aquí SÍ es válido nombrar el agente, porque el propio texto reporta el estudio
        que lo confirma):
        Si el texto incluye 'cultivo faríngeo positivo para Streptococcus pyogenes' o
        'prueba de antígeno fecal positiva para H. pylori', entonces sí puedes escribir
        'Faringoamigdalitis aguda estreptocócica' o 'Gastritis aguda por Helicobacter pylori',
        porque ahí la etiología no la inventaste tú: viene del estudio ya realizado.

        Ejemplos válidos de nombres técnicos (sin etiología no confirmada):

        'Gastritis aguda'
        'Faringoamigdalitis aguda'
        'Hipertensión arterial sistémica no controlada'
        'Diabetes mellitus tipo 2 descompensada'
        'Artrosis de rodilla bilateral'
        'Cefalea tensional crónica'
        'Infección de vías urinarias bajas'
        'Bronquitis aguda'

        No uses términos demasiado generales como: 'Malestar', 'Problema digestivo', 'Dolencia',
        'Infección', 'Problema de salud'.

        Nunca afirmes que el diagnóstico es definitivo.

        =========================================================
        FASE 5 - EVALUACIÓN DE RIESGO CLÍNICO
        =========================================================

        Evalúa si existen señales de alarma:

        • Dificultad respiratoria.
        • Dolor torácico.
        • Sangrado.
        • Pérdida de conciencia.
        • Convulsiones.
        • Fiebre persistente.
        • Alteración neurológica.
        • Dolor intenso.
        • Traumatismos importantes.

        El nivel de riesgo resultante ('bajo', 'medio', 'alto') debe ser consistente con las
        alertas generadas en la Fase 6.

        =========================================================
        FASE 6 - ALERTAS CLÍNICAS
        =========================================================

        Genera alertas ÚNICAMENTE si existe información real que las respalde. Nunca inventes
        alertas.

        Tipos permitidos: alergia | gravedad | respiratoria | cardiaca | neurologica | otro

        Nivel: alto | medio | bajo

        Las descripciones de las alertas deben usar terminología médica precisa.

        =========================================================
        FASE 7 - RECOMENDACIÓN MÉDICA
        =========================================================

        Genera una recomendación concreta relacionada con los síntomas o hallazgos del estudio,
        útil para el médico, con terminología médica precisa.

        Evita frases genéricas como 'Tomar líquidos' o 'Descansar'.

        =========================================================
        FASE 8 - NOTA CLÍNICA POR APARTADOS (PSOAPP)
        =========================================================

        Redacta la nota de evolución en los 6 apartados del formato PSOAPP. Usa ÚNICAMENTE
        información presente en el texto (no inventes signos vitales, hallazgos de exploración
        física ni estudios que no fueron mencionados). Cada apartado es un párrafo breve en prosa
        clínica profesional, no una lista.

        - presentacion: Datos demográficos disponibles (edad, sexo, ocupación si se mencionan),
          motivo de consulta en pocas palabras, y antecedentes de importancia (comorbilidades,
          alergias, cirugías previas) si se refieren.

        - subjetivo: Padecimiento actual narrado cronológicamente (inicio, localización,
          intensidad, características, agravantes/aliviantes, evolución en el tiempo), síntomas
          asociados referidos, y tratamientos o medicamentos que el paciente ya tomó antes de la
          evaluación.

        - objetivo: Signos vitales y hallazgos de exploración física o de estudios de
          laboratorio/imagen SOLO SI aparecen explícitamente en el texto. Si el texto es únicamente
          un relato del paciente sin exploración física ni estudios, escribe: 'No disponible -
          pendiente de exploración física y estudios complementarios por el médico tratante'.

        - analisis: Razonamiento clínico breve integrando lo subjetivo y lo objetivo, e impresión
          diagnóstica probable (nunca definitiva). Menciona si las comorbilidades referidas parecen
          controladas o descompensadas, solo si hay datos para inferirlo.

        - plan: Sugerencias generales de plan diagnóstico (estudios que podrían solicitarse) y
          terapéutico/educativo a nivel de apoyo a la decisión, SIN indicar dosis, vía ni
          frecuencia de medicamentos (eso lo define el médico). Puede incluir vigilancia de signos
          de alarma.

        - pronostico: Estimación general (reservado/bueno/malo) para la vida y para la función, y
          los factores de los que dependería la evolución, siempre aclarando que es una estimación
          preliminar sujeta a valoración médica presencial.

        =========================================================
        FASE 9 - RESPUESTA
        =========================================================

        Devuelve EXCLUSIVAMENTE el siguiente JSON.

        {

        \"nota_psoapp\": {
        \"presentacion\": \"\",
        \"subjetivo\": \"\",
        \"objetivo\": \"\",
        \"analisis\": \"\",
        \"plan\": \"\",
        \"pronostico\": \"\"
        },

        \"sintomas\": [
        \"\"
        ],

        \"diagnostico\": \"\",

        \"recomendacion\": \"\",

        \"confianza\": 0,

        \"nivel_riesgo\": \"bajo\",

        \"alertas\": [
        {
        \"tipo\": \"alergia|gravedad|respiratoria|cardiaca|neurologica|otro\",
        \"titulo\": \"\",
        \"descripcion\": \"\",
        \"nivel\": \"alto|medio|bajo\"
        }
        ]

        }

        =========================================================
        REGLAS IMPORTANTES
        =========================================================

        - Responde únicamente con JSON.
        - No escribas explicaciones fuera del JSON.
        - No uses Markdown.
        - No inventes síntomas, hallazgos ni antecedentes que no estén en el texto.
        - Si un dato no aparece, usa 'No disponible' o 'Sin datos suficientes en la transcripción'.
        - El diagnóstico siempre es probable y nunca definitivo.
        - Prioriza siempre la seguridad del paciente al evaluar riesgo y alertas.
        - Nunca inventes alertas: solo genéralas si hay evidencia real en el texto.

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
                Log::error('Error HTTP al consultar IA clínica', ['status' => $response->status()]);
                return null;
            }

            return json_decode($response->json('choices.0.message.content'), true);

        } catch (\Exception $e) {
            Log::error('Excepción al consultar IA clínica: ' . $e->getMessage());
            return null;
        }
    }
}