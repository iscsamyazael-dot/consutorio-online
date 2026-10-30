<?php

namespace App\Services\ClinicaTrabajo;

use App\Models\ClinicaTrabajo\PacientesClinicaTrabajo;
use App\Services\ClinicaTrabajo\LectorEspirometriaExcel;
use Smalot\PdfParser\Parser as PdfParser;
use App\Services\Terminologia\DiccionarioMedico;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de IA del submódulo de Espirometría.
 *
 * Sigue el mismo patrón que IAClinicaService (expediente médico) y
 * IAClinicaTrabajoService:
 *  - La IA solo PROPONE. El médico revisa y guarda.
 *  - Los datos del paciente (edad, sexo) vienen del expediente, nunca se infieren.
 *  - Cada valor propuesto debe estar anclado al texto del médico: si un número,
 *    una calidad o una aptitud no aparece en lo que se dictó, se descarta.
 *  - Una respuesta vacía nunca sobrescribe un dato que ya existe.
 *  - Los archivos de informe (PDF/Excel) se leen con los servicios existentes.
 *
 * El cliente de Gemini se duplica a propósito, igual que en los otros servicios,
 * para aislar este submódulo. Es deuda técnica aceptada.
 */
class IAServiceEspirometria
{
    private const MODELO = 'gemini-3.5-flash-lite';
    private const MAX_MENSAJES_HISTORIAL = 10;
    private const MAX_TOKENS_ENTRADA = 20000;

    /** Palabras que indican que un booleano se dictó (ej. "niega asma" -> asma false). */
    private const PALABRAS_BOOLEANOS = [
        'fumador' => ['fum', 'cigarr', 'tabaq'],
        'asma' => ['asma'],
        'epoc' => ['epoc', 'epoc'],
        'antecedente_covid' => ['covid'],
        'rinitis_alergica' => ['rinitis'],
        'tuberculosis' => ['tuberc'],
        'neumonia_previa' => ['neumon'],
        'seguimiento_requerido' => ['seguimiento', 'control', 'revis'],
    ];

    public function __construct(
        private LectorEspirometriaExcel $lectorExcel,
    ) {}

    // =========================================================
    // CHAT
    // =========================================================

    /**
     * @param string                       $mensaje      Lo que dictó o escribió el médico.
     * @param array                        $contexto     Estado actual del formulario, por sección.
     * @param array                        $historial    [['sender' => 'user'|'ia', 'text' => '...'], ...]
     * @param PacientesClinicaTrabajo|null $paciente     Paciente del expediente (edad y sexo reales).
     * @param string|null                  $rutaArchivo  Archivo adjunto, si hay.
     * @param string|null                  $tipoArchivo  'pdf' o 'excel'. Obligatorio si hay archivo.
     *
     * @return array{respuesta: string, campos_completados: array, archivo_procesado: bool, advertencias: array}
     */
    public function consultar(
        string $mensaje,
        array $contexto = [],
        array $historial = [],
        ?PacientesClinicaTrabajo $paciente = null,
        ?string $rutaArchivo = null,
        ?string $tipoArchivo = null
    ): array {
        set_time_limit(300);

        $importado = ['parametrosFVL' => [], 'interpretacion' => []];
        $advertencias = [];

        if ($rutaArchivo !== null && $tipoArchivo !== null) {
            $resultadoImport = $this->importarArchivo($rutaArchivo, $tipoArchivo);
            $importado = $resultadoImport['campos'];
            $advertencias = $resultadoImport['advertencias'];
        }

        $archivoProcesado = $importado['parametrosFVL'] !== [] || $importado['interpretacion'] !== [];

        $prompt = $this->construirPromptChat($mensaje, $contexto, $historial, $paciente, $archivoProcesado);
        $data = $this->llamarGemini($prompt, 'consultar');

        if ($data === null) {
            return [
                'respuesta' => 'No pude procesar tu mensaje en este momento. Intenta de nuevo.',
                'campos_completados' => $this->combinar($importado, []),
                'archivo_procesado' => $archivoProcesado,
                'advertencias' => $advertencias,
            ];
        }

        $respuesta = is_string($data['respuesta'] ?? null) && trim($data['respuesta']) !== ''
            ? trim($data['respuesta'])
            : 'Listo.';

        $propuesto = $this->sanitizarCampos($data['campos_completados'] ?? [], $mensaje);

        return [
            'respuesta' => $respuesta,
            'campos_completados' => $this->combinar($importado, $propuesto),
            'archivo_procesado' => $archivoProcesado,
            'advertencias' => $advertencias,
        ];
    }

    // =========================================================
    // IMPORTACIÓN DE INFORMES
    // =========================================================

    /**
     * Lee un informe ndd (PDF o Excel) con los servicios existentes y lo convierte
     * al formato del formulario. Los valores de un informe no se anclan al texto,
     * porque vienen de una tabla estructurada, no de un dictado.
     *
     * @return array{campos: array, advertencias: array}
     */
    public function importarArchivo(string $ruta, string $tipo): array
    {
        try {
            $datos = match ($tipo) {
                'pdf' => $this->analizarEspirometria($ruta, 'application/pdf'),
                'excel' => $this->lectorExcel->leer($ruta),
                default => null,
            };
        } catch (\Throwable $e) {
            Log::error('IAServiceEspirometria: error al leer el informe', [
                'tipo' => $tipo,
                'error' => $e->getMessage(),
            ]);
            $datos = null;
        }

        if (!is_array($datos)) {
            return [
                'campos' => ['parametrosFVL' => [], 'interpretacion' => []],
                'advertencias' => ['No se pudieron extraer datos del informe.'],
            ];
        }

        $campos = $this->mapearImportacion($datos);

        Log::info('Importación: claves de interpretación', [
            'claves' => array_keys($campos['interpretacion']),
        ]);

        return [
            'campos' => $campos,
            'advertencias' => $datos['advertencias'] ?? [],
        ];
    }

    /**
     * Convierte la salida de analizarEspirometria o LectorEspirometriaExcel
     * (mismas claves en ambos) a las secciones del formulario. Quita las claves
     * nulas para que nunca borren lo que ya existe.
     */
    private function mapearImportacion(array $d): array
    {
        $parametros = [];

        $valores = [
            'fvc_pred', 'fvc_lln', 'fev1_pred', 'fev1_lln',
            'fef25_75_pred', 'fef25_75_lln', 'pef_pred', 'pef_lln',
            'fivc_pred', 'fivc_lln', 'pif_pred', 'pif_lln',
            'eotv_pred', 'eotv_lln', 'bev_pred', 'bev_lln',
            'mejor_fvc', 'mejor_fev1', 'mejor_fef25_75', 'mejor_pef',
            'mejor_fivc', 'mejor_pif', 'mejor_eotv', 'mejor_bev', 'mejor_fet',
        ];
        foreach ($valores as $campo) {
            $parametros[$campo] = $this->aNumero($d[$campo] ?? null);
        }

        // FEV1/FVC siempre en porcentaje (el PDF lo trae como fracción)
        $parametros['fev1_fvc_pred'] = $this->aPorcentaje($d['fev1_fvc_pred'] ?? null);
        $parametros['fev1_fvc_lln'] = $this->aPorcentaje($d['fev1_fvc_lln'] ?? null);
        $parametros['mejor_fev1_fvc'] = $this->aPorcentaje($d['mejor_fev1_fvc'] ?? null);

        $porcentajes = [
            'fvc_porcentaje_predicho' => 'fvc_pct',
            'fev1_porcentaje_predicho' => 'fev1_pct',
            'fev1_fvc_porcentaje_predicho' => 'fev1_fvc_pct',
            'fef25_75_porcentaje_predicho' => 'fef25_75_pct',
            'pef_porcentaje_predicho' => 'pef_pct',
            'fivc_porcentaje_predicho' => 'fivc_pct',
            'pif_porcentaje_predicho' => 'pif_pct',
        ];
        foreach ($porcentajes as $destino => $origen) {
            $parametros[$destino] = isset($d[$origen]) ? (int) round((float) $d[$origen]) : null;
        }

        foreach (['fvc_z', 'fev1_z', 'fev1_fvc_z', 'fef25_75_z', 'pef_z', 'fivc_z'] as $campo) {
            $parametros[$campo] = $this->aNumero($d[$campo] ?? null);
        }

        $parametros['fev1_var_l'] = $this->aNumero($d['fev1_var'] ?? $d['fev1_var_l'] ?? null);
        $parametros['fvc_var_l'] = $this->aNumero($d['fvc_var'] ?? $d['fvc_var_l'] ?? null);
        $parametros['calidad_fev1'] = $this->enumOrNull($d['calidad_fev1'] ?? null, $this->calidades());
        $parametros['calidad_fvc'] = $this->enumOrNull($d['calidad_fvc'] ?? null, $this->calidades());

        if (!empty($d['maniobras']) && is_array($d['maniobras'])) {
            $parametros['maniobras'] = array_values(array_map(function (array $m) {
                if (isset($m['fev1_fvc_ratio'])) {
                    $m['fev1_fvc_ratio'] = $this->aPorcentaje($m['fev1_fvc_ratio']);
                }
                return $m;
            }, $d['maniobras']));
        }

        // Calidad y texto del equipo viven en la sección Interpretación
        $interpretacion = [
            'calidad_sesion' => $this->enumOrNull($d['calidad_sesion'] ?? null, $this->calidades()),
            'interpretacion_equipo' => $d['interpretacion'] ?? null,
            'interpretacion_sistema' => $this->patronDesdeTexto($d['interpretacion'] ?? null),
        ];

        $datosGenerales = array_filter(array_merge(
            ['fecha_prueba' => $this->fechaPrueba($d['fecha_prueba'] ?? null)],
            $this->tabaquismoDesdeTexto($d['fumador_texto'] ?? null)
        ), fn ($v) => $v !== null);

        return [
            'datosGenerales' => $datosGenerales,
            'parametrosFVL' => array_filter($parametros, fn ($v) => $v !== null),
            'interpretacion' => array_filter($interpretacion, fn ($v) => $v !== null),
        ];
    }

    // =========================================================
    // PROMPT DEL CHAT
    // =========================================================

    private function construirPromptChat(
        string $mensaje,
        array $contexto,
        array $historial,
        ?PacientesClinicaTrabajo $paciente,
        bool $archivoProcesado
    ): string {
        $vocabulario = DiccionarioMedico::textoReferencia();

        $contextoTexto = $contexto !== []
            ? json_encode($contexto, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            : '{}';

        $bloqueHistorial = $this->bloqueContextoPrevio($historial);
        $bloquePaciente = $this->bloqueDatosPaciente($paciente);

        $avisoArchivo = $archivoProcesado
            ? "El informe adjunto YA fue extraído por el sistema. No repitas esos valores en campos_completados."
            : "No hay informe adjunto en este turno.";

        return <<<PROMPT
Eres un asistente de Inteligencia Artificial de apoyo al médico neumólogo de Medicina del Trabajo durante la captura de una espirometría. Tu función es responder al médico y PROPONER valores para el formulario. El médico revisa y guarda; tú no emites el dictamen final.

IMPORTANTE:
- No reemplazas al médico ni sustituyes su criterio clínico.
- No emites diagnósticos definitivos ni dictámenes por tu cuenta.
- No inventes datos. Si un dato no fue dictado, no lo propongas.
- Tampoco infieras valores por asociación clínica: si el médico no dictó un valor, no lo agregues aunque sea típico del cuadro.
- No escribas "No disponible" en los campos; si no hay dato, omítelo.

Vocabulario de referencia (coloquial -> término médico):
{$vocabulario}
{$bloquePaciente}
{$bloqueHistorial}

ESTADO ACTUAL DEL FORMULARIO (no lo repitas en campos_completados si no cambia):
{$contextoTexto}

{$avisoArchivo}

MENSAJE DEL MÉDICO:
"{$mensaje}"

REGLAS
- "respuesta": breve y profesional, dirigida al médico.
- Propón un valor SOLO si el médico lo dictó o lo menciona en su mensaje de hoy.
- Valores de la prueba dictados (FVC, FEV1, etc.) van en parametrosFVL con el nombre "mejor_..." si son el Mejor, o "..._pred" / "..._lln" si son predicho o límite inferior.
- FEV1/FVC siempre en porcentaje (ej. 73.8), nunca en fracción (0.738).
- Edad y sexo del paciente vienen del expediente; no los cambies salvo que el médico lo corrija explícitamente.
- "aptitud" y "diagnostico_medico" solo si el médico los dicta de forma explícita.
- Si el mensaje es una pregunta, responde sin llenar campos.

NOMBRES DE CAMPOS PERMITIDOS
- datosGenerales: fecha_nacimiento (AAAA-MM-DD), altura_cm, peso_kg, peso_ideal_kg, sexo (Masculino|Femenino), origen_etnico (Hispano|Caucásico|Afrodescendiente|Otro), id_empleado, empresa, remitido_por, prescrito_por, fumador, cigarrillos_dia, anos_fumador, asma, epoc, antecedente_covid, rinitis_alergica, tuberculosis, neumonia_previa, comentarios.
- parametrosFVL: referencia (NHANES III|GLI-2012|Hankinson|Lloveras|Pellegrino), interpretacion_predicha (GOLD(2008)/Hardie|ATS/ERS 2021|GOLD-2023), fvc_pred, fvc_lln, fev1_pred, fev1_lln, fev1_fvc_pred, fev1_fvc_lln, fef25_75_pred, fef25_75_lln, pef_pred, pef_lln, fivc_pred, fivc_lln, pif_pred, pif_lln, eotv_pred, eotv_lln, bev_pred, bev_lln, mejor_fvc, mejor_fev1, mejor_fev1_fvc, mejor_fef25_75, mejor_pef, mejor_fivc, mejor_pif, mejor_eotv, mejor_bev, mejor_fet, fev1_var_l, fvc_var_l, calidad_fev1, calidad_fvc (A|B|C|D|E|F|U).
- interpretacion: calidad_sesion (A|B|C|D|E|F|U), interpretacion_sistema (Espirometría Normal|Patrón Obstructivo|Patrón Restrictivo|Patrón Mixto|Prueba no válida), diagnostico_medico, aptitud (Apto|Apto con restricciones|No apto), recomendaciones, edad_pulmonar, seguimiento_requerido, plazo_proximo_seguimiento (AAAA-MM-DD).

Devuelve EXCLUSIVAMENTE este JSON, sin Markdown ni texto adicional:
{"respuesta": "texto para el médico", "campos_completados": {"datosGenerales": {}, "parametrosFVL": {}, "interpretacion": {}}}
PROMPT;
    }

    /**
     * Datos demográficos reales del expediente (edad y sexo). Nunca se inventan.
     * Mismo criterio que bloqueDatosPaciente de IAClinicaService.
     */
    private function bloqueDatosPaciente(?PacientesClinicaTrabajo $paciente): string
    {
        if (!$paciente) {
            return '';
        }

        $lineas = [];

        if ($paciente->fecha_nacimiento) {
            $lineas[] = 'Fecha de nacimiento: ' . $paciente->fecha_nacimiento->format('Y-m-d');
        }

        $edad = $paciente->edad_formateada ?? null;
        if ($edad !== null && $edad !== 'N/A') {
            $lineas[] = "Edad: {$edad}";
        }

        $sexo = $paciente->sexoReferencia();
        if ($sexo !== null) {
            $lineas[] = "Sexo: {$sexo}";
        }

        if ($lineas === []) {
            return '';
        }

        $textoLineas = implode("\n", $lineas);

        return "
DATOS DEMOGRÁFICOS REALES DEL PACIENTE (del expediente; NUNCA los inventes ni los tomes de otra fuente):
{$textoLineas}
";
    }

    /**
     * Mensajes previos de esta misma valoración, para que la respuesta sea
     * acumulativa. Mismo criterio que bloqueContextoPrevio de IAClinicaService.
     */
    private function bloqueContextoPrevio(array $historial): string
    {
        $lineas = '';
        foreach (array_slice($historial, -self::MAX_MENSAJES_HISTORIAL) as $h) {
            $quien = ($h['sender'] ?? '') === 'user' ? 'Médico' : 'IA';
            $lineas .= "- {$quien}: " . ($h['text'] ?? '') . "\n";
        }

        if (trim($lineas) === '') {
            return '';
        }

        if (mb_strlen($lineas) > self::MAX_TOKENS_ENTRADA * 4) {
            $lineas = mb_substr($lineas, -(self::MAX_TOKENS_ENTRADA * 4));
        }

        return "
MENSAJES ANTERIORES DE ESTA MISMA VALORACIÓN (acumula lo dicho; no inventes que algo mencionado antes sigue vigente si el mensaje de hoy lo contradice):
{$lineas}";
    }

    // =========================================================
    // VALIDACIÓN Y ANCLAJE DE LO QUE PROPONE LA IA
    // =========================================================

    /**
     * Reglas por campo: tipo y rango o valores permitidos, espejo de las columnas
     * de valoraciones_espirometria.
     */
    private function reglas(): array
    {
        $calidades = $this->calidades();
        $numParametro = ['num', 0, 20];
        $porcentaje = ['num', 0, 100];
        $zScore = ['num', -10, 10];

        return [
            'datosGenerales' => [
                'fecha_nacimiento' => ['date'],
                'altura_cm' => ['num', 50, 250],
                'peso_kg' => ['num', 20, 300],
                'peso_ideal_kg' => ['num', 20, 200],
                'sexo' => ['enum', ['Masculino', 'Femenino']],
                'origen_etnico' => ['enum', ['Hispano', 'Caucásico', 'Afrodescendiente', 'Otro']],
                'id_empleado' => ['text', 100],
                'empresa' => ['text', 255],
                'remitido_por' => ['text', 255],
                'prescrito_por' => ['text', 255],
                'fumador' => ['bool'],
                'cigarrillos_dia' => ['int', 0, 100],
                'anos_fumador' => ['int', 0, 80],
                'asma' => ['bool'],
                'epoc' => ['bool'],
                'antecedente_covid' => ['bool'],
                'rinitis_alergica' => ['bool'],
                'tuberculosis' => ['bool'],
                'neumonia_previa' => ['bool'],
                'comentarios' => ['text', 2000],
            ],
            'parametrosFVL' => [
                'referencia' => ['enum', ['NHANES III', 'GLI-2012', 'Hankinson', 'Lloveras', 'Pellegrino']],
                'interpretacion_predicha' => ['enum', ['GOLD(2008)/Hardie', 'ATS/ERS 2021', 'GOLD-2023']],
                'fvc_pred' => $numParametro, 'fvc_lln' => $numParametro,
                'fev1_pred' => $numParametro, 'fev1_lln' => $numParametro,
                'fev1_fvc_pred' => $porcentaje, 'fev1_fvc_lln' => $porcentaje,
                'fef25_75_pred' => $numParametro, 'fef25_75_lln' => $numParametro,
                'pef_pred' => $numParametro, 'pef_lln' => $numParametro,
                'fivc_pred' => $numParametro, 'fivc_lln' => $numParametro,
                'pif_pred' => $numParametro, 'pif_lln' => $numParametro,
                'eotv_pred' => $numParametro, 'eotv_lln' => $numParametro,
                'bev_pred' => $numParametro, 'bev_lln' => $numParametro,
                'mejor_fvc' => $numParametro, 'mejor_fev1' => $numParametro,
                'mejor_fev1_fvc' => $porcentaje,
                'mejor_fef25_75' => $numParametro, 'mejor_pef' => $numParametro,
                'mejor_fivc' => $numParametro, 'mejor_pif' => $numParametro,
                'mejor_eotv' => $numParametro, 'mejor_bev' => $numParametro,
                'mejor_fet' => ['num', 0, 30],
                'fev1_var_l' => ['num', 0, 5],
                'fvc_var_l' => ['num', 0, 5],
                'calidad_fev1' => ['enum', $calidades],
                'calidad_fvc' => ['enum', $calidades],
                'fvc_z' => $zScore, 'fev1_z' => $zScore, 'fev1_fvc_z' => $zScore,
                'fef25_75_z' => $zScore, 'pef_z' => $zScore, 'fivc_z' => $zScore,
            ],
            'interpretacion' => [
                'calidad_sesion' => ['enum', $calidades],
                'interpretacion_sistema' => ['enum', ['Espirometría Normal', 'Patrón Obstructivo', 'Patrón Restrictivo', 'Patrón Mixto', 'Prueba no válida']],
                'diagnostico_medico' => ['text', 4000],
                'aptitud' => ['enum', ['Apto', 'Apto con restricciones', 'No apto']],
                'recomendaciones' => ['text', 4000],
                'edad_pulmonar' => ['int', 0, 120],
                'seguimiento_requerido' => ['bool'],
                'plazo_proximo_seguimiento' => ['date'],
            ],
        ];
    }

    /**
     * Deja solo campos permitidos, con tipo y rango válidos, y anclados al
     * mensaje del médico. Lo demás se descarta y se registra solo el nombre
     * del campo (sin valores clínicos) para auditoría.
     */
    private function sanitizarCampos(mixed $campos, string $mensaje): array
    {
        $salida = ['datosGenerales' => [], 'parametrosFVL' => [], 'interpretacion' => []];

        if (!is_array($campos)) {
            return $salida;
        }

        $mensajeNormalizado = $this->normalizarMensaje($mensaje);

        foreach ($this->reglas() as $seccion => $reglasSeccion) {
            $valoresSeccion = is_array($campos[$seccion] ?? null) ? $campos[$seccion] : [];

            foreach ($reglasSeccion as $campo => $regla) {
                if (!array_key_exists($campo, $valoresSeccion)) {
                    continue;
                }

                $valor = $this->validarValor($valoresSeccion[$campo], $regla);

                if ($valor === null) {
                    continue;
                }

                if (!$this->estaAnclado($campo, $valor, $regla, $mensaje, $mensajeNormalizado)) {
                    Log::info('IAServiceEspirometria: campo descartado por falta de anclaje', [
                        'seccion' => $seccion,
                        'campo' => $campo,
                    ]);
                    continue;
                }

                $salida[$seccion][$campo] = $valor;
            }
        }

        return $salida;
    }

    private function validarValor(mixed $valor, array $regla): mixed
    {
        switch ($regla[0]) {
            case 'num':
                $n = $this->aNumero($valor);
                return ($n !== null && $n >= $regla[1] && $n <= $regla[2]) ? $n : null;

            case 'int':
                $n = $this->aNumero($valor);
                return ($n !== null && floor($n) == $n && $n >= $regla[1] && $n <= $regla[2])
                    ? (int) $n
                    : null;

            case 'bool':
                if (is_bool($valor)) {
                    return $valor;
                }
                return in_array($valor, [1, 0, '1', '0', 'true', 'false'], true)
                    ? filter_var($valor, FILTER_VALIDATE_BOOLEAN)
                    : null;

            case 'enum':
                return in_array($valor, $regla[1], true) ? $valor : null;

            case 'text':
                if (!is_string($valor)) {
                    return null;
                }
                $limpio = mb_substr(trim(strip_tags($valor)), 0, $regla[1]);
                return $limpio !== '' ? $limpio : null;

            case 'date':
                if (!is_string($valor)) {
                    return null;
                }
                $fecha = \DateTime::createFromFormat('Y-m-d', $valor);
                return ($fecha && $fecha->format('Y-m-d') === $valor) ? $valor : null;
        }

        return null;
    }

    /**
     * Anclaje: un valor propuesto solo se acepta si su evidencia aparece en lo
     * que dictó el médico. Equivale a validarAnclajeSintomas del expediente, pero
     * aplicado a cifras, booleanos y enums.
     *
     * Los campos de texto libre no se anclan por palabra; su control es la regla
     * de no inventar del prompt y la revisión del médico antes de guardar.
     */
    private function estaAnclado(string $campo, mixed $valor, array $regla, string $mensaje, string $mensajeNormalizado): bool
    {
        if ($regla[0] === 'text') {
            return true;
        }

        if (in_array($regla[0], ['num', 'int'], true)) {
            return $this->numeroAnclado((float) $valor, $mensajeNormalizado);
        }

        if ($regla[0] === 'bool') {
            $palabras = self::PALABRAS_BOOLEANOS[$campo] ?? null;
            if ($palabras === null) {
                return false;
            }
            $textoBajo = mb_strtolower($mensaje);
            foreach ($palabras as $palabra) {
                if (str_contains($textoBajo, $palabra)) {
                    return true;
                }
            }
            return false;
        }

        if ($regla[0] === 'enum') {
            return $this->enumAnclado($campo, $valor, $mensaje, $mensajeNormalizado);
        }

        if ($regla[0] === 'date') {
            $anio = substr((string) $valor, 0, 4);
            return str_contains($mensajeNormalizado, $anio);
        }

        return false;
    }

    /** Busca el número en el texto, en formato con punto o con coma, sin pegarse a otros dígitos. */
    private function numeroAnclado(float $valor, string $mensajeNormalizado): bool
    {
        $formas = [];
        $formas[] = rtrim(rtrim(sprintf('%.2f', $valor), '0'), '.');
        $formas[] = sprintf('%.1f', $valor);
        $formas[] = (string) $valor;

        foreach (array_unique($formas) as $forma) {
            $patron = '/(?<![\d.])' . preg_quote($forma, '/') . '(?![\d])/';
            if (preg_match($patron, $mensajeNormalizado)) {
                return true;
            }
        }

        return false;
    }

    private function enumAnclado(string $campo, mixed $valor, string $mensaje, string $mensajeNormalizado): bool
    {
        $textoBajo = mb_strtolower($mensaje);

        // Calidad: letra sola, solo cuenta si viene como "grado X" o "calidad X"
        if (in_array($campo, ['calidad_fev1', 'calidad_fvc', 'calidad_sesion'], true)) {
            $patron = '/\b(grado|calidad)\s+' . preg_quote((string) $valor, '/') . '\b/iu';
            return (bool) preg_match($patron, $mensaje);
        }

        // Patrón: se compara con la misma lectura que usa el sistema para el texto del equipo
        if ($campo === 'interpretacion_sistema') {
            return $this->patronDesdeTexto($mensaje) === $valor;
        }

        // Sexo y origen: palabras clave
        if ($campo === 'sexo') {
            $palabras = $valor === 'Masculino' ? ['masculin', 'hombre', 'varón', 'varon'] : ['femen', 'mujer'];
            foreach ($palabras as $p) {
                if (str_contains($textoBajo, $p)) {
                    return true;
                }
            }
            return false;
        }

        // Resto de enums: la palabra clave principal del valor (ej. "NHANES", "GOLD", "Apto")
        $clave = mb_strtolower(preg_split('/[\s\/()\-]+/', (string) $valor)[0] ?? '');
        return $clave !== '' && str_contains($textoBajo, $clave);
    }

    /** Normaliza el mensaje para comparar cifras: coma decimal convertida en punto. */
    private function normalizarMensaje(string $mensaje): string
    {
        return str_replace(',', '.', mb_strtolower($mensaje));
    }

    // =========================================================
    // DICTAMEN (borrador a partir de los criterios calculados)
    // =========================================================

    /**
     * Propone diagnóstico, recomendaciones y aptitud sugerida a partir de los
     * parámetros y maniobras ya capturados. No requiere dictado del médico.
     * El patrón y la calidad salen de criteriosObjetivos(), nunca del modelo.
     *
     * @return array{respuesta: string, campos_completados: array, advertencias: array}
     */
    public function proponerDictamen(array $contexto, ?PacientesClinicaTrabajo $paciente = null, array $historial = []): array
    {
        set_time_limit(300);

        $criterios = $this->criteriosObjetivos($contexto['parametrosFVL'] ?? []);

        if ($criterios['patron'] === null) {
            return [
                'respuesta' => 'Faltan datos para proponer el dictamen: FEV1/FVC o su límite inferior (LLN).',
                'campos_completados' => ['datosGenerales' => [], 'parametrosFVL' => [], 'interpretacion' => $this->camposDesdeCriterios($criterios)],
                'advertencias' => $criterios['advertencias'],
            ];
        }

        $data = $this->llamarGemini($this->construirPromptDictamen($criterios, $paciente, $historial), 'dictamen');

        if ($data === null) {
            return [
                'respuesta' => 'No pude generar la propuesta de dictamen. Intenta de nuevo.',
                'campos_completados' => ['datosGenerales' => [], 'parametrosFVL' => [], 'interpretacion' => $this->camposDesdeCriterios($criterios)],
                'advertencias' => $criterios['advertencias'],
            ];
        }

        $propuesto = $this->sanitizarDictamen(is_array($data['interpretacion'] ?? null) ? $data['interpretacion'] : []);

        return [
            'respuesta' => is_string($data['respuesta'] ?? null) && trim($data['respuesta']) !== ''
                ? trim($data['respuesta'])
                : 'Propuesta de dictamen lista para tu revisión.',
            'campos_completados' => [
                'datosGenerales' => [],
                'parametrosFVL' => [],
                'interpretacion' => array_merge($this->camposDesdeCriterios($criterios), $propuesto),
            ],
            'advertencias' => $criterios['advertencias'],
        ];
    }

    /**
     * Hechos objetivos a partir de la tabla: obstrucción y restricción contra el LLN,
     * calidad de sesión, maniobras marcadas por el equipo y valores limítrofes.
     */
    private function criteriosObjetivos(array $p): array
    {
        $advertencias = [];

        $ratio = $this->aNumero($p['mejor_fev1_fvc'] ?? null);
        $ratioLln = $this->aNumero($p['fev1_fvc_lln'] ?? null);
        $fvc = $this->aNumero($p['mejor_fvc'] ?? null);
        $fvcLln = $this->aNumero($p['fvc_lln'] ?? null);
        $fev1Pct = $this->aNumero($p['fev1_porcentaje_predicho'] ?? null);
        $fvcPct = $this->aNumero($p['fvc_porcentaje_predicho'] ?? null);
        $fvcZ = $this->aNumero($p['fvc_z'] ?? null);

        $obstruccion = ($ratio !== null && $ratioLln !== null) ? $ratio < $ratioLln : null;
        if ($obstruccion === null) {
            $advertencias[] = 'Falta FEV1/FVC o su LLN: no se puede evaluar la obstrucción.';
        }

        $restriccion = ($fvc !== null && $fvcLln !== null) ? $fvc < $fvcLln : null;
        if ($restriccion === null && $fvcZ !== null) {
            $restriccion = $fvcZ < -1.645;
        }
        if ($restriccion === null) {
            $advertencias[] = 'Falta FVC o su LLN: no se puede evaluar la restricción.';
        }

        $patron = null;
        if ($obstruccion !== null && $restriccion !== null) {
            $patron = match (true) {
                $obstruccion && $restriccion => 'Patrón Mixto',
                $obstruccion => 'Patrón Obstructivo',
                $restriccion => 'Patrón Restrictivo',
                default => 'Espirometría Normal',
            };
        }

        $calidades = array_values(array_filter([$p['calidad_fev1'] ?? null, $p['calidad_fvc'] ?? null]));
        $calidad = $calidades !== [] ? $this->peorCalidadIA($calidades) : null;

        $marcadas = [];
        foreach ($p['maniobras'] ?? [] as $m) {
            $campos = array_keys($m['marcados'] ?? []);
            if ($campos !== []) {
                $marcadas[] = ['prueba' => $m['numero'] ?? null, 'parametros' => $campos];
            }
        }

        $limitrofes = [];
        if ($ratio !== null && $ratioLln !== null && $ratio >= $ratioLln && $ratio - $ratioLln < 1.0) {
            $limitrofes[] = 'FEV1/FVC a menos de 1 punto del LLN';
        }
        $fef = $this->aNumero($p['mejor_fef25_75'] ?? null);
        $fefLln = $this->aNumero($p['fef25_75_lln'] ?? null);
        if ($fef !== null && $fefLln !== null && $fef >= $fefLln && $fef - $fefLln < 0.1) {
            $limitrofes[] = 'FEF25-75 a menos de 0.1 L/s del LLN';
        }

        return [
            'patron' => $patron,
            'calidad' => $calidad,
            'obstruccion' => $obstruccion,
            'restriccion' => $restriccion,
            'fev1_fvc' => ['mejor_pct' => $ratio, 'lln_pct' => $ratioLln],
            'fvc' => ['mejor_l' => $fvc, 'lln_l' => $fvcLln, 'pct_predicho' => $fvcPct],
            'fev1_pct_predicho' => $fev1Pct,
            'gravedad_obstructiva' => match (true) {
                $fev1Pct === null => null,
                $fev1Pct >= 80 => 'normal o leve (FEV1 >= 80%)',
                $fev1Pct >= 50 => 'moderada (FEV1 50-79%)',
                $fev1Pct >= 30 => 'grave (FEV1 30-49%)',
                default => 'muy grave (FEV1 < 30%)',
            },
            'z' => [
                'fvc' => $p['fvc_z'] ?? null,
                'fev1' => $p['fev1_z'] ?? null,
                'fev1_fvc' => $p['fev1_fvc_z'] ?? null,
            ],
            'maniobras_marcadas' => $marcadas,
            'valores_limitrofes' => $limitrofes,
            'advertencias' => $advertencias,
        ];
    }

    private function construirPromptDictamen(array $criterios, ?PacientesClinicaTrabajo $paciente, array $historial): string
    {
        $hechos = json_encode(
            array_diff_key($criterios, ['advertencias' => true]),
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
        $bloquePaciente = $this->bloqueDatosPaciente($paciente);
        $bloqueHistorial = $this->bloqueContextoPrevio($historial);

        return <<<PROMPT
Eres un asistente de IA de apoyo al médico neumólogo de Medicina del Trabajo. Redactas una PROPUESTA de dictamen de espirometría. El médico la revisa, la corrige y decide la aptitud.

REGLAS
- Usa EXCLUSIVAMENTE los hechos calculados por el sistema que aparecen abajo. No inventes valores ni agregues datos que no estén aquí.
- El patrón y la calidad ya fueron determinados por el sistema; no los cambies ni los repitas como texto dictaminado.
- Cita los valores que sostienen cada afirmación (por ejemplo, "FEV1/FVC 77.9% contra LLN 75%").
- Si hay maniobras marcadas o valores limítrofes, menciónalos.
- Recomendaciones: medidas generales según los hallazgos (seguimiento, cesación tabáquica si aplica, protección respiratoria si hay exposición). No prescribas medicamentos.
- Aptitud: solo una de estas tres, textual: "Apto", "Apto con restricciones", "No apto". Es una sugerencia; el médico decide.
{$bloquePaciente}
{$bloqueHistorial}

HECHOS CALCULADOS POR EL SISTEMA
{$hechos}

Devuelve EXCLUSIVAMENTE este JSON, sin Markdown:
{"respuesta": "resumen breve para el médico", "interpretacion": {"diagnostico_medico": "...", "recomendaciones": "...", "aptitud": "..."}}
PROMPT;
    }

    /** Patrón y calidad que se proponen directamente desde los criterios calculados. */
    private function camposDesdeCriterios(array $criterios): array
    {
        return array_filter([
            'calidad_sesion' => $criterios['calidad'],
            'interpretacion_sistema' => $criterios['patron'],
        ], fn ($v) => $v !== null);
    }

    /** Solo acepta diagnóstico, recomendaciones y aptitud. Lo demás se descarta. */
    private function sanitizarDictamen(array $campos): array
    {
        $salida = [];

        foreach (['diagnostico_medico' => ['text', 4000], 'recomendaciones' => ['text', 4000]] as $campo => $regla) {
            $valor = $this->validarValor($campos[$campo] ?? null, $regla);
            if ($valor !== null) {
                $salida[$campo] = $valor;
            }
        }

        $aptitud = $this->validarValor($campos['aptitud'] ?? null, ['enum', ['Apto', 'Apto con restricciones', 'No apto']]);
        if ($aptitud !== null) {
            $salida['aptitud'] = $aptitud;
        }

        return $salida;
    }

    private function peorCalidadIA(array $grados): string
    {
        $orden = ['A', 'B', 'C', 'D', 'E', 'U', 'F'];
        usort($grados, fn ($a, $b) => array_search($b, $orden, true) <=> array_search($a, $orden, true));

        return $grados[0];
    }

    // =========================================================
    // COMBINACIÓN
    // =========================================================

    /**
     * El archivo tiene prioridad sobre lo que propuso la IA. Ninguna sección
     * pierde claves que ya existían por una respuesta vacía.
     */
    private function combinar(array $importado, array $propuesto): array
    {
        $salida = [];
        foreach (['datosGenerales', 'parametrosFVL', 'interpretacion'] as $seccion) {
            $salida[$seccion] = array_merge(
                $propuesto[$seccion] ?? [],
                $importado[$seccion] ?? []
            );
        }
        return $salida;
    }

    // =========================================================
    // CLIENTE DE GEMINI (mismo patrón que IAClinicaService)
    // =========================================================

    private function llamarGemini(string $prompt, string $origen): ?array
    {
        return $this->consultarGeminiConPartes([['text' => $prompt]], $origen);
    }

    /**
     * Reintenta ante errores transitorios (429, 500, 503) con backoff exponencial
     * (2s, 4s, 8s). Un error que no cambia con el reintento se devuelve de inmediato.
     */
    private function llamarGeminiConReintentos(string $url, array $parts, int $maxIntentos = 3, array $generationConfigExtra = [])
    {
        $intento = 0;
        $espera = 2;
        $response = null;

        $generationConfig = array_merge(['temperature' => 0.1], $generationConfigExtra);

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

    /**
     * Decodificador robusto: quita bloques <think>, intenta json_decode directo,
     * luego balancea llaves y por último repara valores sin comillas.
     * Agrega el uso de tokens en debug_usage, como en IAClinicaService.
     */
    private function decodificarJsonDesdeTexto(?string $contenido, string $origen, array $respuestaCompletaParaLog = []): ?array
    {
        if ($contenido === null || trim($contenido) === '') {
            Log::error("IAServiceEspirometria: respuesta vacía ({$origen})");
            return null;
        }

        $contenido = trim(preg_replace('/<think>.*?<\/think>/s', '', $contenido));

        if ($contenido === '') {
            Log::error("IAServiceEspirometria: solo razonamiento sin contenido final ({$origen})");
            return null;
        }

        $data = json_decode($contenido, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $balanceado = $this->extraerPrimerJsonBalanceado($contenido);
            if ($balanceado !== null) {
                $data = json_decode($balanceado, true);
            }
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            $reparado = $this->repararValoresSinComillas($contenido);
            $data = json_decode($reparado, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $balanceado = $this->extraerPrimerJsonBalanceado($reparado);
                if ($balanceado !== null) {
                    $data = json_decode($balanceado, true);
                }
            }

            if (json_last_error() === JSON_ERROR_NONE) {
                Log::warning("JSON reparado automáticamente ({$origen})");
            }
        }

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            // Sin el contenido en el log: puede traer datos del paciente
            Log::error("IAServiceEspirometria: JSON inválido o incompleto ({$origen})", [
                'json_error' => json_last_error_msg(),
                'longitud' => strlen($contenido),
            ]);
            return null;
        }

        $data['debug_usage'] = $respuestaCompletaParaLog['usageMetadata'] ?? null;

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

    /** Repara el error más común: elementos de un arreglo que vienen sin comillas. */
    private function repararValoresSinComillas(string $contenido): string
    {
        $reparado = [];

        foreach (explode("\n", $contenido) as $linea) {
            $sinEspacios = trim($linea);

            if (preg_match('/^([A-Za-zÁÉÍÓÚÑáéíóúñ0-9][^":{}\[\]]*?)(,?)\s*$/u', $sinEspacios, $m)) {
                $indentacion = substr($linea, 0, strlen($linea) - strlen(ltrim($linea)));
                $texto = trim($m[1]);

                if ($texto !== '' && !str_contains($texto, ':')) {
                    $reparado[] = $indentacion . '"' . addslashes($texto) . '"' . $m[2];
                    continue;
                }
            }

            $reparado[] = $linea;
        }

        return implode("\n", $reparado);
    }

    // =========================================================
    // IMPORTACIÓN: AUXILIARES
    // =========================================================

    private function calidades(): array
    {
        return ['A', 'B', 'C', 'D', 'E', 'F', 'U'];
    }

    private function aNumero(mixed $valor): ?float
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if (is_string($valor)) {
            $valor = str_replace(',', '.', trim($valor));
        }

        return is_numeric($valor) ? (float) $valor : null;
    }

    /** FEV1/FVC: si viene como fracción (<= 1) la pasa a porcentaje. */
    private function aPorcentaje(mixed $valor): ?float
    {
        $n = $this->aNumero($valor);
        if ($n === null) {
            return null;
        }
        return $n <= 1 ? round($n * 100, 1) : round($n, 1);
    }

    private function enumOrNull(mixed $valor, array $permitidos): ?string
    {
        return in_array($valor, $permitidos, true) ? $valor : null;
    }

    /** Traduce el texto del equipo a un patrón del ENUM, solo si es claro. */
    private function patronDesdeTexto(?string $texto): ?string
    {
        if ($texto === null || trim($texto) === '') {
            return null;
        }

        $t = mb_strtolower($texto);

        if (str_contains($t, 'mixt')) {
            return 'Patrón Mixto';
        }
        if (str_contains($t, 'obstruct')) {
            return 'Patrón Obstructivo';
        }
        if (str_contains($t, 'restrict')) {
            return 'Patrón Restrictivo';
        }
        if (str_contains($t, 'normal')) {
            return 'Espirometría Normal';
        }

        return null;
    }

    // =========================================================
    // ANÁLISIS DE PDF DE ESPIROMETRÍA (movido desde IAClinicaTrabajoService)
    // =========================================================

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

                $data = $this->consultarGeminiConPartes($partes, 'analizarEspirometria');

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

                $resultado['fecha_prueba'] = $this->capturarPrimero($textoNativo, '/Fecha del test\s+(\d{2}\/\d{2}\/\d{4})/u');
                $resultado['fumador_texto'] = $this->capturarPrimero($textoNativo, '/^Fumador\s+(.+)$/mu');

                return $resultado;
            } catch (\Throwable $e) {
                Log::error('Error al analizar espirometría', ['error' => $e->getMessage()]);
                return null;
            }
    }

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

    /**
     * Envía un arreglo de "parts" (texto y/o imágenes) a Gemini y decodifica
     * la respuesta JSON. Reintenta una vez si el JSON no se puede parsear.
     */
    private function consultarGeminiConPartes(array $partes, string $origen): ?array
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

    private function capturarPrimero(string $texto, string $patron): ?string
    {
        return preg_match($patron, $texto, $m) ? trim($m[1]) : null;
    }

    /** Fecha del informe: dd/mm/aaaa (PDF) o número serial de Excel. Devuelve AAAA-MM-DD. */
    private function fechaPrueba(mixed $valor): ?string
    {
        if ($valor === null || trim((string) $valor) === '') {
            return null;
        }
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', trim((string) $valor), $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        if (is_numeric($valor)) {
            return gmdate('Y-m-d', (int) round(((float) $valor - 25569) * 86400));
        }
        return null;
    }

    /** "EX; 4 Cigarrillo/s por día; Años de fumador 6" => fumador false, 4 cigarrillos, 6 años. */
    private function tabaquismoDesdeTexto(?string $texto): array
    {
        if ($texto === null || trim($texto) === '') {
            return [];
        }
        $t = trim($texto);
        $salida = ['fumador' => !preg_match('/^(EX|No)\b/iu', $t)];

        if (preg_match('/(\d+)\s*Cigarrillo/iu', $t, $m)) {
            $salida['cigarrillos_dia'] = (int) $m[1];
        }
        if (preg_match('/a[ñn]os de fumador\s*(\d+)/iu', $t, $m)) {
            $salida['anos_fumador'] = (int) $m[1];
        }
        return $salida;
    }

    
}