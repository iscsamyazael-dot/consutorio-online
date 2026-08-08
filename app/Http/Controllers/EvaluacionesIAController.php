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
            // FILTRO ESPECIALIDAD
            // ==========================
            if ($request->filled('especialidad')) {
                $query->whereHas('consulta.medico', function ($q) use ($request) {
                    $q->where('especialidad_id', $request->especialidad);
                });
            }

            // ==========================
            // FILTRO RIESGO (vive en alertas_clinicas.nivel_riesgo)
            // ==========================
            if ($request->filled('riesgo')) {
                $query->whereHas('consulta.alertasClinicas', function ($q) use ($request) {
                    $q->where('nivel_riesgo', $request->riesgo);
                });
            }

            // ==========================
            // FILTRO ESTADO (vive en alertas_clinicas.estado)
            // ==========================
            if ($request->filled('estado')) {
                $query->whereHas('consulta.alertasClinicas', function ($q) use ($request) {
                    $q->where('estado', $request->estado);
                });
            }

            // ==========================
            // FILTRO CONFIANZA (sí vive en evaluaciones_ia)
            // ==========================
            if ($request->filled('confianzaMin')) {
                $query->where('confianza', '>=', $request->confianzaMin);
            }

            $evaluaciones = $query->paginate(10)->through(function (Evaluacionesia $eva) {

                $alertaReciente = $eva->consulta->alertasClinicas->last();

                return [
                    'folio' => $eva->consulta->paciente->paciente_id ?? 'SIN FOLIO', // se muestra en la tabla
                    'consulta_folio' => $eva->consulta_folio ?? $eva->consulta->folio ?? null, // ← Para identificar en la info de evaluación
                    'paciente' => $eva->consulta->paciente->nombre ?? 'Paciente no registrado',
                    'consulta' => $eva->consulta->folio ?? 'Sin consulta',
                    'fecha' => optional($eva->created_at)->format('Y-m-d H:i'),
                    'riesgo' => $alertaReciente->nivel_riesgo ?? 'BAJO',
                    'confianza' => (float) $eva->confianza,
                    'estado' => $alertaReciente->estado ?? 'pendiente',
                    'medico' => $eva->consulta->medico->nombre ?? '',
                    'especialidad' => $eva->consulta->medico->especialidad->nombre ?? ''
                ];

            });

            return response()->json($evaluaciones);

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
     * Endpoint API para el modal de detalle
     */
    public function show(string $folio)
    {
        try {
            // 1. Buscar primero en la tabla evaluaciones_ia usando solo 'consulta_folio'
            $evaluacionDb = \DB::table('evaluaciones_ia')
                ->where('consulta_folio', $folio)
                ->first();

            $evaluacionId = $evaluacionDb?->id;

            // 2. Si no se halló por consulta_folio pero venía como EV-005 o un número ID
            if (!$evaluacionId) {
                if (preg_match('/^EV-(\d+)$/i', $folio, $m)) {
                    $evaluacionId = (int) ltrim($m[1], '0');
                } elseif (is_numeric($folio)) {
                    $evaluacionId = (int) $folio;
                }
            }

            // 3. Si venía un folio de consulta tipo 'CONS-XXXX', buscar el ID mediante la tabla consultas
            if (!$evaluacionId && str_starts_with(strtoupper($folio), 'CONS-')) {
                $consultaDb = \DB::table('consultas')->where('folio', $folio)->first();
                if ($consultaDb) {
                    $evaluacionDb = \DB::table('evaluaciones_ia')
                        ->where('consulta_id', $consultaDb->id)
                        ->first();
                    $evaluacionId = $evaluacionDb?->id;
                }
            }

            // 4. Cargar la instancia Eloquent completa usando únicamente el ID encontrado
            $evaluacion = null;
            if ($evaluacionId) {
                $evaluacion = Evaluacionesia::with([
                    'consulta.paciente',
                    'consulta.recetas',
                    'consulta.derivaciones.especialidad',
                    'consulta.alertasClinicas',
                    'notaPsoapp'
                ])->find($evaluacionId);
            }

            if (!$evaluacion) {
                return response()->json(['message' => 'Evaluación no encontrada.'], 404);
            }

            $consulta   = $evaluacion->consulta ?? null;
            $paciente   = $consulta?->paciente ?? null;
            $receta     = $consulta?->recetas?->last();
            $derivacion = $consulta?->derivaciones?->last();

            // Formatear Receta
            $recetaData = null;
            if ($receta) {
                $meds = $receta->medicamentos;
                $recetaData = [
                    'id'                     => $receta->id,
                    'fecha'                  => $receta->fecha,
                    'indicaciones_generales' => $receta->indicaciones_generales,
                    'observaciones_ia'       => $receta->observaciones_ia,
                    'medicamentos'           => is_string($meds) ? json_decode($meds, true) : $meds,
                    'estado'                 => $receta->estado,
                ];
            }

            // Formatear Derivación
            $derivacionData = null;
            if ($derivacion) {
                $derivacionData = [
                    'especialidad' => $derivacion->especialidad->nombre ?? 'Especialidad General',
                    'hospital'     => $derivacion->hospital ?? 'N/A',
                    'motivo'       => $derivacion->motivo ?? 'Sin motivo especificado',
                    'prioridad'    => strtoupper($derivacion->prioridad ?? 'MEDIA'),
                    'estado'       => $derivacion->estado ?? 'Pendiente',
                ];
            }

            // Formatear Alertas Clínicas
            $alertasData = collect($consulta?->alertasClinicas ?? [])->map(function ($alerta) {
                return [
                    'id'          => $alerta->id,
                    'titulo'      => $alerta->titulo ?? $alerta->tipo_alerta ?? 'Alerta Clínica',
                    'descripcion' => $alerta->descripcion ?? '',
                    'nivel'       => strtoupper($alerta->nivel ?? $alerta->nivel_riesgo ?? 'MEDIO'),
                    'observaciones' => $alerta->observaciones ?? '',
                ];
            });

            // Formatear folio de salida
            $folioSalida = $evaluacion->consulta_folio 
                ?? $consulta?->folio 
                ?? ('EV-' . str_pad($evaluacion->id, 4, '0', STR_PAD_LEFT));

            return response()->json([
                'folio'                => $folioSalida,
                'fecha'                => optional($evaluacion->created_at)->format('Y-m-d H:i') ?? 'N/A',
                'diagnostico_probable' => $evaluacion->diagnostico_probable ?? 'Sin diagnóstico',
                'sintomas_array'       => $evaluacion->sintomas_array ?? [],
                'riesgo'               => $evaluacion->riesgo ?? 'Bajo',
                'confianza'            => (float) ($evaluacion->confianza ?? 0),
                'recomendacion'        => $evaluacion->recomendacion ?? '',
                'paciente'             => [
                    'id'     => $paciente->paciente_id ?? $paciente->id ?? 'N/A',
                    'nombre' => $paciente->nombre ?? 'Paciente no registrado',
                    'edad'   => $paciente->edad ?? null,
                    'sexo'   => $paciente->sexo ?? null,
                ],
                'nota_psoapp'          => $evaluacion->notaPsoapp ?? $consulta?->notaPsoapp ?? null,
                'receta'               => $recetaData,
                'derivacion'           => $derivacionData,
                'alertas_clinicas'     => $alertasData,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'error'   => 'Error interno al cargar la evaluación',
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine()
            ], 500);
        }
    }
}