<?php
// cSpell:disable
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
    /*
     * Número máximo de tokens de salida que se le permite generar a la IA
     * en las respuestas JSON de análisis clínico. Los prompts (sobre todo
     * el de consultarIA, que pide una nota PSOAPP extensa) pueden generar
     * respuestas largas; si el límite es muy bajo el JSON se corta a la
     * mitad y json_decode() devuelve null, cayendo en "No se pudo
     * determinar" aunque el texto de entrada sea perfectamente válido.
     */
    private const MAX_TOKENS_ANALISIS = 30000;
    private const MAX_TOKENS_ENTRADA = 20000;

    /**
     * Analiza una transcripción médica
     */
    public function analizarTranscripcion(
        $texto,
        $consulta,
        array $historial = [],
        $ultimaNota = null
    ) {
        // --- CONTROL DE TOKENS DE ENTRADA ---
        if (!empty($historial)) {
            // Tomamos los registros más recientes
            $historial = array_slice($historial, -5);
            $historialTexto = implode("\n", $historial);
            
            // Si el texto acumulado supera el límite seguro de caracteres basados en MAX_TOKENS_ENTRADA 
            // (aprox 4 caracteres por token), recortamos el exceso.
            $limiteCaracteres = self::MAX_TOKENS_ENTRADA * 4;
            if (mb_strlen($historialTexto) > $limiteCaracteres) {
                // Nos quedamos con los últimos caracteres permitidos
                $historialTexto = mb_substr($historialTexto, -$limiteCaracteres);
                // Opcional: reasignamos como un bloque de texto plano limitado
                $historial = [$historialTexto]; 
            }
        }
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
                'indicaciones_medico' => null,
                'alertas' => [],
                'nota_psoapp' => null,
            ];
        }

        // ============================================================
        // VALIDACIÓN DE ANCLAJE DE SÍNTOMAS (capa de seguridad opcional)
        // ------------------------------------------------------------
        // Esto es una segunda barrera de bajo costo (regex/str_contains
        // locales, sin llamadas a red, impacto en tiempo ~0ms) para
        // detectar síntomas que la IA generó pero que no tienen ninguna
        // coincidencia léxica razonable con el texto original (ej. "edema
        // de miembros inferiores" agregado por asociación clínica con
        // disnea/taquicardia, sin que el paciente lo haya mencionado).
        //
        // No sustituye el fix real, que es el ajuste del prompt en
        // consultarIA() (ver FASE 3 y FASE 4 más abajo). Es solo una red
        // de detección/logging adicional. Es una heurística conservadora:
        // solo descarta un síntoma si NO encuentra ninguna coincidencia
        // parcial, así que el riesgo de que descarte un síntoma válido
        // por un sinónimo no compartido (ej. "panza" vs "abdomen") existe,
        // pero prioriza no perder datos reales: si tienes dudas, revisa
        // los logs de "Síntoma descartado por falta de anclaje" antes de
        // confiar en que este filtro no está siendo demasiado agresivo.
        //
        // Si por ahora solo quieres el fix del prompt (recomendado como
        // primer paso) y no esta capa extra, comenta la llamada de abajo.
        // ============================================================
        if (!empty($data['sintomas'])) {
            $data['sintomas'] = $this->validarAnclajeSintomas($data['sintomas'], $texto, $consulta);
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
            'indicaciones_medico' => $data['indicaciones_medico'] ?? null,
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

        // FIX: antes se usaba `$data['recomendacion'] ?? 'Sin recomendación'`.
        // El operador `??` solo cubre null/clave inexistente, NO cadenas vacías.
        // Si la IA devolvía 'recomendacion' => '' (string vacío, algo que
        // ocurre porque el prompt sí permite dejar vacío un campo similar,
        // 'recomendaciones_generales', en la Fase 6B), el resultado final era
        // 'recomendaciones' => [''] -> un array con un elemento vacío, que
        // pasaba la validación del frontend (Array.isArray && length > 0) y
        // se renderizaba como un <li> en blanco, dejando la caja vacía.
        // Ahora se valida explícitamente que el texto no esté vacío/solo
        // espacios antes de usarlo, y si lo está, se usa el fallback.
        $recomendacionTexto = trim((string) ($data['recomendacion'] ?? ''));
        $recomendacionFinal = $recomendacionTexto !== '' ? $recomendacionTexto : 'Sin recomendación';

        return [
            'diagnostico_probable' => $data['diagnostico'] ?? 'No determinado',
            'nivel_riesgo' => $nivelRiesgo,
            'recomendaciones' => [$recomendacionFinal],
            'indicaciones_medico' => $data['indicaciones_medico'] ?? null,
            'confianza' => $data['confianza'] ?? null,
            'sintomas' => $data['sintomas'] ?? [],
            'alertas' => $alertasDetectadas,
            'nota_psoapp' => $notaPsoapp,
            'debug_usage' => $data['debug_usage'] ?? null,
        ];
    }

    /**
     * Descarta síntomas que la IA generó pero que no tienen ninguna
     * coincidencia léxica razonable con el texto original (transcripción o
     * reporte de estudio). Ver el comentario extenso en el punto donde se
     * llama, dentro de analizarTranscripcion(), para el detalle de por qué
     * existe esta capa y sus limitaciones (heurística de subcadenas, no
     * análisis semántico completo).
     */
    private function validarAnclajeSintomas(array $sintomas, string $textoOriginal, $consulta): array
    {
        $textoNormalizado = mb_strtolower($textoOriginal);
        $sintomasValidados = [];

        foreach ($sintomas as $sintoma) {
            $palabrasClave = preg_split('/[\s,\/()\-]+/', mb_strtolower($sintoma));
            $palabrasClave = array_filter($palabrasClave, fn($p) => mb_strlen($p) > 4);

            // Si no quedan palabras "significativas" (todo el término es corto),
            // no filtramos: preferimos dejarlo pasar antes que descartar por
            // un falso negativo de la heurística.
            $tieneCoincidencia = empty($palabrasClave);

            foreach ($palabrasClave as $palabra) {
                if (str_contains($textoNormalizado, mb_substr($palabra, 0, 5))) {
                    $tieneCoincidencia = true;
                    break;
                }
            }

            if ($tieneCoincidencia) {
                $sintomasValidados[] = $sintoma;
            } else {
                Log::warning('Síntoma descartado por falta de anclaje en el texto original', [
                    'sintoma' => $sintoma,
                    'consulta_id' => $consulta->id ?? null,
                ]);
            }
        }

        return $sintomasValidados;
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
     * Clasifica el nivel de urgencia/triage de un paciente a partir del motivo
     * de consulta (y opcionalmente los síntomas reportados), usando IA.
     * Se usa al registrar un paciente nuevo para reemplazar la clasificación
     * manual/hardcodeada por una evaluación automática.
     *
     * Devuelve un arreglo con las DOS columnas reales de la tabla `triage`:
     * - estado: 'leve' | 'estable' | 'grave' | 'urgente'          (ENUM real de la tabla)
     * - nivel_urgencia: 'bajo' | 'medio' | 'alto' | 'critico'     (ENUM real de la tabla,
     *   derivado del 'estado' con un mapeo fijo, para que ambas columnas queden
     *   siempre consistentes entre sí y nunca truncadas por un valor inválido).
     *
     * @return array{estado: string, nivel_urgencia: string}
     */
    public function clasificarTriage(?string $motivoConsulta, ?string $sintomas = null): array
    {   
        // Forzamos el límite de ejecución de PHP para evitar cortes inesperados
        set_time_limit(300);
        $motivoConsulta = trim((string) $motivoConsulta);

        if ($motivoConsulta === '') {
            // Sin motivo de consulta no hay información suficiente para clasificar,
            // se deja en 'estable' para que el médico lo revise manualmente.
            return $this->resultadoTriage('estable');
        }

        $textoSintomas = trim((string) $sintomas);

        $prompt = "
        Eres un asistente clínico de Inteligencia Artificial que apoya el proceso de
        triaje inicial de un consultorio. NO sustituyes el criterio médico, solo das
        una primera clasificación de urgencia para ordenar la lista de espera.

        Motivo de consulta reportado por el paciente:
        \"$motivoConsulta\"
        " . ($textoSintomas !== '' ? "

        Síntomas adicionales reportados:
        \"$textoSintomas\"
        " : '') . "

        Clasifica la urgencia del paciente en UNA SOLA de estas cuatro categorías:

        - leve: motivo de consulta de rutina, revisión, seguimiento, trámite o
          síntomas muy leves sin relevancia clínica urgente.
        - estable: síntomas molestos que requieren valoración médica pero sin signos
          de alarma inmediatos (ej. dolor leve-moderado, malestar general, síntomas
          de días de evolución sin empeorar).
        - grave: síntomas que sugieren riesgo importante o complicación relevante,
          requieren atención médica prioritaria (ej. dolor intenso, fiebre alta
          persistente, sangrado moderado, traumatismo relevante).
        - urgente: síntomas que sugieren riesgo vital inminente, requieren atención
          inmediata (ej. dolor torácico, dificultad respiratoria severa, pérdida de
          conciencia, convulsiones, hemorragia importante).

        Responde EXCLUSIVAMENTE con el siguiente JSON, sin texto adicional ni Markdown:

        {\"nivel\": \"leve|estable|grave|urgente\", \"justificacion\": \"texto breve\"}
        ";

        try {
            $response = Http::withToken(config('services.ai.key'))
                ->timeout(300)
                ->post('https://api.deepseek.com/chat/completions', [
                    'model' => 'deepseek-v4-flash',
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'response_format' => ['type' => 'json_object'],
                   // 'max_tokens' => self::MAX_TOKENS_ANALISIS,
                ]);

            if (!$response->successful()) {
                Log::error('Error HTTP al clasificar triage con IA', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return $this->resultadoTriage('estable');
            }

            $data = $this->decodificarJsonRespuesta($response, 'clasificarTriage');

            if (!is_array($data)) {
                return $this->resultadoTriage('estable');
            }

            $nivel = strtolower(trim($data['nivel'] ?? ''));

            if (!in_array($nivel, ['leve', 'estable', 'grave', 'urgente'], true)) {
                Log::warning('IA devolvió un nivel de triage no reconocido', ['respuesta' => $data]);
                return $this->resultadoTriage('estable');
            }

            return $this->resultadoTriage($nivel);

        } catch (\Exception $e) {
            Log::error('Excepción al clasificar triage con IA: ' . $e->getMessage());
            return $this->resultadoTriage('estable');
        }
    }

    /**
     * Arma el arreglo final de clasificación, derivando 'nivel_urgencia' a partir
     * de 'estado' con un mapeo fijo (no se le pide a la IA que invente la segunda
     * escala, para que las dos columnas ENUM de la tabla `triage` sean siempre
     * consistentes entre sí).
     */
    private function resultadoTriage(string $estado): array
    {
        $mapaNivelUrgencia = [
            'leve' => 'bajo',
            'estable' => 'medio',
            'grave' => 'alto',
            'urgente' => 'critico',
        ];

        return [
            'estado' => $estado,
            'nivel_urgencia' => $mapaNivelUrgencia[$estado] ?? 'medio',
        ];
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
                $textoPdf = trim($pdf->getText());

                if (mb_strlen($textoPdf) > 20) {
                    return trim($pdf->getText());
                }

                // Si es un PDF escaneado (sin texto digital interno), 
                // devolvemos una etiqueta para que el controlador sepa que debe enviarlo como imagen a la IA.
                return '[DOCUMENTO_ESCANEADO_O_IMAGEN]';
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
                Log::info('El archivo es una imagen, devolviendo etiqueta de visión.');
                return '[DOCUMENTO_ESCANEADO_O_IMAGEN]';
                // return trim(
                //     (new TesseractOCR($rutaCompleta))
                //         ->executable(config('services.tesseract.path', 'C:\Users\Usuario\Desktop\llave de ultrafarmacia\tesseract.exe'))
                //         ->lang('spa', 'eng')
                //         ->run()
                // );
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
     *
     * FIX: este método antes llamaba a
     * $this->analizarArchivoConVisionDeepSeek(...), un método que NO existe
     * en esta clase (el único método de visión que existe es
     * analizarArchivoConVisionGemini). Si algún flujo llegaba a pasar por
     * aquí con una imagen o PDF escaneado, hubiera lanzado un
     * "Call to undefined method" en vez de analizar el archivo. Se corrige
     * para apuntar al método real.
     *
     * NOTA: actualmente el controlador (ConsultaIAController::subirArchivo)
     * no llama a este método — resuelve el flujo de imagen directamente con
     * analizarArchivoConVisionGemini() y luego analizarTranscripcion().
     * Este método queda disponible por si algún otro punto del sistema lo
     * usa, ya corregido para que no truene si se llega a invocar.
     */
    public function analizarArchivoAdjunto(string $rutaCompleta, string $mime, $consulta)
    {   
        // LOG DE DEPURACIÓN INICIAL
        Log::info('analizarArchivoAdjunto llamado con:', [
            'ruta' => $rutaCompleta,
            'mime' => $mime
        ]);


        $textoExtraido = $this->extraerTextoDeArchivo($rutaCompleta, $mime);
       
        // Log para verificar qué se obtuvo
        Log::info('Texto extraído del archivo', ['texto' => $textoExtraido]);
        // Caso 1: El archivo es una imagen (RX) o un PDF escaneado
        if ($textoExtraido === '[DOCUMENTO_ESCANEADO_O_IMAGEN]') {
            //Ver si entra aquí
            Log::info('¡Entró a la condición de Visión Artificial!'); 
            // Llamamos a la función que conecta con la API multimodal de Gemini enviando la imagen/PDF.
            // (antes decía analizarArchivoConVisionDeepSeek, un método que no existe en esta clase)
            $resultado = $this->analizarArchivoConVisionGemini($rutaCompleta, $mime, $consulta);
            Log::info('<<< Regresó de analizarArchivoConVisionGemini con resultado:', ['resultado' => is_array($resultado) ? 'Array OK' : 'Null/Válido']);
            
            return [
                'texto_extraido' => '[Archivo visual / PDF escaneado analizado por IA]',
                'resultado' => $resultado,
                'error' => null,
            ];
        } else {
        Log::warning('NO entró a la condición de Visión. El texto extraído fue:', ['texto' => $textoExtraido]);
        }


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
     * Comprime y arma los "parts" de imagen en el formato que espera la API
     * de Gemini (inline_data + mime_type/data). Se usa tanto para el modo de
     * llamada única como para el modo de lotes en paralelo.
     */
    private function prepararPartesImagenesGemini(array $rutas, string $promptClinico, string $etiquetaLote = ''): array
    {
        $contenidoMensaje = [
            ['text' => $promptClinico . ($etiquetaLote !== '' ? " ({$etiquetaLote})" : '')]
        ];

        foreach ($rutas as $rutaImg) {
            // Detectamos el tipo real de imagen por su contenido (no confiamos en
            // la extensión del archivo), para no reventar si es PNG, GIF, WEBP, etc.
            $infoImagen = getimagesize($rutaImg);

            if ($infoImagen === false) {
                Log::warning('No se pudo leer la imagen para compresión, se omite.', ['ruta' => $rutaImg]);
                continue;
            }

            $tipoImagen = $infoImagen[2]; // constante IMAGETYPE_*

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

            $anchoNuevo = 1200;
            $altoNuevo = floor($altoOriginal * ($anchoNuevo / $anchoOriginal));
            $imagenModificada = imagecreatetruecolor($anchoNuevo, $altoNuevo);

            // PNG/GIF pueden traer transparencia; si no la preservamos antes de
            // volcar a JPEG (que no soporta canal alfa), las zonas transparentes
            // salen en negro. Rellenamos con blanco antes de copiar.
            if ($tipoImagen === IMAGETYPE_PNG || $tipoImagen === IMAGETYPE_GIF) {
                $fondoBlanco = imagecolorallocate($imagenModificada, 255, 255, 255);
                imagefill($imagenModificada, 0, 0, $fondoBlanco);
            }

            imagecopyresampled($imagenModificada, $imagenOriginal, 0, 0, 0, 0, $anchoNuevo, $altoNuevo, $anchoOriginal, $altoOriginal);

            ob_start();
            imagejpeg($imagenModificada, null, 75); // convertimos todo a JPEG para el envío
            $binarioComprimido = ob_get_clean();

            imagedestroy($imagenOriginal);
            imagedestroy($imagenModificada);

            $base64File = base64_encode($binarioComprimido);

            // Formato Gemini: inline_data + mime_type/data, NO image_url
            // (ese es el formato OpenAI/Groq y Gemini lo ignora silenciosamente).
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
     * Llama a Gemini con reintentos automáticos (backoff exponencial) ante
     * errores transitorios (429 rate limit, 500/503 servidor sobrecargado).
     * Un error de autenticación o de payload no se reintenta: se devuelve
     * de inmediato para no perder tiempo repitiendo algo que no va a cambiar.
     *
     * GEMINI-MIGRACIÓN: se agregó el parámetro $generationConfigExtra para
     * poder pedir cosas como responseMimeType/thinkingConfig sin tener que
     * duplicar este método. Las llamadas existentes (Vision) no pasan nada
     * aquí, así que su comportamiento queda exactamente igual que antes.
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

    public function analizarArchivoConVisionGemini(string $rutaCompleta, string $mime, $consulta)
    { 
        set_time_limit(300);
        $imagenesTemporales = [];

        try {
            if (!file_exists($rutaCompleta)) {
                Log::error("Archivo no encontrado para análisis: {$rutaCompleta}");
                return null;
            }

            $vocabularioSintomas = DiccionarioMedico::textoReferencia();
            
            $promptClinico = "
            Eres un asistente clínico de Inteligencia Artificial utilizado EXCLUSIVAMENTE como apoyo
            para el médico dentro de un expediente médico digital. Tu función es identificar
            síntomas, condiciones probables, riesgos y alertas clínicas a partir de la información proporcionada,
            y organizar la información clínica de forma clara y profesional.

            INSTRUCCIÓN ADICIONAL DEL MÉDICO:
            {$consulta}

            IMPORTANTE:
            - No reemplazas al médico ni sustituyes su criterio clínico.
            - No emites diagnósticos definitivos, únicamente probabilidades clínicas.
            - No inventes información que no esté visible en el documento.
            - Si un dato no aparece, escribe 'No disponible' o 'Sin datos suficientes'.
            - UTILIZA TERMINOLOGÍA MÉDICA PRECISA Y PROFESIONAL.

            Vocabulario de referencia (síntoma coloquial -> término médico):
            $vocabularioSintomas
            ";

            $textoNativo = "";

            // ==========================================
            // PASO 1: INTENTAR EXTRAER TEXTO NATIVO (PDF de texto / Word)
            // ==========================================
            if ($mime === 'application/pdf') {
                try {
                    $parser = new PdfParser();
                    $pdf = $parser->parseFile($rutaCompleta);
                    $textoNativo = trim($pdf->getText());
                } catch (\Exception $e) {
                    Log::warning('No se pudo extraer texto nativo del PDF, se tratará como escaneado.');
                }
            }

            // ==========================================
            // PASO 2: BIFURCACIÓN INTELIGENTE (TEXTO -> DEEPSEEK | IMAGEN/ESCANEADO -> GEMINI)
            // ==========================================
            
            // Si el PDF contiene texto plano válido, lo mandamos directo a DeepSeek
            if (!empty($textoNativo) && mb_strlen($textoNativo) > 40) {
                Log::info('El PDF contiene texto nativo. Derivando a DeepSeek...');
                
                $response = Http::withToken(config('services.ai.key')) // Asegúrate de tener tu variable o token de DeepSeek
                    ->timeout(300)
                    ->post('https://api.deepseek.com/v1/chat/completions', [
                        'model' => 'deepseek-v4-flash',
                        'messages' => [
                            ['role' => 'system', 'content' => $promptClinico],
                            ['role' => 'user', 'content' => "Analiza el siguiente texto clínico extraído del documento:\n\n" . $textoNativo]
                        ],
                        'temperature' => 0.1
                    ]);

                if ($response->successful()) {
                    $resultadoTexto = $response->json('choices.0.message.content');
                    // FIX: igual que en el flujo de imagen/Gemini más abajo, este
                    // texto puede venir con un bloque <think>...</think> del
                    // razonamiento del modelo delante del texto real. Aquí el
                    // resultado es texto libre (no JSON), así que no rompía nada
                    // técnicamente, pero sí ensuciaba el análisis mostrado al
                    // médico con contenido de razonamiento interno que no debería
                    // ver. Se limpia igual que en la rama de Gemini Vision.
                    $resultadoTexto = preg_replace('/<think>.*?<\/think>/s', '', (string) $resultadoTexto);
                    $resultadoTexto = trim($resultadoTexto);
                    Log::info('DeepSeek - Análisis de texto plano exitoso.');
                    return $resultadoTexto;
                } else {
                    Log::error('Error en API de DeepSeek, recurriendo a respaldo de visión.');
                }
            }

            // Si es una imagen o un PDF escaneado (sin texto nativo), procesamos con Gemini Vision
            Log::info('El archivo es una imagen o PDF escaneado. Procesando con Gemini Vision...');

            if ($mime === 'application/pdf') {
                $pdf = (new PdfParser())->parseFile($rutaCompleta);
                $objects = $pdf->getObjects();
                $contadorPagina = 0;

                // Buscar por Subtype /Image
                foreach ($objects as $object) {
                    $details = $object->getDetails();
                    if (isset($details['Subtype']) && $details['Subtype'] === '/Image') {
                        $contadorPagina++;
                        $imagenBinaria = $object->getContent();
                        if (!empty($imagenBinaria)) {
                            $archivoTemporal = sys_get_temp_dir() . '/' . uniqid("pdf_camscanner_{$contadorPagina}_", true) . '.jpg';
                            file_put_contents($archivoTemporal, $imagenBinaria);
                            $imagenesTemporales[] = $archivoTemporal;
                        }
                    }
                }

                // Respaldo de flujos binarios JPEG si no encontró por Subtype
                if (empty($imagenesTemporales)) {
                    foreach ($objects as $object) {
                        $content = $object->getContent();
                        if (str_starts_with($content, "\xFF\xD8\xFF")) {
                            $contadorPagina++;
                            $archivoTemporal = sys_get_temp_dir() . '/' . uniqid("pdf_stream_{$contadorPagina}_", true) . '.jpg';
                            file_put_contents($archivoTemporal, $content);
                            $imagenesTemporales[] = $archivoTemporal;
                        }
                    }
                }
            } else {
                $imagenesTemporales[] = $rutaCompleta;
            }

            if (empty($imagenesTemporales)) {
                Log::error('No se pudieron extraer imágenes o contenido válido del archivo.');
                return null;
            }

            // ==========================================
            // PROCESAMIENTO CON GEMINI VISION (VERSIÓN RÁPIDA)
            // Tier de pago -> límites altos de cuota, así que:
            //  - Documentos pequeños/medianos (<= LIMITE_IMAGENES_UNA_SOLA_LLAMADA
            //    imágenes): se manda TODO en una sola llamada HTTP. Es la ruta
            //    más rápida posible: un solo round-trip de red, sin sleeps.
            //  - Documentos grandes: se agrupan en pocos lotes grandes y se
            //    envían en PARALELO con Http::pool (en vez de uno por uno con
            //    sleep(15) entre cada uno, como antes).
            //  - Reintentos automáticos con backoff (2s, 4s, 8s) ante 429/500/503,
            //    para no exponer errores al usuario aunque haya una ráfaga
            //    puntual que exceda la cuota.
            // ==========================================

            $apiKey = env('GEMINI_API_KEY');
            $modelo = 'gemini-3.5-flash-lite';
            $urlGemini = "https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key={$apiKey}";

            $LIMITE_IMAGENES_UNA_SOLA_LLAMADA = 12; // documentos con hasta esta cantidad van en 1 sola request
            $IMAGENES_POR_LOTE_GRANDE = 8;          // si se excede el límite anterior, se agrupan así

            $totalImagenes = count($imagenesTemporales);
            $analisisCompletoFinal = '';
            $tokensAcumulados = ['prompt' => 0, 'output' => 0, 'total' => 0];

            // --- CASO A: documento chico/mediano -> 1 SOLA LLAMADA (la ruta más rápida) ---
            if ($totalImagenes <= $LIMITE_IMAGENES_UNA_SOLA_LLAMADA) {
                Log::info("Enviando documento completo ({$totalImagenes} imágenes) a Gemini en una sola llamada...");

                $parts = $this->prepararPartesImagenesGemini($imagenesTemporales, $promptClinico);

                if (count($parts) <= 1) {
                    Log::warning('Ninguna imagen válida para enviar a Gemini, se omite la llamada.');
                    return null;
                }

                // FIX (rendimiento): antes este bloque llamaba a
                // llamarGeminiConReintentos() DOS VECES seguidas — la primera
                // llamada se descartaba por completo (solo se verificaba
                // ->successful() y no se usaba su resultado), y era código de
                // prueba/depuración ("AQUÍ PONES LA PRUEBA") que quedó pegado
                // en producción. Esto duplicaba el tiempo de cada análisis de
                // imagen (y, si Gemini entraba en el flujo de reintentos con
                // backoff, hasta lo cuadruplicaba). Ahora solo hay UNA llamada,
                // con su medición de tiempo para los logs.
                $inicio = microtime(true);
                $response = $this->llamarGeminiConReintentos($urlGemini, $parts);
                $fin = microtime(true);

                Log::info('Tiempo respuesta Gemini (llamada única)', [
                    'segundos' => round($fin - $inicio, 2)
                ]);

                if (!$response->successful()) {
                    Log::error('Error HTTP en API de Gemini Vision (llamada única)', [
                        'response' => $response->body()
                    ]);
                    return null;
                }

                $usage = $response->json('usageMetadata');
                $tokensAcumulados = [
                    'prompt' => $usage['promptTokenCount'] ?? 0,
                    'output' => $usage['candidatesTokenCount'] ?? 0,
                    'total'  => $usage['totalTokenCount'] ?? 0,
                ];

                // Formato de respuesta de Gemini: candidates.0.content.parts.0.text
                // (choices.0.message.content es el formato de respuesta de OpenAI/Groq/
                // DeepSeek; usarlo aquí siempre devolvía null aunque Gemini sí respondiera).
                $texto = $response->json('candidates.0.content.parts.0.text');

                if (empty($texto)) {
                    Log::warning('Gemini respondió sin texto utilizable.', [
                        'respuesta_completa' => $response->json(),
                    ]);
                }

                $texto = preg_replace('/<think>.*?<\/think>/s', '', (string) $texto);
                $analisisCompletoFinal = trim($texto);

            } else {
                // --- CASO B: documento grande -> lotes grandes en PARALELO ---
                $bloquesDeImagens = array_chunk($imagenesTemporales, $IMAGENES_POR_LOTE_GRANDE);
                Log::info("Documento grande ({$totalImagenes} imágenes). Enviando " . count($bloquesDeImagens) . " lotes en paralelo...");

                $lotesPreparados = [];
                foreach ($bloquesDeImagens as $index => $grupoImagenes) {
                    $etiqueta = "Nota: Analizando lote " . ($index + 1) . " de " . count($bloquesDeImagens) . " del documento";
                    $parts = $this->prepararPartesImagenesGemini($grupoImagenes, $promptClinico, $etiqueta);
                    if (count($parts) > 1) {
                        $lotesPreparados[$index] = $parts;
                    } else {
                        Log::warning('Ninguna imagen válida en este lote, se omite.', ['lote' => $index + 1]);
                    }
                }

                if (empty($lotesPreparados)) {
                    Log::error('Ningún lote tuvo imágenes válidas para enviar a Gemini.');
                    return null;
                }

                // Primer intento: todos los lotes en paralelo
                $respuestasPool = Http::pool(function ($pool) use ($lotesPreparados, $urlGemini) {
                    $llamadas = [];
                    foreach ($lotesPreparados as $index => $parts) {
                        $llamadas[$index] = $pool->as((string) $index)->timeout(300)->post($urlGemini, [
                            'contents' => [['role' => 'user', 'parts' => $parts]],
                            'generationConfig' => ['temperature' => 0.1],
                        ]);
                    }
                    return $llamadas;
                });

                ksort($respuestasPool);

                foreach ($respuestasPool as $index => $response) {
                    // Si algún lote falló por rate limit/servidor, lo reintentamos aparte
                    // (solo ese lote puntual) en vez de fallar todo el documento.
                    if ($response instanceof \Throwable || !$response->successful()) {
                        $codigo = $response instanceof \Throwable ? null : $response->status();
                        Log::warning('Lote ' . ((int) $index + 1) . " falló (status: {$codigo}), reintentando individualmente...");
                        $response = $this->llamarGeminiConReintentos($urlGemini, $lotesPreparados[$index]);
                    }

                    if (!$response->successful()) {
                        Log::error('Error HTTP en API de Gemini Vision (lote ' . ((int) $index + 1) . ', tras reintentos)', ['response' => $response->body()]);
                        continue;
                    }

                    $usage = $response->json('usageMetadata');
                    $tokensAcumulados['prompt'] += $usage['promptTokenCount'] ?? 0;
                    $tokensAcumulados['output'] += $usage['candidatesTokenCount'] ?? 0;
                    $tokensAcumulados['total']  += $usage['totalTokenCount'] ?? 0;

                    $textoParcial = $response->json('candidates.0.content.parts.0.text');

                    if (empty($textoParcial)) {
                        Log::warning('Gemini respondió sin texto utilizable en este lote.', [
                            'lote' => (int) $index + 1,
                            'respuesta_completa' => $response->json(),
                        ]);
                        continue;
                    }

                    $textoParcial = preg_replace('/<think>.*?<\/think>/s', '', $textoParcial);
                    $analisisCompletoFinal .= "\n\n--- LOTE " . ((int) $index + 1) . " ---\n" . trim($textoParcial);
                }
            }

            Log::info('Uso total de tokens Gemini para el documento completo', $tokensAcumulados);

            $analisisCompletoFinal = trim($analisisCompletoFinal);

            if (empty($analisisCompletoFinal)) {
                Log::error('Gemini devolvió una respuesta vacía para el documento.');
                return null;
            }

            Log::info('Gemini Vision - Documento completo analizado y unificado exitosamente.');
            return $analisisCompletoFinal;

        } catch (\Throwable $e) {
            Log::error('Excepción al procesar archivo clínico', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return null;
        } finally {
            // Limpiar archivos temporales de imágenes generadas
            foreach ($imagenesTemporales ?? [] as $tempFile) {
                if ($tempFile !== $rutaCompleta && file_exists($tempFile)) {
                    @unlink($tempFile);
                }
            }
        }
    }
    /**
     * Sugerencia de medicamentos con triage completo - LENGUAJE MÉDICO PROFESIONAL
     */
    public function sugerirMedicamentoLibre(array $sintomas, array $especialidadesDisponibles = [])
    {
        set_time_limit(300);
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

            REGLA DE COHERENCIA OBLIGATORIA (triage ROJO -> derivación a Urgencias):
        Si el nivel de triage resultante es ROJO, la especialidad de derivación
        (ver FASE 7) DEBE ser \"Urgencias\" cuando esa especialidad exista, escrita
        tal cual, dentro del catálogo disponible. Nunca derives un caso ROJO a
        Medicina General ni a una especialidad de consulta externa si \"Urgencias\"
        está disponible en el catálogo — un caso de emergencia médica no puede
        resolverse con manejo ambulatorio de rutina. Solo si \"Urgencias\" NO existe
        en el catálogo, usa la mejor alternativa disponible y decláralo
        explícitamente como limitación en \"justificacion\" y \"motivo_derivacion\"

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

        AUTOVERIFICACIÓN OBLIGATORIA ANTES DE ASIGNAR PORCENTAJES:
        Para cada diagnóstico probable, revisa que TODOS los síntomas o hallazgos
        que usas como soporte estén EXPLÍCITAMENTE presentes en la lista de síntomas
        recibida al inicio de este prompt (\"$textoSintomas\"). Nunca justifiques un
        diagnóstico con un síntoma que no fue reportado, aunque sea típico de esa
        condición (ej. no asumas \"edema\" solo porque el cuadro sugiere un problema
        cardiaco, si nadie lo reportó). Si un diagnóstico solo se sostiene con
        síntomas no reportados, bájale el porcentaje o elimínalo del listado.

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
        FASE 6B - RECOMENDACIONES GENERALES DE CUIDADO (PARA EL PACIENTE)
        =========================================================

        Solo si el resultado es \"receta_inteligente\", redacta además un texto breve
        (2 a 4 frases, en prosa, dirigido al paciente) con recomendaciones generales
        de cómo tomar los medicamentos y qué cuidados debe seguir. Este texto lo verá
        el médico y podrá editarlo antes de imprimir la receta.

        PERMITIDO:

        - Indicaciones generales de administración (ej. \"tomar con alimentos para
          evitar irritación gástrica\", \"aplicar en la zona afectada\", \"mantener en
          reposo el área lesionada\").
        - Medidas de cuidado general relacionadas con el padecimiento (hidratación,
          reposo, alimentación blanda, evitar irritantes, cuidados de la piel/herida,
          etc.), coherentes con los síntomas y el diagnóstico probable.
        - Advertir cuándo debe volver a consulta o acudir a urgencias (signos de
          alarma a vigilar).

        PROHIBIDO (igual que en la Fase 6):

        - NO indiques dosis, frecuencia ni duración de ningún medicamento.
        - NO indiques vía de administración exacta si no es obvia por el tipo de
          presentación (ej. no inventes \"aplicar 2 gotas cada 8 horas\").
        - NO uses lenguaje que suene a instrucción médica definitiva; usa un tono de
          sugerencia general (\"se recomienda\", \"es aconsejable\").

        Si no hay nada clínicamente relevante que agregar más allá de lo obvio,
        deja este campo como cadena vacía en vez de inventar contenido genérico
        sin valor (\"tomar agua\", \"descansar\") que no aporte nada específico al caso.

        =========================================================
        FASE 7 - DERIVACIÓN
        =========================================================

        Si el resultado es \"derivacion\":

        Selecciona la especialidad médica más adecuada.

        CATÁLOGO REAL DE ESPECIALIDADES DISPONIBLES EN EL SISTEMA:

        $textoEspecialidades

        ORDEN DE PRIORIDAD (léelo en este orden, no lo inviertas):

        1. La COHERENCIA CLÍNICA es SIEMPRE lo primero. Nunca elijas una especialidad
           solo porque está en el catálogo si no tiene relación médica real con los
           síntomas reportados. Enviar a un paciente con la especialidad equivocada
           es un error clínico grave, más grave que no ceñirte estrictamente al
           catálogo.
        2. Si dentro del catálogo SÍ existe una especialidad coherente con los
           síntomas, cópiala EXACTAMENTE como aparece escrita ahí (no la
           modifiques ni la abrevies). En este caso \"especialidad_fuera_catalogo\"
           debe ser false.
        3. Si el catálogo tiene especialidades pero NINGUNA es coherente con los
           síntomas reportados, o si el catálogo no incluye ninguna especialidad
           apropiada para el caso:
           - Identifica cuál sería la especialidad médicamente ideal para el caso
             (según los ejemplos de coherencia clínica de abajo) y guárdala en el
             campo \"especialidad_ideal_no_disponible\" (ej. \"Neumología\").
           - En el campo \"especialidad\" pon la mejor alternativa disponible en el
             catálogo para dar un primer manejo (normalmente \"Medicina General\" si
             existe en el catálogo; si ni siquiera esa existe, usa la especialidad
             del catálogo más cercana clínicamente, aunque no sea ideal).
           - Marca \"especialidad_fuera_catalogo\": true.
           - En \"justificacion\" y en \"motivo_derivacion\" DEBES decirlo de forma
             explícita y transparente, EN ESTE FORMATO (adaptando los nombres):
             \"No se cuenta con la especialidad de <especialidad_ideal_no_disponible>
             en el catálogo de este consultorio; se deriva a <especialidad> para
             valoración inicial y, de ser necesario, referencia externa a
             <especialidad_ideal_no_disponible>.\"
             No lo omitas ni lo dejes implícito: el médico debe poder leer
             claramente que la especialidad ideal no estaba disponible y cuál era.
        4. Solo si el catálogo dice \"No disponible\" (no se proporcionó catálogo),
           sugiere libremente la especialidad médica más apropiada según los
           ejemplos de abajo, marca \"especialidad_fuera_catalogo\": true, y deja
           \"especialidad_ideal_no_disponible\" vacío (aquí no aplica el mensaje del
           punto 3, porque no hay catálogo del que informar una ausencia).

        RECORDATORIO: si el TRIAGE de la FASE 3 fue ROJO, este paso ya está sujeto
        a la regla de coherencia obligatoria definida ahí (derivar a \"Urgencias\" si
        existe en el catálogo). Ese requisito tiene prioridad sobre cualquier otra
        alternativa \"más cercana\" del catálogo.

        NUNCA hagas lo siguiente (ejemplo de error real que debes evitar):
        Síntomas: \"tos\", \"congestión nasal\" -> especialidad elegida: \"Odontología\"
        o \"Ginecología\" solo porque eran las únicas especialidades en el catálogo.
        Eso es INCORRECTO: la tos y la congestión nasal no tienen relación con
        odontología ni ginecología. Lo correcto en ese caso sería \"Neumología\" si
        está en el catálogo, o \"Medicina General\" si no lo está.

        COHERENCIA CLÍNICA OBLIGATORIA (ejemplos de referencia, no exhaustivos):

        - Síntomas respiratorios (tos, congestión nasal, gripe, catarro, dolor de
          garganta sin causa dental) -> Neumología, Otorrinolaringología o Medicina
          General; Urgencias si es grave. NUNCA Odontología, Ginecología, Urología
          ni otra especialidad de otro sistema/órgano no relacionado.
        - Síntomas digestivos (dolor abdominal, diarrea, náuseas, vómito,
          colon irritado, gastritis, reflujo) -> Gastroenterología o Medicina
          General. NUNCA Odontología/Dentista salvo que el síntoma sea
          dental, de encías o de boca explícitamente.
        - Síntomas óseos/articulares/musculares o traumatismos -> Traumatología.
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
        - Síntomas dentales, de encías o de boca -> Odontología.
        - Si el motivo de consulta no calza claramente con ninguna
          especialidad específica -> Medicina General.

        La especialidad elegida SIEMPRE debe tener relación médica directa
        y evidente con los síntomas reportados.

        AUTOVERIFICACIÓN OBLIGATORIA ANTES DE RESPONDER:
        Antes de escribir el JSON final, pregúntate: \"¿un médico revisando este
        caso estaría de acuerdo en que esta especialidad tiene relación directa y
        obvia con los síntomas descritos?\". Si la respuesta es no, o si dudas,
        cambia la especialidad a \"Medicina General\" (esté o no en el catálogo)
        en vez de forzar una especialidad incoherente solo por estar en el
        catálogo. Adicionalmente, si el triage fue ROJO, verifica que la
        especialidad elegida sea \"Urgencias\" (si existe en catálogo) antes de
        responder.

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
        {\"diagnostico\":\"\", \"porcentaje\":0},
        {\"diagnostico\":\"\", \"porcentaje\":0},
        {\"diagnostico\":\"\", \"porcentaje\":0}
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

        \"recomendaciones_generales\":\"\",

        \"especialidad\":\"\",

        \"especialidad_fuera_catalogo\": false,

        \"especialidad_ideal_no_disponible\":\"\",

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
        - No inventes especialidades si el sistema encontró una especialidad
          clínicamente coherente en el catálogo.
        - Si no existen medicamentos en la base de datos, proporciona sugerencias generales con una breve descripción.
        - Si ninguna especialidad del catálogo es clínicamente coherente con los
          síntomas, sugiere de todas formas la mejor alternativa disponible en el
          catálogo, marca \"especialidad_fuera_catalogo\": true, indica en
          \"especialidad_ideal_no_disponible\" cuál sería la especialidad ideal, y
          dilo explícitamente en \"justificacion\"/\"motivo_derivacion\" (ver FASE 7);
          nunca fuerces una especialidad no relacionada solo por estar en el
          catálogo, y nunca omitas mencionar que la especialidad ideal no estaba
          disponible.
        - Prioriza siempre la seguridad del paciente.
        - Ante signos de alarma importantes, prioriza la derivación sobre la receta inteligente.
        - El diagnóstico siempre es probable y nunca definitivo.
        - El campo \"recomendaciones_generales\" solo aplica cuando \"tipo\" es
          \"receta_inteligente\"; si \"tipo\" es \"derivacion\", déjalo como cadena vacía.
          - En \"diagnosticos_probables\", los porcentajes de todos los diagnósticos
          listados deben sumar exactamente 100, en números enteros, y el orden debe
          ir del más alto al más bajo.
        - Si el triage es ROJO, la especialidad DEBE ser \"Urgencias\" cuando exista
          en el catálogo (ver regla de coherencia en FASE 3 y FASE 7).

        ";

        try {
            $response = Http::withToken(config('services.ai.key'))
                ->timeout(300)
                ->post('https://api.deepseek.com/chat/completions', [
                    'model' => 'deepseek-v4-flash',
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'response_format' => ['type' => 'json_object'],
                    //'max_tokens' => self::MAX_TOKENS_ANALISIS,
                ]);

            if (!$response->successful()) {
                Log::error('Error HTTP al consultar sugerencia libre IA', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $this->decodificarJsonRespuesta($response, 'sugerirMedicamentoLibre');

        } catch (\Exception $e) {
            Log::error('Excepción al consultar sugerencia libre IA: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Consulta a la IA para análisis clínico - LENGUAJE MÉDICO PROFESIONAL
     *
     * GEMINI-MIGRACIÓN: este método antes llamaba a DeepSeek
     * (deepseek-v4-flash), un modelo con razonamiento extendido que genera
     * un bloque <think>...</think> del MISMO tamaño que la respuesta final
     * antes de escribir el JSON (confirmado en logs: reasoning_tokens ==
     * completion_tokens). Para el prompt de esta función -que ya de por sí
     * pide una nota PSOAPP extensa- ese razonamiento oculto es el principal
     * responsable de los ~2:38 min de tiempo de respuesta en el panel.
     *
     * El prompt clínico (las 9 fases) se dejó TAL CUAL estaba: el doctor
     * sigue recibiendo exactamente la misma nota, los mismos diagnósticos
     * con porcentaje y las mismas alertas. Solo cambiaron:
     *  1) El proveedor: ahora se llama a Gemini (gemini-3.5-flash-lite, el
     *     mismo modelo ya usado en analizarArchivoConVisionGemini) en vez
     *     de DeepSeek.
     *  2) 'thinkingConfig' => ['thinkingBudget' => 0] para apagar el
     *     razonamiento oculto de Gemini, que es donde estaba el tiempo extra.
     *  3) 'responseMimeType' => 'application/json' para forzar JSON, el
     *     equivalente en Gemini al 'response_format' de DeepSeek.
     *  4) Se quitó la repetición del vocabulario médico que aparecía 2 veces
     *     en el mismo prompt (intro y Fase 4); ahora la Fase 4 solo hace
     *     referencia al vocabulario ya indicado arriba, sin reimprimirlo.
     *     Esto no cambia nada de lo que el modelo puede usar, solo evita
     *     tokens de entrada duplicados.
     *
     * FIX ANCLAJE CLÍNICO (síntomas inventados por asociación): se detectó
     * un caso real donde la IA agregó "edema de miembros inferiores" a la
     * lista de síntomas sin que el paciente lo hubiera mencionado en
     * ningún momento de la transcripción, y luego usó ese mismo síntoma
     * inventado para sustentar un diagnóstico diferencial de insuficiencia
     * cardíaca descompensada (30%). Se agregó una advertencia explícita en
     * la FASE 3 y una autoverificación cruzada en la FASE 4 para reducir
     * este tipo de "alucinación por asociación clínica". Esto es texto de
     * prompt (tokens de entrada), no afecta el tiempo de respuesta.
     */
    private function consultarIA(
        $texto,
        array $historial = [],
        $ultimaNota = null
    ) 
    { // Forzamos el límite de ejecución de PHP para evitar cortes inesperados
        set_time_limit(300);
        $historialTexto = '';

        if (!empty($historial)) {
            $historialTexto = implode("\n\n--- REGISTRO ANTERIOR ---\n\n", $historial);
            // Opcional: Validar longitud de caracteres aproximada si el texto de entrada es masivo
            if (mb_strlen($historialTexto) > (self::MAX_TOKENS_ENTRADA * 4)) {
                $historialTexto = mb_substr($historialTexto, -(self::MAX_TOKENS_ENTRADA * 4));
            }
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
        - Sin embargo, si el dato SÍ aparece en el texto (aunque sea mencionado de forma breve o
          entre otros datos), DEBES extraerlo y transcribirlo de forma completa en el apartado
          correspondiente. NUNCA resumas ni omitas datos clínicos objetivos reales (signos vitales,
          hallazgos de exploración física por aparatos y sistemas, resultados de estudios) que el
          texto sí menciona. Es un error tan grave inventar datos como perder/ignorar datos que sí
          fueron proporcionados.
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

        NOTA: independientemente de si el texto es A o B, si dentro del mismo relato el paciente
        o el médico también incluyen datos de EXPLORACIÓN FÍSICA (signos vitales, inspección,
        auscultación, palpación, etc.), esos datos son igual de válidos y DEBEN extraerse con el
        mismo nivel de detalle que los síntomas — no los descartes por venir mezclados con el
        relato del paciente.

        =========================================================
        FASE 2 - EXTRACCIÓN DE INFORMACIÓN CLÍNICA
        =========================================================

        Extrae del texto ya filtrado, de forma EXHAUSTIVA (no omitas ningún dato clínico presente):

        - Síntomas actuales (o hallazgos del estudio, según el caso de la Fase 1).
        - Duración de síntomas si existe.
        - Intensidad si está disponible.
        - Zona anatómica afectada.
        - Antecedentes relevantes (enfermedades crónicas, cirugías previas, medicamentos habituales).
        - Medicamentos mencionados, incluyendo los que el paciente ya tomó por cuenta propia antes
          de la consulta (ej. automedicación) y con qué resultado.
        - Alergias mencionadas (o su negación explícita, ej. 'niega alergias').
        - Factores de riesgo.
        - TODOS los signos vitales mencionados explícitamente: temperatura, frecuencia cardiaca,
          frecuencia respiratoria, presión arterial, saturación de oxígeno, peso, talla — cada uno
          con su valor y unidad exactos, tal como aparecen en el texto.
        - TODOS los hallazgos de exploración física por aparatos y sistemas que se mencionen:
          estado general/apariencia, cabeza y cuello, orofaringe/amígdalas, ganglios linfáticos,
          auscultación cardiopulmonar, exploración abdominal, extremidades, piel, neurológico, etc.
          Extrae cada hallazgo positivo Y cada hallazgo negativo relevante (ej. 'sin sibilancias',
          'abdomen sin dolor a la palpación') porque ambos son clínicamente informativos.

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

        ADVERTENCIA CRÍTICA - FALSOS POSITIVOS POR ASOCIACIÓN CLÍNICA:
        Nunca agregues un síntoma o hallazgo solo porque suele acompañar al cuadro que
        sospechas, aunque sea clínicamente típico de ese diagnóstico. Ejemplo de error
        real a evitar: si el paciente reporta disnea, taquicardia e hipertensión, NO
        agregues 'edema de miembros inferiores' a menos que el texto lo mencione
        explícitamente — aunque ese hallazgo sea típico de insuficiencia cardíaca,
        agregarlo sin que el paciente/estudio lo haya reportado es INVENTAR un dato
        clínico, algo estrictamente prohibido y tan grave como omitir uno real.
        Antes de escribir cada síntoma, pregúntate: \"¿esta palabra o su significado
        aparece literalmente en el texto de origen, o la estoy infiriendo porque
        'suele ir junto' con lo demás?\". Si es lo segundo, NO lo incluyas.

      =========================================================
FASE 4 - DIAGNÓSTICOS PROBABLES (LENGUAJE MÉDICO PROFESIONAL)
=========================================================

Genera un máximo de tres diagnósticos probables utilizando terminología médica precisa.

Para nombrar correctamente el síntoma o hallazgo referido por el paciente
(antes de razonar el diagnóstico probable), usa el vocabulario de referencia
ya indicado al inicio del prompt.

Ordena los diagnósticos desde el más probable hasta el menos probable.

Asigna a cada diagnóstico un porcentaje de probabilidad clínica relativa
(0-100) de acuerdo con la evidencia clínica disponible.

REGLAS OBLIGATORIAS

- La suma de todos los porcentajes debe ser exactamente 100.
- Utiliza únicamente números enteros.
- El primer diagnóstico debe tener el porcentaje más alto.
- Si existe un solo diagnóstico probable, asígnale 100%.
- Si existen dos diagnósticos, ambos deben sumar 100.
- Si existen tres diagnósticos, los tres deben sumar 100.
- Evita repartir porcentajes iguales salvo que la evidencia clínica sea equivalente.
- El porcentaje representa únicamente una estimación clínica orientativa.
- Nunca afirmes que el diagnóstico está confirmado.

AUTOVERIFICACIÓN OBLIGATORIA ANTES DE ASIGNAR PORCENTAJES:
Para cada diagnóstico en \"diagnosticos_probables\", revisa que TODOS los
síntomas/hallazgos que usas como soporte estén también presentes, tal cual,
en el arreglo \"sintomas\" que tú mismo generaste en la FASE 3 (y que a su vez
debe venir del texto real, no de una asociación clínica). Si vas a justificar
un diagnóstico con un hallazgo que no está en tu propio arreglo de síntomas,
ese diagnóstico está mal fundamentado: bájale el porcentaje o elimínalo, no lo
sostengas con datos que no existen en el caso.

FORMATO

Genera únicamente una lista de diagnósticos con su porcentaje de probabilidad.

Cada diagnóstico debe contener únicamente:

- diagnostico
- porcentaje

El formato exacto del JSON será el definido en la FASE 9.
        =========================================================
        FASE 4B - INFORMACIÓN CLÍNICA COMPLEMENTARIA (APOYO EDUCATIVO PARA EL MÉDICO)
        =========================================================

        Redacta un párrafo breve (3 a 6 frases) de contexto clínico general sobre la condición
        probable principal: qué es, fisiopatología básica relevante, y por qué el cuadro descrito
        encaja con esa entidad. Este contenido es CONOCIMIENTO MÉDICO GENERAL de tu entrenamiento
        (no proviene del expediente del paciente) y sirve como apoyo educativo/de referencia para
        el médico, NUNCA como parte de los hallazgos del paciente.

        REGLA DE SEPARACIÓN OBLIGATORIA:
        - Este párrafo va en un campo JSON aparte ('informacion_complementaria'), NUNCA mezclado
          dentro de 'subjetivo', 'objetivo' ni ningún apartado de la nota PSOAPP.
        - No incluyas aquí ningún dato que aparente ser del paciente (nada de 'el paciente
          presenta...'); redacta en tercera persona sobre la condición en general
          (ej. 'La faringoamigdalitis aguda es un proceso inflamatorio...').
        - No cites fuentes, DOIs, nombres de guías ni estudios específicos: al no tener acceso a
          búsqueda en tiempo real, cualquier cita de fuente concreta sería una atribución no
          verificada. Si quieres dar una referencia orientativa de tipo de fuente, usa lenguaje
          genérico ('de acuerdo con guías clínicas generales para el manejo de...') sin inventar
          nombres, autores ni años específicos.
        - Si el diagnóstico principal es 'No disponible' o no hay suficiente certeza clínica para
          dar contexto útil, deja este campo como cadena vacía en vez de inventar contenido.

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
        FASE 6B - SIGNOS DE ALARMA A VIGILAR (PROSPECTIVO)
        =========================================================

        A diferencia de la Fase 6 (que documenta alertas sobre datos YA presentes en el texto),
        aquí genera una lista corta (máximo 5) de signos de alarma PROSPECTIVOS: manifestaciones
        que, de aparecer en la evolución del padecimiento actual, ameritarían que el paciente
        acuda de inmediato a valoración urgente. Deben ser específicos y clínicamente coherentes
        con el diagnóstico probable principal (no una lista genérica de banderas rojas
        universales). Cada elemento debe ser una frase corta y accionable en lenguaje claro
        (ej. 'Fiebre mayor a 39°C que no cede con antipiréticos', 'Dificultad para respirar o
        dolor torácico de inicio súbito', 'Vómito con sangre o en poso de café').

        Si no hay suficiente información para identificar signos de alarma específicos y
        relevantes al caso, devuelve un arreglo vacío en vez de rellenar con genéricos sin
        relación al padecimiento.

        =========================================================
        FASE 7 - RECOMENDACIÓN MÉDICA (PARA EL MÉDICO)
        =========================================================

        Genera una recomendación concreta relacionada con los síntomas o hallazgos del estudio,
        útil para el médico (ej. estudios a considerar, puntos a explorar, líneas de manejo a
        evaluar), con terminología médica precisa.

        Evita frases genéricas como 'Tomar líquidos' o 'Descansar'.

        =========================================================
        FASE 7B - INDICACIONES PARA EL PACIENTE
        =========================================================

        Redacta ahora un texto breve (2 a 5 frases, en prosa clínica pero comprensible para el
        paciente) con las indicaciones que el paciente debe seguir tras la consulta. Este texto
        es lo que el médico revisará/editará y es lo que verá el paciente impreso en su nota.

        NO es lo mismo que la Fase 7: la Fase 7 es una recomendación técnica para el médico;
        esto es una instrucción práctica para el paciente.

        Incluye, según sea clínicamente relevante para el caso (no agregues puntos que no
        apliquen):

        - Reposo o restricción de actividad si el cuadro lo amerita.
        - Dieta, hidratación o cuidados generales coherentes con el diagnóstico probable
          (ej. dieta blanda y evitar irritantes en cuadros gástricos, evitar carga de peso en
          lesiones articulares, etc.).
        - Cuidados locales si aplica (curaciones, higiene de una herida, uso de frío/calor local).
        - Señales de alarma específicas por las que debe acudir a urgencias de inmediato.
        - Cuándo debe regresar a consulta o control (ej. 'si no hay mejoría en X días').

        PROHIBIDO:

        - NO indiques dosis, frecuencia ni duración de ningún medicamento (eso va en la receta,
          no aquí).
        - NO repitas literalmente el contenido del campo 'recomendacion' de la Fase 7.
        - NO uses relleno genérico sin relación con el caso ('tome agua', 'descanse') si no hay
          nada específico que agregar; en ese caso deja el texto breve pero igual anclado al
          padecimiento reportado, nunca vacío salvo que el texto de origen no tenga información
          clínica suficiente (en ese caso, escribe 'No disponible - pendiente de valoración
          presencial').

        =========================================================
        FASE 8 - NOTA CLÍNICA POR APARTADOS (PSOAPP)
        =========================================================

        Redacta la nota de evolución en los 6 apartados del formato PSOAPP. Usa ÚNICAMENTE
        información presente en el texto (no inventes signos vitales, hallazgos de exploración
        física ni estudios que no fueron mencionados). Cada apartado debe ser un párrafo en prosa
        clínica profesional, TAN DETALLADO Y COMPLETO COMO LO PERMITA LA INFORMACIÓN DISPONIBLE EN
        EL TEXTO. Regla general para las 6 secciones: si el texto trae el dato, DEBE aparecer en
        el apartado correspondiente, con su valor exacto — no lo resumas ni lo generalices, y no
        lo reemplaces por 'No disponible' si en realidad sí viene en el texto.

        - presentacion: Datos demográficos disponibles (edad, sexo, ocupación si se mencionan),
          motivo de consulta, tiempo de evolución del padecimiento actual, y TODOS los
          antecedentes de importancia mencionados (enfermedades crónicas o su negación, alergias
          o su negación, cirugías previas, medicamentos de uso habitual).

        - subjetivo: Padecimiento actual narrado cronológicamente de forma completa: inicio,
          localización, intensidad, características, agravantes y aliviantes, evolución en el
          tiempo, síntomas asociados referidos (enuméralos todos, no solo los principales), y
          cualquier tratamiento o medicamento que el paciente ya haya tomado antes de la
          evaluación, incluyendo si le dio o no mejoría.

        - objetivo: Esta sección es EXCLUSIVAMENTE para datos objetivos (medidos u observados por
          el examinador), NUNCA para lo que el paciente narra sentir. Transcribe de forma completa
          y ordenada TODO lo que el texto reporte de:
            • Signos vitales: temperatura, frecuencia cardiaca, frecuencia respiratoria, presión
              arterial, saturación de oxígeno, peso y talla — cada uno con su valor y unidad
              exactos tal como se mencionan (ej. 'Temperatura 37.8°C, FC 86 lpm, FR 18 rpm, TA
              118/76 mmHg').
            • Estado general/apariencia del paciente a la inspección.
            • Hallazgos de exploración física por regiones/aparatos y sistemas, en el orden en que
              se mencionen o de forma cefalocaudal: cabeza y cuello, orofaringe/amígdalas, ganglios
              linfáticos, tórax/auscultación cardiopulmonar, abdomen, extremidades, piel,
              neurológico, etc. Incluye tanto los hallazgos positivos (anormales) como los
              negativos relevantes que el examinador haya reportado explícitamente (ej. 'sin
              sibilancias ni estertores', 'abdomen blando, depresible, sin dolor a la palpación'),
              ya que descartar hallazgos también es información clínica objetiva válida.
            • Resultados de estudios de laboratorio o imagen, si se mencionan.
          Si el texto es ÚNICAMENTE un relato de síntomas sin ningún dato de exploración física ni
          estudios (es decir, no hay NINGÚN signo vital ni hallazgo de exploración en el texto),
          entonces sí escribe: 'No disponible - pendiente de exploración física y estudios
          complementarios por el médico tratante'. Pero si el texto SÍ trae exploración física,
          usa ese contenido completo — nunca actives este texto de reemplazo si hay datos objetivos
          reales disponibles.

        - analisis: Razonamiento clínico integrando lo subjetivo y lo objetivo (haciendo
          referencia explícita a los signos vitales y hallazgos relevantes de la exploración
          cuando existan), e impresión diagnóstica probable (nunca definitiva) del diagnóstico
          PRINCIPAL, mencionando brevemente que se consideraron diagnósticos diferenciales
          (sin repetir aquí el detalle completo de cada uno, ya que van en su propio campo).
          Menciona si las comorbilidades referidas parecen controladas o descompensadas, solo si
          hay datos para inferirlo.

        - plan: Sugerencias concretas de plan diagnóstico (estudios que podrían solicitarse,
          justificando brevemente por qué, incluyendo si algún estudio ayudaría a distinguir
          entre el diagnóstico principal y los diferenciales) y plan terapéutico/educativo a nivel
          de apoyo a la decisión, SIN indicar dosis, vía ni frecuencia de medicamentos (eso lo
          define el médico). Incluye vigilancia de signos de alarma específicos relacionados con
          el diagnóstico probable, y criterios de reevaluación o seguimiento.

        - pronostico: Estimación general (reservado/bueno/malo) para la vida y para la función, y
          los factores concretos de los que dependería la evolución (ej. apego al tratamiento,
          control de comorbilidades, ausencia de complicaciones), siempre aclarando que es una
          estimación preliminar sujeta a valoración médica presencial.

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

\"diagnosticos_probables\": [
{
\"diagnostico\": \"\",
\"porcentaje\": 0
}
],

\"diagnosticos_diferenciales\": [
\"\"
],

\"informacion_complementaria\": \"\",

\"signos_alarma_vigilar\": [
\"\"
],

\"recomendacion\": \"\",

\"indicaciones_medico\": \"\",

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
        - Tampoco omitas ni resumas en exceso datos objetivos (signos vitales, hallazgos de
          exploración física, resultados de estudios) que el texto sí proporcione: deben quedar
          reflejados de forma completa en el apartado 'objetivo'.
        - Si un dato no aparece, usa 'No disponible' o 'Sin datos suficientes en la transcripción'.
        - El diagnóstico siempre es probable y nunca definitivo.
        - Prioriza siempre la seguridad del paciente al evaluar riesgo y alertas.
        - Nunca inventes alertas: solo genéralas si hay evidencia real en el texto.
        - El campo \"indicaciones_medico\" nunca debe repetir literalmente el campo
          \"recomendacion\": uno es técnico para el médico, el otro es la instrucción práctica
          para el paciente.
        - El campo \"informacion_complementaria\" es conocimiento médico general de apoyo, nunca
          datos del paciente; el campo \"diagnosticos_diferenciales\" nunca debe incluir el mismo
          texto que el campo \"diagnostico\"; el campo \"signos_alarma_vigilar\" es prospectivo
          (qué vigilar a futuro), distinto de \"alertas\" (qué ya se detectó en el texto actual).
        - Ningún síntoma, hallazgo o dato usado para justificar un diagnóstico puede provenir de
          asociación clínica; debe estar anclado literalmente en el texto de origen (ver
          advertencia de la FASE 3 y la autoverificación de la FASE 4).

        ";

        try {

            // GEMINI-MIGRACIÓN: antes se llamaba a DeepSeek
            // (https://api.deepseek.com/chat/completions, deepseek-v4-flash).
            // Ahora se usa Gemini con thinkingBudget=0 para evitar el
            // razonamiento oculto que estaba disparando el tiempo de
            // respuesta. El prompt ($prompt) es exactamente el mismo de
            // siempre, con las 9 fases completas.
            $apiKey = env('GEMINI_API_KEY');
            $modelo = 'gemini-3.5-flash-lite';
            $urlGemini = "https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key={$apiKey}";

            $inicio = microtime(true);

            // DIAGNÓSTICO TEMPORAL: se quitó 'thinkingConfig' de aquí. Si el
            // 400 INVALID_ARGUMENT desaparece con este cambio, confirma que
            // gemini-3.5-flash-lite no acepta ese campo (algunos modelos
            // "lite" no tienen modo de pensamiento que apagar, y Gemini
            // rechaza la petición completa si el campo no aplica). Si el 400
            // persiste, el problema es la API key (ver GEMINI_API_KEY en el
            // .env, revisar que tenga el formato AIzaSy... de Google AI Studio).
            $response = $this->llamarGeminiConReintentos(
                $urlGemini,
                [['text' => $prompt]],
                3,
                [
                    'responseMimeType' => 'application/json',
                ]
            );

            $fin = microtime(true);
            Log::info('Tiempo respuesta Gemini (consultarIA)', [
                'segundos' => round($fin - $inicio, 2)
            ]);

            if (!$response->successful()) {
                // Log::error con el body COMPLETO sin truncar, para poder ver
                // el campo "details" de Google si viene (ahí suele explicar
                // exactamente qué parte del payload rechazó).
                Log::error('Error HTTP al consultar IA clínica (Gemini)', [
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ]);
                return null;
            }

            $contenido = $response->json('candidates.0.content.parts.0.text');

            return $this->decodificarJsonDesdeTexto($contenido, 'consultarIA', $response->json() ?? []);

        } catch (\Exception $e) {
            Log::error('Excepción al consultar IA clínica: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Analiza signos vitales del triage y devuelve prioridad + estado para la tabla
     */
    public function analizarTriage(array $datos): array
    {   
        // Preparamos el texto de cada signo vital: si no viene valor, se manda "No registrada"
        // en vez de dejar que un null/0 se lea como si fuera una medición real.
        $presionTxt     = !empty($datos['presion'])     ? $datos['presion']     . ' mmHg' : 'No registrada';
        $saturacionTxt  = !empty($datos['saturacion'])  ? $datos['saturacion']  . '%'     : 'No registrada';
        $temperaturaTxt = !empty($datos['temperatura']) ? $datos['temperatura'] . '°C'    : 'No registrada';
            
        $prompt = "
            Eres un sistema experto en triage médico de urgencias hospitalarias.
            Analiza los siguientes signos vitales y síntomas del paciente.
            
            Síntomas: {$datos['sintomas']}
            Presión Arterial: {$presionTxt}
            Saturación O₂: {$saturacionTxt}
            Temperatura: {$temperaturaTxt}

            IMPORTANTE: Si un signo vital dice \"No registrada\", significa que NO fue medido todavía.
            Nunca lo interpretes como un valor real de 0 ni como un dato crítico. Basa tu análisis
            únicamente en los signos vitales que sí tengan un valor, y en los síntomas descritos.

            Clasifica al paciente según el Sistema de Triage Manchester (MTS) de 5 niveles:

            ROJO     → Nivel 1. Emergencia / Reanimación. Riesgo vital inmediato. Atención en 0 a 3 minutos.
            NARANJA  → Nivel 2. Muy urgente. Riesgo alto. Atención en menos de 10 a 15 minutos.
            AMARILLO → Nivel 3. Urgente. Riesgo moderado. Atención en 30 a 60 minutos.
            VERDE    → Nivel 4. Urgencia menor. Paciente estable. Atención hasta 120 minutos.
            AZUL     → Nivel 5. No urgente. Puede esperar hasta 180 minutos o derivarse a consulta externa.

            Estado clínico:
            - grave    → corresponde a ROJO o NARANJA
            - moderado → corresponde a AMARILLO
            - leve     → corresponde a VERDE o AZUL

            Devuelve EXCLUSIVAMENTE este JSON sin texto adicional ni Markdown:
            {
            \"prioridad\": \"rojo|naranja|amarillo|verde|azul\",
            \"estado\": \"grave|moderado|leve\",
            \"justificacion\": \"Una sola oración corta explicando la clasificación\"
            }
                ";

        try {
            $response = Http::withToken(config('services.ai.key'))
                ->timeout(15)
                ->post('https://api.deepseek.com/chat/completions', [
                    'model'           => 'deepseek-v4-flash',
                    'messages'        => [['role' => 'user', 'content' => $prompt]],
                    'response_format' => ['type' => 'json_object'],
                    'temperature'     => 0.1,
                ]);

            if (!$response->successful()) {
                Log::error('Error HTTP triage IA', ['status' => $response->status()]);
                return $this->triageFallback();
            }

            $data = json_decode($response->json('choices.0.message.content'), true);

            if (!isset($data['prioridad'], $data['estado'])) {
                Log::error('Respuesta de triage IA incompleta', ['data' => $data]);
                return $this->triageFallback();
            }

            $prioridad = strtolower($data['prioridad']);

            if (!in_array($prioridad, ['rojo', 'naranja', 'amarillo', 'verde', 'azul'], true)) {
                Log::warning('IA devolvió una prioridad de triage no reconocida', ['data' => $data]);
                return $this->triageFallback();
            }

            return [
                'prioridad'     => $prioridad,
                'estado'        => strtolower($data['estado']),
                'justificacion' => $data['justificacion'] ?? '',
                'fuente'        => 'ia',
            ];

        } catch (\Exception $e) {
            Log::error('Excepción triage IA: ' . $e->getMessage());
            return $this->triageFallback();
        }
    }

    private function triageFallback(): array
    {
        // Ojo: por seguridad del paciente, si la IA falla NO asumimos "verde"
        // (podría dejar a alguien grave esperando 120 min sin que nadie lo note).
        // "amarillo" fuerza revisión relativamente pronto mientras un humano evalúa manualmente.
        return [
            'prioridad'     => 'amarillo',
            'estado'        => 'moderado',
            'justificacion' => 'IA no disponible. Clasificación de respaldo — requiere revisión manual.',
            'fuente'        => 'fallback',
        ];
    }




    /** 
     * Decodifica de forma segura el contenido JSON devuelto por la IA,
     * distinguiendo explícitamente entre "la IA respondió y el JSON es
     * válido" y "la IA respondió pero el JSON viene incompleto/corrupto"
     * (típicamente porque la respuesta se cortó por límite de tokens).
     * Loguea el contenido crudo en el segundo caso, para poder diagnosticar
     * sin tener que reproducir el error a ciegas.
     *
     * GEMINI-MIGRACIÓN: se dejó esta firma tal cual (recibe el objeto
     * $response de DeepSeek) para no romper clasificarTriage() ni
     * sugerirMedicamentoLibre(), que siguen usando DeepSeek sin cambios.
     * Internamente ahora delega en decodificarJsonDesdeTexto(), que es la
     * que también usa consultarIA() con la respuesta de Gemini.
     */
    private function decodificarJsonRespuesta($response, string $origen)
    {
        $contenido = $response->json('choices.0.message.content');
        return $this->decodificarJsonDesdeTexto($contenido, $origen, $response->json() ?? []);
    }

    /**
     * Versión genérica del decodificador: recibe directamente el texto de
     * contenido (venga de DeepSeek en 'choices.0.message.content' o de
     * Gemini en 'candidates.0.content.parts.0.text') y el arreglo completo
     * de la respuesta HTTP solo para poder loguearlo y extraer el uso de
     * tokens ('usage' en DeepSeek, 'usageMetadata' en Gemini).
     */
    private function decodificarJsonDesdeTexto(?string $contenido, string $origen, array $respuestaCompletaParaLog = [])
    {
        if (empty($contenido)) {
            Log::error("IA devolvió contenido vacío ({$origen})", [
                'respuesta_completa' => $respuestaCompletaParaLog,
            ]);
            return null;
        }

        // FIX: deepseek-v4-flash (modelo con razonamiento) a veces devuelve
        // su cadena de pensamiento envuelta en <think>...</think> DENTRO del
        // mismo campo "content", antes del JSON final -- aunque
        // "reasoning_content" ya viene separado en la respuesta, el modelo
        // duplica ese razonamiento inline en "content" en algunos casos (se
        // ve confirmado en los logs: reasoning_tokens == completion_tokens).
        // Si no se quita, el string ya no empieza en "{" y json_decode()
        // falla de inmediato, aunque el JSON real que viene después esté
        // perfecto -- esto era lo que causaba el "No se pudo determinar"
        // pese a que la IA sí había respondido bien. Se deja este strip
        // como red de seguridad general (por si algún día se reactiva
        // razonamiento en cualquiera de los dos proveedores), aunque con
        // thinkingBudget=0 en Gemini ya no debería aparecer contenido aquí.
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
            Log::error("JSON inválido o incompleto devuelto por la IA ({$origen})", [
                'json_error' => json_last_error_msg(),
                'longitud_contenido' => strlen($contenido),
                'contenido_crudo' => $contenido,
            ]);
            return null;
        }

        // FIX: el uso de tokens viene fuera del JSON que genera el modelo.
        // DeepSeek lo trae en la clave 'usage' de la respuesta HTTP; Gemini
        // lo trae en 'usageMetadata'. Se soportan ambos formatos aquí para
        // que $data['debug_usage'] siempre viaje correctamente hasta
        // analizarTranscripcion() -> el controlador -> el frontend, sin
        // importar qué proveedor respondió.
        $data['debug_usage'] = $respuestaCompletaParaLog['usage']
            ?? $respuestaCompletaParaLog['usageMetadata']
            ?? null;

        return $data;
    }
}