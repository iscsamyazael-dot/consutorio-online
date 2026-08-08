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
            // FIX: primero se guarda el archivo NUEVO. Solo si guardarImagenLogo()
            // tiene éxito se borra el logo anterior. Antes se borraba primero y,
            // si move() fallaba a la mitad, el registro se quedaba sin logo viejo
            // NI nuevo (columna imagen apuntando a un archivo inexistente).
            $nombreAnterior = $ubicacion->imagen;

            $validated['imagen'] = $this->guardarImagenLogo(
                $request->file('imagen'),
                $validated['nombre']
            );

            if ($nombreAnterior) {
                $rutaAnterior = public_path('personalisarperfil/' . $nombreAnterior);
                if (file_exists($rutaAnterior)) {
                    @unlink($rutaAnterior);
                }
            }
        }

        $ubicacion->update($validated);

        return response()->json([
            'message' => 'Ubicación actualizada correctamente.',
            'data' => $ubicacion->fresh(),
        ]);
    }

    /**
     * Guarda el logo subido en public/personalisarperfil con un nombre ÚNICO
     * y devuelve solo el nombre del archivo (lo que se guarda en la columna
     * `imagen` de la tabla ubicaciones).
     *
     * FIX: antes el nombre dependía solo de nombre-de-sucursal + año, por lo
     * que dos logos subidos el mismo año para la misma sede generaban el
     * MISMO nombre de archivo. Eso causaba que el navegador siguiera
     * mostrando el logo viejo desde caché aunque el archivo en el servidor
     * ya se hubiera reemplazado. Ahora se agrega un sufijo único (uniqid)
     * para que cada logo tenga su propia URL y nunca choque con caché.
     */
    private function guardarImagenLogo($archivo, string $nombreSucursal): string
    {
        $destino = public_path('personalisarperfil');

        if (!file_exists($destino)) {
            mkdir($destino, 0755, true);
        }

        $slug = strtolower($nombreSucursal);
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
        $slug = trim($slug, '-');

        $nombreArchivo = 'logo-' . $slug . '-' . date('Y') . '-' . uniqid() . '.' . $archivo->getClientOriginalExtension();

        $archivo->move($destino, $nombreArchivo);

        return $nombreArchivo;
    }
}