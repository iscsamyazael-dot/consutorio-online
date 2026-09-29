<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Paciente;
use App\Models\Medico;
use App\Models\EmpresaCliente;
use App\Models\PuestoTrabajo;

class ValoracionNutricion extends Model
{
    use HasFactory;

    protected $table = 'valoraciones_nutricion';

    protected $guarded = [];

    protected $casts = [
        'fecha_valoracion' => 'date',
        'plazo_proximo_seguimiento' => 'date',
        'tipo_evaluacion' => 'string',
        'aptitud' => 'string',

        'tension_emocional' => 'string',
        'alta_responsabilidad' => 'string',
        'carga_excesiva_trabajo' => 'string',
        'turno_rotativo' => 'string',
        'turno_nocturno' => 'string',
        'trabajo_repetitivo' => 'string',
        'actividad_rapida_variable' => 'string',
        'actividad_monotona_lenta' => 'string',
        'exp_temperatura_elevada_baja' => 'string',

        'tabaquismo' => 'boolean',
        'alcoholismo' => 'boolean',
        'varia_consumo_estres' => 'boolean',
        'varia_consumo_tristeza' => 'boolean',
        'refiere_diarrea' => 'boolean',
        'refiere_gastritis' => 'boolean',
        'refiere_ulcera' => 'boolean',
        'refiere_nauseas' => 'boolean',
        'refiere_reflujo' => 'boolean',
        'refiere_vomitos' => 'boolean',
        'refiere_colitis' => 'boolean',
        'seguimiento_requerido' => 'boolean',

        'estatura_m' => 'decimal:2',
        'peso_kg' => 'decimal:2',
        'peso_ideal_kg' => 'decimal:2',
        'imc' => 'decimal:2',
        'circunferencia_cintura_cm' => 'decimal:2',
        'circunferencia_cadera_cm' => 'decimal:2',
        'icc' => 'decimal:2',
        'circunferencia_brazo_cm' => 'decimal:2',
        'grasa_corporal_pct' => 'decimal:2',
        'grasa_visceral_pct' => 'decimal:2',
        'musculo_pct' => 'decimal:2',

        'glucosa_mg_dl' => 'decimal:2',
        'trigliceridos_mg_dl' => 'decimal:2',
        'acido_urico_mg_dl' => 'decimal:2',
        'hemoglobina_g_dl' => 'decimal:2',
        'colesterol_total_mg_dl' => 'decimal:2',
        'hdl_mg_dl' => 'decimal:2',
        'ldl_mg_dl' => 'decimal:2',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(EmpresaCliente::class, 'empresa_cliente_id');
    }

    public function puesto(): BelongsTo
    {
        return $this->belongsTo(PuestoTrabajo::class, 'puesto_trabajo_id');
    }

    public function nutriologo(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'nutriologo_id');
    }

    public function alimentos(): BelongsToMany
    {
        return $this->belongsToMany(
            CatalogoAlimento::class,
            'valoracion_alimentos',
            'valoracion_nutricion_id',
            'alimento_id'
        )->withPivot('frecuencia_texto')
         ->withTimestamps();
    }

    public function scopePorEmpresa(Builder $query, $empresaId)
    {
        return $query->where('empresa_cliente_id', $empresaId);
    }

    public function scopeConObesidad(Builder $query)
    {
        return $query->where('imc_clasificacion', 'like', '%Obesidad%');
    }

    public function scopeConDislipidemia(Builder $query)
    {
        return $query->where('colesterol_total_mg_dl', '>', 200)
                     ->orWhere('trigliceridos_mg_dl', '>', 150);
    }
}