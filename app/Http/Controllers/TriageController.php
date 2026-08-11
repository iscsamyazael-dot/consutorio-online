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
     * Display a listing of the resource.
     */
    public function index()
    {
        return Paciente::select(
            'id',
            'paciente_id',
            'nombre'
        )
        ->with([
            'triages:id,paciente_id,triage_codigo,estado,sintomas,presion,saturacion,temperatura,created_at'
        ])
        ->get();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    

    public function store(Request $request)
    {
        // 1. Validamos los parámetros que vienen desde el formulario
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'presion'     => 'nullable|string|max:20',
            'saturacion'  => 'nullable|numeric|min:0|max:100',
            'temperatura' => 'nullable|string|max:10',
            'sintomas'    => 'nullable|string',
        ]);

        // 2. Preparamos el bloque de datos que leerá tu IA para su análisis de triage
        $datosClinicos = [
            "Motivo / Síntomas: " . ($request->sintomas ?? 'No especificados'),
            "Presión Arterial: " . ($request->presion ?? 'No registrada'),
            "Saturación de Oxígeno (SpO2): " . ($request->saturacion ? $request->saturacion . '%' : 'No registrada'),
            "Temperatura Corporal: " . ($request->temperatura ? $request->temperatura . '°C' : 'No registrada')
        ];

        // 3. Inicializamos una prioridad por defecto preventiva
        $estadoDeterminado = 'verde'; 

        try {
            // Instanciamos tu servicio y llamamos al método sugerirMedicamentoLibre que procesa el JSON de 8 fases
            $iaService = new IAClinicaService();
            $resultadoIa = $iaService->sugerirMedicamentoLibre($datosClinicos);

            if ($resultadoIa && isset($resultadoIa['triage']['nivel'])) {
                // Obtenemos la clasificación clínica real devuelta por la IA: VERDE, AMARILLO, NARANJA o ROJO
                $estadoDeterminado = strtolower($resultadoIa['triage']['nivel']); 
            }
        } catch (\Exception $e) {
            \Log::error("Error de comunicación con DeepSeek al realizar Triage: " . $e->getMessage());
            // Si la IA falla, continuará con 'verde' de forma segura y no romperá el flujo del sistema
        }

        // 4. Guardamos en la base de datos con el estado asignado dinámicamente por tu IA
        $id = DB::table('triage')->insertGetId([
            'paciente_id' => $request->paciente_id,
            'presion'     => $request->presion,
            'saturacion'  => $request->saturacion,
            'temperatura' => $request->temperatura,
            'sintomas'    => $request->sintomas,
            'estado'      => $estadoDeterminado, // Aquí se guarda: 'verde', 'amarillo', 'naranja' o 'rojo'
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
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Traemos el Paciente, sus triages, y cargamos sus alertas y recomendaciones asociadas
        return Paciente::with([
            'triages', 
            'alertas',          // Relación hasMany en Paciente
            'recomendaciones'   // Relación hasManyThrough o similar en tu modelo Paciente
        ])->find($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Guarda (o actualiza) los signos vitales de un paciente desde el
     * modal rápido de "Editar signos vitales" en panelatencion.vue.
     *
     * - Si el paciente ya tiene un triage, se actualiza el más reciente
     *   (misma lógica que usa el frontend con ultimoTriage()).
     * - Si el paciente no tiene ningún triage todavía, se crea uno nuevo
     *   con un triage_codigo generado con el mismo formato que usa
     *   PacienteController@store (TRI-AÑO-0001).
     *
     * Ruta: POST /triage/guardar/{id?}  (nombre: triage.guardarRapido)
     * El {id} es el id del paciente. También se acepta como
     * 'paciente_id' en el body por si no viene en la URL.
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
        ]);

        try {
            $triage = DB::transaction(function () use ($paciente, $data) {
                // Tomamos el triage más reciente del paciente (igual que
                // ultimoTriage() en el frontend)
                $triageExistente = $paciente->triages()->latest('id')->first();

                if ($triageExistente) {
                    $triageExistente->update($data);
                    return $triageExistente;
                }

                // No tenía triage: creamos uno nuevo con código propio
                $claveTriage = $this->generarCodigoTriage();

                return Triage::create(array_merge($data, [
                    'triage_codigo'   => $claveTriage,
                    'paciente_id'     => $paciente->id,
                    'codigo_paciente' => $paciente->paciente_id,
                    'estado'          => 'verde', // nivel de triage por defecto; se ajusta desde el panel de edición
                ]));
            });

            return response()->json([
                'success' => true,
                'message' => 'Signos vitales actualizados correctamente',
                'triage'  => $triage,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en TriageController@guardarTriageRapido: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'No se pudieron guardar los signos vitales.',
            ], 500);
        }
    }

    /**
     * Genera un código TRI-AAAA-NNNN con reinicio anual, igual formato
     * que el usado en PacienteController@generarCodigoConReinicioAnual,
     * pero aplicado directamente al modelo Triage con lockForUpdate para
     * evitar duplicados si dos triages se crean al mismo tiempo.
     */
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

        // ✅ Si ya fue analizado, devolver desde BD (sin gastar tokens)
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

        // 🤖 Primera vez: llamar a DeepSeek
        $resultado = $ia->analizarTriage([
            'sintomas'    => $triage->sintomas    ?? 'No especificados',
            'presion'     => $triage->presion     ?? 'No registrada',
            'saturacion'  => $triage->saturacion  ?? '0',
            'temperatura' => $triage->temperatura ?? '0',
        ]);

        // 💾 Guardar en BD
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