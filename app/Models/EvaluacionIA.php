<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluacionIA extends Model
{
     protected $table = 'evaluaciones_ia';

    protected $fillable = [
        'consulta_id',
        'resultado_general',
        'nivel_riesgo',
        'probabilidad_diagnostico',
        'modelo_ia',
        'version_modelo',
        'tiempo_respuesta',
        'observaciones',
        'estado'

    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    // CONSULTA
    public function consulta()
    {
        return $this->belongsTo( Consulta::class);
    }

    // DIAGNÓSTICOS IA
    public function diagnosticos()
    {
        return $this->hasMany(DiagnosticoIA::class);
    }

}
