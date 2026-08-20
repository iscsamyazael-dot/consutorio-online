<?php

namespace App\Http\Controllers;

use App\Models\Derivacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DerivacionController extends Controller
{
    public function index(Request $request)
    {
        $fecha = $request->input('fecha');
        $especialidadId = $request->input('especialidad_id');

        $query = DB::table('derivaciones')
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
            );

        // Filtrar por especialidad
        if ($especialidadId) {
            $query->where(
                'derivaciones.especialidad_id',
                $especialidadId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Si se seleccionó una fecha
        |--------------------------------------------------------------------------
        |
        | Mostramos únicamente las derivaciones de esa fecha.
        |
        */
        if ($fecha) {

            $query->whereDate(
                'derivaciones.created_at',
                $fecha
            );

            $derivaciones = $query
                ->orderByDesc('derivaciones.created_at')
                ->get();

        } else {

            /*
            |--------------------------------------------------------------------------
            | Sin fecha: mostrar hoy
            |--------------------------------------------------------------------------
            */

            $hoy = Carbon::today();

            $derivaciones = (clone $query)
                ->whereDate(
                    'derivaciones.created_at',
                    $hoy
                )
                ->orderByDesc('derivaciones.created_at')
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Si hoy no hay derivaciones,
            | mostrar las más recientes
            |--------------------------------------------------------------------------
            */

            if ($derivaciones->isEmpty()) {

                $derivaciones = $query
                    ->orderByDesc('derivaciones.created_at')
                    ->limit(10)
                    ->get();
            }
        }

        $resultado = $derivaciones->map(function ($item) {

            $motivo = $item->motivo ?? '';

            // Calculamos la prioridad desde el motivo
            $prioridadCalculada =
                $this->obtenerPrioridadDesdeMotivo($motivo);

            // Eliminamos la parte del triage
            $motivo = preg_replace(
                '/triage:\s*(VERDE|AMARILLO|NARANJA|ROJO)\s*[\.\:\,\-]?\s*/i',
                '',
                $motivo
            );

            // Eliminamos caracteres al inicio
            $motivo = preg_replace(
                '/^[\s\.\,\-:]+/',
                '',
                $motivo
            );

            return [
                'id'           => $item->id,
                'paciente'     => $item->paciente,
                'especialidad' => $item->especialidad,
                'hospital'     => $item->hospital,
                'motivo'       => trim($motivo),
                'prioridad'    => $prioridadCalculada,
                'estado'       => $item->estado,
                'fecha'        => Carbon::parse(
                    $item->created_at
                )->format('d/m/Y H:i'),
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
        $atendidos = 0;

        foreach ($derivaciones as $derivacion) {

                // Evaluamos la prioridad generada a partir del motivo
            $prioridadCalculada = strtolower($this->obtenerPrioridadDesdeMotivo($derivacion->motivo) ?? '');

            // Casos críticos = detecta tanto 'critica' como 'alta'
            if ($prioridadCalculada === 'critica' || $prioridadCalculada === 'alta') {
                $casosCriticos++;
            }
            // Canalizados = estado enviado
            if (strtolower($derivacion->estado ?? '') === 'enviado') {
                $canalizados++;
            }

            // Atendidos = estado atendido
            if (strtolower($derivacion->estado ?? '') === 'atendido') {
                $atendidos++;
            }
        }

        return response()->json([
            'total_derivaciones' => $derivaciones->count(),
            'casos_criticos'     => $casosCriticos,
            'canalizados'        => $canalizados,
            'atendidos'          => $atendidos,
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