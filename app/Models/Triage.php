<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Triage extends Model
{
    use HasFactory;

    protected $table = 'triage';

    protected $fillable = [
        'paciente_id',
        'presion',
        'saturacion',
        'temperatura',
        'sintomas',
        'estado',
        'nivel_urgencia',
        'evaluacion_ia',
        'requiere_medico'
    ];

    /**
     * Relación: un triage pertenece a un paciente
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }
}