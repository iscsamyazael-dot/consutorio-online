<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class CitaPrueba extends Model
{
    use HasFactory;
    protected $table = 'citas_pruebas';
    protected $fillable = [
        'nombre_paciente',
        'telefono',
        'fecha_cita',
        'hora_cita',
        'estado',
        'observaciones'
    ];
    protected $casts = [
        'fecha_cita' => 'date',
        'hora_cita' => 'datetime:H:i'
    ];
}