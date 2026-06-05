<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TriageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'presion'     => 'nullable|string|max:20',
            'saturacion'  => 'nullable|numeric|min:0|max:100',
            'temperatura' => 'nullable|string|max:10',
            'sintomas'    => 'nullable|string',
            'estado'      => 'nullable|string|max:50',
        ]);

        $id = DB::table('triage')->insertGetId([
            'presion'     => $request->presion,
            'saturacion'  => $request->saturacion,
            'temperatura' => $request->temperatura,
            'sintomas'    => $request->sintomas,
            'estado'      => $request->estado,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Triage guardado correctamente',
            'id'      => $id,
        ], 201);
    }
}