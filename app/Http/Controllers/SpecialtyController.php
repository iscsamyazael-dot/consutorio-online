<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'imagen'      => 'nullable|image|max:2048',
        ]);

        $data = $request->only('nombre', 'descripcion');

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('especialidades', 'public');
        }

        Specialty::create($data);

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
            'imagen'      => 'nullable|image|max:2048',
        ]);

        $data = $request->only('nombre', 'descripcion');

        if ($request->hasFile('imagen')) {
            if ($specialty->imagen) {
                Storage::disk('public')->delete($specialty->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('especialidades', 'public');
        }

        $specialty->update($data);

        return redirect()->route('specialties.index')
            ->with('success', 'Especialidad actualizada correctamente.');
    }

    public function destroy(Specialty $specialty)
    {
        if ($specialty->imagen) {
            Storage::disk('public')->delete($specialty->imagen);
        }

        $specialty->delete();

        return redirect()->route('specialties.index')
            ->with('success', 'Especialidad eliminada.');
    }
}