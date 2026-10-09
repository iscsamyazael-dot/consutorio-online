<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $connection = 'central';

    protected $table = 'tenants';

    protected $fillable = [
        'folio',
        'nombre_consultorio',
        'db_name',
        'dominio_correo',
        'estatus',
    ];

    public function modulos()
    {
        return $this->belongsToMany(Modulo::class, 'tenant_modulos', 'tenant_id', 'modulo_id')
                    ->withPivot('activo', 'activado_por', 'fecha_activacion')
                    ->wherePivot('activo', 1) 
                    ->withTimestamps();
    }
}
