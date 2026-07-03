<?php

namespace App\Http\Controllers;

use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class UbicacionController extends Controller
{
    /**
     * Muestra la vista principal (formulario + tabla) de ubicaciones.
     */
    public function index(): View
    {
        $ubicaciones = Ubicacion::orderBy('folio_sucursal','DESC')->get();

        return view('ubicaciones.index', compact('ubicaciones'));
    }

    /**
     * Devuelve el listado de ubicaciones en formato JSON,
     * usado por el componente Vue para llenar la tabla.
     */
    public function listar(): JsonResponse
    {
        $ubicaciones = Ubicacion::orderBy('folio_sucursal','ASC')->get();

        return response()->json($ubicaciones);
    }

    /**
     * Valida y guarda una nueva ubicación.
     */
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

        $ubicacion = Ubicacion::create($validated);

        if ($request->wantsJson()) {
            return response()->json($ubicacion, 201);
        }

        return redirect()
            ->route('ubicaciones.index')
            ->with('exito', 'Ubicación registrada correctamente.');
    }

    /**
     * Valida y actualiza una ubicación existente (datos y/o estado activo/inactivo).
     */
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

        return response()->json($ubicacion);
    }
}