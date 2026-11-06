<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ClinicaTrabajo\PacientesClinicaTrabajo;
use App\Models\ClinicaTrabajo\PuestoTrabajo;
use App\Models\ClinicaTrabajo\EmpresaCliente;
use App\Models\Medico;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FichaAudiologiaDelli extends Model
{
    use HasFactory;

    protected $table = 'fichas_audiologia_delli';

    protected $guarded = [];

    protected $fillable = [
        'paciente_id',
        'empresa_cliente_id',
        'puesto_trabajo_id',
        'folio',
        'anos_empresa',
        'meses_empresa',
        'anos_puesto',
        'meses_puesto',
        'riesgo_auditivo',
        'epp_auditivo',
        'ototoxicos_quimicos',
        'antecedentes_medicos',
        'sintomas_auditivos',
        'audiometria',
        'pta_promedio',
        'clasificacion',
        'aptitud',
        'observaciones',
        'datos_json',
        'user_id',
    ];

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

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'user_id', 'user_id');
    }
}