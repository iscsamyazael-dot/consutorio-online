<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\DoctorAvailability;
use Carbon\Carbon;

class CitaValidationService
{
    /**
     * Validar si hay sobreposición de horarios
     */
    public function validarSobreposicion(
        int $user_id,
        Carbon $fecha_hora,
        int $duracion_minutos,
        ?int $cita_id = null
    ): array {
        $inicio = $fecha_hora;
        $fin = $fecha_hora->copy()->addMinutes($duracion_minutos);

        $query = Cita::where('user_id', $user_id)
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->where(function ($q) use ($inicio, $fin) {
                $q->whereBetween('fecha_hora', [$inicio, $fin])
                    ->orWhere('fecha_hora', '<=', $inicio->copy()->subMinutes(1))
                    ->where('fecha_hora', '>', $inicio->copy()->subHours(8));
            });

        if ($cita_id) {
            $query->where('id', '!=', $cita_id);
        }

        $citas_solapadas = $query->get();

        if ($citas_solapadas->isNotEmpty()) {
            return [
                'valido' => false,
                'mensaje' => 'El médico tiene una cita en este horario',
                'citas_solapadas' => $citas_solapadas,
            ];
        }

        return ['valido' => true];
    }

    /**
     * Validar disponibilidad del médico según horario de trabajo
     */
    public function validarDisponibilidadMedico(
        int $user_id,
        Carbon $fecha_hora,
        int $duracion_minutos
    ): array {
        $dia_semana = $fecha_hora->dayOfWeek - 1;
        $hora_inicio = $fecha_hora->format('H:i:s');
        $hora_fin = $fecha_hora->copy()->addMinutes($duracion_minutos)->format('H:i:s');

        $disponibilidad = DoctorAvailability::where('user_id', $user_id)
            ->where('dia_semana', $dia_semana)
            ->where('activo', true)
            ->where('hora_inicio', '<=', $hora_inicio)
            ->where('hora_fin', '>=', $hora_fin)
            ->first();

        if (!$disponibilidad) {
            return [
                'valido' => false,
                'mensaje' => 'El médico no está disponible en este horario',
            ];
        }

        return ['valido' => true];
    }

    /**
     * Validar que la fecha no sea en el pasado
     */
    public function validarFechaFutura(Carbon $fecha_hora): array
    {
        if ($fecha_hora->isPast()) {
            return [
                'valido' => false,
                'mensaje' => 'No se puede agendar citas en el pasado',
            ];
        }

        return ['valido' => true];
    }

    /**
     * Validar duración mínima
     */
    public function validarDuracionMinima(int $duracion_minutos): array
    {
        if ($duracion_minutos < 15) {
            return [
                'valido' => false,
                'mensaje' => 'La duración mínima es 15 minutos',
            ];
        }

        if ($duracion_minutos > 480) {
            return [
                'valido' => false,
                'mensaje' => 'La duración máxima es 8 horas',
            ];
        }

        return ['valido' => true];
    }

    /**
     * Ejecutar todas las validaciones
     */
    public function validarCompleto(
        int $user_id,
        Carbon $fecha_hora,
        int $duracion_minutos,
        ?int $cita_id = null
    ): array {
        $validacion = $this->validarDuracionMinima($duracion_minutos);
        if (!$validacion['valido']) {
            return $validacion;
        }

        $validacion = $this->validarFechaFutura($fecha_hora);
        if (!$validacion['valido']) {
            return $validacion;
        }

        $validacion = $this->validarDisponibilidadMedico($user_id, $fecha_hora, $duracion_minutos);
        if (!$validacion['valido']) {
            return $validacion;
        }

        $validacion = $this->validarSobreposicion($user_id, $fecha_hora, $duracion_minutos, $cita_id);
        if (!$validacion['valido']) {
            return $validacion;
        }

        return ['valido' => true, 'mensaje' => 'Cita válida'];
    }
}
