<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivoClinico extends Model
{
    protected $table = 'archivos_clinicos';

    protected $fillable = [
        'paciente_id',
        'codigo_paciente',
        'consulta_id',
        'tipo_archivo',
        'archivo_url',
        'descripcion',
        'analisis_ia',
        'fecha_subida',
        'procesado_ia',
        'nivel_confianza',
        'tipo_estudio', 
        'Estado',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function consulta()
    {
        return $this->belongsTo(Consulta::class, 'consulta_id');
    }
}

