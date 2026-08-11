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
}
