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

    public function create()
    {
        return view('pacientes.create');
    }

    public function store(Request $request)
    {

        $ultimoPaciente = Paciente::latest('id')->first();
        // Calculamos el número que servirá para la clave
        $numero = $ultimoPaciente ? $ultimoPaciente->id + 1 : 1;
        // Generamos la clave
        $clave = 'PAC-' . date('Y') . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);

        $paciente = Paciente::create([
            'paciente_id' => $clave,
            'nombre' => $request->nombre,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'edad' => $request->edad_anios, // Guardamos la edad en años
            'sexo' => $request->sexo,
            'direccion' => $request->direccion,
            'tipo_sangre' => $request->tipo_sangre,
            'contacto_emergencia' => $request->contacto_emergencia,
            'telefono_emergencia' => $request->telefono_emergencia,
            'curp' => $request->curp,
            'estado' => null,
            'foto' => null,
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
        
    }

    public function edit(string $id)
    {
       
    }

    public function update(Request $request, string $id)
    {
       
    }

    public function destroy(string $id)
    {
        
    }

    
}