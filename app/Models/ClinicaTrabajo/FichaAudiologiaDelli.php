<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FichaAudiologiaDelli extends Model
{
    use HasFactory;

    protected $table = 'fichas_audiologia_delli';

    protected $guarded = [];

    // datos_json no se castea: los controladores lo leen con json_decode
    protected $casts = [
        'ototoxicos_quimicos' => 'array',
        'antecedentes_medicos' => 'array',
        'sintomas_auditivos' => 'array',
        'audiometria' => 'array',
        'pta_promedio' => 'decimal:2',
        'anos_empresa' => 'integer',
        'meses_empresa' => 'integer',
        'anos_puesto' => 'integer',
        'meses_puesto' => 'integer',
    ];

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
}