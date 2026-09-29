<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\ValoracionPsicologica;
use App\Models\CatalogoFactorPsicosocial;

class ValoracionFactorPsicosocial extends Model
{
    protected $table = 'valoracion_factores_psicosociales';

    protected $guarded = [];

    protected $casts = [
        'presente' => 'boolean',
        'nivel_severidad' => 'string',
    ];

    public function valoracion(): BelongsTo
    {
        return $this->belongsTo(ValoracionPsicologica::class);
    }

    public function factor(): BelongsTo
    {
        return $this->belongsTo(CatalogoFactorPsicosocial::class);
    }
}