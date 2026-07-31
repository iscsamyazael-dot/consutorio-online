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
        $ubicaciones = Ubicacion::orderBy('folio_sucursal', 'asc')->get();
        return response()->json($ubicaciones);
    }

    public function listar()
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
            'telefono' => ['nullable', 'string', 'max:20'],
            'horario_apertura' => ['nullable', 'date_format:H:i'],
            'horario_cierre' => ['nullable', 'date_format:H:i'],
            'activo' => ['nullable', 'boolean'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('imagen')) {
            $validated['imagen'] = $this->guardarImagenLogo(
                $request->file('imagen'),
                $validated['nombre']
            );
        }

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
            'telefono' => ['nullable', 'string', 'max:20'],
            'horario_apertura' => ['nullable', 'date_format:H:i'],
            'horario_cierre' => ['nullable', 'date_format:H:i'],
            'activo' => ['required', 'boolean'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('imagen')) {
            // Borra el logo anterior del disco, si existía, antes de guardar el nuevo
            if ($ubicacion->imagen) {
                $rutaAnterior = public_path('personalisarperfil/' . $ubicacion->imagen);
                if (file_exists($rutaAnterior)) {
                    @unlink($rutaAnterior);
                }
            }

            $validated['imagen'] = $this->guardarImagenLogo(
                $request->file('imagen'),
                $validated['nombre']
            );
        }

        $ubicacion->update($validated);

        return response()->json([
            'message' => 'Ubicación actualizada correctamente.',
            'data' => $ubicacion
        ]);
    }

    /**
     * Guarda el logo subido en public/personalisarperfil con un nombre único
     * y devuelve solo el nombre del archivo (lo que se guarda en la columna
     * `imagen` de la tabla ubicaciones).
     */
    private function guardarImagenLogo($archivo, string $nombreSucursal): string
{
    $destino = public_path('personalisarperfil');

    if (!file_exists($destino)) {
        mkdir($destino, 0755, true);
    }

    // Convierte el nombre a un formato seguro
    $slug = strtolower($nombreSucursal);
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
    $slug = trim($slug, '-');

    $nombreArchivo = 'logo-' . $slug . '-' . date('Y') . '.' . $archivo->getClientOriginalExtension();

    $archivo->move($destino, $nombreArchivo);

    return $nombreArchivo;
}
}