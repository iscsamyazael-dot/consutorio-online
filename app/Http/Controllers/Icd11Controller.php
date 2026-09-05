<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\IcdApiService;

class Icd11Controller extends Controller
{
    public function buscar(Request $request, IcdApiService $icdApiService)
    {
        $validated = $request->validate([
            'texto' => 'required|string|min:2',
        ]);

        $resultados = $icdApiService->buscar($validated['texto']);

        return response()->json([
            'success'    => true,
            'resultados' => $resultados,
        ]);
    }
}