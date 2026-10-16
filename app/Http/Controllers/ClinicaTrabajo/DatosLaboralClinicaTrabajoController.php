<?php

namespace App\Http\Controllers\ClinicaTrabajo;

use App\Http\Controllers\Controller;
use App\Models\ClinicaTrabajo\PacientesClinicaTrabajo;
use App\Models\ClinicaTrabajo\DatosLaboral;
use App\Models\ClinicaTrabajo\EmpresaCliente;
use App\Models\ClinicaTrabajo\PuestoTrabajo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class DatosLaboralClinicaTrabajoController extends Controller
{
    /**
     * Display a listing of the resource.
     * Requires paciente_id as query parameter
     */
    public function index(Request $request)
    {
        try {
            // Validate paciente_id from query string
            $validated = $request->validate([
                'paciente_id' => 'required|exists:pacientes_clinica_trabajo,id'
            ]);

            $paciente = PacientesClinicaTrabajo::find($validated['paciente_id']);
            $datosLaborales = $paciente->datosLaborales()->with(['empresaCliente', 'puestoTrabajo'])->get();

            return response()->json([
                'success' => true,
                'data' => $datosLaborales
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving labor data.'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     * Requires paciente_id in request body
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Validate the request including paciente_id
            $validated = $request->validate([
                'paciente_id' => 'required|exists:pacientes_clinica_trabajo,id',
                'empresa_cliente_id' => 'required|exists:empresas_cliente,id',
                'puesto_trabajo_id' => 'nullable|exists:puestos_trabajo,id',
                'numero_empleado' => 'nullable|string|max:50',
                'fecha_ingreso_empresa' => 'nullable|date',
                'tipo_contrato' => 'nullable|string|max:50',
                'turno' => 'nullable|string|max:20',
            ]);

            // Get the paciente
            $paciente = PacientesClinicaTrabajo::find($validated['paciente_id']);

            // Additional business rules
            $empresa = EmpresaCliente::find($validated['empresa_cliente_id']);
            if (!$empresa || !$empresa->activo) {
                throw ValidationException::withMessages(['empresa_cliente_id' => ['La empresa seleccionada no está activa.']]);
            }

            if (isset($validated['puesto_trabajo_id']) && $validated['puesto_trabajo_id']) {
                $puesto = PuestoTrabajo::find($validated['puesto_trabajo_id']);
                if (!$puesto || $puesto->empresa_cliente_id != $empresa->id) {
                    throw ValidationException::withMessages(['puesto_trabajo_id' => ['El puesto no pertenece a la empresa seleccionada.']]);
                }
            }

            // Create asignacion
            $asignacionData = [
                'paciente_id' => $paciente->id,
                'empresa_cliente_id' => $validated['empresa_cliente_id'],
                'puesto_trabajo_id' => $validated['puesto_trabajo_id'] ?? null,
                'numero_empleado' => $validated['numero_empleado'] ?? null,
                'fecha_ingreso_empresa' => $validated['fecha_ingreso_empresa'] ?? null,
                'tipo_contrato' => $validated['tipo_contrato'] ?? null,
                'turno' => $validated['turno'] ?? null,
            ];

            // Remove null values
            $asignacionData = array_filter($asignacionData, fn($value) => $value !== null);

            $datosLaboral = DatosLaboral::create($asignacionData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Labor assignment created successfully',
                'data' => $datosLaboral->load(['empresaCliente', 'puestoTrabajo'])
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
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the labor assignment.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $datosLaboral = DatosLaboral::with(['empresaCliente', 'puestoTrabajo'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $datosLaboral
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Labor assignment not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the labor assignment.'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Find the asignacion (datos_laborales record)
            $datosLaboral = DatosLaboral::findOrFail($id);

            // Get paciente_id for validation (but don't allow updating it)
            $paciente_id = $datosLaboral->paciente_id;

            // Validate the request (only allow updating specific fields, not paciente_id or empresa_cliente_id)
            $validated = $request->validate([
                'puesto_trabajo_id' => 'nullable|exists:puestos_trabajo,id',
                'numero_empleado' => 'nullable|string|max:50',
                'fecha_ingreso_empresa' => 'nullable|date',
                'tipo_contrato' => 'nullable|string|max:50',
                'turno' => 'nullable|string|max:20',
                // paciente_id and empresa_cliente_id are not allowed to be updated (as per spec)
            ]);

            // Additional business rules for puesto_trabajo_id if provided
            if (isset($validated['puesto_trabajo_id']) && $validated['puesto_trabajo_id']) {
                $empresa = EmpresaCliente::find($datosLaboral->empresa_cliente_id);
                $puesto = PuestoTrabajo::find($validated['puesto_trabajo_id']);
                if (!$puesto || $puesto->empresa_cliente_id != $empresa->id) {
                    throw ValidationException::withMessages(['puesto_trabajo_id' => ['El puesto no pertenece a la empresa seleccionada.']]);
                }
            }

            // Update the asignacion
            $datosLaboral->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Labor assignment updated successfully',
                'data' => $datosLaboral->load(['empresaCliente', 'puestoTrabajo'])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Labor assignment not found.'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the labor assignment.'
            ], 500);
        }
    }

    /**
     * Create method - not applicable for API
     */
    public function create()
    {
        return response()->json([
            'success' => false,
            'message' => 'No aplica para API'
        ], 404);
    }

    /**
     * Edit method - not applicable for API
     */
    public function edit($id)
    {
        return response()->json([
            'success' => false,
            'message' => 'No aplica para API'
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     * Labor assignments are edited, not deleted in this phase
     */
    public function destroy($id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Las asignaciones se editan, no se eliminan'
        ], 405);
    }
}