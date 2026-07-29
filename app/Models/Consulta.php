<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\NotaPsoapp;


class Consulta extends Model
{
    protected $table = 'consultas';

    protected $fillable = [
        'folio',
        'paciente_id',
        'user_id',
        'motivo_consulta',
        'diagnostico',
        'observaciones',
        'origen',
        'nivel_urgencia',
        'clasificacion_ia',
        'estado',
        'requiere_especialista',
        'especialidad_sugerida',
        'evaluacion_ia_id',
        'tipo_consulta',
        'consulta_inteligente',
        'resumen_ia',
        'transcripcion_activa',
        'audio_consulta',
        'estado_consulta'
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sintomas()
    {
        return $this->hasMany(SintomaDetectado::class);
    }

    public function transcripciones()
    {
        return $this->hasMany(ConsultaTranscripcion::class);
    }

    public function recetas()
    {
        return $this->hasMany(Receta::class);
    }

    public function archivos()
    {
        return $this->hasMany(ArchivoClinico::class);
    }

    public function evaluacionIA()
    {
        return $this->belongsTo(EvaluacionIA::class, 'evaluacion_ia_id');
    }

    public function notaPsoapp()
    {
        return $this->hasOne(NotaPsoapp::class);
    }
    public function eventosIA()
{
    return $this->hasMany(EventoIA::class, 'consulta_id');
}
public function notaPsoapp()
{
    return $this->hasOne(NotaPsoapp::class, 'consulta_id');
}
}