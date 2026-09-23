<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValoracionSeguimiento extends Model
{
    use HasFactory;

    protected $table = 'valoracion_seguimiento';

    /**
     * ASUNCIÓN A CONFIRMAR: mismo caso que las otras 2 tablas hijas — ajusta a
     * `false` si tu BD real no tiene created_at/updated_at aquí.
     */
    public $timestamps = true;

    protected $fillable = [
        'valoracion_id',
        'tipo_revaloracion',
        'plazo',
        'objetivo',
        'completado',
        'fecha_completado',
    ];

    protected $casts = [
        'completado' => 'boolean',
        'fecha_completado' => 'date',
    ];

    public function valoracionOcupacional(): BelongsTo
    {
        return $this->belongsTo(ValoracionOcupacional::class, 'valoracion_id');
    }
}