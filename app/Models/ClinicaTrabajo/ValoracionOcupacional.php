<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ValoracionOcupacional extends Model
{
    use HasFactory;

    protected $table = 'valoraciones_ocupacionales';

    protected $fillable = [
        'folio',
        'paciente_id',
        'empresa_cliente_id',
        'puesto_trabajo_id',
        'puesto_nombre_snapshot',
        'medico_id',
        'tipo',
        'fecha_valoracion',
        'ta_sistolica',
        'ta_diastolica',
        'fc',
        'fr',
        'spo2',
        'estatura_cm',
        'peso_kg',
        'talla_abdomen_cm',
        'imc',
        'imc_clasificacion',
        'grasa_visceral',
        'grasa_corporal_pct',
        'musculo_pct',
        'edad_biologica',
        'antecedentes_heredofamiliares',
        'antecedentes_patologicos_activos',
        'estilo_vida_habitos',
        'exploracion_fisica_funcional',
        'estudios_paraclinicos_resumen',
        'analisis_correlacion_riesgos',
        'dictamen',
        'restricciones',
        'dictamen_justificacion',
        'plan_intervencion',
        'vigencia_hasta',
        'firmado_medico_en',
        'firmado_trabajador_en',
    ];

    protected $casts = [
        'fecha_valoracion' => 'date',
        'spo2' => 'decimal:2',
        'estatura_cm' => 'decimal:2',
        'peso_kg' => 'decimal:2',
        'talla_abdomen_cm' => 'decimal:2',
        'imc' => 'decimal:2',
        'grasa_visceral' => 'decimal:2',
        'grasa_corporal_pct' => 'decimal:2',
        'musculo_pct' => 'decimal:2',
        'vigencia_hasta' => 'date',
        'firmado_medico_en' => 'datetime',
        'firmado_trabajador_en' => 'datetime',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function empresaCliente(): BelongsTo
    {
        return $this->belongsTo(EmpresaCliente::class, 'empresa_cliente_id');
    }

    public function puestoTrabajo(): BelongsTo
    {
        return $this->belongsTo(PuestoTrabajo::class, 'puesto_trabajo_id');
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }

    public function estudiosParaclinicos(): HasMany
    {
        return $this->hasMany(ValoracionEstudioParaclinico::class, 'valoracion_id');
    }

    public function normasAplicadas(): HasMany
    {
        return $this->hasMany(ValoracionNormaAplicada::class, 'valoracion_id');
    }

    public function normasOficiales(): BelongsToMany
    {
        return $this->belongsToMany(NormaOficial::class, 'valoracion_norma_aplicada', 'valoracion_id', 'norma_id')
            ->withPivot(['aplicacion_especifica']);
    }

    public function seguimientos(): HasMany
    {
        return $this->hasMany(ValoracionSeguimiento::class, 'valoracion_id');
    }

    /**
     * true si ya fue firmada por el médico (dictamen cerrado).
     */
    public function getEstaFirmadaAttribute(): bool
    {
        return ! is_null($this->firmado_medico_en);
    }
}