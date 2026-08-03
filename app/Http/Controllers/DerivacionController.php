<?php

namespace App\Http\Controllers;

use App\Models\Derivacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
                'derivaciones.estado',
                'pacientes.paciente_id',
                'derivaciones.created_at'
            )
            ->orderByDesc('derivaciones.created_at')
            ->get();

        $resultado = $derivaciones->map(function ($item) {

            $motivo = $item->motivo ?? '';

            // Calculamos la prioridad desde el motivo
            $prioridadCalculada = $this->obtenerPrioridadDesdeMotivo($motivo);

            // Eliminamos la parte del triage del texto
            $motivo = preg_replace(
                '/triage:\s*(VERDE|AMARILLO|NARANJA|ROJO)\s*[\.\:\,\-]?\s*/i',
                '',
                $motivo
            );

            // Eliminamos puntos, comas o espacios al inicio
            $motivo = preg_replace('/^[\s\.\,\-:]+/', '', $motivo);

            return [
                'id'           => $item->id,
                'paciente'     => $item->paciente,
                'especialidad' => $item->especialidad,
                'hospital'     => $item->hospital,
                'motivo'       => trim($motivo),
                'prioridad'    => $prioridadCalculada,
                'estado'       => $item->estado,
                'fecha'        => Carbon::parse($item->created_at)->format('d/m/Y H:i'),
                'folio'        => $item->paciente_id,
            ];
        });

        return response()->json($resultado);
    }

    public function obtenerEstadisticas()
    {
        $derivaciones = Derivacion::all();

        $casosCriticos = 0;
        $canalizados = 0;

        foreach ($derivaciones as $derivacion) {

            if ($this->obtenerPrioridadDesdeMotivo($derivacion->motivo) === 'critica') {
                $casosCriticos++;
            }

            if ($derivacion->estado === 'enviado') {
                $canalizados++;
            }
        }

        return response()->json([
            'total_derivaciones' => $derivaciones->count(),
            'casos_criticos'     => $casosCriticos,
            'canalizados'        => $canalizados,
        ]);
    }

    public function actualizarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,enviado,atendido'
        ]);

        $derivacion = Derivacion::findOrFail($id);

        $derivacion->estado = $request->estado;
        $derivacion->save();

        return response()->json([
            'message' => 'Estado actualizado correctamente.',
            'estado'  => $derivacion->estado
        ]);
    }

    /**
     * Obtiene la prioridad a partir del texto del motivo.
     */
    private function obtenerPrioridadDesdeMotivo($motivo)
    {
        if (!$motivo) {
            return 'media';
        }

        if (preg_match('/triage:\s*(VERDE|AMARILLO|NARANJA|ROJO)/i', $motivo, $matches)) {

            switch (strtoupper($matches[1])) {

                case 'VERDE':
                    return 'baja';

                case 'AMARILLO':
                    return 'media';

                case 'NARANJA':
                    return 'alta';

                case 'ROJO':
                    return 'critica';
            }
        }

        return 'media';
    }
}