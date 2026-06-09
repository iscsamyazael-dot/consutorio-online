<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    protected $table = 'pacientes';

    protected $fillable = [
        'paciente_codigo',
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
}