<?php

namespace App\Models;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Receta;

class Paciente extends Model
{
    use HasFactory;

    protected $table = 'pacientes';

    protected $fillable = [
        'paciente_id',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'telefono',
        'email',
        'edad',
        'edad_unidad',
        'sexo',
        'direccion',
        'tipo_sangre',
        'contacto_emergencia',
        'telefono_emergencia',
        'curp',
        'estado',
        'foto',
        'notas_generales',
        'alergias',
        'alergia_medicamentos',
        'antecedentes_medicos',
        'antecedentes_quirurgicos',
        'enfermedades_cronicas',
        'medicamentos_actuales',
        'fecha_nacimiento',
        'whatsapp_id',
        'consentimiento_datos',
        'ultima_interaccion'
    ];

    protected $appends = ['edad_formateada'];


    /**
     * Un paciente puede tener muchas consultas
     */
    public function consultas()
    {
        return $this->hasMany(Consulta::class, 'paciente_id');
    }

    public function expediente()
    {
        return $this->hasOne(ExpedienteClinico::class);
    }
    
    public function citas()
    {
        return $this->hasMany(Cita::class);
    }
    
     public function archivos()
    {
        return $this->hasMany(ArchivoClinico::class);
    }

    /**
     * Un paciente puede tener muchos triages*/
    
    // Relación con Triage (Un paciente tiene muchos triages)
    public function triages()
    {
        return $this->hasMany(Triage::class, 'paciente_id', 'id');
    }
    
    /**
    * Obtener únicamente el último triaje asignado al paciente
    */
    public function ultimoTriage()
    {
        return $this->hasOne(Triage::class, 'paciente_id', 'id')->latest();
    }

    // Relación con Alertas (Un paciente tiene muchas alertas)
    public function alertas()
    {
        // Apunta a la tabla 'alertas_clinicas' usando el 'paciente_id'
        return $this->hasMany(AlertaClinica::class, 'paciente_id', 'id');
    }

    // Relación con Recomendaciones
    public function recomendaciones()
    {
        // Si tu tabla de recomendaciones está ligada al triage_id (consulta_id), usamos hasManyThrough
        return $this->hasManyThrough(
            RecomendacionIa::class,
            Triage::class,
            'paciente_id', // Llave foránea en tabla triage
            'consulta_id',  // Llave foránea en tabla recomendaciones_ia
            'id',          // Llave local en pacientes
            'id'           // Llave local en triage
        );
    }


    public function recetas()
    {
    return $this->hasManyThrough(
        Receta::class,      // modelo destino
        Consulta::class,    // modelo intermedio
        'paciente_id',      // FK en 'consultas' que apunta a 'pacientes'
        'consulta_id',      // FK en 'recetas' que apunta a 'consultas'
        'id',                // PK local en 'pacientes'
        'id'                 // PK local en 'consultas'
    );
    }
    
    /**
     * Devuelve la edad ya formateada con su unidad correcta, ej. "7 meses",
     * "2 años", "15 días" — singular/plural correcto incluido.
     * Uso: $paciente->edad_formateada
     */
    public function getEdadFormateadaAttribute(): ?string
    {
        if (is_null($this->edad)) {
            return null;
        }

        $unidades = [
            'dias'  => $this->edad == 1 ? 'día'  : 'días',
            'meses' => $this->edad == 1 ? 'mes'  : 'meses',
            'anios' => $this->edad == 1 ? 'año'  : 'años',
        ];

        $etiqueta = $unidades[$this->edad_unidad] ?? 'años';

        return $this->edad . ' ' . $etiqueta;
    }

    
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($paciente) {
            if (empty($paciente->qr_token)) {
                $paciente->qr_token = (string) Str::uuid();
            }
        });
    }
}
