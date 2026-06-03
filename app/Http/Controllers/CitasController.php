<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\CitaPrueba;

class CitasController extends Controller
{   
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return "Panel de Citas de Prueba";
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
        // 1. URL de pruebas (Test URL) que copiaste del nodo Webhook en n8n
        $urlN8n = 'http://localhost:5678/webhook-test/Nueva-cita'; 

        // 2. Simulamos los datos que eventualmente vendrán de tu formulario en Vue.js
        $datosCita = [
            'id_cita'    => rand(100, 999), // ID aleatorio para la prueba
            'paciente'   => 'Juan Pérez',
            'telefono'   => '+529991234567', // Cambia por tu número real si quieres probar WhatsApp después
            'fecha_hora' => '2026-06-15 10:30:00'
        ];

        // 3. Enviamos la petición HTTP POST hacia n8n
        $response = Http::post($urlN8n, $datosCita);

        // 4. Retornamos la respuesta a la pantalla para saber qué pasó
        return response()->json([
            'status' => 'Petición enviada a n8n desde el método STORE',
            'codigo_http_n8n' => $response->status(),
            'respuesta_del_servidor_n8n' => $response->json() // n8n suele devolver un mensaje de éxito
        ]);
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
