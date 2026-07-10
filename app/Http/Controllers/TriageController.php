<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Triage;
use App\Models\Paciente;

class TriageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       //Para poner en el controlador del Triage - Atención Médica - Triage                                                                                          public function index()
    
            return Paciente::select(
                'id',
                'paciente_id',
                'nombre'
            )
            ->with([
                'triages:id,paciente_id,triage_codigo,estado,sintomas,presion,saturacion,temperatura,created_at'
            ])
            ->get();
    }
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        return Paciente::with([
                'triages'])->find($id);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
