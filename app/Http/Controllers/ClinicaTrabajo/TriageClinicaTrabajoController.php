<?php

namespace App\Http\Controllers\ClinicaTrabajo;

use App\Http\Controllers\Controller;
use App\Models\ClinicaTrabajo\TriageClinicaTrabajo;
use Illuminate\Http\Request;

class TriageClinicaTrabajoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TriageClinicaTrabajo::with(['pacienteClinica', 'valoracionOcupacional', 'usuario']);

        // Filtros de búsqueda
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('folio', 'like', "%{$search}%")
                  ->orWhereHas('pacienteClinica', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%");
                  });
        }

        // Filtros adicionales
        if ($request->filled('nivel_urgencia')) {
            $query->where('nivel_urgencia', $request->input('nivel_urgencia'));
        }

        if ($request->filled('requiere_medico')) {
            $query->where('requiere_medico', $request->input('requiere_medico'));
        }

        $triages = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $triages
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_clinica_id' => 'required|exists:pacientes_clinica_trabajo,id',
            'valoracion_id' => 'nullable|exists:valoraciones_ocupacionales,id',
            'usuario_triage_id' => 'required|exists:users,id',
            'fecha_triage' => 'required|date',
            'motivo_consulta' => 'nullable|string',
            'sintomas' => 'nullable|string',
            'ta_sistolica' => 'nullable|integer|min:50|max:300',
            'ta_diastolica' => 'nullable|integer|min:30|max:200',
            'fc' => 'nullable|integer|min:20|max:250',
            'fr' => 'nullable|integer|min:5|max:80',
            'spo2' => 'nullable|numeric|min:50|max:100',
            'temperatura_c' => 'nullable|numeric|min:30|max:45',
            'peso_kg' => 'nullable|numeric|min:1|max:500',
            'estatura_cm' => 'nullable|numeric|min:50|max:250',
            'imc' => 'nullable|numeric|min:5|max:100',
            'imc_clasificacion' => 'nullable|in:Bajo peso,Normal,Sobrepeso,Obesidad I,Obesidad II,Obesidad III',
            'nivel_urgencia' => 'nullable|in:estable,atencion,urgente',
            'requiere_medico' => 'nullable|boolean',
            'evaluacion_ia' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'historial_ediciones' => 'nullable|array',
        ]);

        $triage = TriageClinicaTrabajo::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Triage de Clínica de Trabajo creado exitosamente',
            'data' => $triage->load(['pacienteClinica', 'valoracionOcupacional', 'usuario'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(TriageClinicaTrabajo $triageClinicaTrabajo)
    {
        return response()->json([
            'success' => true,
            'data' => $triageClinicaTrabajo->load([
                'pacienteClinica',
                'valoracionOcupacional',
                'usuario'
            ])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TriageClinicaTrabajo $triageClinicaTrabajo)
    {
        $validated = $request->validate([
            'paciente_clinica_id' => 'sometimes|required|exists:pacientes_clinica_trabajo,id',
            'valoracion_id' => 'nullable|exists:valoraciones_ocupacionales,id',
            'usuario_triage_id' => 'sometimes|required|exists:users,id',
            'fecha_triage' => 'sometimes|required|date',
            'motivo_consulta' => 'nullable|string',
            'sintomas' => 'nullable|string',
            'ta_sistolica' => 'nullable|integer|min:50|max:300',
            'ta_diastolica' => 'nullable|integer|min:30|max:200',
            'fc' => 'nullable|integer|min:20|max:250',
            'fr' => 'nullable|integer|min:5|max:80',
            'spo2' => 'nullable|numeric|min:50|max:100',
            'temperatura_c' => 'nullable|numeric|min:30|max:45',
            'peso_kg' => 'nullable|numeric|min:1|max:500',
            'estatura_cm' => 'nullable|numeric|min:50|max:250',
            'imc' => 'nullable|numeric|min:5|max:100',
            'imc_clasificacion' => 'nullable|in:Bajo peso,Normal,Sobrepeso,Obesidad I,Obesidad II,Obesidad III',
            'nivel_urgencia' => 'nullable|in:estable,atencion,urgente',
            'requiere_medico' => 'nullable|boolean',
            'evaluacion_ia' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'historial_ediciones' => 'nullable|array',
        ]);

        $triageClinicaTrabajo->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Triage de Clínica de Trabajo actualizado exitosamente',
            'data' => $triageClinicaTrabajo->load(['pacienteClinica', 'valoracionOcupacional', 'usuario'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TriageClinicaTrabajo $triageClinicaTrabajo)
    {
        $triageClinicaTrabajo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Triage de Clínica de Trabajo eliminado exitosamente'
        ]);
    }

    /**
     * Restaurar un triage eliminado lógicamente
     */
    public function restore($id)
    {
        $triage = TriageClinicaTrabajo::withTrashed()->findOrFail($id);
        $triage->restore();

        return response()->json([
            'success' => true,
            'message' => 'Triage de Clínica de Trabajo restaurado exitosamente',
            'data' => $triage
        ]);
    }

    /**
     * Eliminar permanentemente un triage
     */
    public function forceDelete($id)
    {
        $triage = TriageClinicaTrabajo::withTrashed()->findOrFail($id);
        $triage->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Triage de Clínica de Trabajo eliminado permanentemente'
        ]);
    }
}