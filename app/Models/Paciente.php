<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $table = 'pacientes';

    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'sexo',
        'telefono',
        'email',
        'direccion',
        'tipo_sangre',
        'contacto_emergencia',
        'telefono_emergencia',
        'curp',
        'estado',
        'foto',
        'notas_generales'
    ];

    public function consultas()
    {
        return $this->hasMany(Consulta::class);
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

    public function triages()
    {
        return $this->hasMany(Triage::class, 'paciente_id');
    }
}
