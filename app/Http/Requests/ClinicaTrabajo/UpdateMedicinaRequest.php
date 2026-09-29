<?php

namespace App\Http\Requests\ClinicaTrabajo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedicinaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'folio' => 'sometimes|required|string|unique:valoraciones_ocupacionales,folio,' . $id,
            'paciente_id' => 'sometimes|required|exists:pacientes,id',
            'empresa_cliente_id' => 'sometimes|required|exists:empresas_cliente,id',
            'puesto_trabajo_id' => 'nullable|exists:puestos_trabajo,id',
            'medico_id' => 'sometimes|required|exists:medicos,id',
            'tipo' => [
                'sometimes',
                Rule::in(['Ingreso', 'Periodica', 'Seguimiento', 'Extraordinaria', 'Reingreso'])
            ],
            'fecha_valoracion' => 'sometimes|required|date|before_or_equal:today',
            'hora_valoracion' => 'nullable|date_format:H:i',

            // Signos vitales
            'ta_sistolica' => 'nullable|integer|min:50|max:250',
            'ta_diastolica' => 'nullable|integer|min:30|max:150',
            'fc' => 'nullable|integer|min:30|max:250',
            'fr' => 'nullable|integer|min:5|max:40',
            'spo2' => 'nullable|numeric|min:70|max:100',

            // Antropometría
            'estatura_cm' => 'nullable|numeric|min:50|max:250',
            'peso_kg' => 'nullable|numeric|min:2|max:300',
            'imc' => 'nullable|numeric|min:10|max:80',
            'imc_clasificacion' => 'nullable|string',

            // Antecedentes
            'antecedentes_patologicos_activos' => 'nullable|string',
            'antecedentes_heredofamiliares' => 'nullable|string',
            'estilo_vida_habitos' => 'nullable|string',

            // Exploración física
            'exploracion_fisica_funcional' => 'nullable|string',
            'estudios_paraclinicos_resumen' => 'nullable|string',
            'analisis_correlacion_riesgos' => 'nullable|string',

            // Dictamen y restricciones
            'dictamen' => [
                'sometimes',
                Rule::in(['APTO', 'APTO_CON_RESTRICCIONES', 'NO_APTO', 'SUSPENDIDO'])
            ],
            'restricciones' => 'nullable|string',
            'dictamen_justificacion' => 'nullable|string',
            'plan_intervencion' => 'nullable|string',

            // Firma y vigencia
            'firmado_medico_en' => 'nullable|date',
            'firmado_trabajador_en' => 'nullable|date',
            'vigencia_hasta' => 'nullable|date|after:fecha_valoracion',
        ];
    }

    public function messages(): array
    {
        return [
            'folio.required' => 'El folio es obligatorio',
            'folio.unique' => 'Este folio ya existe',
            'paciente_id.required' => 'Debe seleccionar un paciente',
            'empresa_cliente_id.required' => 'Debe seleccionar una empresa',
            'medico_id.required' => 'Debe seleccionar un médico',
            'tipo.required' => 'Debe seleccionar el tipo de evaluación',
            'fecha_valoracion.before_or_equal' => 'La fecha no puede ser futura',
            'dictamen.required' => 'Debe indicar el dictamen',
            'ta_sistolica.min' => 'La presión sistólica debe ser ≥ 50',
            'ta_diastolica.min' => 'La presión diastólica debe ser ≥ 30',
            'spo2.min' => 'SpO2 debe ser ≥ 70%',
        ];
    }
}