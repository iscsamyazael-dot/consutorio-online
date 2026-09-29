<?php

namespace App\Http\Requests\ClinicaTrabajo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNutricionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'folio' => 'required|string|unique:valoraciones_nutricion,folio',
            'paciente_id' => 'required|exists:pacientes,id',
            'empresa_cliente_id' => 'required|exists:empresas_cliente,id',
            'puesto_trabajo_id' => 'nullable|exists:puestos_trabajo,id',
            'nutriologo_id' => 'required|exists:medicos,id',
            'tipo_evaluacion' => [
                'required',
                Rule::in(['ingreso','periodica','seguimiento','extraordinaria'])
            ],
            'fecha_valoracion' => 'required|date|before_or_equal:today',
            'hora_valoracion' => 'nullable|date_format:H:i',

            // Riesgo laboral (enums)
            'tension_emocional' => [
                'nullable',
                Rule::in(['bajo','medio','alto','muy_alto'])
            ],
            'alta_responsabilidad' => [
                'nullable',
                Rule::in(['bajo','medio','alto','muy_alto'])
            ],
            'carga_excesiva_trabajo' => [
                'nullable',
                Rule::in(['bajo','medio','alto','muy_alto'])
            ],
            'turno_rotativo' => [
                'nullable',
                Rule::in(['bajo','medio','alto','muy_alto'])
            ],
            'turno_nocturno' => [
                'nullable',
                Rule::in(['bajo','medio','alto','muy_alto'])
            ],
            'trabajo_repetitivo' => [
                'nullable',
                Rule::in(['bajo','medio','alto','muy_alto'])
            ],
            'actividad_rapida_variable' => [
                'nullable',
                Rule::in(['bajo','medio','alto','muy_alto'])
            ],
            'actividad_monotona_lenta' => [
                'nullable',
                Rule::in(['bajo','medio','alto','muy_alto'])
            ],
            'exp_temperatura_elevada_baja' => [
                'nullable',
                Rule::in(['bajo','medio','alto','muy_alto'])
            ],

            // Hábitos
            'tabaquismo' => 'boolean',
            'alcoholismo' => 'boolean',
            'varia_consumo_estres' => 'boolean',
            'varia_consumo_tristeza' => 'boolean',

            // Escalas
            'escala_fagerstrm' => 'nullable|integer|min:0|max:10',
            'escala_audit' => 'nullable|integer|min:0|max:40',
            'frecuencia_consumo_alcohol' => [
                'nullable',
                Rule::in(['nunca','mensual','semanal','diario'])
            ],

            // Síntomas gastrointestinales
            'refiere_diarrea' => 'boolean',
            'refiere_gastritis' => 'boolean',
            'refiere_ulcera' => 'boolean',
            'refiere_nauseas' => 'boolean',
            'refiere_reflujo' => 'boolean',
            'refiere_vomitos' => 'boolean',
            'refiere_colitis' => 'boolean',

            // Antropometría
            'estatura_m' => 'nullable|numeric|min:0|max:3',
            'peso_kg' => 'nullable|numeric|min:0|max:300',
            'peso_ideal_kg' => 'nullable|numeric|min:0|max:200',
            'circunferencia_cintura_cm' => 'nullable|numeric|min:0|max:200',
            'circunferencia_cadera_cm' => 'nullable|numeric|min:0|max:200',
            'circunferencia_brazo_cm' => 'nullable|numeric|min:0|max:100',
            'grasa_corporal_pct' => 'nullable|numeric|min:0|max:100',
            'grasa_visceral_pct' => 'nullable|numeric|min:0|max:100',
            'musculo_pct' => 'nullable|numeric|min:0|max:100',

            // Bioquímica
            'glucosa_mg_dl' => 'nullable|numeric|min:0|max:500',
            'trigliceridos_mg_dl' => 'nullable|numeric|min:0|max:1000',
            'acido_urico_mg_dl' => 'nullable|numeric|min:0|max:20',
            'hemoglobina_g_dl' => 'nullable|numeric|min:0|max:25',
            'colesterol_total_mg_dl' => 'nullable|numeric|min:0|max:500',
            'hdl_mg_dl' => 'nullable|numeric|min:0|max:200',
            'ldl_mg_dl' => 'nullable|numeric|min:0|max:500',

            // Diagnóstico
            'diagnostico_problema' => 'nullable|string',
            'imc_clasificacion' => 'nullable|string',
            'aptitud' => [
                'required',
                Rule::in(['apto','apto_con_restricciones','no_apto'])
            ],
            'restricciones' => 'nullable|string',
            'recomendaciones' => 'nullable|string',
            'seguimiento_requerido' => 'boolean',
            'plazo_proximo_seguimiento' => 'nullable|date|after:fecha_valoracion',

            // Alimentos (tabla puente)
            'alimentos_ids' => 'nullable|array',
            'alimentos_ids.*' => 'exists:catalogo_alimentos,id',
            'alimentos_frecuencias' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'folio.required' => 'El folio es obligatorio',
            'folio.unique' => 'Este folio ya existe',
            'paciente_id.required' => 'Debe seleccionar un paciente',
            'nutriologo_id.required' => 'Debe seleccionar un nutriólogo',
            'aptitud.required' => 'Debe indicar la aptitud (no tiene default)',
            'fecha_valoracion.before_or_equal' => 'La fecha no puede ser futura',
        ];
    }
}