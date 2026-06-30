<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Models\HorarioMedico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicoController extends Controller
{

    public function index()
    {
        // Trae los médicos activos con sus relaciones completas
        $medicos = Medico::with(['especialidad', 'horarios'])
        ->where('activo', 1)
        ->get();

        // Retorna la respuesta de golpe a Vue
        return response()->json($medicos);
    
  
        return $medico = Medico::all();

        // Construimos la subconsulta exactamente como la definiste
        $resultado = DB::table('medicos as m')
    ->join('especialidades as e', 'm.especialidad_id', '=', 'e.id')
    ->join('horarios_medicos as h', 'm.id', '=', 'h.medico_id')
    ->where('m.activo', 1)
    ->where('e.estado', 'Activo')
    ->where('e.id', 2)
    ->select(
        'm.id as medico_id',
        'm.folio as medico_folio',
        'm.nombre as medico',
        'e.nombre as especialidad',
        DB::raw("GROUP_CONCAT(
            CASE h.dia_semana
                WHEN 1 THEN 'Lunes'
                WHEN 2 THEN 'Martes'
                WHEN 3 THEN 'Miércoles'
                WHEN 4 THEN 'Jueves'
                WHEN 5 THEN 'Viernes'
                WHEN 6 THEN 'Sábado'
                WHEN 0 THEN 'Domingo'
            END
            ORDER BY h.dia_semana ASC
            SEPARATOR ', '
        ) as dias_disponibles"),
        DB::raw("TIME_FORMAT(MIN(h.hora_inicio), '%H:%i') as hora_inicio"),
        DB::raw("TIME_FORMAT(MAX(h.hora_fin), '%H:%i') as hora_fin"),
        DB::raw("MAX(h.duracion_consulta) as duracion_minutos")
    )
    ->groupBy('m.id', 'm.folio', 'm.nombre', 'e.nombre')
    ->get();

            // Retornamos los datos al componente Vue
            return response()->json($resultado);



    }

    public function store(Request $request)
    {
        // A. VALIDACIÓN DE LOS DATOS
        $request->validate([
            'nombre'             => 'required|string|max:255',
            'cedula_profesional' => 'nullable|string|max:50',
            // Verifica que el ID seleccionado exista en tu tabla `especialidades` y esté 'Activo'
            'especialidad'       => 'required|exists:especialidades,id,estado,Activo',
            'hora_entrada'       => 'required',
            'hora_salida'        => 'required',
            'dias_laborales'     => 'required|array|min:1',
        ]);

        // Mapeo para traducir el texto del Blade (Lunes, Martes...) al tinyint (1, 2...) de tu tabla `horarios_medicos`
        $mapeoDias = [
            'Lunes'     => 1,
            'Martes'    => 2,
            'Miércoles' => 3,
            'Jueves'    => 4,
            'Viernes'   => 5,
            'Sábado'    => 6,
            'Domingo'   => 7
        ];

        // B. INICIAR TRANSACCIÓN
        DB::beginTransaction();

        try {
            // 1. Autogenerar un folio único para cumplir con el NOT NULL de tu tabla `medicos`
            $folioMedico = 'MEDI-' . strtoupper(substr(uniqid(), -8));

            // 2. Insertar el registro principal en la tabla `medicos`
            $medico = Medico::create([
                'folio'              => $folioMedico,
                'nombre'             => $request->nombre,
                'cedula_profesional' => $request->cedula_profesional,
                'especialidad_id'    => $request->especialidad, // Aquí guardamos la llave foránea
                'activo'             => 1, // 1 = Activo por defecto
            ]);

            // 3. Iterar los días que el usuario seleccionó y guardarlos en `horarios_medicos`
            foreach ($request->dias_laborales as $diaNombre) {
                // Buscamos el número equivalente (si es Lunes pone 1, etc.)
                $diaNumero = $mapeoDias[$diaNombre] ?? 1;

                HorarioMedico::create([
                    'medico_id'         => $medico->id, // El ID autoincrementable que se acaba de generar arriba
                    'dia_semana'        => $diaNumero,
                    'hora_inicio'       => $request->hora_entrada,
                    'hora_fin'          => $request->hora_salida,
                    'duracion_consulta' => 30, // 30 minutos por defecto como pide tu estructura
                ]);
            }

            // C. CONFIRMAR CAMBIOS: Si todo se ejecutó sin errores, se guardan los datos en la BD permanentemente
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '¡Médico y horarios registrados con éxito!',
                'folio' => $folioMedico,
                'medico_id' => $medico->id
            ], 201);

        } catch (\Exception $e) {
            // D. REVERTIR CAMBIOS: Si algo falla (ej. error de sintaxis), se deshace todo en ambas tablas
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el registro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
    }

    public function update(Request $request, $id)
    {
    }

    public function destroy($id)
    {
    }

    public function filtrar_medico(Request $request)
    {
        $buscar = $request->buscar;
    
        return Medico::where('nombre', 'like', "%{$buscar}%")
            ->orWhere('especialidad', 'like', "%{$buscar}%")
            ->orWhere('folio', 'like', "%{$buscar}%")
            ->select(
                'folio',
                'nombre', 
                'cedula_profesional', 
                'especialidad_id', 
            )
            ->get();
    }

}