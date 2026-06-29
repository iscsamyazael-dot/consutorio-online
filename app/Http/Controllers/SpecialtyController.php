<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use Illuminate\Http\Request;

class SpecialtyController extends Controller
{
    // Vista principal
    public function index(Request $request)
    {

        return $specialties = Specialty::all();

        $specialties = Specialty::query()
            ->when($request->search, function ($query, $search) {
                $query->where('nombre', 'like', "%{$search}%");
            })
            ->paginate(12);

        return view('specialties.index', compact('specialties'));
    }

    // API para Vue (devuelve JSON)
    public function list(Request $request)
    {
        return Specialty::query()
            ->when($request->search, function ($query, $search) {
                $query->where('nombre', 'like', "%{$search}%");
            })
            ->get();
    }

    public function create()
    {
        return view('specialties.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100',
            'doctor'      => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'estado'      => 'required|in:Activo,Inactivo',
        ]);

        Specialty::create([
            'nombre'      => $request->nombre,
            'doctor'      => $request->doctor,
            'descripcion' => $request->descripcion,
            'estado'      => $request->estado,
        ]);

        return response()->json([
            'message' => 'Especialidad creada correctamente.'
        ]);
    }

    public function show(Specialty $specialty)
    {
        return response()->json($specialty);
    }

    public function edit(Specialty $specialty)
    {
        return response()->json($specialty);
    }

    public function update(Request $request, Specialty $specialty)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100',
            'doctor'      => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'estado'      => 'required|in:Activo,Inactivo',
        ]);

        $specialty->update([
            'nombre'      => $request->nombre,
            'doctor'      => $request->doctor,
            'descripcion' => $request->descripcion,
            'estado'      => $request->estado,
        ]);

        return response()->json([
            'message' => 'Especialidad actualizada correctamente.'
        ]);
    }

    public function destroy(Specialty $specialty)
    {
        $specialty->delete();

        return response()->json([
            'message' => 'Especialidad eliminada correctamente.'
        ]);
    }
}