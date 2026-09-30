<?php

namespace App\Services\ClinicaTrabajo;

use App\Models\ClinicaTrabajo\ValoracionOcupacional;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Paciente;
use App\Models\Medico;
use App\Models\EmpresaCliente;
use App\Models\PuestoTrabajo;

class MedicinaService
{
    /**
     * Crea una nueva valoración médica/ocupacional
     */
    public function crear(array $datos, int $usuarioId): ValoracionOcupacional
    {
        // Generar folio único: FO-YYYYMMDD-NNNN
        if (!isset($datos['folio'])) {
            $datos['folio'] = $this->generarFolio();
        }

        $valoracion = ValoracionOcupacional::create($datos);

        activity('medicina')
            ->causedBy(Auth::user())
            ->performedOn($valoracion)
            ->log('created');

        return $valoracion;
    }

    /**
     * Crea la ficha médica ocupacional completa desde MasterFichaOcupacional
     */
    public function crearFichaCompleta(array $datos, int $usuarioId): ValoracionOcupacional
    {
        $paciente = Paciente::findOrFail($datos['paciente_id']);
        $datosPuesto = $datos['datos_puesto'] ?? [];
        $exposicionRiesgos = $datos['exposicion_riesgos'] ?? [];
        $antecedentes = $datos['antecedentes'] ?? [];
        $clinicoExamen = $datos['clinico_examen'] ?? [];

        // Determinar empresa y puesto del paciente si no vienen en datos
        $empresaId = $datosPuesto['empresa_cliente_id'] ?? $paciente->empresa_cliente_id;
        $puestoId = $datosPuesto['puesto_trabajo_id'] ?? $paciente->puesto_trabajo_id;

        // Generar folio único
        $folio = $this->generarFolio();

        $valoracion = ValoracionOcupacional::create([
            'folio' => $folio,
            'paciente_id' => $paciente->id,
            'empresa_cliente_id' => $empresaId,
            'puesto_trabajo_id' => $puestoId,
            'medico_id' => $usuarioId,
            'fecha_valoracion' => $datosPuesto['fecha_evaluacion'] ?? now(),
            'tipo' => $datosPuesto['tipo_evaluacion'] ?? 'Inicial',

            // Datos de identificación laboral
            'puesto_nombre_snapshot' => $datosPuesto['puesto'] ?? $datosPuesto['descripcion_puesto'] ?? '',
            'empresa_nombre_snapshot' => $datosPuesto['empresa'] ?? '',
            'departamento' => $datosPuesto['departamento'] ?? '',
            'antiguedad_puesto' => $datosPuesto['antiguedad'] ?? '',
            'antiguedad_empresa' => $datosPuesto['antiguedad_empresa'] ?? '',
            'tipo_contrato' => $datosPuesto['tipo_contrato'] ?? '',
            'jornada' => $datosPuesto['jornada'] ?? '',
            'descripcion_puesto' => $datosPuesto['descripcion_puesto'] ?? '',
            'riesgos_identificados' => $datosPuesto['riesgos_identificados'] ?? '',

            // Signos vitales
            'ta_sistolica' => $this->extraerSistolica($datosPuesto['signos_vitales']['presion_arterial'] ?? ''),
            'ta_diastolica' => $this->extraerDiastolica($datosPuesto['signos_vitales']['presion_arterial'] ?? ''),
            'fc' => $datosPuesto['signos_vitales']['frecuencia_cardiaca'] ?? null,
            'fr' => $datosPuesto['signos_vitales']['frecuencia_respiratoria'] ?? null,
            'spo2' => $datosPuesto['signos_vitales']['saturacion_oxigeno'] ?? null,
            'temperatura' => $datosPuesto['signos_vitales']['temperatura'] ?? null,
            'peso_kg' => $clinicoExamen['signos_vitales_peso'] ?? null,
            'estatura_cm' => $clinicoExamen['signos_vitales_estatura'] ? ($clinicoExamen['signos_vitales_estatura'] * 100) : null,

            // Exposición y riesgos
            'agentes_exposicion' => $exposicionRiesgos['agentes'] ?? [],
            'condiciones_riesgo' => $exposicionRiesgos['condiciones_riesgo'] ?? [],
            'otras_condiciones' => $exposicionRiesgos['otras_condiciones'] ?? [],
            'empresas_anteriores' => $exposicionRiesgos['empresas_anteriores'] ?? [],

            // Antecedentes
            'antecedentes_heredo_familiares' => $antecedentes['heredo_familiares'] ?? [],
            'antecedentes_no_patologicos' => $antecedentes['no_patologicos'] ?? [],
            'antecedentes_gineco_urologicos' => array_merge(
                $antecedentes['gineco'] ?? [],
                $antecedentes['urologo'] ?? []
            ),
            'antecedentes_patologicos_activos' => $antecedentes['patologicos'] ?? [],
            'accidentes_trabajo' => $antecedentes['accidentes'] ?? [],

            // Examen clínico
            'comorbilidades' => array_filter([
                'hipertension' => $clinicoExamen['comorbilidades_hipertension'] ?? false,
                'diabetes' => $clinicoExamen['comorbilidades_diabetes'] ?? false,
                'asma' => $clinicoExamen['comorbilidades_asma'] ?? false,
                'cardiopatia' => $clinicoExamen['comorbilidades_cardiopatia'] ?? false,
                'neurologica' => $clinicoExamen['comorbilidades_neurologica'] ?? false,
                'psiquiatrica' => $clinicoExamen['comorbilidades_psiquiatrica'] ?? false,
            ]),
            'medicamentos_actuales' => $clinicoExamen['medicamentos_actuales'] ?? '',
            'alergias' => $clinicoExamen['alergias'] ?? '',
            'exploracion_fisica_funcional' => $clinicoExamen['hallazgos_examen'] ?? '',
            'examen_normal' => $clinicoExamen['examen_normal'] ?? [],
            'estudios_paraclinicos_resumen' => $clinicoExamen['paraclinicos'] ?? '',

            // Dictamen (se completa después)
            'dictamen' => $datos['aptitud'] ?? 'Pendiente',
        ]);

        activity('medicina')
            ->causedBy(Auth::user())
            ->performedOn($valoracion)
            ->log('created');

        return $valoracion;
    }

    /**
     * Actualiza una valoración existente
     */
    public function actualizar(int $id, array $datos): ValoracionOcupacional
    {
        $valoracion = ValoracionOcupacional::findOrFail($id);
        $valoracion->update($datos);

        activity('medicina')
            ->causedBy(Auth::user())
            ->performedOn($valoracion)
            ->log('updated');

        return $valoracion;
    }

    private function generarFolio(): string
    {
        $año = now()->year;
        $mes = now()->month;
        $ultimoNumero = ValoracionOcupacional::whereYear('created_at', $año)
            ->whereRaw('MONTH(created_at) = ?', [$mes])
            ->max(DB::raw('CAST(SUBSTRING(folio, 4, 4) AS UNSIGNED)')) ?? 0;

        $nuevoNumero = str_pad($ultimoNumero + 1, 4, '0', STR_PAD_LEFT);

        return "FO-{$año}{str_pad($mes, 2, '0', STR_PAD_LEFT)}-{$nuevoNumero}";
    }

    private function extraerSistolica(string $presion): ?int
    {
        if (empty($presion) || !str_contains($presion, '/')) {
            return null;
        }
        $partes = explode('/', $presion);
        return (int) trim($partes[0]);
    }

    private function extraerDiastolica(string $presion): ?int
    {
        if (empty($presion) || !str_contains($presion, '/')) {
            return null;
        }
        $partes = explode('/', $presion);
        return (int) trim($partes[1]);
    }
}