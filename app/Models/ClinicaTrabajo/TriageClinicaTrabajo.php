<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TriageClinicaTrabajo extends Model
{
    use HasFactory;

    protected $table = 'triage_clinica_trabajo';

    protected $fillable = [
        'folio',
        'paciente_clinica_id',
        'valoracion_id',
        'usuario_triage_id',
        'fecha_triage',
        'motivo_consulta',
        'sintomas',
        'ta_sistolica',
        'ta_diastolica',
        'fc',
        'fr',
        'spo2',
        'temperatura_c',
        'peso_kg',
        'estatura_cm',
        'imc',
        'imc_clasificacion',
        'nivel_urgencia',
        'requiere_medico',
        'evaluacion_ia',
        'observaciones',
        'historial_ediciones',
        'deleted_at',
    ];

    protected $dates = [
        'fecha_triage',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'fecha_triage' => 'datetime',
        'ta_sistolica' => 'integer',
        'ta_diastolica' => 'integer',
        'fc' => 'integer',
        'fr' => 'integer',
        'spo2' => 'decimal:2',
        'temperatura_c' => 'decimal:1',
        'peso_kg' => 'decimal:2',
        'estatura_cm' => 'decimal:2',
        'imc' => 'decimal:2',
        'historial_ediciones' => 'array',
        'requiere_medico' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relación con el paciente de clínica de trabajo
     */
    public function pacienteClinica(): BelongsTo
    {
        return $this->belongsTo(PacientesClinicaTrabajo::class, 'paciente_clinica_id');
    }

    /**
     * Relación con la valoración ocupacional generada a partir de este triage
     */
    public function valoracionOcupacional(): BelongsTo
    {
        return $this->belongsTo(ValoracionOcupacional::class, 'valoracion_id');
    }

    /**
     * Relación con el usuario que realizó el triage
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_triage_id');
    }
}