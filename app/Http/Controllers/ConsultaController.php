<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;

use App\Models\Consulta;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\WhatsAppService;

class ConsultaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
     return $Consulta = Consulta::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       
        return Consulta::with('paciente', 'medico')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_paciente' => 'required',
            'telefono' => 'required',
            'motivo_consulta' => 'nullable',
            'diagnostico' => 'nullable',
            'observaciones' => 'nullable',
        ]);

        // CREAR O BUSCAR PACIENTE
        $paciente = Paciente::firstOrCreate(

            [
                'nombre' => $request->nombre_paciente
            ],

            [
                'telefono' => $request->telefono
            ]
        );

        // ACTUALIZAR TELÉFONO
        $paciente->telefono = $request->telefono;
        $paciente->save();

        // CREAR CONSULTA
        $consulta = Consulta::create([

            'folio' => 'CONS-' . time(),

            'paciente_id' => $paciente->id,

            'user_id' => Auth::id(),

            'motivo_consulta' => $request->motivo_consulta,

            'diagnostico' => $request->diagnostico,

            'observaciones' => $request->observaciones,

            'estado' => 'activa',

            'estado_consulta' => 'finalizada'
        ]);

        // ENVIAR WHATSAPP
        $telefono = '52' . $paciente->telefono;

        $mensaje = "Hola {$paciente->nombre}, tu consulta médica fue registrada correctamente.";

        WhatsAppService::enviar($telefono, $mensaje);

        return response()->json([

            'success' => true,

            'message' => 'Consulta registrada correctamente',

            'consulta' => $consulta
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Consulta::with('paciente', 'medico')->findOrFail($id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $consulta = Consulta::findOrFail($id);

        $consulta->delete();

        return response()->json([

            'success' => true,

            'message' => 'Consulta eliminada correctamente'
        ]);
    }
}