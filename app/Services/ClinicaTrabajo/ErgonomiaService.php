<?php

namespace App\Services\ClinicaTrabajo;

use App\Models\ClinicaTrabajo\ValoracionErgonomica;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ErgonomiaService
{
    /**
     * Crea una nueva valoración ergonómica
     */
    public function crear(array $datos, int $usuarioId): ValoracionErgonomica
    {
        if (!isset($datos['folio'])) {
            $datos['folio'] = $this->generarFolio();
        }

        $valoracion = ValoracionErgonomica::create($datos);

        // Sincronizar riesgos ergonómicos
        if (isset($datos['riesgos_ids'])) {
            $this->sincronizarRiesgos($valoracion, $datos);
        }

        activity('ergonomia')
            ->causedBy(Auth::user())
            ->performedOn($valoracion)
            ->log('created');

        return $valoracion;
    }

    /**
     * Actualiza una valoración existente
     */
    public function actualizar(int $id, array $datos): ValoracionErgonomica
    {
        $valoracion = ValoracionErgonomica::findOrFail($id);
        $valoracion->update($datos);

        if (isset($datos['riesgos_ids'])) {
            $this->sincronizarRiesgos($valoracion, $datos);
        }

        activity('ergonomia')
            ->causedBy(Auth::user())
            ->performedOn($valoracion)
            ->log('updated');

        return $valoracion;
    }

    private function sincronizarRiesgos(ValoracionErgonomica $valoracion, array $datos): void
    {
        $riesgos = [];
        foreach ($datos['riesgos_ids'] as $index => $riesgoId) {
            $riesgos[$riesgoId] = [
                'presente' => true,
                'medida_control' => $datos['riesgos_controles'][$index] ?? null,
            ];
        }

        $valoracion->riesgosErgonomicos()->sync($riesgos);
    }

    private function generarFolio(): string
    {
        $año = now()->year;
        $ultimoNumero = ValoracionErgonomica::whereYear('created_at', $año)
            ->max(DB::raw('CAST(SUBSTRING(folio, -4) AS UNSIGNED)')) ?? 0;

        return "ERG-{$año}-" . str_pad($ultimoNumero + 1, 4, '0', STR_PAD_LEFT);
    }
}