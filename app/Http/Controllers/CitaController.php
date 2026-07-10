<?php

namespace App\Http\Controllers;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Medico;
use App\Models\Especialidad;

// Clase para manejar las peticiones HTTP
use Illuminate\Http\Request;

class CitaController extends Controller
{
    // Muestra todas las citas en la vista.
    // NUEVO: ahora acepta filtros opcionales por medico_id y especialidad_id
    // vía query params (?medico_id=1&especialidad_id=2), que es justo lo que
    // manda el frontend a través de ApiService.get('citas', { params }).
    public function index(Request $request)
    {
        $query = Cita::with(['paciente', 'medico', 'especialidad']);

        // Si el frontend envía medico_id, filtra por ese médico.
        if ($request->filled('medico_id')) {
            $query->where('medico_id', $request->medico_id);
        }

        // Si el frontend envía especialidad_id, filtra por esa especialidad.
        if ($request->filled('especialidad_id')) {
            $query->where('especialidad_id', $request->especialidad_id);
        }

        return $query->get();
    }

    // Devuelve todas las citas en formato JSON para el calendario
    // NOTA: si el calendario ahora consume el endpoint de index() con filtros,
    // este método puede volverse redundante. Se deja intacto por si otra
    // vista todavía lo usa.
    public function getCitas()
    {
        $citas = Cita::with([
            'paciente',
            'medico',
            'especialidad'
        ])->get();

        return response()->json(

            $citas->map(function ($cita) {

                return [

                    // Datos principales
                    'id'     => $cita->id,
                    'title'  => 'Cita: ' . ($cita->paciente->nombre ?? 'Sin paciente'),
                    'start'  => $cita->fecha . 'T' . $cita->hora,

                    // Información de la cita
                    'folio'  => $cita->folio,
                    'fecha'  => $cita->fecha,
                    'hora'   => $cita->hora,
                    'estado' => $cita->estado,
                    'tipo'   => $cita->tipo,

                    // Información del paciente
                    // NOTA: se agregan todos los campos que necesita
                    // el formulario de "Registrar Paciente" para poder
                    // precargar los datos cuando están incompletos.
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

                    // Información del médico
                    'medico' => $cita->medico ? [

                        'id'     => $cita->medico->id,
                        'nombre' => $cita->medico->nombre,

                    ] : null,

                    // Información de la especialidad
                    'especialidad' => $cita->especialidad ? [

                        'id'     => $cita->especialidad->id,
                        'nombre' => $cita->especialidad->nombre,

                    ] : null,

                ];
            })
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

 //Actualiza solo el estado de la cita (Agendado, Finalizada, Cancelada, Inasistencia)
    public function actualizarEstado(Request $request, $id)
    {
        // Verifica que el estado recibido sea válido.
        $request->validate([

            'estado' => 'required|in:Agendado,Finalizada,Cancelada,Inasistencia'

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
}