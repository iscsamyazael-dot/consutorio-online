<?php
// cSpell:disable
namespace App\Services;

use App\Services\Terminologia\DiccionarioMedico;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;

/**
 * Servicio de IA para el módulo de Medicina del Trabajo (Valoraciones
 * ocupacionales y Accidentes de trabajo).
 *
 * DECISIÓN DE ARQUITECTURA (ver conversación de diseño): este servicio
 * duplica intencionalmente los métodos genéricos de infraestructura de
 * IAClinicaService.php (llamada HTTP a Gemini con reintentos, parseo/
 * reparación de JSON, extracción de texto de archivos, preparación de
 * imágenes para Gemini Vision) EN VEZ de extraerlos a un trait compartido.
 *
 * Motivo: IAClinicaService.php ya está en producción con la consulta
 * médica de los 4 tenants. Tocarlo -aunque sea solo para mover métodos a
 * un trait sin cambiar su lógica- implica riesgo de romper ese flujo y
 * obligaría a reprobar todo el módulo de consulta médica sin ganar
 * funcionalidad nueva a cambio. Duplicar esta infraestructura (unas
 * ~250 líneas) aísla el radio de impacto de este módulo nuevo: si algo
 * sale mal aquí, no toca la consulta médica.
 *
 * Esta duplicación es deuda técnica aceptada a propósito. Cuando este
 * servicio lleve tiempo funcionando en producción sin sobresaltos, es el
 * momento correcto para extraer un trait compartido (ManejaLlamadasGemini
 * o similar) con dos usos reales ya confirmados para validar que el
 * trait quedó bien -en vez de hacerlo ahora a ciegas con uno solo.
 */
class IAClinicaTrabajoService
{
    private const MAX_TOKENS_ANALISIS = 30000;
    private const MAX_TOKENS_ENTRADA = 20000;

    // =========================================================
    // MÉTODOS PÚBLICOS — ANÁLISIS DE MEDICINA DEL TRABAJO
    // =========================================================

    /**
     * Analiza la narrativa libre de un accidente de trabajo (texto escrito
     * o transcripción de dictado) y sugiere el llenado de los campos
     * estructurados del formulario de `accidentes_trabajo`.
     *
     * El formulario sigue siendo TRADICIONAL: este método solo PRELLENA
     * campos para que el médico los revise/edite antes de guardar. La IA
     * nunca decide el registro final, y campos administrativos que no se
     * pueden inferir de forma confiable de una narrativa (fecha exacta,
     * lugar, folio, notificación IMSS, secuela, fecha de alta) NO se
     * incluyen aquí a propósito — esos los llena el médico directo en el
     * formulario.
     *
     * @param string $narrativa Texto/dictado de qué pasó, en palabras del
     *   médico o del trabajador.
     * @param array $normasCandidatas Normas ya asociadas al puesto del
     *   trabajador (de `puesto_norma_aplicable` JOIN `normas_oficiales`),
     *   cada una como ['id' => ..., 'codigo' => ..., 'titulo' => ...].
     *   La IA SOLO puede sugerir normas de esta lista — nunca inventa
     *   códigos de norma nuevos, para evitar alucinaciones de NOMs que no
     *   existen o no aplican al puesto.
     * @param string|null $instruccionAdicional Indicación extra opcional
     *   del médico (mismo patrón usado en IAClinicaService).
     *
     * @return array|null Arreglo decodificado del JSON de la IA, o null
     *   si la llamada/parseo falló (el controlador debe manejar este caso
     *   dejando el formulario vacío para llenado 100% manual).
     */
    public function analizarNarrativaAccidente(
        string $narrativa,
        array $normasCandidatas = [],
        ?string $instruccionAdicional = null
    ): ?array {
        set_time_limit(120);

        $vocabularioSintomas = DiccionarioMedico::textoReferencia();

        $bloqueNormas = '';
        if (!empty($normasCandidatas)) {
            $listaNormas = collect($normasCandidatas)
                ->map(fn($n) => "id={$n['id']} | {$n['codigo']} — {$n['titulo']}")
                ->implode("\n");

            $bloqueNormas = "
            NORMAS OFICIALES YA ASOCIADAS AL PUESTO DE ESTE TRABAJADOR
            (SOLO puedes sugerir normas de esta lista, usando el 'id' tal
            cual aparece aquí. Si ninguna aplica claramente al accidente
            descrito, regresa 'normas_sugeridas' como arreglo vacío. NUNCA
            inventes un código de norma que no esté en esta lista):

            {$listaNormas}
            ";
        } else {
            $bloqueNormas = "
            No hay normas oficiales asociadas todavía al puesto de este
            trabajador. Regresa 'normas_sugeridas' como arreglo vacío.
            ";
        }

        $bloqueInstruccion = $instruccionAdicional
            ? "\n\nINSTRUCCIÓN ADICIONAL DEL MÉDICO:\n{$instruccionAdicional}\n"
            : '';

        $prompt = "
        Eres un asistente de Inteligencia Artificial que apoya a un médico
        del trabajo a llenar el reporte estructurado de un accidente
        laboral, a partir de la narrativa libre de lo ocurrido. NO
        sustituyes el criterio médico ni legal: tu única función es
        clasificar y estructurar la información que YA está en la
        narrativa, sin inventar datos que no se mencionan.

        Vocabulario de referencia (síntoma/lesión coloquial -> término
        médico preciso):
        {$vocabularioSintomas}
        {$bloqueNormas}
        {$bloqueInstruccion}

        NARRATIVA DEL ACCIDENTE:
        \"{$narrativa}\"

        Devuelve EXCLUSIVAMENTE el siguiente JSON, sin texto adicional ni
        Markdown, con estas reglas por campo:

        - mecanismo_lesion: cómo ocurrió la lesión (ej. 'caída de altura',
          'golpe por objeto en movimiento', 'sobreesfuerzo al levantar
          carga'), en términos técnicos breves.
        - parte_cuerpo_afectada: parte(s) del cuerpo lesionada(s), términos
          anatómicos precisos (ej. 'mano derecha, falange proximal del
          índice').
        - tipo_lesion: tipo de lesión en términos médicos (ej. 'laceración',
          'esguince grado I', 'quemadura de segundo grado').
        - gravedad: clasifica en UNA sola de estas 4 opciones EXACTAS:
          'leve', 'moderado', 'grave', 'fatal'.
        - descripcion_hechos: la narrativa reescrita de forma clara,
          cronológica y en tercera persona/lenguaje técnico, SIN inventar
          hechos que no se mencionaron — es una limpieza de redacción, no
          una reinterpretación.
        - amerita_incapacidad: true/false — si la narrativa sugiere que el
          trabajador requerirá días de reposo/incapacidad. Si no hay
          información suficiente para saberlo, responde false.
        - dias_incapacidad: número entero de días SOLO si la narrativa lo
          menciona explícitamente o lo sugiere con claridad (ej. 'el
          médico le dio 5 días'); si no se menciona, responde null. NUNCA
          inventes un número.
        - acciones_correctivas: sugerencia breve de acción correctiva o
          preventiva relacionada directamente con lo descrito (ej. 'revisar
          estado del EPP asignado', 'reforzar señalización de la zona') —
          es una sugerencia para que el médico la edite, no una instrucción
          definitiva.
        - normas_sugeridas: arreglo de objetos {\"id\": <int, de la lista de
          arriba>, \"justificacion\": \"texto breve de por qué esta norma
          aplica a este accidente específico\"}. Arreglo vacío si ninguna
          aplica o no hay normas candidatas.

        {\"mecanismo_lesion\": \"\", \"parte_cuerpo_afectada\": \"\", \"tipo_lesion\": \"\", \"gravedad\": \"leve\", \"descripcion_hechos\": \"\", \"amerita_incapacidad\": false, \"dias_incapacidad\": null, \"acciones_correctivas\": \"\", \"normas_sugeridas\": []}
        ";

        return $this->consultarGeminiTrabajo($prompt, 'analizarNarrativaAccidente');
    }

    /**
     * Analiza la transcripción (dictado en vivo, una o varias rondas) de
     * una valoración ocupacional y sugiere el llenado de
     * `valoraciones_ocupacionales` + sus tablas hijas
     * `valoracion_norma_aplicada` y `valoracion_seguimiento`.
     *
     * Sigue el mismo patrón que analizarTranscripcion() de la consulta
     * médica general: acumula rondas de dictado sin perder lo ya
     * capturado. El IMC y su clasificación NO se piden a la IA -se
     * calculan en el backend a partir de peso/estatura, es aritmética
     * simple y no debe depender de que el modelo no se equivoque-.
     *
     * @param string $textoRonda Texto de la ronda de dictado actual.
     * @param array $historial Texto de rondas anteriores de esta misma
     *   valoración (mismo patrón que $historial en IAClinicaService).
     * @param array|null $datosPuesto Info del puesto para poder
     *   correlacionar hallazgos vs. riesgos, ej. ['nombre' => ...,
     *   'descripcion_actividades' => ..., 'riesgos_activos' => ['riesgo_ergonomico', 'riesgo_ruido', ...]]
     *   (los flags de riesgo de `puestos_trabajo` que están en 1).
     * @param array $normasCandidatas Igual que en analizarNarrativaAccidente
     *   -normas ya asociadas al puesto vía `puesto_norma_aplicable`-.
     *
     * @return array|null
     */
    public function analizarTranscripcionValoracion(
        string $textoRonda,
        array $historial = [],
        ?array $datosPuesto = null,
        array $normasCandidatas = []
    ): ?array {
        set_time_limit(300);

        $vocabularioSintomas = DiccionarioMedico::textoReferencia();

        $historialTexto = '';
        if (!empty($historial)) {
            $historialTexto = implode("\n\n--- RONDA ANTERIOR ---\n\n", $historial);
            if (mb_strlen($historialTexto) > (self::MAX_TOKENS_ENTRADA * 4)) {
                $historialTexto = mb_substr($historialTexto, -(self::MAX_TOKENS_ENTRADA * 4));
            }
        }

        $bloqueHistorial = $historialTexto !== ''
            ? "\n\nRONDAS ANTERIORES DE DICTADO DE ESTA MISMA VALORACIÓN (no repitas\nlo ya capturado, pero considéralo para el cuadro completo):\n{$historialTexto}\n"
            : '';

        $bloquePuesto = '';
        if ($datosPuesto) {
            $riesgos = !empty($datosPuesto['riesgos_activos'])
                ? implode(', ', $datosPuesto['riesgos_activos'])
                : 'ninguno marcado';

            $bloquePuesto = "
            DATOS DEL PUESTO DEL TRABAJADOR (para el análisis de
            correlación riesgos-puesto):
            Puesto: {$datosPuesto['nombre']}
            Actividades: {$datosPuesto['descripcion_actividades']}
            Riesgos activos del puesto: {$riesgos}
            ";
        }

        $bloqueNormas = '';
        if (!empty($normasCandidatas)) {
            $listaNormas = collect($normasCandidatas)
                ->map(fn($n) => "id={$n['id']} | {$n['codigo']} — {$n['titulo']}")
                ->implode("\n");

            $bloqueNormas = "
            NORMAS OFICIALES ASOCIADAS AL PUESTO (SOLO puedes usar estos
            'id' en 'normas_aplicadas'. Arreglo vacío si ninguna aplica):
            {$listaNormas}
            ";
        }

        $prompt = "
        Eres un asistente de Inteligencia Artificial que apoya a un
        médico especialista en Medicina del Trabajo a llenar el
        dictamen de una valoración ocupacional (aptitud laboral), a
        partir de dictado en vivo durante la consulta. NO sustituyes el
        criterio médico ni emites el dictamen final por tu cuenta -tus
        campos son un borrador para que el médico revise, corrija y
        firme-.

        Vocabulario de referencia (síntoma coloquial -> término médico):
        {$vocabularioSintomas}
        {$bloquePuesto}
        {$bloqueNormas}
        {$bloqueHistorial}

        TEXTO DE HOY (esta ronda de dictado):
        \"{$textoRonda}\"

        Devuelve EXCLUSIVAMENTE el siguiente JSON, sin texto adicional ni
        Markdown. Para CADA campo de texto: si el texto de HOY no aporta
        nada nuevo o relevante para ese campo, responde con cadena vacía
        (\"\") -el sistema conserva lo ya capturado en rondas anteriores,
        NUNCA escribas 'No disponible' ni inventes contenido de relleno-.
        Para los campos numéricos de signos vitales/antropometría: null si
        no se mencionaron en el texto de HOY.

        - ta_sistolica, ta_diastolica, fc, fr (enteros), spo2, estatura_cm,
          peso_kg, talla_abdomen_cm, grasa_visceral, grasa_corporal_pct,
          musculo_pct (decimales), edad_biologica (entero): SOLO si se
          dictaron explícitamente como medición de hoy.
        - antecedentes_heredofamiliares: enfermedades de familiares
          directos mencionadas.
        - antecedentes_patologicos_activos: enfermedades/condiciones que el
          trabajador tiene actualmente (distinto de antecedentes
          quirúrgicos o heredofamiliares).
        - estilo_vida_habitos: tabaquismo, alcoholismo, actividad física,
          alimentación, sueño, mencionados.
        - exploracion_fisica_funcional: hallazgos de la exploración física
          relevantes para aptitud laboral (rango de movimiento, fuerza,
          agudeza visual/auditiva, etc.).
        - estudios_paraclinicos_resumen: resumen narrativo de resultados de
          laboratorio/gabinete mencionados verbalmente (los resultados
          estructurados de estudios adjuntos se capturan aparte, esto es
          solo lo que se dictó de viva voz).
        - analisis_correlacion_riesgos: análisis técnico de cómo los
          hallazgos de esta valoración se relacionan con los riesgos
          activos del puesto (dato de arriba) -este es el corazón del
          dictamen técnico, redáctalo con el detalle que el texto permita-.
        - dictamen: SOLO si el texto de hoy indica una conclusión de
          aptitud explícita del médico, una de estas 3 EXACTAS: 'apto',
          'apto_con_restricciones', 'no_apto'. Si no se ha dictaminado
          todavía en el texto de hoy, responde cadena vacía.
        - restricciones: texto de restricciones SOLO si dictamen fue
          'apto_con_restricciones' y se mencionaron; si no, cadena vacía.
        - dictamen_justificacion: razonamiento clínico-técnico del
          dictamen, SOLO si el texto lo aporta.
        - plan_intervencion: recomendaciones/plan de acción mencionado.
        - normas_aplicadas: arreglo de objetos {\"id\": <int, de la lista de
          arriba>, \"aplicacion_especifica\": \"cómo aplica esta norma a
          ESTE trabajador/puesto según lo dictado\"}. Vacío si no aplica.
        - seguimiento: arreglo de objetos {\"tipo_revaloracion\": \"texto\",
          \"plazo\": \"texto, ej. 'A los 30 días', 'Semestral'\",
          \"objetivo\": \"texto\"} SOLO si el médico mencionó explícitamente
          que se requiere revaloración o seguimiento. Vacío si no.

        {\"ta_sistolica\": null, \"ta_diastolica\": null, \"fc\": null, \"fr\": null, \"spo2\": null, \"estatura_cm\": null, \"peso_kg\": null, \"talla_abdomen_cm\": null, \"grasa_visceral\": null, \"grasa_corporal_pct\": null, \"musculo_pct\": null, \"edad_biologica\": null, \"antecedentes_heredofamiliares\": \"\", \"antecedentes_patologicos_activos\": \"\", \"estilo_vida_habitos\": \"\", \"exploracion_fisica_funcional\": \"\", \"estudios_paraclinicos_resumen\": \"\", \"analisis_correlacion_riesgos\": \"\", \"dictamen\": \"\", \"restricciones\": \"\", \"dictamen_justificacion\": \"\", \"plan_intervencion\": \"\", \"normas_aplicadas\": [], \"seguimiento\": []}
        ";

        return $this->consultarGeminiTrabajo($prompt, 'analizarTranscripcionValoracion');
    }

    // =========================================================
    // NÚCLEO COMPARTIDO DE LOS 2 MÉTODOS DE ARRIBA
    // =========================================================

    /**
     * Envía un prompt ya armado a Gemini y decodifica su respuesta JSON.
     * Mismo patrón que consultarIA() en IAClinicaService: URL con
     * GEMINI_API_KEY + modelo, llamada con reintentos, extracción de
     * candidates.0.content.parts.0.text, reintento único adicional si el
     * primer parseo de JSON falla.
     */
    private function consultarGeminiTrabajo(string $prompt, string $origen): ?array
    {
        try {
            $apiKey = env('GEMINI_API_KEY');
            $modelo = 'gemini-3.5-flash-lite';
            $urlGemini = "https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key={$apiKey}";

            $inicio = microtime(true);

            $response = $this->llamarGeminiConReintentos(
                $urlGemini,
                [['text' => $prompt]],
                3,
                ['responseMimeType' => 'application/json']
            );

            Log::info('Tiempo respuesta Gemini (' . $origen . ')', [
                'segundos' => round(microtime(true) - $inicio, 2),
            ]);

            if (!$response->successful()) {
                Log::error("Error HTTP al consultar IA de Medicina del Trabajo ({$origen})", [
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ]);
                return null;
            }

            $contenido = $response->json('candidates.0.content.parts.0.text');
            $data = $this->decodificarJsonDesdeTexto($contenido, $origen, $response->json() ?? []);

            if ($data === null) {
                Log::warning("JSON inválido en {$origen} tras reparación, reintentando...");
                $response = $this->llamarGeminiConReintentos(
                    $urlGemini,
                    [['text' => $prompt]],
                    1,
                    ['responseMimeType' => 'application/json']
                );
                if ($response->successful()) {
                    $contenido = $response->json('candidates.0.content.parts.0.text');
                    $data = $this->decodificarJsonDesdeTexto($contenido, "{$origen} (reintento)", $response->json() ?? []);
                }
            }

            return $data;

        } catch (\Exception $e) {
            Log::error("Excepción al consultar IA de Medicina del Trabajo ({$origen}): " . $e->getMessage());
            return null;
        }
    }

    // =========================================================
    // INFRAESTRUCTURA GENÉRICA — DUPLICADA A PROPÓSITO de
    // IAClinicaService.php (ver nota de arquitectura al inicio del
    // archivo). Sin cambios de lógica respecto al original.
    // =========================================================

    /**
     * Llama a Gemini con reintentos automáticos (backoff exponencial) ante
     * errores transitorios (429 rate limit, 500/503 servidor sobrecargado).
     */
    private function llamarGeminiConReintentos(string $url, array $parts, int $maxIntentos = 3, array $generationConfigExtra = [])
    {
        $intento = 0;
        $espera = 2; // segundos, se duplica en cada reintento (2s, 4s, 8s)
        $response = null;

        $generationConfig = array_merge(
            ['temperature' => 0.1],
            $generationConfigExtra
        );

        while ($intento < $maxIntentos) {
            $intento++;

            $response = Http::timeout(300)->post($url, [
                'contents' => [['role' => 'user', 'parts' => $parts]],
                'generationConfig' => $generationConfig,
            ]);

            if ($response->successful()) {
                return $response;
            }

            $codigo = $response->status();
            if (in_array($codigo, [429, 500, 503], true) && $intento < $maxIntentos) {
                Log::warning("Gemini respondió {$codigo}, reintentando en {$espera}s (intento {$intento}/{$maxIntentos})");
                sleep($espera);
                $espera *= 2;
                continue;
            }

            return $response; // se agotaron los intentos o es un error no reintentable
        }

        return $response;
    }

    /**
     * Extrae texto de un archivo adjunto (PDF/Word) o marca que necesita
     * visión artificial (imagen o PDF escaneado sin texto digital).
     */
    public function extraerTextoDeArchivo(string $rutaCompleta, string $mime): string
    {
        try {
            if (!file_exists($rutaCompleta)) {
                Log::error('Archivo no encontrado', ['ruta' => $rutaCompleta]);
                return '';
            }

            if ($mime === 'application/pdf') {
                $parser = new PdfParser();
                $pdf = $parser->parseFile($rutaCompleta);
                $textoPdf = trim($pdf->getText());

                if (mb_strlen($textoPdf) > 20) {
                    return trim($pdf->getText());
                }

                return '[DOCUMENTO_ESCANEADO_O_IMAGEN]';
            }

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

            if (str_starts_with($mime, 'image/')) {
                return '[DOCUMENTO_ESCANEADO_O_IMAGEN]';
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
     * Comprime y arma los "parts" de imagen en el formato que espera la
     * API de Gemini (inline_data + mime_type/data).
     */
    private function prepararPartesImagenesGemini(
        array $rutas,
        string $promptClinico,
        string $etiquetaLote = '',
        int $anchoMax = 1200,
        int $calidadJpeg = 75
    ): array {
        $contenidoMensaje = [
            ['text' => $promptClinico . ($etiquetaLote !== '' ? " ({$etiquetaLote})" : '')]
        ];

        foreach ($rutas as $rutaImg) {
            $infoImagen = getimagesize($rutaImg);

            if ($infoImagen === false) {
                Log::warning('No se pudo leer la imagen para compresión, se omite.', ['ruta' => $rutaImg]);
                continue;
            }

            $tipoImagen = $infoImagen[2];

            $imagenOriginal = match ($tipoImagen) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($rutaImg),
                IMAGETYPE_PNG  => imagecreatefrompng($rutaImg),
                IMAGETYPE_GIF  => imagecreatefromgif($rutaImg),
                IMAGETYPE_WEBP => imagecreatefromwebp($rutaImg),
                default        => null,
            };

            if ($imagenOriginal === null) {
                Log::warning('Formato de imagen no soportado para compresión, se omite.', [
                    'ruta' => $rutaImg,
                    'tipo_detectado' => $tipoImagen,
                ]);
                continue;
            }

            $anchoOriginal = imagesx($imagenOriginal);
            $altoOriginal = imagesy($imagenOriginal);

            $anchoNuevo = $anchoMax;
            $altoNuevo = floor($altoOriginal * ($anchoNuevo / $anchoOriginal));
            $imagenModificada = imagecreatetruecolor($anchoNuevo, $altoNuevo);

            if ($tipoImagen === IMAGETYPE_PNG || $tipoImagen === IMAGETYPE_GIF) {
                $fondoBlanco = imagecolorallocate($imagenModificada, 255, 255, 255);
                imagefill($imagenModificada, 0, 0, $fondoBlanco);
            }

            imagecopyresampled($imagenModificada, $imagenOriginal, 0, 0, 0, 0, $anchoNuevo, $altoNuevo, $anchoOriginal, $altoOriginal);

            ob_start();
            imagejpeg($imagenModificada, null, $calidadJpeg);
            $binarioComprimido = ob_get_clean();

            imagedestroy($imagenOriginal);
            imagedestroy($imagenModificada);

            $base64File = base64_encode($binarioComprimido);

            $contenidoMensaje[] = [
                'inline_data' => [
                    'mime_type' => 'image/jpeg',
                    'data' => $base64File,
                ],
            ];
        }

        return $contenidoMensaje;
    }

    /**
     * Extrae imágenes embebidas de un PDF (útil para PDFs "escaneados" que
     * en realidad son fotos pegadas, ej. estudios de laboratorio
     * fotografiados con el celular y guardados como PDF).
     */
    private function extraerImagenesDePdf(string $rutaCompleta, string $prefijo = 'pdf'): array
    {
        $imagenes = [];
        $pdf = (new PdfParser())->parseFile($rutaCompleta);
        $objects = $pdf->getObjects();
        $contador = 0;

        foreach ($objects as $object) {
            $details = $object->getDetails();
            if (isset($details['Subtype']) && $details['Subtype'] === '/Image') {
                $contador++;
                $imagenBinaria = $object->getContent();
                if (!empty($imagenBinaria)) {
                    $archivoTemporal = sys_get_temp_dir() . '/' . uniqid("{$prefijo}_trabajo_{$contador}_", true) . '.jpg';
                    file_put_contents($archivoTemporal, $imagenBinaria);
                    $imagenes[] = $archivoTemporal;
                }
            }
        }

        if (empty($imagenes)) {
            foreach ($objects as $object) {
                $content = $object->getContent();
                if (str_starts_with($content, "\xFF\xD8\xFF")) {
                    $contador++;
                    $archivoTemporal = sys_get_temp_dir() . '/' . uniqid("{$prefijo}_stream_{$contador}_", true) . '.jpg';
                    file_put_contents($archivoTemporal, $content);
                    $imagenes[] = $archivoTemporal;
                }
            }
        }

        return $imagenes;
    }

    /**
     * Decodifica el JSON de la respuesta HTTP completa (usada si en el
     * futuro se agrega un proveedor con formato 'choices.0.message.content'
     * tipo DeepSeek, como ya hace IAClinicaService para triage rápido).
     */
    private function decodificarJsonRespuesta($response, string $origen)
    {
        $contenido = $response->json('choices.0.message.content');
        return $this->decodificarJsonDesdeTexto($contenido, $origen, $response->json() ?? []);
    }

    /**
     * Decodificador robusto de JSON con reparación automática. Mismo
     * comportamiento que en IAClinicaService: limpia razonamiento
     * envuelto en <think>, intenta json_decode directo, si falla intenta
     * balancear llaves, si sigue fallando intenta reparar valores de
     * arreglo sin comillas.
     */
    private function decodificarJsonDesdeTexto(?string $contenido, string $origen, array $respuestaCompletaParaLog = [])
    {
        if (empty($contenido)) {
            Log::error("IA devolvió contenido vacío ({$origen})", [
                'respuesta_completa' => $respuestaCompletaParaLog,
            ]);
            return null;
        }

        $contenido = preg_replace('/<think>.*?<\/think>/s', '', $contenido);
        $contenido = trim($contenido);

        if ($contenido === '') {
            Log::error("IA devolvió únicamente razonamiento sin contenido final ({$origen})", [
                'respuesta_completa' => $respuestaCompletaParaLog,
            ]);
            return null;
        }

        $data = json_decode($contenido, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $jsonBalanceado = $this->extraerPrimerJsonBalanceado($contenido);
            if ($jsonBalanceado !== null) {
                $data = json_decode($jsonBalanceado, true);
            }
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            $contenidoReparado = $this->repararValoresSinComillas($contenido);
            $data = json_decode($contenidoReparado, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $jsonBalanceado = $this->extraerPrimerJsonBalanceado($contenidoReparado);
                if ($jsonBalanceado !== null) {
                    $data = json_decode($jsonBalanceado, true);
                }
            }

            if (json_last_error() === JSON_ERROR_NONE) {
                Log::warning('JSON reparado automáticamente (valores sin comillas)', ['origen' => $origen]);
            }
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("JSON inválido o incompleto devuelto por la IA ({$origen})", [
                'json_error' => json_last_error_msg(),
                'longitud_contenido' => strlen($contenido),
                'contenido_crudo' => $contenido,
            ]);
            return null;
        }

        $data['debug_usage'] = $respuestaCompletaParaLog['usage']
            ?? $respuestaCompletaParaLog['usageMetadata']
            ?? null;

        return $data;
    }

    private function extraerPrimerJsonBalanceado(string $contenido): ?string
    {
        $inicio = strpos($contenido, '{');
        if ($inicio === false) {
            return null;
        }

        $profundidad = 0;
        $dentroString = false;
        $escapando = false;

        for ($i = $inicio; $i < strlen($contenido); $i++) {
            $char = $contenido[$i];

            if ($escapando) {
                $escapando = false;
                continue;
            }

            if ($char === '\\' && $dentroString) {
                $escapando = true;
                continue;
            }

            if ($char === '"') {
                $dentroString = !$dentroString;
                continue;
            }

            if ($dentroString) {
                continue;
            }

            if ($char === '{') {
                $profundidad++;
            } elseif ($char === '}') {
                $profundidad--;
                if ($profundidad === 0) {
                    return substr($contenido, $inicio, $i - $inicio + 1);
                }
            }
        }

        return null;
    }

    private function repararValoresSinComillas(string $contenido): string
    {
        $lineas = explode("\n", $contenido);
        $reparado = [];

        foreach ($lineas as $linea) {
            $sinEspacios = trim($linea);

            if (preg_match('/^([A-Za-zÁÉÍÓÚÑÁáéíóúñ0-9][^":{}\[\]]*?)(,?)\s*$/u', $sinEspacios, $m)) {
                $indentacion = substr($linea, 0, strlen($linea) - strlen(ltrim($linea)));
                $texto = trim($m[1]);
                $coma = $m[2];

                if ($texto !== '' && !str_contains($texto, ':')) {
                    $reparado[] = $indentacion . '"' . addslashes($texto) . '"' . $coma;
                    continue;
                }
            }

            $reparado[] = $linea;
        }

        return implode("\n", $reparado);
    }
}