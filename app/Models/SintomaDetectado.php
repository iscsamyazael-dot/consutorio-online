<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SintomaDetectado extends Model
{
    /*public $timestamps = false;*/
    protected $table = 'sintomas_detectados';
    protected $fillable = [
        'consulta_id',
        'consulta_folio',
        'session_uuid',
        'nombre_sintoma',
        'descripcion',
        'nivel_confianza',
        'detectado_por_ia',
        'estado',
        'intensidad',
        'origen',
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

}
