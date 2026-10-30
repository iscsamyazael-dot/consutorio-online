<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Medico;

class EspirometriaIaMensaje extends Model
{
    use HasFactory;

    protected $table = 'espirometria_ia_mensajes';

    protected $guarded = [];

    protected $casts = [
        'campos_propuestos' => 'array',
        'aceptado_medico' => 'boolean',
    ];

    public function valoracion(): BelongsTo
    {
        return $this->belongsTo(ValoracionEspirometria::class, 'valoracion_id');
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }
}