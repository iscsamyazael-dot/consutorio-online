<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PuestoNormaAplicable extends Model
{
    use HasFactory;

    protected $table = 'puesto_norma_aplicable';

    protected $fillable = [
        'puesto_trabajo_id',
        'norma_id',
        'origen',
        'confirmada_por_medico',
    ];

    protected $casts = [
        'confirmada_por_medico' => 'boolean',
    ];

    public function puestoTrabajo(): BelongsTo
    {
        return $this->belongsTo(PuestoTrabajo::class, 'puesto_trabajo_id');
    }

    public function normaOficial(): BelongsTo
    {
        return $this->belongsTo(NormaOficial::class, 'norma_id');
    }
}