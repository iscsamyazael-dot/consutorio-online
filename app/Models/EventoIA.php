<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventoIA extends Model
{
    protected $table = 'eventos_ia';

    public $timestamps = false; // la tabla solo tiene created_at, no updated_at

    protected $fillable = [
        'consulta_id',
        'tipo_evento',
        'descripcion',
        'resultado',
    ];

    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }
}