<?php

namespace App\Http\Controllers\ClinicaTrabajo;

use App\Http\Controllers\Controller;
use App\Models\ClinicaTrabajo\CatalogoFactorPsicosocial;
use App\Models\ClinicaTrabajo\CatalogoAlimento;
use App\Models\ClinicaTrabajo\CatalogoRiesgoErgonomico;
use Illuminate\Http\JsonResponse;

class CatalogoController extends Controller
{
    /**
     * GET /clinica-trabajo/catalogo/factores-psicosociales
     */
    public function factoresPsicosociales(): JsonResponse
    {
        $factores = CatalogoFactorPsicosocial::where('activo', true)
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get();

        return response()->json($factores);
    }

    /**
     * GET /clinica-trabajo/catalogo/alimentos
     */
    public function alimentos(): JsonResponse
    {
        $alimentos = CatalogoAlimento::where('activo', true)
            ->orderBy('grupo')
            ->orderBy('nombre')
            ->get();

        return response()->json($alimentos);
    }

    /**
     * GET /clinica-trabajo/catalogo/riesgos-ergonomicos
     */
    public function riesgosErgonomicos(): JsonResponse
    {
        $riesgos = CatalogoRiesgoErgonomico::where('activo', true)
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get();

        return response()->json($riesgos);
    }

    /**
     * GET /empresas-cliente
     * Lista empresas cliente para selects
     */
    public function empresasCliente(): JsonResponse
    {
        $empresas = \App\Models\ClinicaTrabajo\EmpresaCliente::where('activo', true)
            ->orderBy('razon_social')
            ->get(['id', 'razon_social', 'giro_actividad']);

        return response()->json($empresas);
    }
}