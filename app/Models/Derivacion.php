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
        'estado',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Specialty::class, 'especialidad_id');
    }
}