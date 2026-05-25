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
        'estado',
        'motivo',
        'notas',
        'tipo_cita',
        'ubicacion',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
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
