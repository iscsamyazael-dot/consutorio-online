<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    use HasFactory;

    protected $table = 'medicos';

    protected $fillable = [
        'folio',
        'nombre',
        'cedula_profesional',
        'especialidad_id',
        'activo',
    ];

    // ✅ Un médico pertenece a una especialidad
    public function especialidad()
    {
        return $this->belongsTo(Specialty::class, 'especialidad_id');
    }
}