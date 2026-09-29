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

class ValoracionAudiologia extends Model
{
    use HasFactory;

    protected $table = 'valoraciones_audiologia';

    protected $guarded = [];

    protected $casts = [
        'fecha_valoracion' => 'date',
        'plazo_proximo_seguimiento' => 'date',
        'tipo_evaluacion' => 'string',
        'aptitud' => 'string',

        'disolventes_exposicion' => 'boolean',
        'metales_exposicion' => 'boolean',
        'gases_exposicion' => 'boolean',
        'sales_exposicion' => 'boolean',
        'tabaco_exposicion' => 'boolean',

        'antibioticos_consume' => 'boolean',
        'diureticos_consume' => 'boolean',
        'salicilatos_consume' => 'boolean',
        'antimalarcicos_consume' => 'boolean',

        'armas_fuego_caza' => 'boolean',
        'niveles_altos_musica' => 'boolean',
        'lugares_ruidosos_discoteca' => 'boolean',
        'automovilismo_motociclismo' => 'boolean',
        'otros_pasatiempos_ruidosos' => 'boolean',

        'historia_familiar_perdida_auditiva' => 'boolean',
        'sarampion' => 'boolean',
        'rubeola' => 'boolean',
        'paperas' => 'boolean',
        'meningitis' => 'boolean',
        'otitis_externa_media' => 'boolean',
        'covid19_antecedente' => 'boolean',
        'resfriad_reciente' => 'boolean',
        'alergias_auditivas' => 'boolean',
        'uso_aparato_auditivo' => 'boolean',

        'niega_sintomatologia_auditiva' => 'boolean',
        'otalgia_der' => 'boolean',
        'otalgia_izq' => 'boolean',
        'otorrea_der' => 'boolean',
        'otorrea_izq' => 'boolean',
        'prurito_der' => 'boolean',
        'prurito_izq' => 'boolean',
        'acufenos_der' => 'boolean',
        'acufenos_izq' => 'boolean',
        'vertigo_presente' => 'boolean',
        'otoscopia_normal' => 'boolean',
        'otoscopia_patologica' => 'boolean',

        'au_125_hz_der' => 'int',
        'au_250_hz_der' => 'int',
        'au_500_hz_der' => 'int',
        'au_1000_hz_der' => 'int',
        'au_2000_hz_der' => 'int',
        'au_3000_hz_der' => 'int',
        'au_4000_hz_der' => 'int',
        'au_6000_hz_der' => 'int',
        'au_8000_hz_der' => 'int',

        'au_125_hz_izq' => 'int',
        'au_250_hz_izq' => 'int',
        'au_500_hz_izq' => 'int',
        'au_1000_hz_izq' => 'int',
        'au_2000_hz_izq' => 'int',
        'au_3000_hz_izq' => 'int',
        'au_4000_hz_izq' => 'int',
        'au_6000_hz_izq' => 'int',
        'au_8000_hz_izq' => 'int',

        'tipo_hipoacusia' => 'string',
        'pta_promedio_tonal_puro_der' => 'int',
        'pta_promedio_tonal_puro_izq' => 'int',
        'requiere_seguimiento_audiometrico' => 'boolean',

        'uso_protector_auditivo' => 'string',
        'recomendacion_seguimiento' => 'string',
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

    public function audiologo(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'audiologo_id');
    }

    public function scopeAudiometriaAnormal(Builder $query)
    {
        return $query->whereNotNull('au_500_hz_der')
                     ->where('tipo_hipoacusia', '!=', 'normal');
    }

    public function scopeExposicionRuidoAlto(Builder $query)
    {
        return $query->where('exposicion_ruido_actual', '>=', '85');
    }
}