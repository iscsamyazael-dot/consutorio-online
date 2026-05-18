<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicamento extends Model
{
     protected $table = 'medicamentos';

    protected $fillable = [
        'nombre',
        'nombre_generico',
        'presentacion',
        'concentracion',
        'via_administracion',
        'descripcion',
        'indicaciones',
        'contraindicaciones',
        'efectos_secundarios',
        'estado'

    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    // DETALLES DE RECETA
    public function recetaDetalles()
    {
        return $this->hasMany(RecetaDetalle::class);
    }

}
