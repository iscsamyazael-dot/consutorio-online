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
        // Buscamos citas 'Agendado' que corresponden a hoy y cuya hora ya pasó
        $citas = Cita::where('estado', 'Agendado')
            ->where('fecha', now()->format('Y-m-d'))
            ->where('hora', '<=', now()->subHour()->format('H:i:s'))
            ->get();

        foreach ($citas as $cita) {
            $cita->update(['estado' => 'Inasistencia']);
            $this->info("Cita ID {$cita->id} actualizada a Inasistencia.");
        }
    }
}
