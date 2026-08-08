<?php

namespace App\Http\Controllers;

use App\Models\ArchivoClinico;
use App\Models\Paciente;
use Illuminate\Http\Request;

class ArchivosClinicosController extends Controller
{
    /**
     * Mostrar listado de archivos clínicos.
     */
   public function index()
    {
        return ArchivoClinico::query()
            ->leftJoin('pacientes', 'archivos_clinicos.paciente_id', '=', 'pacientes.id')
            ->leftJoin('consultas', 'archivos_clinicos.consulta_id', '=', 'consultas.id')
            ->with('paciente:id,nombre')
            ->select(
                'archivos_clinicos.id',
                'archivos_clinicos.paciente_id',
                'archivos_clinicos.consulta_id',
                'archivos_clinicos.tipo_archivo',
                'archivos_clinicos.fecha_subida',
                'archivos_clinicos.Estado',
                'archivos_clinicos.archivo_url',

                // Folio del paciente
                'pacientes.paciente_id as codigo_paciente',

                // Fecha de la consulta
                'consultas.created_at as fecha_consulta'
            )
            ->orderByDesc('archivos_clinicos.id')
            ->get();
    }
    /**
     * Mostrar un archivo.
     */
    public function show(string $id)
    {
        return ArchivoClinico::query()
            ->leftJoin('pacientes', 'archivos_clinicos.paciente_id', '=', 'pacientes.id')
            ->leftJoin('consultas', 'archivos_clinicos.consulta_id', '=', 'consultas.id')
            ->with('paciente:id,nombre')
            ->select(
                'archivos_clinicos.id',
                'archivos_clinicos.paciente_id',
                'archivos_clinicos.consulta_id',
                'archivos_clinicos.tipo_archivo',
                'archivos_clinicos.fecha_subida',
                'archivos_clinicos.Estado',
                'archivos_clinicos.archivo_url',

                'pacientes.paciente_id as codigo_paciente',
                'consultas.created_at as fecha_consulta'
            )
            ->where('archivos_clinicos.id', $id)
            ->first();
    }

    /**
     * Guardar registro.
     */
    public function store(Request $request)
    {
        $paciente = Paciente::findOrFail($request->paciente_id);

        $archivo = ArchivoClinico::create([
            'paciente_id'      => $paciente->id,
            'codigo_paciente'  => $paciente->paciente_id,
            'consulta_id'      => null,
            'tipo_archivo'     => $request->tipo_archivo,
            'archivo_url'      => $request->rutaArchivo,
            'descripcion'      => $request->descripcion,
            'fecha_subida'     => $request->fecha,
            'Estado'           => $request->estado
        ]);

        return response()->json([
            'success' => true,
            'archivo' => $archivo
        ]);
    }

    public function create()
    {
    }

    public function edit(string $id)
    {
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'Estado' => 'sometimes|in:Pendiente,En Revisión,Completado,Cancelado',
            'tipo_archivo' => 'sometimes|in:Radiografía,Receta Médica,Análisis Clínico,Expediente'
        ]);

        $archivo = ArchivoClinico::findOrFail($id);

        $archivo->fill($request->only([
            'Estado',
            'tipo_archivo'
        ]));

        $archivo->save();

        return response()->json([
            'success' => true,
            'message' => 'Archivo clínico actualizado correctamente.',
            'data' => $archivo
        ]);
    }

    public function destroy(string $id)
    {
    }

    /**
     * Subir archivo clínico.
     */
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

            $file->move(
                public_path('archivos_clinicos'),
                $nombreArchivo
            );

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