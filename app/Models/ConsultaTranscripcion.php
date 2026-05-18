<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultaTranscripcion extends Model
{
    /*public $timestamps = false;*/
    protected $table = 'consulta_transcripciones';

    protected $fillable = [
        'consulta_id',
        'consulta_folio',
        'sesion_consulta_id',
        'tipo_usuario',
        'mensaje',
        'timestamp_audio',
        'analizado_ia',
        'observaciones_ia'
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

    // SESIÓN
    public function sesion()
    {
        return $this->belongsTo(SesionConsulta::class,'sesion_consulta_id');
    }
}
