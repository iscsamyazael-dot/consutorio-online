<?php

namespace App\Http\Controllers;

use App\Models\Consulta;
use App\Models\Evaluacionesia;
use App\Models\Especialidad;
use App\Models\Medico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Js;

class EvaluacionesiaController extends Controller
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

    /**
     * Calcula indicadores utilizando la base de datos (Optimizado O(1) memoria)
     */
    private function calcularIndicadores(): array
    {
        $riesgoAlto = Evaluacionesia::whereHas('consulta.alertasClinicas', function ($q) {
            $q->whereRaw('LOWER(nivel_riesgo) = ?', ['alto']);
        })->distinct('consulta_id')->count('consulta_id');

        $evaluacionesHoy = Evaluacionesia::whereDate('created_at', today())->count();

        $pendientesRevision = Evaluacionesia::whereHas('consulta.alertasClinicas', function ($q) {
            $q->whereRaw('LOWER(estado) = ?', ['pendiente']);
        })->distinct('consulta_id')->count('consulta_id');

        $confianzaPromedio = round((float) Evaluacionesia::avg('confianza'));

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
            $query = Evaluacionesia::query();

            // Aplicación de Filtros
            if ($request->filled('paciente')) {
                $buscar = $request->paciente;
                $query->whereHas('consulta.paciente', fn ($q) => $q->where('nombre', 'like', "%{$buscar}%"));
            }

            if ($request->filled('medico')) {
                $query->whereHas('consulta.medico', fn ($q) => $q->where('id', $request->medico));
            }

            if ($request->filled('fecha')) {
                $query->whereDate('created_at', $request->fecha);
            }

            if ($request->filled('especialidad')) {
                $query->whereHas('consulta.medico', fn ($q) => $q->where('especialidad_id', $request->especialidad));
            }

            if ($request->filled('riesgo')) {
                $query->whereHas('consulta.alertasClinicas', fn ($q) => $q->where('nivel_riesgo', $request->riesgo));
            }

            if ($request->filled('estado')) {
                $query->whereHas('consulta.alertasClinicas', fn ($q) => $q->where('estado', $request->estado));
            }

            if ($request->filled('confianzaMin')) {
                $query->where('confianza', '>=', $request->confianzaMin);
            }

            // Obtener IDs de consultas paginadas a nivel base de datos
            $porPagina = 10;
            $pagina = (int) $request->get('page', 1);

            $subQuery = (clone $query)
                ->selectRaw('consulta_id, MAX(id) as latest_id')
                ->groupBy('consulta_id');

            $total = DB::table(DB::raw("({$subQuery->toSql()}) as sub"))
                ->mergeBindings($subQuery->getQuery())
                ->count();

            $consultasPaginadas = (clone $query)
                ->selectRaw('consulta_id, MAX(id) as latest_id')
                ->groupBy('consulta_id')
                ->orderByDesc('latest_id')
                ->offset(($pagina - 1) * $porPagina)
                ->limit($porPagina)
                ->pluck('consulta_id');

            // Cargar evaluaciones completas de los IDs obtenidos
            $evaluaciones = Evaluacionesia::with([
                'consulta.paciente',
                'consulta.medico.especialidad',
                'consulta.alertasClinicas'
            ])
            ->whereIn('consulta_id', $consultasPaginadas)
            ->latest('id')
            ->get();

            $resultado = $evaluaciones
                ->groupBy('consulta_id')
                ->map(function ($grupo) {
                    $evaPrincipal = $grupo->first();
                    $consulta = $evaPrincipal->consulta;
                    $alertaReciente = $consulta?->alertasClinicas?->last();

                    $confianzas = $grupo
                        ->pluck('confianza')
                        ->reject(fn ($v) => is_null($v))
                        ->map(fn ($v) => (float) $v)
                        ->values()
                        ->all();

                    $evaluacionesData = $grupo->map(fn ($eva) => [
                        'id' => $eva->id,
                        'diagnostico' => $eva->diagnostico_probable ?? 'Sin diagnóstico',
                        'confianza' => (float) ($eva->confianza ?? 0),
                        'riesgo' => $eva->riesgo ?? 'Bajo',
                    ])->values()->all();

                    return [
                        'consulta_id' => $consulta?->id,
                        'folio' => $consulta?->paciente?->paciente_id ?? 'SIN FOLIO',
                        'consulta_folio' => $evaPrincipal->consulta_folio ?? $consulta?->folio ?? null,
                        'paciente' => $consulta?->paciente?->nombre ?? 'Paciente no registrado',
                        'consulta' => $consulta?->folio ?? 'Sin consulta',
                        'fecha' => optional($evaPrincipal->created_at)->format('Y-m-d H:i'),
                        'riesgo' => $alertaReciente?->nivel_riesgo ?? 'BAJO',
                        'confianzas' => $confianzas,
                        'evaluaciones' => $evaluacionesData,
                        'estado' => $alertaReciente?->estado ?? 'pendiente',
                        'medico' => $consulta?->medico?->nombre ?? '',
                        'especialidad' => $consulta?->medico?->especialidad?->nombre ?? '',
                    ];
                })
                ->values();

            return response()->json([
                'current_page' => $pagina,
                'data' => $resultado,
                'from' => $total > 0 ? (($pagina - 1) * $porPagina) + 1 : null,
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
            $consulta = null;
            $relacionesConsulta = [
                'paciente',
                'medico.especialidad',
                'recetas',
                'derivaciones.especialidad',
                'alertasClinicas',
                'notaPsoapp',
            ];

            // CASO 1: Folio de consulta
            if (str_starts_with(strtoupper($folio), 'CONS-')) {
                $consulta = Consulta::with($relacionesConsulta)
                    ->where('folio', $folio)
                    ->first();
            }

            // CASO 2 & 3: Folio EV-0001 o ID numérico directos
            if (!$consulta) {
                $evaluacionId = null;

                if (preg_match('/^EV-(\d+)$/i', $folio, $m)) {
                    $evaluacionId = (int) ltrim($m[1], '0');
                } elseif (is_numeric($folio)) {
                    $evaluacionId = (int) $folio;
                }

                if ($evaluacionId) {
                    $evaluacion = Evaluacionesia::with(['consulta' => fn ($q) => $q->with($relacionesConsulta)])
                        ->find($evaluacionId);
                    $consulta = $evaluacion?->consulta;
                }
            }

            if (!$consulta) {
                return response()->json(['message' => 'Consulta no encontrada.'], 404);
            }

            $evaluaciones = Evaluacionesia::query()
                ->where('consulta_id', $consulta->id)
                ->orderBy('id')
                ->get();

            if ($evaluaciones->isEmpty()) {
                return response()->json(['message' => 'La consulta no tiene evaluaciones IA registradas.'], 404);
            }

            $evaluacionesData = $evaluaciones->map(fn ($eva) => [
                'id' => $eva->id,
                'diagnostico_probable' => $eva->diagnostico_probable ?? 'Sin diagnóstico',
                'sintomas_array' => $eva->sintomas_array ?? [],
                'riesgo' => $eva->riesgo ?? 'Bajo',
                'confianza' => (float) ($eva->confianza ?? 0),
                'recomendacion' => $eva->recomendacion ?? '',
                'fecha' => optional($eva->created_at)->format('Y-m-d H:i'),
            ])->values();

            $evaluacionPrincipal = $evaluaciones->first();
            $paciente = $consulta->paciente;
            $receta = $consulta->recetas?->last();

            $recetaData = $receta ? [
                'id' => $receta->id,
                'fecha' => $receta->fecha,
                'indicaciones_generales' => $receta->indicaciones_generales,
                'observaciones_ia' => $receta->observaciones_ia,
                'medicamentos' => is_string($receta->medicamentos) ? json_decode($receta->medicamentos, true) : $receta->medicamentos,
                'estado' => $receta->estado,
            ] : null;

            $derivacion = $consulta->derivaciones?->last();
            $derivacionData = $derivacion ? [
                'especialidad' => $derivacion->especialidad->nombre ?? 'Especialidad General',
                'hospital' => $derivacion->hospital ?? 'N/A',
                'motivo' => $derivacion->motivo ?? 'Sin motivo especificado',
                'prioridad' => strtoupper($derivacion->prioridad ?? 'MEDIA'),
                'estado' => $derivacion->estado ?? 'Pendiente',
            ] : null;

            $alertasData = collect($consulta->alertasClinicas ?? [])->map(fn ($alerta) => [
                'id' => $alerta->id,
                'titulo' => $alerta->titulo ?? $alerta->tipo_alerta ?? 'Alerta Clínica',
                'descripcion' => $alerta->descripcion ?? '',
                'nivel' => strtoupper($alerta->nivel ?? $alerta->nivel_riesgo ?? 'MEDIO'),
                'observaciones' => $alerta->observaciones ?? '',
            ])->values();

            $folioSalida = $consulta->folio 
                ?? $evaluacionPrincipal->consulta_folio 
                ?? ('EV-' . str_pad($evaluacionPrincipal->id, 4, '0', STR_PAD_LEFT));

            $confianzas = $evaluaciones
                ->pluck('confianza')
                ->reject(fn ($v) => is_null($v))
                ->map(fn ($v) => (float) $v)
                ->values();

            return response()->json([
                'folio' => $folioSalida,
                'consulta_id' => $consulta->id,
                'fecha' => optional($evaluacionPrincipal->created_at)->format('Y-m-d H:i') ?? 'N/A',
                'evaluaciones' => $evaluacionesData,
                'confianzas' => $confianzas,
                'diagnostico_probable' => $evaluacionPrincipal->diagnostico_probable ?? 'Sin diagnóstico',
                'sintomas_array' => $evaluacionPrincipal->sintomas_array ?? [],
                'riesgo' => $evaluacionPrincipal->riesgo ?? 'Bajo',
                'confianza' => (float) ($evaluacionPrincipal->confianza ?? 0),
                'recomendacion' => $evaluacionPrincipal->recomendacion ?? '',
                'paciente' => [
                    'id' => $paciente->paciente_id ?? $paciente->id ?? 'N/A',
                    'nombre' => $paciente->nombre ?? 'Paciente no registrado',
                    'edad' => $paciente->edad ?? null,
                    'sexo' => $paciente->sexo ?? null,
                ],
                'nota_psoapp' => $consulta->notaPsoapp ?? $evaluacionPrincipal->notaPsoapp ?? null,
                'receta' => $recetaData,
                'derivacion' => $derivacionData,
                'alertas_clinicas' => $alertasData,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Error interno al cargar la evaluación',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}