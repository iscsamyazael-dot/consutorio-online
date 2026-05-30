<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use Illuminate\Http\Request;

class SpecialtyController extends Controller
{
    public function index(Request $request)
    {
        $specialties = Specialty::query()
            ->when($request->search, fn($q, $s) => $q->where('nombre', 'like', "%$s%"))
            ->paginate(12);

        return view('specialties.index', compact('specialties'));
    }

    public function create()
    {
        return view('specialties.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        Specialty::create($request->only('nombre', 'descripcion'));

        return redirect()->route('specialties.index')
            ->with('success', 'Especialidad creada correctamente.');
    }

    public function show(Specialty $specialty)
    {
        return view('specialties.show', compact('specialty'));
    }

    public function edit(Specialty $specialty)
    {
        return view('specialties.edit', compact('specialty'));
    }

    public function update(Request $request, Specialty $specialty)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $specialty->update($request->only('nombre', 'descripcion'));

        return redirect()->route('specialties.index')
            ->with('success', 'Especialidad actualizada correctamente.');
    }

    public function destroy(Specialty $specialty)
    {
        $specialty->delete();

        return redirect()->route('specialties.index')
            ->with('success', 'Especialidad eliminada.');
    }
}