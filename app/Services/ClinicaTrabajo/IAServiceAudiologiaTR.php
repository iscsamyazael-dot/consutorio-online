<?php

namespace App\Services\ClinicaTrabajo;

use App\Models\ClinicaTrabajo\PacientesClinicaTrabajo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * IA del submódulo Trabajo de Alto Riesgo (TR).
 * La IA solo PROPONE campos. El médico revisa y guarda.
 * Modos: chat (conversación) y recomendar (diagnóstico y aptitud a partir de la ficha).
 */
class IAServiceAudiologiaTR
{
    private const MODELO = 'gemini-3.5-flash-lite';
    private const MAX_MENSAJES_HISTORIAL = 10;

    /** Aptitudes válidas */
    private const APTITUDES = ['Apto', 'Apto con restricciones', 'No apto'];

    /** Resultados de paraclínicos */
    private const RESULTADOS_PARACLINICOS = ['Normal', 'Anormal', 'No realizado'];

    /**
     * Campos que la IA puede proponer, agrupados por hoja.
     */
    private const CAMPOS_PERMITIDOS = [
        'hoja1' => [
            'nombre', 'puesto', 'fecha_nacimiento', 'edad', 'genero',
            'empresa', 'numero_empleado', 'departamento',
            'antiguedad_empresa_anos', 'antiguedad_empresa_meses',
            'antiguedad_puesto_anos', 'antiguedad_puesto_meses',
        ],
        'hoja2' => [
            // Coronaria
            'marcapaso', 'valvulopatias', 'hipertension_arterial', 'diabetes',
            'taquicardia', 'protesis_valvulares', 'insuficiencia_vascular',
            'cardiopatia_congenita', 'lesiones_vasculares', 'dislipidemia',
            'arritmias', 'cardiopatia_isquemica', 'trombosis', 'insuficiencia_cardiaca',
            // Vascular
            'varices', 'trombosis_venosa',
            // Metabólica
            'obesidad',
            // Neurológica
            'insomnio', 'convulsion', 'evc', 'letargo', 'paralisis', 'tce',
            'migrana', 'hipercinesia', 'epilepsia', 'hipoestesia', 'sincope',
            'hiperestesia', 'paresia', 'parestesia',
            // Respiratoria
            'asma', 'disnea', 'epoc', 'apnea_sueno', 'infectocontagiosos',
            'tromboembolia_pulmonar', 'neumopatias_intersticiales',
            // Osteomuscular
            'artropatias', 'lesiones_columna', 'amputacion',
            'arcos_movimiento_limitados', 'deformidad', 'espasticidad',
            'rigidez', 'hipotonia',
            // Visual
            'catarata', 'discromatopsia', 'glaucoma', 'ptosis_palpebral',
            'estrabismo', 'ceguera_nocturna', 'retinopatia', 'estereopsis_anormal',
            'diplopia', 'campimetria_anormal', 'nistagmo', 'movimientos_oculares',
            // Auditiva
            'hipoacusia_severa', 'trauma_acustico', 'vertigo_equilibrio',
            'tinnitus_acufeno', 'anacusia',
            // Hematopoyética
            'anemia_grave', 'policitemia_vera', 'neutropenia', 'neoplasias',
            'sx_mielodisplasicos', 'trastornos_plaquetas',
            // Genitourinaria
            'enf_renal_cronica', 'pielonefritis', 'litiasis_renal', 'incontinencia_severa',
            // Dermatológica
            'dermatitis', 'lesiones_premalignas',
            // Endocrinológica
            'infeccion_act', 'hipertiroidismo', 'hipotiroidismo_sev',
            'enf_addison', 'sx_cushing',
            // Digestiva
            'gastroparesia_sev', 'erge_sii_severo', 'ulcera_peptica',
            'enf_crohn_activa', 'hepatopatia_grave', 'diverticulitis_aguda',
            // Psiquiátrica
            'trastorno_psicotico', 'trastorno_bipolar', 'depresion_mayor',
            'trastorno_sueno_vigilia', 'trastorno_estres_post',
            'ansiedad_generalizada', 'trastorno_personalidad',
            'abuso_dependencia_sust', 'deficit_cognitivo',
        ],
        'hoja3' => [
            'peso_kg', 'estatura_m', 'imc',
            'presion_sistolica', 'presion_diastolica',
            'frecuencia_cardiaca', 'frecuencia_respiratoria', 'score',
            'cabeza', 'ojos', 'oidos', 'nariz', 'boca', 'cuello',
            'cardiovascular', 'pulmonar', 'abdomen', 'genitourinario',
            'osteomuscular', 'neurologico', 'tegumentos',
        ],
        'hoja4' => [
            'ecg', 'rx_torax', 'evaluacion_visual', 'audiometria', 'espirometria',
            'diagnosticos', 'enfermedad_profesional',
            'diagnostico_principal', 'aptitud', 'observaciones', 'recomendaciones',
        ],
    ];

    /** Campos que genera el modo recomendar */
    private const CAMPOS_RECOMENDACION = [
        'diagnostico_principal', 'aptitud', 'observaciones', 'recomendaciones',
    ];

    /**
     * Reglas duras: combinación de riesgo + patología = No apto.
     */
    private const REGLAS_NO_APTO = [
        'altura' => [
            'epilepsia', 'convulsion', 'sincope', 'vertigo_equilibrio',
            'cardiopatia_isquemica', 'insuficiencia_cardiaca',
        ],
        'espacio_confinado' => [
            'trastorno_psicotico', 'ansiedad_generalizada',
            'epoc', 'asma', 'cardiopatia_isquemica',
        ],
        'trabajo_electrico' => [
            'marcapaso', 'protesis_valvulares', 'epilepsia', 'convulsion',
            'discromatopsia',
        ],
        'maquinaria_pesada' => [
            'epilepsia', 'convulsion', 'sincope', 'vertigo_equilibrio',
            'ceguera_nocturna', 'diplopia', 'campimetria_anormal',
            'abuso_dependencia_sust',
        ],
        'materiales_peligrosos' => [
            'enf_addison', 'sx_cushing', 'hipotiroidismo_sev', 'hipertiroidismo',
            'hepatopatia_grave', 'enf_renal_cronica',
        ],
        'izaje_carga' => [
            'cardiopatia_isquemica', 'insuficiencia_cardiaca', 'lesiones_columna',
            'artropatias',
        ],
        'trabajo_caliente' => [
            'epilepsia', 'convulsion', 'sincope', 'trastorno_psicotico',
            'abuso_dependencia_sust',
        ],
        'excavacion_zanjas' => [
            'epilepsia', 'convulsion', 'sincope', 'vertigo_equilibrio',
            'cardiopatia_isquemica', 'insuficiencia_cardiaca',
        ],
    ];

    /**
     * Reglas de aptitud con restricciones (no inhabilitan pero requieren ajuste).
     */
    private const REGLAS_RESTRICCIONES = [
        'altura' => [
            'hipertension_arterial' => 'HTA controlada: apto con restricción de altura >5m',
            'diabetes' => 'Diabetes controlada: apto con restricción de altura >5m',
            'obesidad' => 'Obesidad: apto con restricción de arneses especiales',
        ],
        'trabajo_electrico' => [
            'discromatopsia' => 'Discromatopsia: apto con restricción (no trabajos con códigos de color)',
            'hipoacusia_severa' => 'Hipoacusia severa: apto con restricción (señales visuales obligatorias)',
        ],
        'maquinaria_pesada' => [
            'hipoacusia_severa' => 'Hipoacusia: apto con restricción (señales visuales obligatorias)',
            'catarata' => 'Catarata: apto con restricción (corrección óptica obligatoria)',
            'glaucoma' => 'Glaucoma: apto con restricción (control oftalmológico semestral)',
        ],
    ];

    /**
     * Consulta en modo chat
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
     * Modo recomendar: propone diagnóstico, aptitud, observaciones y recomendaciones.
     */
    public function recomendar(array $contexto, ?PacientesClinicaTrabajo $paciente = null): array
    {
        set_time_limit(300);
        $hallazgos = $this->calcularHallazgos($contexto);
        $prompt = $this->construirPromptRecomendacion($contexto, $paciente, $hallazgos);
        $data = $this->llamarGemini($prompt);

        if ($data === null) {
            return $this->respuestaFallida();
        }

        $advertencias = [];
        $campos = $this->sanitizarCampos($data['campos'] ?? [], $advertencias);
        $hoja4 = array_intersect_key($campos['hoja4'] ?? [], array_flip(self::CAMPOS_RECOMENDACION));

        // Normalizar recomendaciones
        $hoja4['recomendaciones'] = $this->normalizarRecomendaciones($hoja4['recomendaciones'] ?? []);
        if (!$hoja4['recomendaciones']) {
            unset($hoja4['recomendaciones']);
        }

        // Aplicar reglas duras
        $this->aplicarReglasAptitud($hoja4, $contexto, $hallazgos, $advertencias);

        // Validar que la aptitud propuesta sea coherente con los riesgos
        $this->validarAptitudContraRiesgos($hoja4, $contexto, $hallazgos, $advertencias);

        // Banderas rojas: si hay signos vitales críticos y la IA no los menciona
        $this->validarBanderasRojas($hoja4, $hallazgos, $advertencias);

        // ===== GENERAR DIAGNÓSTICOS CON CIE-10 =====
        $diagnosticos = $this->generarDiagnosticos($contexto, $hallazgos);
        if (!empty($diagnosticos)) {
            $hoja4['diagnosticos'] = $diagnosticos;
            // El primero siempre es el principal
            $hoja4['diagnostico_principal'] = $diagnosticos[0] ?? '';
        }

        $campos = $hoja4 ? ['hoja4' => $hoja4] : [];

        // Números no anclados
        foreach ($this->numerosNoAnclados($hoja4, $contexto) as $numero) {
            $advertencias[] = "Revisa el número {$numero}: no aparece en la ficha.";
        }

        return [
            'respuesta' => 'Recomendaciones generadas. Revísalas antes de guardar.',
            'campos_completados' => $campos,
            'advertencias' => $advertencias,
        ];
    }

    /**
     * Calcula en PHP los hallazgos clínicos relevantes para TR.
     */
    private function calcularHallazgos(array $contexto): array
    {
        $h2 = $contexto['hoja2'] ?? [];
        $h3 = $contexto['hoja3'] ?? [];
        $h4 = $contexto['hoja4'] ?? [];
        $h1 = $contexto['hoja1'] ?? [];

        $hallazgos = [
            'riesgos_trabajo' => [],
            'patologias_activas' => [],
            'patologias_controladas' => [],
            'signos_vitales_anormales' => [],
            'sistemas_anormales' => [],
            'paraclinicos_anormales' => [],
            'banderas_rojas' => [],
            'imc' => null,
            'clasificacion_imc' => null,
            'clasificacion_pa' => null,
        ];

        // 1. Riesgos de trabajo seleccionados
        $riesgos = $h1['tipo_trabajo_riesgo'] ?? [];
        foreach ($riesgos as $riesgo => $activo) {
            if ($activo) {
                $hallazgos['riesgos_trabajo'][] = $riesgo;
            }
        }

        // 2. Patologías activas
        foreach (self::CAMPOS_PERMITIDOS['hoja2'] as $campo) {
            $valor = $h2[$campo] ?? null;
            if ($this->esPatologiaActiva($campo, $valor)) {
                $hallazgos['patologias_activas'][] = $campo;
                if (is_array($valor) && isset($valor['enControl']) && $valor['enControl'] === 'Si') {
                    $hallazgos['patologias_controladas'][] = $campo;
                }
            }
        }

        // 3. Signos vitales anormales
        $pa_sistolica = $h3['presion_sistolica'] ?? null;
        $pa_diastolica = $h3['presion_diastolica'] ?? null;
        $fc = $h3['frecuencia_cardiaca'] ?? null;
        $fr = $h3['frecuencia_respiratoria'] ?? null;

        if ($pa_sistolica && $pa_diastolica) {
            if ($pa_sistolica >= 140 || $pa_diastolica >= 90) {
                $hallazgos['signos_vitales_anormales'][] = "HTA: {$pa_sistolica}/{$pa_diastolica} mmHg";
                $hallazgos['clasificacion_pa'] = $this->clasificarPA($pa_sistolica, $pa_diastolica);
            }
        }

        if ($fc && ($fc < 60 || $fc > 100)) {
            $hallazgos['signos_vitales_anormales'][] = "FC anormal: {$fc} lpm";
        }

        if ($fr && ($fr < 12 || $fr > 20)) {
            $hallazgos['signos_vitales_anormales'][] = "FR anormal: {$fr} rpm";
        }

        // 4. IMC
        $peso = $h3['peso_kg'] ?? null;
        $estatura = $h3['estatura_m'] ?? null;
        if ($peso && $estatura && $estatura > 0) {
            $imc = round($peso / ($estatura * $estatura), 1);
            $hallazgos['imc'] = $imc;
            $hallazgos['clasificacion_imc'] = $this->clasificarIMC($imc);
        }

        // 5. Sistemas anormales
        $sistemas = ['cabeza', 'ojos', 'oidos', 'nariz', 'boca', 'cuello',
                     'cardiovascular', 'pulmonar', 'abdomen', 'genitourinario',
                     'osteomuscular', 'neurologico', 'tegumentos'];
        foreach ($sistemas as $sistema) {
            $datos = $h3[$sistema] ?? [];
            if (isset($datos['estado']) && $datos['estado'] !== 'Normal') {
                $hallazgos['sistemas_anormales'][] = [
                    'sistema' => $sistema,
                    'estado' => $datos['estado'],
                    'observaciones' => $datos['observaciones'] ?? '',
                ];
            }
        }

        // 6. Paraclínicos anormales
        if (($h4['ecg']['resultado'] ?? 'Normal') === 'Anormal') {
            $hallazgos['paraclinicos_anormales'][] = 'ECG anormal';
        }
        if (($h4['rx_torax']['resultado'] ?? 'Normal') === 'Anormal') {
            $hallazgos['paraclinicos_anormales'][] = 'Rx tórax anormal';
        }
        if (($h4['evaluacion_visual']['resultado'] ?? 'Normal') !== 'Normal') {
            $hallazgos['paraclinicos_anormales'][] = 'Evaluación visual anormal';
        }
        if (($h4['audiometria']['resultado'] ?? 'Normal') !== 'Normal') {
            $hallazgos['paraclinicos_anormales'][] = 'Audiometría anormal';
        }
        if (($h4['espirometria']['resultado'] ?? 'Normal') !== 'Normal') {
            $hallazgos['paraclinicos_anormales'][] = 'Espirometría anormal';
        }

        // 7. Banderas rojas (combinaciones críticas)
        $this->detectarBanderasRojas($hallazgos, $contexto);

        return $hallazgos;
    }

    /**
     * Determina si una patología está activa según su estructura.
     */
    private function esPatologiaActiva(string $campo, $valor): bool
    {
        if (is_bool($valor)) {
            return $valor;
        }
        if (is_array($valor)) {
            return $valor['activo'] ?? false;
        }
        return false;
    }

    /**
     * Clasifica la presión arterial según JNC 7.
     */
    private function clasificarPA(int $sistolica, int $diastolica): string
    {
        if ($sistolica < 120 && $diastolica < 80) return 'Normal';
        if ($sistolica < 140 && $diastolica < 90) return 'Prehipertensión';
        if ($sistolica < 160 || $diastolica < 100) return 'HTA Etapa 1';
        return 'HTA Etapa 2';
    }

    /**
     * Clasifica el IMC según OMS.
     */
    private function clasificarIMC(float $imc): string
    {
        if ($imc < 18.5) return 'Bajo peso';
        if ($imc < 25) return 'Normal';
        if ($imc < 30) return 'Sobrepeso';
        if ($imc < 35) return 'Obesidad Grado I';
        if ($imc < 40) return 'Obesidad Grado II';
        return 'Obesidad Grado III';
    }

    /**
     * Detecta banderas rojas (combinaciones críticas riesgo + patología).
     */
    private function detectarBanderasRojas(array &$hallazgos, array $contexto): void
    {
        $riesgos = $hallazgos['riesgos_trabajo'];
        $patologias = $hallazgos['patologias_activas'];

        foreach ($riesgos as $riesgo) {
            $reglas = self::REGLAS_NO_APTO[$riesgo] ?? [];
            foreach ($reglas as $patologia) {
                if (in_array($patologia, $patologias, true)) {
                    $hallazgos['banderas_rojas'][] = [
                        'tipo' => 'no_apto',
                        'riesgo' => $riesgo,
                        'patologia' => $patologia,
                        'mensaje' => "Trabajo de {$riesgo} + {$patologia} = No apto",
                    ];
                }
            }
        }

        // Signos vitales críticos
        $h3 = $contexto['hoja3'] ?? [];
        $pa_s = $h3['presion_sistolica'] ?? 0;
        $pa_d = $h3['presion_diastolica'] ?? 0;
        if ($pa_s >= 180 || $pa_d >= 110) {
            $hallazgos['banderas_rojas'][] = [
                'tipo' => 'critico',
                'mensaje' => "Crisis hipertensiva: {$pa_s}/{$pa_d} mmHg",
            ];
        }

        $fc = $h3['frecuencia_cardiaca'] ?? 0;
        if ($fc > 150 || $fc < 40) {
            $hallazgos['banderas_rojas'][] = [
                'tipo' => 'critico',
                'mensaje' => "Arritmia crítica: FC {$fc} lpm",
            ];
        }
    }

    /**
     * Aplica reglas duras de aptitud según riesgos y patologías.
     */
    private function aplicarReglasAptitud(array &$hoja4, array $contexto, array $hallazgos, array &$advertencias): void
    {
        $aptitudPropuesta = $hoja4['aptitud'] ?? null;

        // Si hay banderas rojas de "no_apto", forzar No apto
        $tieneNoApto = false;
        foreach ($hallazgos['banderas_rojas'] as $bandera) {
            if ($bandera['tipo'] === 'no_apto') {
                $tieneNoApto = true;
                break;
            }
        }

        if ($tieneNoApto && $aptitudPropuesta !== 'No apto') {
            $hoja4['aptitud'] = 'No apto';
            $advertencias[] = 'Aptitud corregida a "No apto" por combinación de riesgo laboral + patología inhabilitante.';
        }

        // Si hay banderas rojas críticas (signos vitales), forzar No apto
        foreach ($hallazgos['banderas_rojas'] as $bandera) {
            if ($bandera['tipo'] === 'critico') {
                $hoja4['aptitud'] = 'No apto';
                $advertencias[] = "Aptitud corregida a 'No apto' por hallazgo crítico: {$bandera['mensaje']}";
                break;
            }
        }
    }

    /**
     * Valida que la aptitud propuesta sea coherente con los riesgos laborales.
     */
    private function validarAptitudContraRiesgos(array &$hoja4, array $contexto, array $hallazgos, array &$advertencias): void
    {
        $aptitud = $hoja4['aptitud'] ?? null;
        if ($aptitud === 'Apto') {
            $riesgos = $hallazgos['riesgos_trabajo'];
            $patologias = $hallazgos['patologias_activas'];

            foreach ($riesgos as $riesgo) {
                $reglas = self::REGLAS_RESTRICCIONES[$riesgo] ?? [];
                foreach ($reglas as $patologia => $mensaje) {
                    if (in_array($patologia, $patologias, true)) {
                        $hoja4['aptitud'] = 'Apto con restricciones';
                        $advertencias[] = "Aptitud cambiada a 'Apto con restricciones': {$mensaje}";
                        break 2;
                    }
                }
            }
        }
    }

    /**
     * Valida que las banderas rojas se mencionen en las observaciones.
     */
    private function validarBanderasRojas(array &$hoja4, array $hallazgos, array &$advertencias): void
    {
        if (empty($hallazgos['banderas_rojas'])) {
            return;
        }

        $observaciones = mb_strtolower($hoja4['observaciones'] ?? '');
        $diagnostico = mb_strtolower($hoja4['diagnostico_principal'] ?? '');
        $textoCompleto = $observaciones . ' ' . $diagnostico;

        foreach ($hallazgos['banderas_rojas'] as $bandera) {
            $patologia = $bandera['patologia'] ?? '';
            if ($patologia && !str_contains($textoCompleto, $patologia)) {
                $advertencias[] = "Bandera roja no mencionada en observaciones: {$bandera['mensaje']}";
            }
        }
    }

    /**
     * Respuesta fallida
     */
    private function respuestaFallida(): array
    {
        return [
            'respuesta' => 'No pude procesar tu solicitud en este momento. Intenta de nuevo.',
            'campos_completados' => [],
            'advertencias' => ['La IA no respondió.'],
        ];
    }

    /**
     * Construye el prompt para modo chat
     */
    private function construirPrompt(string $mensaje, array $contexto, array $historial, ?PacientesClinicaTrabajo $paciente): string
    {
        $camposPermitidos = json_encode(self::CAMPOS_PERMITIDOS, JSON_UNESCAPED_UNICODE);
        $contextoJson = mb_substr(json_encode($contexto, JSON_UNESCAPED_UNICODE) ?: '{}', 0, 12000);
        $datosPaciente = $this->bloquePaciente($paciente);
        $historialTexto = $this->bloqueHistorial($historial);

        return <<<TXT
Eres el asistente del módulo de Trabajo de Alto Riesgo (TR) de Medicina del Trabajo.
Ayudas al médico a completar el Examen Médico para trabajos de alto riesgo.

REGLAS
- Solo propones valores de los campos permitidos listados abajo. Cualquier otro campo se descarta.
- Solo propones lo que el médico dictó o escribió en este mensaje o en el historial. No inventes datos.
- Si falta información necesaria, pídela en "respuesta".
- "aptitud" debe ser exactamente Apto, Apto con restricciones o No apto.
- Responde SOLO con JSON válido con esta forma:
{"respuesta": "texto para el médico", "campos": {"hoja2": {"diabetes": {"activo": true, "evolucion": "5 años", "enControl": "Si"}}}}
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

    /**
     * Construye el prompt para modo recomendar
     */
    private function construirPromptRecomendacion(array $contexto, ?PacientesClinicaTrabajo $paciente, array $hallazgos): string
    {
        $contextoJson = mb_substr(json_encode($contexto, JSON_UNESCAPED_UNICODE) ?: '{}', 0, 12000);
        $hallazgosJson = json_encode($hallazgos, JSON_UNESCAPED_UNICODE) ?: '{}';
        $datosPaciente = $this->bloquePaciente($paciente);

        $riesgosTexto = implode(', ', $hallazgos['riesgos_trabajo'] ?? []) ?: 'Ninguno especificado';
        $patologiasTexto = implode(', ', $hallazgos['patologias_activas'] ?? []) ?: 'Ninguna';

        return <<<TXT
Eres el asistente del módulo de Trabajo de Alto Riesgo (TR) de Medicina del Trabajo.
Con la ficha médica de abajo, propón el diagnóstico principal, la aptitud laboral, las observaciones y las recomendaciones.

CONTEXTO CLÍNICO
- Riesgos laborales: {$riesgosTexto}
- Patologías activas: {$patologiasTexto}

RECOMENDACIONES:
- Propón en "recomendaciones" las recomendaciones clínicas y de seguridad y salud en el trabajo que este caso necesite.
- Basa cada recomendación en los hallazgos de la ficha: riesgos laborales, patologías, signos vitales, exploración física y paraclínicos.
- Cada recomendación debe ser una acción concreta y verificable: qué hacer, quién, y en qué plazo si aplica.
- Considera las NOMs aplicables según el tipo de trabajo de alto riesgo:
  * Altura: NOM-009-STPS-2011
  * Espacio confinado: NOM-033-STPS-2015
  * Trabajo eléctrico: NOM-029-STPS-2011
  * Maquinaria pesada: NOM-029-STPS-2005
  * Materiales peligrosos: NOM-018-STPS-2015
  * Izaje de carga: NOM-006-STPS-2014
- Si hay banderas rojas (combinaciones críticas riesgo + patología), inclúyelas en el diagnóstico y justifica la aptitud.
- Ordena las recomendaciones por prioridad.
- Si la aptitud es "Apto", las recomendaciones son de prevención primaria y seguimiento routine.
- Si la aptitud es "Apto con restricciones", especifica claramente las restricciones laborales.
- Si la aptitud es "No apto", explica el motivo clínico y sugiere reubicación o seguimiento.

REGLAS
- En "diagnostico_principal", redacta un resumen clínico en prosa que integre: riesgos laborales, patologías activas, hallazgos de exploración física y paraclínicos anormales.
- Usa las cuatro hojas como un solo cuadro clínico: datos laborales (hoja 1), antecedentes (hoja 2), exploración física (hoja 3) y paraclínicos (hoja 4).
- Basa cada propuesta en la ficha. No inventes antecedentes, síntomas, exposiciones ni resultados.
- Los HALLAZGOS CALCULADOS ya identifican banderas rojas, signos vitales anormales, sistemas anormales y paraclínicos anormales. Describe lo que indican; no los recalcul.
- Menciona banderas rojas solo si HALLAZGOS CALCULADOS las registra. Si la lista está vacía, no las menciones.
- "aptitud" debe ser exactamente Apto, Apto con restricciones o No apto.
- No propongas "No apto" salvo que exista una bandera roja de tipo "no_apto" o "critico" en los hallazgos.
- Escribe siempre con acentos, tildes y la ñ del español correctamente.
- Cualquier número que cites debe aparecer en la ficha.
- Responde SOLO con JSON válido con esta forma:
{"campos": {"hoja4": {"diagnostico_principal": "...", "aptitud": "...", "observaciones": "...", "recomendaciones": ["...", "..."]}}}

HALLAZGOS CALCULADOS (no los modifiques)
{$hallazgosJson}

DATOS DEL PACIENTE (referencia)
{$datosPaciente}

FICHA MÉDICA
{$contextoJson}
TXT;
    }

    /**
     * Bloque con datos del paciente (para el prompt)
     */
    private function bloquePaciente(?PacientesClinicaTrabajo $paciente): string
    {
        if (!$paciente) {
            return 'No disponible.';
        }
        return 'Nombre: ' . ($paciente->nombre ?? 'N/D');
    }

    /**
     * Bloque con historial de conversación
     */
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
                Log::error('IAServiceAudiologiaTR: error HTTP', ['status' => $response?->status()]);
                return null;
            }

            return $this->decodificar($response->json('candidates.0.content.parts.0.text'));
        } catch (\Throwable $e) {
            Log::error('IAServiceAudiologiaTR: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Decodifica la respuesta JSON de Gemini, tolerante a texto extra.
     */
    private function decodificar(?string $texto): ?array
    {
        if ($texto === null || trim($texto) === '') {
            return null;
        }

        $data = json_decode($texto, true);
        if (is_array($data)) {
            return $data;
        }

        $inicio = strpos($texto, '{');
        $fin = strrpos($texto, '}');
        if ($inicio === false || $fin === false || $fin <= $inicio) {
            return null;
        }

        $data = json_decode(substr($texto, $inicio, $fin - $inicio + 1), true);
        return is_array($data) ? $data : null;
    }

    /**
     * Deja solo campos permitidos, descarta vacíos y valida aptitud.
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

                $limpios[$hoja][$campo] = $valor;
            }
        }

        return $limpios;
    }

    /**
     * Convierte la propuesta en una lista de textos limpios.
     */
    private function normalizarRecomendaciones(mixed $valor): array
    {
        $lista = is_string($valor) ? [$valor] : (is_array($valor) ? $valor : []);
        $limpias = [];

        foreach ($lista as $texto) {
            if (!is_string($texto)) {
                continue;
            }
            $texto = trim($texto);
            if ($texto === '') {
                continue;
            }
            $limpias[] = $texto;
        }

        return $limpias;
    }

    /**
     * Números que la IA cita en el texto y que no aparecen en la ficha.
     */
    private function numerosNoAnclados(array $hoja4, array $contexto): array
    {
        $texto = implode(' ', array_filter(
            [
                $hoja4['diagnostico_principal'] ?? '',
                $hoja4['observaciones'] ?? '',
            ],
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

    /**
 * Genera hasta 5 diagnósticos con CIE-10 basados en los hallazgos.
 */
    private function generarDiagnosticos(array $contexto, array $hallazgos): array
    {
        $diagnosticos = [];
        $h1 = $contexto['hoja1'] ?? [];
        $h2 = $contexto['hoja2'] ?? [];
        $h3 = $contexto['hoja3'] ?? [];
        $h4 = $contexto['hoja4'] ?? [];

        // 1. Diagnóstico principal: si no hay patologías, es examen normal
        if (empty($hallazgos['patologias_activas']) && empty($hallazgos['sistemas_anormales'])) {
            $diagnosticos[] = 'Z02.1 - Examen médico de aptitud laboral sin alteraciones';
        } else {
            // Mapeo de patologías a CIE-10
            $mapeoCIE10 = [
                'hipertension_arterial' => 'I10 - Hipertensión esencial (primaria)',
                'diabetes' => 'E11.9 - Diabetes mellitus tipo 2, sin complicaciones',
                'obesidad' => 'E66.9 - Obesidad, no especificada',
                'asma' => 'J45.9 - Asma, no especificada',
                'epoc' => 'J44.9 - Enfermedad pulmonar obstructiva crónica, no especificada',
                'epilepsia' => 'G40.9 - Epilepsia, no especificada',
                'cardiopatia_isquemica' => 'I25.1 - Cardiopatía isquémica aterosclerótica',
                'insuficiencia_cardiaca' => 'I50.9 - Insuficiencia cardíaca, no especificada',
                'dislipidemia' => 'E78.5 - Hiperlipidemia, no especificada',
                'arritmias' => 'I49.9 - Arritmia cardíaca, no especificada',
                'hipoacusia_severa' => 'H90.3 - Pérdida de audición neurosensorial bilateral',
                'vertigo_equilibrio' => 'H81.9 - Trastorno vestibular, no especificado',
                'catarata' => 'H26.9 - Catarata, no especificada',
                'glaucoma' => 'H40.9 - Glaucoma, no especificado',
                'lesiones_columna' => 'M54.5 - Dolor lumbar bajo',
                'artropatias' => 'M19.9 - Artrosis, no especificada',
                'enf_renal_cronica' => 'N18.9 - Enfermedad renal crónica, no especificada',
                'hepatopatia_grave' => 'K76.9 - Enfermedad hepática, no especificada',
                'depresion_mayor' => 'F32.9 - Episodio depresivo, no especificado',
                'ansiedad_generalizada' => 'F41.1 - Trastorno de ansiedad generalizada',
                'abuso_dependencia_sust' => 'F19.2 - Trastornos mentales y del comportamiento por uso de múltiples sustancias, dependencia',
            ];

            foreach ($hallazgos['patologias_activas'] as $patologia) {
                if (isset($mapeoCIE10[$patologia]) && count($diagnosticos) < 5) {
                    $diagnosticos[] = $mapeoCIE10[$patologia];
                }
            }

            // Si no hay patologías mapeadas pero hay sistemas anormales
            if (empty($diagnosticos) && !empty($hallazgos['sistemas_anormales'])) {
                $diagnosticos[] = 'R53.83 - Fatiga, no especificada (hallazgos en exploración física)';
            }
        }

        // 2. Agregar hallazgos de paraclínicos anormales
        if (!empty($hallazgos['paraclinicos_anormales']) && count($diagnosticos) < 5) {
            foreach ($hallazgos['paraclinicos_anormales'] as $paraclinico) {
                if (count($diagnosticos) >= 5) break;
                
                if (str_contains($paraclinico, 'ECG')) {
                    $diagnosticos[] = 'R94.31 - Electrocardiograma anormal';
                } elseif (str_contains($paraclinico, 'Rx tórax')) {
                    $diagnosticos[] = 'R91.8 - Otros hallazgos anormales en imágenes de campo pulmonar';
                } elseif (str_contains($paraclinico, 'visual')) {
                    $diagnosticos[] = 'H53.9 - Trastorno visual, no especificado';
                } elseif (str_contains($paraclinico, 'Audiometría')) {
                    $diagnosticos[] = 'H83.3 - Efectos del ruido sobre el oído interno';
                } elseif (str_contains($paraclinico, 'Espirometría')) {
                    $diagnosticos[] = 'R94.2 - Pruebas de función pulmonar anormales';
                }
            }
        }

        // 3. Agregar riesgos laborales relevantes si hay espacio
        if (!empty($hallazgos['riesgos_trabajo']) && count($diagnosticos) < 5) {
            $riesgosCIE10 = [
                'altura' => 'Z57.8 - Exposición ocupacional a otros factores de riesgo',
                'espacio_confinado' => 'Z57.8 - Exposición ocupacional a otros factores de riesgo',
                'trabajo_electrico' => 'Z57.8 - Exposición ocupacional a otros factores de riesgo',
                'maquinaria_pesada' => 'Z57.8 - Exposición ocupacional a otros factores de riesgo',
                'materiales_peligrosos' => 'Z57.3 - Exposición ocupacional a otros contaminantes del aire',
            ];
            
            foreach ($hallazgos['riesgos_trabajo'] as $riesgo) {
                if (count($diagnosticos) >= 5) break;
                if (isset($riesgosCIE10[$riesgo])) {
                    $diagnosticos[] = $riesgosCIE10[$riesgo];
                }
            }
        }

        // Limitar a 5 diagnósticos
        return array_slice($diagnosticos, 0, 5);
    }
}