<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cita;
use Carbon\Carbon;

class ActualizarCitasInasistencias extends Command
{
    protected $signature = 'app:actualizar-citas-inasistencias';
    protected $description = 'Marca como Inasistencia las citas que ya pasaron de su hora programada';

    public function handle()
    {
        // Forzamos la hora exacta actual de México
        $ahora = Carbon::now('America/Mexico_City');
        $fechaHoy = $ahora->format('Y-m-d');
        
        // Si quieres darles una tolerancia (ej. 15 o 30 minutos después de su hora), 
        // puedes usar ->subMinutes(15). Si quieres que sea exactamente al cumplirse la hora, déjalo solo con $ahora->format('H:i:s')
        $horaActual = $ahora->format('H:i:s');

        $this->info("Hora actual de México: " . $horaActual);
        $this->info("Buscando citas de hoy ($fechaHoy) con hora <= $horaActual");

        // Buscamos citas agendadas de hoy cuya hora ya pasó
        $citas = Cita::where('estado', 'Agendado')
            ->where('fecha', $fechaHoy)
            ->where('hora', '<=', $horaActual)
            ->get();

        if ($citas->isEmpty()) {
            $this->info("No se encontraron citas para actualizar.");
            return;
        }

        foreach ($citas as $cita) {
            $cita->update(['estado' => 'Inasistencia']);
            $this->info("Cita {$cita->folio} marcada como Inasistencia.");
        }
    }
}
