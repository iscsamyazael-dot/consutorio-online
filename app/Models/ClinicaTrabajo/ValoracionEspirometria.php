<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ClinicaTrabajo\PacientesClinicaTrabajo;
use App\Models\Medico;
use App\Models\ClinicaTrabajo\EmpresaCliente;
use App\Models\ClinicaTrabajo\PuestoTrabajo;

class ValoracionEspirometria extends Model
{
    use HasFactory;

    protected $table = 'valoraciones_espirometria';

    protected $guarded = [];

    protected $casts = [
        // Fechas
        'fecha_valoracion' => 'date',
        'fecha_nacimiento' => 'date',
        'plazo_proximo_seguimiento' => 'date',

        // Enums y textos
        'tipo_evaluacion' => 'string',
        'aptitud' => 'string',
        'sexo' => 'string',
        'origen_etnico' => 'string',
        'referencia' => 'string',
        'interpretacion_predicha' => 'string',
        'calidad_sesion' => 'string',
        'calidad_fev1' => 'string',
        'calidad_fvc' => 'string',
        'interpretacion_sistema' => 'string',
        'remitido_por' => 'string',
        'prescrito_por' => 'string',
        'interpretacion_equipo' => 'string',

        // Datos generales
        'altura_cm' => 'decimal:2',
        'peso_kg' => 'decimal:2',
        'peso_ideal_kg' => 'decimal:2',
        'imc' => 'decimal:2',
        'superficie_corporal' => 'decimal:2',
        'cigarrillos_dia' => 'int',
        'anos_fumador' => 'int',
        'pack_years' => 'decimal:2',

        // Booleanos
        'fumador' => 'boolean',
        'asma' => 'boolean',
        'epoc' => 'boolean',
        'antecedente_covid' => 'boolean',
        'rinitis_alergica' => 'boolean',
        'tuberculosis' => 'boolean',
        'neumonia_previa' => 'boolean',
        'seguimiento_requerido' => 'boolean',

        // Parámetros FVL (Mejor de cada parámetro)
        'fvc_l' => 'decimal:2',
        'fev1_l' => 'decimal:2',
        'fev1_fvc_ratio' => 'decimal:2', // porcentaje, nunca fracción
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

        // Predicho y LLN por parámetro
        'fvc_pred' => 'decimal:2',
        'fvc_lln' => 'decimal:2',
        'fev1_pred' => 'decimal:2',
        'fev1_lln' => 'decimal:2',
        'fev1_fvc_pred' => 'decimal:2',
        'fev1_fvc_lln' => 'decimal:2',
        'fef25_75_pred' => 'decimal:2',
        'fef25_75_lln' => 'decimal:2',
        'pef_pred' => 'decimal:2',
        'pef_lln' => 'decimal:2',
        'fivc_pred' => 'decimal:2',
        'fivc_lln' => 'decimal:2',
        'pif_pred' => 'decimal:2',
        'pif_lln' => 'decimal:2',
        'eotv_pred' => 'decimal:2',
        'eotv_lln' => 'decimal:2',
        'bev_pred' => 'decimal:2',
        'bev_lln' => 'decimal:2',

        // Puntuación Z (solo FVC, FEV1, FEV1/FVC, FEF25-75, PEF y FIVC)
        'fvc_z' => 'decimal:2',
        'fev1_z' => 'decimal:2',
        'fev1_fvc_z' => 'decimal:2',
        'fef25_75_z' => 'decimal:2',
        'pef_z' => 'decimal:2',
        'fivc_z' => 'decimal:2',

        // Porcentajes del predicho
        'fvc_porcentaje_predicho' => 'int',
        'fev1_porcentaje_predicho' => 'int',
        'fef25_75_porcentaje_predicho' => 'int',
        'pef_porcentaje_predicho' => 'int',
        'fev1_fvc_porcentaje_predicho' => 'int',
        'fivc_porcentaje_predicho' => 'int',
        'pif_porcentaje_predicho' => 'int',

        // Interpretación
        'edad_pulmonar' => 'int',

        // Maniobras (columna JSON)
        'maniobras' => 'array',
    ];

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    public function paciente(): BelongsTo
    {
        // FK a pacientes_clinica_trabajo. Usa SoftDeletes: si el paciente
        // fue dado de baja, la relación devuelve null salvo withTrashed().
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

    public function medico(): BelongsTo
    {
        // medico_id apunta a medicos.id (no a users.id)
        return $this->belongsTo(Medico::class, 'medico_id');
    }

    public function mensajesIa(): HasMany
    {
        return $this->hasMany(EspirometriaIaMensaje::class, 'valoracion_id');
    }
}