<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'medicamento_id',
        'tipo_movimiento',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
        'lote',
        'fecha_caducidad',
        'costo_unitario',
        'proveedor',
        'motivo_movimiento',
        'referencia_documento',
        'observaciones',
        'usuario_id',
        'fecha_movimiento'
    ];

    //RELACIONES//
    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }
}
