<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cita;

class ActualizarCitasInasistencias extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:actualizar-citas-inasistencias';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Usamos 'now()' que es la hora que Laravel tiene configurada
        $fechaHoy = now()->format('Y-m-d');
        $horaLimite = now()->subHour()->format('H:i:s');

        $this->info("Hora actual del sistema: " . now()->format('H:i:s'));
        $this->info("Buscando citas de hoy ($fechaHoy) con hora <= $horaLimite");

        $citas = Cita::where('estado', 'Agendado')
            ->where('fecha', $fechaHoy)
            ->where('hora', '<=', $horaLimite)
            ->get();

        if ($citas->isEmpty()) {
            $this->info("No se encontraron citas para actualizar.");
        }

        foreach ($citas as $cita) {
            $cita->update(['estado' => 'Inasistencia']);
            $this->info("Cita {$cita->folio} marcada como Inasistencia.");
        }
    }
}
