<?php

namespace App\Http\Controllers\ClinicaTrabajo;

use App\Http\Controllers\Controller;
use App\Models\ClinicaTrabajo\DatosLaboral;
use App\Models\ClinicaTrabajo\PacientesClinicaTrabajo;
use App\Models\ClinicaTrabajo\ValoracionOcupacional;
use App\Models\Empresa;
use App\Models\Medico;
use App\Services\IAClinicaTrabajoService;
use App\Services\ClinicaTrabajo\IAServiceFichaOcupacional;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FichaOcupacionalController extends Controller
{
    /** Relaciones que se cargan en las respuestas. */
    private const RELACIONES = ['paciente', 'empresaCliente', 'puestoTrabajo', 'medico'];

    private const AGENTES = [
        'polvo_mineral', 'asbesto', 'silice', 'berilio', 'cadmio', 'plomo', 'mercurio',
        'cromo', 'arsenico', 'niquel', 'dioxinas', 'vibraciones', 'ruido',
        'radiacion_ionizante', 'radiacion_no_ionizante', 'temperatura_extrema',
        'estres_termico', 'exposicion_solar',
    ];

    // Clave de la UI => columna de condiciones_riesgo
    private const CONDICIONES = [
        'trabajo_altura_2m' => 'trabajo_altura_2m',
        'espacio_confinado' => 'espacio_confinado',
        'maquinaria_pesada' => 'maquinaria_pesada',
        'cargas_25kg' => 'cargas_25kg',
        'sustancias_quimicas' => 'sustancias_quimicas',
        'agentes_biologicos' => 'agentes_biologicos',
        'radiaciones_ionizantes' => 'radiaciones_ionizantes',
        'radiaciones_no_ionizantes' => 'radiaciones_no_ionizantes',
        'turnos_rotativos_nocturnos' => 'turnos_rotativos_nocturnos',
        'estres_ocupacional' => 'estres_ocupacional',
        'trabajo_repetitivo' => 'trabajo_repetitivo',
        'posturas_forzadas' => 'posturas_forzadas',
        'vibraciones_2' => 'vibraciones',
        'temperatura_extrema_2' => 'temperatura_extrema',
        'exposicion_solar_prolongada' => 'exposicion_solar_prolongada',
    ];

    private const EXIGENCIAS = [
        'carga_mental', 'decisiones_criticas', 'responsabilidad_personas', 'comunicacion_constante',
        'precision_extrema', 'ritmo_acelerado', 'monotonia', 'falta_autonomia', 'conflictos',
        'acoso', 'discriminacion',
    ];

    private const HEREDO = [
        'cancer', 'diabetes', 'hipertension', 'cardiopatias', 'infarto', 'acv', 'pulmonar',
        'tuberculosis', 'hepatica', 'renal', 'artritis', 'osteoporosis', 'mental', 'adiccion',
    ];

    private const PATOLOGICOS = [
        'hipertension', 'cardiopatia', 'infarto', 'arritmia', 'asma', 'epoc', 'tuberculosis',
        'neumonia', 'silicosis', 'ulcera', 'gastritis', 'hepatitis', 'cirrosis', 'diabetes',
        'hipertiroidismo', 'hipotiroidismo', 'nefritis', 'litiasis', 'artrosis', 'artritis',
        'osteoporosis', 'hernia_discal', 'sindrome_tunel_carpal', 'epilepsia', 'migraña',
        'depresion', 'ansiedad',
    ];

    // Solo estas condiciones tienen columna *_obs en antecedentes_patologicos
    private const PATOLOGICOS_CON_OBS = [
        'hipertension', 'cardiopatia', 'infarto', 'diabetes', 'tuberculosis', 'hepatitis',
        'hernia_discal', 'sindrome_tunel_carpal', 'epilepsia',
    ];

    private const VACUNAS = ['covid19', 'td', 'tdpa', 'influenza', 'hepatitis_ab', 'sr'];

    private const TIPOS_REACCION = ['leve', 'moderada', 'severa'];

    // Clave de sistema en la UI => prefijo de columnas en clinico_examen
    private const SISTEMAS = [
        'cabeza' => 'cabeza',
        'cuello' => 'cuello',
        'ojos' => 'ojos',
        'oidos' => 'oidos',
        'nariz' => 'nariz',
        'cardiovascular' => 'cardiovascular',
        'pulmonar' => 'pulmonar',
        'gastrointestinal' => 'gastrointestinal',
        'abdomen' => 'abdomen',
        'miembros_superiores' => 'miembros_sup',
        'miembros_inferiores' => 'miembros_inf',
        'genitourinario' => 'genitourinario',
        'piel' => 'piel',
        'inmunologico' => 'inmunologico',
    ];

    // Campos específicos de cada sistema => columna
    private const CAMPOS_SISTEMA = [
        'cuello' => [
            'tiroides' => 'cuello_tiroides',
            'traquea' => 'cuello_traquea',
            'adenopatias' => 'cuello_adenopatias',
            'neoplasias' => 'cuello_neoplasias',
        ],
        'ojos' => [
            'agudeza_visual_od' => 'ojos_agudeza_od',
            'agudeza_visual_oi' => 'ojos_agudeza_oi',
            'ishihara' => 'ojos_ishihara',
            'presion_intraocular' => 'ojos_presion_intraocular',
        ],
        'oidos' => [
            'acufenos' => 'oidos_acufenos',
            'mareos' => 'oidos_mareos',
            'audiometria' => 'oidos_audiometria',
        ],
        'nariz' => [
            'epistaxis' => 'nariz_epistaxis',
            'obstruccion' => 'nariz_obstruccion',
            'desviacion_septal' => 'nariz_desviacion_septal',
        ],
        'cardiovascular' => [
            'auscultacion' => 'cardiovascular_auscultacion',
            'pulsos_perifericos' => 'cardiovascular_pulsos',
            'edemas' => 'cardiovascular_edemas',
            'varices' => 'cardiovascular_varices',
        ],
        'pulmonar' => [
            'auscultacion' => 'pulmonar_auscultacion',
            'tos_esputo' => 'pulmonar_tos_esputo',
            'espirometria' => 'pulmonar_espirometria',
        ],
        'gastrointestinal' => [
            'palpacion' => 'gastrointestinal_palpacion',
            'ruidos_intestinales' => 'gastrointestinal_ruidos',
            'tamano_higado' => 'gastrointestinal_higado',
            'bazo' => 'gastrointestinal_bazo',
        ],
        'genitourinario' => [
            'hallazgos_genitales' => 'genitourinario_hallazgos',
            'puño_percusion' => 'genitourinario_puño_percusion',
        ],
        'piel' => [
            'dermatitis' => 'piel_dermatitis',
            'verrugas' => 'piel_verrugas',
            'nevos' => 'piel_nevos',
            'nodulos' => 'piel_nodulos',
            'acne' => 'piel_acne',
        ],
        'inmunologico' => [
            'lupus' => 'inmunologico_lupus',
            'vih' => 'inmunologico_vih',
            'artritis_reumatoide' => 'inmunologico_artritis_reumatoide',
        ],
    ];

    // Tablas con valoracion_id que se reemplazan al actualizar
    private const TABLAS_HIJAS = [
        'datos_puesto', 'agentes_exposicion', 'condiciones_riesgo', 'otras_condiciones_trabajo',
        'antecedentes_accidentes', 'antecedentes_heredofamiliares', 'antecedentes_no_patologicos',
        'antecedentes_gineco', 'antecedentes_patologicos', 'clinico_examen',
        'pruebas_complementarias', 'incapacidades_salud', 'enfermedades_profesionales_reportadas',
    ];

    private const CAMPOS_BOOLEANOS = [
        'acufenos', 'mareos', 'epistaxis', 'obstruccion', 'dermatitis', 'verrugas',
        'nevos', 'nodulos', 'acne', 'lupus', 'vih', 'artritis_reumatoide',
    ];

    /** GET /api/clinica/medicina/fichas */
    public function index(Request $request): JsonResponse
    {
        $fichas = ValoracionOcupacional::query()
            ->when($request->filled('paciente_id'), fn ($q) => $q->where('paciente_id', $request->input('paciente_id')))
            ->when($request->filled('empresa_id'), fn ($q) => $q->where('empresa_cliente_id', $request->input('empresa_id')))
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->input('tipo')))
            ->when($request->filled('aptitud'), fn ($q) => $q->where('dictamen', $request->input('aptitud')))
            ->when(
                $request->filled('fecha_desde') && $request->filled('fecha_hasta'),
                fn ($q) => $q->whereBetween('fecha_valoracion', [
                    $request->input('fecha_desde'),
                    $request->input('fecha_hasta'),
                ])
            )
            ->with(self::RELACIONES)
            ->orderByDesc('fecha_valoracion')
            ->orderByDesc('id')
            ->paginate(15);

            $fichas->getCollection()->transform(function (ValoracionOcupacional $ficha) {
            $ficha->setAttribute('riesgos', $this->riesgosValoracion($ficha->id));

            return $ficha;
        });

        return response()->json($fichas);
    }

    /** GET /api/clinica/medicina/fichas/create: folio sugerido y médico, sin reservarlos. */
    public function create(Request $request): JsonResponse
    {
        $medicoNombre = Medico::where('user_id', $request->user()->id)
            ->where('activo', 1)
            ->value('nombre');

        return response()->json([
            'success' => true,
            'folio' => $this->folioSugerido(),
            'medico' => $medicoNombre,
        ]);
    }

    /** POST /api/clinica/medicina/fichas */
    public function store(Request $request): JsonResponse
    {
        $medicoId = $this->medicoId($request);
        if (!$medicoId) {
            return $this->sinMedico();
        }

        $validated = $request->validate($this->reglas());

       $valoracion = $this->crearFicha($request->all(), $medicoId);

        return response()->json([
            'success' => true,
            'message' => 'Ficha médica guardada correctamente',
            'data' => $valoracion->load(self::RELACIONES),
        ], 201);
    }

    /** GET /api/clinica/medicina/fichas/{ficha} */
    public function show(string $id): JsonResponse
    {
        $ficha = ValoracionOcupacional::with(array_merge(
            self::RELACIONES,
            ['estudiosParaclinicos', 'normasAplicadas', 'seguimientos']
        ))->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $ficha,
        ]);
    }

    /** GET /api/clinica/medicina/fichas/{ficha}/edit: ficha guardada con la forma del formulario. */
    public function edit(string $id): JsonResponse
    {
        $ficha = ValoracionOcupacional::with(self::RELACIONES)->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'folio' => $ficha->folio,
                'paciente_id' => $ficha->paciente_id,
                'paciente' => $ficha->paciente,
                'esta_firmada' => $ficha->esta_firmada,
                'form' => $this->formularioDesdeFicha($ficha),
            ],
        ]);
    }

    /** PUT|PATCH /api/clinica/medicina/fichas/{ficha} */
    public function update(Request $request, string $id): JsonResponse
    {
        $valoracion = ValoracionOcupacional::findOrFail($id);
        
        if ($valoracion->esta_firmada) {
            return response()->json([
                'success' => false,
                'message' => 'La ficha ya fue firmada y no se puede modificar.',
            ], 409);
        }

        $validated = $request->validate($this->reglas());

        if ((int) $validated['paciente_id'] !== (int) $valoracion->paciente_id) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede cambiar el paciente de una ficha existente.',
            ], 422);
        }

        $valoracion = $this->actualizarFicha($valoracion, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Ficha médica actualizada correctamente',
            'data' => $valoracion->load(self::RELACIONES),
        ]);
    }

    // Sin destroy: las valoraciones son registros médicos y no se borran.

    /** GET /api/clinica/medicina/fichas/{ficha}/imprimir: reporte PDF. */
    public function imprimir(Request $request, $id)
    {
        $ficha = ValoracionOcupacional::with(['paciente', 'empresaCliente', 'puestoTrabajo', 'medico'])->findOrFail($id);
        $version = $request->query('version') === 'empresa' ? 'empresa' : 'completo';
        $consultorio = $this->datosConsultorio($ficha);
        $riesgos = $this->riesgosValoracion($ficha->id);

        $pdf = Pdf::loadView('pdf.ClinicaTrabajo.Ocupacional.ficha_ocupacional', [
            'v'         => $ficha,
            'paciente'  => $ficha->paciente,
            'version'   => $version,
            'medico'    => $ficha->medico?->usuario,
            'cedula'    => $ficha->medico?->cedula_profesional,
            'ubicacion' => $consultorio['ubicacion'],
            'logoPath'  => $consultorio['logoPath'],
            'riesgos'   => $riesgos,
        ])->setPaper('letter');

        return $pdf->stream('ficha-ocupacional-' . $ficha->folio . '.pdf');
    }

    /**
     * POST /api/clinica/medicina/consultar-ia
     * Consulta a la IA con el contexto de la ficha (copiado de ValoracionMedicinaController).
     */
    public function consultarIA(Request $request, IAClinicaTrabajoService $ia): JsonResponse
    {
        $validated = $request->validate([
            'mensaje' => 'required|string|min:1|max:40000',
            'paciente_id' => 'required|exists:pacientes_clinica_trabajo,id',
            'contexto' => 'nullable|array',
        ]);

        $resultado = $ia->consultarMedicinaOcupacional(
            $validated['mensaje'],
            $validated['paciente_id'],
            $validated['contexto'] ?? []
        );

        if ($resultado === null) {
            return response()->json([
                'success' => false,
                'message' => 'La IA no pudo procesar tu solicitud. Intenta de nuevo.',
            ], 502);
        }

        return response()->json([
            'success' => true,
            'data' => $resultado,
        ]);
    }

    /** Propuesta automática de la IA: diagnóstico -> aptitud -> recomendaciones -> dictamen. */
    public function proponer(Request $request, IAServiceFichaOcupacional $ia): JsonResponse
    {
        $request->validate([
            'datos_puesto' => 'required|array',
        ]);

        return response()->json([
            'success' => true,
            'data' => $ia->proponer($request->all()),
        ]);
    }

    /** GET /clinica/medicina: vista Blade (copiado de ValoracionMedicinaController). */
    public function vista()
    {
        return view('clinica-trabajo.Medicina');
    }

    // ===================================================================
    // Validación y acceso
    // ===================================================================

    private function reglas(): array
    {
        return [
            'paciente_id' => 'required|exists:pacientes_clinica_trabajo,id',
            'datos_puesto' => 'required|array',
            'datos_puesto.tipo_evaluacion' => 'required|in:ingreso,periodico,egreso,extraordinario,cambio_puesto,retorno_laboral',
            'datos_puesto.fecha_evaluacion' => 'required|date',
            'datos_puesto.hora_evaluacion' => 'nullable|date_format:H:i',
            'datos_puesto.puesto' => 'nullable|string|max:150',
            'exposicion_riesgos' => 'nullable|array',
            'antecedentes' => 'nullable|array',
            'clinico_examen' => 'nullable|array',
            'aptitud' => 'required|in:apto,apto_con_restricciones,no_apto',
            'aptitud_hoja' => 'nullable|array',
            'aptitud_hoja.restricciones' => 'required_if:aptitud,apto_con_restricciones',
            'conversacion' => 'nullable|array',
        ];
    }

    /** Sucursal del médico (o empresa si no tiene) y logo del tenant, con respaldo genérico. */
    private function datosConsultorio(ValoracionOcupacional $valoracion): array
    {
        $ubicacion = null;
        $medicoCatalogo = $valoracion->medico;
        if ($medicoCatalogo) {
            $medicoCatalogo->load('configuraciones.ubicacion');
            $ubicacion = $medicoCatalogo->configuraciones->first()?->ubicacion;
        }

        if (!$ubicacion) {
            $empresa = Empresa::first();
            if ($empresa) {
                $ubicacion = (object) [
                    'nombre'    => $empresa->nombre_empresa,
                    'direccion' => $empresa->direccion,
                    'telefono'  => $empresa->telefono,
                    'imagen'    => $empresa->logo_url,
                ];
            }
        }

        $logoRelativo = Empresa::first()?->logo_url ?: $ubicacion?->imagen;

        $logoPath = null;
        if (!empty($logoRelativo)) {
            $valorImagen = ltrim($logoRelativo, '/');

            $rutaCandidata = str_starts_with($valorImagen, 'personalisarperfil/')
                ? public_path($valorImagen)
                : public_path('personalisarperfil/' . $valorImagen);

            $logoPath = file_exists($rutaCandidata) ? $rutaCandidata : null;
        }

        if (!$logoPath) {
            $logoGenerico = public_path('images/logo.png');
            $logoPath = file_exists($logoGenerico) ? $logoGenerico : null;
        }

        return ['ubicacion' => $ubicacion, 'logoPath' => $logoPath];
    }


    /** Riesgos capturados en la valoración (tablas ligadas a valoracion_id). */
    private function riesgosValoracion(int $valoracionId): array
    {
        $etiquetasAgentes = [
            'polvo_mineral' => 'Polvo mineral', 'asbesto' => 'Asbesto', 'silice' => 'Sílice',
            'berilio' => 'Berilio', 'cadmio' => 'Cadmio', 'plomo' => 'Plomo', 'mercurio' => 'Mercurio',
            'cromo' => 'Cromo', 'arsenico' => 'Arsénico', 'niquel' => 'Níquel', 'dioxinas' => 'Dioxinas',
            'vibraciones' => 'Vibraciones', 'ruido' => 'Ruido', 'radiacion_ionizante' => 'Radiación ionizante',
            'radiacion_no_ionizante' => 'Radiación no ionizante', 'temperatura_extrema' => 'Temperatura extrema',
            'estres_termico' => 'Estrés térmico', 'exposicion_solar' => 'Exposición solar',
        ];

        $etiquetasCondiciones = [
            'trabajo_altura_2m' => 'Trabajo en altura (2 m)', 'espacio_confinado' => 'Espacio confinado',
            'maquinaria_pesada' => 'Maquinaria pesada', 'cargas_25kg' => 'Cargas mayores a 25 kg',
            'sustancias_quimicas' => 'Sustancias químicas', 'agentes_biologicos' => 'Agentes biológicos',
            'radiaciones_ionizantes' => 'Radiaciones ionizantes', 'radiaciones_no_ionizantes' => 'Radiaciones no ionizantes',
            'turnos_rotativos_nocturnos' => 'Turnos rotativos o nocturnos', 'estres_ocupacional' => 'Estrés ocupacional',
            'trabajo_repetitivo' => 'Trabajo repetitivo', 'posturas_forzadas' => 'Posturas forzadas',
            'vibraciones' => 'Vibraciones', 'temperatura_extrema' => 'Temperatura extrema',
            'exposicion_solar_prolongada' => 'Exposición solar prolongada',
        ];

        $etiquetasExigencias = [
            'carga_mental' => 'Carga mental', 'decisiones_criticas' => 'Decisiones críticas',
            'responsabilidad_personas' => 'Responsabilidad sobre personas', 'comunicacion_constante' => 'Comunicación constante',
            'precision_extrema' => 'Precisión extrema', 'ritmo_acelerado' => 'Ritmo acelerado', 'monotonia' => 'Monotonía',
            'falta_autonomia' => 'Falta de autonomía', 'conflictos' => 'Conflictos', 'acoso' => 'Acoso', 'discriminacion' => 'Discriminación',
        ];

        $agentes = DB::table('agentes_exposicion')->where('valoracion_id', $valoracionId)->first();
        $condiciones = DB::table('condiciones_riesgo')->where('valoracion_id', $valoracionId)->first();
        $otras = DB::table('otras_condiciones_trabajo')->where('valoracion_id', $valoracionId)->first();

        $marcados = fn ($fila, array $etiquetas) => $fila
            ? collect($etiquetas)->filter(fn ($etiqueta, $campo) => (bool) ($fila->{$campo} ?? 0))->values()->all()
            : [];

        return [
            'agentes'       => $marcados($agentes, $etiquetasAgentes),
            'agentes_otros' => $agentes?->otros ?: null,
            'condiciones'   => $marcados($condiciones, $etiquetasCondiciones),
            'postura'       => $otras?->realiza_generalmente ?: null,
            'exigencias'    => $marcados($otras, $etiquetasExigencias),
        ];
    }

    private function medicoId(Request $request): ?int
    {
        return Medico::where('user_id', $request->user()->id)
            ->where('activo', 1)
            ->value('id');
    }

    private function sinMedico(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Tu usuario no está asociado a un médico activo. Pide al administrador que lo vincule.',
        ], 403);
    }

    // ===================================================================
    // Guardado (crear y actualizar)
    // ===================================================================

    private function crearFicha(array $datos, int $medicoId): ValoracionOcupacional
    {
        return DB::transaction(function () use ($datos, $medicoId) {
            $laboral = $this->datosLaboralActual((int) $datos['paciente_id']);

            $valoracion = ValoracionOcupacional::create(array_merge(
                $this->atributosValoracion($datos, $laboral, $medicoId),
                ['folio' => $this->generarFolio()]
            ));

            $this->poblarHijas($valoracion, $datos, $laboral, true);

            return $valoracion;
        });
    }

    private function actualizarFicha(ValoracionOcupacional $valoracion, array $datos): ValoracionOcupacional
    {
        return DB::transaction(function () use ($valoracion, $datos) {
            $laboral = $this->datosLaboralActual((int) $datos['paciente_id']);

            // El folio y el médico que la creó no cambian
            $valoracion->update($this->atributosValoracion($datos, $laboral, $valoracion->medico_id));

            $this->borrarHijas($valoracion->id);
            $this->poblarHijas($valoracion, $datos, $laboral, $this->esUltimaFicha($valoracion));

            return $valoracion->fresh();
        });
    }

    private function datosLaboralActual(int $pacienteId): DatosLaboral
    {
        $laboral = DatosLaboral::where('paciente_id', $pacienteId)->latest('id')->first();

        if (!$laboral || !$laboral->empresa_cliente_id) {
            abort(422, 'El paciente no tiene empresa asignada en datos laborales.');
        }

        return $laboral;
    }

    private function atributosValoracion(array $datos, DatosLaboral $laboral, int $medicoId): array
    {
        $dp = $datos['datos_puesto'] ?? [];
        $hoja4 = $datos['aptitud_hoja'] ?? [];
        $ce = $datos['clinico_examen'] ?? [];

        $vitales = $this->signosVitales($dp, $ce);
        $antropo = $this->antropometria($ce);

        return [
            'paciente_id' => $datos['paciente_id'],
            'empresa_cliente_id' => $laboral->empresa_cliente_id,
            'puesto_trabajo_id' => $laboral->puesto_trabajo_id,
            'puesto_nombre_snapshot' => $this->texto($dp['puesto'] ?? null, 150),
            'medico_id' => $medicoId,
            'tipo' => $dp['tipo_evaluacion'],
            'fecha_valoracion' => $dp['fecha_evaluacion'],
            'hora_valoracion' => $this->nulo($dp['hora_evaluacion'] ?? null),
            'ta_sistolica' => $vitales['ta_sistolica'],
            'ta_diastolica' => $vitales['ta_diastolica'],
            'fc' => $vitales['fc'],
            'fr' => $vitales['fr'],
            'spo2' => $vitales['spo2'],
            'temperatura_c' => $vitales['temperatura_c'],
            'glicemia' => $vitales['glicemia'],
            'estatura_cm' => $antropo['estatura_cm'],
            'peso_kg' => $antropo['peso_kg'],
            'talla_abdomen_cm' => $antropo['talla_abdomen_cm'],
            'perimetro_cadera_cm' => $antropo['perimetro_cadera_cm'],
            'imc' => $antropo['imc'],
            'imc_clasificacion' => $antropo['clasificacion'],
            'estado_nutricional' => $antropo['clasificacion'],
            'complexion_fisica' => $antropo['complexion_fisica'],
            'anos_puesto' => $this->entero($dp['antiguedad'] ?? null),
            'analisis_correlacion_riesgos' => $this->nulo($hoja4['hallazgos_relevantes_aptitud'] ?? null),
            'diagnostico_clinico' => $this->nulo($hoja4['diagnostico'] ?? null),
            'cie11_codigo' => $this->texto($hoja4['cie11_codigo'] ?? null, 20),
            'cie11_titulo' => $this->texto($hoja4['cie11_titulo'] ?? null, 500),
            'dictamen' => $datos['aptitud'],
            'restricciones' => $datos['aptitud'] === 'apto_con_restricciones' ? $this->nulo($hoja4['restricciones'] ?? null) : null,
            'recomendaciones' => $this->nulo($hoja4['recomendaciones'] ?? null),
            'plan_intervencion' => $this->nulo($hoja4['plan_intervencion'] ?? null),
            'derivaciones_especialista' => $this->nulo($hoja4['derivaciones_especialista'] ?? null),
            'observaciones' => $this->nulo($hoja4['observaciones'] ?? null),
            'dictamen_justificacion' => $this->nulo($hoja4['dictamen_final'] ?? null),
            'vigencia_hasta' => $this->vigencia($hoja4['proximo_seguimiento'] ?? null, $dp['fecha_evaluacion']),
        ];
    }

    private function poblarHijas(ValoracionOcupacional $valoracion, array $datos, DatosLaboral $laboral, bool $esActual): void
    {
        $vid = $valoracion->id;
        $paciente = PacientesClinicaTrabajo::findOrFail($datos['paciente_id']);
        $dp = $datos['datos_puesto'] ?? [];
        $er = $datos['exposicion_riesgos'] ?? [];
        $an = $datos['antecedentes'] ?? [];
        $ce = $datos['clinico_examen'] ?? [];

        DB::table('datos_puesto')->insert([
            'valoracion_id' => $vid,
            'antiguedad_empresa' => $this->texto($dp['antiguedad_empresa'] ?? null, 50),
            'riesgos_identificados' => $this->nulo($dp['riesgos_identificados'] ?? null),
            'descripcion_puesto' => $this->nulo($dp['descripcion_puesto'] ?? null),
        ]);

        $this->guardarExposicion($vid, $er);
        $this->guardarAntecedentes($vid, $an);
        $this->guardarClinicoExamen($vid, $ce);
        $this->guardarPruebas($vid, $ce['pruebas_complementarias'] ?? []);
        $this->guardarHistoriaPaciente($paciente, $vid, $er, $an, $esActual);
        if ($esActual) {
            $this->actualizarPaciente($paciente, $dp, $an);
            $this->actualizarLaboral($laboral, $dp);
        }
    }

    private function borrarHijas(int $valoracionId): void
    {
        foreach (self::TABLAS_HIJAS as $tabla) {
            DB::table($tabla)->where('valoracion_id', $valoracionId)->delete();
        }
    }

    
    /** True si esta ficha es la más reciente del paciente. */
    private function esUltimaFicha(ValoracionOcupacional $valoracion): bool
    {
        return !ValoracionOcupacional::where('paciente_id', $valoracion->paciente_id)
            ->where('id', '!=', $valoracion->id)
            ->where(function ($q) use ($valoracion) {
                $q->where('fecha_valoracion', '>', $valoracion->fecha_valoracion)
                    ->orWhere(function ($q2) use ($valoracion) {
                        $q2->where('fecha_valoracion', $valoracion->fecha_valoracion)
                            ->where('id', '>', $valoracion->id);
                    });
            })
            ->exists();
    }

    // ===================================================================
    // Signos vitales, antropometría y clasificaciones
    // ===================================================================

    private function signosVitales(array $dp, array $ce): array
    {
        $h1 = $dp['signos_vitales'] ?? [];
        $h31 = $ce['signos_vitales'] ?? [];

        $ta = $this->elegir($h31['ta'] ?? null, $h1['presion_arterial'] ?? null);
        [$sistolica, $diastolica] = $this->parseTA($ta);

        return [
            'ta_sistolica' => $sistolica,
            'ta_diastolica' => $diastolica,
            'fc' => $this->entero($this->elegir($h31['fc'] ?? null, $h1['frecuencia_cardiaca'] ?? null)),
            'fr' => $this->entero($this->elegir($h31['fr'] ?? null, $h1['frecuencia_respiratoria'] ?? null)),
            'spo2' => $this->decimal($this->elegir($h31['saturacion'] ?? null, $h1['saturacion_oxigeno'] ?? null)),
            'temperatura_c' => $this->decimal($this->elegir($h31['temperatura'] ?? null, $h1['temperatura'] ?? null)),
            'glicemia' => $this->decimal($this->elegir($h31['glucosa'] ?? null, $h1['glucosa'] ?? null)),
        ];
    }

    private function antropometria(array $ce): array
    {
        $a = $ce['antropometria'] ?? [];
        $peso = $this->decimal($a['peso'] ?? null);
        $talla = $this->decimal($a['talla'] ?? null); // cm
        $imc = ($peso && $talla) ? round($peso / (($talla / 100) ** 2), 2) : null;

        return [
            'peso_kg' => $peso,
            'estatura_cm' => $talla,
            'talla_abdomen_cm' => $this->decimal($a['perimetro_cintura'] ?? null),
            'perimetro_cadera_cm' => $this->decimal($a['perimetro_cadera'] ?? null),
            'imc' => $imc,
            'clasificacion' => $imc === null ? null : $this->clasificarImc($imc),
            'complexion_fisica' => $this->texto($a['complexion_fisica'] ?? null, 50),
        ];
    }

    private function parseTA(?string $ta): array
    {
        if (!$ta || !preg_match('/(\d{2,3})\s*\/\s*(\d{2,3})/', $ta, $m)) {
            return [null, null];
        }
        return [(int) $m[1], (int) $m[2]];
    }

    private function clasificarImc(float $imc): string
    {
        return match (true) {
            $imc < 18.5 => 'Bajo peso',
            $imc < 25 => 'Normal',
            $imc < 30 => 'Sobrepeso',
            $imc < 35 => 'Obesidad I',
            $imc < 40 => 'Obesidad II',
            default => 'Obesidad III',
        };
    }

    // ===================================================================
    // Tablas ligadas a la valoración
    // ===================================================================

    private function guardarExposicion(int $vid, array $er): void
    {
        $agentes = $er['agentes'] ?? [];
        $fila = ['valoracion_id' => $vid, 'otros' => $this->nulo($agentes['otros'] ?? null)];
        foreach (self::AGENTES as $a) {
            $fila[$a] = (int) ($agentes[$a] ?? false);
        }
        DB::table('agentes_exposicion')->insert($fila);

        $cr = $er['condiciones_riesgo'] ?? [];
        $fila = ['valoracion_id' => $vid];
        foreach (self::CONDICIONES as $clave => $columna) {
            $fila[$columna] = (int) ($cr[$clave] ?? false);
        }
        DB::table('condiciones_riesgo')->insert($fila);

        $oc = $er['otras_condiciones'] ?? [];
        $fila = [
            'valoracion_id' => $vid,
            'realiza_generalmente' => $this->postura($oc['realiza_generalmente'] ?? null),
        ];
        foreach (self::EXIGENCIAS as $e) {
            $fila[$e] = (int) ($oc[$e] ?? false);
        }
        DB::table('otras_condiciones_trabajo')->insert($fila);
    }

    private function guardarAntecedentes(int $vid, array $an): void
    {
        foreach ($an['accidentes'] ?? [] as $acc) {
            if ($this->vacio($acc)) {
                continue;
            }
            DB::table('antecedentes_accidentes')->insert([
                'valoracion_id' => $vid,
                'fecha' => $this->nulo($acc['fecha'] ?? null),
                'empresa' => $this->texto($acc['empresa'] ?? null, 200),
                'tipo' => $this->texto($acc['tipo'] ?? null, 100),
                'parte_cuerpo' => $this->texto($acc['parte_cuerpo'] ?? null, 100),
                'dias_incapacidad' => $this->entero($acc['dias_incapacidad'] ?? null),
                'secuelas' => $this->nulo($acc['secuelas'] ?? null),
            ]);
        }

        $h = $an['heredo_familiares'] ?? [];
        $fila = ['valoracion_id' => $vid, 'otros' => $this->nulo($h['otros'] ?? null)];
        foreach (self::HEREDO as $k) {
            $fila["{$k}_presente"] = (int) ($h[$k]['presente'] ?? false);
            $fila["{$k}_parentesco"] = $this->texto($h[$k]['parentesco'] ?? null, 50);
        }
        DB::table('antecedentes_heredofamiliares')->insert($fila);

        $np = $an['no_patologicos'] ?? [];
        DB::table('antecedentes_no_patologicos')->insert([
            'valoracion_id' => $vid,
            'tabaquismo' => $this->nulo($np['tabaquismo'] ?? null),
            'tabaquismo_detalles' => $this->nulo($np['tabaquismo_detalles'] ?? null),
            'alcoholismo' => $this->nulo($np['alcoholismo'] ?? null),
            'alcoholismo_detalles' => $this->nulo($np['alcoholismo_detalles'] ?? null),
            'drogas_no' => (int) ($np['drogas_no'] ?? false),
            'drogas_si' => (int) ($np['drogas_si'] ?? false),
            'drogas_detalles' => $this->nulo($np['drogas_detalles'] ?? null),
            'actividad_fisica' => $this->nulo($np['actividad_fisica'] ?? null),
            'estres' => $this->nulo($np['estres'] ?? null),
            'estres_detalles' => $this->nulo($np['estres_detalles'] ?? null),
        ]);

        $g = $an['gineco'] ?? [];
        $u = $an['urologo'] ?? [];
        $genero = $an['genero'] ?? null;
        DB::table('antecedentes_gineco')->insert([
            'valoracion_id' => $vid,
            'genero' => in_array($genero, ['M', 'F', 'Otro'], true) ? $genero : null,
            'menarquia' => $this->entero($g['menarquia'] ?? null),
            'ciclo_dias' => $this->entero($g['ciclo_dias'] ?? null),
            'duracion_dias' => $this->entero($g['duracion_dias'] ?? null),
            'gestaciones' => $this->entero($g['gestaciones'] ?? null),
            'partos' => $this->entero($g['partos'] ?? null),
            'abortos' => $this->entero($g['abortos'] ?? null),
            'desorden_menstrual' => (int) ($g['desorden_menstrual'] ?? false),
            'dismenorrea' => (int) ($g['dismenorrea'] ?? false),
            'anticonceptivos' => (int) ($g['anticonceptivos'] ?? false),
            'disfuncion_erectil' => (int) ($u['disfuncion_erectil'] ?? false),
            'infertilidad' => (int) ($u['infertilidad'] ?? false),
            'problemas_prostaticos' => (int) ($u['problemas_prostaticos'] ?? false),
            'infecciones_urinarias' => (int) ($u['infecciones_urinarias'] ?? false),
            'otros_urologo' => $this->nulo($u['otros'] ?? null),
        ]);

        $pat = $an['patologicos'] ?? [];
        $fila = ['valoracion_id' => $vid, 'otros' => $this->nulo($pat['otros'] ?? null)];
        foreach (self::PATOLOGICOS as $k) {
            $fila["{$k}_presente"] = (int) ($pat[$k]['presente'] ?? false);
            if (in_array($k, self::PATOLOGICOS_CON_OBS, true)) {
                $fila["{$k}_obs"] = $this->nulo($pat[$k]['observaciones'] ?? null);
            }
        }
        DB::table('antecedentes_patologicos')->insert($fila);
    }

    private function guardarClinicoExamen(int $vid, array $ce): void
    {
        $fila = [
            'valoracion_id' => $vid,
            'comorbilidades' => json_encode($this->comorbilidades($ce['comorbilidades'] ?? [])),
            'medicamentos' => $this->nulo($ce['medicamentos'] ?? null),
            'alergias' => $this->nulo($ce['alergias'] ?? null),
        ];

        $sistemas = $ce['sistemas'] ?? [];
        foreach (self::SISTEMAS as $clave => $prefijo) {
            $s = $sistemas[$clave] ?? [];
            $fila["{$prefijo}_interrogatorio"] = $this->nulo($s['interrogatorio'] ?? null);
            $fila["{$prefijo}_exploracion"] = $this->nulo($s['exploracion'] ?? null);
            $fila["{$prefijo}_notas"] = $this->nulo($s['notas'] ?? null);
        }

        foreach (self::CAMPOS_SISTEMA as $clave => $campos) {
            $s = $sistemas[$clave] ?? [];
            foreach ($campos as $campo => $columna) {
                $valor = $s[$campo] ?? null;
                $fila[$columna] = is_bool($valor) ? (int) $valor : $this->nulo($valor);
            }
        }

        DB::table('clinico_examen')->insert($fila);
    }

    private function guardarPruebas(int $vid, array $pruebas): void
    {
        foreach ($pruebas as $p) {
            if ($this->nulo($p['tipo'] ?? null) === null) {
                continue;
            }
            DB::table('pruebas_complementarias')->insert([
                'valoracion_id' => $vid,
                'tipo' => $this->texto($p['tipo'], 150),
                'fecha' => $this->nulo($p['fecha'] ?? null),
                'resultado' => $this->nulo($p['resultado'] ?? null),
            ]);
        }
    }

    // ===================================================================
    // Datos a nivel paciente y registros de eventos
    // ===================================================================

    /** Solo reemplaza el historial del paciente si el formulario trae algún dato. */
    private function tieneHistorial(array $er, array $an): bool
    {
        return collect($er['empresas_anteriores'] ?? [])->contains(fn ($e) => $this->nulo($e['nombre'] ?? null) !== null)
            || collect($an['alergias'] ?? [])->contains(fn ($a) => $this->nulo($a['sustancia'] ?? null) !== null)
            || collect($an['vacunas'] ?? [])->contains(fn ($v) => is_array($v) && !empty($v['presente']))
            || $this->nulo($an['vacunas']['otros'] ?? null) !== null;
    }

    private function guardarHistoriaPaciente(PacientesClinicaTrabajo $paciente, int $vid, array $er, array $an, bool $esActual): void
    {
        $pid = $paciente->id;
        if ($esActual && $this->tieneHistorial($er, $an)) {
            // Empleos anteriores: estado actual del paciente
            DB::table('paciente_empleos_anteriores')->where('paciente_id', $pid)->delete();
            $orden = 1;
            foreach ($er['empresas_anteriores'] ?? [] as $emp) {
                if ($this->nulo($emp['nombre'] ?? null) === null) {
                    continue;
                }
                DB::table('paciente_empleos_anteriores')->insert([
                    'paciente_id' => $pid,
                    'empresa_nombre' => $this->texto($emp['nombre'], 200),
                    'puesto_nombre' => $this->texto($emp['puesto'] ?? null, 150),
                    'antiguedad_texto' => $this->texto($emp['antiguedad'] ?? null, 100),
                    'orden' => $orden++,
                ]);
            }

            // Vacunas: estado actual del paciente
            DB::table('paciente_vacunas')->where('paciente_id', $pid)->delete();
            $vac = $an['vacunas'] ?? [];
            foreach (self::VACUNAS as $v) {
                if (empty($vac[$v]['presente'])) {
                    continue;
                }
                DB::table('paciente_vacunas')->insert([
                    'paciente_id' => $pid,
                    'vacuna' => $v,
                    'fecha_aplicacion' => $this->fechaMes($vac[$v]['fecha'] ?? null),
                ]);
            }
            if ($this->nulo($vac['otros'] ?? null) !== null) {
                DB::table('paciente_vacunas')->insert([
                    'paciente_id' => $pid,
                    'vacuna' => 'otra',
                    'vacuna_otra_nombre' => $this->texto($vac['otros'], 100),
                ]);
            }

            // Alergias: estado actual del paciente
            DB::table('paciente_alergias')->where('paciente_id', $pid)->delete();
            foreach ($an['alergias'] ?? [] as $al) {
                if ($this->nulo($al['sustancia'] ?? null) === null) {
                    continue;
                }
                $reaccion = $al['tipo_reaccion'] ?? null;
                $hosp = $al['hospitalizacion'] ?? null;
                DB::table('paciente_alergias')->insert([
                    'paciente_id' => $pid,
                    'sustancia' => $this->texto($al['sustancia'], 150),
                    'tipo_reaccion' => in_array($reaccion, self::TIPOS_REACCION, true) ? $reaccion : null,
                    'hospitalizacion' => in_array($hosp, ['si', true, 1], true) ? 1 : 0,
                ]);
            }

        }

        // Incapacidades de los últimos 3 meses: eventos ligados a la valoración
        if (!empty($an['incapacidades_3m']['tiene'])) {
            foreach ($an['incapacidades_3m']['items'] ?? [] as $inc) {
                if ($this->nulo($inc['motivo'] ?? null) === null) {
                    continue;
                }
                DB::table('incapacidades_salud')->insert([
                    'paciente_id' => $pid,
                    'valoracion_id' => $vid,
                    'motivo' => $this->texto($inc['motivo'], 255),
                    'numero_dias' => $this->entero($inc['dias'] ?? null),
                ]);
            }
        }

        // Enfermedades profesionales: eventos ligados a la valoración
        foreach ($an['enfermedades_laborales'] ?? [] as $enf) {
            if ($this->nulo($enf['diagnostico'] ?? null) === null) {
                continue;
            }
            DB::table('enfermedades_profesionales_reportadas')->insert([
                'paciente_id' => $pid,
                'valoracion_id' => $vid,
                'diagnostico' => $this->texto($enf['diagnostico'], 255),
                'fecha_diagnostico' => $this->fechaMes($enf['fecha'] ?? null),
            ]);
        }
    }

    private function actualizarPaciente(PacientesClinicaTrabajo $paciente, array $dp, array $an): void
    {
        [$tipoSangre, $rh] = $this->separarSangre($dp['tipo_sanguineo'] ?? null);

        $alergias = collect($an['alergias'] ?? [])
            ->filter(fn ($a) => $this->nulo($a['sustancia'] ?? null) !== null)
            ->map(fn ($a) => $a['sustancia'] . (!empty($a['tipo_reaccion']) ? " ({$a['tipo_reaccion']})" : ''))
            ->implode('; ');

        $transfusiones = collect($an['transfusiones'] ?? [])
            ->filter(fn ($t) => $this->nulo($t['motivo'] ?? null) !== null);

        $cirugias = collect($an['cirugias_accidentes_no_laborales'] ?? [])
            ->pluck('descripcion')
            ->filter(fn ($d) => $this->nulo($d) !== null)
            ->implode('; ');

        $datos = [
            'nombre' => $this->nulo($dp['nombre'] ?? null),
            'fecha_nacimiento' => $this->nulo($dp['fecha_nacimiento'] ?? null),
            'genero' => $this->generoPaciente($dp['genero'] ?? null),
            'estado_civil' => $this->nulo($dp['estado_civil'] ?? null),
            'escolaridad' => $this->nulo($dp['escolaridad'] ?? null),
            'lugar_nacimiento' => $this->nulo($dp['lugar_nacimiento'] ?? null),
            'cedula_profesional' => $this->nulo($dp['cedula'] ?? null),
            'tipo_sangre' => $tipoSangre,
            'rh' => $rh,
            'telefono' => $this->nulo($dp['telefono'] ?? null),
            'celular' => $this->nulo($dp['celular'] ?? null),
            'email' => $this->nulo($dp['email'] ?? null),
            'direccion' => $this->nulo($dp['direccion'] ?? null),
            'contacto_emergencia' => $this->nulo($dp['emergencia_nombre'] ?? null),
            'relacion_contacto_emergencia' => $this->nulo($dp['emergencia_relacion'] ?? null),
            'telefono_emergencia' => $this->nulo($dp['emergencia_telefono'] ?? null),
            'fecha_ultima_consulta_medica' => $this->nulo($dp['ultima_consulta_fecha'] ?? null),
            'motivo_ultima_consulta_medica' => $this->nulo($dp['ultima_consulta_motivo'] ?? null),
            'edad_inicio_vida_laboral' => $this->entero($dp['edad_inicio_vida_laboral'] ?? null),
            'alergias' => $alergias !== '' ? $alergias : null,
            'transfusion_sanguinea' => $transfusiones->isNotEmpty(),
            'transfusion_detalle' => $transfusiones->isNotEmpty()
                ? $transfusiones->map(fn ($t) => trim($t['motivo'] . ' ' . ($t['fecha'] ?? '')))->implode('; ')
                : null,
            'antecedentes_quirurgicos' => $cirugias !== '' ? $cirugias : null,
        ];

        $datos['nombre'] = $datos['nombre'] ?? $paciente->nombre;
        if ($transfusiones->isEmpty()) {
            unset($datos['transfusion_sanguinea'], $datos['transfusion_detalle']);
        }
        $paciente->update(array_filter($datos, fn ($v) => $v !== null));
    }

    private function actualizarLaboral(DatosLaboral $laboral, array $dp): void
    {
        $datos = [
            'turno' => $this->nulo($dp['jornada'] ?? null),
            'tipo_contrato' => $this->nulo($dp['tipo_contrato'] ?? null),
            'modalidad_trabajo' => $this->nulo($dp['modalidad_trabajo'] ?? null),
            'categoria_laboral' => $this->nulo($dp['categoria_laboral'] ?? null),
        ];

        $laboral->update(array_filter($datos, fn ($v) => $v !== null));
    }

    // ===================================================================
    // Folio, vigencia y utilidades
    // ===================================================================

    /** Folio sugerido para el formulario nuevo (sin reservarlo). */
    private function folioSugerido(): string
    {
        $prefijo = 'MED-' . now()->year . '-';

        $ultimo = ValoracionOcupacional::where('folio', 'like', $prefijo . '%')
            ->orderByDesc('folio')
            ->value('folio');

        $siguiente = $ultimo ? ((int) substr($ultimo, strlen($prefijo))) + 1 : 1;

        return $prefijo . str_pad((string) $siguiente, 4, '0', STR_PAD_LEFT);
    }

    /** Folio definitivo: se llama dentro de la transacción de crearFicha. */
    private function generarFolio(): string
    {
        $prefijo = 'MED-' . now()->year . '-';

        $ultimo = ValoracionOcupacional::where('folio', 'like', $prefijo . '%')
            ->lockForUpdate()
            ->orderByDesc('folio')
            ->value('folio');

        $siguiente = $ultimo ? ((int) substr($ultimo, strlen($prefijo))) + 1 : 1;

        return $prefijo . str_pad((string) $siguiente, 4, '0', STR_PAD_LEFT);
    }

    private function vigencia(?string $seguimiento, string $fecha): ?string
    {
        $meses = ['3m' => 3, '6m' => 6, '12m' => 12][$seguimiento] ?? null;

        return $meses ? Carbon::parse($fecha)->addMonths($meses)->toDateString() : null;
    }

    private function postura(?string $valor): ?string
    {
        $valor = $valor === 'de_pie' ? 'pie' : $valor;

        return in_array($valor, ['pie', 'sentado', 'alternando', 'movimiento_constante'], true) ? $valor : null;
    }

    private function comorbilidades(array $c): array
    {
        return array_filter($c, fn ($v) => $v === true || (is_string($v) && trim($v) !== ''));
    }

    private function generoPaciente(?string $genero): ?string
    {
        return ['M' => 'Hombre', 'F' => 'Mujer', 'Otro' => 'Otro'][$genero] ?? null;
    }

    private function separarSangre(?string $valor): array
    {
        if (!$valor || !preg_match('/^(AB|A|B|O)([+-])$/', $valor, $m)) {
            return [null, null];
        }
        return [$m[1], $m[2]];
    }

    private function elegir($principal, $respaldo)
    {
        return $this->nulo($principal) ?? $this->nulo($respaldo);
    }

    private function vacio(array $fila): bool
    {
        return collect($fila)->every(fn ($v) => $this->nulo($v) === null);
    }

    private function nulo($valor)
    {
        if ($valor === null || (is_string($valor) && trim($valor) === '')) {
            return null;
        }
        return $valor;
    }

    private function texto($valor, int $max): ?string
    {
        $valor = $this->nulo($valor);
        return $valor === null ? null : mb_substr(trim((string) $valor), 0, $max);
    }

    private function entero($valor): ?int
    {
        $valor = $this->nulo($valor);
        return $valor === null ? null : (int) $valor;
    }

    private function decimal($valor): ?float
    {
        $valor = $this->nulo($valor);
        return $valor === null ? null : (float) $valor;
    }

    /** Convierte 'YYYY-MM' (input month de la UI) a 'YYYY-MM-01'. */
    private function fechaMes($valor): ?string
    {
        $valor = $this->nulo($valor);
        if ($valor === null) {
            return null;
        }
        return strlen($valor) === 7 ? $valor . '-01' : $valor;
    }

        /** Arma la forma del formulario a partir de la ficha y sus tablas hijas. */
    private function formularioDesdeFicha(ValoracionOcupacional $f): array
    {
        $vid = $f->id;
        $p = $f->paciente;
        $puesto = $f->puestoTrabajo;
        $laboral = DatosLaboral::where('paciente_id', $f->paciente_id)->latest('id')->first();

        $dp = DB::table('datos_puesto')->where('valoracion_id', $vid)->first();
        $ae = DB::table('agentes_exposicion')->where('valoracion_id', $vid)->first();
        $cr = DB::table('condiciones_riesgo')->where('valoracion_id', $vid)->first();
        $oc = DB::table('otras_condiciones_trabajo')->where('valoracion_id', $vid)->first();
        $acc = DB::table('antecedentes_accidentes')->where('valoracion_id', $vid)->get();
        $hf = DB::table('antecedentes_heredofamiliares')->where('valoracion_id', $vid)->first();
        $np = DB::table('antecedentes_no_patologicos')->where('valoracion_id', $vid)->first();
        $gy = DB::table('antecedentes_gineco')->where('valoracion_id', $vid)->first();
        $pa = DB::table('antecedentes_patologicos')->where('valoracion_id', $vid)->first();
        $ce = DB::table('clinico_examen')->where('valoracion_id', $vid)->first();
        $pruebas = DB::table('pruebas_complementarias')->where('valoracion_id', $vid)->get();
        $enf = DB::table('enfermedades_profesionales_reportadas')->where('valoracion_id', $vid)->get();
        $inc = DB::table('incapacidades_salud')->where('valoracion_id', $vid)->get();
        $empleos = DB::table('paciente_empleos_anteriores')->where('paciente_id', $f->paciente_id)->orderBy('orden')->get();
        $vacunas = DB::table('paciente_vacunas')->where('paciente_id', $f->paciente_id)->get();
        $alergias = DB::table('paciente_alergias')->where('paciente_id', $f->paciente_id)->get();

        $fecha = fn ($v) => $v ? Carbon::parse($v)->toDateString() : '';
        $mes = fn ($v) => $v ? substr((string) $v, 0, 7) : '';
        $pv = fn (string $campo) => $p?->{$campo} ?? '';
        $genero = ['Hombre' => 'M', 'Mujer' => 'F', 'Otro' => 'Otro'][$p?->genero] ?? '';
        $tipoSangre = ($p?->tipo_sangre && $p?->rh) ? $p->tipo_sangre . $p->rh : '';
        $edad = $p?->fecha_nacimiento ? Carbon::parse($p->fecha_nacimiento)->age : '';
        $ta = ($f->ta_sistolica && $f->ta_diastolica) ? "{$f->ta_sistolica}/{$f->ta_diastolica}" : '';

        $estadoCivil = $this->normalizarOpcion($p?->estado_civil, [
            'casad' => 'casado', 'solter' => 'soltero', 'divorci' => 'divorciado',
            'viud' => 'viudo', 'union' => 'union_libre', 'unión' => 'union_libre',
        ]);
        $escolaridad = $this->normalizarOpcion($p?->escolaridad, [
            'universidad' => 'profesional', 'licenciatura' => 'profesional', 'profesional' => 'profesional',
            'tecnic' => 'tecnico', 'técnic' => 'tecnico', 'secundaria' => 'secundaria',
            'primaria' => 'primaria', 'posgrado' => 'posgrado', 'maestr' => 'posgrado',
            'doctor' => 'posgrado', 'ninguna' => 'ninguna',
        ]);
        $tipoContrato = $this->normalizarOpcion($laboral?->tipo_contrato, [
            'indefinid' => 'indefinido', 'indeterminad' => 'indefinido', 'planta' => 'indefinido',
            'temporal' => 'temporal', 'fijo' => 'temporal', 'determinad' => 'temporal',
            'proyecto' => 'proyecto', 'obra' => 'proyecto', 'practicante' => 'practicante',
        ]);
        $antiguedadEmpresa = $dp?->antiguedad_empresa ?? $this->antiguedadTexto($laboral?->fecha_ingreso_empresa);

        $proximo = '';
        if ($f->vigencia_hasta) {
            $meses = (int) Carbon::parse($f->fecha_valoracion)->diffInMonths(Carbon::parse($f->vigencia_hasta));
            $proximo = in_array($meses, [3, 6, 12], true) ? $meses . 'm' : '';
        }

        // Riesgos
        $agentes = ['otros' => $ae?->otros ?? ''];
        foreach (self::AGENTES as $a) {
            $agentes[$a] = (bool) ($ae?->{$a} ?? false);
        }

        $condiciones = [];
        foreach (self::CONDICIONES as $clave => $columna) {
            $condiciones[$clave] = (bool) ($cr?->{$columna} ?? false);
        }

        $otras = ['realiza_generalmente' => $oc?->realiza_generalmente ?? ''];
        foreach (self::EXIGENCIAS as $e) {
            $otras[$e] = (bool) ($oc?->{$e} ?? false);
        }

        // Antecedentes
        $heredo = ['otros' => $hf?->otros ?? ''];
        foreach (self::HEREDO as $k) {
            $heredo[$k] = [
                'presente' => (bool) ($hf?->{"{$k}_presente"} ?? false),
                'parentesco' => $hf?->{"{$k}_parentesco"} ?? '',
            ];
        }

        $patologicos = ['otros' => $pa?->otros ?? ''];
        foreach (self::PATOLOGICOS as $k) {
            $patologicos[$k] = [
                'presente' => (bool) ($pa?->{"{$k}_presente"} ?? false),
                'observaciones' => in_array($k, self::PATOLOGICOS_CON_OBS, true) ? ($pa?->{"{$k}_obs"} ?? '') : '',
            ];
        }

        return [
            'datos_puesto' => [
                'folio' => $f->folio,
                'tipo_evaluacion' => $f->tipo,
                'fecha_evaluacion' => $fecha($f->fecha_valoracion),
                'hora_evaluacion' => $f->hora_valoracion ? substr((string) $f->hora_valoracion, 0, 5) : '',
                'medico_evaluador' => $f->medico?->nombre ?? '',
                'nombre' => $pv('nombre'),
                'fecha_nacimiento' => $fecha($p?->fecha_nacimiento),
                'edad' => $edad,
                'genero' => $genero,
                'estado_civil' => $estadoCivil,
                'escolaridad' => $escolaridad,
                'edad_inicio_vida_laboral' => $pv('edad_inicio_vida_laboral'),
                'cedula' => $pv('cedula_profesional'),
                'lugar_nacimiento' => $pv('lugar_nacimiento'),
                'tipo_sanguineo' => $tipoSangre,
                'telefono' => $pv('telefono'),
                'celular' => $pv('celular'),
                'email' => $pv('email'),
                'direccion' => $pv('direccion'),
                'emergencia_nombre' => $pv('contacto_emergencia'),
                'emergencia_telefono' => $pv('telefono_emergencia'),
                'emergencia_relacion' => $pv('relacion_contacto_emergencia'),
                'ultima_consulta_fecha' => $fecha($p?->fecha_ultima_consulta_medica),
                'ultima_consulta_motivo' => $pv('motivo_ultima_consulta_medica'),
                'numero_empleado' => $laboral?->numero_empleado ?? '',
                'empresa' => $f->empresaCliente?->razon_social ?? '',
                'departamento' => $puesto?->departamento ?? '',
                'puesto' => $f->puesto_nombre_snapshot ?? '',
                'antiguedad' => $f->anos_puesto ?? '',
                'antiguedad_empresa' => $antiguedadEmpresa,
                'tipo_contrato' => $tipoContrato,
                'jornada' => $laboral?->turno ?? '',
                'modalidad_trabajo' => $laboral?->modalidad_trabajo ?? '',
                'categoria_laboral' => $laboral?->categoria_laboral ?? '',
                'descripcion_puesto' => $dp?->descripcion_puesto ?? $puesto?->descripcion_actividades ?? '',
                'riesgos_identificados' => $dp?->riesgos_identificados ?? '',
                'signos_vitales' => [
                    'frecuencia_cardiaca' => $f->fc ?? '',
                    'frecuencia_respiratoria' => $f->fr ?? '',
                    'presion_arterial' => $ta,
                    'temperatura' => $f->temperatura_c ?? '',
                    'saturacion_oxigeno' => $f->spo2 ?? '',
                    'glucosa' => $f->glicemia ?? '',
                ],
            ],

            'exposicion_riesgos' => [
                'empresas_anteriores' => $empleos->map(fn ($e) => [
                    'nombre' => $e->empresa_nombre,
                    'puesto' => $e->puesto_nombre ?? '',
                    'antiguedad' => $e->antiguedad_texto ?? '',
                ])->values()->all(),
                'agentes' => $agentes,
                'condiciones_riesgo' => $condiciones,
                'otras_condiciones' => $otras,
            ],

            'antecedentes' => [
                'accidentes' => $acc->map(fn ($a) => [
                    'fecha' => $fecha($a->fecha),
                    'empresa' => $a->empresa ?? '',
                    'tipo' => $a->tipo ?? '',
                    'parte_cuerpo' => $a->parte_cuerpo ?? '',
                    'dias_incapacidad' => $a->dias_incapacidad ?? '',
                    'secuelas' => $a->secuelas ?? '',
                ])->values()->all(),
                'enfermedades_laborales' => $enf->map(fn ($e) => [
                    'diagnostico' => $e->diagnostico,
                    'fecha' => $mes($e->fecha_diagnostico),
                ])->values()->all(),
                'incapacidades_3m' => [
                    'tiene' => $inc->isNotEmpty(),
                    'items' => $inc->map(fn ($i) => [
                        'motivo' => $i->motivo,
                        'dias' => $i->numero_dias ?? '',
                    ])->values()->all(),
                ],
                'vacunas' => $this->vacunasDesdeBD($vacunas),
                'cirugias_accidentes_no_laborales' => collect(explode('; ', (string) $pv('antecedentes_quirurgicos')))
                    ->filter()
                    ->map(fn ($d) => ['descripcion' => $d])
                    ->values()
                    ->all(),
                'alergias' => $alergias->map(fn ($a) => [
                    'sustancia' => $a->sustancia,
                    'tipo_reaccion' => $a->tipo_reaccion ?? '',
                    'hospitalizacion' => $a->hospitalizacion ? 'si' : 'no',
                ])->values()->all(),
                'transfusiones' => $p?->transfusion_sanguinea
                    ? [['motivo' => $p->transfusion_detalle ?? '', 'fecha' => '']]
                    : [],
                'heredo_familiares' => $heredo,
                'no_patologicos' => [
                    'tabaquismo' => $np?->tabaquismo ?? '',
                    'tabaquismo_detalles' => $np?->tabaquismo_detalles ?? '',
                    'alcoholismo' => $np?->alcoholismo ?? '',
                    'alcoholismo_detalles' => $np?->alcoholismo_detalles ?? '',
                    'drogas_no' => (bool) ($np?->drogas_no ?? false),
                    'drogas_si' => (bool) ($np?->drogas_si ?? false),
                    'drogas_detalles' => $np?->drogas_detalles ?? '',
                    'actividad_fisica' => $np?->actividad_fisica ?? '',
                    'estres' => $np?->estres ?? '',
                    'estres_detalles' => $np?->estres_detalles ?? '',
                ],
                'genero' => $gy?->genero ?? '',
                'gineco' => [
                    'menarquia' => $gy?->menarquia ?? '',
                    'ciclo_dias' => $gy?->ciclo_dias ?? '',
                    'duracion_dias' => $gy?->duracion_dias ?? '',
                    'desorden_menstrual' => (bool) ($gy?->desorden_menstrual ?? false),
                    'dismenorrea' => (bool) ($gy?->dismenorrea ?? false),
                    'gestaciones' => $gy?->gestaciones ?? '',
                    'partos' => $gy?->partos ?? '',
                    'abortos' => $gy?->abortos ?? '',
                    'anticonceptivos' => (bool) ($gy?->anticonceptivos ?? false),
                ],
                'urologo' => [
                    'disfuncion_erectil' => (bool) ($gy?->disfuncion_erectil ?? false),
                    'infertilidad' => (bool) ($gy?->infertilidad ?? false),
                    'problemas_prostaticos' => (bool) ($gy?->problemas_prostaticos ?? false),
                    'infecciones_urinarias' => (bool) ($gy?->infecciones_urinarias ?? false),
                    'otros' => $gy?->otros_urologo ?? '',
                ],
                'patologicos' => $patologicos,
            ],

            'clinico_examen' => [
                'signos_vitales' => [
                    'fc' => $f->fc ?? '',
                    'fr' => $f->fr ?? '',
                    'ta' => $ta,
                    'temperatura' => $f->temperatura_c ?? '',
                    'saturacion' => $f->spo2 ?? '',
                    'glucosa' => $f->glicemia ?? '',
                ],
                'antropometria' => [
                    'peso' => $f->peso_kg ?? '',
                    'talla' => $f->estatura_cm ?? '',
                    'imc' => $f->imc ?? '',
                    'perimetro_cintura' => $f->talla_abdomen_cm ?? '',
                    'perimetro_cadera' => $f->perimetro_cadera_cm ?? '',
                    'complexion_fisica' => $f->complexion_fisica ?? '',
                    'estado_nutricional' => $f->estado_nutricional ?? '',
                ],
                'comorbilidades' => json_decode($ce?->comorbilidades ?? '[]', true) ?: [],
                'sistemas' => $this->sistemasDesdeBD($ce),
                'medicamentos' => $ce?->medicamentos ?? '',
                'alergias' => $ce?->alergias ?? '',
                'pruebas_complementarias' => $pruebas->map(fn ($x) => [
                    'tipo' => $x->tipo,
                    'fecha' => $fecha($x->fecha),
                    'resultado' => $x->resultado ?? '',
                ])->values()->all(),
            ],

            'aptitud' => [
                'aptitud' => $f->dictamen,
                'hallazgos_relevantes_aptitud' => $f->analisis_correlacion_riesgos ?? '',
                'diagnostico' => $f->diagnostico_clinico ?? '',
                'cie11_codigo' => $f->cie11_codigo ?? '',
                'cie11_titulo' => $f->cie11_titulo ?? '',
                'cie11_termino' => $f->cie11_titulo ?? '',
                'restricciones' => $f->restricciones ?? '',
                'recomendaciones' => $f->recomendaciones ?? '',
                'plan_intervencion' => $f->plan_intervencion ?? '',
                'derivaciones_especialista' => $f->derivaciones_especialista ?? '',
                'observaciones' => $f->observaciones ?? '',
                'dictamen_final' => $f->dictamen_justificacion ?? '',
                'proximo_seguimiento' => $proximo,
                'dictamen_confirmado' => true,
            ],
        ];
    }

    /** Vacunas del paciente en la forma de la UI (presente, fecha YYYY-MM). */
    private function vacunasDesdeBD($filas): array
    {
        $vac = [];
        foreach (self::VACUNAS as $v) {
            $vac[$v] = ['presente' => false, 'fecha' => ''];
        }

        foreach ($filas as $fila) {
            if ($fila->vacuna === 'otra') {
                $vac['otros'] = $fila->vacuna_otra_nombre ?? '';
                continue;
            }
            $vac[$fila->vacuna] = [
                'presente' => true,
                'fecha' => $fila->fecha_aplicacion ? substr((string) $fila->fecha_aplicacion, 0, 7) : '',
            ];
        }

        return $vac;
    }

    /** Sistemas de la exploración física en la forma de la UI. */
    private function sistemasDesdeBD(?object $ce): array
    {
        $sistemas = [];
        foreach (self::SISTEMAS as $clave => $prefijo) {
            $sistemas[$clave] = [
                'interrogatorio' => $ce?->{"{$prefijo}_interrogatorio"} ?? '',
                'exploracion' => $ce?->{"{$prefijo}_exploracion"} ?? '',
                'notas' => $ce?->{"{$prefijo}_notas"} ?? '',
            ];
        }

        foreach (self::CAMPOS_SISTEMA as $clave => $campos) {
            foreach ($campos as $campo => $columna) {
                $valor = $ce?->{$columna} ?? null;
                $sistemas[$clave][$campo] = in_array($campo, self::CAMPOS_BOOLEANOS, true)
                    ? (bool) $valor
                    : ($valor ?? '');
            }
        }

        return $sistemas;
    }

        /** Devuelve la opción del select cuyo patrón aparece en el valor guardado; vacío si no hay coincidencia. */
    private function normalizarOpcion(?string $valor, array $mapa): string
    {
        $val = mb_strtolower(trim((string) $valor));
        if ($val === '') {
            return '';
        }

        foreach ($mapa as $patron => $opcion) {
            if (str_contains($val, $patron)) {
                return $opcion;
            }
        }

        return '';
    }

    /** Antigüedad en texto desde la fecha de ingreso, igual que obtenerPacientes(). */
    private function antiguedadTexto($fecha): string
    {
        if (!$fecha) {
            return '';
        }

        $inicio = Carbon::parse($fecha);
        $anos = (int) $inicio->diffInYears(now());
        $meses = (int) $inicio->copy()->addYears($anos)->diffInMonths(now());

        if ($anos > 0 && $meses > 0) {
            return "{$anos} años, {$meses} meses";
        }

        return $anos > 0 ? "{$anos} años" : "{$meses} meses";
    }
}