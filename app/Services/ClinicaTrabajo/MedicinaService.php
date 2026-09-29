<?php

namespace App\Services\ClinicaTrabajo;

use App\Models\ClinicaTrabajo\ValoracionOcupacional;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Paciente;
use App\Models\Medico;
use App\Models\EmpresaCliente;
use App\Models\PuestoTrabajo;

class MedicinaService
{
    /**
     * Crea una nueva valoración médica/ocupacional
     */
    public function crear(array $datos, int $usuarioId): ValoracionOcupacional
    {
        // Generar folio único: FO-YYYYMMDD-NNNN
        if (!isset($datos['folio'])) {
            $datos['folio'] = $this->generarFolio();
        }

        $valoracion = ValoracionOcupacional::create($datos);

        activity('medicina')
            ->causedBy(Auth::user())
            ->performedOn($valoracion)
            ->log('created');

        return $valoracion;
    }

    /**
     * Actualiza una valoración existente
     */
    public function actualizar(int $id, array $datos): ValoracionOcupacional
    {
        $valoracion = ValoracionOcupacional::findOrFail($id);
        $valoracion->update($datos);

        activity('medicina')
            ->causedBy(Auth::user())
            ->performedOn($valoracion)
            ->log('updated');

        return $valoracion;
    }

    private function generarFolio(): string
    {
        $año = now()->year;
        $mes = now()->month;
        $ultimoNumero = ValoracionOcupacional::whereYear('created_at', $año)
            ->whereRaw('MONTH(created_at) = ?', [$mes])
            ->max(DB::raw('CAST(SUBSTRING(folio, 4, 4) AS UNSIGNED)')) ?? 0;

        $nuevoNumero = str_pad($ultimoNumero + 1, 4, '0', STR_PAD_LEFT);

        return "FO-{$año}{str_pad($mes, 2, '0', STR_PAD_LEFT)}-{$nuevoNumero}";
    }
}