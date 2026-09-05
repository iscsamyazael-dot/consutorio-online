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
        'diagnostico_icd11_codigo',   
        'diagnostico_icd11_titulo',   
        'recomendaciones_medico',     
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
        return $this->belongsTo(Medico::class, 'user_id','user_id');
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
        return $this->hasMany(Receta::class, 'consulta_id');
    }

    public function derivaciones()
    {
        return $this->hasMany(Derivacion::class, 'consulta_id');
    }

    public function alertasClinicas()
    {
        return $this->hasMany(AlertaClinica::class, 'consulta_id');
    }

    public function especialidad()
    {
        // Opción A: Si usas una relación BelongsTo directa con clave foránea especialidad_id
        return $this->belongsTo(Especialidad::class, 'especialidad_id');

        // Opción B: Si guardas el ID o Slug en especialidad_sugerida
         //return $this->belongsTo(Especialidad::class, 'especialidad_sugerida');
    }

    public function archivos()
    {
        return $this->hasMany(ArchivoClinico::class);
    }

    public function evaluacionIA()
    {
        return $this->belongsTo(EvaluacionIA::class, 'evaluacion_ia_id');
    }

    public function eventosIA()
    {
        return $this->hasMany(EventoIA::class, 'consulta_id');
    }

    public function notaPsoapp()
    {
        return $this->hasOne(NotaPsoapp::class, 'consulta_id');
         return $this->hasOne(NotaPsoapp::class);
    }
}