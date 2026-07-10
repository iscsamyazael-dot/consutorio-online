<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\Consulta;
use App\Models\ConsultaTranscripcion;


use App\Services\IAClinicaService;

class ConsultaIAController extends Controller
{   
     protected $iaClinicaService;

    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    public function __construct(IAClinicaService $iaClinicaService){
        $this->iaClinicaService = $iaClinicaService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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

        try{
             /* ---------------Codigo para  guardar la consulta IA en la tabla ConsultaIA----------- */
        if($request->iniciar_consulta){
            
            //Obtenemos el ID de la ultima consulta//
            $ultimaConsulta = Consulta::latest('id')->first();
            //Calculamos el numero que servira para el folio//
            $numero = $ultimaConsulta ? $ultimaConsulta -> id + 1:1;
            //Generamos el folio//
            $folio = 'CONS-'.date('Y').'-'.str_pad($numero,4,'0',STR_PAD_LEFT);


            $consulta = new Consulta(); /*Declaración del modelo */
            $consulta -> paciente_id = 1;
            $consulta -> folio = $folio;
            $consulta -> user_id = 1;
            $consulta -> motivo_consulta = 'Consulta Inteligente';
            $consulta -> estado = 'en_proceso';
            $consulta -> consulta_inteligente = 1;
            $consulta->session_uuid = Str::uuid();
            $consulta -> save();
            return response()->json([
            'success' => true,
            'consulta_id' => $consulta->id,
            'consulta_folio' => $consulta -> folio,
            'session_uuid' => $consulta->session_uuid
        ]);
        }

        /*Código para buscar una consulta existente*/
        $consulta = Consulta::find(
            $request->consulta_id
        );
        
        if(!$consulta){
            return response()->json([
                'success' => false,
                'error' => 'Consulta no encontrada'
            ]);
        }


        /* ---------------Codigo para  guardar la transcripción en la tabla transcripcíón ----------- */
        if($request -> transcripcion){
            $transcripcion = new ConsultaTranscripcion();
            $transcripcion->consulta_id = $consulta->id;
            $transcripcion-> consulta_folio = $consulta->folio;
            $transcripcion->session_uuid = $consulta->session_uuid;
            $transcripcion->mensaje = $request->transcripcion;
            $transcripcion->tipo_usuario = 'paciente';
            $transcripcion->save();

             /*
                |--------------------------------------------------------------------------
                | ANALIZAR TRANSCRIPCIÓN CON IA
                |--------------------------------------------------------------------------
                */
            $this->iaClinicaService ->analizarTranscripcion(
                        $request->transcripcion,
                        $consulta
                    );
            } 
        

        /*Retornamos el JSON para finalizar y enviar todo a las tablas de la base de datos */
        return response()-> json([
            'success' => true,
            'consulta_id' => $consulta->id,
            'consulta_folio' => $consulta->folio,
            'session_uuid' => $consulta->session_uuid
        ]);
        }catch(\Exception $e){
            return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
