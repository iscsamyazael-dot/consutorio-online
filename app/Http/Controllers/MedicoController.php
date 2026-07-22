<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Models\User;
use App\Models\HorarioMedico;
use App\Models\ConfiguracionMedicoSucursal; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicoController extends Controller
{
    public function index()
    {
        $medicos = Medico::with(['especialidad', 'horarios', 'configuraciones.ubicacion'])
            ->where('activo', 1)
            ->get();

        return response()->json($medicos);
    }

    public function store(Request $request)
    {
        // A. VALIDACIÓN DE LOS DATOS
        $request->validate([
            'nombre'             => 'required|string|max:255',
            'cedula_profesional' => 'nullable|string|max:50',
            'especialidad'       => 'required|exists:especialidades,id,estado,Activo',
            'hora_entrada'       => 'required',
            'hora_salida'        => 'required',
            'dias_laborales'     => 'required|array|min:1',
            'duracion_consulta'  => 'required|integer|min:5|max:240',
            
            // Campos para la nueva tabla de configuración
            'ubicacion_id'       => 'required|exists:ubicaciones,id',
            'costo_consulta'     => 'required|numeric|min:0',
        ]);

        $mapeoDias = [
            'Lunes'     => 1, 'Martes'    => 2, 'Miércoles' => 3,
            'Jueves'    => 4, 'Viernes'   => 5, 'Sábado'    => 6, 'Domingo'   => 7
        ];

        // B. INICIAR TRANSACCIÓN
        DB::beginTransaction();

        try {
            // 1. Crear el Médico principal (Genera el folio MEDI-2026-XXX en su booted solo)
            $medico = Medico::create([
                'nombre'             => $request->nombre,
                'cedula_profesional' => $request->cedula_profesional,
                'especialidad_id'    => $request->especialidad, 
                'activo'             => 1, 
            ]);

            // 2. Crear la configuración de la sucursal y costo con el nuevo modelo
            ConfiguracionMedicoSucursal::create([
                'medico_id'      => $medico->id, // Guarda el ID numérico recién generado
                'ubicacion_id'   => $request->ubicacion_id,
                'costo_consulta' => $request->costo_consulta,
            ]);

            // 3. Iterar los días seleccionados para guardar los horarios
            foreach ($request->dias_laborales as $diaNombre) {
                $diaNumero = $mapeoDias[$diaNombre] ?? 1;

                HorarioMedico::create([
                    'medico_id'         => $medico->id, 
                    'dia_semana'        => $diaNumero,
                    'hora_inicio'       => $request->hora_entrada,
                    'hora_fin'          => $request->hora_salida,
                    'duracion_consulta' => $request->duracion_consulta,
                    'ubicacion_id'      => $request->ubicacion_id, // Se mapea también en horarios si es necesario
                ]);
            }

            // C. CONFIRMAR CAMBIOS
            DB::commit();

            return response()->json([
                'success'   => true,
                'message'   => '¡Médico, horarios y costos registrados con éxito!',
                'folio'     => $medico->folio, // Retorna el folio real (ej: MEDI-2026-001) para tu Vue
                'medico_id' => $medico->id
            ], 201);

        } catch (\Exception $e) {
            // D. REVERTIR SI ALGO FALLA
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el registro',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $medico = Medico::with([
            'especialidad',
            'horarios',
            'configuraciones.ubicacion'
        ])->findOrFail($id);

        return response()->json($medico);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre'            => 'required|string|max:255',
            'especialidad_id'   => 'required|exists:especialidades,id',
            'estado'            => 'required|in:Activo,Inactivo',
            'hora_inicio'       => 'required',
            'hora_fin'          => 'required',
            'dias'              => 'required|array|min:1',
            'duracion_consulta' => 'required|integer|min:5|max:240',
            
            // Agregamos la validación de los campos de configuración que vienen del modal
            'ubicacion_id'      => 'required|exists:ubicaciones,id',
            'costo_consulta'    => 'required|numeric|min:0',
        ]);

        $mapeoDias = [
            'Lunes'     => 1,
            'Martes'    => 2,
            'Miércoles' => 3,
            'Jueves'    => 4,
            'Viernes'   => 5,
            'Sábado'    => 6,
            'Domingo'   => 7,
        ];

        DB::beginTransaction();

        try {
            $medico = Medico::findOrFail($id);

            // 1. Actualizar datos base del Médico
            $medico->nombre          = $request->nombre;
            $medico->especialidad_id = $request->especialidad_id;
            $medico->activo          = $request->estado === 'Activo' ? 1 : 0;
            $medico->save();

            // 2. Actualizar o crear la configuración de sucursal y costo
            ConfiguracionMedicoSucursal::updateOrCreate(
                ['medico_id' => $medico->id], // Condición de búsqueda
                [
                    'ubicacion_id'   => $request->ubicacion_id,
                    'costo_consulta' => $request->costo_consulta,
                ]
            );

            // 3. Borrar los horarios anteriores y recrearlos incorporando los nuevos datos
            HorarioMedico::where('medico_id', $medico->id)->delete();

            foreach ($request->dias as $diaNombre) {
                HorarioMedico::create([
                    'medico_id'         => $medico->id,
                    'dia_semana'        => $mapeoDias[$diaNombre] ?? 1,
                    'hora_inicio'       => $request->hora_inicio,
                    'hora_fin'          => $request->hora_fin,
                    'duracion_consulta' => $request->duracion_consulta,
                    'ubicacion_id'      => $request->ubicacion_id, // Añadido para mantener consistencia con el store
                ]);
            }

            DB::commit();

            // Devuelve el médico actualizado cargando también su configuración para que Vue actualice la tabla reactiva correctamente
            $medico->load(['especialidad', 'horarios', 'configuraciones.ubicacion']);

            return response()->json([
                'success' => true,
                'message' => 'Médico actualizado correctamente',
                'medico'  => $medico,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el médico',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $medico = Medico::find($id);

        if (!$medico) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Médico no encontrado'
            ], 404); 
        }

        $medico->activo = 0;
        $medico->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Médico eliminado correctamente'
        ]);
    }

    public function filtrar_medico(Request $request)
    {
        $buscar = $request->buscar;
    
        return Medico::where('nombre', 'like', "%{$buscar}%")
            ->orWhere('folio', 'like', "%{$buscar}%")
            ->select(
                'folio',
                'nombre', 
                'cedula_profesional', 
                'especialidad_id', 
            )
            ->get();
    }

    public function obtenerEstadisticas()
    {
        // SELECT COUNT(folio) FROM medicos
        $total = Medico::count();

        // SELECT COUNT(folio) FROM medicos WHERE activo = 1
        $activos = Medico::where('activo', 1)->count();

        // SELECT COUNT(folio) FROM medicos WHERE activo = 0
        $inactivos = Medico::where('activo', 0)->count();

        // Retornamos los tres contadores en una sola respuesta JSON
        return response()->json([
            'total'     => $total,
            'activos'   => $activos,
            'inactivos' => $inactivos
        ]);
    }

    //Api (IONIC) para consultar la informacion del medico
    public function getPerfilMedico(Request $request) {
        // Obtenemos el ID del usuario autenticado de forma segura
        $userId = $request->user()->id; 

        $medico = DB::table('users')
                    ->join('medicos', 'medicos.user_id', '=', 'users.id')
                    ->select('medicos.folio', 'medicos.nombre')
                    ->where('users.id', $userId) // Aquí validamos contra el ID del usuario logueado
                    ->first();
        
        return response()->json($medico);
    }

    public function getMedicoConfiguracion(Request $request) {
        // 1. Obtenemos el ID del usuario autenticado
        $userId = $request->user()->id;

        // 2. Ejecutamos la consulta usando ese ID
        // Usamos DB::select porque tu consulta tiene un GROUP_CONCAT complejo
        $agenda = DB::select("
            SELECT 
                medicos.user_id,
                medicos.id,
                medicos.cedula_profesional as cedula, 
                medicos.folio, 
                medicos.nombre, 
                especialidades.nombre as especialidad,
                CONCAT(horarios_medicos.hora_inicio, '-', horarios_medicos.hora_fin) AS horarios,
                GROUP_CONCAT(
                    CASE horarios_medicos.dia_semana 
                        WHEN 1 THEN 'Lunes' WHEN 2 THEN 'Martes' WHEN 3 THEN 'Miércoles' 
                        WHEN 4 THEN 'Jueves' WHEN 5 THEN 'Viernes' WHEN 6 THEN 'Sábado' WHEN 7 THEN 'Domingo'
                    END 
                    ORDER BY horarios_medicos.dia_semana ASC SEPARATOR ', '
                ) AS dias_atencion,
                ubicaciones.nombre AS lugar, 
                ubicaciones.direccion AS direccion
            FROM users 
            JOIN medicos ON medicos.user_id = users.id
            JOIN especialidades ON especialidades.id = medicos.especialidad_id 
            JOIN horarios_medicos ON horarios_medicos.medico_id = medicos.id
            JOIN ubicaciones ON ubicaciones.id = horarios_medicos.ubicacion_id
            WHERE users.id = ?
            GROUP BY medicos.cedula_profesional, medicos.folio, medicos.nombre, especialidades.nombre, horarios, lugar, direccion
        ", [$userId]); // Pasamos el ID de forma segura como parámetro

        // 3. Retornamos el objeto (o null si no hay datos)
        return response()->json($agenda);
    }

}