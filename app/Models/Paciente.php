<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'antecedentes_medicos',
        'antecedentes_quirurgicos',
        'enfermedades_cronicas',
        'medicamentos_actuales',
        'fecha_nacimiento',
        'whatsapp_id',
        'consentimiento_datos',
        'ultima_interaccion'
    ];


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
    * Un paciente puede tener muchos triages
    */
    
    // Relación con Triage (Un paciente tiene muchos triages)
    public function triages()
    {
        return $this->hasMany(Triage::class, 'paciente_id', 'id');
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


}
