<?php

namespace App\Http\Controllers\ClinicaTrabajo;

use App\Http\Controllers\Controller;
use App\Models\ClinicaTrabajo\PacientesClinicaTrabajo;
use App\Models\ClinicaTrabajo\EmpresaCliente;
use App\Models\ClinicaTrabajo\DatosLaboral;
use App\Models\ClinicaTrabajo\PuestoTrabajo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PacientesClinicaTrabajoController extends Controller
{
    // Campos que van a datos_laborales, no a pacientes_clinica_trabajo
    private const CAMPOS_LABORALES = [
        'asignacion', 'empresa_cliente_id', 'puesto_trabajo_id', 'numero_empleado',
        'fecha_ingreso_empresa', 'tipo_contrato', 'turno',
    ];

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
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'paciente_origen_id' => 'nullable|exists:pacientes,id',
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
                'rh' => 'nullable|in:+,-',
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
                'notas_generales' => 'nullable|string',
                // Prohibit folio as it's auto-generated
                'folio' => 'prohibited',
                // Laboral fields (for backward compatibility, but we'll use asignacion)
                'empresa_cliente_id' => 'nullable|exists:empresas_cliente,id',
                'puesto_trabajo_id' => 'nullable|exists:puestos_trabajo,id',
                'numero_empleado' => 'nullable|string|max:50',
                'fecha_ingreso_empresa' => 'nullable|date',
                'tipo_contrato' => 'nullable|string|max:50',
                'turno' => 'nullable|string|max:20',
                // New asignacion object
                'asignacion' => 'nullable|array',
                'asignacion.empresa_cliente_id' => 'nullable|exists:empresas_cliente,id',
                'asignacion.puesto_trabajo_id' => 'nullable|exists:puestos_trabajo,id',
                'asignacion.numero_empleado' => 'nullable|string|max:50',
                'asignacion.fecha_ingreso_empresa' => 'nullable|date',
                'asignacion.tipo_contrato' => 'nullable|string|max:50',
                'asignacion.turno' => 'nullable|string|max:20',
            ]);

            // Attempt to create patient with retry for duplicate folio
            $maxAttempts = 3;
            $attempt = 0;
            $paciente = null;

            while ($attempt < $maxAttempts) {
                try {
                    $paciente = PacientesClinicaTrabajo::create(Arr::except($validated, self::CAMPOS_LABORALES));
                    break; // Success, exit loop
                } catch (\Illuminate\Database\QueryException $e) {
                    $attempt++;
                    if ($attempt >= $maxAttempts) {
                        throw $e;
                    }
                    // Check if it's a duplicate folio error (MySQL 1062)
                    if ($e->errorInfo[1] === 1062 && str_contains($e->getMessage(), 'folio')) {
                        // Folio will be regenerated on next attempt due to creating event
                        continue;
                    }
                    // Re-throw if not a folio duplicate
                    throw $e;
                }
            }

            if (!$paciente) {
                throw new \Exception('Failed to create patient after maximum attempts');
            }

            // Handle asignacion if provided
            if ($request->has('asignacion')) {
                $asignacion = $request->input('asignacion');

                // Validate asignacion fields
                $asignacionRules = [
                    'empresa_cliente_id' => 'required|exists:empresas_cliente,id',
                    'puesto_trabajo_id' => 'nullable|exists:puestos_trabajo,id',
                    'numero_empleado' => 'nullable|string|max:50',
                    'fecha_ingreso_empresa' => 'nullable|date',
                    'tipo_contrato' => 'nullable|string|max:50',
                    'turno' => 'nullable|string|max:20',
                ];

                $asignacionValidated = Validator::make($asignacion, $asignacionRules)->validate();

                // Additional business rules
                $empresa = EmpresaCliente::find($asignacionValidated['empresa_cliente_id']);
                if (!$empresa || !$empresa->activo) {
                    throw ValidationException::withMessages(['empresa_cliente_id' => ['La empresa seleccionada no está activa.']]);
                }

                if (isset($asignacionValidated['puesto_trabajo_id']) && $asignacionValidated['puesto_trabajo_id']) {
                    $puesto = PuestoTrabajo::find($asignacionValidated['puesto_trabajo_id']);
                    if (!$puesto || $puesto->empresa_cliente_id != $empresa->id) {
                        throw ValidationException::withMessages(['puesto_trabajo_id' => ['El puesto no pertenece a la empresa seleccionada.']]);
                    }
                }

                // Create asignacion
                $asignacionData = [
                    'paciente_id' => $paciente->id,
                    'empresa_cliente_id' => $asignacionValidated['empresa_cliente_id'],
                    'puesto_trabajo_id' => $asignacionValidated['puesto_trabajo_id'] ?? null,
                    'numero_empleado' => $asignacionValidated['numero_empleado'] ?? null,
                    'fecha_ingreso_empresa' => $asignacionValidated['fecha_ingreso_empresa'] ?? null,
                    'tipo_contrato' => $asignacionValidated['tipo_contrato'] ?? null,
                    'turno' => $asignacionValidated['turno'] ?? null,
                ];

                // Remove null values
                $asignacionData = array_filter($asignacionData, fn($value) => $value !== null);

                DatosLaboral::create($asignacionData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paciente de Clínica de Trabajo creado exitosamente',
                'data' => $paciente->load(['pacienteOrigen', 'datosLaborales.empresaCliente', 'datosLaborales.puestoTrabajo'])
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the patient.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $pacienteClinicaTrabajo = PacientesClinicaTrabajo::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $pacienteClinicaTrabajo->load([
                'pacienteOrigen',
                'datosLaborales.empresaCliente',
                'datosLaborales.puestoTrabajo',
                'accidentesTrabajo',
                'historialPuestos',
                'valoracionesOcupacionales'
            ])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $pacienteClinicaTrabajo = PacientesClinicaTrabajo::findOrFail($id);

        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'paciente_origen_id' => 'nullable|exists:pacientes,id',
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
                'rh' => 'nullable|in:+,-',
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
                'notas_generales' => 'nullable|string',
                // Prohibit folio as it's auto-generated
                'folio' => 'prohibited',
                // Laboral fields (for backward compatibility)
                'empresa_cliente_id' => 'nullable|exists:empresas_cliente,id',
                'puesto_trabajo_id' => 'nullable|exists:puestos_trabajo,id',
                'numero_empleado' => 'nullable|string|max:50',
                'fecha_ingreso_empresa' => 'nullable|date',
                'tipo_contrato' => 'nullable|string|max:50',
                'turno' => 'nullable|string|max:20',
                // New asignacion object
                'asignacion' => 'nullable|array',
                'asignacion.empresa_cliente_id' => 'nullable|exists:empresas_cliente,id',
                'asignacion.puesto_trabajo_id' => 'nullable|exists:puestos_trabajo,id',
                'asignacion.numero_empleado' => 'nullable|string|max:50',
                'asignacion.fecha_ingreso_empresa' => 'nullable|date',
                'asignacion.tipo_contrato' => 'nullable|string|max:50',
                'asignacion.turno' => 'nullable|string|max:20',
            ]);

            $pacienteClinicaTrabajo->update(Arr::except($validated, self::CAMPOS_LABORALES));

            // Handle asignacion if provided
            if ($request->has('asignacion')) {
                $asignacion = $request->input('asignacion');

                // Validate asignacion fields
                $asignacionRules = [
                    'empresa_cliente_id' => 'required|exists:empresas_cliente,id',
                    'puesto_trabajo_id' => 'nullable|exists:puestos_trabajo,id',
                    'numero_empleado' => 'nullable|string|max:50',
                    'fecha_ingreso_empresa' => 'nullable|date',
                    'tipo_contrato' => 'nullable|string|max:50',
                    'turno' => 'nullable|string|max:20',
                ];

                $asignacionValidated = Validator::make($asignacion, $asignacionRules)->validate();

                // Additional business rules
                $empresa = EmpresaCliente::find($asignacionValidated['empresa_cliente_id']);
                if (!$empresa || !$empresa->activo) {
                    throw ValidationException::withMessages(['empresa_cliente_id' => ['La empresa seleccionada no está activa.']]);
                }

                if (isset($asignacionValidated['puesto_trabajo_id']) && $asignacionValidated['puesto_trabajo_id']) {
                    $puesto = PuestoTrabajo::find($asignacionValidated['puesto_trabajo_id']);
                    if (!$puesto || $puesto->empresa_cliente_id != $empresa->id) {
                        throw ValidationException::withMessages(['puesto_trabajo_id' => ['El puesto no pertenece a la empresa seleccionada.']]);
                    }
                }

                // Create asignacion (always create new, as per spec - to change company, create new asignacion)
                $asignacionData = [
                    'paciente_id' => $pacienteClinicaTrabajo->id,
                    'empresa_cliente_id' => $asignacionValidated['empresa_cliente_id'],
                    'puesto_trabajo_id' => $asignacionValidated['puesto_trabajo_id'] ?? null,
                    'numero_empleado' => $asignacionValidated['numero_empleado'] ?? null,
                    'fecha_ingreso_empresa' => $asignacionValidated['fecha_ingreso_empresa'] ?? null,
                    'tipo_contrato' => $asignacionValidated['tipo_contrato'] ?? null,
                    'turno' => $asignacionValidated['turno'] ?? null,
                ];

                // Remove null values
                $asignacionData = array_filter($asignacionData, fn($value) => $value !== null);

                DatosLaboral::create($asignacionData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paciente de Clínica de Trabajo actualizado exitosamente',
                'data' => $pacienteClinicaTrabajo->load(['pacienteOrigen', 'datosLaborales.empresaCliente', 'datosLaborales.puestoTrabajo'])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the patient.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pacienteClinicaTrabajo = PacientesClinicaTrabajo::findOrFail($id);

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