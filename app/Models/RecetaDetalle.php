<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecetaDetalle extends Model
{
    protected $table = 'receta_detalles';
    protected $fillable = [
        'receta_id',
        'medicamento_id',
        'medicamento',
        'dosis',
        'frecuencia',
        'duracion',
        'via_administracion',
        'indicaciones',
        'observaciones'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    // RECETA
    public function receta()
    {
        return $this->belongsTo(Receta::class);
    }

    // MEDICAMENTO
    public function medicamentoRelacion()
    {
        return $this->belongsTo(Medicamento::class,'medicamento_id');
    }
}
