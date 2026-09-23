<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PuestoTrabajo extends Model
{
    use HasFactory;

    protected $table = 'puestos_trabajo';

    protected $fillable = [
        'empresa_cliente_id',
        'nombre',
        'descripcion_actividades',
        'riesgo_ergonomico',
        'riesgo_psicosocial',
        'riesgo_quimico',
        'riesgo_ruido',
        'riesgo_alturas',
        'riesgo_espacios_confinados',
        'riesgo_manejo_cargas',
        'riesgo_vibraciones',
        'riesgo_electrico',
        'riesgo_biologico',
        'conduce_vehiculos',
        'manejo_alimentos',
        'requiere_epp',
        'riesgos_adicionales',
        'activo',
    ];

    protected $casts = [
        'riesgo_ergonomico' => 'boolean',
        'riesgo_psicosocial' => 'boolean',
        'riesgo_quimico' => 'boolean',
        'riesgo_ruido' => 'boolean',
        'riesgo_alturas' => 'boolean',
        'riesgo_espacios_confinados' => 'boolean',
        'riesgo_manejo_cargas' => 'boolean',
        'riesgo_vibraciones' => 'boolean',
        'riesgo_electrico' => 'boolean',
        'riesgo_biologico' => 'boolean',
        'conduce_vehiculos' => 'boolean',
        'manejo_alimentos' => 'boolean',
        'requiere_epp' => 'boolean',
        'riesgos_adicionales' => 'array',
        'activo' => 'boolean',
    ];

    /**
     * Mapeo columna de riesgo -> código(s) de norma sugerida, tomado de los
     * comentarios de columna del esquema (consultorio_online_template_v8.sql).
     * Se usa para sembrar puesto_norma_aplicable (origen = sugerida_automatica)
     * cuando se crea o edita un puesto. requiere_epp también se incluye porque
     * dispara NOM-017-STPS-2024 igual que un flag de riesgo.
     *
     * NOTA: riesgo_biologico puede mapear a NOM-004 o NOM-030 según el tipo de
     * exposición real del puesto; aquí se listan ambas como candidatas y el
     * médico confirma cuál aplica (confirmada_por_medico).
     */
    public const MAPA_RIESGO_NORMA = [
        'riesgo_ergonomico' => ['NOM-036-1-STPS-2018'],
        'riesgo_psicosocial' => ['NOM-035-STPS-2018'],
        'riesgo_quimico' => ['NOM-010-STPS-2014'],
        'riesgo_ruido' => ['NOM-011-STPS-2001'],
        'riesgo_alturas' => ['NOM-009-STPS-2011'],
        'riesgo_espacios_confinados' => ['NOM-033-STPS-2015'],
        'riesgo_manejo_cargas' => ['NOM-006-STPS-2014'],
        'riesgo_vibraciones' => ['NOM-024-STPS-2001'],
        'riesgo_electrico' => ['NOM-029-STPS-2011'],
        'riesgo_biologico' => ['NOM-004-STPS-1999', 'NOM-030-STPS-2009'],
        'requiere_epp' => ['NOM-017-STPS-2024'],
    ];

    public function empresaCliente(): BelongsTo
    {
        return $this->belongsTo(EmpresaCliente::class, 'empresa_cliente_id');
    }

    public function puestoNormaAplicable(): HasMany
    {
        return $this->hasMany(PuestoNormaAplicable::class, 'puesto_trabajo_id');
    }

    public function normasOficiales(): BelongsToMany
    {
        return $this->belongsToMany(NormaOficial::class, 'puesto_norma_aplicable', 'puesto_trabajo_id', 'norma_id')
            ->withPivot(['origen', 'confirmada_por_medico'])
            ->withTimestamps();
    }

    public function datosLaborales(): HasMany
    {
        return $this->hasMany(DatosLaboral::class, 'puesto_trabajo_id');
    }

    public function historialPuestos(): HasMany
    {
        return $this->hasMany(HistorialPuesto::class, 'puesto_trabajo_id');
    }

    public function accidentesTrabajo(): HasMany
    {
        return $this->hasMany(AccidenteTrabajo::class, 'puesto_trabajo_id');
    }

    public function valoracionesOcupacionales(): HasMany
    {
        return $this->hasMany(ValoracionOcupacional::class, 'puesto_trabajo_id');
    }

    /**
     * Códigos de norma sugeridos según los flags de riesgo activos en este puesto.
     * Útil para sembrar puesto_norma_aplicable al guardar el puesto.
     *
     * @return string[]
     */
    public function codigosNormaSugeridos(): array
    {
        $codigos = [];

        foreach (self::MAPA_RIESGO_NORMA as $columna => $codigosNorma) {
            if ($this->{$columna}) {
                $codigos = array_merge($codigos, $codigosNorma);
            }
        }

        return array_values(array_unique($codigos));
    }
}