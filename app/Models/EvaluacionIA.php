<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluacionIA extends Model
{
     protected $table = 'evaluaciones_ia';

    protected $fillable = [
        'consulta_id',
        'consulta_folio',
        'session_uuid',
        'sintomas_detectados',
        'diagnostico_probable',
        'recomendacion',
        'confianza'

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
