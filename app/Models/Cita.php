<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $fillable = [
        'folio',
        'paciente_id',
        'especialidad_id',
        'medico_id',
        'fecha',
        'hora',
        'tipo',
        'estado',
        'observaciones',
    ];

    protected static function booted()
    {
        static::creating(function ($cita) {
            if (empty($cita->folio)) {
                $year = date('Y');

                $ultimo = self::where('folio', 'like', "CIT-{$year}-%")
                    ->orderByRaw('CAST(SUBSTRING_INDEX(folio, "-", -1) AS UNSIGNED) DESC')
                    ->lockForUpdate()
                    ->first();

                $consecutivo = $ultimo
                    ? ((int) substr($ultimo->folio, -4)) + 1
                    : 1;

                $cita->folio = "CIT-{$year}-" . str_pad($consecutivo, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }
}