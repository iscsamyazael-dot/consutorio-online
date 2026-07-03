<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
   
    use HasFactory;
    protected $table = 'ubicaciones';
    public $timestamps = false;
    protected $fillable = [
        'folio_sucursal',
        'nombre',
        'direccion',
        'horario_apertura',
        'horario_cierre',
        'activo',
    ];
    protected $casts = [
        'activo' => 'boolean',
    ];
}