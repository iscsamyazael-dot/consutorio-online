<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ValoracionErgonomica;
use App\Models\CatalogoRiesgoErgonomico;

class ValoracionRiesgoErgonomico extends Model
{
    protected $table = 'valoracion_riesgos_ergonomicos';

    protected $guarded = [];

    protected $casts = [
        'presente' => 'boolean',
        'medida_control' => 'string',
    ];

    public function valoracion(): BelongsTo
    {
        return $this->belongsTo(ValoracionErgonomica::class);
    }

    public function riesgo(): BelongsTo
    {
        return $this->belongsTo(CatalogoRiesgoErgonomico::class);
    }
}