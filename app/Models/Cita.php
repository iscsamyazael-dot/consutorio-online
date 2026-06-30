<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $fillable = [
        'folio',
        'paciente_id',
        'especialidad_id',
        'medico_id',
        'fecha',
        'hora',
        'tipo',
        'estado',
        'observaciones',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }
}