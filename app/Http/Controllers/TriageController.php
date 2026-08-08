<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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