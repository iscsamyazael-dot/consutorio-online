<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecomendacionIA extends Model
{
    protected $table = 'recomendaciones_ia';
    protected $fillable = [
        'consulta_id',
        'evaluacion_ia_id',
        'tipo_recomendacion',
        'titulo',
        'descripcion',
        'prioridad',
        'estado',
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
