<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\Consulta;
use App\Models\ConsultaTranscripcion;
use App\Models\Medicamento;
use App\Models\Specialty;

use App\Services\IAClinicaService;

class ConsultaIAController extends Controller
{   
     protected $iaClinicaService;

    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    public function __construct(IAClinicaService $iaClinicaService){
        $this->iaClinicaService = $iaClinicaService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    { 
        try {
            // 1. INICIAR CONSULTA (Solo si se solicita iniciar)
            if($request->iniciar_consulta) {

                // El frontend debe mandar el paciente_id real (el que se
                // seleccionó con doble clic en la lista de pacientes y se
                // guardó en localStorage). Antes esto estaba hardcodeado
                // en 1, por eso todas las consultas se guardaban con el
                // mismo paciente sin importar cuál se seleccionara.
                $validated = $request->validate([
                    'paciente_id' => 'required|integer|exists:pacientes,id',
                ]);

                $ultimaConsulta = Consulta::latest('id')->first();
                $numero = $ultimaConsulta ? $ultimaConsulta->id + 1 : 1;
                $folio = 'CONS-'.date('Y').'-'.str_pad($numero, 4, '0', STR_PAD_LEFT);

                $consulta = Consulta::create([
                    'paciente_id' => $validated['paciente_id'],
                    'folio' => $folio,
                    // Usamos el usuario autenticado en vez de un ID fijo,
                    // para que quede registrado quién abrió la consulta.
                    'user_id' => auth()->id(),
                    'motivo_consulta' => 'Consulta Inteligente',
                    'estado' => 'en_proceso',
                    'consulta_inteligente' => 1,
                    'session_uuid' => Str::uuid()
                ]);

                return response()->json([
                    'success' => true,
                    'consulta_id' => $consulta->id,
                    'consulta_folio' => $consulta->folio,
                    'session_uuid' => $consulta->session_uuid
                ]);
            }

            // 2. BUSCAR CONSULTA EXISTENTE
            $consulta = Consulta::find($request->consulta_id);
            if (!$consulta) {
                return response()->json(['success' => false, 'error' => 'Consulta no encontrada'], 404);
            }

            // 3. PROCESAR TRANSCRIPCIÓN Y IA (Solo si hay texto nuevo)
            $iaData = null;
            if ($request->has('transcripcion') && !empty($request->transcripcion)) {

                // Guardamos la pregunta/mensaje del paciente
                $transcripcion = ConsultaTranscripcion::create([
                    'consulta_id' => $consulta->id,
                    'consulta_folio' => $consulta->folio,
                    'mensaje' => $request->transcripcion,
                    'tipo_usuario' => 'paciente'
                ]);

                // Llamada al servicio de IA
                $iaData = $this->iaClinicaService->analizarTranscripcion($request->transcripcion, $consulta);

                // Guardamos la respuesta de la IA en la MISMA fila, para el historial clínico
                if ($iaData) {
                    $recomendaciones = is_array($iaData['recomendaciones'] ?? null)
                        ? implode(' ', $iaData['recomendaciones'])
                        : '';

                    $transcripcion->update([
                        'analizado_ia' => 1,
                        'observaciones_ia' => trim(
                            ($iaData['diagnostico_probable'] ?? '') . ' — ' . $recomendaciones
                        )
                    ]);
                }
            }

            // 4. RESPUESTA FINAL COMPLETA
            // Retornamos todos los datos necesarios para que el frontend no pierda el contexto
            return response()->json([
                'success' => true,
                'consulta_id' => $consulta->id,
                'consulta_folio' => $consulta->folio,
                'session_uuid' => $consulta->session_uuid,
                'ia_data' => $iaData // Si no hubo transcripción, será null, el frontend lo manejará
            ]);

        } catch(\Exception $e) {
            // Registro de error para depuración
            \Log::error("Error en ConsultaIAController: " . $e->getMessage());
            
            return response()->json([
                'success' => false, 
                'error' => 'Ocurrió un error al procesar la consulta.'
            ], 500);
        }
    }

    /**
     * Recibe un archivo adjunto (pdf, word o imagen) para una consulta
     * ya existente, extrae su texto (OCR en caso de imagen), lo guarda
     * como una transcripción más (para que aparezca en el historial
     * clínico igual que un mensaje de voz/texto) y lo manda al mismo
     * pipeline de análisis IA que usa analizarTranscripcion().
     */
    public function subirArchivo(Request $request)
    {
        try {
            $validated = $request->validate([
                'consulta_id' => 'required|integer|exists:consultas,id',
                'archivo'     => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:15360', // 15MB
            ]);

            $consulta = Consulta::find($validated['consulta_id']);
            if (!$consulta) {
                return response()->json(['success' => false, 'error' => 'Consulta no encontrada'], 404);
            }

            $archivo = $request->file('archivo');
            $nombreOriginal = $archivo->getClientOriginalName();
            $mime = $archivo->getMimeType();

            // Guardamos el archivo físico usando el disco 'local'.
            // IMPORTANTE: no construir la ruta a mano con storage_path('app/'...),
            // porque en Laravel 11 el disco 'local' guarda por defecto en
            // storage/app/private (no storage/app). Storage::path() siempre
            // devuelve la ruta absoluta real sin importar dónde esté configurado.
            $rutaGuardada = $archivo->store('adjuntos_clinicos', 'local');
            $rutaCompleta = \Illuminate\Support\Facades\Storage::disk('local')->path($rutaGuardada);

            // Extraemos el texto (pdf/word directo, imagen vía OCR)
            $textoExtraido = $this->iaClinicaService->extraerTextoDeArchivo($rutaCompleta, $mime);
                
            \Log::info('Texto extraído', [
                    'texto' => $textoExtraido
                ]);
            if (empty($textoExtraido)) {
                return response()->json([
                    'success' => false,
                    'error'   => 'No se pudo extraer texto legible del archivo. Intenta con otro archivo o mejor calidad de imagen.'
                ], 422);
            }

            // Guardamos como una transcripción más, dejando rastro de que vino de un archivo
            $transcripcion = ConsultaTranscripcion::create([
                'consulta_id'    => $consulta->id,
                'consulta_folio' => $consulta->folio,
                'mensaje'        => "[Archivo adjunto: {$nombreOriginal}]\n\n" . $textoExtraido,
                'tipo_usuario'   => 'paciente',
            ]);

            // Mismo pipeline de análisis que ya usas con texto hablado
            $iaData = $this->iaClinicaService->analizarTranscripcion($textoExtraido, $consulta);

            if ($iaData) {
                $recomendaciones = is_array($iaData['recomendaciones'] ?? null)
                    ? implode(' ', $iaData['recomendaciones'])
                    : '';

                $transcripcion->update([
                    'analizado_ia'     => 1,
                    'observaciones_ia' => trim(
                        ($iaData['diagnostico_probable'] ?? '') . ' — ' . $recomendaciones
                    )
                ]);
            }

            return response()->json([
                'success'         => true,
                'consulta_id'     => $consulta->id,
                'consulta_folio'  => $consulta->folio,
                'archivo_nombre'  => $nombreOriginal,
                'texto_extraido'  => $textoExtraido,
                'ia_data'         => $iaData,
            ]);

        } catch (\Throwable $e) {
            // \Throwable para atrapar también "Class not found" si falta
            // instalar algún paquete de Composer.
            \Log::error("Error en subirArchivo: " . $e->getMessage(), [
                'clase_error' => get_class($e),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'error'   => 'Ocurrió un error al procesar el archivo.'
            ], 500);
        }
    }

    /**
     * Ejecuta el triage clínico completo con la IA (fases 1-8) y, según
     * el "tipo" que decida:
     *
     * - receta_inteligente: busca medicamentos reales en el inventario
     *   usando las palabras clave sugeridas por la IA. Si no hay
     *   coincidencias, devuelve los "medicamentos_sugeridos" genéricos
     *   de la IA, marcados como NO VERIFICADOS.
     *
     * - derivacion: no busca medicamentos; devuelve la especialidad y
     *   el motivo de derivación sugeridos por la IA.
     */
    public function recetaInteligente(Request $request)
    {
        try {
            $validated = $request->validate([
                'consulta_id' => 'nullable|integer|exists:consultas,id',
                'sintomas'    => 'required|array|min:1',
                'sintomas.*'  => 'string'
            ]);

            $sintomas = $validated['sintomas'];

            // Catálogo real de especialidades activas, para anclar a la IA
            // y evitar que invente nombres que no existen en el sistema.
            $nombresEspecialidades = Specialty::where('estado', 'Activo')
                ->orderBy('nombre')
                ->pluck('nombre')
                ->toArray();

            // 1. La IA hace el triage completo y decide tipo: receta_inteligente | derivacion
            $respuestaIA = $this->iaClinicaService->sugerirMedicamentoLibre($sintomas, $nombresEspecialidades);

            if (!$respuestaIA) {
                return response()->json([
                    'success' => false,
                    'error'   => 'No se pudo obtener respuesta de la IA.'
                ], 500);
            }

            // 2. Si la IA decide DERIVACIÓN (triage naranja/rojo, o amarillo con
            //    signos de alarma), no buscamos medicamentos.
            if (($respuestaIA['tipo'] ?? null) === 'derivacion') {
                return response()->json([
                    'success'                  => true,
                    'tipo'                     => 'derivacion',
                    'triage'                   => $respuestaIA['triage'] ?? null,
                    'diagnosticos_probables'   => $respuestaIA['diagnosticos_probables'] ?? [],
                    'especialidad_sugerida_ia' => $respuestaIA['especialidad'] ?? null,
                    'motivo_derivacion'        => $respuestaIA['motivo_derivacion'] ?? null,
                    'requiere_urgencias'       => $respuestaIA['requiere_urgencias'] ?? false,
                    'justificacion'            => $respuestaIA['justificacion'] ?? null,
                ]);
            }

            // 3. tipo === 'receta_inteligente': buscamos en el inventario real
            //    usando las palabras clave que sugirió la IA (Fase 6)
            $palabras = $respuestaIA['palabras_clave_busqueda'] ?? [];

            $medicamentos = collect();

            if (!empty($palabras)) {
                $medicamentos = Medicamento::where('activo', 1)
                    ->where(function ($query) use ($palabras) {
                        foreach ($palabras as $palabra) {
                            $query->orWhere('nombre', 'LIKE', "%$palabra%")
                                  ->orWhere('nombre_generico', 'LIKE', "%$palabra%")
                                  ->orWhere('descripcion', 'LIKE', "%$palabra%")
                                  ->orWhere('indicaciones', 'LIKE', "%$palabra%");
                        }
                    })
                    ->select([
                        'id',
                        'codigo',
                        'nombre',
                        'nombre_generico',
                        'presentacion',
                        'concentracion',
                        'via_administracion',
                        'indicaciones',
                        'contraindicaciones',
                        'efectos_secundarios',
                        'requiere_receta'
                    ])
                    ->limit(10)
                    ->get();
            }

            // 4. Si el inventario no tiene coincidencias, usamos las
            //    sugerencias genéricas que ya trae la respuesta de la IA
            //    (Fase 6), marcadas explícitamente como no verificadas.
            $medicamentosSugeridosIA = $medicamentos->isEmpty()
                ? ($respuestaIA['medicamentos_sugeridos'] ?? [])
                : [];

            return response()->json([
                'success'                   => true,
                'tipo'                      => 'receta_inteligente',
                'triage'                    => $respuestaIA['triage'] ?? null,
                'diagnosticos_probables'    => $respuestaIA['diagnosticos_probables'] ?? [],
                'medicamentos'              => $medicamentos,
                'medicamentos_sugeridos_ia' => $medicamentosSugeridosIA, // no verificados
                'justificacion'             => $respuestaIA['justificacion'] ?? null,
            ]);

        } catch (\Exception $e) {
            \Log::error("Error en recetaInteligente: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error'   => 'No se pudo consultar la base de medicamentos.'
            ], 500);
        }
    }

    /**
     * Sugiere una especialidad médica a la que derivar al paciente.
     *
     * Fuente 1 (principal): la IA hace el triage completo (fases 1-8),
     * recibiendo el catálogo REAL de especialidades activas como parte
     * del prompt (para no inventar nombres), y devuelve una especialidad
     * sugerida en base a signos de alarma, región anatómica, etc.
     *
     * Fuente 2 (respaldo): si la IA no responde, falla, su sugerencia
     * no coincide con ninguna especialidad activa, o el match encontrado
     * no es clínicamente coherente con los síntomas, se usa el mapa de
     * palabras clave como respaldo determinístico.
     *
     * NOTA: las descripciones de "especialidades" en la BD son
     * institucionales (no listas de síntomas), por lo que el respaldo
     * usa un mapa de palabras clave en vez de un LIKE directo contra
     * la columna descripcion.
     */
    public function derivacionInteligente(Request $request)
    {
        try {
            $validated = $request->validate([
                'consulta_id' => 'nullable|integer|exists:consultas,id',
                'sintomas'    => 'required|array|min:1',
                'sintomas.*'  => 'string'
            ]);

            $sintomas = $validated['sintomas'];

            // Listado completo activo, para poblar el <select> del frontend
            // y para anclar a la IA (evita que invente especialidades).
            $todasLasEspecialidades = Specialty::where('estado', 'Activo')
                ->orderBy('nombre')
                ->get(['id', 'nombre']);

            $nombresEspecialidades = $todasLasEspecialidades->pluck('nombre')->toArray();

            // 1. FUENTE PRINCIPAL: triage de la IA, ya anclado al catálogo real
            $respuestaIA = $this->iaClinicaService->sugerirMedicamentoLibre($sintomas, $nombresEspecialidades);

            $triage = null;
            $diagnosticosProbables = [];
            $motivoDerivacionIA = null;
            $requiereUrgencias = false;
            $especialidad = null;
            $fuente = null;

            if ($respuestaIA && ($respuestaIA['tipo'] ?? null) === 'derivacion') {
                $triage = $respuestaIA['triage'] ?? null;
                $diagnosticosProbables = $respuestaIA['diagnosticos_probables'] ?? [];
                $motivoDerivacionIA = $respuestaIA['motivo_derivacion'] ?? null;
                $requiereUrgencias = $respuestaIA['requiere_urgencias'] ?? false;

                $nombreSugeridoIA = trim((string) ($respuestaIA['especialidad'] ?? ''));

                // Solo intentamos usar la sugerencia de la IA si viene un
                // nombre real y no vacío. Antes esto permitía que un string
                // vacío generara un LIKE '%%' que hacía match con CUALQUIER
                // especialidad de la tabla (normalmente la primera por id),
                // causando derivaciones absurdas sin relación con los síntomas.
                if ($nombreSugeridoIA !== '') {
                    // Preferimos coincidencia EXACTA (la IA fue instruida a
                    // copiar el nombre tal cual del catálogo). Esto evita
                    // falsos positivos de un LIKE parcial.
                    $especialidad = Specialty::where('estado', 'Activo')
                        ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombreSugeridoIA)])
                        ->first();

                    // Si no hubo coincidencia exacta, probamos un LIKE
                    // acotado (por si la IA agregó texto extra), escapando
                    // los comodines % y _ para no hacer matches accidentales.
                    if (!$especialidad) {
                        $comodinEscapado = str_replace(['%', '_'], ['\\%', '\\_'], $nombreSugeridoIA);
                        $especialidad = Specialty::where('estado', 'Activo')
                            ->where('nombre', 'LIKE', '%' . $comodinEscapado . '%')
                            ->first();
                    }

                    if ($especialidad) {
                        $fuente = 'ia_triage';
                    }
                }
            }

            // 2. RESPALDO: mapa de palabras clave, solo si la IA no aplica,
            //    no respondió, o su sugerencia no coincidió con la BD.
            if (!$especialidad) {
                $especialidad = $this->buscarEspecialidadPorMapaDeRespaldo($sintomas);
                $fuente = 'mapa_respaldo';
            }

            return response()->json([
                'success'                => true,
                'especialidad_sugerida'  => $especialidad,
                'especialidades'         => $todasLasEspecialidades,
                'fuente'                 => $fuente, // 'ia_triage' | 'mapa_respaldo'
                'triage'                 => $triage,
                'diagnosticos_probables' => $diagnosticosProbables,
                'motivo_derivacion_ia'   => $motivoDerivacionIA,
                'requiere_urgencias'     => $requiereUrgencias,
            ]);

        } catch (\Exception $e) {
            \Log::error("Error en derivacionInteligente: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'No se pudo determinar la especialidad sugerida.'
            ], 500);
        }
    }

    /**
     * Respaldo determinístico (sin IA) para sugerir especialidad,
     * usado cuando la IA no responde o su sugerencia no coincide con
     * ninguna especialidad activa en la base de datos.
     */
    private function buscarEspecialidadPorMapaDeRespaldo(array $sintomas)
    {
            // Texto unificado en minúsculas para buscar coincidencias
            $textoSintomas = mb_strtolower(implode(' ', $sintomas));

            // Mapa de palabras clave -> nombre de especialidad
            $mapaEspecialidades = [
                'diente'       => 'Dentista',
                'muela'        => 'Dentista',
                'encía'        => 'Dentista',
                'encia'        => 'Dentista',
                'boca'         => 'Dentista',
                'dental'       => 'Dentista',

                'estómago'     => 'Gastroenterología',
                'estomago'     => 'Gastroenterología',
                'digestivo'    => 'Gastroenterología',
                'digestión'    => 'Gastroenterología',
                'digestion'    => 'Gastroenterología',
                'hígado'       => 'Gastroenterología',
                'higado'       => 'Gastroenterología',
                'intestino'    => 'Gastroenterología',
                'colon'        => 'Gastroenterología',
                'colitis'      => 'Gastroenterología',
                'gastritis'    => 'Gastroenterología',
                'náusea'       => 'Gastroenterología',
                'nausea'       => 'Gastroenterología',
                'vómito'       => 'Gastroenterología',
                'vomito'       => 'Gastroenterología',
                'diarrea'      => 'Gastroenterología',
                'estreñimiento'=> 'Gastroenterología',
                'estrenimiento'=> 'Gastroenterología',
                'abdominal'    => 'Gastroenterología',
                'abdomen'      => 'Gastroenterología',
                'reflujo'      => 'Gastroenterología',
                'acidez'       => 'Gastroenterología',

                'alimentación' => 'Nutricion',
                'alimentacion' => 'Nutricion',
                'peso'         => 'Nutricion',
                'dieta'        => 'Nutricion',

                'niño'         => 'Pediatría',
                'nino'         => 'Pediatría',
                'bebé'         => 'Pediatría',
                'bebe'         => 'Pediatría',
                'infante'      => 'Pediatría',

                'ansiedad'     => 'Psicología',
                'depresión'    => 'Psicología',
                'depresion'    => 'Psicología',
                'estrés'       => 'Psicología',
                'estres'       => 'Psicología',
                'emocional'    => 'Psicología',

                'hueso'        => 'Traumatologia',
                'fractura'     => 'Traumatologia',
                'articulación' => 'Traumatologia',
                'articulacion' => 'Traumatologia',
                'músculo'      => 'Traumatologia',
                'musculo'      => 'Traumatologia',
                'esguince'     => 'Traumatologia',
            ];

            $especialidadSugerida = null;

            foreach ($mapaEspecialidades as $palabraClave => $nombreEspecialidad) {
                if (str_contains($textoSintomas, $palabraClave)) {
                    $especialidadSugerida = $nombreEspecialidad;
                    break;
                }
            }

            // FALLBACK: si no hubo match específico, derivamos a Medicina General
            if (!$especialidadSugerida) {
                $especialidadSugerida = 'Medicina general';
            }

            return Specialty::where('estado', 'Activo')
                ->where('nombre', $especialidadSugerida)
                ->first();
    }

    /**
     * Devuelve el historial de intercambios (pregunta del paciente +
     * respuesta de la IA) de una consulta, para el timeline de HistorialClinico.
     */
    public function historialClinico(Request $request)
    {
        try {
            $validated = $request->validate([
                'consulta_id' => 'required|integer|exists:consultas,id'
            ]);

            $transcripciones = ConsultaTranscripcion::where('consulta_id', $validated['consulta_id'])
                ->orderBy('created_at', 'asc')
                ->get(['id', 'mensaje', 'tipo_usuario', 'analizado_ia', 'observaciones_ia', 'created_at']);

            return response()->json([
                'success' => true,
                'historial' => $transcripciones
            ]);

        } catch (\Exception $e) {
            \Log::error("Error en historialClinico: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'No se pudo obtener el historial clínico.'
            ], 500);
        }
    }
}