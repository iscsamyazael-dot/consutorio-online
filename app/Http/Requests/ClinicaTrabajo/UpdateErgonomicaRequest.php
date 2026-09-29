<?php

namespace App\Http\Requests\ClinicaTrabajo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateErgonomicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'tipo_evaluacion' => [
                'nullable',
                Rule::in(['ingreso','periodica','seguimiento','extraordinaria','reingreso'])
            ],
            'fecha_valoracion' => 'nullable|date|before_or_equal:today',
            'fecha_evaluacion_puesto' => 'nullable|date',
            'instrumento_utilizado' => [
                'nullable',
                Rule::in(['RULA','REBA','NIOSH','OTRO'])
            ],

            'rula_puntuacion' => 'nullable|integer|min:1|max:7',
            'rula_nivel_riesgo' => [
                'nullable',
                Rule::in(['insignificante','bajo','medio','alto'])
            ],
            'reba_puntuacion' => 'nullable|integer|min:1|max:15',
            'reba_nivel_accion' => [
                'nullable',
                Rule::in(['insignificante','bajo','medio','alto','muy_alto','cambios_inmediatos'])
            ],
            'niosh_indice_levantamiento' => 'nullable|numeric|min:0',
            'niosh_es_seguro' => 'boolean',
            'niosh_peso_real_kg' => 'nullable|numeric|min:0|max:200',
            'niosh_peso_recomendado_kg' => 'nullable|numeric|min:0|max:200',

            'posturas_forzadas' => 'boolean',
            'movimientos_repetitivos' => 'boolean',
            'manipulacion_cargas' => 'boolean',
            'carga_maxima_manipulada_kg' => 'nullable|numeric|min:0|max:500',

            'examen_medico_realizado' => 'boolean',
            'seguimiento_requerido' => 'boolean',
            'plazo_proximo_seguimiento' => 'nullable|date',

            'aptitud' => [
                'required',
                Rule::in(['apto','apto_con_restricciones','no_apto'])
            ],
            'restricciones' => 'nullable|string',
            'recomendaciones' => 'nullable|string',

            'riesgos_ids' => 'nullable|array',
            'riesgos_ids.*' => 'exists:catalogo_riesgos_ergonomicos,id',
            'riesgos_controles' => 'nullable|array',
        ];
    }
}