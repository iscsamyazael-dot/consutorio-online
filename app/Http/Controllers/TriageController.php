<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Triage;
use App\Models\Paciente;
use App\Models\AlertaClinica;       // Asegúrate de tener o crear este modelo
use App\Models\RecomendacionIA;     // Asegúrate de tener o crear este modelo
use App\Services\IAClinicaService; // Importamos tu servicio estrella

class TriageController extends Controller
{
    /**
     * Mapeo defensivo del nivel que devuelve la IA (Rojo/Naranja/Amarillo/
     * Verde) hacia los ÚNICOS 4 valores que existen en el ENUM `estado`
     * real de la tabla triage: leve, estable, grave, urgente.
     *
     * Equivalencia clínica confirmada:
     *   Rojo    -> urgente
     *   Naranja -> grave
     *   Amarillo-> estable
     *   Verde   -> leve
     *
     * Si la IA regresa un nivel que no reconocemos, lo tratamos como
     * 'urgente' para no perder urgencia por un valor inesperado.
     */
    private function mapearEstadoIa(?string $nivelIa): string
    {
        $mapaEstados = [
            'rojo'     => 'urgente',
            'naranja'  => 'grave',
            'amarillo' => 'estable',
            'verde'    => 'leve',
        ];

        return $mapaEstados[strtolower($nivelIa ?? '')] ?? 'urgente';
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Paciente::with([
                'triages' => function ($q) use ($request) {
                    $q->latest();
                    if ($request->filled('fecha')) {
                        $q->whereDate('created_at', $request->fecha);
                    }
                },
                'consultas' => function ($q) {
                    $q->latest()->limit(1); // la consulta más reciente del paciente
                }
            ]);

            if ($request->filled('fecha')) {
                $query->whereHas('triages', function ($q) use ($request) {
                    $q->whereDate('created_at', $request->fecha);
                });
            }

            $pacientes = $query->get()->map(function ($p) {
                $consulta = $p->consultas->first();

                return [
                    'id'              => $p->id,
                    'nombre'          => trim($p->nombre . ' ' . ($p->apellido ?? '')),
                    'estado_consulta' => $consulta->estado_consulta ?? null, // ya no default a 'en_proceso'
                    'triages'         => $p->triages->map(function ($t) {
                        return [
                            'id'          => $t->id,
                            'sintomas'    => $t->sintomas ?? $t->motivo_consulta ?? 'Sin síntomas',
                            'presion'     => $t->presion,
                            'saturacion'  => $t->saturacion,
                            'temperatura' => $t->temperatura,
                            'estado'      => $t->estado ?? 'leve',
                            'created_at'  => $t->created_at,
                        ];
                    })->values(),
                ];
            })
            // Oculta pacientes sin triage O sin estado_consulta (no pueden entrar en el flujo de espera/alertas)
            ->filter(fn ($p) => $p['triages']->isNotEmpty() && !is_null($p['estado_consulta']))
            ->values();

            return response()->json($pacientes, 200);

        } catch (\Throwable $e) {
            return response()->json([
                'error'   => 'Error al consultar triages',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'presion'     => 'nullable|string|max:20',
            'saturacion'  => 'nullable|numeric|min:0|max:100',
            'temperatura' => 'nullable|string|max:10',
            'sintomas'    => 'nullable|string',
        ]);

        $datosClinicos = [
            "Motivo / Síntomas: " . ($request->sintomas ?? 'No especificados'),
            "Presión Arterial: " . ($request->presion ?? 'No registrada'),
            "Saturación de Oxígeno (SpO2): " . ($request->saturacion ? $request->saturacion . '%' : 'No registrada'),
            "Temperatura Corporal: " . ($request->temperatura ? $request->temperatura . '°C' : 'No registrada')
        ];

        // FIX: la columna `estado` en la BD es un ENUM('leve','estable','grave','urgente').
        // Usar 'Verde'/'Rojo' truena el insert con "Data truncated for
        // column 'estado'" (SQLSTATE[01000] 1265) porque esos valores no
        // existen en el enum real.
        $estadoDeterminado = 'leve';

        try {
            $iaService = new IAClinicaService();
            $resultadoIa = $iaService->sugerirMedicamentoLibre($datosClinicos);

            if ($resultadoIa && isset($resultadoIa['triage']['nivel'])) {
                // Rojo/Naranja/Amarillo/Verde -> leve/estable/grave/urgente
                $estadoDeterminado = $this->mapearEstadoIa($resultadoIa['triage']['nivel']);
            }
        } catch (\Exception $e) {
            Log::error("Error de comunicación con DeepSeek al realizar Triage: " . $e->getMessage());
        }

        $id = DB::table('triage')->insertGetId([
            'paciente_id' => $request->paciente_id,
            'presion'     => $request->presion,
            'saturacion'  => $request->saturacion,
            'temperatura' => $request->temperatura,
            'sintomas'    => $request->sintomas,
            'estado'      => $estadoDeterminado, // leve, estable, grave o urgente
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Triage guardado y clasificado mediante IA con éxito',
            'id'      => $id,
            'estado'  => $estadoDeterminado
        ], 201);
    }

    public function show(string $id)
    {
        // return Paciente::with([
        //     'triages',
        //     'alertas',
        //     'recomendaciones'
        $paciente = Paciente::with([
            'triages' => fn($q) => $q->latest(),
            'alertas',
            'recomendaciones',
            'consultas' => fn($q) => $q->latest()->limit(1),
        ])->find($id);

        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        $consulta = $paciente->consultas->first();

        return response()->json([
            'id'              => $paciente->id,
            'nombre'          => trim($paciente->nombre . ' ' . ($paciente->apellido ?? '')),
            'estado_consulta' => $consulta->estado_consulta ?? null,
            'triages'         => $paciente->triages->map(function ($t) {
                return [
                    'id'          => $t->id,
                    'sintomas'    => $t->sintomas ?? $t->motivo_consulta ?? 'Sin síntomas',
                    'presion'     => $t->presion,
                    'saturacion'  => $t->saturacion,
                    'temperatura' => $t->temperatura,
                    'estado'      => $t->estado ?? 'leve',
                    'created_at'  => $t->created_at,
                ];
            })->values(),
            'alertas'         => $paciente->alertas,
            'recomendaciones' => $paciente->recomendaciones,
        ]);
    }

    public function edit(string $id) {}

    public function update(Request $request, string $id) {}

    public function destroy(string $id) {}

    /**
     * Guarda los signos vitales de un paciente desde el modal rápido de
     * "Editar signos vitales" en panelatencion.vue.
     *
     * CAMBIO: cada llamada crea un registro NUEVO en `triage` (no
     * sobreescribe el último, para conservar el histórico).
     *
     * MEJORA: si no llega estado_triage explícito, y hay motivo_consulta
     * o sintomas, se le pregunta a la IA (mismo flujo que store()) y se
     * usa el nivel que regrese. Si no hay motivo/síntomas o la IA falla,
     * cae al estado del triage anterior; si nunca hubo uno, 'leve'.
     *
     * FIX: enum real de `estado` es leve/estable/grave/urgente (antes
     * causaba SQLSTATE[01000] 1265 Data truncated al mandar 'Rojo').
     *
     * Ruta: POST /triage/guardar/{id?} (nombre: triage.guardarRapido)
     */
    public function guardarTriageRapido(Request $request, $id = null)
    {
        $pacienteId = $id ?? $request->input('paciente_id');

        if (!$pacienteId) {
            return response()->json([
                'success' => false,
                'message' => 'Falta el id del paciente.',
            ], 422);
        }

        $paciente = Paciente::findOrFail($pacienteId);

        $data = $request->validate([
            'presion'                 => 'nullable|string|max:20',
            'saturacion'              => 'nullable|numeric|min:0|max:100',
            'temperatura'             => 'nullable|numeric',
            'frecuencia_cardiaca'     => 'nullable|numeric',
            'frecuencia_respiratoria' => 'nullable|numeric',
            'peso'                    => 'nullable|numeric',
            'talla'                   => 'nullable|numeric',
            'motivo_consulta'         => 'nullable|string',
            'sintomas'                => 'nullable|string',
            'estado_triage'           => 'nullable|string|in:leve,estable,grave,urgente',
        ]);

        $estadoExplicito = $data['estado_triage'] ?? null;
        unset($data['estado_triage']);

        try {
            $triage = DB::transaction(function () use ($paciente, $data, $estadoExplicito) {
                $claveTriage = $this->generarCodigoTriage();

                $estado = $estadoExplicito;

                if (!$estado) {
                    $motivo = $data['motivo_consulta'] ?? $data['sintomas'] ?? null;

                    if ($motivo) {
                        try {
                            $datosClinicos = [
                                "Motivo / Síntomas: " . $motivo,
                                "Presión Arterial: " . ($data['presion'] ?? 'No registrada'),
                                "Saturación de Oxígeno (SpO2): " . (isset($data['saturacion']) ? $data['saturacion'] . '%' : 'No registrada'),
                                "Temperatura Corporal: " . (isset($data['temperatura']) ? $data['temperatura'] . '°C' : 'No registrada'),
                            ];

                            $iaService = new IAClinicaService();
                            $resultadoIa = $iaService->sugerirMedicamentoLibre($datosClinicos);

                            if ($resultadoIa && isset($resultadoIa['triage']['nivel'])) {
                                $estado = $this->mapearEstadoIa($resultadoIa['triage']['nivel']);
                            }
                        } catch (\Exception $e) {
                            Log::error('Error de comunicación con DeepSeek en guardarTriageRapido: ' . $e->getMessage());
                        }
                    }

                    // Si no hay estado explícito ni evaluado por la IA, asignamos uno por defecto ('leve')
                    // o puedes cambiarlo según tus requerimientos clínicos.
                    if (!$estado) {
                        $estado = 'leve'; 
                    }
                }

                return Triage::create(array_merge($data, [
                    'triage_codigo'   => $claveTriage,
                    'paciente_id'     => $paciente->id,
                    'codigo_paciente' => $paciente->paciente_id,
                    'estado'          => $estado,
                ]));
            });

            return response()->json([
                'success' => true,
                'message' => 'Se agregó un nuevo registro de triage',
                'triage'  => $triage,
            ]);

        } catch (\Exception $e) {
            Log::error('Error en TriageController@guardarTriageRapido: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'No se pudieron guardar los signos vitales.',
            ], 500);
        }
    }

    private function generarCodigoTriage(): string
    {
        $anioActual = date('Y');

        $ultimoRegistro = Triage::where('triage_codigo', 'LIKE', "TRI-{$anioActual}-%")
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        $numero = $ultimoRegistro
            ? ((int) substr($ultimoRegistro->triage_codigo, -4)) + 1
            : 1;

        return 'TRI-' . $anioActual . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    public function analizarIA(int $pacienteId, IAClinicaService $ia): JsonResponse
    {
        $paciente = Paciente::with([
            'triages' => fn($q) => $q->latest()->limit(1)
        ])->find($pacienteId);

        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        $triage = $paciente->triages->first();

        if (!$triage) {
            return response()->json(['error' => 'Sin triage registrado'], 404);
        }

        if ($triage->nivel_urgencia && $triage->evaluacion_ia) {
            $evaluacion = json_decode($triage->evaluacion_ia, true);
            return response()->json([
                'paciente_id'   => $paciente->id,
                'triage_id'     => $triage->id,
                'created_at'    => $triage->created_at,
                'prioridad'     => $triage->nivel_urgencia,
                'estado'        => $triage->estado,
                'justificacion' => $evaluacion['justificacion'] ?? '',
                'fuente'        => 'cache_bd',
            ]);
        }

        $resultado = $ia->analizarTriage([
            'sintomas'    => $triage->sintomas    ?? 'No especificados',
            'presion'     => $triage->presion     ?? 'No registrada',
            'saturacion'  => $triage->saturacion  ?? '0',
            'temperatura' => $triage->temperatura ?? '0',
        ]);

        $triage->update([
            'nivel_urgencia' => $resultado['prioridad'],
            'estado'         => $resultado['estado'],
            'evaluacion_ia'  => json_encode([
                'estado'        => $resultado['estado'],
                'justificacion' => $resultado['justificacion'],
            ]),
        ]);

        return response()->json([
            'paciente_id'   => $paciente->id,
            'triage_id'     => $triage->id,
            'created_at'    => $triage->created_at,
            'prioridad'     => $resultado['prioridad'],
            'estado'        => $resultado['estado'],
            'justificacion' => $resultado['justificacion'],
            'fuente'        => $resultado['fuente'],
        ]);
    }
}