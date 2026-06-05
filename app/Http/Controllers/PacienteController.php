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
        $pacientes = Paciente::paginate(10);

        return view('pacientes.index', compact('pacientes'));
    }

    public function create()
    {
        return view('pacientes.create');
    }

    public function store(Request $request)
    {
        $paciente = Paciente::create([
        'nombre' => $request->nombre,
        'apellido_paterno' => $request->apellido_paterno,
        'apellido_materno' => $request->apellido_materno,
        'telefono' => $request->telefono,
        'email' => $request->email,
        'edad' => $request->edad,
        'direccion' => $request->descripcion,
        'tipo_sangre' => $request->indicaciones,
        'contacto_emergencia' => $request->contraindicaciones,
        'telefono_emergencia' => $request->efectos_secundarios,
        'curp' => $request->precio,
        'estado' => $request->requiere_receta,
        'foto' => null,
        'notas_generales' => $request->activo,
        'fecha_nacimiento' => $request->activo,
        'whatsapp_id' => null,
        'consentimiento' => null,
        'ultima_interaccion' => null,
         ]);
        
         $triage= Triage::create([
        'consulta_id' => null,
        'presion' => $request->presion,
        'saturacion' => $request->saturacion,
        'temperatura' => $request->temperatura,
        'sintomas' => $request->sintomas,
        'estado' => $request->estado,
        'nivel_urgencia' => $request->nivel_urgencia,
        'evaluacion_ia' => null,
        'requiere_medico' => null,

         ]);
         

        return response()->json([
        'success' => true,
        'message' => 'Paciente y triage creados correctamente',
        'data' => [
            'paciente' => $paciente,
            'Triage' => $triage]
        ]);

    }



    public function show(string $id)
    {
        $paciente = Paciente::findOrFail($id);

        return view('pacientes.show', compact('paciente'));
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

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente actualizado');
    }

    public function destroy(string $id)
    {
        $paciente = Paciente::findOrFail($id);

        $paciente->delete();

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente eliminado');
    }
}