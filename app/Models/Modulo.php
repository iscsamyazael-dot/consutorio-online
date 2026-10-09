<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    protected $connection = 'central';
    protected $table = 'modulos_sistema';
    protected $fillable = ['clave', 'nombre', 'descripcion'];
}