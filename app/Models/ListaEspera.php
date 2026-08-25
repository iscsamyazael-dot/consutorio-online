<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListaEspera extends Model
{
    protected $table = 'lista_espera';

    protected $fillable = [
        'folio',
        'numero_turno',
        'paciente_id',
        'medico_id',
        'especialidad_id',
        'cita_id',
        'fecha',
        'hora_llegada',
        'estado',
        'consultorio',
        'observaciones',
    ];

    // ──────────────────────────────────────────
    // NOTA: folio y numero_turno YA NO se generan aquí.
    // Se generan en ListaEsperaController, dentro de
    // DB::transaction() + lockForUpdate(), para evitar
    // condiciones de carrera (ver generarCodigoConReinicioAnual()
    // y generarTurnoConReinicioDiario() en el controlador).
    // ──────────────────────────────────────────

    // ──────────────────────────────────────────
    // Relaciones
    // ──────────────────────────────────────────

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }

    public function especialidad()
    {
        // Specialty.php es el modelo activo, apunta a la tabla
        // 'especialidades' (ver protected $table en Specialty.php).
        // Especialidad.php existe pero NO está operativo.
        return $this->belongsTo(Specialty::class, 'especialidad_id');
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    public function triage()
    {
        return $this->hasOne(Triage::class, 'lista_espera_id');
    }
}