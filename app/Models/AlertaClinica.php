<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertaClinica extends Model
{
    protected $table = 'alertas_clinicas';
    protected $fillable = [
        'consulta_id',
        'paciente_id',
        'tipo_alerta',
        'titulo',
        'descripcion',
        'nivel_riesgo',
        'estado',
        'generada_por_ia',
        'requiere_atencion',
        'fecha_alerta',
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

    // PACIENTE
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }
}
