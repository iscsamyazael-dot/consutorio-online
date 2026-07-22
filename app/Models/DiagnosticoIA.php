<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosticoIA extends Model
{
    protected $table = 'diagnosticos_ia';
    protected $fillable = [
        'consulta_id',
        'evaluacion_ia_id',
        'diagnostico',
        'descripcion',
        'nivel_confianza',
        'prioridad',
        'requiere_validacion',
        'validado_medico',
        'observaciones'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    // CONSULTA
    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }

    // EVALUACIÓN IA
    public function evaluacionIA()
    {
        return $this->belongsTo(EvaluacionIA::class);
    }
}
