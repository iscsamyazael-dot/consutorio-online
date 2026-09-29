<?php

namespace App\Services\ClinicaTrabajo;

use App\Models\ClinicaTrabajo\ValoracionNutricion;
use App\Models\ClinicaTrabajo\CatalogoAlimento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class NutricionService
{
    /**
     * Crea una nueva valoración nutricional
     */
    public function crear(array $datos, int $usuarioId): ValoracionNutricion
    {
        // Generar folio
        if (!isset($datos['folio'])) {
            $datos['folio'] = $this->generarFolio();
        }

        // Calcular IMC si hay peso y estatura
        if (isset($datos['peso_kg']) && isset($datos['estatura_m']) && $datos['estatura_m'] > 0) {
            $datos['imc'] = round(
                $datos['peso_kg'] / ($datos['estatura_m'] ** 2),
                2
            );
            $datos['imc_clasificacion'] = $this->clasificarIMC($datos['imc']);
        }

        // Interpretar escalas
        if (isset($datos['escala_fagerstrm']) && $datos['escala_fagerstrm'] > 0) {
            $datos['tabaquismo'] = true;
        }
        if (isset($datos['escala_audit']) && $datos['escala_audit'] > 0) {
            $datos['alcoholismo'] = true;
        }

        $valoracion = ValoracionNutricion::create($datos);

        // Sincronizar alimentos
        if (isset($datos['alimentos_ids'])) {
            $this->sincronizarAlimentos($valoracion, $datos);
        }

        activity('nutricion')
            ->causedBy(Auth::user())
            ->performedOn($valoracion)
            ->log('created');

        return $valoracion;
    }

    /**
     * Actualiza una valoración existente
     */
    public function actualizar(int $id, array $datos): ValoracionNutricion
    {
        $valoracion = ValoracionNutricion::findOrFail($id);

        // Recalcular IMC si cambió peso o estatura
        if (isset($datos['peso_kg']) || isset($datos['estatura_m'])) {
            $peso = $datos['peso_kg'] ?? $valoracion->peso_kg;
            $estatura = $datos['estatura_m'] ?? $valoracion->estatura_m;

            if ($peso && $estatura && $estatura > 0) {
                $datos['imc'] = round($peso / ($estatura ** 2), 2);
                $datos['imc_clasificacion'] = $this->clasificarIMC($datos['imc']);
            }
        }

        $valoracion->update($datos);

        if (isset($datos['alimentos_ids'])) {
            $this->sincronizarAlimentos($valoracion, $datos);
        }

        activity('nutricion')
            ->causedBy(Auth::user())
            ->performedOn($valoracion)
            ->log('updated');

        return $valoracion;
    }

    /**
     * Clasifica el IMC según estándares OMS
     */
    private function clasificarIMC(float $imc): string
    {
        if ($imc < 18.5) return 'Bajo peso';
        if ($imc < 25) return 'Normal';
        if ($imc < 30) return 'Sobrepeso';
        if ($imc < 35) return 'Obesidad I';
        if ($imc < 40) return 'Obesidad II';
        return 'Obesidad III';
    }

    private function sincronizarAlimentos(ValoracionNutricion $valoracion, array $datos): void
    {
        $alimentos = [];
        foreach ($datos['alimentos_ids'] as $index => $alimentoId) {
            $alimentos[$alimentoId] = [
                'frecuencia_texto' => $datos['alimentos_frecuencias'][$index] ?? 'otro',
            ];
        }

        $valoracion->alimentos()->sync($alimentos);
    }

    private function generarFolio(): string
    {
        $año = now()->year;
        $ultimoNumero = ValoracionNutricion::whereYear('created_at', $año)
            ->max(DB::raw('CAST(SUBSTRING(folio, -4) AS UNSIGNED)')) ?? 0;

        return "NUT-{$año}-" . str_pad($ultimoNumero + 1, 4, '0', STR_PAD_LEFT);
    }
}