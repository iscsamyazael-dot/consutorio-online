<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Paciente;
use App\Models\Medico;

class AccidenteTrabajo extends Model
{
    use HasFactory;

    protected $table = 'accidentes_trabajo';

    protected $fillable = [
        'folio',
        'paciente_id',
        'empresa_cliente_id',
        'puesto_trabajo_id',
        'puesto_nombre_snapshot',
        'medico_id',
        'fecha_accidente',
        'lugar',
        'mecanismo_lesion',
        'parte_cuerpo_afectada',
        'tipo_lesion',
        'gravedad',
        'descripcion_hechos',
        'amerita_incapacidad',
        'dias_incapacidad',
        'fecha_alta',
        'notificado_imss',
        'numero_aviso_st7',
        'secuela',
        'descripcion_secuela',
        'acciones_correctivas',
        'firmado_medico_en',
    ];

    protected $casts = [
        'fecha_accidente' => 'datetime',
        'amerita_incapacidad' => 'boolean',
        'fecha_alta' => 'date',
        'notificado_imss' => 'boolean',
        'secuela' => 'boolean',
        'firmado_medico_en' => 'datetime',
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

    /**
     * true si ya fue firmado por el médico (dictamen cerrado).
     */
    public function getEstaFirmadoAttribute(): bool
    {
        return ! is_null($this->firmado_medico_en);
    }
}