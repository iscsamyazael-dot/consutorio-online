<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Model;

class ErgonomiaIaMensaje extends Model
{
    protected $table = 'ergonomia_ia_mensajes';

    protected $guarded = [];

    protected $casts = [
        'campos_propuestos' => 'array',
        'aceptado_medico' => 'boolean',
    ];

    public function valoracion()
    {
        return $this->belongsTo(ValoracionErgonomica::class, 'valoracion_id');
    }

    public function medico()
    {
        return $this->belongsTo(\App\Models\Medico::class, 'medico_id');
    }
}