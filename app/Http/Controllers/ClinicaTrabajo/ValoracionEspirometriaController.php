<?php

namespace App\Http\Controllers\ClinicaTrabajo;

use Illuminate\Http\Request;
use App\Services\ClinicaTrabajo\IAServiceEspirometria;
use App\Http\Controllers\Controller;
use App\Models\ClinicaTrabajo\ValoracionEspirometria;
use App\Models\ClinicaTrabajo\EspirometriaIaMensaje;
use App\Models\ClinicaTrabajo\PacientesClinicaTrabajo;
use App\Models\Medico;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Empresa;
use App\Services\ClinicaTrabajo\GraficasEspirometria;

class ValoracionEspirometriaController extends Controller
{
    /** Relaciones que se cargan en las respuestas. */
    private const RELACIONES = ['paciente', 'empresa', 'puesto', 'medico'];

    /**
     * GET /clinica-trabajo/espirometria
     * Lista paginada con filtros opcionales.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ValoracionEspirometria::query();

        if ($request->filled('empresa_id')) {
            $query->where('empresa_cliente_id', $request->input('empresa_id'));
        }
        if ($request->filled('paciente_id')) {
            $query->where('paciente_id', $request->input('paciente_id'));
        }
        if ($request->filled('tipo')) {
            $query->where('tipo_evaluacion', $request->input('tipo'));
        }
        if ($request->filled('aptitud')) {
            $query->where('aptitud', $request->input('aptitud'));
        }
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $query->whereBetween('fecha_valoracion', [
                $request->input('fecha_desde'),
                $request->input('fecha_hasta'),
            ]);
        }

        $valoraciones = $query
            ->with(self::RELACIONES)
            ->orderByDesc('fecha_valoracion')
            ->orderByDesc('id')
            ->paginate(15);

        return response()->json($valoraciones);
    }

    /**
     * GET /clinica-trabajo/espirometria/{id}
     */
    public function show(string $id): JsonResponse
    {
        $valoracion = ValoracionEspirometria::with(self::RELACIONES)->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $valoracion,
        ]);
    }

    /**
     * POST /clinica-trabajo/espirometria
     * Crea una valoración. El médico se toma del usuario autenticado
     * (medicos.user_id) y el folio lo genera el backend.
     */
    public function store(Request $request): JsonResponse
    {
        $medico = $this->medicoAutenticado($request);
        if (!$medico) {
            return $this->sinMedico();
        }

       
        $validated = $request->validate($this->reglas(true));
        $validated['maniobras'] = $request->input('maniobras', []);

        $valoracion = $this->crearConFolio($validated, $medico->id);

        // Vincula los mensajes del chat de esta sesión con la valoración recién creada
        $sesionIa = $request->validate(['session_uuid' => 'nullable|string|max:100'])['session_uuid'] ?? null;
        if ($sesionIa !== null) {
            EspirometriaIaMensaje::where('session_uuid', $sesionIa)
                ->whereNull('valoracion_id')
                ->update(['valoracion_id' => $valoracion->id]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Valoración espirométrica creada exitosamente',
            'id' => $valoracion->id,
            'folio' => $valoracion->folio,
            'data' => $valoracion->load(self::RELACIONES),
        ], 201);
    }

    /**
     * PUT|PATCH /clinica-trabajo/espirometria/{valoracion}
     * Actualiza una valoración. No cambia el folio ni el médico que la creó.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $valoracion = ValoracionEspirometria::findOrFail($id);
        $validated = $request->validate($this->reglas(false));

        if ($request->has('maniobras')) {
            $validated['maniobras'] = $request->input('maniobras');
            $validated['num_maniobras'] = count($validated['maniobras'] ?? []) ?: null;
        }

        $valoracion->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Valoración espirométrica actualizada',
            'id' => $valoracion->id,
            'data' => $valoracion->fresh()->load(self::RELACIONES),
        ]);
    }

    /**
     * DELETE /clinica-trabajo/espirometria/{valoracion}
     * Borrado físico (la tabla no tiene deleted_at). Los mensajes de IA
     * asociados se eliminan en cascada por la FK.
     */
    public function destroy(string $id): JsonResponse
    {   
        $valoracion = ValoracionEspirometria::findOrFail($id);
        $valoracion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Valoración espirométrica eliminada',
        ]);
    }

    /**
     * GET /clinica-trabajo/espirometria/{valoracion}/imprimir
     * Datos del reporte: predichos, LLN, puntuación Z, porcentajes,
     * varianzas, calidad (sesión, FEV1 y FVC) y maniobras.
     */
    public function imprimir(string $id,GraficasEspirometria $graficas)
    {
        $valoracion = ValoracionEspirometria::with(['paciente', 'empresa', 'medico'])
            ->findOrFail($id);

        $maniobras = $valoracion->maniobras ?? [];
        $filas = $this->filasReporte($valoracion, $maniobras);
        $datos = $this->datosConsultorio($valoracion);

        $medicoCatalogo = $valoracion->medico;
        $medicoUsuario = $medicoCatalogo?->usuario;

        $pdf = Pdf::loadView('pdf.ClinicaTrabajo.Espirometria.reporteespirometria', [
            'v' => $valoracion,
            'maniobras' => $maniobras,
            'filas' => $filas,
            'hayZ' => collect($filas)->contains(fn ($f) => $f['z'] !== null),
            'graficas' => [
                'flujo' => $graficas->flujoVolumen($valoracion),
                'tiempo' => $graficas->volumenTiempo($valoracion),
                'z' => $graficas->puntuacionZ($valoracion),
            ],
            'medico' => $medicoUsuario,
            'cedula' => $medicoCatalogo?->cedula_profesional,
            'ubicacion' => $datos['ubicacion'],
            'logoPath' => $datos['logoPath'],
        ])->setPaper('letter');

        return $pdf->stream('espirometria-' . $valoracion->folio . '.pdf');
    }

    private function filasReporte(ValoracionEspirometria $v, array $maniobras): array
    {
        // [etiqueta, unidad, LLN, Pred, Mejor, %Pred, campo en maniobra, decimales, columna Z]
        $defs = [
            ['FVC', 'L', 'fvc_lln', 'fvc_pred', 'fvc_l', 'fvc_porcentaje_predicho', 'fvc_l', 2, 'fvc_z'],
            ['FEV1', 'L', 'fev1_lln', 'fev1_pred', 'fev1_l', 'fev1_porcentaje_predicho', 'fev1_l', 2, 'fev1_z'],
            ['FEV1/FVC', '%', 'fev1_fvc_lln', 'fev1_fvc_pred', 'fev1_fvc_ratio', 'fev1_fvc_porcentaje_predicho', 'fev1_fvc_ratio', 1, 'fev1_fvc_z'],
            ['FEF25-75', 'L/s', 'fef25_75_lln', 'fef25_75_pred', 'fef25_75', 'fef25_75_porcentaje_predicho', 'fef25_75', 2, 'fef25_75_z'],
            ['PEF', 'L/s', 'pef_lln', 'pef_pred', 'pef_l_s', 'pef_porcentaje_predicho', 'pef_l_s', 2, 'pef_z'],
            ['FET', 's', null, null, 'fet_s', null, 'fet_s', 1, null],
            ['FIVC', 'L', 'fivc_lln', 'fivc_pred', 'fivc_l', 'fivc_porcentaje_predicho', 'fivc_l', 2, 'fivc_z'],
            ['PIF', 'L/s', 'pif_lln', 'pif_pred', 'pif_l_s', 'pif_porcentaje_predicho', 'pif_l_s', 2, null],
            ['EOTV', 'L', 'eotv_lln', 'eotv_pred', 'eotv_l', null, 'eotv_l', 2, null],
            ['BEV', 'L', 'bev_lln', 'bev_pred', 'bev_l', null, 'bev_l', 2, null],
        ];

        $filas = [];
        foreach ($defs as [$label, $unidad, $lln, $teor, $mejor, $pct, $campo, $d, $zCampo]) {
            $pruebas = [];
            foreach ($maniobras as $m) {
                $pruebas[$m['numero']] = [
                    'valor' => $m[$campo] ?? null,
                    'marcada' => !empty($m['marcados'][$campo]),
                ];
            }
            $filas[] = [
                'label' => $label,
                'unidad' => $unidad,
                'lln' => $lln ? $v->{$lln} : null,
                'teor' => $teor ? $v->{$teor} : null,
                'mejor' => $v->{$mejor},
                'pct' => $pct ? $v->{$pct} : null,
                'z' => $zCampo ? $v->{$zCampo} : null,
                'pruebas' => $pruebas,
                'd' => $d,
            ];
        }

        return $filas;
    }

    /**
     * POST /clinica-trabajo/espirometria/importar
     * Lee el informe ndd (PDF o Excel) con IAServiceEspirometria y devuelve
     * las secciones del formulario: datosGenerales, parametrosFVL, interpretacion.
     */
    public function importarArchivo(Request $request, IAServiceEspirometria $iaService): JsonResponse
    {
        $request->validate([
            'archivo' => 'required|file|max:10240|extensions:pdf,xls,xlsx',
        ]);

        $ruta = $request->file('archivo')->getRealPath();
        $tipo = $this->detectarTipoArchivo($ruta);

        if ($tipo === null) {
            return response()->json([
                'success' => false,
                'message' => 'Formato no soportado. Sube un PDF o Excel de espirometría ndd.',
            ], 422);
        }

        $resultado = $iaService->importarArchivo($ruta, $tipo);

        if (empty($resultado['campos']['parametrosFVL'])) {
            return response()->json([
                'success' => false,
                'message' => $tipo === 'excel'
                    ? 'No se pudo leer la tabla del Excel. Verifica que sea un informe de espirometría ndd.'
                    : 'No se pudieron extraer los datos del informe. Verifica que sea un PDF de espirometría ndd con texto legible.',
                'advertencias' => $resultado['advertencias'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Datos extraídos exitosamente',
            'data' => $resultado['campos'],
            'advertencias' => $resultado['advertencias'],
        ]);
    }

    /**
     * POST /clinica-trabajo/espirometria/consultar-ia
     * Chat del médico con la IA de espirometría. Guarda el mensaje del médico y
     * la respuesta de la IA en espirometria_ia_mensajes. Acepta un archivo opcional.
     *
     * contexto_actual: JSON (string) con el estado del formulario por sección.
     */
    public function consultarIA(Request $request, IAServiceEspirometria $iaService): JsonResponse
    {
        $medico = $this->medicoAutenticado($request);
        if (!$medico) {
            return $this->sinMedico();
        }

        $request->validate([
            'mensaje' => 'required|string|max:4000',
            'session_uuid' => 'nullable|string|max:100',
            'paciente_id' => 'nullable|integer|exists:pacientes_clinica_trabajo,id',
            'valoracion_id' => 'nullable|integer|exists:valoraciones_espirometria,id',
            'contexto_actual' => 'nullable|string',
            'archivo' => 'nullable|file|max:10240|extensions:pdf,xls,xlsx',
            'modo' => 'nullable|in:chat,dictamen',
        ]);

        $sessionUuid = $request->input('session_uuid') ?: (string) Str::uuid();

        $valoracion = $request->filled('valoracion_id')
            ? ValoracionEspirometria::find($request->input('valoracion_id'))
            : null;

        $paciente = $valoracion?->paciente
            ?? ($request->filled('paciente_id')
                ? PacientesClinicaTrabajo::find($request->input('paciente_id'))
                : null);

        $contexto = json_decode($request->input('contexto_actual') ?: '{}', true);
        if (!is_array($contexto)) {
            $contexto = [];
        }

        // Historial de esta sesión, antes de guardar el mensaje actual
        $historial = EspirometriaIaMensaje::where('session_uuid', $sessionUuid)
            ->orderBy('id')
            ->get()
            ->map(fn ($m) => [
                'sender' => $m->tipo_usuario === 'medico' ? 'user' : 'ia',
                'text' => (string) $m->mensaje,
            ])
            ->all();

        $rutaArchivo = null;
        $tipoArchivo = null;
        $nombreArchivo = null;

        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            $rutaArchivo = $archivo->getRealPath();
            $tipoArchivo = $this->detectarTipoArchivo($rutaArchivo);
            $nombreArchivo = $archivo->getClientOriginalName();

            if ($tipoArchivo === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato no soportado. Sube un PDF o Excel de espirometría ndd.',
                ], 422);
            }
        }

        $mensaje = $request->input('mensaje');

        EspirometriaIaMensaje::create([
            'valoracion_id' => $valoracion?->id,
            'session_uuid' => $sessionUuid,
            'medico_id' => $medico->id,
            'tipo_usuario' => 'medico',
            'mensaje' => $mensaje,
            'archivo_nombre' => $nombreArchivo,
        ]);

        if ($request->input('modo') === 'dictamen') {
            $resultado = $iaService->proponerDictamen($contexto, $paciente, $historial);
            $resultado['archivo_procesado'] = false;
        } else {
            $resultado = $iaService->consultar(
                $mensaje,
                $contexto,
                $historial,
                $paciente,
                $rutaArchivo,
                $tipoArchivo
            );
        }

        EspirometriaIaMensaje::create([
            'valoracion_id' => $valoracion?->id,
            'session_uuid' => $sessionUuid,
            'medico_id' => $medico->id,
            'tipo_usuario' => 'ia',
            'mensaje' => $resultado['respuesta'],
            'campos_propuestos' => $resultado['campos_completados'],
            'aceptado_medico' => false,
        ]);

        return response()->json([
            'success' => true,
            'session_uuid' => $sessionUuid,
            'respuesta' => $resultado['respuesta'],
            'campos_completados' => $resultado['campos_completados'],
            'archivo_procesado' => $resultado['archivo_procesado'],
            'advertencias' => $resultado['advertencias'],
        ]);
    }
    // ---------------------------------------------------------------
    // Auxiliares
    // ---------------------------------------------------------------

    /**
     * Médico asociado al usuario autenticado (medicos.user_id -> users.id).
     */
    private function medicoAutenticado(Request $request): ?Medico
    {
        return Medico::where('user_id', $request->user()->id)
            ->where('activo', 1)
            ->first();
    }

    private function sinMedico(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Tu usuario no está asociado a un médico activo. Pide al administrador que lo vincule.',
        ], 403);
    }

    /**
     * Crea la valoración con folio ESP-AAAA-NNNN. Si dos guardados chocan
     * en el mismo folio (índice único), reintenta hasta 3 veces.
     */
    private function crearConFolio(array $validated, int $medicoId): ValoracionEspirometria
    {
        $datos = array_merge($validated, [
            'medico_id' => $medicoId,
            'num_maniobras' => count($validated['maniobras'] ?? []) ?: null,
        ]);

        for ($intento = 1; ; $intento++) {
            try {
                return DB::transaction(function () use ($datos) {
                    return ValoracionEspirometria::create(array_merge($datos, [
                        'folio' => $this->generarFolio(),
                    ]));
                });
            } catch (QueryException $e) {
                // 1062 = clave duplicada (folio ya tomado por otro guardado)
                if (($e->errorInfo[1] ?? null) !== 1062 || $intento >= 3) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Siguiente folio del año: ESP-AAAA-NNNN. Debe llamarse dentro de una transacción.
     */
    private function generarFolio(): string
    {
        $prefijo = 'ESP-' . now()->year . '-';

        $ultimo = ValoracionEspirometria::where('folio', 'like', $prefijo . '%')
            ->lockForUpdate()
            ->orderByDesc('folio')
            ->value('folio');

        $siguiente = $ultimo ? ((int) substr($ultimo, strlen($prefijo))) + 1 : 1;

        return $prefijo . str_pad((string) $siguiente, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Reglas de validación para crear (store) y actualizar (update).
     * Los campos coinciden con las columnas de valoraciones_espirometria.
     */
    private function reglas(bool $esCreacion): array
    {
        $requerido = $esCreacion ? 'required' : 'sometimes|required';
        $numero = 'nullable|numeric';
        $calidad = 'nullable|in:A,B,C,D,E,F,U';
        $booleano = 'sometimes|boolean';

        return [
            // Obligatorios en la tabla
            'paciente_id' => $requerido . '|integer|exists:pacientes_clinica_trabajo,id',
            'empresa_cliente_id' => $requerido . '|integer|exists:empresas_cliente,id',
            'puesto_trabajo_id' => 'nullable|integer|exists:puestos_trabajo,id',
            'tipo_evaluacion' => $requerido . '|in:ingreso,periodica,seguimiento,extraordinaria',
            'fecha_valoracion' => $requerido . '|date',
            'fecha_prueba' => 'nullable|date',
            'hora_valoracion' => 'nullable|date_format:H:i',
            'nombre' => $requerido . '|string|max:255',
            'aptitud' => $requerido . '|in:Apto,Apto con restricciones,No apto',

            // Datos generales del paciente (snapshot al momento de la prueba)
            'fecha_nacimiento' => 'nullable|date',
            'id_empleado' => 'nullable|string|max:100',
            'edad' => 'nullable|integer|min:0|max:120',
            'sexo' => 'nullable|in:Masculino,Femenino',
            'origen_etnico' => 'nullable|in:Hispano,Caucásico,Afrodescendiente,Otro',
            'empresa' => 'nullable|string|max:255',
            'remitido_por' => 'nullable|string|max:255',
            'prescrito_por' => 'nullable|string|max:255',

            // Antropometría y tabaquismo
            'altura_cm' => 'nullable|numeric|between:50,250',
            'peso_kg' => 'nullable|numeric|between:20,300',
            'peso_ideal_kg' => 'nullable|numeric|between:20,200',
            'imc' => $numero,
            'superficie_corporal' => $numero,
            'cigarrillos_dia' => 'nullable|integer|min:0|max:100',
            'anos_fumador' => 'nullable|integer|min:0|max:80',
            'pack_years' => $numero,
            'fumador' => $booleano,
            'asma' => $booleano,
            'epoc' => $booleano,
            'antecedente_covid' => $booleano,
            'rinitis_alergica' => $booleano,
            'tuberculosis' => $booleano,
            'neumonia_previa' => $booleano,
            'comentarios' => 'nullable|string',

            // Referencia y mejor maniobra
            'referencia' => 'nullable|in:NHANES III,GLI-2012,Hankinson,Lloveras,Pellegrino',
            'interpretacion_predicha' => 'nullable|in:GOLD(2008)/Hardie,ATS/ERS 2021,GOLD-2023',
            'fvc_l' => $numero,
            'fev1_l' => $numero,
            'fev1_fvc_ratio' => 'nullable|numeric|between:0,100', // porcentaje
            'fef25_75' => $numero,
            'pef_l_s' => $numero,
            'fet_s' => $numero,
            'fivc_l' => $numero,
            'pif_l_s' => $numero,
            'eotv_l' => $numero,
            'bev_l' => $numero,
            'fev1_var_l' => $numero,
            'fvc_var_l' => $numero,
            'pef_var_l_s' => $numero,
            'fef2575_var_l_s' => $numero,

            // Predicho y LLN por parámetro
            'fvc_pred' => $numero, 'fvc_lln' => $numero,
            'fev1_pred' => $numero, 'fev1_lln' => $numero,
            'fev1_fvc_pred' => $numero, 'fev1_fvc_lln' => $numero,
            'fef25_75_pred' => $numero, 'fef25_75_lln' => $numero,
            'pef_pred' => $numero, 'pef_lln' => $numero,
            'fivc_pred' => $numero, 'fivc_lln' => $numero,
            'pif_pred' => $numero, 'pif_lln' => $numero,
            'eotv_pred' => $numero, 'eotv_lln' => $numero,
            'bev_pred' => $numero, 'bev_lln' => $numero,

            // Porcentajes del predicho
            'fvc_porcentaje_predicho' => 'nullable|integer|min:0',
            'fev1_porcentaje_predicho' => 'nullable|integer|min:0',
            'fef25_75_porcentaje_predicho' => 'nullable|integer|min:0',
            'pef_porcentaje_predicho' => 'nullable|integer|min:0',
            'fev1_fvc_porcentaje_predicho' => 'nullable|integer|min:0',
            'fivc_porcentaje_predicho' => 'nullable|integer|min:0',
            'pif_porcentaje_predicho' => 'nullable|integer|min:0',

            // Puntuación Z (solo cuando el informe la trae)
            'fvc_z' => $numero, 'fev1_z' => $numero,
            'fev1_fvc_z' => $numero, 'fef25_75_z' => $numero,
            'pef_z' => $numero, 'fivc_z' => $numero,

            // Calidad (ATS/ERS)
            'calidad_sesion' => $calidad,
            'calidad_fev1' => $calidad,
            'calidad_fvc' => $calidad,

            // Interpretación y dictamen
            'edad_pulmonar' => 'nullable|integer|min:0|max:120',
            'interpretacion_sistema' => 'nullable|in:Espirometría Normal,Patrón Obstructivo,Patrón Restrictivo,Patrón Mixto,Prueba no válida',
            'interpretacion_equipo' => 'nullable|string',
            'diagnostico_medico' => 'nullable|string',
            'recomendaciones' => 'nullable|string',
            'seguimiento_requerido' => $booleano,
            'plazo_proximo_seguimiento' => 'nullable|date',

            // Maniobras (JSON)
            'maniobras' => 'nullable|array',
            'maniobras.*.numero' => 'nullable|integer',
        ];
    }

    /**
     * Identifica el tipo de archivo por sus primeros bytes (firma).
     */
    private function detectarTipoArchivo(string $ruta): ?string
    {
        $manejador = @fopen($ruta, 'rb');
        if ($manejador === false) {
            return null;
        }

        $cabecera = fread($manejador, 8);
        fclose($manejador);

        return match (true) {
            str_starts_with($cabecera, '%PDF') => 'pdf',
            str_starts_with($cabecera, "\xD0\xCF\x11\xE0") => 'excel',
            str_starts_with($cabecera, "PK\x03\x04") => 'excel',
            default => null,
        };
    }

    private function datosConsultorio(ValoracionEspirometria $valoracion): array
    {
        // Nombre, dirección y teléfono: sucursal del médico (igual que consultas)
        $ubicacion = null;
        $medicoCatalogo = $valoracion->medico;
        if ($medicoCatalogo) {
            $medicoCatalogo->load('configuraciones.ubicacion');
            $ubicacion = $medicoCatalogo->configuraciones->first()?->ubicacion;
        }

        if (!$ubicacion) {
            $empresa = \App\Models\Empresa::first();
            if ($empresa) {
                $ubicacion = (object) [
                    'nombre'    => $empresa->nombre_empresa,
                    'direccion' => $empresa->direccion,
                    'telefono'  => $empresa->telefono,
                    'imagen'    => $empresa->logo_url,
                ];
            }
        }

        // Logo: igual que el expediente (configuracion_empresa.logo_url);
        // si no existe, se usa la imagen de la sucursal y después el genérico.
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
}