<?php

namespace App\Http\Requests\ClinicaTrabajo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAudiologiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
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
            'antibioticos_consume' => 'boolean',
            'diureticos_consume' => 'boolean',
            'salicilatos_consume' => 'boolean',
            'antimalarcicos_consume' => 'boolean',
            'armas_fuego_caza' => 'boolean',
            'niveles_altos_musica' => 'boolean',
            'lugares_ruidosos_discoteca' => 'boolean',
            'automovilismo_motociclismo' => 'boolean',
            'otros_pasatiempos_ruidosos' => 'boolean',
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
            'aptitud' => [
                'required',
                Rule::in(['apto','apto_con_restricciones','no_apto'])
            ],
            'restricciones' => 'nullable|string',
            'recomendaciones' => 'nullable|string',
        ];
    }
}