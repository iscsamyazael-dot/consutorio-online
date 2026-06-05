<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\CitaPrueba;
use Carbon\Carbon;

class CitasController extends Controller
{   
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CitasPrueba::all();
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
        //Procedimiento para poner el código del país y formatearlo ejemplo: 5219992217449
        // 1. Capturamos el teléfono que viene de la petición (ej: puede venir como "9889677449" o "529889677449")
            $telefonoOriginal = $request->telefono;
        // Eliminar cualquier espacio, guion o paréntesis por si las dudas
            $telefonoLimpio = preg_replace('/[^0-9]/', '', $telefonoOriginal);
        // 2. Aplicamos la regla del código de país "52" y el "1"
            if (str_starts_with($telefonoLimpio, '52')) {
                // Si ya empieza con 52 pero no tiene el 1 después (ej: 52988...), le metemos el 1
                if (!str_starts_with($telefonoLimpio, '521')) {
                    $telefonoFinal = '521' . substr($telefonoLimpio, 2);
                } else {
                    $telefonoFinal = $telefonoLimpio; // Ya viene perfecto como 521...
                }
            } else {
                // Si viene solo a 10 dígitos (ej: 9889677449), le pegamos el 521 al principio
                $telefonoFinal = '521' . $telefonoLimpio;
            }
        // 1. URL de pruebas (Test URL) que copiaste del nodo Webhook en n8n
        $urlN8n = 'http://localhost:5678/webhook-test/Cita-Creada'; 

        

        // 2. Simulamos los datos que eventualmente vendrán de tu formulario en Vue.js
        $citas = CitaPrueba::create([
            'nombre_paciente' => $request->nombre,
            'telefono' => $telefonoFinal,
            'fecha_cita' => $request->fecha,
            'hora_cita' => $request->hora,
            'estado' => $request->estado,
            'observaciones' => $request->observaciones,
        ]);
        $datosParaN8n8 = [
            'nombre_paciente'=> $citas->nombre_paciente,
            'teleono'=> $citas->telefono,
            'fecha_cita'=> Carbon::parse($citas->fecha_cita)->format('d/m/Y'),
            'hora_cita'=> $citas->hora,
            'estado'=> $citas->estado,
            'observaciones'=> $citas->observaciones
        ];
        // 3. Enviamos la petición HTTP POST hacia n8n
        $response = Http::post($urlN8n, $datosParaN8n8);
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
