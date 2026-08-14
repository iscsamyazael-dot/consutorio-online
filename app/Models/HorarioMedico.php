<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioMedico extends Model
{
    use HasFactory;

    protected $table = 'horarios_medicos';

    protected $fillable = [
        'medico_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'duracion_consulta',
        'ubicacion_id'
    ];

    // Relación inversa: Un horario le pertenece a un médico
    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }
}