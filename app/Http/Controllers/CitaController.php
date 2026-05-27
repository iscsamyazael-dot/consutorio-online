<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Paciente;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    public function index()
    {
        $citas = Cita::with(['paciente', 'medico'])->orderBy('fecha_hora')->get();

        return view('citas.index', compact('citas'));
    }

    public function calendario()
    {
        return view('citas.calendario');
    }

    public function create()
    {
        $pacientes = Paciente::orderBy('nombre')->get();

        return view('citas.create', compact('pacientes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'fecha_hora' => ['required', 'date'],
            'duracion' => ['required', 'string', 'max:50'],
            'motivo' => ['required', 'string', 'max:255'],
            'tipo_cita' => ['required', 'string', 'max:100'],
            'estado' => ['required', 'string', 'max:50'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string'],
        ]);

        Cita::create($request->only([
            'paciente_id',
            'fecha_hora',
            'duracion',
            'motivo',
            'tipo_cita',
            'estado',
            'ubicacion',
            'notas',
        ]));

        return redirect()->route('citas.index')->with('success', 'Cita creada correctamente.');
    }

    public function show(string $id)
    {
        $cita = Cita::with(['paciente', 'medico'])->findOrFail($id);

        return response()->json($cita);
    }

    public function edit(string $id)
    {
        $cita = Cita::findOrFail($id);
        $pacientes = Paciente::orderBy('nombre')->get();

        return view('citas.create', compact('cita', 'pacientes'));
    }

    public function update(Request $request, string $id)
    {
        $cita = Cita::findOrFail($id);

        $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'fecha_hora' => ['required', 'date'],
            'duracion' => ['required', 'string', 'max:50'],
            'motivo' => ['required', 'string', 'max:255'],
            'tipo_cita' => ['required', 'string', 'max:100'],
            'estado' => ['required', 'string', 'max:50'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string'],
        ]);

        $cita->update($request->only([
            'paciente_id',
            'fecha_hora',
            'duracion',
            'motivo',
            'tipo_cita',
            'estado',
            'ubicacion',
            'notas',
        ]));

        return redirect()->route('citas.index')->with('success', 'Cita actualizada correctamente.');
    }

    public function destroy(string $id)
    {
        $cita = Cita::findOrFail($id);
        $cita->delete();

        return redirect()->route('citas.index')->with('success', 'Cita eliminada correctamente.');
    }
}
