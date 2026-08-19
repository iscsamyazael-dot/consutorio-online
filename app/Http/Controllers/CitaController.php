<?php

namespace App\Http\Controllers;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Medico;
use App\Models\Especialidad;
use App\Models\Consulta;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


// Clase para manejar las peticiones HTTP
use Illuminate\Http\Request;

class CitaController extends Controller
{
    // Muestra todas las citas en la vista.
    // NUEVO: ahora acepta filtros opcionales por medico_id y especialidad_id
    // vía query params (?medico_id=1&especialidad_id=2), que es justo lo que
    public function getCitas(Request $request)
    {
        // Citas agendadas desde el módulo de Agenda (tabla `citas`).
        // Este endpoint es consumido también por la app móvil Ionic y
        // debe reflejar EXCLUSIVAMENTE la tabla `citas`. Las consultas
        // clínicas reales viven en su propio endpoint dedicado:
        // GET /VerHistorialConsultas -> ConsultaController@historial().
        // NO reintroducir aquí la combinación con `consultas`: ya se hizo
        // antes y causó que Agenda Médica mostrara folios CONS- sin
        // médico/especialidad.
        $citas = Cita::with([
            'paciente',
            'medico',
            'especialidad'
        ])->get();

        $citasFormateadas = $citas->map(function ($cita) {
            return [
                'id'     => 'cita-' . $cita->id,
                'origen' => 'cita',
                'title'  => 'Cita: ' . ($cita->paciente->nombre ?? 'Sin paciente'),
                'start'  => $cita->fecha . 'T' . $cita->hora,
                'folio'  => $cita->folio,
                'fecha'  => $cita->fecha,
                'hora'   => $cita->hora,
                'estado' => $cita->estado,
                'tipo'   => $cita->tipo,
                'paciente' => $cita->paciente ? [
                    'id'          => $cita->paciente->id,
                    'nombre'      => $cita->paciente->nombre,
                    'sexo'        => $cita->paciente->sexo,
                    'telefono'    => $cita->paciente->telefono,
                    'email'       => $cita->paciente->email,
                    'direccion'   => $cita->paciente->direccion,
                    'curp'        => $cita->paciente->curp,
                    'tipo_sangre' => $cita->paciente->tipo_sangre,
                    'contacto_emergencia' => $cita->paciente->contacto_emergencia,
                    'alergias' => $cita->paciente->alergias,
                    'fecha_nacimiento' => $cita->paciente->fecha_nacimiento,
                    'edad'        => $cita->paciente->edad,
                    'estado'      => $cita->paciente->estado,
                    'Alergias'     => $cita->paciente->alergias,
                    'Alergias A medicamentos' => $cita->paciente->alergias_a_medicamentos,
                    'Antecedentes' => $cita->paciente->antecedentes,
                    'presion_arterial' => $cita->paciente->presion_arterial,
                    'saturacion_oxigeno' => $cita->paciente->saturacion_oxigeno,
                    'frecuencia_cardiaca' => $cita->paciente->frecuencia_cardiaca,
                    'frecuencia_respiratoria' => $cita->paciente->frecuencia_respiratoria,
                    'peso' => $cita->paciente->peso,
                    'talla' => $cita->paciente->talla,
                    'temperatura' => $cita->paciente->temperatura,
                    'sintomas' => $cita->paciente->sintomas,
                    'motivo_consulta' => $cita->paciente->motivo_consulta,
                ] : null,
                'medico' => $cita->medico ? [
                    'id'     => $cita->medico->id,
                    'nombre' => $cita->medico->nombre,
                ] : null,
                'especialidad' => $cita->especialidad ? [
                    'id'     => $cita->especialidad->id,
                    'nombre' => $cita->especialidad->nombre,
                ] : null,
            ];
        });

        return response()->json(
            $citasFormateadas->sortByDesc('start')->values()
        );
    }
   //muestra el formulario para crear una nueva cita.
    public function create()
    {
        // Obtiene únicamente los pacientes activos.
        $pacientes = Paciente::select('id', 'nombre')
            ->where('estado', 'activo')
            ->get();

        // Obtiene únicamente los médicos activos.
        $medicos = Medico::select('id', 'nombre', 'especialidad_id')
            ->where('activo', 1)
            ->get();

        // Obtiene únicamente las especialidades activas.
        $especialidades = Especialidad::select('id', 'nombre')
            ->where('estado', 'Activo')
            ->get();

        return [
            'pacientes' => $pacientes,
            'medicos' => $medicos,
            'especialidades' => $especialidades
        ];
        
        // view(
        //     'citas.create',
        //     compact(
        //         'pacientes',
        //         'medicos',
        //         'especialidades'
        //     )
        // );
    }


    public function store(Request $request)
    {
        // Validación de datos.
        // Si algo falla, Laravel responde con un
        // error 422 en JSON (porque el fetch manda
        // el header "Accept: application/json").
        $request->validate([

            'paciente_id'     => 'required|exists:pacientes,id',
            'medico_id'       => 'required|exists:medicos,id',
            'especialidad_id' => 'required|exists:especialidades,id',
            'fecha'           => 'required|date',
            'hora'            => 'required',
            'estado'          => 'required',

        ]);

        //verifica si la hora ya está ocupada para el médico seleccionado
        $horaOcupada = Cita::where('medico_id', $request->medico_id)
            ->where('fecha', $request->fecha)
            ->where('hora', $request->hora)
            ->where('estado', '!=', 'Cancelada')
            ->exists();

        if ($horaOcupada) {
            return response()->json([
                'message' => 'Esa hora ya está ocupada para este médico. Por favor selecciona otra hora.',
            ], 422);
        }

      // Crea la cita en la base de datos. El folio se genera automáticamente
        $cita = Cita::create([

            'paciente_id'     => $request->paciente_id,
            'medico_id'       => $request->medico_id,
            'especialidad_id' => $request->especialidad_id,

            'fecha' => $request->fecha,
            'hora'  => $request->hora,

            // Estado inicial
            'estado' => $request->estado,

            'tipo'          => $request->tipo,
            'observaciones' => $request->observaciones,

        ]);

        // Responde en JSON con el mensaje de éxito.
        return response()->json([

            'message' => 'Cita agendada correctamente',
            'cita'    => $cita,

        ]);
    } // ← cierre del método store()

    //muestra una cita por su id.
    public function show($id)
    {
        return Cita::with([
            'paciente',
            'medico',
            'especialidad'
        ])->findOrFail($id);
    }

   //actualiza una cita por su id.
    public function update(Request $request, $id)
    {
        // Busca la cita por su id, o falla si no existe.
        $cita = Cita::findOrFail($id);

        // Validación de datos.
        $horaOcupada = Cita::where('medico_id', $request->medico_id)
            ->where('fecha', $request->fecha)
            ->where('hora', $request->hora)
            ->where('estado', '!=', 'Cancelada')
            ->where('id', '!=', $id)
            ->exists();

        if ($horaOcupada) {
            return response()->json([
                'message' => 'Esa hora ya está ocupada para este médico. Por favor selecciona otra hora.',
            ], 422);
        }

        // Actualiza todos los campos con los nuevos datos.
        $cita->update([

            'paciente_id'     => $request->paciente_id,
            'medico_id'       => $request->medico_id,
            'especialidad_id' => $request->especialidad_id,

            'fecha' => $request->fecha,
            'hora'  => $request->hora,

            'estado' => $request->estado,

            'tipo'          => $request->tipo,
            'observaciones' => $request->observaciones,

        ]);

        // Devuelve la cita ya actualizada.
        return response()->json($cita);
    }

 //Actualiza solo el estado de la cita (Agendado, Confirmada, Finalizada, Cancelada, Inasistencia)
    public function actualizarEstado(Request $request, $id)
    {
        // Verifica que el estado recibido sea válido.
        $request->validate([

            'estado' => 'required|in:Agendado,Confirmada,Finalizada,Cancelada,Inasistencia'

        ]);

        // Busca la cita.
        $cita = Cita::findOrFail($id);

        // Cambia solamente el estado.
        $cita->estado = $request->estado;

        // Guarda el cambio.
        $cita->save();

        // Devuelve respuesta al frontend.
        return response()->json([

            'success' => true,
            'message' => 'Estado actualizado correctamente.',
            'cita'    => $cita

        ]);
    }

   //elimina una cita por su id.
    public function destroy($id)
    {
        // Borra la cita usando su id.
        Cita::destroy($id);

        return response()->json([

            'message' => 'Cita eliminada correctamente'

        ]);
    }

    //Función para el DashboardCard de IONIC//
    public function getDashboardStats(Request $request) {
        $userId = $request->user()->id;
        $fechaHoy = date('Y-m-d'); // 2026-07-20

        $stats = DB::table('citas')
            ->join('medicos', 'citas.medico_id', '=', 'medicos.id')
            ->join('users', 'medicos.user_id', '=', 'users.id')
            ->where('users.id', $userId)
            ->whereDate('citas.fecha', $fechaHoy)
            ->select(
               DB::raw('count(citas.id) as total_hoy'),
                
                // Pendientes (busca los que están en cola)
                DB::raw("SUM(CASE WHEN citas.estado IN ('Agendado', 'Confirmado') THEN 1 ELSE 0 END) as pendientes"),
                
                // Completadas (usando el valor exacto 'Finalizada' de tu BD)
                DB::raw("SUM(CASE WHEN citas.estado IN ('Finalizada', 'Completada') THEN 1 ELSE 0 END) as completadas"),
                
                // Canceladas (usando el valor exacto 'Cancelado' de tu BD)
                DB::raw("SUM(CASE WHEN citas.estado IN ('Cancelado', 'Cancelada') THEN 1 ELSE 0 END) as canceladas")
            )
            ->first();

        return response()->json([
            'citas_hoy' => $stats->total_hoy ?? 0,
            'pendientes' => $stats->pendientes ?? 0,
            'completadas' => $stats->completadas ?? 0,
            'canceladas' => $stats->canceladas ?? 0,
        ]);
    }

    //Función para traer las citas del día//
    public function getCitasDelDia(Request $request) {
        $userId = $request->user()->id;
        $fechaHoy = Carbon::now('America/Mexico_City')->toDateString();

        $citas = DB::table('citas')
            ->join('medicos', 'citas.medico_id', '=', 'medicos.id')
            ->join('users', 'medicos.user_id', '=', 'users.id')
            ->join('pacientes', 'citas.paciente_id', '=', 'pacientes.id')
            ->where('users.id', $userId)
            ->whereDate('citas.fecha', $fechaHoy)
            ->whereIn('citas.estado', ['Agendado', 'confirmada'])
            ->select(
                'citas.id',
                'citas.hora',
                'pacientes.nombre as paciente',
                'citas.observaciones',
                'citas.estado'
            )
            ->orderBy('citas.hora', 'asc')
            ->get();

        return response()->json($citas);
    }
 
    //Función para traer el resumen de las citas del día de hoy, mañana, semana//
    public function getResumenCitas(Request $request) {
        $userId = $request->user()->id;
        $filtro = $request->query('filtro', 'hoy'); // Por defecto trae 'hoy'

        $query = DB::table('citas')
            ->join('medicos', 'citas.medico_id', '=', 'medicos.id')
            ->join('users', 'medicos.user_id', '=', 'users.id')
            ->where('users.id', $userId);

        // Evaluamos el filtro de fecha según lo que pida el frontend
        switch ($filtro) {
            case 'mañana':
            case 'manana':
                $query->whereDate('citas.fecha', now()->addDay()->toDateString());
                break;
                
            case 'semana':
                // Desde hoy hasta el final de la semana (ej. próximos 7 días o semana en curso)
                $query->whereBetween('citas.fecha', [now()->toDateString(), now()->addDays(7)->toDateString()]);
                break;
                
            case 'hoy':
            default:
                $query->whereDate('citas.fecha', now()->toDateString());
                break;
        }

        $resumen = $query->select(
            DB::raw('count(citas.id) as total'),
            DB::raw("SUM(CASE WHEN citas.estado = 'Confirmada' THEN 1 ELSE 0 END) as confirmadas"),
            DB::raw("SUM(CASE WHEN citas.estado = 'Agendado' THEN 1 ELSE 0 END) as agendadas"),
            DB::raw("SUM(CASE WHEN citas.estado = 'Finalizada' THEN 1 ELSE 0 END) as finalizadas"), // <-- Agregar esta
            DB::raw("SUM(CASE WHEN citas.estado = 'Cancelado' THEN 1 ELSE 0 END) as canceladas")
        )->first();

        return response()->json([
            'citas_total' => $resumen->total ?? 0,
            'confirmadas' => $resumen->confirmadas ?? 0,
            'pendientes' => $resumen->agendadas ?? 0,
            'finalizadas' => $resumen->finalizadas ?? 0, // <-- Agregamos esta propiedad clave
            'canceladas' => $resumen->canceladas ?? 0,
        ]);
    }
  
    //Función para traer la lista de los citados del día de hoy, mañana, semana//
    public function getListaCitas(Request $request) {
        $userId = $request->user()->id;
        $filtro = $request->query('filtro', 'hoy'); // Por defecto 'hoy'

        $query = DB::table('citas')
            ->join('pacientes', 'citas.paciente_id', '=', 'pacientes.id')
            ->join('medicos', 'citas.medico_id', '=', 'medicos.id')
            ->join('users', 'medicos.user_id', '=', 'users.id')
            ->where('users.id', $userId);

        // Aplicamos el rango de fechas según el filtro recibido
        switch ($filtro) {
            case 'mañana':
            case 'manana':
                $query->whereDate('citas.fecha', now()->addDay()->toDateString());
                break;
                
            case 'semana':
                // Desde hoy hasta los próximos 7 días (o ajusta los rangos si prefieres la semana natural)
                $query->whereBetween('citas.fecha', [now()->toDateString(), now()->addDays(7)->toDateString()]);
                break;
                
            case 'hoy':
            default:
                $query->whereDate('citas.fecha', now()->toDateString());
                break;
        }

        $citas = $query->select(
                'citas.id', // Es importante incluir el id para poder actualizar estados o buscar registros
                'citas.folio',
                'pacientes.nombre as NombrePaciente',
                'citas.estado',
                'citas.hora',
                'citas.fecha'
            )
            ->orderBy('citas.hora', 'asc')
            ->get();

        return response()->json($citas);
    }

    //Función para la lista del calendario//
    public function getCitasPorFecha(Request $request) {
        $userId = $request->user()->id;
        $fechaSeleccionada = $request->query('fecha'); // Espera formato 'YYYY-MM-DD'

        if (!$fechaSeleccionada) {
            return response()->json(['error' => 'La fecha es obligatoria'], 400);
        }

        $citas = DB::table('citas')
            ->join('pacientes', 'citas.paciente_id', '=', 'pacientes.id')
            ->join('medicos', 'citas.medico_id', '=', 'medicos.id')
            ->join('users', 'medicos.user_id', '=', 'users.id')
            ->where('users.id', $userId)
            ->whereDate('citas.fecha', $fechaSeleccionada)
            ->select(
                'citas.id',
                'citas.folio',
                'pacientes.nombre as NombrePaciente',
                'citas.estado',
                'citas.hora',
                'citas.fecha'
            )
            ->orderBy('citas.hora', 'asc')
            ->get();

        return response()->json($citas);
    }

    //Función para ver el detalle de una cita:
    public function getDetalleCita(Request $request) {
        $userId = $request->user()->id; // ID del médico autenticado
        $folio = $request->query('folio'); // Folio que viaja cuando haces clic en la lista

        if (!$folio) {
            return response()->json(['error' => 'El folio de la cita es obligatorio'], 400);
        }

        $detalle = DB::table('citas')
            ->join('pacientes', 'citas.paciente_id', '=', 'pacientes.id')
            ->join('medicos', 'medicos.id', '=', 'citas.medico_id')
            ->join('especialidades', 'especialidades.id', '=', 'medicos.especialidad_id')
            ->join('users', 'users.id', '=', 'medicos.user_id')
            ->where('users.id', $userId)
            ->where('citas.folio', $folio)
            ->select(
                'pacientes.id as paciente_id',
                'pacientes.nombre as NombrePaciente',
                'pacientes.nombre as NombrePaciente',
                'pacientes.sexo as genero',
                'pacientes.edad',
                'pacientes.telefono',
                'citas.folio',
                'citas.fecha',
                'citas.hora',
                'especialidades.nombre as especialidad',
                'medicos.nombre as medico',
                'citas.tipo',
                'citas.estado',
                'citas.observaciones'
            )
            ->first();

        if (!$detalle) {
            return response()->json(['error' => 'Cita no encontrada'], 404);
        }

        return response()->json($detalle);
    }

    // Función para obtener el historial de citas pasadas del médico
    public function getHistorialCitas(Request $request) {
        $userId = $request->user()->id; // ID del médico autenticado por su sesión/token
        
        // Opcional: Si quieres filtrar por un paciente específico desde la vista de detalles
        $pacienteId = $request->query('paciente_id'); 

        // Fecha actual usando Carbon para evitar desfases de zona horaria del servidor
        $fechaHoy = \Carbon\Carbon::now()->toDateString();

        $query = DB::table('citas')
            ->join('pacientes', 'citas.paciente_id', '=', 'pacientes.id')
            ->join('medicos', 'medicos.id', '=', 'citas.medico_id')
            ->join('especialidades', 'especialidades.id', '=', 'medicos.especialidad_id')
            ->join('users', 'users.id', '=', 'medicos.user_id')
            ->where('users.id', $userId)
            ->where('citas.fecha', '<=', $fechaHoy) // Filtra estrictamente de hoy hacia atrás (pasadas)
            ->select(
                'citas.folio',
                'citas.fecha',
                'citas.hora',
                'citas.estado',
                'citas.observaciones',
                'citas.tipo',
                'pacientes.id as paciente_id',
                'pacientes.nombre as NombrePaciente',
                'pacientes.sexo as genero',
                'pacientes.edad',
                'pacientes.telefono',
                'especialidades.nombre as especialidad'
            );

        // Si mandan un paciente_id por la petición, filtramos el historial para ese paciente en específico
        if ($pacienteId) {
            $query->where('pacientes.id', $pacienteId);
        }

        // Ordenamos de la más reciente a la más antigua (hoy, ayer, antier...)
        $historial = $query->orderBy('citas.fecha', 'desc')
                           ->orderBy('citas.hora', 'desc')
                           ->get();

        return response()->json($historial);
    }

    //Función para ver el total de las citas por dia esto servira para definir los puntos que se pintan en el calendario//
    public function citasPorMes(Request $request)
    {
        $anio = $request->input('anio');
        $mes = $request->input('mes');
        $medicoId = $request->user()->id;

        $citas = DB::table('citas')
            ->join('medicos', 'citas.medico_id', '=', 'medicos.id')
            ->join('users', 'medicos.user_id', '=', 'users.id')
            ->select(
                DB::raw("DATE(citas.fecha) as fecha"),
                DB::raw("GROUP_CONCAT(citas.estado) as estados"),
                DB::raw("COUNT(*) as total_citas")
            )
            ->where('users.id', $medicoId)
            ->whereYear('citas.fecha', $anio)
            ->whereMonth('citas.fecha', $mes)
            ->groupBy(DB::raw("DATE(citas.fecha)"))
            ->get()
            ->keyBy('fecha');

        return response()->json($citas);
    }

    //Funcion para actualizar el estado de la cita a traves de IONIC
    public function actualizarEstadoCita(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|string|in:Agendado,Confirmada,Completada,Finalizada,Cancelada',
        ]);
        // Preparamos los datos que vamos a actualizar
        $datosActualizar = [
            'estado' => $request->estado,
        ];
        // Si pasa a un estado final, también actualizamos la notificación en la misma sentencia
        if (in_array($request->estado, ['Finalizada', 'Completada', 'Cancelada'])) {
            $datosActualizar['notificacion_leida'] = 1;
        }
        // Aquí haces el UPDATE de forma directa y explícita en la tabla 'citas'
        Cita::where('id', $id)->update($datosActualizar);
        // Volvemos a consultar la cita actualizada para retornarla en el JSON
        $cita = Cita::findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Estado de la cita actualizado correctamente',
            'cita' => $cita
        ]);
    }
}