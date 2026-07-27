<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Http\Request;
use App\Models\Triage;

class PacienteController extends Controller
{
    public function index()
    {
       return Paciente::with(['triages'])->get();
    }

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

    public function create()
    {
        return view('pacientes.create');
    }

    public function store(Request $request)
    {

        $ultimoPaciente = Paciente::latest('id')->first();// funcion para obtener el codigo del ultimo paciente registrado//
        // Calculamos el número que servirá para la clave//
        $numero = $ultimoPaciente ? $ultimoPaciente->id + 1 : 1;
        // Generamos la clave//
        $clave = 'PAC-' . date('Y') . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
        $paciente = Paciente::create([
            'paciente_id' => $clave,
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'edad' => $request->edad_anios, // Guardamos la edad en años
            'sexo' => $request->sexo,
            'direccion' => $request->direccion,
            'tipo_sangre' => $request->tipo_sangre,
            'contacto_emergencia' => $request->contacto_emergencia,
            'telefono_emergencia' => $request->telefono_emergencia,
            'curp' => $request->curp,
            'estado' =>$request->estado,
            'foto' => "null",// Guardamos la ruta de la foto en la base de datos//
            'notas_generales' => $request->notas_generales,
            'alergias' => $request->alergias,
            'antecedentes_medicos' => $request->antecedentes,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'whatsapp_id' => null,
            'consentimiento' => null,
            'ultima_interaccion' => null,
            ]);
        

        $ultimoTriage = Triage::latest('id')->first();
        // Calculamos el número que servirá para la clave
        $numero = $ultimoTriage ? $ultimoTriage->id + 1 : 1;
        // Generamos la clave
        $claveTriage = 'TRI-' . date('Y') . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
        $triage= Triage::create([
            'triage_codigo' => $claveTriage,
            'paciente_id' => $paciente->id,
            'codigo_paciente' => $paciente -> paciente_id,
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
            'Triage' => $triage]
        ]);
    }
    
    public function show(string $id)
    {
        return Paciente::with(['triages','archivos'])->find($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('pacientes.edit', compact('paciente'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $paciente->update($request->all());

        return redirect()->route('pacientes.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
         $paciente->delete();
        return back();
    }
}
