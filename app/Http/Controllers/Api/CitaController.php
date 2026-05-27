<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\User;
use App\Services\CitaValidationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class CitaController extends Controller
{
    /**
     * Obtener citas en un rango de fechas para el calendario
     */
    public function listaPorRango(Request $request): JsonResponse
    {
        $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date'],
            'medico_id' => ['nullable', 'exists:users,id'],
        ]);

        $query = Cita::with(['paciente', 'medico'])
            ->whereBetween('fecha_hora', [
                Carbon::parse($request->start)->startOfDay(),
                Carbon::parse($request->end)->endOfDay(),
            ]);

        if ($request->medico_id) {
            $query->where('user_id', $request->medico_id);
        }

        $citas = $query->get();

        return response()->json($this->formatearParaCalendario($citas));
    }

    /**
     * Crear cita
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'user_id' => ['required', 'exists:users,id'],
            'fecha_hora' => ['required', 'date', 'after:now'],
            'duracion_minutos' => ['required', 'integer', 'min:15', 'max:480'],
            'motivo' => ['required', 'string', 'max:255'],
            'tipo_cita' => ['required', 'string'],
            'ubicacion' => ['nullable', 'string'],
            'notas' => ['nullable', 'string'],
        ]);

        // Validar con el servicio
        $validacion = app(CitaValidationService::class)->validarCompleto(
            $validated['user_id'],
            Carbon::parse($validated['fecha_hora']),
            $validated['duracion_minutos']
        );

        if (!$validacion['valido']) {
            return response()->json([
                'success' => false,
                'mensaje' => $validacion['mensaje'],
            ], 422);
        }

        $cita = Cita::create([
            ...$validated,
            'estado' => 'pendiente',
            'color' => '#3b82f6',
        ]);

        return response()->json([
            'success' => true,
            'cita' => $cita->load(['paciente', 'medico']),
        ], 201);
    }

    /**
     * Actualizar cita
     */
    public function update(Request $request, Cita $cita): JsonResponse
    {
        $validated = $request->validate([
            'paciente_id' => ['exists:pacientes,id'],
            'user_id' => ['exists:users,id'],
            'fecha_hora' => ['date', 'after:now'],
            'duracion_minutos' => ['integer', 'min:15', 'max:480'],
            'motivo' => ['string', 'max:255'],
            'tipo_cita' => ['string'],
            'ubicacion' => ['nullable', 'string'],
            'notas' => ['nullable', 'string'],
            'estado' => ['string', 'in:pendiente,confirmada,cancelada,atendida'],
        ]);

        if (isset($validated['user_id'], $validated['fecha_hora'], $validated['duracion_minutos'])) {
            $validacion = app(CitaValidationService::class)->validarCompleto(
                $validated['user_id'] ?? $cita->user_id,
                Carbon::parse($validated['fecha_hora'] ?? $cita->fecha_hora),
                $validated['duracion_minutos'] ?? 30,
                $cita->id
            );

            if (!$validacion['valido']) {
                return response()->json([
                    'success' => false,
                    'mensaje' => $validacion['mensaje'],
                ], 422);
            }
        }

        $cita->update($validated);

        return response()->json([
            'success' => true,
            'cita' => $cita->load(['paciente', 'medico']),
        ]);
    }

    /**
     * Cambiar estado de cita
     */
    public function cambiarEstado(Request $request, Cita $cita): JsonResponse
    {
        $validated = $request->validate([
            'estado' => ['required', 'string', 'in:pendiente,confirmada,cancelada,atendida'],
            'razon_cancelacion' => ['nullable', 'string'],
        ]);

        $cita->update([
            'estado' => $validated['estado'],
            'razon_cancelacion' => $validated['razon_cancelacion'] ?? null,
            'cancelada_en' => $validated['estado'] === 'cancelada' ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'cita' => $cita,
        ]);
    }

    /**
     * Confirmar cita por paciente
     */
    public function confirmarPaciente(Request $request, Cita $cita): JsonResponse
    {
        $cita->update([
            'confirmada_paciente' => true,
            'estado' => 'confirmada',
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Cita confirmada',
            'cita' => $cita,
        ]);
    }

    /**
     * Buscar paciente
     */
    public function buscarPaciente(Request $request): JsonResponse
    {
        $busqueda = $request->query('q', '');

        if (strlen($busqueda) < 2) {
            return response()->json([]);
        }

        $pacientes = Paciente::where('nombre', 'like', "%$busqueda%")
            ->orWhere('apellido_paterno', 'like', "%$busqueda%")
            ->orWhere('apellido_materno', 'like', "%$busqueda%")
            ->orWhere('numero_cedula', 'like', "%$busqueda%")
            ->limit(10)
            ->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno', 'numero_cedula']);

        return response()->json($pacientes);
    }

    /**
     * Obtener médicos disponibles
     */
    public function obtenerMedicos(): JsonResponse
    {
        $medicos = User::where('role', 'doctor')
            ->orWhere('role', 'medico')
            ->get(['id', 'name']);

        return response()->json($medicos);
    }

    /**
     * Eliminar cita
     */
    public function destroy(Cita $cita): JsonResponse
    {
        $cita->delete();

        return response()->json([
            'success' => true,
            'mensaje' => 'Cita eliminada',
        ]);
    }

    /**
     * Formatear citas para FullCalendar
     */
    private function formatearParaCalendario($citas): array
    {
        return $citas->map(function (Cita $cita) {
            $inicio = Carbon::parse($cita->fecha_hora);
            $fin = $inicio->copy()->addMinutes($cita->duracion_minutos ?? 30);

            return [
                'id' => $cita->id,
                'title' => $cita->paciente->nombre . ' (' . $cita->motivo . ')',
                'start' => $inicio->toIso8601String(),
                'end' => $fin->toIso8601String(),
                'backgroundColor' => $cita->color ?? '#3b82f6',
                'borderColor' => $cita->color ?? '#3b82f6',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'paciente' => $cita->paciente->nombre,
                    'medico' => $cita->medico->name ?? 'Sin asignar',
                    'motivo' => $cita->motivo,
                    'tipo' => $cita->tipo_cita,
                    'estado' => $cita->estado,
                    'ubicacion' => $cita->ubicacion,
                    'confirmada' => $cita->confirmada_paciente,
                ],
            ];
        })->toArray();
    }
}
