<?php

namespace App\Http\Controllers\ClinicaTrabajo;

use App\Http\Controllers\Controller;
use App\Models\ClinicaTrabajo\PacientesClinicaTrabajo;
use Illuminate\Http\Request;

class PacientesClinicaTrabajoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PacientesClinicaTrabajo::with('pacienteOrigen');

        // Filtros de búsqueda
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('nombre', 'like', "%{$search}%")
                  ->orWhere('folio', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        // Filtros adicionales
        if ($request->filled('genero')) {
            $query->where('genero', $request->input('genero'));
        }

        if ($request->filled('estado_civil')) {
            $query->where('estado_civil', $request->input('estado_civil'));
        }

        $pacientes = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $pacientes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_origen_id' => 'nullable|exists:pacientes,id',
            'folio' => 'required|string|max:20|unique:pacientes_clinica_trabajo,folio',
            'nombre' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'genero' => 'nullable|string|max:20',
            'estado_civil' => 'nullable|string|max:50',
            'escolaridad' => 'nullable|string|max:100',
            'lugar_nacimiento' => 'nullable|string|max:150',
            'curp' => 'nullable|string|max:18|unique:pacientes_clinica_trabajo,curp',
            'cedula_profesional' => 'nullable|string|max:50|unique:pacientes_clinica_trabajo,cedula_profesional',
            'foto' => 'nullable|string|max:255',
            'tipo_sangre' => 'nullable|in:O,A,B,AB',
            'rh' => 'nullable|in:+, -',
            'telefono' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:pacientes_clinica_trabajo,email',
            'direccion' => 'nullable|string',
            'contacto_emergencia' => 'nullable|string|max:255',
            'relacion_contacto_emergencia' => 'nullable|string|max:100',
            'telefono_emergencia' => 'nullable|string|max:20',
            'alergias' => 'nullable|string',
            'alergia_medicamentos' => 'nullable|string',
            'transfusion_sanguinea' => 'nullable|boolean',
            'transfusion_detalle' => 'nullable|string',
            'antecedentes_medicos' => 'nullable|string',
            'antecedentes_quirurgicos' => 'nullable|string',
            'enfermedades_cronicas' => 'nullable|string',
            'medicamentos_actuales' => 'nullable|string',
            'fecha_ultima_consulta_medica' => 'nullable|date',
            'motivo_ultima_consulta_medica' => 'nullable|string|max:255',
            'edad_inicio_vida_laboral' => 'nullable|integer|min:10|max:99',
            'primer_empleo_empresa' => 'nullable|string|max:200',
            'primer_empleo_puesto' => 'nullable|string|max:150',
            'consentimiento_datos' => 'nullable|boolean',
            'whatsapp_id' => 'nullable|string|max:50',
            'qr_token' => 'nullable|string|max:36|unique:pacientes_clinica_trabajo,qr_token',
            'notas_generales' => 'nullable|string',
        ]);

        $paciente = PacientesClinicaTrabajo::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Paciente de Clínica de Trabajo creado exitosamente',
            'data' => $paciente->load('pacienteOrigen')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PacientesClinicaTrabajo $pacienteClinicaTrabajo)
    {
        return response()->json([
            'success' => true,
            'data' => $pacienteClinicaTrabajo->load([
                'pacienteOrigen',
                'datosLaborales',
                'accidentesTrabajo',
                'historialPuestos',
                'valoracionesOcupacionales'
            ])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PacientesClinicaTrabajo $pacienteClinicaTrabajo)
    {
        $validated = $request->validate([
            'paciente_origen_id' => 'nullable|exists:pacientes,id',
            'folio' => 'sometimes|required|string|max:20|unique:pacientes_clinica_trabajo,folio,'.$pacienteClinicaTrabajo->id,
            'nombre' => 'sometimes|required|string|max:255',
            'fecha_nacimiento' => 'sometimes|required|date',
            'genero' => 'nullable|string|max:20',
            'estado_civil' => 'nullable|string|max:50',
            'escolaridad' => 'nullable|string|max:100',
            'lugar_nacimiento' => 'nullable|string|max:150',
            'curp' => 'sometimes|nullable|string|max:18|unique:pacientes_clinica_trabajo,curp,'.$pacienteClinicaTrabajo->id,
            'cedula_profesional' => 'sometimes|nullable|string|max:50|unique:pacientes_clinica_trabajo,cedula_profesional,'.$pacienteClinicaTrabajo->id,
            'foto' => 'nullable|string|max:255',
            'tipo_sangre' => 'nullable|in:O,A,B,AB',
            'rh' => 'nullable|in:+, -',
            'telefono' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'email' => 'sometimes|nullable|email|max:255|unique:pacientes_clinica_trabajo,email,'.$pacienteClinicaTrabajo->id,
            'direccion' => 'nullable|string',
            'contacto_emergencia' => 'nullable|string|max:255',
            'relacion_contacto_emergencia' => 'nullable|string|max:100',
            'telefono_emergencia' => 'nullable|string|max:20',
            'alergias' => 'nullable|string',
            'alergia_medicamentos' => 'nullable|string',
            'transfusion_sanguinea' => 'nullable|boolean',
            'transfusion_detalle' => 'nullable|string',
            'antecedentes_medicos' => 'nullable|string',
            'antecedentes_quirurgicos' => 'nullable|string',
            'enfermedades_cronicas' => 'nullable|string',
            'medicamentos_actuales' => 'nullable|string',
            'fecha_ultima_consulta_medica' => 'nullable|date',
            'motivo_ultima_consulta_medica' => 'nullable|string|max:255',
            'edad_inicio_vida_laboral' => 'nullable|integer|min:10|max:99',
            'primer_empleo_empresa' => 'nullable|string|max:200',
            'primer_empleo_puesto' => 'nullable|string|max:150',
            'consentimiento_datos' => 'nullable|boolean',
            'whatsapp_id' => 'nullable|string|max:50',
            'qr_token' => 'sometimes|nullable|string|max:36|unique:pacientes_clinica_trabajo,qr_token,'.$pacienteClinicaTrabajo->id,
            'notas_generales' => 'nullable|string',
        ]);

        $pacienteClinicaTrabajo->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Paciente de Clínica de Trabajo actualizado exitosamente',
            'data' => $pacienteClinicaTrabajo->load('pacienteOrigen')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PacientesClinicaTrabajo $pacienteClinicaTrabajo)
    {
        $pacienteClinicaTrabajo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Paciente de Clínica de Trabajo eliminado exitosamente'
        ]);
    }

    /**
     * Restaurar un paciente eliminado lógicamente
     */
    public function restore($id)
    {
        $paciente = PacientesClinicaTrabajo::withTrashed()->findOrFail($id);
        $paciente->restore();

        return response()->json([
            'success' => true,
            'message' => 'Paciente de Clínica de Trabajo restaurado exitosamente',
            'data' => $paciente
        ]);
    }

    /**
     * Eliminar permanentemente un paciente
     */
    public function forceDelete($id)
    {
        $paciente = PacientesClinicaTrabajo::withTrashed()->findOrFail($id);
        $paciente->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Paciente de Clínica de Trabajo eliminado permanentemente'
        ]);
    }
}