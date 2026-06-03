<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicamento extends Model
{
     protected $table = 'medicamentos';

     protected $fillable = [
        'codigo',
        'nombre',
        'nombre_generico',
        'presentacion',
        'concentracion',
        'via_administracion',
        'descripcion',
        'indicaciones',
        'contraindicaciones',
        'efectos_secundarios',
        'precio',
        'requiere_receta',
        'activo'
        ];

     /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    // RECETAS
    public function recetaDetalles()
    {
        return $this->hasMany(RecetaDetalle::class);
    }

    // INVENTARIO
    public function inventario()
    {
        return $this->hasOne(Inventario::class,'medicamento_id','id');
    }

    // MOVIMIENTOS
    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class, 'medicamento_id','id');
    }
    //ULTOMO MOVIMIENTO
    public function ultimoMovimiento()
    {
        return $this->hasOne(MovimientoInventario::class,'medicamento_id','id')->latestOfMany();
    }
}
