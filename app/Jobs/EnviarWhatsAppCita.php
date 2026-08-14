<?php

namespace App\Jobs;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class EnviarWhatsAppCita implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $cita;

    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
    }

    public function handle(): void
    {
        $mensaje =
            "🏥 CONSULTORIO MÉDICO \n\n" .
            "Hola {$this->cita->paciente_nombre}, tu consulta fue registrada correctamente.\n\n" .
            "Fecha: {$this->cita->fecha}\n" .
            "Hora: {$this->cita->hora_cita}\n" .
            "Estado: {$this->cita->estado}";

        Http::asForm()->post(
            'https://api.callmebot.com/whatsapp.php',
            [
                'phone'  => $this->cita->telefono,
                'text'   => $mensaje,
                'apikey' => '7268112'
            ]
        );
    }
}