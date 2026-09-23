<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialPuesto extends Model
{
    use HasFactory;

    protected $table = 'historial_puestos';

    protected $fillable = [
        'paciente_id',
        'puesto_trabajo_id',
        'fecha_inicio',
        'fecha_fin',
        'motivo_cambio',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function puestoTrabajo(): BelongsTo
    {
        return $this->belongsTo(PuestoTrabajo::class, 'puesto_trabajo_id');
    }

    /**
     * true cuando fecha_fin es NULL (puesto actual del trabajador).
     */
    public function getEsPuestoActualAttribute(): bool
    {
        return is_null($this->fecha_fin);
    }
}