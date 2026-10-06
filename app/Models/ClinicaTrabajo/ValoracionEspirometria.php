<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Paciente;
use App\Models\Medico;
use App\Models\EmpresaCliente;
use App\Models\PuestoTrabajo;

class ValoracionEspirometria extends Model
{
    use HasFactory;

    protected $table = 'valoraciones_espirometria';

    protected $guarded = [];

    protected $casts = [
        'fecha_valoracion' => 'date',
        'fecha_nacimiento' => 'date',
        'tipo_evaluacion' => 'string',
        'aptitud' => 'string',

        // Datos Generales
        'altura_cm' => 'decimal:2',
        'peso_kg' => 'decimal:2',
        'peso_ideal_kg' => 'decimal:2',
        'imc' => 'decimal:2',
        'superficie_corporal' => 'decimal:2',
        'cigarrillos_dia' => 'int',
        'anos_fumador' => 'int',
        'pack_years' => 'decimal:2',

        // Parámetros FVL
        'referencia' => 'string',
        'interpretacion_predicha' => 'string',
        'fvc_l' => 'decimal:2',
        'fev1_l' => 'decimal:2',
        'fev1_fvc_ratio' => 'decimal:3',
        'fef25_75' => 'decimal:2',
        'pef_l_s' => 'decimal:2',
        'fet_s' => 'decimal:2',
        'fivc_l' => 'decimal:2',
        'pif_l_s' => 'decimal:2',
        'eotv_l' => 'decimal:2',
        'bev_l' => 'decimal:2',
        'fev1_var_l' => 'decimal:3',
        'fvc_var_l' => 'decimal:3',
        'pef_var_l_s' => 'decimal:2',
        'fef2575_var_l_s' => 'decimal:2',
        'num_maniobras' => 'int',

        // Porcentajes predichos
        'fvc_porcentaje_predicho' => 'int',
        'fev1_porcentaje_predicho' => 'int',
        'fef25_75_porcentaje_predicho' => 'int',
        'pef_porcentaje_predicho' => 'int',

        // Interpretación
        'edad_pulmonar' => 'int',
        'calidad_sesion' => 'string',
        'interpretacion_sistema' => 'string',

        // Booleanos
        'fumador' => 'boolean',
        'asma' => 'boolean',
        'epoc' => 'boolean',
        'antecedente_covid' => 'boolean',
        'rinitis_alergica' => 'boolean',
        'tuberculosis' => 'boolean',
        'neumonia_previa' => 'boolean',
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

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }
}