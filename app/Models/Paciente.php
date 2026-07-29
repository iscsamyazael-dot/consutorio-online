<?php

namespace App\Models;

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
     * Un paciente puede tener muchos triages
     */
    public function triages()
    {
        return $this->hasMany(Triage::class, 'paciente_id');
    }

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
}
