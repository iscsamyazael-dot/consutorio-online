<?php

namespace App\Http\Controllers\ClinicaTrabajo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

// Modelos del módulo Clínica de Trabajo
use App\Models\ClinicaTrabajo\PacientesClinicaTrabajo;
use App\Models\ClinicaTrabajo\EmpresaCliente;
use App\Models\ClinicaTrabajo\PuestoTrabajo;
use App\Models\ClinicaTrabajo\DatosLaboral;

// Modelos de Valoraciones
use App\Models\ClinicaTrabajo\ValoracionOcupacional;
use App\Models\ClinicaTrabajo\ValoracionNutricion;
use App\Models\ClinicaTrabajo\ValoracionErgonomica;
use App\Models\ClinicaTrabajo\ValoracionPsicologica;
use App\Models\ClinicaTrabajo\ValoracionAudiologia;
use App\Models\ClinicaTrabajo\ValoracionEspirometria;

class BuscadorPacientesController extends Controller
{
    /**
     * 1. BUSCAR PACIENTES CON FILTROS COMBINADOS
     * GET /clinica-trabajo-buscador/pacientes
     */
    public function buscar(Request $request): JsonResponse
    {
        try {
            $empresaId = $request->query('empresa_id');
            $busqueda = trim($request->query('busqueda', ''));
            $pagina = max(1, (int) $request->query('pagina', 1));
            $porPagina = min(max(1, (int) $request->query('por_pagina', 20)), 100);

            // Consulta base con eager loading optimizado
            $query = PacientesClinicaTrabajo::query()
                ->with([
                    'datosLaborales' => function ($q) {
                        $q->with(['empresaCliente', 'puestoTrabajo'])
                          ->latest('fecha_ingreso_empresa');
                    }
                ]);

            // 1. Filtro por empresa
            if ($empresaId) {
                $query->whereHas('datosLaborales', function ($q) use ($empresaId) {
                    $q->where('empresa_cliente_id', $empresaId);
                });
            }

            // 2. Filtro por texto libre (nombre, folio, CURP o número de empleado)
            if ($busqueda !== '') {
                $query->where(function ($q) use ($busqueda) {
                    $q->where('nombre', 'LIKE', "%{$busqueda}%")
                      ->orWhere('folio', 'LIKE', "%{$busqueda}%")
                      ->orWhere('curp', 'LIKE', "%{$busqueda}%")
                      ->orWhereHas('datosLaborales', function ($dq) use ($busqueda) {
                          $dq->where('numero_empleado', 'LIKE', "%{$busqueda}%");
                      });
                });
            }

            // 3. Obtener total para la paginación
            $total = $query->count();

            // 4. Obtener pacientes paginados
            $pacientes = $query->skip(($pagina - 1) * $porPagina)
                               ->take($porPagina)
                               ->get();

            // 5. Transformar resultados de forma 100% segura (Defensive Programming)
            $resultados = $pacientes->map(function ($paciente) {
                // Descartar inmediatamente si el registro es nulo o inválido
                if (!$paciente || !$paciente->id) {
                    return null;
                }

                // Obtener el último empleo de forma segura
                $ultimoEmpleo = $paciente->datosLaborales->first();
                $empresa = $ultimoEmpleo?->empresaCliente;
                $puesto = $ultimoEmpleo?->puestoTrabajo;

                return [
                    'id' => $paciente->id,
                    'folio' => $paciente->folio ?? 'N/A',
                    'nombre' => $paciente->nombre ?? 'Sin nombre',
                    'genero' => $paciente->genero ?? null,
                    'fecha_nacimiento' => $paciente->fecha_nacimiento?->format('Y-m-d'),
                    'edad' => $paciente->fecha_nacimiento?->age,
                    'curp' => $paciente->curp ?? null,
                    'numero_empleado' => $ultimoEmpleo?->numero_empleado,
                    'empresa' => $empresa ? [
                        'id' => $empresa->id,
                        'folio' => $empresa->folio,
                        'razon_social' => $empresa->razon_social,
                    ] : null,
                    'puesto' => $puesto ? [
                        'id' => $puesto->id,
                        'nombre' => $puesto->nombre,
                        'departamento' => $puesto->departamento,
                    ] : null,
                    'fecha_ingreso' => $ultimoEmpleo?->fecha_ingreso_empresa?->format('Y-m-d'),
                    'antiguedad_anos' => $this->calcularAntiguedad($ultimoEmpleo?->fecha_ingreso_empresa),
                    'ultima_evaluacion' => $this->obtenerUltimaEvaluacion($paciente->id),
                ];
            })
            ->filter(function ($item) {
                // Filtrar estrictamente: solo permitir objetos válidos con ID
                return $item !== null && isset($item['id']);
            })
            ->values(); // Reindexar el array para evitar índices huecos en el frontend

            return response()->json([
                'success' => true,
                'data' => $resultados,
                'total' => $total,
                'pagina' => $pagina,
                'por_pagina' => $porPagina,
                'total_paginas' => (int) ceil($total / $porPagina)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en BuscadorPacientesController@buscar: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar pacientes',
                'error' => config('app.debug') ? $e->getMessage() : 'Ocurrió un error inesperado.',
            ], 500);
        }
    }

    /**
     * 2. LISTAR EMPRESAS PARA EL FILTRO
     * GET /clinica-trabajo-buscador/empresas
     */
    public function empresas(Request $request): JsonResponse
    {
        try {
            $busqueda = $request->query('busqueda');
            $query = EmpresaCliente::where('activo', true);

            if ($busqueda) {
                $query->where(function ($q) use ($busqueda) {
                    $q->where('razon_social', 'LIKE', "%{$busqueda}%")
                      ->orWhere('folio', 'LIKE', "%{$busqueda}%")
                      ->orWhere('rfc', 'LIKE', "%{$busqueda}%");
                });
            }

            $empresas = $query->orderBy('razon_social')
                              ->limit(100)
                              ->get(['id', 'folio', 'razon_social', 'rfc', 'giro_actividad'])
                              ->map(function ($empresa) {
                                  return [
                                      'id' => $empresa->id,
                                      'folio' => $empresa->folio,
                                      'razon_social' => $empresa->razon_social,
                                      'rfc' => $empresa->rfc,
                                      'giro_actividad' => $empresa->giro_actividad,
                                      'total_empleados' => $empresa->datosLaborales()->count(),
                                  ];
                              });

            return response()->json([
                'success' => true,
                'data' => $empresas,
                'total' => $empresas->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar empresas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 3. OBTENER DETALLE COMPLETO DE UN PACIENTE (Para prellenar el Master)
     * GET /clinica-trabajo-buscador/paciente/{id}
     */
    public function pacienteDetalle(int $id): JsonResponse
    {
        try {
            $paciente = PacientesClinicaTrabajo::with([
                'datosLaborales' => function ($q) {
                    $q->with(['empresaCliente', 'puestoTrabajo'])->latest('fecha_ingreso_empresa');
                },
                'historialPuestos' => function ($q) {
                    $q->with('puestoTrabajo.empresaCliente')->latest('fecha_inicio');
                }
            ])->findOrFail($id);

            $ultimoEmpleo = $paciente->datosLaborales->first();
            $empresa = $ultimoEmpleo?->empresaCliente;
            $puesto = $ultimoEmpleo?->puestoTrabajo;

            return response()->json([
                'success' => true,
                'data' => [
                    'paciente_id' => $paciente->id,
                    'folio' => $paciente->folio,
                    'nombre' => $paciente->nombre,
                    'fecha_nacimiento' => $paciente->fecha_nacimiento?->format('Y-m-d'),
                    'edad' => $paciente->fecha_nacimiento?->age,
                    'genero' => $paciente->genero,
                    'estado_civil' => $paciente->estado_civil,
                    'escolaridad' => $paciente->escolaridad,
                    'curp' => $paciente->curp,
                    'tipo_sangre' => $paciente->tipo_sangre ? $paciente->tipo_sangre . $paciente->rh : null,
                    'telefono' => $paciente->telefono,
                    'celular' => $paciente->celular,
                    'email' => $paciente->email,
                    'direccion' => $paciente->direccion,
                    'alergias' => $paciente->alergias,
                    'antecedentes_medicos' => $paciente->antecedentes_medicos,
                    
                    // Datos laborales actuales
                    'empresa_cliente_id' => $empresa?->id,
                    'empresa_nombre' => $empresa?->razon_social,
                    'puesto_trabajo_id' => $puesto?->id,
                    'puesto_nombre' => $puesto?->nombre,
                    'numero_empleado' => $ultimoEmpleo?->numero_empleado,
                    'fecha_ingreso' => $ultimoEmpleo?->fecha_ingreso_empresa?->format('Y-m-d'),
                    'tipo_contrato' => $ultimoEmpleo?->tipo_contrato,
                    'turno' => $ultimoEmpleo?->turno,
                    'antiguedad_anos' => $this->calcularAntiguedad($ultimoEmpleo?->fecha_ingreso_empresa),
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Paciente no encontrado'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener detalle', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 4. HISTORIAL DE EVALUACIONES DE UN PACIENTE
     * GET /clinica-trabajo-buscador/paciente/{id}/evaluaciones
     */
    public function evaluacionesPaciente(int $id): JsonResponse
    {
        try {
            $paciente = PacientesClinicaTrabajo::findOrFail($id);
            $evaluaciones = collect();

            // Función auxiliar para agregar evaluaciones de cada tipo
            $agregar = function ($query, $tipo, $campos) use (&$evaluaciones) {
                $query->where('paciente_id', $id)
                      ->with(['empresaCliente', 'puestoTrabajo'])
                      ->orderByDesc('fecha_valoracion')
                      ->get()
                      ->each(function ($v) use ($tipo, $campos, &$evaluaciones) {
                          $evaluaciones->push([
                              'tipo' => $tipo,
                              'id' => $v->id,
                              'folio' => $v->folio,
                              'fecha' => $v->fecha_valoracion?->format('Y-m-d'),
                              'empresa' => $v->empresaCliente?->razon_social,
                              'puesto' => $v->puestoTrabajo?->nombre,
                              'aptitud' => $v->{$campos['aptitud']} ?? $v->dictamen ?? null,
                              'seguimiento' => $v->plazo_proximo_seguimiento?->format('Y-m-d') ?? $v->vigencia_hasta?->format('Y-m-d'),
                          ]);
                      });
            };

            $agregar(ValoracionOcupacional::query(), 'ocupacional', ['aptitud' => 'dictamen']);
            $agregar(ValoracionNutricion::query(), 'nutricion', ['aptitud' => 'aptitud']);
            $agregar(ValoracionErgonomica::query(), 'ergonomia', ['aptitud' => 'aptitud']);
            $agregar(ValoracionPsicologica::query(), 'psicologia', ['aptitud' => 'aptitud']);
            $agregar(ValoracionAudiologia::query(), 'audiologia', ['aptitud' => 'aptitud']);
            $agregar(ValoracionEspirometria::query(), 'espirometria', ['aptitud' => 'aptitud']);

            return response()->json([
                'success' => true,
                'data' => [
                    'paciente' => ['id' => $paciente->id, 'nombre' => $paciente->nombre, 'folio' => $paciente->folio],
                    'evaluaciones' => $evaluaciones->sortByDesc('fecha')->values(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener evaluaciones', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 5. EVALUACIONES PENDIENTES O VENCIDAS (Dashboard)
     * GET /clinica-trabajo-buscador/evaluaciones-pendientes
     */
    public function evaluacionesPendientes(Request $request): JsonResponse
    {
        try {
            $empresaId = $request->query('empresa_id');
            $diasUmbral = (int) $request->query('dias_umbral', 30);
            $hoy = Carbon::today();
            $fechaUmbral = $hoy->copy()->addDays($diasUmbral);
            $pendientes = collect();

            $modelos = [
                'ocupacional' => ValoracionOcupacional::class,
                'nutricion' => ValoracionNutricion::class,
                'ergonomia' => ValoracionErgonomica::class,
                'psicologia' => ValoracionPsicologica::class,
                'audiologia' => ValoracionAudiologia::class,
                'espirometria' => ValoracionEspirometria::class,
            ];

            foreach ($modelos as $tipo => $modelo) {
                $query = $modelo::with(['paciente', 'empresaCliente', 'puestoTrabajo']);
                if ($empresaId) $query->where('empresa_cliente_id', $empresaId);
                
                $query->get()->each(function ($v) use ($tipo, &$pendientes, $hoy, $fechaUmbral) {
                    $proximo = $v->plazo_proximo_seguimiento ?? $v->vigencia_hasta ?? null;
                    if (!$proximo) return;
                    
                    $proximo = Carbon::parse($proximo);
                    $estaVencida = $proximo->isPast();
                    $estaProxima = $proximo->between($hoy, $fechaUmbral);
                    
                    if ($estaVencida || $estaProxima) {
                        $pendientes->push([
                            'tipo' => $tipo,
                            'paciente_id' => $v->paciente_id,
                            'paciente_nombre' => $v->paciente?->nombre,
                            'empresa' => $v->empresaCliente?->razon_social,
                            'puesto' => $v->puestoTrabajo?->nombre,
                            'proximo_seguimiento' => $proximo->format('Y-m-d'),
                            'dias_restantes' => $hoy->diffInDays($proximo, false),
                            'esta_vencida' => $estaVencida,
                        ]);
                    }
                });
            }

            $ordenadas = $pendientes->sortBy([
                ['esta_vencida', 'desc'],
                ['dias_restantes', 'asc'],
            ])->values();

            return response()->json([
                'success' => true,
                'data' => $ordenadas,
                'resumen' => [
                    'total' => $ordenadas->count(),
                    'vencidas' => $ordenadas->where('esta_vencida', true)->count(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 6. ESTADÍSTICAS GENERALES
     * GET /clinica-trabajo-buscador/estadisticas
     */
    public function estadisticas(Request $request): JsonResponse
    {
        try {
            $inicioMes = Carbon::today()->startOfMonth();
            $finMes = Carbon::today()->endOfMonth();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_pacientes' => PacientesClinicaTrabajo::count(),
                    'total_empresas' => EmpresaCliente::where('activo', true)->count(),
                    'evaluaciones_mes' => [
                        'ocupacional' => ValoracionOcupacional::whereBetween('fecha_valoracion', [$inicioMes, $finMes])->count(),
                        'nutricion' => ValoracionNutricion::whereBetween('fecha_valoracion', [$inicioMes, $finMes])->count(),
                        'ergonomia' => ValoracionErgonomica::whereBetween('fecha_valoracion', [$inicioMes, $finMes])->count(),
                        'psicologia' => ValoracionPsicologica::whereBetween('fecha_valoracion', [$inicioMes, $finMes])->count(),
                        'audiologia' => ValoracionAudiologia::whereBetween('fecha_valoracion', [$inicioMes, $finMes])->count(),
                        'espirometria' => ValoracionEspirometria::whereBetween('fecha_valoracion', [$inicioMes, $finMes])->count(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // MÉTODOS PRIVADOS AUXILIARES
    // =========================================================================

    private function obtenerUltimaEvaluacion(int $pacienteId): ?array
    {
        $tablas = [
            'ocupacional' => [ValoracionOcupacional::class, 'fecha_valoracion', 'dictamen'],
            'nutricion' => [ValoracionNutricion::class, 'fecha_valoracion', 'aptitud'],
            'ergonomia' => [ValoracionErgonomica::class, 'fecha_valoracion', 'aptitud'],
            'psicologia' => [ValoracionPsicologica::class, 'fecha_valoracion', 'aptitud'],
            'audiologia' => [ValoracionAudiologia::class, 'fecha_valoracion', 'aptitud'],
            'espirometria' => [ValoracionEspirometria::class, 'fecha_valoracion', 'aptitud'],
        ];

        $ultima = null;
        foreach ($tablas as $tipo => [$modelo, $campoFecha, $campoResultado]) {
            $eval = $modelo::where('paciente_id', $pacienteId)->orderByDesc($campoFecha)->first();
            if ($eval && (!$ultima || $eval->{$campoFecha} > $ultima['fecha'])) {
                $ultima = [
                    'tipo' => $tipo,
                    'fecha' => $eval->{$campoFecha}?->format('Y-m-d'),
                    'folio' => $eval->folio,
                    'resultado' => $eval->{$campoResultado},
                ];
            }
        }
        return $ultima;
    }

    private function calcularAntiguedad(?Carbon $fechaIngreso): ?float
    {
        if (!$fechaIngreso) return null;
        return round($fechaIngreso->diffInYears(Carbon::now(), true), 1);
    }
}