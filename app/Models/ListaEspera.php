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
    // Autogeneración de folio y numero_turno
    // ──────────────────────────────────────────
    // folio: consecutivo global por año (LE-2026-0001...), mismo
    // patrón que Cita::boot() en Cita.php.
    // numero_turno: se reinicia cada DÍA (no cada año), porque es
    // lo que ve el paciente en la pantalla de TV.
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($registro) {

            // --- FOLIO ---
            if (empty($registro->folio)) {
                $year = date('Y');
                $ultimo = self::where('folio', 'like', "LE-{$year}-%")
                    ->orderByDesc('id')
                    ->first();

                $consecutivo = 1;
                if ($ultimo && preg_match('/(\d+)$/', $ultimo->folio, $m)) {
                    $consecutivo = (int) $m[1] + 1;
                }

                $registro->folio = "LE-{$year}-" . str_pad($consecutivo, 4, '0', STR_PAD_LEFT);
            }

            // --- NUMERO_TURNO ---
            if (empty($registro->numero_turno)) {
                $fecha = $registro->fecha ?? date('Y-m-d');

                $ultimoTurno = self::where('fecha', $fecha)
                    ->orderByDesc('numero_turno')
                    ->value('numero_turno');

                $registro->numero_turno = ($ultimoTurno ?? 0) + 1;
            }
        });
    }

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
}
