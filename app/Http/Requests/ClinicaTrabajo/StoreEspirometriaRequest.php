<?php

namespace App\Http\Requests\ClinicaTrabajo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEspirometriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'folio' => 'required|string|unique:valoraciones_espirometria,folio',
            'paciente_id' => 'required|exists:pacientes,id',
            'empresa_cliente_id' => 'required|exists:empresas_cliente,id',
            'puesto_trabajo_id' => 'nullable|exists:puestos_trabajo,id',
            'medico_id' => 'nullable|exists:medicos,id',
            'tipo_evaluacion' => [
                'required',
                Rule::in(['ingreso','periodica','seguimiento','extraordinaria'])
            ],
            'fecha_valoracion' => 'required|date|before_or_equal:today',
            'hora_valoracion' => 'nullable|date_format:H:i',
            'fecha_nacimiento' => 'nullable|date|before_or_equal:today',

            // Datos Generales
            'nombre' => 'required|string|max:255',
            'id_empleado' => 'nullable|string|max:100',
            'edad' => 'nullable|integer|min:14|max:100',
            'sexo' => [
                'nullable',
                Rule::in(['Masculino','Femenino'])
            ],
            'origen_etnico' => [
                'nullable',
                Rule::in(['Hispano','Caucásico','Afrodescendiente','Otro'])
            ],
            'empresa' => 'nullable|string|max:255',
            'altura_cm' => 'nullable|numeric|min:50|max:250',
            'peso_kg' => 'nullable|numeric|min:20|max:300',
            'peso_ideal_kg' => 'nullable|numeric|min:20|max:200',
            'imc' => 'nullable|numeric|min:0|max:100',
            'superficie_corporal' => 'nullable|numeric|min:0|max:5',
            'cigarrillos_dia' => 'nullable|integer|min:0|max:100',
            'anos_fumador' => 'nullable|integer|min:0|max:80',
            'pack_years' => 'nullable|numeric|min:0|max:100',
            'fumador' => 'boolean',
            'asma' => 'boolean',
            'epoc' => 'boolean',
            'antecedente_covid' => 'boolean',
            'rinitis_alergica' => 'boolean',
            'tuberculosis' => 'boolean',
            'neumonia_previa' => 'boolean',
            'comentarios' => 'nullable|string',

            // Parámetros FVL
            'referencia' => [
                'nullable',
                Rule::in(['NHANES III','GLI-2012','Hankinson','Lloveras','Pellegrino'])
            ],
            'interpretacion_predicha' => [
                'nullable',
                Rule::in(['GOLD(2008)/Hardie','ATS/ERS 2021','GOLD-2023'])
            ],
            'fvc_l' => 'nullable|numeric|min:0|max:15',
            'fev1_l' => 'nullable|numeric|min:0|max:10',
            'fev1_fvc_ratio' => 'nullable|numeric|min:0|max:1',
            'fef25_75' => 'nullable|numeric|min:0|max:10',
            'pef_l_s' => 'nullable|numeric|min:0|max:20',
            'fet_s' => 'nullable|numeric|min:0|max:30',
            'fivc_l' => 'nullable|numeric|min:0|max:15',
            'pif_l_s' => 'nullable|numeric|min:0|max:15',
            'eotv_l' => 'nullable|numeric|min:0|max:15',
            'bev_l' => 'nullable|numeric|min:0|max:10',
            'fev1_var_l' => 'nullable|numeric|min:0|max:2',
            'fvc_var_l' => 'nullable|numeric|min:0|max:2',
            'pef_var_l_s' => 'nullable|numeric|min:0|max:5',
            'fef2575_var_l_s' => 'nullable|numeric|min:0|max:5',
            'num_maniobras' => 'nullable|integer|min:1|max:15',

            // Porcentajes predichos
            'fvc_porcentaje_predicho' => 'nullable|integer|min:0|max:200',
            'fev1_porcentaje_predicho' => 'nullable|integer|min:0|max:200',
            'fef25_75_porcentaje_predicho' => 'nullable|integer|min:0|max:200',
            'pef_porcentaje_predicho' => 'nullable|integer|min:0|max:200',

            // Interpretación
            'edad_pulmonar' => 'nullable|integer|min:0|max:120',
            'calidad_sesion' => [
                'nullable',
                Rule::in(['A','B','C','D','F'])
            ],
            'interpretacion_sistema' => [
                'nullable',
                Rule::in(['Espirometría Normal','Patrón Obstructivo','Patrón Restrictivo','Patrón Mixto','Prueba no válida'])
            ],
            'diagnostico_medico' => 'nullable|string',
            'aptitud' => [
                'required',
                Rule::in(['Apto','Apto con restricciones','No apto'])
            ],
            'recomendaciones' => 'nullable|string',
            'seguimiento_requerido' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'folio.required' => 'El folio es obligatorio',
            'folio.unique' => 'Este folio ya existe',
            'paciente_id.required' => 'Debe seleccionar un paciente',
            'empresa_cliente_id.required' => 'Debe seleccionar una empresa',
            'aptitud.required' => 'Debe indicar la aptitud (no tiene default)',
            'fecha_test.before_or_equal' => 'La fecha no puede ser futura',
            'calidad_sesion.in' => 'La calidad de sesión debe ser A, B, C, D o F',
            'interpretacion_sistema.in' => 'Interpretación del sistema no válida',
        ];
    }
}