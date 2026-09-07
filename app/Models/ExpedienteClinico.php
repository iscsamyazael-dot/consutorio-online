<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ExpedienteClinico extends Model
{
    protected $table = 'expediente_clinico';

    protected $fillable = [
        'paciente_id',
        'tipo_sangre',
        'alergias',
        'enfermedades_cronicas',
        'antecedentes_medicos',
        'antecedentes_heredofamiliares',
        'antecedentes_no_patologicos',
        'padecimiento_actual',
        'interrogatorio_aparatos_sistemas',
        'exploracion_fisica',
        'plan_tratamiento_inicial',
        'completado_ia',
        'revisado_medico',
        'firmado_por',
        'firmado_en',
        'notas_generales',
        'resumen_ia',
        'riesgos_detectados',
        'ultima_evaluacion_ia',
    ];

    protected $casts = [
        'completado_ia'        => 'boolean',
        'revisado_medico'      => 'boolean',
        'firmado_en'           => 'datetime',
        'ultima_evaluacion_ia' => 'datetime',
    ];

    // Los 7 apartados del interrogatorio + exploración física + plan que
    // exige la NOM-004-SSA3-2012 (numeral 6.1) y que se autollenan por IA.
    public const CAMPOS_CLINICOS = [
        'antecedentes_heredofamiliares',
        'antecedentes_medicos', // antecedentes personales PATOLÓGICOS
        'antecedentes_no_patologicos',
        'padecimiento_actual',
        'interrogatorio_aparatos_sistemas',
        'exploracion_fisica',
        'plan_tratamiento_inicial',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }
}