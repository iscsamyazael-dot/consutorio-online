<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValoracionEstudioParaclinico extends Model
{
    use HasFactory;

    protected $table = 'valoracion_estudios_paraclinicos';

    /**
     * ASUNCIÓN A CONFIRMAR: el SQL base (consultorio_online_template_v8.sql) no
     * incluye created_at/updated_at en esta tabla, pero la sesión de diseño
     * registra que se agregaron como corrección aplicada en las 4 bases reales.
     * Si en tu BD esta tabla NO tiene esas columnas, cambia esto a `false`.
     */
    public $timestamps = true;

    protected $fillable = [
        'valoracion_id',
        'tipo_estudio',
        'laboratorio',
        'fecha_estudio',
        'resultados',
        'interpretacion',
        'archivo_url',
    ];

    protected $casts = [
        'fecha_estudio' => 'date',
        'resultados' => 'array',
    ];

    public function valoracionOcupacional(): BelongsTo
    {
        return $this->belongsTo(ValoracionOcupacional::class, 'valoracion_id');
    }
}