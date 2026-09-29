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

class ValoracionPsicologica extends Model
{
    use HasFactory;

    protected $table = 'valoraciones_psicologicas';

    protected $guarded = [];

    protected $casts = [
        'fecha_valoracion' => 'date',
        'fecha_evento_traumatico' => 'date',
        'plazo_proximo_seguimiento' => 'date',
        'tipo_evaluacion' => 'string',
        'aptitud' => 'string',
        'ha_presenciado_evento_traumatico' => 'boolean',
        'requiere_canalizacion_imss' => 'boolean',
        'guia_ref_i_aplicada' => 'boolean',
        'guia_ref_iii_aplicada' => 'boolean',
        'relacionado_con_trabajo' => 'boolean',
        'requiere_seguimiento' => 'boolean',
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

    public function psicologo(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'psicologo_id');
    }

    public function factoresPsicosociales(): BelongsToMany
    {
        return $this->belongsToMany(
            CatalogoFactorPsicosocial::class,
            'valoracion_factores_psicosociales',
            'valoracion_id',
            'factor_id'
        )->withPivot('presente', 'nivel_severidad', 'observaciones')
         ->withTimestamps();
    }

    public function scopePorEmpresa(Builder $query, $empresaId)
    {
        return $query->where('empresa_cliente_id', $empresaId);
    }

    public function scopePorTipo(Builder $query, $tipo)
    {
        return $query->where('tipo_evaluacion', $tipo);
    }

    public function scopeEnFecha(Builder $query, $desde, $hasta)
    {
        return $query->whereBetween('fecha_valoracion', [$desde, $hasta]);
    }

    public function scopeRequiereCanalizacion(Builder $query)
    {
        return $query->where('requiere_canalizacion_imss', true);
    }
}