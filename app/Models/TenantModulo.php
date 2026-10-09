<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantModulo extends Model
{
    protected $connection = 'central';
    protected $table = 'tenant_modulos';
    protected $fillable = [
        'tenant_id',
        'modulo_id',
        'activo',
        'activado_por',
        'fecha_activacion'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_activacion' => 'datetime',
    ];
}