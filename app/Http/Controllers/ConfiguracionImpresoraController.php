<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionImpresora;
use Illuminate\Http\Request;

class ConfiguracionImpresoraController extends Controller
{
    /**
     * GET /configuracion-impresora
     * Trae la configuración activa de la impresora térmica del kiosco
     * para mostrarla en el formulario de administración. Si aún no se
     * ha configurado ninguna, regresa null para que el frontend muestre
     * el formulario vacío.
     */
    public function show()
    {
        $configuracion = ConfiguracionImpresora::where('activo', 1)->first();

        return response()->json([
            'configuracion' => $configuracion,
        ]);
    }

    /**
     * POST /configuracion-impresora
     * Crea o actualiza la configuración de la impresora térmica.
     * Usa updateOrCreate por 'activo' => 1 para que siempre haya una
     * sola fila activa por tenant (mismo patrón que Empresa::first()).
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'nombre'         => 'required|string|max:100',
            'ip'             => 'required|ip',
            'puerto'         => 'required|integer|min:1|max:65535',
            'ancho_papel_mm' => 'required|integer|in:58,80',
        ]);

        $configuracion = ConfiguracionImpresora::updateOrCreate(
            ['activo' => 1],
            $validated
        );

        return response()->json([
            'success'       => true,
            'configuracion' => $configuracion,
        ]);
    }
}