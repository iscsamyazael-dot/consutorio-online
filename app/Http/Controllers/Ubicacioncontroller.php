<?php

namespace App\Http\Controllers;

use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class UbicacionController extends Controller
{
    public function index(): View
    {
        return view('ubicaciones.index');
    }

    public function listar(): JsonResponse
    {
        $ubicaciones = Ubicacion::orderBy('folio_sucursal', 'asc')->get();
        return response()->json($ubicaciones);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'folio_sucursal' => ['required', 'string', 'max:20', 'unique:ubicaciones,folio_sucursal'],
            'nombre' => ['required', 'string', 'max:100'],
            'direccion' => ['required', 'string', 'max:255'],
            'horario_apertura' => ['nullable', 'date_format:H:i'],
            'horario_cierre' => ['nullable', 'date_format:H:i'],
            'activo' => ['nullable', 'boolean'],
        ]);

        Ubicacion::create($validated);

        return redirect()
            ->route('ubicaciones.index')
            ->with('exito', 'Ubicación registrada correctamente.');
    }

    public function update(Request $request, $id): JsonResponse
    {
        $ubicacion = Ubicacion::findOrFail($id);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'direccion' => ['required', 'string', 'max:255'],
            'horario_apertura' => ['nullable', 'date_format:H:i'],
            'horario_cierre' => ['nullable', 'date_format:H:i'],
            'activo' => ['required', 'boolean'],
        ]);

        $ubicacion->update($validated);

        return response()->json([
            'message' => 'Ubicación actualizada correctamente.',
            'data' => $ubicacion
        ]);
    }
}