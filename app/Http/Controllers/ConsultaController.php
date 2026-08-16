<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\NotaPsoapp;
use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\Medico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\WhatsAppService;

class ConsultaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Consulta::with(
            'paciente',
            'medico',
            'notaPsoapp'
        )->get();
    }

    /**
     * Show the form for creating a new resource.
     */

    // Función para obtener datos del doctor y paciente
    // para ambas vistas (expediente y nueva consulta)
    public function create($id = null)
    {
        $user = auth()->user();

        $medico = $user->medico?->load('especialidad');

        return view('consultas.create', [
            'pacienteId' => $id,

            'doctor' => [
                'id' => $user->id,

                'nombre' => $user->name,

                'cedula' => $medico?->cedula_profesional ?? 'Pendiente',

                'especialidad' =>
                    $medico?->especialidad?->nombre
                    ?? 'Medicina General',
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([

            'paciente_id' => 'required|exists:pacientes,id',

            'motivo' => 'nullable',

            'diagnostico' => 'nullable',

            'observaciones' => 'nullable',

            'sintomas' => 'nullable',

            'presentacion' => 'nullable|string',

            'subjetivo' => 'nullable|string',

            'objetivo' => 'nullable|string',

            'analisis' => 'nullable|string',

            'plan' => 'nullable|string',

            'pronostico' => 'nullable|string',
        ]);

        // El paciente ya existe
        // (viene de la ruta consultaNormal/{id})
        $paciente = Paciente::findOrFail(
            $request->paciente_id
        );

        // CREAR CONSULTA
        $consulta = Consulta::create([

            'folio' => 'CONS-' . time(),

            'paciente_id' => $paciente->id,

            'user_id' => Auth::id(),

            'motivo_consulta' => $request->motivo,

            'diagnostico' => $request->diagnostico,

            'observaciones' => $request->observaciones,

            'estado' => 'activa',

            'estado_consulta' => 'finalizada',
        ]);

        // CREAR NOTA PSOAPP
        NotaPsoapp::create([

            'consulta_id' => $consulta->id,

            'consulta_folio' => $consulta->folio,

            'evaluacion_ia_id' => $consulta->evaluacion_ia_id,

            'presentacion' => $request->presentacion,

            'subjetivo' => $request->subjetivo,

            'objetivo' => $request->objetivo,

            'analisis' => $request->analisis,

            'plan' => $request->plan,

            'pronostico' => $request->pronostico,

            'estado' => 'completada',
        ]);

        // ENVIAR WHATSAPP
        // solo si el paciente tiene teléfono registrado
        if ($paciente->telefono) {

            $telefono = '52' . $paciente->telefono;

            $mensaje =
                "Hola {$paciente->nombre}, tu consulta médica fue registrada correctamente.";

            WhatsAppService::enviar(
                $telefono,
                $mensaje
            );
        }

        return response()->json([

            'success' => true,

            'message' => 'Consulta registrada correctamente',

            'consulta' => $consulta,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Consulta::with(
            'paciente',
            'medico',
            'notaPsoapp'
        )->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {

            $consulta = Consulta::findOrFail($id);

            // Actualizar únicamente los campos
            // que hayan sido enviados

            if ($request->has('motivo')) {

                $consulta->motivo_consulta =
                    $request->motivo;
            }

            if ($request->has('diagnostico')) {

                $consulta->diagnostico =
                    $request->diagnostico;
            }

            if ($request->has('observaciones')) {

                $consulta->observaciones =
                    $request->observaciones;
            }

            if ($request->has('estado')) {

                $consulta->estado =
                    $request->estado;
            }

            if ($request->has('estado_consulta')) {

                $consulta->estado_consulta =
                    $request->estado_consulta;
            }

            $consulta->save();

            return response()->json([

                'success' => true,

                'message' =>
                    'Consulta actualizada correctamente',

                'consulta' => $consulta,
            ]);

        } catch (\Throwable $e) {

            \Log::error(
                'Error al actualizar consulta',
                [
                    'consulta_id' => $id,

                    'user_id' => Auth::id(),

                    'error' => $e->getMessage(),

                    'linea' => $e->getLine(),

                    'archivo' => $e->getFile(),
                ]
            );

            return response()->json([

                'success' => false,

                'message' =>
                    'Error al actualizar la consulta',

                'error' => $e->getMessage(),

            ], 500);
        }
    }

    /**
     * Actualiza solamente el estado de la consulta.
     *
     * NUEVO: se agrega para que HistorialConsulta.vue pueda cambiar el
     * estado de las consultas "tradicionales" (tabla `consultas`) desde
     * el mismo chip clickeable que ya usa para las citas de Agenda.
     * El frontend maneja las etiquetas en español con mayúscula/espacio
     * ('Agendado', 'En proceso', 'Finalizada', 'Cancelada'), así que aquí
     * se traducen a los valores internos que usa `estado_consulta`.
     */
    public function actualizarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:Agendado,En proceso,Finalizada,Cancelada',
        ]);

        $consulta = Consulta::findOrFail($id);

        $mapaEstados = [
            'Agendado'    => 'activa',
            'En proceso'  => 'en_proceso',
            'Finalizada'  => 'finalizada',
            'Cancelada'   => 'cancelada',
        ];

        $consulta->estado_consulta = $mapaEstados[$request->estado] ?? $request->estado;
        $consulta->save();

        return response()->json([
            'success'  => true,
            'message'  => 'Estado actualizado correctamente.',
            'consulta' => $consulta,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $consulta = Consulta::findOrFail($id);

        $consulta->delete();

        return response()->json([

            'success' => true,

            'message' =>
                'Consulta eliminada correctamente',
        ]);
    }

    /**
 * Historial de consultas reales (tabla `consultas`), para
 * HistorialConsulta.vue. Reemplaza el uso incorrecto de
 * CitaController@getCitas, que traía citas de Agenda en vez
 * de consultas ya registradas.
 *
 * Filtros soportados (todos opcionales):
 *  - fecha            (Y-m-d)  -> WHERE DATE(created_at) = fecha
 *  - medico_id                 -> WHERE user_id = medico_id (id de medicos, no de users)
 *  - especialidad_id           -> WHERE medico.especialidad_id = especialidad_id
 *
 * NOTA: si consultas.user_id no corresponde a ningún medicos.user_id
 * (p.ej. consultas creadas/tocadas por un admin), 'medico' y
 * 'especialidad' regresan null en vez de un dato incorrecto.
 */
    public function historial(Request $request)
    {
        $query = Consulta::with(['paciente', 'medico.especialidad']);

        if ($request->filled('fecha')) {
            $query->whereDate('created_at', $request->fecha);
        }

        if ($request->filled('medico_id')) {
            $medico = Medico::find($request->medico_id);
            $query->where('user_id', $medico?->user_id ?? -1);
        }

        if ($request->filled('especialidad_id')) {
            $query->whereHas('medico', function ($q) use ($request) {
                $q->where('especialidad_id', $request->especialidad_id);
            });
        }

        $mapaEstadosDisplay = [
            'activa'     => 'Agendado',
            'en_proceso' => 'En proceso',
            'finalizada' => 'Finalizada',
            'cancelada'  => 'Cancelada',
        ];

        $consultas = $query->orderBy('created_at')->get()->map(function ($consulta) use ($mapaEstadosDisplay) {
            return [
                'id'     => 'consulta-' . $consulta->id,
                'origen' => 'consulta',
                'title'  => 'Consulta: ' . ($consulta->paciente->nombre ?? 'Sin paciente'),
                'folio'  => $consulta->folio,
                'fecha'  => optional($consulta->created_at)->format('Y-m-d'),
                'hora'   => optional($consulta->created_at)->format('H:i:s'),
                'estado' => $mapaEstadosDisplay[$consulta->estado_consulta] ?? $consulta->estado_consulta,
                'tipo'   => 'Consulta',

                'paciente' => $consulta->paciente ? [
                    'id'     => $consulta->paciente->id,
                    'nombre' => trim(implode(' ', array_filter([
                        $consulta->paciente->nombre,
                        $consulta->paciente->apellido_paterno,
                        $consulta->paciente->apellido_materno,
                    ]))),
                ] : null,

                'medico' => $consulta->medico ? [
                    'id'     => $consulta->medico->id,
                    'nombre' => $consulta->medico->nombre,
                ] : null,

                'especialidad' => $consulta->medico?->especialidad ? [
                    'id'     => $consulta->medico->especialidad->id,
                    'nombre' => $consulta->medico->especialidad->nombre,
                ] : null,
            ];
        });

        return response()->json($consultas);
    }
}