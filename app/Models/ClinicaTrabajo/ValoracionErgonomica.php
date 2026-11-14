<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Medico;

class ValoracionErgonomica extends Model
{
    use HasFactory;

    protected $table = 'valoraciones_ergonomicas';

    protected $guarded = [];

    protected $casts = [
        // Fechas
        'fecha_valoracion' => 'date',
        'fecha_evaluacion_puesto' => 'date',
        'plazo_proximo_seguimiento' => 'date',

        // Enums y textos
        'tipo_evaluacion' => 'string',
        'instrumento_utilizado' => 'string',
        'aptitud' => 'string',
        'rula_nivel_riesgo' => 'string',
        'reba_nivel_accion' => 'string',

        // Puntuaciones
        'rula_puntuacion' => 'integer',
        'reba_puntuacion' => 'integer',
        'niosh_indice_levantamiento' => 'decimal:2',
        'niosh_peso_real_kg' => 'decimal:2',
        'niosh_peso_recomendado_kg' => 'decimal:2',
        'carga_maxima_manipulada_kg' => 'decimal:2',

        // Booleanos
        'niosh_es_seguro' => 'boolean',
        'posturas_forzadas' => 'boolean',
        'movimientos_repetitivos' => 'boolean',
        'manipulacion_cargas' => 'boolean',
        'examen_medico_realizado' => 'boolean',
        'seguimiento_requerido' => 'boolean',

        'user_id' => 'integer',
    ];

    // datos_json no se castea: los controladores lo leen con json_decode (igual que DELLI)

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(PacientesClinicaTrabajo::class, 'paciente_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(EmpresaCliente::class, 'empresa_cliente_id');
    }

    public function puesto(): BelongsTo
    {
        return $this->belongsTo(PuestoTrabajo::class, 'puesto_trabajo_id');
    }

    public function medico(): BelongsTo
    {
        // medico_id apunta a medicos.id (el ergónomo que firma)
        return $this->belongsTo(Medico::class, 'medico_id');
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    public function scopeConRiesgoAlto(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('rula_nivel_riesgo', 'alto')
              ->orWhere('reba_nivel_accion', 'cambios_inmediatos')
              ->orWhere('niosh_es_seguro', false);
        });
    }

    public function scopePorInstrumento(Builder $query, string $instrumento): Builder
    {
        return $query->where('instrumento_utilizado', $instrumento);
    }

    public function scopePorAptitud(Builder $query, string $aptitud): Builder
    {
        return $query->where('aptitud', $aptitud);
    }
}