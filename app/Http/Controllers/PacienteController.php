<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use App\Models\Triage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PacienteController extends Controller
{
    /**
     * API: Carga rápida para tabla con paginación/límite
     */
    public function index()
    {
        // NOTA: Traer TODO sin límite frenará tu app. 
        // Si usas datatables o Vue, se recomienda paginar o seleccionar campos específicos.
        return Paciente::with(['triages' => function ($q) {
            $q->latest()->limit(1); // Opcional: solo traer el último triage si no necesitas todo el historial aquí
        }])
        ->select('id', 'paciente_id', 'nombre', 'apellido_paterno', 'apellido_materno', 'telefono')
        ->latest('id')
        ->paginate(15); 
    }

    /**
     * Búsqueda ultrarrápida tipo Autocomplete (input de búsqueda ligera)
     */
    public function buscar(Request $request)
    {
        $texto = $request->texto;

        return Paciente::select(
                'id',
                'nombre',
            )
            ->where(function ($query) use ($texto) {
                $query->where('nombre', 'like', "%{$texto}%");
            })
            ->limit(8)
            ->get()
            ->map(function ($paciente) {
                $paciente->nombre_completo = $paciente->nombre;

                return $paciente;
            });
    }

    /**
     * Filtro optimizado sin CONCAT para aprovechar índices de MySQL
     */
    public function filtrar_paciente(Request $request)
    {
        $buscar = trim($request->get('buscar'));

        if (empty($buscar)) {
            return response()->json([]);
        }

        $match = strlen($buscar) < 3 ? "{$buscar}%" : "%{$buscar}%";

        return Paciente::select('id', 'paciente_id', 'nombre', 'apellido_paterno', 'apellido_materno')
            ->where('paciente_id', 'like', $match)
            ->orWhere('nombre', 'like', $match)
            ->orWhere('apellido_paterno', 'like', $match)
            ->orWhere('apellido_materno', 'like', $match)
            ->limit(10)
            ->get();
    }

    public function lista()
    {
        $totalPacientes = Paciente::count();

        $camposObligatorios = [
            'nombre', 'telefono', 'email', 'sexo',
            'fecha_nacimiento', 'curp', 'direccion',
            'contacto_emergencia', 'telefono_emergencia', 'tipo_sangre',
        ];

        $pacientesPendientes = Paciente::where(function ($query) use ($camposObligatorios) {
            foreach ($camposObligatorios as $campo) {
                $query->orWhereNull($campo)->orWhere($campo, '');
            }
        })->get();

        return view('pacientes.index', compact(
            'totalPacientes',
            'pacientesPendientes'
        ))->with('totalPendientes', $pacientesPendientes->count());
    }

    public function create($id = null)
    {
        $paciente = $id ? Paciente::findOrFail($id) : null;
        return view('pacientes.create', compact('paciente'));
    }

    /**
     * Guarda el paciente y su triage de forma atómica y segura
     */
    public function store(Request $request)
    {
        // DB::transaction asegura que si falla el Triage, NO se guarde el Paciente a medias
        return DB::transaction(function () use ($request) {
            
            // 1. Buscamos o preparamos la creación
            $paciente = Paciente::firstOrNew([
                'nombre' => $request->nombre,
                'telefono' => $request->telefono,
            ]);

            // Si es un paciente nuevo, generamos su código PAC
            if (!$paciente->exists) {
                $nextId = (Paciente::max('id') ?? 0) + 1;
                $paciente->paciente_id = 'PAC-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            }

            // Asignamos el resto de datos
            $paciente->fill([
                'email' => $request->email,
                'edad' => $request->edad_anios,
                'sexo' => $request->sexo,
                'direccion' => $request->direccion,
                'tipo_sangre' => $request->tipo_sangre,
                'contacto_emergencia' => $request->contacto_emergencia,
                'telefono_emergencia' => $request->telefono_emergencia,
                'curp' => $request->curp,
                'estado' => $request->estado,
                'notas_generales' => $request->notas_generales,
                'alergias' => $request->alergias,
                'antecedentes_medicos' => $request->antecedentes,
                'fecha_nacimiento' => $request->fecha_nacimiento,
            ]);

            $paciente->save(); // Se guarda y ya tenemos $paciente fresco

            // 2. Generamos clave de Triage
            $nextTriageId = (Triage::max('id') ?? 0) + 1;
            $claveTriage = 'TRI-' . date('Y') . '-' . str_pad($nextTriageId, 4, '0', STR_PAD_LEFT);

            // 3. Creamos Triage asociado
            $triage = Triage::create([
                'triage_codigo' => $claveTriage,
                'paciente_id' => $paciente->id,
                'codigo_paciente' => $paciente->paciente_id,
                'presion' => $request->presion_arterial,
                'saturacion' => $request->saturacion,
                'temperatura' => $request->temperatura,
                'sintomas' => $request->sintomas,
                'estado' => 'grave',
                'requiere_medico' => 0,
                'frecuencia_cardiaca' => $request->frecuencia_cardiaca,
                'frecuencia_respiratoria' => $request->frecuencia_respiratoria,
                'peso' => $request->peso,
                'talla' => $request->talla,
                'motivo_consulta' => $request->motivo_consulta,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Paciente y triage guardados correctamente',
                'data' => [
                    'Paciente' => $paciente,
                    'Triage' => $triage,
                ]
            ]);
        });
    }

    public function show(string $id)
    {
        return Paciente::with(['triages', 'archivos'])->findOrFail($id);
    }

    public function edit(string $id)
    {
        $paciente = Paciente::findOrFail($id);
        return view('pacientes.edit', compact('paciente'));
    }

    public function update(Request $request, string $id)
    {
        $paciente = Paciente::findOrFail($id);
        $paciente->update($request->all());

        return redirect()->route('pacientes.index');
    }

    public function destroy(string $id)
    {
        $paciente = Paciente::findOrFail($id);
        $paciente->delete();

        return back();
    }
}