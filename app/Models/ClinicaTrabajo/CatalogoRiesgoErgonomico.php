<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ValoracionErgonomica;

class CatalogoRiesgoErgonomico extends Model
{
    protected $table = 'catalogo_riesgos_ergonomicos';

    protected $guarded = [];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function valoraciones(): BelongsToMany
    {
        return $this->belongsToMany(
            ValoracionErgonomica::class,
            'valoracion_riesgos_ergonomicos',
            'riesgo_id',
            'valoracion_id'
        )->withPivot('presente', 'medida_control')
         ->withTimestamps();
    }

    public function scopeActivos(Builder $query)
    {
        return $query->where('activo', true);
    }
}