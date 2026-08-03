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
        // ------------------------------------
        
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

            // IMPRESIÓN DIRECTA PARA DEBUG (Se imprime pase lo que pase con el status)
            Log::info('Respuesta cruda de DeepSeek Receta Inteligente:', [
                'status' => $response->status(),
                'usage' => $response->json('usage'), // Aquí verás exactamente los tokens
                'body_error' => $response->successful() ? null : $response->body()
            ]);

            if (!$response->successful()) {
                Log::error('Error HTTP al clasificar triage con IA', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return $this->resultadoTriage('estable');
            }

            $data = $this->decodificarJsonRespuesta($response, 'clasificarTriage');

            // 1. Validamos primero que sea un array válido
            if (!is_array($data)) {
                Log::warning('La respuesta de la IA no pudo decodificarse como array en clasificarTriage.');
                return $this->resultadoTriage('estable');
            }

            $nivel = strtolower(trim($data['nivel'] ?? ''));

            if (!in_array($nivel, ['leve', 'estable', 'grave', 'urgente'], true)) {
                Log::warning('IA devolvió un nivel de triage no reconocido', ['respuesta' => $data]);
                return $this->resultadoTriage('estable');
            }
            
            // 2. Generamos el resultado base de triage
            $resultadoTriage = $this->resultadoTriage($nivel);
            
            // 3. Inyectamos de forma segura el uso de tokens al resultado final para que viaje al frontend
            if (is_array($resultadoTriage)) {
                $resultadoTriage['debug_usage'] = $response->json('usage');
            }

            return $resultadoTriage;

            // AQUí AGREGAMOS EL USO PARA QUE VIAJE AL FRONTEND
            // if (is_array($data)) {
            //     $data['debug_usage'] = $response->json('usage');
            // }

            



           

            // return $this->resultadoTriage($nivel);

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

        \"recomendaciones_generales\":\"\",

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
        - El campo \"recomendaciones_generales\" solo aplica cuando \"tipo\" es
          \"receta_inteligente\"; si \"tipo\" es \"derivacion\", déjalo como cadena vacía.

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

            // IMPRESIÓN DIRECTA PARA DEBUG (Se imprime pase lo que pase con el status)
            Log::info('Respuesta cruda de DeepSeek Sugerir Medicamentos:', [
                'status' => $response->status(),
                'usage' => $response->json('usage'), // Aquí verás exactamente los tokens
                'body_error' => $response->successful() ? null : $response->body()
            ]);


            if (!$response->successful()) {
                Log::error('Error HTTP al consultar sugerencia libre IA', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            // 1. Primero decodificamos la respuesta de la IA en la variable $data
            $data = $this->decodificarJsonRespuesta($response, 'sugerirMedicamentoLibre');
            
            // AQUí AGREGAMOS EL USO PARA QUE VIAJE AL FRONTEND
            if (!is_array($data)) {
                Log::warning('La respuesta de la IA no pudo decodificarse como array en sugerirMedicamentoLibre.');
                return null;
            }
            // 3. Inyectamos de forma segura la información de uso de tokens
            $data['debug_usage'] = $response->json('usage');

            // 4. Retornamos el array completo con el debug incluido
            return $data;

            // return $this->decodificarJsonRespuesta($response, 'sugerirMedicamentoLibre');

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
    ) 
    { 
        // Forzamos el límite de ejecución de PHP para evitar cortes inesperados
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

        =========================================================
        FASE 4 - DIAGNÓSTICO PROBABLE Y DIAGNÓSTICOS DIFERENCIALES - NOMBRES TÉCNICOS
        =========================================================

        Genera una condición probable PRINCIPAL basada únicamente en los síntomas o, si aplica, en
        la impresión diagnóstica del estudio adjunto. UTILIZA EL NOMBRE TÉCNICO COMPLETO de la
        entidad clínica.

        Además del diagnóstico principal, construye una lista de HASTA 4 DIAGNÓSTICOS
        DIFERENCIALES: otras entidades clínicas razonablemente compatibles con el mismo cuadro,
        que el médico debería considerar y descartar antes de confirmar el diagnóstico principal.
        Para cada diferencial incluye, en una sola frase, el dato clínico concreto del texto que lo
        hace plausible y — si aplica — el dato que ayudaría a descartarlo (qué se necesitaría
        explorar o solicitar). No repitas el diagnóstico principal dentro de los diferenciales. Si
        el cuadro es tan característico que razonablemente no hay diferenciales relevantes que
        aportar, devuelve un arreglo vacío; no rellenes con entidades poco plausibles solo para
        completar la lista.

        Ejemplo de diferencial bien construido (no copiar literal, es solo formato):
        'Faringoamigdalitis viral: la ausencia de exudado purulento y la tos referida por el
        paciente son más compatibles con etiología viral que bacteriana; de persistir la fiebre
        más de 48-72h o aparecer exudado, valorar prueba rápida de estreptococo.'

        REGLA CRÍTICA - NO INVENTES ETIOLOGÍA NI AGENTE CAUSAL:

        El nombre técnico de la condición NO es lo mismo que su causa específica. Puedes nombrar
        la entidad clínica (ej. 'Gastritis aguda'), pero NUNCA le agregues un agente causal,
        microorganismo o etiología específica (ej. 'por Helicobacter pylori', 'estreptocócica',
        'viral', 'bacteriana') a menos que ese agente venga EXPLÍCITAMENTE confirmado en el texto
        (por ejemplo, un resultado de laboratorio o cultivo ya reportado), o que lo estés usando
        dentro de un diagnóstico DIFERENCIAL como hipótesis a valorar (ahí sí es válido plantear
        la etiología como posibilidad a descartar, siempre y cuando quede claro que es hipotética
        y no confirmada). Si el texto es solo el relato de síntomas del paciente, sin estudios que
        confirmen el agente causal, omite la etiología del diagnóstico PRINCIPAL por completo.

        Incorrecto (etiología inventada como si fuera un hecho, sin respaldo en el texto):
        'Gastritis aguda por Helicobacter pylori'
        'Faringoamigdalitis aguda estreptocócica'

        Correcto (misma entidad clínica, sin inventar el agente causal en el diagnóstico principal):
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

        Nunca afirmes que el diagnóstico principal es definitivo.

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
            // IMPRESIÓN DIRECTA PARA DEBUG (Se imprime pase lo que pase con el status)
            Log::info('Respuesta cruda de DeepSeek Diagnostico:', [
                'status' => $response->status(),
                'usage' => $response->json('usage'), // Aquí verás exactamente los tokens
                'body_error' => $response->successful() ? null : $response->body()
            ]);

            if (!$response->successful()) {
                Log::error('Error HTTP al consultar IA clínica', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }
            
            // 1. Primero decodificamos la respuesta en la variable $data
            $data = $this->decodificarJsonRespuesta($response, 'consultarIA');

            
            // AQUí AGREGAMOS EL USO PARA QUE VIAJE AL FRONTEND
            if (!is_array($data)) {
                Log::warning('La respuesta de la IA no pudo decodificarse como array en consultarIA.');
                return null;
            }

            $data['debug_usage'] = $response->json('usage');
            return $data;
            // return $this->decodificarJsonRespuesta($response, 'consultarIA');

        } catch (\Exception $e) {
            Log::error('Excepción al consultar IA clínica: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Decodifica de forma segura el contenido JSON devuelto por la IA,
     * distinguiendo explícitamente entre "la IA respondió y el JSON es
     * válido" y "la IA respondió pero el JSON viene incompleto/corrupto"
     * (típicamente porque la respuesta se cortó por límite de tokens).
     * Loguea el contenido crudo en el segundo caso, para poder diagnosticar
     * sin tener que reproducir el error a ciegas.
     */
    private function decodificarJsonRespuesta($response, string $origen)
    {
        $contenido = $response->json('choices.0.message.content');

        if (empty($contenido)) {
            Log::error("IA devolvió contenido vacío ({$origen})", [
                'respuesta_completa' => $response->json(),
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

        // FIX: el uso de tokens (prompt/completion/total) viene en el
        // bloque "usage" de la respuesta HTTP de DeepSeek, NO dentro del
        // JSON que genera el modelo — nunca se le pidió a la IA que lo
        // incluyera en su propio JSON de salida (ver esquema de la Fase 8/9
        // de cada prompt: no existe una clave "debug_usage" ahí). Lo
        // agregamos aquí para que $data['debug_usage'] exista y viaje
        // correctamente hasta analizarTranscripcion() -> el controlador
        // -> el frontend.
        $data['debug_usage'] = $response->json('usage');

        return $data;
    }
}