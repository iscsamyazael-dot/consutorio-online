<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Http\Request;


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
        Paciente::create($request->all());

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente registrado');
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