<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;

use App\Models\NotaPsoapp;
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
     return Consulta::with('paciente', 'medico')->get();
    }

    /**
     * Show the form for creating a new resource.
     */

    //Funcion para obtener datos del doctor de acuerdo a tabla y relacion
    public function create($id)
    {
        $user = auth()->user();
        $medico = $user->medico?->load('especialidad');

        return view('consultas.create', [
            'pacienteId' => $id,
            'doctor' => [
                'id'           => $user->id,
                'nombre'       => $user->name,
                'cedula'       => $medico?->cedula_profesional ?? 'Pendiente',
                'especialidad' => $medico?->especialidad?->nombre ?? 'Medicina General',
            ],
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([

            'paciente_id' => 'required|exists:pacientes,id',
            'motivo' => 'nullable',
            'diagnostico' => 'nullable',
            'observaciones' => 'nullable',
            'sintomas' => 'nullable',
            'presentacion' => 'nullable|string',
            'subjetivo' => 'nullable|string',
            'objetivo' => 'nullable|string',
            'analisis' => 'nullable|string',
            'plan' => 'nullable|string',
            'pronostico' => 'nullable|string',

        ]);

        // El paciente ya existe (viene de la ruta consultaNormal/{id})
        $paciente = Paciente::findOrFail($request->paciente_id);

        // CREAR CONSULTA
        $consulta = Consulta::create([
            'folio' => 'CONS-' . time(),
            'paciente_id' => $paciente->id,
            'user_id' => Auth::id(),
            'motivo_consulta' => $request->motivo,
            'diagnostico' => $request->diagnostico,
            'observaciones' => $request->observaciones,
            'estado' => 'activa',
            'estado_consulta' => 'finalizada'
        ]); 

        NotaPsoapp::create([

            'consulta_id'      => $consulta->id,
            'consulta_folio'   => $consulta->folio,
            'evaluacion_ia_id' => $consulta->evaluacion_ia_id,
            'presentacion' => $request->presentacion,
            'subjetivo' => $request->subjetivo,
            'objetivo' => $request->objetivo,
            'analisis' => $request->analisis,
            'plan' => $request->plan,
            'pronostico' => $request->pronostico,
            'estado' => 'completada'

        ]);

        // ENVIAR WHATSAPP (solo si el paciente tiene teléfono registrado)
        if ($paciente->telefono) {
            $telefono = '52' . $paciente->telefono;
            $mensaje = "Hola {$paciente->nombre}, tu consulta médica fue registrada correctamente.";
            WhatsAppService::enviar($telefono, $mensaje);
        }

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