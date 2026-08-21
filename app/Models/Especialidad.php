<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    protected $table = 'especialidades';

    protected $fillable = [
    'folio',
    'nombre',
    'descripcion',
    'estado',
];
    public function medicos()
    {
        return $this->hasMany(Medico::class);
    }

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }
}