<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automatización para cambiar el estado de la cita de agenado a inasistencia
Schedule::command('app:actualizar-citas-inasistencias')->everyTenMinutes();