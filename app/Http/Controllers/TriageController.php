<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Triage;
use App\Models\Paciente;
use Illuminate\Http\Request;

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
                'nombre',
                'apellido_paterno',
                'apellido_materno'
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
        //
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
