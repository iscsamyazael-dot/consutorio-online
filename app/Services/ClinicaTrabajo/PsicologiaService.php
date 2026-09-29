<?php

namespace App\Services\ClinicaTrabajo;

use App\Models\ClinicaTrabajo\ValoracionPsicologica;
use App\Models\ClinicaTrabajo\CatalogoFactorPsicosocial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PsicologiaService
{
    /**
     * Crea una nueva valoración psicológica
     */
    public function crear(array $datos, int $usuarioId): ValoracionPsicologica
    {
        // Generar folio si no viene en datos
        if (!isset($datos['folio'])) {
            $datos['folio'] = $this->generarFolio();
        }

        // Crear la valoración principal
        $valoracion = ValoracionPsicologica::create($datos);

        // Sincronizar factores psicosociales (tabla puente)
        if (isset($datos['factores_ids']) && is_array($datos['factores_ids'])) {
            $this->sincronizarFactores($valoracion, $datos['factores_ids'], $datos['factores_severidad'] ?? []);
        }

        // Log de auditoría
        activity('psicologia')
            ->causedBy(Auth::user())
            ->performedOn($valoracion)
            ->log('created');

        return $valoracion;
    }

    /**
     * Actualiza una valoración existente
     */
    public function actualizar(int $id, array $datos): ValoracionPsicologica
    {
        $valoracion = ValoracionPsicologica::findOrFail($id);

        // Actualizar campos
        $valoracion->update($datos);

        // Sincronizar factores si vienen en datos
        if (isset($datos['factores_ids'])) {
            $this->sincronizarFactores($valoracion, $datos['factores_ids'], $datos['factores_severidad'] ?? []);
        }

        activity('psicologia')
            ->causedBy(Auth::user())
            ->performedOn($valoracion)
            ->log('updated');

        return $valoracion;
    }

    /**
     * Sincroniza la tabla puente valoracion_factores_psicosociales
     */
    private function sincronizarFactores(
        ValoracionPsicologica $valoracion,
        array $factoresIds,
        array $severidades = []
    ): void {
        $datos = [];
        foreach ($factoresIds as $index => $factorId) {
            $datos[$factorId] = [
                'presente' => true,
                'nivel_severidad' => $severidades[$index] ?? 'moderado',
                'observaciones' => null,
            ];
        }

        $valoracion->factoresPsicosociales()->sync($datos);
    }

    /**
     * Genera folio único: PSI-YYYY-NNNN
     */
    private function generarFolio(): string
    {
        $año = now()->year;
        $ultimoNumero = ValoracionPsicologica::whereYear('created_at', $año)
            ->max(DB::raw('CAST(SUBSTRING(folio, -4) AS UNSIGNED)')) ?? 0;

        $nuevoNumero = str_pad($ultimoNumero + 1, 4, '0', STR_PAD_LEFT);

        return "PSI-{$año}-{$nuevoNumero}";
    }
}