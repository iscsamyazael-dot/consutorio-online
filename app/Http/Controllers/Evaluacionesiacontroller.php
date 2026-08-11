<?php

namespace App\Http\Controllers;

use App\Models\Evaluacionesia;
use App\Models\Especialidad;
use App\Models\Medico;
use Illuminate\Http\Request;
use Illuminate\Support\Js;

class EvaluacionesIAController extends Controller
{
    /**
     * Carga la vista principal de Blade con catálogos
     */
    public function index()
    {
        $especialidades = Especialidad::orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn ($e) => ['value' => $e->id, 'label' => $e->nombre]);

        $medicos = Medico::where('activo', 1)
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn ($m) => ['value' => $m->id, 'label' => $m->nombre]);

        return view('atencion-medica.evaluacion-ia', [
            'especialidadesJs' => Js::from($especialidades),
            'medicosJs'        => Js::from($medicos),
        ]);
    }

    private function calcularIndicadores(): array
    {
        $vigentes = Evaluacionesia::query()
            ->with(['consulta.alertasClinicas', 'notaPsoapp'])
            ->get();

        $riesgoAlto = $vigentes->filter(function ($e) {
            $nivel = strtolower($e->consulta?->alertasClinicas?->last()?->nivel_riesgo ?? '');
            return $nivel === 'alto';
        })->count();

        $evaluacionesHoy = $vigentes->filter(function ($e) {
            return $e->created_at && $e->created_at->isToday();
        })->count();

        $pendientesRevision = $vigentes->filter(function ($e) {
            $estado = strtolower($e->consulta?->alertasClinicas?->last()?->estado ?? '');
            return $estado === 'pendiente';
        })->count();

        $confianzaPromedio = $vigentes->count()
            ? round($vigentes->avg('confianza'))
            : 0;

        return [
            [
                'label' => 'Riesgo alto',
                'valor' => $riesgoAlto,
                'icon'  => 'fa-exclamation-triangle',
                'color' => 'danger',
            ],
            [
                'label' => 'Evaluaciones del día',
                'valor' => $evaluacionesHoy,
                'icon'  => 'fa-calendar-day',
                'color' => 'info',
            ],
            [
                'label' => 'Pendientes de revisión',
                'valor' => $pendientesRevision,
                'icon'  => 'fa-sync-alt',
                'color' => 'warning',
            ],
            [
                'label' => 'Confianza promedio',
                'valor' => $confianzaPromedio . '%',
                'icon'  => 'fa-brain',
                'color' => 'success',
            ],
        ];
    }

    public function indicadores()
    {
        return response()->json($this->calcularIndicadores());
    }

    /**
     * Endpoint API para la tabla paginada en Vue
     */
    public function api(Request $request)
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | 1. CONSULTAS QUE TIENEN EVALUACIONES IA
            |--------------------------------------------------------------------------
            |
            | La tabla debe mostrar UNA fila por consulta,
            | aunque esa consulta tenga varias evaluaciones IA.
            |
            */

            $query = Evaluacionesia::query()
                ->with([
                    'consulta.paciente',
                    'consulta.medico.especialidad',
                    'consulta.alertasClinicas'
                ])
                ->latest('id');

            // ==========================
            // FILTRO PACIENTE
            // ==========================
            if ($request->filled('paciente')) {

                $buscar = $request->paciente;

                $query->whereHas('consulta.paciente', function ($q) use ($buscar) {
                    $q->where('nombre', 'like', "%{$buscar}%");
                });
            }

            // ==========================
            // FILTRO MÉDICO
            // ==========================
            if ($request->filled('medico')) {

                $query->whereHas('consulta.medico', function ($q) use ($request) {
                    $q->where('id', $request->medico);
                });
            }

            // ==========================
            // FILTRO FECHA
            // ==========================
            if ($request->filled('fecha')) {
                // whereDate asegura comparar la fecha sin importar la hora/minutos (HH:mm:ss)
                $query->whereDate('created_at', $request->fecha);
            }

            // ==========================
            // FILTRO ESPECIALIDAD
            // ==========================
            if ($request->filled('especialidad')) {

                $query->whereHas('consulta.medico', function ($q) use ($request) {
                    $q->where('especialidad_id', $request->especialidad);
                });
            }

            // ==========================
            // FILTRO RIESGO
            // ==========================
            if ($request->filled('riesgo')) {

                $query->whereHas('consulta.alertasClinicas', function ($q) use ($request) {
                    $q->where('nivel_riesgo', $request->riesgo);
                });
            }

            // ==========================
            // FILTRO ESTADO
            // ==========================
            if ($request->filled('estado')) {

                $query->whereHas('consulta.alertasClinicas', function ($q) use ($request) {
                    $q->where('estado', $request->estado);
                });
            }

            // ==========================
            // FILTRO CONFIANZA
            // ==========================
            if ($request->filled('confianzaMin')) {

                $query->where('confianza', '>=', $request->confianzaMin);
            }

            /*
            |--------------------------------------------------------------------------
            | 2. OBTENER CONSULTAS ÚNICAS
            |--------------------------------------------------------------------------
            */

            $consultasAgrupadas = (clone $query)
            ->reorder()
            ->selectRaw('consulta_id, MAX(id) as latest_id')
            ->groupBy('consulta_id')
            ->orderByDesc('latest_id')
            ->get();

            /*
            |--------------------------------------------------------------------------
            | 3. PAGINACIÓN POR CONSULTA
            |--------------------------------------------------------------------------
            |
            | IMPORTANTE:
            | Ya no paginamos evaluaciones individuales.
            | Paginamos consultas únicas.
            |
            */

           $porPagina = 10;

            $pagina = (int) $request->get('page', 1);

            $total = $consultasAgrupadas->count();

            $idsPagina = $consultasAgrupadas
                ->forPage($pagina, $porPagina)
                ->pluck('consulta_id')
                ->values();

            /*
            |--------------------------------------------------------------------------
            | 4. CARGAR TODAS LAS EVALUACIONES DE ESAS CONSULTAS
            |--------------------------------------------------------------------------
            */

            $evaluaciones = Evaluacionesia::query()
                ->with([
                    'consulta.paciente',
                    'consulta.medico.especialidad',
                    'consulta.alertasClinicas'
                ])
                ->whereIn('consulta_id', $idsPagina)
                ->latest('id')
                ->get();

            /*
            |--------------------------------------------------------------------------
            | 5. AGRUPAR POR CONSULTA
            |--------------------------------------------------------------------------
            */

            $resultado = $evaluaciones
                ->groupBy('consulta_id')
                ->map(function ($grupo) {

                    // Evaluación más reciente de la consulta
                    $evaPrincipal = $grupo->first();

                    $consulta = $evaPrincipal->consulta;

                    $alertaReciente = $consulta?->alertasClinicas?->last();

                    /*
                    |--------------------------------------------------------------------------
                    | Confianzas de todas las evaluaciones de esta consulta
                    |--------------------------------------------------------------------------
                    */

                    $confianzas = $grupo
                        ->pluck('confianza')
                        ->filter(fn ($valor) => $valor !== null)
                        ->map(fn ($valor) => (float) $valor)
                        ->values()
                        ->all();

                    /*
                    |--------------------------------------------------------------------------
                    | Evaluaciones / diagnósticos
                    |--------------------------------------------------------------------------
                    */

                    $evaluacionesData = $grupo->map(function ($eva) {

                        return [
                            'id' => $eva->id,
                            'diagnostico' => $eva->diagnostico_probable ?? 'Sin diagnóstico',
                            'confianza' => (float) ($eva->confianza ?? 0),
                            'riesgo' => $eva->riesgo ?? 'Bajo',
                        ];

                    })->values()->all();

                    return [
                        'consulta_id' => $consulta?->id,

                        'folio' => $consulta?->paciente?->paciente_id
                            ?? 'SIN FOLIO',

                        'consulta_folio' => $evaPrincipal->consulta_folio
                            ?? $consulta?->folio
                            ?? null,

                        'paciente' => $consulta?->paciente?->nombre
                            ?? 'Paciente no registrado',

                        'consulta' => $consulta?->folio
                            ?? 'Sin consulta',

                        'fecha' => optional($evaPrincipal->created_at)
                            ->format('Y-m-d H:i'),

                        'riesgo' => $alertaReciente?->nivel_riesgo
                            ?? 'BAJO',

                        /*
                        |--------------------------------------------------------------------------
                        | TODAS LAS CONFIANZAS
                        |--------------------------------------------------------------------------
                        */
                        'confianzas' => $confianzas,

                        /*
                        |--------------------------------------------------------------------------
                        | TODAS LAS EVALUACIONES
                        |--------------------------------------------------------------------------
                        */
                        'evaluaciones' => $evaluacionesData,

                        'estado' => $alertaReciente?->estado
                            ?? 'pendiente',

                        'medico' => $consulta?->medico?->nombre
                            ?? '',

                        'especialidad' => $consulta?->medico?->especialidad?->nombre
                            ?? '',
                    ];
                })
                ->values();

            /*
            |--------------------------------------------------------------------------
            | 6. RESPUESTA CON FORMATO DE PAGINACIÓN
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'current_page' => $pagina,
                'data' => $resultado,
                'from' => $total > 0
                    ? (($pagina - 1) * $porPagina) + 1
                    : null,
                'last_page' => max(1, (int) ceil($total / $porPagina)),
                'per_page' => $porPagina,
                'to' => min($pagina * $porPagina, $total),
                'total' => $total,
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Endpoint API para el detalle de una consulta/evaluación IA
     */
    public function show(string $folio)
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | 1. IDENTIFICAR LA CONSULTA
            |--------------------------------------------------------------------------
            |
            | El botón "Ver" ahora manda el consulta_folio:
            |
            | CONS-2026-0822
            |
            */

            $consulta = null;

            /*
            |--------------------------------------------------------------------------
            | CASO 1: Viene directamente un folio de consulta
            |--------------------------------------------------------------------------
            */

            if (str_starts_with(strtoupper($folio), 'CONS-')) {

                $consulta = \App\Models\Consulta::with([
                    'paciente',
                    'recetas',
                    'derivaciones.especialidad',
                    'alertasClinicas',
                    'notaPsoapp',
                ])
                ->where('folio', $folio)
                ->first();
            }

            /*
            |--------------------------------------------------------------------------
            | CASO 2: Viene un folio de evaluación tipo EV-0001
            |--------------------------------------------------------------------------
            */

            if (!$consulta && preg_match('/^EV-(\d+)$/i', $folio, $m)) {

                $evaluacionId = (int) ltrim($m[1], '0');

                $evaluacion = Evaluacionesia::with('consulta')
                    ->find($evaluacionId);

                $consulta = $evaluacion?->consulta;

                if ($consulta) {
                    $consulta->load([
                        'paciente',
                        'recetas',
                        'derivaciones.especialidad',
                        'alertasClinicas',
                        'notaPsoapp',
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | CASO 3: Viene directamente un ID numérico
            |--------------------------------------------------------------------------
            */

            if (!$consulta && is_numeric($folio)) {

                $evaluacion = Evaluacionesia::with('consulta')
                    ->find((int) $folio);

                $consulta = $evaluacion?->consulta;

                if ($consulta) {
                    $consulta->load([
                        'paciente',
                        'recetas',
                        'derivaciones.especialidad',
                        'alertasClinicas',
                        'notaPsoapp',
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | CONSULTA NO ENCONTRADA
            |--------------------------------------------------------------------------
            */

            if (!$consulta) {

                return response()->json([
                    'message' => 'Consulta no encontrada.'
                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | 2. OBTENER TODAS LAS EVALUACIONES IA DE LA CONSULTA
            |--------------------------------------------------------------------------
            |
            | Aquí está el cambio importante.
            |
            | Si CONS-2026-0822 tiene:
            |
            | evaluación 1 → 90%
            | evaluación 2 → 95%
            |
            | las obtenemos las dos.
            |
            */

            $evaluaciones = Evaluacionesia::query()
                ->where('consulta_id', $consulta->id)
                ->orderBy('id')
                ->get();

            /*
            |--------------------------------------------------------------------------
            | 3. VALIDAR QUE LA CONSULTA TENGA EVALUACIONES
            |--------------------------------------------------------------------------
            */

            if ($evaluaciones->isEmpty()) {

                return response()->json([
                    'message' => 'La consulta no tiene evaluaciones IA registradas.'
                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | 4. EVALUACIONES IA FORMATEADAS
            |--------------------------------------------------------------------------
            */

            $evaluacionesData = $evaluaciones->map(function ($evaluacion) {

                return [
                    'id' => $evaluacion->id,

                    'diagnostico_probable' =>
                        $evaluacion->diagnostico_probable
                        ?? 'Sin diagnóstico',

                    'sintomas_array' =>
                        $evaluacion->sintomas_array
                        ?? [],

                    'riesgo' =>
                        $evaluacion->riesgo
                        ?? 'Bajo',

                    'confianza' =>
                        (float) ($evaluacion->confianza ?? 0),

                    'recomendacion' =>
                        $evaluacion->recomendacion
                        ?? '',

                    'fecha' =>
                        optional($evaluacion->created_at)
                        ->format('Y-m-d H:i'),
                ];

            })->values();

            /*
            |--------------------------------------------------------------------------
            | 5. EVALUACIÓN PRINCIPAL
            |--------------------------------------------------------------------------
            |
            | La usamos únicamente para mantener compatibilidad
            | con el frontend actual mientras hacemos la transición.
            |
            */

            $evaluacionPrincipal = $evaluaciones->first();

            /*
            |--------------------------------------------------------------------------
            | 6. DATOS DEL PACIENTE
            |--------------------------------------------------------------------------
            */

            $paciente = $consulta->paciente;

            /*
            |--------------------------------------------------------------------------
            | 7. RECETA
            |--------------------------------------------------------------------------
            */

            $receta = $consulta->recetas?->last();

            $recetaData = null;

            if ($receta) {

                $meds = $receta->medicamentos;

                $recetaData = [
                    'id' =>
                        $receta->id,

                    'fecha' =>
                        $receta->fecha,

                    'indicaciones_generales' =>
                        $receta->indicaciones_generales,

                    'observaciones_ia' =>
                        $receta->observaciones_ia,

                    'medicamentos' =>
                        is_string($meds)
                            ? json_decode($meds, true)
                            : $meds,

                    'estado' =>
                        $receta->estado,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | 8. DERIVACIÓN
            |--------------------------------------------------------------------------
            */

            $derivacion = $consulta->derivaciones?->last();

            $derivacionData = null;

            if ($derivacion) {

                $derivacionData = [
                    'especialidad' =>
                        $derivacion->especialidad->nombre
                        ?? 'Especialidad General',

                    'hospital' =>
                        $derivacion->hospital
                        ?? 'N/A',

                    'motivo' =>
                        $derivacion->motivo
                        ?? 'Sin motivo especificado',

                    'prioridad' =>
                        strtoupper(
                            $derivacion->prioridad
                            ?? 'MEDIA'
                        ),

                    'estado' =>
                        $derivacion->estado
                        ?? 'Pendiente',
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | 9. ALERTAS CLÍNICAS
            |--------------------------------------------------------------------------
            */

            $alertasData = collect(
                $consulta->alertasClinicas ?? []
            )->map(function ($alerta) {

                return [
                    'id' =>
                        $alerta->id,

                    'titulo' =>
                        $alerta->titulo
                        ?? $alerta->tipo_alerta
                        ?? 'Alerta Clínica',

                    'descripcion' =>
                        $alerta->descripcion
                        ?? '',

                    'nivel' =>
                        strtoupper(
                            $alerta->nivel
                            ?? $alerta->nivel_riesgo
                            ?? 'MEDIO'
                        ),

                    'observaciones' =>
                        $alerta->observaciones
                        ?? '',
                ];

            })->values();

            /*
            |--------------------------------------------------------------------------
            | 10. FOLIO DE SALIDA
            |--------------------------------------------------------------------------
            */

            $folioSalida =
                $consulta->folio
                ?? $evaluacionPrincipal->consulta_folio
                ?? ('EV-' . str_pad(
                    $evaluacionPrincipal->id,
                    4,
                    '0',
                    STR_PAD_LEFT
                ));

            /*
            |--------------------------------------------------------------------------
            | 11. CONFIANZAS
            |--------------------------------------------------------------------------
            |
            | Ejemplo:
            |
            | [90, 95]
            |
            */

            $confianzas = $evaluaciones
                ->pluck('confianza')
                ->filter(fn ($valor) => $valor !== null)
                ->map(fn ($valor) => (float) $valor)
                ->values();

            /*
            |--------------------------------------------------------------------------
            | 12. RESPUESTA
            |--------------------------------------------------------------------------
            */

            return response()->json([

                /*
                |--------------------------------------------------------------------------
                | INFORMACIÓN GENERAL
                |--------------------------------------------------------------------------
                */

                'folio' =>
                    $folioSalida,

                'consulta_id' =>
                    $consulta->id,

                'fecha' =>
                    optional($evaluacionPrincipal->created_at)
                    ->format('Y-m-d H:i')
                    ?? 'N/A',

                /*
                |--------------------------------------------------------------------------
                | TODAS LAS EVALUACIONES IA
                |--------------------------------------------------------------------------
                */

                'evaluaciones' =>
                    $evaluacionesData,

                'confianzas' =>
                    $confianzas,

                /*
                |--------------------------------------------------------------------------
                | CAMPOS PRINCIPALES
                |--------------------------------------------------------------------------
                |
                | Se mantienen para no romper todavía
                | el frontend actual.
                |
                */

                'diagnostico_probable' =>
                    $evaluacionPrincipal->diagnostico_probable
                    ?? 'Sin diagnóstico',

                'sintomas_array' =>
                    $evaluacionPrincipal->sintomas_array
                    ?? [],

                'riesgo' =>
                    $evaluacionPrincipal->riesgo
                    ?? 'Bajo',

                'confianza' =>
                    (float) ($evaluacionPrincipal->confianza ?? 0),

                'recomendacion' =>
                    $evaluacionPrincipal->recomendacion
                    ?? '',

                /*
                |--------------------------------------------------------------------------
                | PACIENTE
                |--------------------------------------------------------------------------
                */

                'paciente' => [

                    'id' =>
                        $paciente->paciente_id
                        ?? $paciente->id
                        ?? 'N/A',

                    'nombre' =>
                        $paciente->nombre
                        ?? 'Paciente no registrado',

                    'edad' =>
                        $paciente->edad
                        ?? null,

                    'sexo' =>
                        $paciente->sexo
                        ?? null,
                ],

                /*
                |--------------------------------------------------------------------------
                | PSOAP
                |--------------------------------------------------------------------------
                |
                | UNO SOLO PARA TODAS LAS EVALUACIONES.
                |
                */

                'nota_psoapp' =>
                    $consulta->notaPsoapp
                    ?? $evaluacionPrincipal->notaPsoapp
                    ?? null,

                /*
                |--------------------------------------------------------------------------
                | RECETA
                |--------------------------------------------------------------------------
                */

                'receta' =>
                    $recetaData,

                /*
                |--------------------------------------------------------------------------
                | DERIVACIÓN
                |--------------------------------------------------------------------------
                */

                'derivacion' =>
                    $derivacionData,

                /*
                |--------------------------------------------------------------------------
                | ALERTAS
                |--------------------------------------------------------------------------
                */

                'alertas_clinicas' =>
                    $alertasData,
            ]);

        } catch (\Throwable $e) {

            return response()->json([

                'error' =>
                    'Error interno al cargar la evaluación',

                'message' =>
                    $e->getMessage(),

                'file' =>
                    $e->getFile(),

                'line' =>
                    $e->getLine()

            ], 500);
        }
    }
}