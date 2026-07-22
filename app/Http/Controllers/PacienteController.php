<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Http\Request;
use App\Models\Triage;

class PacienteController extends Controller
{
    /**
     * API: usada por el componente Vue <pacientes-index></pacientes-index>
     * para traer la tabla completa de pacientes con sus triages.
     */
    public function index()
    {
        return Paciente::with(['triages'])->get();
    }

    /**
     * Vista: renderiza pacientes.index con las tarjetas de estadísticas
     * (Total Pacientes / Pacientes con datos incompletos) usando datos reales.
     */
    public function lista()
    {
        $totalPacientes = Paciente::count();

        // Campos que un paciente debe tener completos.
        // Ajusta esta lista si algún campo no debe ser obligatorio.
        $camposObligatorios = [
            'nombre',
            'telefono',
            'email',
            'sexo',
            'fecha_nacimiento',
            'curp',
            'direccion',
            'contacto_emergencia',
            'telefono_emergencia',
            'tipo_sangre',
        ];

        $pacientesPendientes = Paciente::where(function ($query) use ($camposObligatorios) {
            foreach ($camposObligatorios as $campo) {
                $query->orWhereNull($campo)->orWhere($campo, '');
            }
        })->get();

        $totalPendientes = $pacientesPendientes->count();

        return view('pacientes.index', compact(
            'totalPacientes',
            'totalPendientes',
            'pacientesPendientes'
        ));
    }

    /**
     * Filtro de pacientes por nombre completo o paciente_id (usado por un input de búsqueda).
     */
    public function filtrar_paciente(Request $request)
    {
        $buscar = $request->buscar;

        return Paciente::whereRaw(
                "CONCAT(nombre,' ',apellido_paterno,' ',apellido_materno) LIKE ?",
                ["%{$buscar}%"]
            )
            ->orWhere('paciente_id', 'like', "%{$buscar}%")
            ->select(
                'id',
                'paciente_id',
                'nombre',
                'apellido_paterno',
                'apellido_materno'
            )
            ->limit(10)
            ->get();
    }

    /**
     * Formulario de alta de paciente.
     */
   public function create($id = null)
{
    $paciente = null;

    if ($id) {
        $paciente = Paciente::findOrFail($id);
    }

    return view('pacientes.create', compact('paciente'));
}

    /**
     * Guarda un paciente nuevo junto con su primer triage.
     */
    public function store(Request $request)
    {
        // Generamos el código del paciente (PAC-AÑO-0001)
        $ultimoPaciente = Paciente::latest('id')->first();
        $numero = $ultimoPaciente ? $ultimoPaciente->id + 1 : 1;
        $clave = 'PAC-' . date('Y') . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
        
        // Usamos updateOrCreate para actualizar el paciente si ya existe (por nombre y teléfono) o crearlo si no
        $paciente = Paciente::updateOrCreate(
            [
                'nombre' => $request->nombre,
                'telefono' => $request->telefono,
            ],

            [
                'paciente_id' => DB::raw("COALESCE(paciente_id, '{$clave}')"), // Mantiene su ID anterior si ya existía
                'email' => $request->email,
                'edad' => $request->edad_anios, // Guardamos la edad en años
                'sexo' => $request->sexo,
                'direccion' => $request->direccion,
                'tipo_sangre' => $request->tipo_sangre,
                'contacto_emergencia' => $request->contacto_emergencia,
                'telefono_emergencia' => $request->telefono_emergencia,
                'curp' => $request->curp,
                'estado' => $request->estado,
                'foto' => "null", // Guardamos la ruta de la foto en la base de datos
                'notas_generales' => $request->notas_generales,
                'alergias' => $request->alergias,
                'antecedentes_medicos' => $request->antecedentes,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'whatsapp_id' => null,
                'consentimiento' => null,
                'ultima_interaccion' => null,
            ]
         );

        // Asegurarnos de obtener el paciente fresco con su ID correcto de la BD
        $paciente = Paciente::where('nombre', $request->nombre)->where('telefono', $request->telefono)->first();

        // Generamos el código del triage (TRI-AÑO-0001)
        $ultimoTriage = Triage::latest('id')->first();
        $numero = $ultimoTriage ? $ultimoTriage->id + 1 : 1;
        $claveTriage = 'TRI-' . date('Y') . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);

        $triage = Triage::create([
            'triage_codigo' => $claveTriage,
            'paciente_id' => $paciente->id,
            'codigo_paciente' => $paciente->paciente_id,
            'presion' => $request->presion_arterial,
            'saturacion' => $request->saturacion,
            'temperatura' => $request->temperatura,
            'sintomas' => $request->sintomas,
            'estado' => 'grave',
            'nivel_urgencia' => null,
            'evaluacion_ia' => null,
            'requiere_medico' => 0,
            'frecuencia_cardiaca' => $request->frecuencia_cardiaca,
            'frecuencia_respiratoria' => $request->frecuencia_respiratoria,
            'peso' => $request->peso,
            'talla' => $request->talla,
            'motivo_consulta' => $request->motivo_consulta,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Paciente y triage creados correctamente',
            'data' => [
                'Paciente' => $paciente,
                'Triage' => $triage,
            ]
        ]);
    }

    /**
     * Muestra el detalle de un paciente (expediente) con sus triages y archivos.
     */
    public function show(string $id)
    {
        return Paciente::with(['triages', 'archivos'])->find($id);
    }

    /**
     * Formulario de edición.
     */
    public function edit(string $id)
    {
        $paciente = Paciente::findOrFail($id);

        return view('pacientes.edit', compact('paciente'));
    }

    /**
     * Actualiza un paciente existente.
     */
    public function update(Request $request, string $id)
    {
        $paciente = Paciente::findOrFail($id);

        $paciente->update($request->all());

        return redirect()->route('pacientes.index');
    }

    /**
     * Elimina un paciente.
     */
    public function destroy(string $id)
    {
        $paciente = Paciente::findOrFail($id);

        $paciente->delete();

        return back();
    }
}