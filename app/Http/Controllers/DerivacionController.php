<?php

namespace App\Http\Controllers;
use App\Models\Derivacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DerivacionController extends Controller
{
    public function getDerivacionesByConsulta($consulta_id)
    {
        $derivaciones = DB::table('derivaciones')
            ->join('consultas', 'derivaciones.consulta_id', '=', 'consultas.id')
            ->join('pacientes', 'consultas.paciente_id', '=', 'pacientes.id')
            ->join('especialidades', 'derivaciones.especialidad_id', '=', 'especialidades.id')
            ->where('derivaciones.consulta_id', $consulta_id)
            ->select(
                'derivaciones.id',
                'pacientes.nombre as paciente',
                'especialidades.nombre as especialidad',
                'derivaciones.hospital',
                'derivaciones.motivo',
                'derivaciones.prioridad',
                'derivaciones.estado',
                'derivaciones.created_at'
            )
            ->get();

        // Mapeamos para limpiar el motivo y asignar la prioridad basada en el texto
        $resultado = $derivaciones->map(function ($item) {
            $motivo = $item->motivo;
            $prioridadCalculada = $item->prioridad; // Valor por defecto en la BD ('media')

            // Detectamos si contiene algún patrón de triage en el texto (ej: "triage: AMARILLO")
            if (preg_match('/triage:\s*(VERDE|AMARILLO|ROJO)/i', $motivo, $matches)) {
                $color = strtoupper($matches[1]);

                // Asignamos prioridad según el color del triage
                if ($color === 'VERDE') $prioridadCalculada = 'baja';
                if ($color === 'AMARILLO') $prioridadCalculada = 'media';
                if ($color === 'ROJO') $prioridadCalculada = 'alta';

                // Eliminamos la frase del motivo para que quede limpio
                $motivo = preg_replace('/triage:\s*(VERDE|AMARILLO|ROJO)[\s,-]*/i', '', $motivo);
            }

            return [
                'id'           => $item->id,
                'paciente'     => $item->paciente,
                'especialidad' => $item->especialidad,
                'hospital'     => $item->hospital,
                'motivo'       => trim($motivo), // Motivo sin la palabra "triage: ..."
                'prioridad'    => $prioridadCalculada, // 'baja', 'media' o 'alta'
                'estado'       => $item->estado,
            ];
        });

        return response()->json($resultado);
    }

    public function obtenerEstadisticas()
    {
        $estadisticas = Derivacion::selectRaw("
            COUNT(*) as total_derivaciones,
            SUM(CASE WHEN prioridad = 'alta' THEN 1 ELSE 0 END) as alta_prioridad,
            SUM(CASE WHEN estado = 'enviado' THEN 1 ELSE 0 END) as canalizados
        ")->first();

        return response()->json($estadisticas);
    }
    


}