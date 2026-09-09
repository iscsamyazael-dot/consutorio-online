<?php

namespace App\Http\Controllers;

use App\Models\ExpedienteClinico;
use Illuminate\Http\Request;

class ExpedienteClinicoController extends Controller
{
    /**
     * Devuelve el expediente clínico del paciente (o uno vacío si aún no
     * existe) junto con el progreso de los 9 campos exigidos por la
     * NOM-004-SSA3-2012. Mismo cálculo que ya usa
     * IAClinicaService::actualizarExpedienteClinico, pero solo de lectura.
     */
    public function obtener($pacienteId)
    {
        $expediente = ExpedienteClinico::firstOrNew(['paciente_id' => $pacienteId]);

        $campos = ExpedienteClinico::CAMPOS_CLINICOS;
        $completados = 0;
        $faltantes = [];

        foreach ($campos as $campo) {
            if (trim((string) $expediente->{$campo}) !== '') {
                $completados++;
            } else {
                $faltantes[] = $campo;
            }
        }

        return response()->json([
            'success'    => true,
            'expediente' => $expediente,
            'progreso'   => [
                'completados'      => $completados,
                'total'            => count($campos),
                'completa'         => $completados === count($campos),
                'campos_faltantes' => $faltantes,
            ],
        ]);
    }

    /**
     * Guarda ediciones manuales del médico sobre el expediente clínico
     * (los mismos 9 campos que autollena la IA, más los generales:
     * tipo_sangre, alergias, enfermedades_cronicas, notas_generales).
     *
     * Si el médico marca "revisado", se registra quién y cuándo lo
     * revisó — igual que 'estado' en NotaPsoapp, pero aquí es un
     * checkpoint único por paciente, no por consulta.
     */
    public function guardar(Request $request, $pacienteId)
    {
        $validated = $request->validate([
            'tipo_sangre'                      => 'nullable|string|max:10',
            'alergias'                         => 'nullable|string',
            'enfermedades_cronicas'            => 'nullable|string',
            'antecedentes_heredofamiliares'    => 'nullable|string',
            'antecedentes_medicos'             => 'nullable|string',
            'antecedentes_quirurgicos'         => 'nullable|string',
            'medicamentos_actuales'            => 'nullable|string',
            'antecedentes_no_patologicos'      => 'nullable|string',
            'padecimiento_actual'              => 'nullable|string',
            'interrogatorio_aparatos_sistemas' => 'nullable|string',
            'exploracion_fisica'               => 'nullable|string',
            'plan_tratamiento_inicial'         => 'nullable|string',
            'notas_generales'                  => 'nullable|string',
            'marcar_revisado'                  => 'nullable|boolean',
        ]);

        $marcarRevisado = $validated['marcar_revisado'] ?? false;
        unset($validated['marcar_revisado']);

        $expediente = ExpedienteClinico::firstOrNew(['paciente_id' => $pacienteId]);
        $expediente->fill($validated);
        $expediente->paciente_id = $pacienteId;

        if ($marcarRevisado) {
            $expediente->revisado_medico = true;
            $expediente->firmado_por = auth()->id();
            $expediente->firmado_en = now();
        }

        // Recalculamos completado_ia por si la edición manual terminó de
        // llenar lo que faltaba (mismo criterio que en el servicio de IA).
        $campos = ExpedienteClinico::CAMPOS_CLINICOS;
        $completados = collect($campos)->filter(
            fn ($campo) => trim((string) $expediente->{$campo}) !== ''
        )->count();
        $expediente->completado_ia = $completados === count($campos);

        $expediente->save();

        return response()->json([
            'success'    => true,
            'expediente' => $expediente->fresh(),
        ]);
    }
}