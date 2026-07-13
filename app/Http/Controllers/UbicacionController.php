<?php

namespace App\Http\Controllers;

use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class UbicacionController extends Controller
{
    public function index(): JsonResponse
    {
      $ubicaciones = Ubicacion::orderBy('folio_sucursal', 'asc')->get();// Obtiene todas las ubicaciones ordenadas por folio_sucursal de manera ascendente
        return response()->json($ubicaciones);// Devuelve todas las ubicaciones ordenadas por folio_sucursal en formato JSON
    }
    

    public function listar()
    {
        $ubicaciones = Ubicacion::orderBy('folio_sucursal', 'asc')->get();
        return response()->json($ubicaciones);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'folio_sucursal' => ['required', 'string', 'max:20', 'unique:ubicaciones,folio_sucursal'],// Valida que el folio_sucursal sea único en la tabla ubicaciones
            'nombre' => ['required', 'string', 'max:100'],// Valida que el nombre sea obligatorio, de tipo string y con un máximo de 100 caracteres
            'direccion' => ['required', 'string', 'max:255'],// Valida que la dirección sea obligatoria, de tipo string y con un máximo de 255 caracteres
            'horario_apertura' => ['nullable', 'date_format:H:i'],// Valida que el horario de apertura sea opcional y tenga el formato de hora HH:MM
            'horario_cierre' => ['nullable', 'date_format:H:i'],// Valida que el horario de cierre sea opcional y tenga el formato de hora HH:MM
            'activo' => ['nullable', 'boolean'],// Valida que el campo activo sea opcional y de tipo booleano
        ]);

        Ubicacion::create($validated);

        return redirect()
            ->route('ubicaciones.index')// Redirige a la ruta de índice de ubicaciones después de crear una nueva ubicación
            ->with('exito', 'Ubicación registrada correctamente.');// Agrega un mensaje de éxito a la sesión para mostrarlo en la vista
    }

    public function update(Request $request, $id): JsonResponse
    {
        $ubicacion = Ubicacion::findOrFail($id);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],// Valida que el nombre sea obligatorio, de tipo string y con un máximo de 100 caracteres
            'direccion' => ['required', 'string', 'max:255'],// Valida que la dirección sea obligatoria, de tipo string y con un máximo de 255 caracteres
            'horario_apertura' => ['nullable', 'date_format:H:i'],// Valida que el horario de apertura sea opcional y tenga el formato de hora HH:MM
            'horario_cierre' => ['nullable', 'date_format:H:i'],// Valida que el horario de cierre sea opcional y tenga el formato de hora HH:MM
            'activo' => ['required', 'boolean'],// Valida que el campo activo sea obligatorio y de tipo booleano
        ]);

        $ubicacion->update($validated);// Actualiza la ubicación con los datos validados del formulario

        return response()->json([
            'message' => 'Ubicación actualizada correctamente.',// Devuelve un mensaje de éxito en formato JSON
            'data' => $ubicacion
        ]);
    }
}