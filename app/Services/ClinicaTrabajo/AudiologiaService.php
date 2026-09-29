<?php

namespace App\Services\ClinicaTrabajo;

use App\Models\ClinicaTrabajo\ValoracionAudiologia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AudiologiaService
{
    /**
     * Crea una nueva valoración audiológica
     */
    public function crear(array $datos, int $usuarioId): ValoracionAudiologia
    {
        if (!isset($datos['folio'])) {
            $datos['folio'] = $this->generarFolio();
        }

        // Calcular PTA (promedio tonal puro) si hay audiometría
        if ($this->tieneAudiometria($datos)) {
            $datos['pta_promedio_tonal_puro_der'] = $this->calcularPTA(
                $datos['au_500_hz_der'] ?? 0,
                $datos['au_1000_hz_der'] ?? 0,
                $datos['au_2000_hz_der'] ?? 0,
                $datos['au_3000_hz_der'] ?? 0
            );
            $datos['pta_promedio_tonal_puro_izq'] = $this->calcularPTA(
                $datos['au_500_hz_izq'] ?? 0,
                $datos['au_1000_hz_izq'] ?? 0,
                $datos['au_2000_hz_izq'] ?? 0,
                $datos['au_3000_hz_izq'] ?? 0
            );

            // Clasificar tipo de hipoacusia
            $datos['tipo_hipoacusia'] = $this->clasificarHipoacusia(
                $datos['pta_promedio_tonal_puro_der'],
                $datos['pta_promedio_tonal_puro_izq']
            );
        }

        $valoracion = ValoracionAudiologia::create($datos);

        activity('audiologia')
            ->causedBy(Auth::user())
            ->performedOn($valoracion)
            ->log('created');

        return $valoracion;
    }

    /**
     * Actualiza una valoración existente
     */
    public function actualizar(int $id, array $datos): ValoracionAudiologia
    {
        $valoracion = ValoracionAudiologia::findOrFail($id);

        // Recalcular PTA si hay audiometría
        if ($this->tieneAudiometria($datos)) {
            $datos['pta_promedio_tonal_puro_der'] = $this->calcularPTA(
                $datos['au_500_hz_der'] ?? $valoracion->au_500_hz_der ?? 0,
                $datos['au_1000_hz_der'] ?? $valoracion->au_1000_hz_der ?? 0,
                $datos['au_2000_hz_der'] ?? $valoracion->au_2000_hz_der ?? 0,
                $datos['au_3000_hz_der'] ?? $valoracion->au_3000_hz_der ?? 0
            );
            $datos['pta_promedio_tonal_puro_izq'] = $this->calcularPTA(
                $datos['au_500_hz_izq'] ?? $valoracion->au_500_hz_izq ?? 0,
                $datos['au_1000_hz_izq'] ?? $valoracion->au_1000_hz_izq ?? 0,
                $datos['au_2000_hz_izq'] ?? $valoracion->au_2000_hz_izq ?? 0,
                $datos['au_3000_hz_izq'] ?? $valoracion->au_3000_hz_izq ?? 0
            );

            $datos['tipo_hipoacusia'] = $this->clasificarHipoacusia(
                $datos['pta_promedio_tonal_puro_der'],
                $datos['pta_promedio_tonal_puro_izq']
            );
        }

        $valoracion->update($datos);

        activity('audiologia')
            ->causedBy(Auth::user())
            ->performedOn($valoracion)
            ->log('updated');

        return $valoracion;
    }

    /**
     * Calcula el PTA (promedio de 500, 1000, 2000, 3000 Hz)
     */
    private function calcularPTA(int $f500, int $f1000, int $f2000, int $f3000): int
    {
        $valores = array_filter([$f500, $f1000, $f2000, $f3000], fn($v) => $v > 0);
        return $valores ? round(array_sum($valores) / count($valores)) : 0;
    }

    /**
     * Clasifica el tipo de hipoacusia según el PTA
     */
    private function clasificarHipoacusia(int $pta_der, int $pta_izq): string
    {
        $pta_promedio = ($pta_der + $pta_izq) / 2;

        if ($pta_promedio <= 20) return 'normal';
        if ($pta_promedio <= 40) return 'leve';
        if ($pta_promedio <= 60) return 'moderada';
        if ($pta_promedio <= 90) return 'severa';
        return 'profunda';
    }

    private function tieneAudiometria(array $datos): bool
    {
        return isset($datos['au_500_hz_der']) ||
               isset($datos['au_1000_hz_der']) ||
               isset($datos['au_2000_hz_der']) ||
               isset($datos['au_3000_hz_der']) ||
               isset($datos['au_500_hz_izq']) ||
               isset($datos['au_1000_hz_izq']) ||
               isset($datos['au_2000_hz_izq']) ||
               isset($datos['au_3000_hz_izq']);
    }

    private function generarFolio(): string
    {
        $año = now()->year;
        $ultimoNumero = ValoracionAudiologia::whereYear('created_at', $año)
            ->max(DB::raw('CAST(SUBSTRING(folio, -4) AS UNSIGNED)')) ?? 0;

        return "AUD-{$año}-" . str_pad($ultimoNumero + 1, 4, '0', STR_PAD_LEFT);
    }
}