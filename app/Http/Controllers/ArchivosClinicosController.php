<?php

namespace App\Http\Controllers;
use App\Models\Archivoclinico;
use App\Models\Paciente;
use App\Models\Consulta;
use Illuminate\Http\Request;

class ArchivosClinicosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return Archivoclinico::select(
                'id',
                'paciente_id',
                'tipo_archivo',
                'fecha_subida',
                'Estado',
                'archivo_url'
                
            )
            ->with([
                'paciente'
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
    //Función Store para el controlador Archivos Clinicos 
public function store(Request $request)
    {
        $paciente = Paciente::findOrFail($request->paciente_id);
            dd($request->all());
        $archivo = ArchivoClinico::create([
        'paciente_id' => $paciente->id,
        'codigo_paciente' => $paciente->paciente_id,
        'consulta_id' => null,
        'tipo_archivo' => $request->tipo_archivo,
        'archivo_url' => $request->rutaArchivo,
        'descripcion' => $request->descripcion,
        'fecha_subida' => $request->fecha,
        'Estado' => $request->estado
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

   public function archivoclinico(Request $request)
{
    try {

        if (!$request->hasFile('archivo')) {
            return response()->json([
                'success' => false,
                'message' => 'No se recibió ningún archivo'
            ], 400);
        }

        $file = $request->file('archivo');

        $nombreArchivo =
            $request->codigo_paciente . '_' .
            time() . '.' .
            $file->getClientOriginalExtension();

        // Guarda el archivo físico
        $file->move(
            public_path('archivos_clinicos'),
            $nombreArchivo
        );

        // Guarda en BD
        $archivo = new ArchivoClinico();

        $archivo->paciente_id = $request->paciente_id;
        $archivo->codigo_paciente = $request->codigo_paciente;
        $archivo->tipo_archivo = $request->tipo_archivo;
        $archivo->archivo_url = 'archivos_clinicos/' . $nombreArchivo;
        $archivo->fecha_subida = now();
        $archivo->Estado = $request->Estado;

        $archivo->save();

        return response()->json([
            'success' => true,
            'message' => 'Archivo guardado correctamente',
            'archivo' => $archivo
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);

    }
}


}
