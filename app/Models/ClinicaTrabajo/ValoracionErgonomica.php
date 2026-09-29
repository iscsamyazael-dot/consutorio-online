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

class ValoracionErgonomica extends Model
{
    use HasFactory;

    protected $table = 'valoraciones_ergonomicas';

    protected $guarded = [];

    protected $casts = [
        'fecha_valoracion' => 'date',
        'fecha_evaluacion_puesto' => 'date',
        'plazo_proximo_seguimiento' => 'date',
        'tipo_evaluacion' => 'string',
        'aptitud' => 'string',
        'instrumento_utilizado' => 'string',

        'rula_puntuacion' => 'int',
        'rula_nivel_riesgo' => 'string',

        'reba_puntuacion' => 'int',
        'reba_nivel_accion' => 'string',

        'niosh_indice_levantamiento' => 'decimal:2',
        'niosh_es_seguro' => 'boolean',
        'niosh_peso_real_kg' => 'decimal:2',
        'niosh_peso_recomendado_kg' => 'decimal:2',

        'posturas_forzadas' => 'boolean',
        'movimientos_repetitivos' => 'boolean',
        'manipulacion_cargas' => 'boolean',
        'carga_maxima_manipulada_kg' => 'decimal:2',

        'examen_medico_realizado' => 'boolean',
        'seguimiento_requerido' => 'boolean',
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

    public function ergonomo(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'ergonomo_id');
    }

    public function riesgosErgonomicos(): BelongsToMany
    {
        return $this->belongsToMany(
            CatalogoRiesgoErgonomico::class,
            'valoracion_riesgos_ergonomicos',
            'valoracion_id',
            'riesgo_id'
        )->withPivot('presente', 'medida_control')
         ->withTimestamps();
    }

    public function scopeConRiesgoAlto(Builder $query)
    {
        return $query->where(function($q) {
            $q->where('rula_nivel_riesgo', 'alto')
              ->orWhere('reba_nivel_accion', 'cambios_inmediatos')
              ->orWhere('niosh_es_seguro', false);
        });
    }

    public function scopePorInstrumento(Builder $query, $instrumento)
    {
        return $query->where('instrumento_utilizado', $instrumento);
    }
}