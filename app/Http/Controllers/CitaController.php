<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Medico;
use App\Models\Especialidad;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    public function index()
    {
        $citas = Cita::with(['paciente', 'medico', 'especialidad'])->get();
        return view('citas.index', compact('citas'));
    }

    public function getCitas()
    {
        $citas = Cita::with(['paciente', 'medico', 'especialidad'])->get();

        return response()->json(
            $citas->map(function ($cita) {
                return [
                    'id'    => $cita->id,
                    'title' => 'Cita: ' . ($cita->paciente->nombre ?? 'Sin paciente'),
                    'start' => $cita->fecha . 'T' . $cita->hora,
                    'folio'  => $cita->folio,
                    'fecha'  => $cita->fecha,
                    'hora'   => $cita->hora,
                    'estado' => $cita->estado,
                    'tipo'   => $cita->tipo,
                    'paciente' => $cita->paciente ? [
                        'id'     => $cita->paciente->id,
                        'nombre' => $cita->paciente->nombre,
                    ] : null,
                    'medico' => $cita->medico ? [
                        'id'     => $cita->medico->id,
                        'nombre' => $cita->medico->nombre,
                    ] : null,
                    'especialidad' => $cita->especialidad ? [
                        'id'     => $cita->especialidad->id,
                        'nombre' => $cita->especialidad->nombre,
                    ] : null,
                ];
            })
        );
    }

    public function create()
    {
        $pacientes      = Paciente::select('id', 'nombre')->where('estado', 'activo')->get();
        $medicos        = Medico::select('id', 'nombre', 'especialidad_id')->where('activo', 1)->get();
        $especialidades = Especialidad::select('id', 'nombre')->where('estado', 'Activo')->get();

        return view('citas.create', compact('pacientes', 'medicos', 'especialidades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'paciente_id'     => 'required|exists:pacientes,id',
            'medico_id'       => 'required|exists:medicos,id',
            'especialidad_id' => 'required|exists:especialidades,id',
            'fecha'           => 'required|date',
            'hora'            => 'required',
            'estado'          => 'required',
        ]);

        Cita::create([
            'folio'           => 'CIT-' . time(),
            'paciente_id'     => $request->paciente_id,
            'medico_id'       => $request->medico_id,
            'especialidad_id' => $request->especialidad_id,
            'fecha'           => $request->fecha,
            'hora'            => $request->hora,
            'estado'          => $request->estado,
            'tipo'            => $request->tipo,
            'observaciones'   => $request->observaciones,
        ]);

        return redirect()->route('citas.index')->with('success', 'Cita registrada correctamente.');
    }

    public function show($id)
    {
        return Cita::with(['paciente', 'medico', 'especialidad'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $cita = Cita::findOrFail($id);

        $cita->update([
            'paciente_id'     => $request->paciente_id,
            'medico_id'       => $request->medico_id,
            'especialidad_id' => $request->especialidad_id,
            'fecha'           => $request->fecha,
            'hora'            => $request->hora,
            'estado'          => $request->estado,
            'tipo'            => $request->tipo,
            'observaciones'   => $request->observaciones,
        ]);

        return response()->json($cita);
    }

    public function destroy($id)
    {
        Cita::destroy($id);
        return response()->json(['message' => 'Cita eliminada correctamente']);
    }
}