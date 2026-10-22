<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PacientesClinicaTrabajo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pacientes_clinica_trabajo';

    protected $fillable = [
        'paciente_origen_id',
        'folio',
        'nombre',
        'fecha_nacimiento',
        'genero',
        'estado_civil',
        'escolaridad',
        'lugar_nacimiento',
        'curp',
        'cedula_profesional',
        'foto',
        'tipo_sangre',
        'rh',
        'telefono',
        'celular',
        'email',
        'direccion',
        'contacto_emergencia',
        'relacion_contacto_emergencia',
        'telefono_emergencia',
        'alergias',
        'alergia_medicamentos',
        'transfusion_sanguinea',
        'transfusion_detalle',
        'antecedentes_medicos',
        'antecedentes_quirurgicos',
        'enfermedades_cronicas',
        'medicamentos_actuales',
        'fecha_ultima_consulta_medica',
        'motivo_ultima_consulta_medica',
        'edad_inicio_vida_laboral',
        'primer_empleo_empresa',
        'primer_empleo_puesto',
        'consentimiento_datos',
        'whatsapp_id',
        'qr_token',
        'ultima_interaccion',
        'notas_generales',
    ];

    protected $dates = [
        'fecha_nacimiento',
        'fecha_ultima_consulta_medica',
        'ultima_interaccion',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_ultima_consulta_medica' => 'date',
        'ultima_interaccion' => 'datetime',
        'transfusion_sanguinea' => 'boolean',
        'consentimiento_datos' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    // ✅ 1. AGREGADO: Indica a Laravel que incluya este atributo virtual en el JSON
    protected $appends = [
        'edad_formateada'
    ];

    /**
     * Relación con el paciente original (si existe)
     */
    public function pacienteOrigen(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Paciente::class, 'paciente_origen_id');
    }

    /**
     * Relación con datos laborales
     */
    public function datosLaborales(): HasMany
    {
        return $this->hasMany(DatosLaboral::class, 'paciente_id');
    }

    /**
     * Relación con accidentes de trabajo
     */
    public function accidentesTrabajo(): HasMany
    {
        return $this->hasMany(AccidenteTrabajo::class, 'paciente_id');
    }

    /**
     * Relación con historial de puestos
     */
    public function historialPuestos(): HasMany
    {
        return $this->hasMany(HistorialPuesto::class, 'paciente_id');
    }

    /**
     * Relación con valoraciones ocupacionales
     */
    public function valoracionesOcupacionales(): HasMany
    {
        return $this->hasMany(ValoracionOcupacional::class, 'paciente_id');
    }

    /**
     * Boot the model - generate folio and qr_token automatically
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate folio if empty: PCT-AAAA-NNNN
            if (empty($model->folio)) {
                $year = now()->year;
                $count = static::withTrashed()->whereYear('created_at', $year)->count() + 1;
                $model->folio = 'PCT-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }

            // Generate qr_token if empty: UUID
            if (empty($model->qr_token)) {
                $model->qr_token = (string) Str::uuid();
            }
        });
    }

    /**
     * Get sex reference for spirometry calculations based on gender
     * Returns: 'Masculino', 'Femenino', or null for non-binary/other/unspecified
     */
    public function sexoReferencia()
    {
        return match($this->genero) {
            'Hombre' => 'Masculino',
            'Mujer' => 'Femenino',
            default => null,
        };
    }

    // ✅ 2. AGREGADO: El accesor que calcula la edad automáticamente
    /**
     * Obtiene la edad calculada a partir de la fecha de nacimiento.
     */
    public function getEdadFormateadaAttribute(): string
    {
        // Como 'fecha_nacimiento' ya está en $casts como 'date', 
        // Laravel ya lo trata como un objeto Carbon.
        if (!$this->fecha_nacimiento) {
            return 'N/A';
        }
        
        return $this->fecha_nacimiento->age . ' años';
    }
}