<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ValoracionNutricion;
use App\Models\CatalogoAlimento;

class ValoracionAlimento extends Model
{
    protected $table = 'valoracion_alimentos';

    protected $guarded = [];

    protected $casts = [
        'frecuencia_texto' => 'string',
    ];

    public function valoracion(): BelongsTo
    {
        return $this->belongsTo(ValoracionNutricion::class);
    }

    public function alimento(): BelongsTo
    {
        return $this->belongsTo(CatalogoAlimento::class);
    }
}