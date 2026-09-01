<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionImpresora extends Model
{
    use HasFactory;

    protected $table = 'configuracion_impresora';

    protected $fillable = [
        'nombre',
        'ip',
        'puerto',
        'ancho_papel_mm',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'puerto' => 'integer',
        'ancho_papel_mm' => 'integer',
    ];
}