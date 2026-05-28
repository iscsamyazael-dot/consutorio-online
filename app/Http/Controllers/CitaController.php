<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Paciente;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    public function index() {
        // CORRECCIÓN: Obtenemos todas las citas con los datos del paciente relacionados
        $citas = Cita::with('paciente')->get();
        
        // CORRECCIÓN: Enviamos $citas a la vista para que el @forelse y los contadores funcionen
        return view('citas.index', compact('citas'));
    }

    public function getEventos() {
        $citas = Cita::with('paciente')->get();
        $eventos = [];
        foreach ($citas as $cita) {
            $eventos[] = [
                'title' => $cita->paciente ? $cita->paciente->nombre : 'Sin nombre',
                'start' => $cita->fecha_cita . 'T' . $cita->hora_cita,
            ];
        }
        return response()->json($eventos);
    }

    public function create() {
        $pacientes = Paciente::all();
        return view('citas.create', compact('pacientes'));
    }

    public function store(Request $request) {
        $request->validate([
            'paciente_id' => 'required', 
            'fecha_cita' => 'required', 
            'hora_cita' => 'required',
            'estado' => 'required' // Asegúrate de incluir el campo estado si lo usas en tu tabla
        ]);
        
        Cita::create($request->all());
        return redirect()->route('citas.index')->with('success', 'Cita creada.');
    }
}