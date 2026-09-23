<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValoracionNormaAplicada extends Model
{
    use HasFactory;

    protected $table = 'valoracion_norma_aplicada';

    /**
     * ASUNCIÓN A CONFIRMAR: mismo caso que ValoracionEstudioParaclinico — el SQL
     * base no trae created_at/updated_at aquí, pero se registra como corrección
     * ya aplicada en las bases reales. Ajusta a `false` si no es el caso.
     */
    public $timestamps = true;

    protected $fillable = [
        'valoracion_id',
        'norma_id',
        'aplicacion_especifica',
    ];

    public function valoracionOcupacional(): BelongsTo
    {
        return $this->belongsTo(ValoracionOcupacional::class, 'valoracion_id');
    }

    public function normaOficial(): BelongsTo
    {
        return $this->belongsTo(NormaOficial::class, 'norma_id');
    }
}