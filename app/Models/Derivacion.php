<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Derivacion extends Model
{
    protected $table = 'derivaciones';

    protected $fillable = [
        'consulta_id',
        'especialidad_id',
        'hospital',
        'motivo',
        'prioridad',
        'estado',
    ];
}
