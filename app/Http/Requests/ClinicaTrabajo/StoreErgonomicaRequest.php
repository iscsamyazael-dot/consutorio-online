<?php

namespace App\Http\Requests\ClinicaTrabajo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreErgonomicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'folio' => 'required|string|unique:valoraciones_ergonomicas,folio',
            'paciente_id' => 'required|exists:pacientes,id',
            'empresa_cliente_id' => 'required|exists:empresas_cliente,id',
            'puesto_trabajo_id' => 'nullable|exists:puestos_trabajo,id',
            'ergonomo_id' => 'required|exists:medicos,id',
            'tipo_evaluacion' => [
                'required',
                Rule::in(['ingreso','periodica','seguimiento','extraordinaria','reingreso'])
            ],
            'fecha_valoracion' => 'required|date|before_or_equal:today',
            'fecha_evaluacion_puesto' => 'nullable|date',
            'instrumento_utilizado' => [
                'required',
                Rule::in(['RULA','REBA','NIOSH','OTRO'])
            ],

            // RULA
            'rula_puntuacion' => 'nullable|integer|min:1|max:7',
            'rula_nivel_riesgo' => [
                'nullable',
                Rule::in(['insignificante','bajo','medio','alto'])
            ],

            // REBA
            'reba_puntuacion' => 'nullable|integer|min:1|max:15',
            'reba_nivel_accion' => [
                'nullable',
                Rule::in(['insignificante','bajo','medio','alto','muy_alto','cambios_inmediatos'])
            ],

            // NIOSH
            'niosh_indice_levantamiento' => 'nullable|numeric|min:0',
            'niosh_es_seguro' => 'boolean',
            'niosh_peso_real_kg' => 'nullable|numeric|min:0|max:200',
            'niosh_peso_recomendado_kg' => 'nullable|numeric|min:0|max:200',

            // Hallazgos ergonómicos
            'posturas_forzadas' => 'boolean',
            'movimientos_repetitivos' => 'boolean',
            'manipulacion_cargas' => 'boolean',
            'carga_maxima_manipulada_kg' => 'nullable|numeric|min:0|max:500',

            // Examen médico
            'examen_medico_realizado' => 'boolean',
            'seguimiento_requerido' => 'boolean',
            'plazo_proximo_seguimiento' => 'nullable|date|after:fecha_valoracion',

            // Diagnóstico
            'aptitud' => [
                'required',
                Rule::in(['apto','apto_con_restricciones','no_apto'])
            ],
            'restricciones' => 'nullable|string',
            'recomendaciones' => 'nullable|string',

            // Riesgos ergonómicos (tabla puente)
            'riesgos_ids' => 'nullable|array',
            'riesgos_ids.*' => 'exists:catalogo_riesgos_ergonomicos,id',
            'riesgos_controles' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'folio.required' => 'El folio es obligatorio',
            'folio.unique' => 'Este folio ya existe',
            'paciente_id.required' => 'Debe seleccionar un paciente',
            'ergonomo_id.required' => 'Debe seleccionar un ergonómico',
            'instrumento_utilizado.required' => 'Debe seleccionar un instrumento (RULA, REBA, NIOSH, OTRO)',
            'aptitud.required' => 'Debe indicar la aptitud (no tiene default)',
            'fecha_valoracion.before_or_equal' => 'La fecha no puede ser futura',
        ];
    }
}