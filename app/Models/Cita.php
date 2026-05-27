<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Cita extends Model
{
    protected $table = 'citas';

    protected $fillable = [
        'paciente_id',
        'user_id',
        'fecha_hora',
        'duracion',
        'duracion_minutos',
        'estado',
        'motivo',
        'notas',
        'tipo_cita',
        'ubicacion',
        'color',
        'confirmada_paciente',
        'recordatorio_enviado',
        'razon_cancelacion',
        'cancelada_en',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'cancelada_en' => 'datetime',
        'confirmada_paciente' => 'boolean',
        'recordatorio_enviado' => 'boolean',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
