<?php

namespace App\Http\Requests\ClinicaTrabajo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePsicologicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'folio' => 'required|string|unique:valoraciones_psicologicas,folio',
            'paciente_id' => 'required|exists:pacientes,id',
            'empresa_cliente_id' => 'required|exists:empresas_cliente,id',
            'puesto_trabajo_id' => 'nullable|exists:puestos_trabajo,id',
            'psicologo_id' => 'required|exists:medicos,id',
            'tipo_evaluacion' => [
                'required',
                Rule::in(['ingreso','periodica','seguimiento_trauma','extraordinaria'])
            ],
            'fecha_valoracion' => 'required|date|before_or_equal:today',
            'hora_valoracion' => 'nullable|date_format:H:i',

            // Guía I (Eventos traumáticos)
            'guia_ref_i_aplicada' => 'boolean',
            'ha_presenciado_evento_traumatico' => 'nullable|boolean',
            'descripcion_evento_traumatico' => 'nullable|string|max:1000',
            'fecha_evento_traumatico' => 'nullable|date',
            'requiere_canalizacion_imss' => 'boolean',

            // Guía III (Factores psicosociales)
            'guia_ref_iii_aplicada' => 'boolean',
            'ambiente_laboral_descripcion' => 'nullable|string',
            'puntuacion_riesgo_texto' => 'nullable|string',

            // Diagnóstico
            'diagnostico_clinico' => 'nullable|string',
            'codigo_cie11' => 'nullable|string|max:20',
            'relacionado_con_trabajo' => 'boolean',
            'aptitud' => [
                'required',
                Rule::in(['apto','apto_con_restricciones','no_apto'])
            ],
            'restricciones' => 'nullable|string',
            'recomendaciones' => 'nullable|string',

            // Seguimiento
            'requiere_seguimiento' => 'boolean',
            'plazo_proximo_seguimiento' => 'nullable|date|after:fecha_valoracion',
            'canalizado_a' => [
                'nullable',
                Rule::in(['psicologia_externa','imss','otro','ninguno'])
            ],
            'lugar_canalizacion' => 'nullable|string|max:200',
            'notas_seguimiento' => 'nullable|string',

            // Factores psicosociales (tabla puente)
            'factores_ids' => 'nullable|array',
            'factores_ids.*' => 'exists:catalogo_factores_psicosociales,id',
            'factores_severidad' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'folio.required' => 'El folio es obligatorio',
            'folio.unique' => 'Este folio ya existe',
            'paciente_id.required' => 'Debe seleccionar un paciente',
            'aptitud.required' => 'Debe indicar la aptitud (no tiene default)',
            'fecha_valoracion.before_or_equal' => 'La fecha no puede ser futura',
            'plazo_proximo_seguimiento.after' => 'El plazo de seguimiento debe ser posterior a la fecha de valoración',
        ];
    }
}