<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionMedicoSucursal extends Model
{
    protected $table = 'configuracion_medico_sucursal';
    
    // Desactiva los timestamps si tu tabla no tiene created_at y updated_at
    public $timestamps = false;

    protected $fillable = [
        'medico_id',
        'ubicacion_id',
        'costo_consulta',
    ];

    // Pertenece a un médico
    public function medico()
    {
        return $this->belongsTo(Medico::class);
    }

    // Pertenece a una ubicación
    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class);
    }
}