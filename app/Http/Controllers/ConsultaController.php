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
     * Función para obtener datos del doctor y paciente para ambas vistas (expediente y nueva consulta)
     */
    public function create($id = null)
    {
        $user = auth()->user();
        $medico = $user->medico?->load('especialidad');

        return view('consultas.create', [
            'pacienteId' => $id,
            'doctor' => [
                'id'           => $user->id,
                'nombre'       => $user->name,
                'cedula'       => $medico?->cedula_profesional ?? 'Pendiente',
                'especialidad' => $medico?->especialidad?->nombre ?? 'Medicina General',
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'paciente_id'  => 'required|exists:pacientes,id',
            'motivo'       => 'nullable',
            'diagnostico'  => 'nullable',
            'observaciones' => 'nullable',
            'sintomas'     => 'nullable',
            'presentacion' => 'nullable|string',
            'subjetivo'    => 'nullable|string',
            'objetivo'     => 'nullable|string',
            'analisis'     => 'nullable|string',
            'plan'         => 'nullable|string',
            'pronostico'   => 'nullable|string',
        ]);

        $paciente = Paciente::findOrFail($request->paciente_id);

        // CREAR CONSULTA
        $consulta = Consulta::create([
            'folio'           => 'CONS-' . time(),
            'paciente_id'     => $paciente->id,
            'user_id'         => Auth::id(),
            'motivo_consulta' => $request->motivo,
            'diagnostico'     => $request->diagnostico,
            'observaciones'   => $request->observaciones,
            'estado'          => 'activa',
            'estado_consulta' => 'finalizada',
        ]);

        // 2. SINCRONIZAR LA LISTA DE ESPERA PARA LA TV
        // Buscamos si el paciente tiene un registro pendiente hoy en la lista de espera y lo actualizamos
        \App\Models\ListaEspera::where('paciente_id', $paciente->id)
            ->where('fecha', now()->toDateString())
            ->whereIn('estado', ['En espera', 'Llamando', 'En proceso'])
            ->update(['estado' => 'Finalizada']); // O el estado que corresponda al finalizar

        // CREAR NOTA PSOAPP
        NotaPsoapp::create([
            'consulta_id'      => $consulta->id,
            'consulta_folio'   => $consulta->folio,
            'evaluacion_ia_id' => $consulta->evaluacion_ia_id,
            'presentacion'     => $request->presentacion,
            'subjetivo'        => $request->subjetivo,
            'objetivo'         => $request->objetivo,
            'analisis'         => $request->analisis,
            'plan'             => $request->plan,
            'pronostico'       => $request->pronostico,
            'estado'           => 'completada',
        ]);

        // ENVIAR WHATSAPP (solo si el paciente tiene teléfono)
        if ($paciente->telefono) {
            $telefono = '52' . $paciente->telefono;
            $mensaje = "Hola {$paciente->nombre}, tu consulta médica fue registrada correctamente.";
            WhatsAppService::enviar($telefono, $mensaje);
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Consulta registrada correctamente',
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

            if ($request->has('motivo')) {
                $consulta->motivo_consulta = $request->motivo;
            }

            if ($request->has('diagnostico')) {
                $consulta->diagnostico = $request->diagnostico;
            }

            if ($request->has('observaciones')) {
                $consulta->observaciones = $request->observaciones;
            }

            if ($request->has('estado')) {
                $consulta->estado = $request->estado;
            }

            if ($request->has('estado_consulta')) {
                $consulta->estado_consulta = $request->estado_consulta;
            }

            $consulta->save();

            return response()->json([
                'success'  => true,
                'message'  => 'Consulta actualizada correctamente',
                'consulta' => $consulta,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error al actualizar consulta', [
                'consulta_id' => $id,
                'user_id'     => Auth::id(),
                'error'       => $e->getMessage(),
                'linea'       => $e->getLine(),
                'archivo'     => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la consulta',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualiza solamente el estado de la consulta desde componentes Vue.
     */
    public function actualizarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:Agendado,En proceso,Finalizada,Cancelada',
        ]);

        $consulta = Consulta::findOrFail($id);

        $mapaEstados = [
            'Agendado'   => 'activa',
            'En proceso' => 'en_proceso',
            'Finalizada' => 'finalizada',
            'Cancelada'  => 'cancelada',
        ];
        
        $nuevoEstadoConsulta = $mapaEstados[$request->estado] ?? $request->estado;
        $consulta->estado_consulta = $nuevoEstadoConsulta;
        $consulta->save();

        // ── AQUÍ ESTABA EL FALTANTE PARA LA TV ──
        // Traducimos el estado de la consulta al formato que lee ListaEspera
        $mapaListaEspera = [
            'activa'     => 'En espera',
            'en_proceso' => 'En consulta',
            'finalizada' => 'Finalizada', // O el estado que use tu TV para ocultarlo
            'cancelada'  => 'Cancelada',
        ];

        if (isset($mapaListaEspera[$nuevoEstadoConsulta])) {
            \App\Models\ListaEspera::where('paciente_id', $consulta->paciente_id)
                ->where('fecha', now()->toDateString())
                ->update(['estado' => $mapaListaEspera[$nuevoEstadoConsulta]]);
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Estado actualizado correctamente.',
            'consulta' => $consulta,
        ]);
    }

    /**
     * Actualiza o crea el estado inicial de consulta por paciente (p. ej. desde Triage).
     */
    public function actualizarEstadoConsulta(Request $request, string $pacienteId)
    {
        $request->validate([
            'estado_consulta' => 'required|in:en_proceso,excedido,finalizada',
        ]);

        $paciente = Paciente::findOrFail($pacienteId);

        $consulta = Consulta::where('paciente_id', $paciente->id)
            ->latest()
            ->first();

        if (!$consulta) {
            $consulta = Consulta::create([
                'folio'           => 'CONS-' . time(),
                'paciente_id'     => $paciente->id,
                'user_id'         => Auth::id(),
                'estado'          => 'activa',
                'estado_consulta' => $request->estado_consulta,
            ]);
        } else {
            $consulta->update([
                'estado_consulta' => $request->estado_consulta,
            ]);
        }

        return response()->json([
            'success'         => true,
            'message'         => 'Estado de consulta actualizado correctamente',
            'estado_consulta' => $consulta->estado_consulta,
        ]);
    }

    /**
     * Historial de consultas con filtros por fecha, médico y especialidad.
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
                'id'           => 'consulta-' . $consulta->id,
                'origen'       => 'consulta',
                'title'        => 'Consulta: ' . ($consulta->paciente->nombre ?? 'Sin paciente'),
                'folio'        => $consulta->folio,
                'fecha'        => optional($consulta->created_at)->format('Y-m-d'),
                'hora'         => optional($consulta->created_at)->format('H:i:s'),
                'estado'       => $mapaEstadosDisplay[$consulta->estado_consulta] ?? $consulta->estado_consulta,
                'tipo'         => 'Consulta',
                'paciente'     => $consulta->paciente ? [
                    'id'     => $consulta->paciente->id,
                    'nombre' => trim(implode(' ', array_filter([
                        $consulta->paciente->nombre,
                        $consulta->paciente->apellido_paterno,
                        $consulta->paciente->apellido_materno,
                    ]))),
                ] : null,
                'medico'       => $consulta->medico ? [
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $consulta = Consulta::findOrFail($id);
        $consulta->delete();

        return response()->json([
            'success' => true,
            'message' => 'Consulta eliminada correctamente',
        ]);
    }
}