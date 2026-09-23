<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NormaOficial extends Model
{
    use HasFactory;

    protected $table = 'normas_oficiales';

    protected $fillable = [
        'codigo',
        'titulo',
        'dependencia',
        'categoria',
        'genera_dato_clinico',
        'vigente',
        'fecha_publicacion_dof',
        'norma_sustituida_id',
        'resumen',
    ];

    protected $casts = [
        'genera_dato_clinico' => 'boolean',
        'vigente' => 'boolean',
        'fecha_publicacion_dof' => 'date',
    ];

    /**
     * La norma que esta norma sustituyó (ej. NOM-017-STPS-2024 -> apunta a NOM-017-STPS-2008).
     */
    public function normaSustituida(): BelongsTo
    {
        return $this->belongsTo(NormaOficial::class, 'norma_sustituida_id');
    }

    /**
     * Normas más nuevas que sustituyeron a esta.
     */
    public function normasQueLaSustituyen(): HasMany
    {
        return $this->hasMany(NormaOficial::class, 'norma_sustituida_id');
    }

    public function puestoNormaAplicable(): HasMany
    {
        return $this->hasMany(PuestoNormaAplicable::class, 'norma_id');
    }

    public function puestosTrabajo(): BelongsToMany
    {
        return $this->belongsToMany(PuestoTrabajo::class, 'puesto_norma_aplicable', 'norma_id', 'puesto_trabajo_id')
            ->withPivot(['origen', 'confirmada_por_medico'])
            ->withTimestamps();
    }

    public function valoracionNormaAplicada(): HasMany
    {
        return $this->hasMany(ValoracionNormaAplicada::class, 'norma_id');
    }

    public function valoracionesOcupacionales(): BelongsToMany
    {
        return $this->belongsToMany(ValoracionOcupacional::class, 'valoracion_norma_aplicada', 'norma_id', 'valoracion_id')
            ->withPivot(['aplicacion_especifica']);
    }
}