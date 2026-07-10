<?php

namespace App\Services;

use App\Models\SintomaDetectado;
use App\Models\ConsultaTranscripcion;
use App\Models\EvaluacionIA;
use App\Models\AlertaClinica;
use Illuminate\Support\Facades\Log;

class IAClinicaService
{
    /**
     * Analizar transcripción médica
     */
    public function analizarTranscripcion($texto, $consulta)
    {
        // Detectar síntomas
        $sintomas = $this->detectarSintomas($texto);
        // Guardar síntomas
        foreach ($sintomas as $sintoma) {

            /*
            |--------------------------------------------------------------------------
            | VALIDAR DUPLICADO
            |--------------------------------------------------------------------------
            */

            $existe = SintomaDetectado::where(
                'consulta_id',
                $consulta->id
            )
            ->where(
                'nombre_sintoma',
                $sintoma
            )
            ->exists();

            /*
            |--------------------------------------------------------------------------
            | GUARDAR SOLO SI NO EXISTE
            |--------------------------------------------------------------------------
            */
            if(!$existe){
                SintomaDetectado::create([
                'consulta_id' => $consulta->id,
                'consulta_folio' => $consulta->folio,
                'session_uuid' => $consulta->session_uuid,
                'nombre_sintoma' => $sintoma,
                'origen' => 'ia'
                ]);
            }
        }

        // ==========================
        // GENERAR EVALUACIÓN IA
        // ==========================

        $evaluacion = $this->generarEvaluacionIA($sintomas);
        
        // ==========================
        // GENERAR ALERTA CLINICA IA
        // ==========================
        $this->generarAlertasClinicas(
            $sintomas,
            $consulta
        );

        // ==========================
        // GUARDAR EVALUACIÓN IA
        // ==========================

        EvaluacionIA::create([

            'consulta_id' => $consulta->id,
            'consulta_folio' => $consulta->folio,
            'session_uuid' => $consulta->session_uuid,
            'sintomas_detectados' => implode(', ', $sintomas),
            'diagnostico_probable' => $evaluacion['diagnostico'],
            'recomendacion' => $evaluacion['recomendacion'],
            'confianza' => $evaluacion['confianza']
        ]);


        return [
            'success' => true,
            'sintomas' => $sintomas,
            'evaluacion_ia' => $evaluacion
        ];
    }

    /**
     * Detectar síntomas básicos
     */
    private function detectarSintomas($texto)
    {
        $texto = strtolower($texto);

        $catalogo = [
            'fiebre',
            'dolor de cabeza',
            'tos',
            'mareo',
            'náusea',
            'vomito',
            'dolor abdominal',
            'dificultad respiratoria',
            'dolor toracico'
        ];

        $detectados = [];

        foreach ($catalogo as $sintoma) {

            if (str_contains($texto, $sintoma)) {
                $detectados[] = $sintoma;
            }
        }

        return $detectados;
    }

    /**
     * Generar evaluación IA básica
     */
    private function generarEvaluacionIA($sintomas)
    {

        $diagnostico = 'Síntomas generales detectados';
        $recomendacion = 'Se recomienda valoración médica';
        $confianza = 75;

        // Reglas básicas IA

        if(in_array('fiebre', $sintomas) &&
           in_array('tos', $sintomas)){

            $diagnostico = 'Posible infección respiratoria';

            $recomendacion = 'Monitorear temperatura y acudir a valoración';

            $confianza = 85;
        }

        return [

            'diagnostico' => $diagnostico,
            'recomendacion' => $recomendacion,
            'confianza' => $confianza
        ];
    }

    private function generarAlertasClinicas($sintomas,$consulta){
        // ==========================
        // ALERTA: DIFICULTAS RESPIRATORIA
        // ==========================

        if(in_array('dificultad respiratoria',$sintomas)){
            AlertaClinica::create([
            'consulta_id' => $consulta->id,
            'consulta_folio' => $consulta->folio,
            'session_uuid' => $consulta->session_uuid,
            'paciente_id' => $consulta->paciente_id,
            'tipo_alerta' => 'respiratoria',
            'titulo' => 'Dificultad respiratoria detectada',
            'descripcion' => 'La IA detectó posible compromiso respiratorio.',
            'nivel' => 'critico',
            'nivel_riesgo' => 'alto',
            'estado' => 'pendiente',
            'generada_por_ia' => 1,
            'requiere_atencion' => 1,
            'fecha_alerta' => now()
        ]);
        }

        // ==========================
        // ALERTA: DOLOR TORÁCICO
        // ==========================
        
        if(in_array('dolor toracico',$sintomas)){
            AlertaClinica::create([
            'consulta_id' => $consulta->id,
            'consulta_folio' => $consulta->folio,
            'session_uuid' => $consulta->session_uuid,
            'paciente_id' => $consulta->paciente_id,
            'tipo_alerta' => 'cardiaca',
            'titulo' => 'Dolor torácico detectado',
            'descripcion' =>'La IA detectó posible riesgo cardíaco.',
            'nivel' => 'alto',
            'nivel_riesgo' => 'alto',
            'estado' => 'pendiente',
            'generada_por_ia' => 1,
            'requiere_atencion' => 1,
            'fecha_alerta' => now()
        ]);
        }
    }
}