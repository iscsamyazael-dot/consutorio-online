<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaPsoapp extends Model
{
    protected $table = 'notas_psoapp';

    protected $fillable = [
        'evaluacion_ia_id',
        'consulta_id',
        'consulta_folio',
        'session_uuid',
        'presentacion',
        'subjetivo',
        'objetivo',
        'analisis',
        'plan',
        'pronostico',
        'estado',
    ];

    public function evaluacionIA()
    {
        return $this->belongsTo(EvaluacionIA::class, 'evaluacion_ia_id');
    }

    public function consulta()
    {
        return $this->belongsTo(Consulta::class, 'consulta_id');
    }
}