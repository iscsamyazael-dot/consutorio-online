<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ValoracionNutricion;

class CatalogoAlimento extends Model
{
    protected $table = 'catalogo_alimentos';

    protected $guarded = [];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function valoraciones(): BelongsToMany
    {
        return $this->belongsToMany(
            ValoracionNutricion::class,
            'valoracion_alimentos',
            'alimento_id',
            'valoracion_nutricion_id'
        )->withPivot('frecuencia_texto')
         ->withTimestamps();
    }

    public function scopeActivos(Builder $query)
    {
        return $query->where('activo', true);
    }
}