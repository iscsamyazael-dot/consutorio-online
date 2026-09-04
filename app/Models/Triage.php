<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Triage extends Model
{
    use HasFactory;

    protected $table = 'triage';

   protected $fillable = [
    'triage_codigo',
    'paciente_id',
    'lista_espera_id',
    'codigo_paciente',
    'usuario_triage_id',
    'presion',
    'saturacion',
    'temperatura',
    'peso',
    'talla',
    'imc',
    'imc_percentil',
    'imc_clasificacion',
    'frecuencia_cardiaca',
    'frecuencia_respiratoria',
    'sintomas',
    'motivo_consulta',
    'estado',
    'nivel_urgencia',
    'evaluacion_ia',
    'requiere_medico',
    'historial_ediciones'
];
    
    protected $casts = [
        'historial_ediciones' => 'array',
    ];
    /**
     * El triage pertenece a un paciente
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    /**
     * Usuario que realizó el triages
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_triage_id');
    }

    /**
     * Un triage genera una consulta
     */
    public function consulta()
    {
        return $this->hasOne(Consulta::class, 'triage_id');
    }

    //Relacion del Triage a la lista de espera//
    public function listaEspera()
    {
        return $this->belongsTo(ListaEspera::class, 'lista_espera_id');
    }
}

