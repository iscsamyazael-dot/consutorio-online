<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
   

    protected $table = 'medicos';

    // Campos exactos de tu base de datos
    protected $fillable = [
        'folio',
        'nombre', 
        'cedula_profesional', 
        'especialidad_id', 
        'activo'
    ];

    // Un médico pertenece a una especialidad

    
    public function especialidad()
    {
        return $this->belongsTo(Specialty::class, 'especialidad_id');
        return $this->belongsTo(Especialidad::class);
    }


    // Relación con Horarios
    public function horarios()
    {
        return $this->hasMany(HorarioMedico::class, 'medico_id');
    }

    // Relación con ConfiguracionMedicoSucursal (es la tabla intermedia)
    public function configuraciones()
    {
        return $this->hasMany(ConfiguracionMedicoSucursal::class, 'medico_id');
    }
    
    //Metodo para crear el folio o ID representativo

    protected static function booted()
    {
        static::creating(function ($medico) {
            if (empty($medico->folio)) {
                $year = date('Y');

                // Busca el último médico registrado este año con este formato
                $ultimo = self::where('folio', 'like', "MEDI-{$year}-%")
                    ->orderByRaw('CAST(SUBSTRING_INDEX(folio, "-", -1) AS UNSIGNED) DESC')
                    ->lockForUpdate()
                    ->first();

                // Extrae los últimos 3 dígitos o empieza en 1
                $consecutivo = $ultimo
                    ? ((int) substr($ultimo->folio, -3)) + 1
                    : 1;

                // Asigna el folio secuencial: MEDI-2026-001
                $medico->folio = "MEDI-{$year}-" . str_pad($consecutivo, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    // public function especialidad()
    // {
        
    // }
    
    public function citas()
    {
        return $this->hasMany(Cita::class);
    }
}