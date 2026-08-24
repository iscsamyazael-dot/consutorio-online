<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Dispositivo extends Model
{
    use HasApiTokens;

    protected $table = 'codigos_emparejamiento_dispositivo';

    protected $fillable = [
        'codigo',
        'nombre_dispositivo',
        'tipo',
        'expira_en',
        'usado_en',
    ];

    protected $casts = [
        'expira_en' => 'datetime',
        'usado_en'  => 'datetime',
    ];
}