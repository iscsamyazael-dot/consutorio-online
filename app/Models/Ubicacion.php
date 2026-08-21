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
        'empresa_id',
        'folio_sucursal',
        'nombre',
        'direccion',
        'horario_apertura',
        'horario_cierre',
        'activo',
        'imagen',
        'telefono'
    ];
    protected $casts = [
        'activo' => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}