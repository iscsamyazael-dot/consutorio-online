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
use Illuminate\Database\Eloquent\ModelNotFoundException;

// ✅ Importamos la nueva clase de importación (sin Maatwebsite)
use App\Imports\ClinicaTrabajo\PacientesImport;

class PacientesClinicaTrabajoController extends Controller
{
    // Llave que va a datos_laborales, no a pacientes_clinica_trabajo
    private const CAMPOS_LABORALES = ['asignacion'];

    /**
     * Display a listing of the resource (SOLO PARA VUE / API).
     */
    public function index(Request $request)
    {
        // ✅ 1. Cargamos también la relación 'empresaCliente' dentro de 'datosLaborales'
        $query = PacientesClinicaTrabajo::with(['pacienteOrigen', 'datosLaborales.empresaCliente']);

        // Filtro por texto (nombre, folio, email)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                ->orWhere('folio', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtro por género
        if ($request->filled('genero')) {
            $query->where('genero', $request->input('genero'));
        }

        // Filtro por estado civil
        if ($request->filled('estado_civil')) {
            $query->where('estado_civil', $request->input('estado_civil'));
        }

        // ✅ 2. NUEVO: Filtro por Empresa (usando whereHas para buscar en la relación)
        if ($request->filled('empresa_id')) {
            $query->whereHas('datosLaborales', function ($q) use ($request) {
                $q->where('empresa_cliente_id', $request->input('empresa_id'));
            });
        }

        $pacientes = $query->latest()->get();

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
            $validated = $request->validate(array_merge($this->reglasPaciente(), [
                'folio' => 'prohibited',
                'asignacion' => 'nullable|array',
            ], $this->reglasAsignacionAnidada()));

            // Reintento por folio duplicado
            $maxAttempts = 3;
            $attempt = 0;
            $paciente = null;

            while ($attempt < $maxAttempts) {
                try {
                    $paciente = PacientesClinicaTrabajo::create(Arr::except($validated, self::CAMPOS_LABORALES));
                    break;
                } catch (\Illuminate\Database\QueryException $e) {
                    $attempt++;
                    if ($attempt >= $maxAttempts) {
                        throw $e;
                    }
                    if ($e->errorInfo[1] === 1062 && str_contains($e->getMessage(), 'folio')) {
                        continue;
                    }
                    throw $e;
                }
            }

            if (!$paciente) {
                throw new \Exception('Failed to create patient after maximum attempts');
            }

            if ($request->has('asignacion')) {
                $this->guardarAsignacion($request->input('asignacion'), $paciente->id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paciente de Clínica de Trabajo creado exitosamente',
                'data' => $paciente->load(['pacienteOrigen', 'datosLaborales.empresaCliente', 'datosLaborales.puestoTrabajo'])
            ], 201);
        } catch (ValidationException $e) {
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
                'message' => 'La asignación laboral no existe o no pertenece a este paciente.'
            ], 404);
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
            $validated = $request->validate(array_merge($this->reglasPaciente($pacienteClinicaTrabajo->id), [
                'folio' => 'prohibited',
                'asignacion' => 'nullable|array',
            ], $this->reglasAsignacionAnidada()));

            $pacienteClinicaTrabajo->update(Arr::except($validated, self::CAMPOS_LABORALES));

            if ($request->has('asignacion')) {
                $this->guardarAsignacion($request->input('asignacion'), $pacienteClinicaTrabajo->id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paciente de Clínica de Trabajo actualizado exitosamente',
                'data' => $pacienteClinicaTrabajo->load(['pacienteOrigen', 'datosLaborales.empresaCliente', 'datosLaborales.puestoTrabajo'])
            ]);
        } catch (ValidationException $e) {
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
                'message' => 'La asignación laboral no existe o no pertenece a este paciente.'
            ], 404);
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

    public function create($id = null)
    {
        $paciente = null;
        if ($id) {
            $paciente = PacientesClinicaTrabajo::findOrFail($id);
        }
        return view('clinica-trabajo.RegistroPaciente', compact('paciente'));
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

    public function TotalPacientesyPendientes()
    {
        $totalPacientes = PacientesClinicaTrabajo::count();

        $pacientesPendientes = PacientesClinicaTrabajo::select('id', 'nombre', 'folio')
            ->where(function ($query) {
                $query->whereNull('email')->orWhere('email', '')
                      ->orWhereNull('genero')->orWhere('genero', '')
                      ->orWhereNull('tipo_sangre')->orWhere('tipo_sangre', '')
                      ->orWhereNull('curp')->orWhere('curp', '')
                      ->orWhereNull('fecha_nacimiento')->orWhere('fecha_nacimiento', '');
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'totalPacientes' => $totalPacientes,
                'totalPendientes' => $pacientesPendientes->count(),
                'pacientesPendientes' => $pacientesPendientes
            ]
        ]);
    }

    /* ==========================================================
     |  Helpers privados
     * ========================================================== */

    /**
     * Reglas de los campos del paciente. Con id, unique ignora ese registro.
     */
    private function reglasPaciente(?int $ignorarId = null): array
    {
        $unique = fn (string $campo) => $ignorarId
            ? "unique:pacientes_clinica_trabajo,{$campo},{$ignorarId}"
            : "unique:pacientes_clinica_trabajo,{$campo}";

        return [
            'paciente_origen_id' => 'nullable|exists:pacientes,id',
            'nombre' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'genero' => 'nullable|string|max:20',
            'estado_civil' => 'nullable|string|max:50',
            'escolaridad' => 'nullable|string|max:100',
            'lugar_nacimiento' => 'nullable|string|max:150',
            'curp' => 'nullable|string|max:18|' . $unique('curp'),
            'cedula_profesional' => 'nullable|string|max:50|' . $unique('cedula_profesional'),
            'foto' => 'nullable|string|max:255',
            'tipo_sangre' => 'nullable|in:O,A,B,AB',
            'rh' => 'nullable|in:+,-',
            'telefono' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|' . $unique('email'),
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
        ];
    }

    /**
     * Reglas de la asignación laboral (sin prefijo). Todas opcionales:
     * la empresa puede quedar vacía para desvincular.
     */
    private function reglasAsignacion(): array
    {
        return [
            'id' => 'nullable|integer',
            'empresa_cliente_id' => 'nullable|exists:empresas_cliente,id',
            'puesto_trabajo_id' => 'nullable|exists:puestos_trabajo,id',
            'numero_empleado' => 'nullable|string|max:50',
            'fecha_ingreso_empresa' => 'nullable|date',
            'tipo_contrato' => 'nullable|string|max:50',
            'turno' => 'nullable|string|max:20',
        ];
    }

    /**
     * Mismas reglas con prefijo "asignacion." para validar desde el request principal.
     */
    private function reglasAsignacionAnidada(): array
    {
        $reglas = [];
        foreach ($this->reglasAsignacion() as $campo => $regla) {
            $reglas["asignacion.{$campo}"] = $regla;
        }
        return $reglas;
    }

    /**
     * Crea, actualiza o desvincula la asignación laboral del paciente.
     * - Sin empresa y con id: elimina esa asignación (solo si pertenece al paciente).
     * - Sin empresa y sin id: no hace nada.
     * - Con empresa y con id: actualiza ese registro (solo si pertenece al paciente).
     * - Con empresa y sin id: crea una nueva.
     */
    private function guardarAsignacion(array $asignacion, int $pacienteId): void
    {
        $validated = Validator::make($asignacion, $this->reglasAsignacion())->validate();
        $empresaId = $validated['empresa_cliente_id'] ?? null;
        $asignacionId = $validated['id'] ?? null;

        if (!$empresaId) {
            if ($asignacionId) {
                DatosLaboral::where('paciente_id', $pacienteId)
                    ->where('id', $asignacionId)
                    ->firstOrFail()
                    ->delete();
            }
            return;
        }

        $empresa = EmpresaCliente::find($empresaId);
        if (!$empresa || !$empresa->activo) {
            throw ValidationException::withMessages([
                'asignacion.empresa_cliente_id' => ['La empresa seleccionada no está activa.']
            ]);
        }

        if (!empty($validated['puesto_trabajo_id'])) {
            $puesto = PuestoTrabajo::find($validated['puesto_trabajo_id']);
            if (!$puesto || $puesto->empresa_cliente_id != $empresa->id) {
                throw ValidationException::withMessages([
                    'asignacion.puesto_trabajo_id' => ['El puesto no pertenece a la empresa seleccionada.']
                ]);
            }
        }

        // Sin array_filter: los null se guardan para poder quitar un puesto o un dato
        $datos = Arr::only($validated, [
            'empresa_cliente_id', 'puesto_trabajo_id', 'numero_empleado',
            'fecha_ingreso_empresa', 'tipo_contrato', 'turno',
        ]);

        if ($asignacionId) {
            DatosLaboral::where('paciente_id', $pacienteId)
                ->where('id', $asignacionId)
                ->firstOrFail()
                ->update($datos);
        } else {
            DatosLaboral::create(['paciente_id' => $pacienteId] + $datos);
        }
    }

    /**
     * ✅ MÉTODO DE IMPORTACIÓN ACTUALIZADO (Sin Maatwebsite)
     */
    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:5120'
        ]);

        try {
            // Instanciamos la clase de importación y llamamos al método process
            $importer = new PacientesImport();
            $stats = $importer->process($request->file('archivo')->getPathname());

            // Construimos un mensaje informativo basado en las estadísticas
            $message = "Proceso de importación finalizado. ";
            if ($stats['imported'] > 0) {
                $message .= "{$stats['imported']} pacientes importados exitosamente. ";
            }
            
            if ($stats['errors'] > 0) {
                $message .= "{$stats['errors']} filas fueron omitidas. ";
            }
            if (count($stats['advertencias']) > 0) {
                $message .= "Revisa los avisos antes de continuar.";
            }

            return response()->json([
                'success' => true,
                'message' => trim($message),
                'advertencias' => $stats['advertencias'],
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en importación de pacientes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}