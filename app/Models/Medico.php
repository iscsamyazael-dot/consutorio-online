<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
   

    protected $table = 'medicos';

    // Campos exactos de tu base de datos
    protected $fillable = [
        'folio',
        'nombre', 
        'cedula_profesional', 
        'especialidad_id', 
        'activo'
    ];

    // Un médico pertenece a una especialidad
    public function especialidad()
    {
        return $this->belongsTo(Specialty::class, 'especialidad_id');
        return $this->belongsTo(Especialidad::class);
    }


    public function horarios()
    {
        return $this->hasMany(HorarioMedico::class, 'medico_id');
    }


    // public function especialidad()
    // {
        
    // }
    
    public function citas()
    {
        return $this->hasMany(Cita::class);
    }
}