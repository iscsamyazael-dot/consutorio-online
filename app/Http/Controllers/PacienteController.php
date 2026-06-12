<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Http\Request;

class PacienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $pacientes = \App\Models\Paciente::paginate(10);
       return view('pacientes.index',compact('pacientes'));
    }

    /* FUNCION PARA FILTRAR PACIENTES EN ARCHIVOS CLINICOS*/
    public function filtrar_paciente(Request $request)
    {
        $buscar = $request->buscar;
        return Paciente::where('nombre','like',"%{$buscar}%")
               ->orWhere('apellido_paterno','like',"%{$buscar}%")
               ->orWhere('apellido_materno','like',"%{$buscar}%")
               ->orWhere('paciente_id','like',"%{$buscar}%")
               ->select(
                    'id',
                    'paciente_id',
                    'nombre',
                    'apellido_paterno',
                    'apellido_materno'
                )
                ->limit(10)
                ->get();
    }




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    { 
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Paciente::create($request->all());

        return redirect()->route('pacientes.index')
            ->with('success','Paciente registrado');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('pacientes.show', compact('paciente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('pacientes.edit', compact('paciente'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $paciente->update($request->all());

        return redirect()->route('pacientes.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
         $paciente->delete();
        return back();
    }
}
