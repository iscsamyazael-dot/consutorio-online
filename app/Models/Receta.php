<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    protected $table = 'recetas';
    protected $fillable = [
        'consulta_id',
        'paciente_id',
        'user_id',
        'indicaciones_generales',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'observaciones'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    // CONSULTA
    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }

    // PACIENTE
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    // MÉDICO
    public function medico()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    // DETALLES
    public function detalles()
    {
        return $this->hasMany(RecetaDetalle::class);
    }
}
