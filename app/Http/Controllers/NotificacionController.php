<?php

namespace App\Http\Controllers;
use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificacionController extends Controller
{
    /**
     * Obtiene las notificaciones del médico autenticado de forma dinámica
     */
    public function index(Request $request)
    {
        // Validamos que el usuario esté autenticado para evitar que truene
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'No autorizado'], 401);
        }
        // Obtenemos el ID del médico autenticado de forma dinámica igual que en tu ejemplo
        $userId = $user->id;

        $citas = DB::select("
            SELECT 
                citas.id,
                citas.folio,
                medicos.nombre as medico,
                citas.estado,
                citas.notificacion_leida,
                citas.updated_at,
                citas.paciente_id,
                pacientes.nombre as paciente_nombre
            FROM citas
            JOIN pacientes ON citas.paciente_id = pacientes.id
            JOIN medicos ON citas.medico_id = medicos.id
            JOIN users ON users.id = medicos.user_id
            WHERE medicos.user_id = ? AND citas.notificacion_leida = 0
            ORDER BY citas.updated_at DESC
            LIMIT 20
        ", [$userId]);

        $notificaciones = collect($citas)->map(function($cita) {
            return $this->formatearNotificacion($cita);
        });

        return response()->json($notificaciones);
    }

    /**
     * Marca la notificación como leída
     */
    public function marcarLeida($id)
    {
        $cita = Cita::findOrFail($id);
        $cita->notificacion_leida = 1; 
        $cita->save();

        return response()->json(['message' => 'Notificación marcada como leída']);
    }

    /**
     * Función privada para formatear cada registro
     */
    private function formatearNotificacion($cita)
    {
        $nombrePaciente = trim(($cita->paciente_nombre ?? '') . ' ' . ($cita->paciente_apellidos ?? ''));
        if (empty($nombrePaciente)) {
            $nombrePaciente = 'Un paciente';
        }

        $tipo = 'nueva-cita';
        $titulo = 'Actualización de cita';
        $mensaje = "La cita con {$nombrePaciente} ha sido actualizada.";

        switch (strtolower($cita->estado)) {
            case 'agendado':
                $tipo = 'nueva-cita';
                $titulo = 'Nueva cita agendada';
                $mensaje = "{$nombrePaciente} ha agendado una nueva cita.";
                break;
            case 'cancelada':
            case 'cancelado': // Por si en algún registro tienes "Cancelado" con 'o'
                $tipo = 'cancelada';
                $titulo = 'Cita cancelada';
                $mensaje = "{$nombrePaciente} canceló su cita.";
                break;
            case 'inasistencia':
                $tipo = 'inasistencia';
                $titulo = 'Inasistencia registrada';
                $mensaje = "{$nombrePaciente} no se presentó a su cita.";
                break;
            case 'finalizada':
                $tipo = 'finalizada';
                $titulo = 'Cita finalizada';
                $mensaje = "La cita con {$nombrePaciente} ha finalizado.";
                break;
            default:
                $tipo = 'actualizacion';
                $titulo = 'Actualización de cita';
                $mensaje = "La cita con {$nombrePaciente} ha sido actualizada.";
                break;
        }

        Carbon::setLocale('es');
        $fechaNotificacion = Carbon::parse($cita->updated_at);
        $horaFormateada = $fechaNotificacion->format('h:i A');

        if ($fechaNotificacion->isToday()) {
            $fechaString = "Hoy · {$horaFormateada}";
        } elseif ($fechaNotificacion->isYesterday()) {
            $fechaString = "Ayer · {$horaFormateada}";
        } else {
            $fechaString = $fechaNotificacion->translatedFormat('d M') . " · {$horaFormateada}";
        }

        return [
            'id' => $cita->id,
            'folio' => $cita->folio,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'fecha' => $fechaString,
            'leida' => (bool) $cita->notificacion_leida,
            'citaId' => $cita->id
        ];
    }
}