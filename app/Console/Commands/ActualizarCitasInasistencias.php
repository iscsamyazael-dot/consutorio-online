<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cita;
use Carbon\Carbon;

class ActualizarCitasInasistencias extends Command
{
    protected $signature = 'app:actualizar-citas-inasistencias';
    protected $description = 'Marca como Inasistencia las citas que ya pasaron de su hora programada, con un margen de tolerancia';

    public function handle()
    {
        // Forzamos la hora exacta actual de México
        $ahora = Carbon::now('America/Mexico_City');
        $fechaHoy = $ahora->format('Y-m-d');

        // Margen de tolerancia: una cita solo se marca como Inasistencia
        // si ya pasaron 30 minutos de su hora programada. Sin esto, una
        // cita creada "ahora mismo" (ej. al agregar un paciente desde la
        // búsqueda en ConsultaClinica.vue, que usa la hora actual como
        // hora de la cita) se marcaba Inasistencia en la siguiente
        // corrida del cron (cada 10 min), aunque el médico apenas la
        // fuera a atender.
        $limite = $ahora->copy()->subMinutes(30)->format('H:i:s');

        $this->info("Hora actual de México: " . $ahora->format('H:i:s'));
        $this->info("Buscando citas de hoy ($fechaHoy) con hora <= $limite (30 min de tolerancia)");

        // Buscamos citas agendadas de hoy cuya hora ya pasó, dando margen
        $citas = Cita::where('estado', 'Agendado')
            ->where('fecha', $fechaHoy)
            ->where('hora', '<=', $limite)
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