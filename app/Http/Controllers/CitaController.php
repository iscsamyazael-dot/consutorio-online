<?php

namespace App\Http\Controllers;

// ==============================
// Importación de modelos
// ==============================
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Medico;
use App\Models\Especialidad;

// Clase para manejar las peticiones HTTP
use Illuminate\Http\Request;

class CitaController extends Controller
{
    /**
     * ============================================
     * Muestra la vista principal de las citas.
     * ============================================
     */
    public function index()
    {
        // Obtiene todas las citas junto con
        // el paciente, médico y especialidad relacionados.
        $citas = Cita::with(['paciente', 'medico', 'especialidad'])->get();

        // Envía la información a la vista.
        return view('citas.index', compact('citas'));
    }

    /**
     * ============================================
     * Devuelve las citas en formato JSON.
     * Se utiliza desde Vue (Agenda).
     * ============================================
     */
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

    /**
     * ============================================
     * Muestra el formulario para crear una cita.
     * ============================================
     */
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

        return view(
            'citas.create',
            compact(
                'pacientes',
                'medicos',
                'especialidades'
            )
        );
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

        // Guarda la cita.
        // IMPORTANTE: se guarda en la variable $cita
        // para poder devolverla en la respuesta de abajo.
        $cita = Cita::create([

            'folio' => 'CIT-' . time(),

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

    /**
     * ============================================
     * Muestra una sola cita.
     * ============================================
     */
    public function show($id)
    {
        return Cita::with([
            'paciente',
            'medico',
            'especialidad'
        ])->findOrFail($id);
    }

    /**
     * ============================================
     * Actualiza todos los datos de una cita.
     * ============================================
     */
    public function update(Request $request, $id)
    {
        // Busca la cita por su id, o falla si no existe.
        $cita = Cita::findOrFail($id);

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

    /**
     * ============================================
     * Actualiza únicamente el estado de la cita.
     *
     * Este método será utilizado por el modal
     * cuando el usuario cambie el estado.
     * ============================================
     */
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

    /**
     * ============================================
     * Elimina una cita.
     * ============================================
     */
    public function destroy($id)
    {
        // Borra la cita usando su id.
        Cita::destroy($id);

        return response()->json([

            'message' => 'Cita eliminada correctamente'

        ]);
    }
}