<?php

namespace App\Http\Controllers;
use App\Models\Derivacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DerivacionController extends Controller
{

   
    public function index()
    {
        $derivaciones = DB::table('derivaciones')
            ->join('consultas', 'derivaciones.consulta_id', '=', 'consultas.id')
            ->join('pacientes', 'consultas.paciente_id', '=', 'pacientes.id')
            ->join('especialidades', 'derivaciones.especialidad_id', '=', 'especialidades.id')
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
            ->orderByDesc('derivaciones.created_at')
            ->get();

        $resultado = $derivaciones->map(function ($item) {
            $motivo = $item->motivo;
            $prioridadCalculada = $item->prioridad;

            if (preg_match('/triage:\s*(VERDE|AMARILLO|NARANJA|ROJO)/i', $motivo, $matches)) {
                $color = strtoupper($matches[1]);

                if ($color === 'VERDE') $prioridadCalculada = 'baja';
                if ($color === 'AMARILLO') $prioridadCalculada = 'media';
                if ($color === 'NARANJA') $prioridadCalculada = 'alta';
                if ($color === 'ROJO') $prioridadCalculada = 'critica';

                $motivo = preg_replace('/triage:\s*(VERDE|AMARILLO|NARANJA|ROJO)\s*[\.\:\,\-]?\s*/i','',$motivo);
            }

            return [
                'id'           => $item->id,
                'paciente'     => $item->paciente,
                'especialidad' => $item->especialidad,
                'hospital'     => $item->hospital,
                'motivo'       => trim($motivo),
                'prioridad'    => $prioridadCalculada,
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