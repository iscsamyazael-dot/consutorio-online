<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ClinicaTrabajo\PacientesClinicaTrabajo;
use App\Models\ClinicaTrabajo\PuestoTrabajo;
use App\Models\ClinicaTrabajo\EmpresaCliente;
use App\Models\Medico;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FichaAudiologiaTR extends Model
{
    use HasFactory;

    protected $table = 'examenes_audiologia_tr';

    protected $guarded = [];

    protected $fillable = [
        'paciente_id',
        'empresa_cliente_id',
        'puesto_trabajo_id',
        'folio',
        'tipo_trabajo_riesgo',
        'riesgos',
        'antecedentes_salud',
        'peso',
        'estatura',
        'imc',
        'presion_arterial',
        'frecuencia_cardiaca',
        'exploracion_fisica',
        'ecg',
        'rx_torax',
        'evaluacion_visual',
        'audiometria',
        'espirometria',
        'diagnosticos',
        'enfermedad_profesional',
        'diagnostico_principal',
        'aptitud',
        'observaciones',
        'datos_json',
        'user_id',
        'anos_empresa',
        'meses_empresa',
        'anos_puesto',
        'meses_puesto',
    ];

    protected $casts = [
        'riesgos' => 'array',
        'antecedentes_salud' => 'array',
        'exploracion_fisica' => 'array',
        'evaluacion_visual' => 'array',
        'audiometria' => 'array',
        'espirometria' => 'array',
        'diagnosticos' => 'array',
        'peso' => 'decimal:2',
        'estatura' => 'decimal:2',
        'imc' => 'decimal:2',
        'anos_empresa' => 'integer',
        'meses_empresa' => 'integer',
        'anos_puesto' => 'integer',
        'meses_puesto' => 'integer',
        'enfermedad_profesional' => 'boolean',
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

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'user_id', 'user_id');
    }
}