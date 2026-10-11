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
 * ocupacionales, Accidentes de trabajo y Espirometría).
 *
 * DECISIÓN DE ARQUITECTURA: este servicio duplica intencionalmente la
 * infraestructura genérica de IAClinicaService.php en vez de extraerla a un
 * trait compartido, para aislar el radio de impacto de este módulo nuevo.
 * Es deuda técnica aceptada; extraer un trait queda pendiente para cuando
 * este servicio lleve tiempo estable en producción.
 */
class IAClinicaTrabajoService
{
    private const MAX_TOKENS_ANALISIS = 30000;
    private const MAX_TOKENS_ENTRADA = 20000;

    // =========================================================
    // MÉTODOS PÚBLICOS — ANÁLISIS DE MEDICINA DEL TRABAJO
    // =========================================================

    /**
     * Analiza la narrativa libre de un accidente de trabajo y sugiere el
     * llenado de los campos estructurados de `accidentes_trabajo`.
     *
     * La IA solo PRELLENA; el médico revisa antes de guardar. Campos
     * administrativos (fecha exacta, lugar, folio, notificación IMSS,
     * secuela, fecha de alta) no se incluyen a propósito.
     *
     * @param string $narrativa Texto/dictado de lo ocurrido.
     * @param array $normasCandidatas Normas asociadas al puesto:
     *   ['id' => ..., 'codigo' => ..., 'titulo' => ...]. La IA solo puede
     *   sugerir normas de esta lista.
     * @param string|null $instruccionAdicional Indicación extra del médico.
     *
     * @return array|null JSON decodificado o null si falló la llamada/parseo.
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
     * Analiza la transcripción de una ronda de dictado de una valoración
     * ocupacional. Acumula rondas sin perder lo ya capturado.
     *
     * El IMC NO se pide a la IA: se calcula en el backend.
     *
     * @param string $textoRonda Texto de la ronda actual.
     * @param array $historial Texto de rondas anteriores de esta valoración.
     * @param array|null $datosPuesto ['nombre' => ..., 'descripcion_actividades' => ...,
     *   'riesgos_activos' => [...]]
     * @param array $normasCandidatas Normas asociadas al puesto (ver arriba).
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

    /**
     * Analiza el dictado/chat completo de la Ficha Médica Ocupacional.
     * La IA solo PROPONE; el médico revisa y confirma.
     */
    public function analizarFichaOcupacional(string $textoCompleto): ?array
    {
        set_time_limit(120);

        $vocabulario = DiccionarioMedico::textoReferencia();

        $prompt = <<<PROMPT
            Eres un co-piloto experto en Medicina del Trabajo que apoya a un médico a llenar la Ficha Médica Ocupacional a partir del dictado/chat de la consulta.

            Tu tarea tiene dos partes muy claras con reglas distintas:
            1. **Clasificación Estricta**: Para los campos 'empresa', 'puesto_trabajo', 'descripcion_puesto', 'epp_requerido', 'antecedentes', 'examen_fisico' y 'examenes_complementarios'. Aquí solo debes estructurar y resumir lo que ya está mencionado en el texto. Si un dato no se menciona, responde estrictamente con "". NUNCA inventes ni asumas datos clínicos.
            2. **Sugerencia Proactiva**: Para los campos 'departamento', 'antiguedad', 'dictamen', 'recomendaciones' y 'sintomas_detectados'. Aquí la IA DEBE proponer de manera inteligente y proactiva sugerencias automatizadas basadas en su conocimiento de medicina ocupacional y los riesgos del puesto descritos, para que el médico los valide o edite.

            Vocabulario de referencia (coloquial -> término médico):
            {$vocabulario}

            TEXTO DE LA CONSULTA:
            "{$textoCompleto}"

            REGLAS GENERALES
            - Devuelve EXCLUSIVAMENTE el JSON indicado, sin Markdown ni texto extra.
            - Respeta las negaciones: "sin limitación", "niega alergias" o "sin dolor" describen AUSENCIA de hallazgos; no son restricciones ni antecedentes positivos.
            - Copia el contenido clínico con fidelidad, pero SIN encabezados ni frases introductorias del dictado (quita cosas como "En el historial clínico:", "El EPP requerido incluye:", "Exámenes complementarios:").

            CAMPOS
            - empresa: razón social completa, incluyendo el sufijo (ej. "S.A. de C.V.").
            - departamento: área o departamento. Si no se menciona explícitamente, infiere un área lógica según el puesto (ej. "Operaciones", "Construcción", "Mantenimiento", "Producción").
            - puesto_trabajo: solo el nombre del puesto, sin la empresa.
            - antiguedad: antigüedad en la empresa. Si el texto no menciona años/meses y es evaluación periódica, coloca "No especificada (evaluación periódica)". Si es de ingreso, "Nuevo ingreso".
            - descripcion_puesto: actividades/tareas del puesto, en una o dos frases.
            - riesgos: arreglo con las claves de los factores a los que el trabajador está expuesto POR SU PUESTO. Solo estas claves: "ruido", "quimicos", "polvos", "vibraciones", "ergonomicos", "psicosociales", "alturas".
            - epp_requerido: lista de EPP separada por comas.
            - antecedentes: antecedentes patológicos, quirúrgicos, alérgicos, hábitos y ocupacionales dictados.
            - examen_fisico: SOLO hallazgos de la exploración física.
            - examenes_complementarios: resultados de laboratorio/gabinete (espirometría, audiometría, antidoping, RX, etc.).
            - dictamen: Sugiere de forma inteligente y razonable un dictamen de aptitud basándote en el análisis de la salud, exploración física, y exámenes complementarios del trabajador en relación con los riesgos de su puesto. Solo responde con "apto", "apto_con_restricciones" o "no_apto". (Ejemplo: si la exploración y exámenes complementarios son normales y sanos, debe sugerir "apto").
            - restricciones: solo si el dictamen sugerido es "apto_con_restricciones" y se mencionan limitaciones; si no, "".
            - recomendaciones: Genera y sugiere proactivamente de 2 a 4 recomendaciones preventivas, de seguridad, higiene o de vigilancia de la salud oportunas según los riesgos de este puesto (ej. uso continuo y obligatorio del EPP auditivo, visual o respiratorio; higiene postural; exámenes de espirometría y audiometría periódicos anuales; pausas activas; hidratación constante). ¡Debes proponerlas proactivamente de forma automatizada y completa, nunca las dejes vacías!
            - sintomas_detectados: un arreglo JSON de textos cortos (de 2 a 5 palabras cada uno) que representen de forma exhaustiva TODOS los factores de riesgo de exposición descritos para el puesto de trabajo (ej. "Ruido Elevado", "Humos de soldadura", "Sustancias químicas", "Trabajo en alturas", "Vibraciones") y los hallazgos o resultados clínicos de relevancia (ej. "Espirometría normal", "Audiometría normal", "Antidoping negativo"). ¡Extrae de forma detallada cada factor del texto!

            {"empresa":"","departamento":"","puesto_trabajo":"","antiguedad":"","descripcion_puesto":"","riesgos":[],"epp_requerido":"","antecedentes":"","examen_fisico":"","examenes_complementarios":"","dictamen":"","restricciones":"","recomendaciones":"","sintomas_detectados":[]}
            PROMPT;

        return $this->consultarGeminiTrabajo($prompt, 'analizarFichaOcupacional');
    }

    /**
     * Consulta a la IA con contexto de la ficha médica ocupacional
     * para responder preguntas del médico en tiempo real.
     */
    public function consultarMedicinaOcupacional(
        string $mensaje,
        int $pacienteId,
        array $contexto = []
    ): ?array {
        set_time_limit(60);

        $vocabulario = DiccionarioMedico::textoReferencia();

        $contextoTexto = '';
        if (!empty($contexto)) {
            $contextoTexto = "\nCONTEXTO ACTUAL DE LA FICHA:\n" . json_encode($contexto, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        $prompt = "
        Eres un asistente experto en Medicina del Trabajo que apoya al médico durante la consulta ocupacional en tiempo real.

        {$contextoTexto}

        Vocabulario de referencia (coloquial -> término médico):
        {$vocabulario}

        PREGUNTA DEL MÉDICO:
        \"{$mensaje}\"

        REGLAS:
        - Responde de forma concisa, profesional y útil para el médico
        - Si la pregunta es sobre llenar un campo específico, da la respuesta directa
        - Si es una pregunta clínica, da orientación basada en medicina ocupacional
        - Si pide cálculos (IMC, etc.), hazlos con los datos del contexto
        - NO inventes datos que no estén en el contexto
        - Si no tienes información suficiente, dilo claramente

        Devuelve EXCLUSIVAMENTE el siguiente JSON:
        {
            \"respuesta\": \"tu respuesta aquí\",
            \"campos_completados\": {}
        }

        En \"campos_completados\" solo incluye campos si el médico pidió explícitamente llenarlos y tú tienes la info del contexto. Formato: {\"campo\": \"valor\"}.
        ";

        return $this->consultarGeminiTrabajo($prompt, 'consultarMedicinaOcupacional');
    }

    /**
     * Analiza un informe de espirometría en PDF con texto nativo y devuelve
     * las maniobras, valores predichos y calidad de sesión.
     *
     * Solo procesa PDFs con texto digital. Imágenes y PDFs escaneados
     * devuelven null (el controlador responde 422).
     *
     * @param string $rutaCompleta Ruta absoluta al archivo.
     * @param string $mime Tipo MIME del archivo.
     * @return array|null Estructura lista para el frontend, o null si falla.
     */
    public function analizarEspirometria(string $rutaCompleta, string $mime): ?array
    {
        set_time_limit(300);


        $promptEspirometria = <<<PROMPT
Eres un extractor de datos clínicos especializado en informes de espirometría ndd Medical Technologies.

Tu tarea es devolver CADA valor asociado a su columna por NOMBRE, nunca por posición. Usa la tabla del informe (y la imagen de la página si se adjunta) como fuente principal.

ORDEN DE LAS COLUMNAS EN EL TEXTO EXTRAÍDO (de izquierda a derecha en el texto, que es el inverso del orden visual del informe):
%Pred | Prueba 3 | Prueba 4 | Prueba 6 | Mejor | LLN | Pred | Parámetro

Esta es solo la referencia del orden en el texto. Para asignar cada valor, identifica el encabezado de su columna y úsalo.

REGLAS POR FILA:
- Filas con 7 valores numéricos (FVC, FEV1, FEV1/FVC, FEF25-75%, PEF, FIVC): asigna por columna: %Pred, Prueba 3, Prueba 4, Prueba 6, Mejor, LLN, Pred.
- Filas con guiones '--' o '---' pegados al nombre (BEV, PIF, EOTV, FET u otras sin Pred/LLN): 'pred', 'lln' y 'mejor' son null SOLO si el informe no muestra valor en esas columnas. Si hay un valor en la columna Mejor, debe extraerse.
- Si una fila tiene 4 valores (Mejor + 3 pruebas), asigna en orden: Mejor, Prueba 6, Prueba 4, Prueba 3. Nunca desplaces valores entre filas.
- Si una columna está vacía en el informe, usa null. NUNCA desplaces valores para rellenar huecos.

REGLAS DE EXTRACCIÓN:
1. ASTERISCOS (*): "3.85*" → 3.85. Ignora el asterisco; nunca omitas el valor.
2. SIGNOS: conserva el signo tal como aparece en el informe. "0.10" es positivo; "-0.10" es negativo. No cambies signos.
3. NUNCA inventes datos ni corrijas valores atípicos (un valor bajo como 0.58 es válido si aparece).
4. CALIDAD DE SESIÓN: de "A(FEV1 Var=0.08L(1.9%); FVC Var=0.05L(0.9%))" extrae calidad_sesion="A", fev1_var=0.08, fvc_var=0.05.
5. INTERPRETACIÓN: busca "Espirometría Normal" o un texto equivalente y devuélvelo tal cual.
6. Los valores de FET, PIF, EOTV y BEV pueden ser decimales pequeños o negativos; respétalos.

Responde EXCLUSIVAMENTE con este JSON, sin texto adicional ni Markdown. Cada maniobra y cada fila va identificada por nombre:
{
  "filas": {
    "fvc":       {"unidad": "L",    "pred": null, "lln": null, "mejor": null, "prueba6": null, "prueba4": null, "prueba3": null, "pct_pred": null},
    "fev1":      {"unidad": "L",    "pred": null, "lln": null, "mejor": null, "prueba6": null, "prueba4": null, "prueba3": null, "pct_pred": null},
    "fev1_fvc":  {"unidad": "",     "pred": null, "lln": null, "mejor": null, "prueba6": null, "prueba4": null, "prueba3": null, "pct_pred": null},
    "fef25_75":  {"unidad": "L/s",  "pred": null, "lln": null, "mejor": null, "prueba6": null, "prueba4": null, "prueba3": null, "pct_pred": null},
    "pef":       {"unidad": "L/s",  "pred": null, "lln": null, "mejor": null, "prueba6": null, "prueba4": null, "prueba3": null, "pct_pred": null},
    "fet":       {"unidad": "s",    "pred": null, "lln": null, "mejor": null, "prueba6": null, "prueba4": null, "prueba3": null, "pct_pred": null},
    "fivc":      {"unidad": "L",    "pred": null, "lln": null, "mejor": null, "prueba6": null, "prueba4": null, "prueba3": null, "pct_pred": null},
    "pif":       {"unidad": "L/s",  "pred": null, "lln": null, "mejor": null, "prueba6": null, "prueba4": null, "prueba3": null, "pct_pred": null},
    "eotv":      {"unidad": "L",    "pred": null, "lln": null, "mejor": null, "prueba6": null, "prueba4": null, "prueba3": null, "pct_pred": null},
    "bev":       {"unidad": "L",    "pred": null, "lln": null, "mejor": null, "prueba6": null, "prueba4": null, "prueba3": null, "pct_pred": null}
  },
  "calidad_sesion": null,
  "fev1_var": null,
  "fvc_var": null,
  "interpretacion": null
}

REGLAS FINALES:
- Reemplaza cada null con el valor REAL del informe. Si no existe, déjalo en null.
- Las claves "prueba6", "prueba4" y "prueba3" corresponden a los encabezados del informe; no las reordenes.
- NUNCA inventes datos.
PROMPT;

            try {
                if ($mime !== 'application/pdf') {
                    Log::warning('analizarEspirometria: tipo de archivo no soportado', ['mime' => $mime]);
                    return null;
                }

                $textoNativo = '';
                try {
                    $parser = new PdfParser();
                    $pdf = $parser->parseFile($rutaCompleta);
                    $textoNativo = trim($pdf->getText());
                } catch (\Throwable $e) {
                    Log::warning('No se pudo extraer texto nativo del PDF.', ['error' => $e->getMessage()]);
                }

                if (mb_strlen($textoNativo) <= 40) {
                    Log::warning('PDF sin texto nativo suficiente para espirometría.');
                    return null;
                }

                Log::info('Texto extraído del PDF (espirometría)', ['texto' => mb_substr($textoNativo, 0, 3000)]);

                $partes = [[
                    'text' => $promptEspirometria . "\n\nTEXTO COMPLETO DEL PDF:\n" . $textoNativo,
                ]];

                $paginaImagen = $this->renderizarPaginaPdfComoImagen($rutaCompleta);
                if ($paginaImagen !== null) {
                    $partes[] = [
                        'inline_data' => [
                            'mime_type' => 'image/jpeg',
                            'data' => base64_encode($paginaImagen),
                        ],
                    ];
                }

                $data = $this->consultarGeminiTrabajoConPartes($partes, 'analizarEspirometria');

                if ($data === null) {
                    return null;
                }

                $tabla = $this->extraerTablaDesdeTexto($textoNativo);
                if ($tabla === null) {
                    Log::warning('La tabla no se leyó del texto del PDF; se usan los valores de Gemini');
                }
                if ($tabla !== null) {
                    $data['filas'] = array_merge($data['filas'] ?? [], $tabla['filas']);
                }

                $resultado = $this->normalizarEspirometria($data);

                // Si el texto del PDF trae la tabla, sus maniobras reemplazan a las de Gemini
                if ($tabla !== null && !empty($tabla['maniobras'])) {
                    $resultado['maniobras'] = $tabla['maniobras'];
                }

                return $resultado;
            } catch (\Throwable $e) {
                Log::error('Error al analizar espirometría', ['error' => $e->getMessage()]);
                return null;
            }
    }

    // =========================================================
    // NÚCLEO DE LLAMADA A GEMINI
    // =========================================================

    /**
     * Envía un prompt de texto a Gemini y decodifica la respuesta JSON.
     */
    private function consultarGeminiTrabajo(string $prompt, string $origen): ?array
    {
        return $this->consultarGeminiTrabajoConPartes([['text' => $prompt]], $origen);
    }

    /**
     * Envía un arreglo de "parts" (texto y/o imágenes) a Gemini y decodifica
     * la respuesta JSON. Reintenta una vez si el JSON no se puede parsear.
     */
    private function consultarGeminiTrabajoConPartes(array $partes, string $origen): ?array
    {
        try {
            $apiKey = env('GEMINI_API_KEY');
            $modelo = 'gemini-3.5-flash-lite';
            $urlGemini = "https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key={$apiKey}";

            $inicio = microtime(true);

            $response = $this->llamarGeminiConReintentos(
                $urlGemini,
                $partes,
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
                    $partes,
                    1,
                    ['responseMimeType' => 'application/json']
                );

                if ($response->successful()) {
                    $contenido = $response->json('candidates.0.content.parts.0.text');
                    $data = $this->decodificarJsonDesdeTexto($contenido, "{$origen} (reintento)", $response->json() ?? []);
                }
            }

            return $data;

        } catch (\Throwable $e) {
            Log::error("Excepción al consultar IA de Medicina del Trabajo ({$origen}): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Llama a Gemini con reintentos automáticos (backoff exponencial) ante
     * errores transitorios (429, 500, 503).
     */
    private function llamarGeminiConReintentos(string $url, array $parts, int $maxIntentos = 3, array $generationConfigExtra = [])
    {
        $intento = 0;
        $espera = 2; // segundos, se duplica en cada reintento
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

            return $response;
        }

        return $response;
    }

    // =========================================================
    // PROCESAMIENTO DE ARCHIVOS E IMÁGENES
    // =========================================================

    /**
     * Renderiza la primera página de un PDF como JPEG usando Imagick.
     * Devuelve null si Imagick no está instalado o si falla el render;
     * en ese caso el análisis sigue funcionando solo con texto.
     */
    private function renderizarPaginaPdfComoImagen(string $ruta): ?string
    {
        if (!extension_loaded('imagick')) {
            return null;
        }

        try {
            $img = new \Imagick();
            $img->setResolution(150, 150);
            $img->readImage($ruta . '[0]');
            $img->setImageFormat('jpeg');
            $img->setImageCompressionQuality(80);
            $blob = $img->getImageBlob();
            $img->clear();

            return $blob;
        } catch (\Throwable $e) {
            Log::warning('No se pudo renderizar la página del PDF', ['error' => $e->getMessage()]);
            return null;
        }
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
                    return $textoPdf;
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
                'mime' => $mime,
            ]);
            return '';
        }
    }

    /**
     * Comprime imágenes y las arma como "parts" inline_data para Gemini Vision.
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

            $contenidoMensaje[] = [
                'inline_data' => [
                    'mime_type' => 'image/jpeg',
                    'data' => base64_encode($binarioComprimido),
                ],
            ];
        }

        return $contenidoMensaje;
    }

    /**
     * Extrae imágenes embebidas de un PDF (PDFs "escaneados" con fotos pegadas).
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

    // =========================================================
    // PARSEO DE JSON
    // =========================================================

    /**
     * Decodificador robusto: limpia bloques <think>, intenta json_decode
     * directo, luego balancea llaves y por último repara valores sin comillas.
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

    // =========================================================
    // NORMALIZACIÓN DE ESPIROMETRÍA
    // =========================================================

    /**
     * Convierte la respuesta por nombre a la estructura que espera el
     * frontend y registra en log las maniobras cuyo FEV1/FVC no cuadra con
     * FEV1 / FVC (posible desplazamiento de columna). No corrige nada.
     */
    private function normalizarEspirometria(array $data): array
    {
        $filas = $data['filas'] ?? [];

        $mapaColumnas = ['prueba3' => 3, 'prueba4' => 4, 'prueba6' => 6];
        $mapaCampos = [
            'fvc'      => 'fvc_l',
            'fev1'     => 'fev1_l',
            'fev1_fvc' => 'fev1_fvc_ratio',
            'fef25_75' => 'fef25_75',
            'pef'      => 'pef_l_s',
            'fet'      => 'fet_s',
            'fivc'     => 'fivc_l',
            'pif'      => 'pif_l_s',
            'eotv'     => 'eotv_l',
            'bev'      => 'bev_l',
        ];

        $maniobras = [3 => ['numero' => 3], 4 => ['numero' => 4], 6 => ['numero' => 6]];

        foreach ($mapaCampos as $fila => $campo) {
            $valores = $filas[$fila] ?? [];
            foreach ($mapaColumnas as $columna => $numero) {
                $valor = $valores[$columna] ?? null;
                $maniobras[$numero][$campo] = ($valor === null || $valor === '') ? null : (float) $valor;
            }
        }

        $resultado = [
            'maniobras'      => array_values($maniobras),
            'calidad_sesion' => $data['calidad_sesion'] ?? null,
            'fev1_var'       => $data['fev1_var'] ?? null,
            'fvc_var'        => $data['fvc_var'] ?? null,
            'interpretacion' => $data['interpretacion'] ?? null,
        ];

        foreach ($mapaCampos as $fila => $campo) {
            $valores = $filas[$fila] ?? [];
            $resultado[$fila . '_pred']     = $valores['pred'] ?? null;
            $resultado[$fila . '_lln']      = $valores['lln'] ?? null;
            $resultado['mejor_' . $fila]    = $valores['mejor'] ?? null;
            $resultado[$fila . '_pct']      = $valores['pct_pred'] ?? null;
        }

        $this->validarFev1Fvc($resultado['maniobras']);

        return $resultado;
    }

    /**
     * Compara FEV1/FVC extraído contra FEV1 / FVC de la misma maniobra.
     * Solo registra discrepancias; no corrige.
     */
    private function validarFev1Fvc(array $maniobras): void
    {
        foreach ($maniobras as $m) {
            if (empty($m['fev1_l']) || empty($m['fvc_l']) || empty($m['fev1_fvc_ratio'])) {
                continue;
            }

            $calculado = $m['fev1_l'] / $m['fvc_l'];

            if (abs($calculado - $m['fev1_fvc_ratio']) > 0.002) {
                Log::warning('Posible desplazamiento de columna en espirometría', [
                    'prueba' => $m['numero'],
                    'esperado' => round($calculado, 3),
                    'extraido' => $m['fev1_fvc_ratio'],
                ]);
            }
        }
    }

    /**
     * Lee la tabla de maniobras directamente del texto del PDF (formato ndd).
     * Orden de celdas en el texto: %Pred | Prueba 3 | Prueba 4 | Prueba 6 | Mejor | LLN | Pred
     */
    private function extraerTablaDesdeTexto(string $texto): ?array
    {
        $columnas = $this->extraerColumnasEncabezado($texto);
        if ($columnas === null) {
            Log::warning('No se encontró el encabezado de la tabla de espirometría en el texto del PDF');
            return null;
        }

        // etiqueta en el texto => [clave en filas, unidad, decimales, campo en la maniobra]
        $etiquetas = [
            'FVC [L]'         => ['fvc', 'L', 2, 'fvc_l'],
            'FEV1 [L]'        => ['fev1', 'L', 2, 'fev1_l'],
            'FEV1/FVC'        => ['fev1_fvc', '', 3, 'fev1_fvc_ratio'],
            'FEF25-75% [L/s]' => ['fef25_75', 'L/s', 2, 'fef25_75'],
            'PEF [L/s]'       => ['pef', 'L/s', 2, 'pef_l_s'],
            'FET [s]'         => ['fet', 's', 1, 'fet_s'],
            'FIVC [L]'        => ['fivc', 'L', 2, 'fivc_l'],
            'PIF [L/s]'       => ['pif', 'L/s', 2, 'pif_l_s'],
            'EOTV [L]'        => ['eotv', 'L', 2, 'eotv_l'],
            'BEV [L]'         => ['bev', 'L', 2, 'bev_l'],
        ];

        $alternativas = implode('|', array_map(fn ($e) => preg_quote($e, '/'), array_keys($etiquetas)));

        // Formato A: etiqueta al final y valores pegados ("4.354.00...FVC [L]")
        $patronEtiquetaAlFinal = '/^([\d\.\-\*\s—–]*?)\s*(' . $alternativas . ')$/u';
        // Formato B: etiqueta al inicio y valores separados por espacios ("FVC [L]  4.35 3.62 ...")
        $patronEtiquetaAlInicio = '/^(' . $alternativas . ')\s+(.+)$/u';

        $filas = [];
        $maniobras = []; // [numero] => ['numero' => n, 'campo' => valor, ..., 'marcados' => [...]]

        foreach (preg_split('/\R/', $texto) as $linea) {
            $linea = trim($linea);

            if (preg_match($patronEtiquetaAlInicio, $linea, $m)) {
                $etiqueta = $m[1];
                $fila = $this->tokenizarFilaEspaciada($m[2], $columnas);
            } elseif (preg_match($patronEtiquetaAlFinal, $linea, $m)) {
                $etiqueta = $m[2];
                $nums = str_replace(['—', '–'], '-', preg_replace('/\s+/u', '', $m[1]));
                $fila = $this->tokenizarFilaEspirometria($nums, $etiquetas[$etiqueta][2], $columnas);
            } else {
                continue;
            }

            [$clave, $unidad, , $campo] = $etiquetas[$etiqueta];

            if ($fila === null) {
                Log::warning('Fila de espirometría no válida al leer el texto del PDF', [
                    'fila' => $clave,
                    'linea' => $linea,
                ]);
                continue;
            }

            $v = $fila['valores'];

            $filas[$clave] = [
                'unidad'   => $unidad,
                'pred'     => $v['pred'] ?? null,
                'lln'      => $v['lln'] ?? null,
                'mejor'    => $v['mejor'] ?? null,
                'pct_pred' => $v['pct'] ?? null,
            ];

            // Cada columna "Prueba N" del encabezado se convierte en una maniobra N
            foreach ($columnas as $columna) {
                if (!str_starts_with($columna, 'run:')) {
                    continue;
                }

                $numero = (int) substr($columna, 4);
                $maniobras[$numero] ??= ['numero' => $numero, 'marcados' => []];
                $maniobras[$numero][$campo] = $v[$columna] ?? null;

                if (!empty($fila['marcadas'][$columna])) {
                    $maniobras[$numero]['marcados'][$campo] = true;
                }
            }
        }

        if (empty($filas)) {
            Log::warning('No se leyó ninguna fila de la tabla de espirometría del texto del PDF');
            return null;
        }

        ksort($maniobras);

        // Validación informativa: el Mejor de PIF y EOTV debería ser el máximo de las pruebas
        foreach (['pif' => 'pif_l_s', 'eotv' => 'eotv_l'] as $clave => $campo) {
            if (!isset($filas[$clave]) || $filas[$clave]['mejor'] === null) {
                continue;
            }

            $pruebas = array_filter(array_column($maniobras, $campo), fn ($v) => $v !== null);

            if ($pruebas && abs(max($pruebas) - $filas[$clave]['mejor']) > 0.005) {
                Log::info('Mejor distinto del máximo de las pruebas (puede ser normal según el informe)', [
                    'fila' => $clave,
                    'mejor' => $filas[$clave]['mejor'],
                    'maximo' => max($pruebas),
                ]);
            }
        }

        return ['filas' => $filas, 'maniobras' => array_values($maniobras)];
    }
    /**
     * Separa una fila de números pegados (ej. "-0.100.110.11---") en sus 7 celdas.
     * Un "-" es celda vacía. Si la fila tiene 6 tokens y el primero empieza con "-",
     * ese guion es la celda %Pred vacía pegada al número.
     */
     /**
     * Separa una fila de números pegados según las columnas del encabezado.
     * Un "-" es celda vacía. Devuelve ['valores' => [...], 'marcadas' => [...]] o null.
     */
    private function tokenizarFilaEspirometria(string $nums, int $dec, array $columnas): ?array
    {
        $pctEntero = null;

        if ($columnas[0] === 'pct' && preg_match('/^(\d{2,3})(?=-?\d+\.\d)/', $nums, $p)) {
            $pctEntero = (int) $p[1];
            $nums = substr($nums, strlen($p[1]));
        }

        preg_match_all('/-?\d+\.\d{' . $dec . '}\*?|-/', $nums, $t);
        $tokens = $t[0];

        // Guion de %Pred vacío pegado al número: "-0.10" = "-" + "0.10"
        if ($columnas[0] === 'pct' && $pctEntero === null
            && count($tokens) === count($columnas) - 1
            && strlen($tokens[0]) > 1 && str_starts_with($tokens[0], '-')) {
            $primero = array_shift($tokens);
            array_unshift($tokens, '-', substr($primero, 1));
        }

        $esperadas = count($columnas) - ($pctEntero !== null ? 1 : 0);
        if (count($tokens) !== $esperadas) {
            return null;
        }

        $celdas = $pctEntero !== null ? array_merge([(string) $pctEntero], $tokens) : $tokens;

        return $this->construirValoresFila($celdas, $columnas);
    }

    /**
     * Lee el orden de las columnas desde el encabezado del texto extraído.
     * Devuelve algo como: ['pct', 'run:3', 'run:4', 'run:6', 'mejor', 'lln', 'pred']
     */
    private function extraerColumnasEncabezado(string $texto): ?array
    {
        foreach (preg_split('/\R/', $texto) as $linea) {
            if (!str_contains($linea, 'Parámetro') || !str_contains($linea, 'Mejor')) {
                continue;
            }

            preg_match_all('/%Pred|Prueba \d+|Mejor|LLN|Pred/u', $linea, $m);

            $columnas = [];
            foreach ($m[0] as $token) {
                if ($token === '%Pred') {
                    $columnas[] = 'pct';
                } elseif ($token === 'Pred') {
                    $columnas[] = 'pred';
                } elseif ($token === 'LLN') {
                    $columnas[] = 'lln';
                } elseif ($token === 'Mejor') {
                    $columnas[] = 'mejor';
                } else {
                    $columnas[] = 'run:' . (int) substr($token, 7); // "Prueba 3" => run:3
                }
            }

            $hayPruebas = array_filter($columnas, fn ($c) => str_starts_with($c, 'run:'));
            return $hayPruebas ? $columnas : null;
        }

        return null;
    }

    /**
     * Formato con espacios: separa por espacios y convierte "—" en celda vacía.
     */
    private function tokenizarFilaEspaciada(string $resto, array $columnas): ?array
    {
        $partes = preg_split('/\s+/u', trim($resto), -1, PREG_SPLIT_NO_EMPTY);

        if (count($partes) !== count($columnas)) {
            return null;
        }

        $celdas = array_map(fn ($p) => preg_match('/^[—–\-]+$/u', $p) ? '-' : $p, $partes);

        return $this->construirValoresFila($celdas, $columnas);
    }

    /**
     * Convierte las celdas de una fila en valores numéricos y marcas de asterisco.
     */
    private function construirValoresFila(array $celdas, array $columnas): array
    {
        $valores = [];
        $marcadas = [];

        foreach ($columnas as $i => $columna) {
            $c = $celdas[$i];

            if ($columna === 'pct') {
                $valores[$columna] = $c === '-' ? null : (int) $c;
            } else {
                $valores[$columna] = $c === '-' ? null : (float) str_replace('*', '', $c);
            }

            // El asterisco solo se registra; el número no cambia
            $marcadas[$columna] = str_contains($c, '*');
        }

        return ['valores' => $valores, 'marcadas' => $marcadas];
    }
}