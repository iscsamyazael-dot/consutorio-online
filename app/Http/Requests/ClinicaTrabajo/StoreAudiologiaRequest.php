<?php

namespace App\Http\Requests\ClinicaTrabajo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAudiologiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'folio' => 'required|string|unique:valoraciones_audiologia,folio',
            'paciente_id' => 'required|exists:pacientes,id',
            'empresa_cliente_id' => 'required|exists:empresas_cliente,id',
            'puesto_trabajo_id' => 'nullable|exists:puestos_trabajo,id',
            'audiologo_id' => 'required|exists:medicos,id',
            'tipo_evaluacion' => [
                'required',
                Rule::in(['ingreso','periodica','seguimiento','extraordinaria'])
            ],
            'fecha_valoracion' => 'required|date|before_or_equal:today',
            'hora_valoracion' => 'nullable|date_format:H:i',

            // Exposición laboral
            'exposicion_ruido_actual' => [
                'nullable',
                Rule::in(['<85','85-90','90-95','95-100','>100'])
            ],
            'anos_exposicion_ruido' => 'nullable|integer|min:0|max:50',
            'uso_protector_auditivo' => [
                'nullable',
                Rule::in(['siempre','frecuentemente','ocasionalmente','nunca','no_aplica'])
            ],
            'disolventes_exposicion' => 'boolean',
            'metales_exposicion' => 'boolean',
            'gases_exposicion' => 'boolean',
            'sales_exposicion' => 'boolean',
            'tabaco_exposicion' => 'boolean',

            // Ototóxicos
            'antibioticos_consume' => 'boolean',
            'diureticos_consume' => 'boolean',
            'salicilatos_consume' => 'boolean',
            'antimalarcicos_consume' => 'boolean',

            // Ocio ruidoso
            'armas_fuego_caza' => 'boolean',
            'niveles_altos_musica' => 'boolean',
            'lugares_ruidosos_discoteca' => 'boolean',
            'automovilismo_motociclismo' => 'boolean',
            'otros_pasatiempos_ruidosos' => 'boolean',

            // Antecedentes médicos
            'historia_familiar_perdida_auditiva' => 'boolean',
            'sarampion' => 'boolean',
            'rubeola' => 'boolean',
            'paperas' => 'boolean',
            'meningitis' => 'boolean',
            'otitis_externa_media' => 'boolean',
            'covid19_antecedente' => 'boolean',
            'resfriad_reciente' => 'boolean',
            'alergias_auditivas' => 'boolean',
            'uso_aparato_auditivo' => 'boolean',

            // Síntomas
            'niega_sintomatologia_auditiva' => 'boolean',
            'otalgia_der' => 'boolean',
            'otalgia_izq' => 'boolean',
            'otorrea_der' => 'boolean',
            'otorrea_izq' => 'boolean',
            'prurito_der' => 'boolean',
            'prurito_izq' => 'boolean',
            'acufenos_der' => 'boolean',
            'acufenos_izq' => 'boolean',
            'vertigo_presente' => 'boolean',
            'otoscopia_normal' => 'boolean',
            'otoscopia_patologica' => 'boolean',

            // Audiometría (18 campos - 9 frecuencias × 2 oídos)
            'au_125_hz_der' => 'nullable|integer|min:0|max:120',
            'au_250_hz_der' => 'nullable|integer|min:0|max:120',
            'au_500_hz_der' => 'nullable|integer|min:0|max:120',
            'au_1000_hz_der' => 'nullable|integer|min:0|max:120',
            'au_2000_hz_der' => 'nullable|integer|min:0|max:120',
            'au_3000_hz_der' => 'nullable|integer|min:0|max:120',
            'au_4000_hz_der' => 'nullable|integer|min:0|max:120',
            'au_6000_hz_der' => 'nullable|integer|min:0|max:120',
            'au_8000_hz_der' => 'nullable|integer|min:0|max:120',

            'au_125_hz_izq' => 'nullable|integer|min:0|max:120',
            'au_250_hz_izq' => 'nullable|integer|min:0|max:120',
            'au_500_hz_izq' => 'nullable|integer|min:0|max:120',
            'au_1000_hz_izq' => 'nullable|integer|min:0|max:120',
            'au_2000_hz_izq' => 'nullable|integer|min:0|max:120',
            'au_3000_hz_izq' => 'nullable|integer|min:0|max:120',
            'au_4000_hz_izq' => 'nullable|integer|min:0|max:120',
            'au_6000_hz_izq' => 'nullable|integer|min:0|max:120',
            'au_8000_hz_izq' => 'nullable|integer|min:0|max:120',

            // Interpretación
            'tipo_hipoacusia' => [
                'nullable',
                Rule::in(['normal','leve','moderada','severa','profunda'])
            ],
            'pta_promedio_tonal_puro_der' => 'nullable|integer|min:0|max:120',
            'pta_promedio_tonal_puro_izq' => 'nullable|integer|min:0|max:120',
            'requiere_seguimiento_audiometrico' => 'boolean',
            'recomendacion_seguimiento' => [
                'nullable',
                Rule::in(['ninguno','control_anual','control_6_meses','canalizacion_especialista'])
            ],

            // Diagnóstico
            'aptitud' => [
                'required',
                Rule::in(['apto','apto_con_restricciones','no_apto'])
            ],
            'restricciones' => 'nullable|string',
            'recomendaciones' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'folio.required' => 'El folio es obligatorio',
            'folio.unique' => 'Este folio ya existe',
            'paciente_id.required' => 'Debe seleccionar un paciente',
            'audiologo_id.required' => 'Debe seleccionar un audiólogo',
            'aptitud.required' => 'Debe indicar la aptitud (no tiene default)',
            'fecha_valoracion.before_or_equal' => 'La fecha no puede ser futura',
            'au_*.min' => 'El umbral auditivo no puede ser negativo',
            'au_*.max' => 'El umbral auditivo no puede exceder 120 dB',
        ];
    }
}