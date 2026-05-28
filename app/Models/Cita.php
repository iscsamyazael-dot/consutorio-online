<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $fillable = ['paciente_id', 'fecha_cita', 'hora_cita', 'estado', 'motivo'];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }
}