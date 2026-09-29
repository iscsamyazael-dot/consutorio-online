<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ValoracionPsicologica;

class CatalogoFactorPsicosocial extends Model
{
    protected $table = 'catalogo_factores_psicosociales';

    protected $guarded = [];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function valoraciones(): BelongsToMany
    {
        return $this->belongsToMany(
            ValoracionPsicologica::class,
            'valoracion_factores_psicosociales',
            'factor_id',
            'valoracion_id'
        )->withPivot('presente', 'nivel_severidad', 'observaciones')
         ->withTimestamps();
    }

    public function scopeActivos(Builder $query)
    {
        return $query->where('activo', true);
    }
}