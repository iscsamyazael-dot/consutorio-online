<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePsicologicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }
    
    public function rules(): array
    {
        return [
            // folio se ignora — no es editable
            
            // Campos que pueden actualizarse:
            'ambiente_laboral_descripcion' => 'nullable|string',
            'puntuacion_riesgo_texto' => 'nullable|string',
            'diagnostico_clinico' => 'nullable|string',
            'codigo_cie11' => 'nullable|string|max:20',
            'relacionado_con_trabajo' => 'boolean',
            'aptitud' => [
                'required',
                Rule::in(['apto','apto_con_restricciones','no_apto'])
            ],
            'restricciones' => 'nullable|string',
            'recomendaciones' => 'nullable|string',
            'requiere_seguimiento' => 'boolean',
            'plazo_proximo_seguimiento' => 'nullable|date',
            'canalizado_a' => [
                'nullable',
                Rule::in(['psicologia_externa','imss','otro','ninguno'])
            ],
            'lugar_canalizacion' => 'nullable|string|max:200',
            'notas_seguimiento' => 'nullable|string',
            
            // Factores psicosociales
            'factores_ids' => 'nullable|array',
            'factores_ids.*' => 'exists:catalogo_factores_psicosociales,id',
        ];
    }
}