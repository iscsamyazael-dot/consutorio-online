<?php

namespace App\Http\Controllers;

use App\Models\Ubicacion;
use Illuminate\Http\Request;
;
use Illuminate\View\View;

class UbicacionController extends Controller
{
    /**
     * Muestra la vista principal (formulario + tabla) de ubicaciones.
     */
    public function index()
    {
       return $ubicaciones = Ubicacion::all();
    }

    /**
     * Devuelve el listado de ubicaciones en formato JSON,
     * usado por el componente Vue para llenar la tabla.
     */
    public function listar()
    {
        // Obtenemos solo las sucursales activas ordenadas por nombre
        $ubicaciones = Ubicacion::where('activo', 1)
            ->orderBy('nombre')
            ->get();

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
}