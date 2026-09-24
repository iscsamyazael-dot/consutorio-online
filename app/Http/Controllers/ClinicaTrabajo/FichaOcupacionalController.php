<?php

namespace App\Http\Controllers\ClinicaTrabajo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClinicaTrabajo\ValoracionOcupacional;
use App\Services\IAClinicaTrabajoService;

class FichaOcupacionalController extends Controller
{
    /**
     * API: Retorna los datos de las fichas médicas ocupacionales de la base de datos.
     */
    public function index()
    {
        // En nuestra arquitectura, index() únicamente sirve para retornar los datos del modelo/API
        $fichas = ValoracionOcupacional::with(['paciente', 'empresaCliente', 'puestoTrabajo', 'medico'])->latest()->get();
        return response()->json($fichas);
    }

    /**
     * Retorna la vista Blade contenedora para la interfaz de la Ficha Médica Ocupacional.
     */
    public function vista()
    {
        return view('clinica-trabajo.ficha-ocupacional.index');
    }

    /**
     * API: Guarda un nuevo registro de Valoración Ocupacional.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'empresa' => 'nullable|string',
            'departamento' => 'nullable|string',
            'puesto_trabajo' => 'nullable|string',
            'antiguedad' => 'nullable|string',
            'descripcion_puesto' => 'nullable|string',
            'antecedentes' => 'nullable|string',
            'examen_fisico' => 'nullable|string',
            'examenes_complementarios' => 'nullable|string',
            'aptitud' => 'required|string',
            'restricciones' => 'nullable|string',
            'recomendaciones' => 'nullable|string',
        ]);

        // Guardamos el registro de la valoración ocupacional
        $ficha = new ValoracionOcupacional();
        $ficha->folio = 'FO-' . strtoupper(uniqid());
        $ficha->paciente_id = $validated['paciente_id'];
        $ficha->puesto_nombre_snapshot = $validated['puesto_trabajo'] ?? 'Operador';
        $ficha->tipo = 'Ingreso';
        $ficha->fecha_valoracion = now();
        
        // Mapeo de campos adicionales
        $ficha->antecedentes_patologicos_activos = $validated['antecedentes'] ?? '';
        $ficha->exploracion_fisica_funcional = $validated['examen_fisico'] ?? '';
        $ficha->estudios_paraclinicos_resumen = $validated['examenes_complementarios'] ?? '';
        $ficha->dictamen = $validated['aptitud'];
        $ficha->restricciones = $validated['restricciones'] ?? '';
        $ficha->plan_intervencion = $validated['recomendaciones'] ?? '';
        
        // Intenta asociar al médico autenticado si existe
        if (auth()->check()) {
            $ficha->medico_id = auth()->user()->id; // O ajusta según la relación de tu modelo User/Medico
        }

        $ficha->save();

        return response()->json([
            'success' => true,
            'message' => 'Ficha Médica Ocupacional guardada correctamente.',
            'data' => $ficha
        ], 201);
    }

    /**
     * API: analiza el dictado/chat completo y devuelve sugerencias para
     * llenar la ficha. NO guarda nada; el médico revisa y confirma.
     */
    public function analizar(Request $request, IAClinicaTrabajoService $ia)
    {
        $data = $request->validate([
            'texto' => 'required|string|min:10|max:40000',
        ]);
    
        $resultado = $ia->analizarFichaOcupacional($data['texto']);
    
        if ($resultado === null) {
            return response()->json([
                'success' => false,
                'message' => 'La IA no pudo procesar el dictado. Intenta de nuevo o llena la ficha manualmente.',
            ], 502);
        }
    
        unset($resultado['debug_usage']);
    
        return response()->json([
            'success' => true,
            'data'    => $resultado,
        ]);
    }
}
