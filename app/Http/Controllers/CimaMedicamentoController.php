<?php

namespace App\Http\Controllers;

use App\Services\CimaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CimaMedicamentoController extends Controller
{
    public function __construct(protected CimaService $cima)
    {
    }

    /**
     * GET /api/cima/buscar?q=paracetamol
     * Búsqueda general en CIMA (autocompletar mientras el médico escribe).
     * Esto NO toca tu tabla `medicamentos` ni tu inventario.
     */
    public function buscar(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:2']);

        $resultado = $this->cima->buscarPorNombre($request->query('q'));

        return response()->json($resultado);
    }

    /**
     * GET /api/cima/{nregistro}
     * Ficha completa de un medicamento según CIMA.
     *
     * Incluye "posologia" (sección 4.2 de la ficha técnica: Posología
     * y forma de administración), usada por RecetaInteligente.vue para
     * autocompletar "Indicaciones" y "Recomendación general" cuando el
     * médico agrega el medicamento a la receta. El médico puede editar
     * ese texto libremente antes de guardar.
     */
    public function detalle(string $nregistro): JsonResponse
    {
        $detalle = $this->cima->detalleMedicamento($nregistro);

        if (!$detalle) {
            return response()->json(['message' => 'Medicamento no encontrado'], 404);
        }

        $detalle['posologia'] = $this->cima->posologia($nregistro);

        return response()->json($detalle);
    }

    /**
     * POST /api/cima/receta
     * Body: { "texto": "Paracetamol 500 mg" }
     * Pensado para el buscador dentro de RecetaInteligente.vue.
     */
    public function buscarParaReceta(Request $request): JsonResponse
    {
        $request->validate(['texto' => 'required|string|min:2']);

        $detalle = $this->cima->buscarParaReceta($request->input('texto'));

        if (!$detalle) {
            return response()->json(['message' => 'No se encontró información para ese medicamento'], 404);
        }

        return response()->json($detalle);
    }
}