<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatosLaboral extends Model
{
    use HasFactory;

    protected $table = 'datos_laborales';

    protected $fillable = [
        'paciente_id',
        'empresa_cliente_id',
        'puesto_trabajo_id',
        'numero_empleado',
        'fecha_ingreso_empresa',
        'tipo_contrato',
        'turno',
    ];

    protected $casts = [
        'fecha_ingreso_empresa' => 'date',
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
}