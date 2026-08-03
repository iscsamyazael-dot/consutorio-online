<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class inventario extends Model
{
    protected $table = 'inventario';

    protected $fillable = [
        'medicamento_id',
        'stock_actual',
        'stock_minimo',
        'ubicacion',
        'fecha_caducidad'
    ];
    //RELACIONES//
    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }
}