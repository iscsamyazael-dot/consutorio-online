<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\Consulta;
use App\Models\ConsultaTranscripcion;
use App\Models\Medicamento;
use App\Models\Specialty;
use App\Models\ArchivoClinico;
use App\Models\EvaluacionIA;
use App\Models\NotaPsoapp;
use App\Models\Receta;
use App\Models\Derivacion;
use Barryvdh\DomPDF\Facade\Pdf;

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
                    // NOTA: 'estado' y 'estado_consulta' son columnas
                    // DISTINTAS en la tabla consultas. El flujo real del
                    // sistema (Home.vue, finalizarConsulta, etc.) usa
                    // estado_consulta, con default 'en_proceso'. Se deja
                    // explícito aquí para que no dependa del default de
                    // la BD y quede claro cuál es la columna que importa.
                    'estado_consulta' => 'en_proceso',
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

            // 2B. BLOQUEAR SI YA FUE FINALIZADA
            // Una vez que el médico presiona "Finalizar" (finalizarConsulta()),
            // el diagnóstico de esta consulta queda cerrado. No se aceptan
            // más transcripciones para que no se siga modificando.
            //
            // FIX: antes se comparaba contra la columna 'estado' (default
            // 'pendiente', que nunca se actualiza en ningún flujo real).
            // La columna que realmente se marca como 'finalizada' es
            // 'estado_consulta' (ver finalizarConsulta() más abajo), así
            // que el bloqueo debe leer esa misma columna.
            if ($consulta->estado_consulta === 'finalizada') {
                return response()->json([
                    'success' => false,
                    'error'   => 'Esta consulta ya fue finalizada. Inicia una nueva consulta para continuar.'
                ], 409);
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

                // Contexto de continuidad: mensajes anteriores de ESTA misma
                // consulta (ya guardados y analizados) y la última nota PSOAPP
                // registrada. Sin esto, cada mensaje se analizaba de forma
                // aislada y la IA podía "cambiar de opinión" de un mensaje a
                // otro en vez de refinar el mismo diagnóstico.
                $historial = ConsultaTranscripcion::where('consulta_id', $consulta->id)
                    ->where('id', '!=', $transcripcion->id)
                    ->where('analizado_ia', 1)
                    ->orderBy('created_at', 'asc')
                    ->pluck('mensaje')
                    ->toArray();

                $ultimaNota = NotaPsoapp::where('consulta_id', $consulta->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                // Llamada al servicio de IA, ya con el contexto de la consulta
                $iaData = $this->iaClinicaService->analizarTranscripcion(
                    $request->transcripcion,
                    $consulta,
                    $historial,
                    $ultimaNota
                );

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
     * Marca una consulta como finalizada (botón "Finalizar" en
     * ConsultaTiempoReal.vue). A partir de este punto:
     *
     * - store() y subirArchivo() rechazan cualquier transcripción/archivo
     *   nuevo para esta consulta (HTTP 409).
     * - El diagnóstico/evaluación IA de esta consulta queda tal cual quedó,
     *   sin más actualizaciones.
     *
     * IMPORTANTE: esto actualiza ÚNICAMENTE la columna estado_consulta
     * de la tabla `consultas`. NO se toca la tabla `triage` (columna
     * `estado`, con valores 'leve'/'grave'): ese campo es el nivel de
     * severidad con el que llegó el paciente y debe conservarse como
     * parte del historial clínico, sin importar que la consulta ya
     * haya sido cerrada.
     *
     * No borra ni modifica nada de lo ya guardado; solo cierra la puerta a
     * seguir enviando mensajes.
     */
    public function finalizarConsulta($consultaId)
    {
        try {
            $consulta = Consulta::find($consultaId);
            if (!$consulta) {
                return response()->json(['success' => false, 'error' => 'Consulta no encontrada'], 404);
            }

            // FIX: antes se leía/escribía la columna 'estado' (default
            // 'pendiente', sin uso real en el sistema). La columna que
            // consulta el resto de la app (y la que se ve en phpMyAdmin
            // con los valores 'en_proceso'/'finalizada') es
            // 'estado_consulta'.
            if ($consulta->estado_consulta === 'finalizada') {
                return response()->json([
                    'success' => true,
                    'ya_estaba_finalizada' => true,
                    'estado_consulta' => $consulta->estado_consulta,
                ]);
            }

            $consulta->update(['estado_consulta' => 'finalizada']);

            return response()->json([
                'success' => true,
                'estado_consulta' => $consulta->estado_consulta,
            ]);

        } catch (\Exception $e) {
            \Log::error("Error en finalizarConsulta: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error'   => 'No se pudo finalizar la consulta.'
            ], 500);
        }
    }

    /**
     * Recibe un archivo adjunto (pdf, word o imagen) para una consulta
     * ya existente, extrae su texto (OCR en caso de imagen), lo guarda
     * como una transcripción más (para que aparezca en el historial
     * clínico igual que un mensaje de voz/texto) y lo manda al mismo
     * pipeline de análisis IA que usa analizarTranscripcion().
     *
     * Además registra el archivo en archivos_clinicos para que
     * ArchivosClinicos.vue pueda listarlo y permitir abrirlo.
     *
     * NOTA (guardado físico): el archivo se guarda directamente en
     * public/archivos_clinicos, igual que el flujo manual de
     * ArchivosClinicosController@archivoclinico, en vez del disco
     * privado 'local' que se usaba antes. Esto permite verlo como
     * carpeta física dentro de public/ y reutiliza el mismo lugar de
     * almacenamiento para ambos flujos de subida.
     *
     * NOTA (nombre del archivo): antes el nombre empezaba con
     * "consulta_{id}_..." usando el ID interno de la consulta. Ahora
     * empieza con "consultaIA-{numero de 4 dígitos}-...", usando el
     * mismo consecutivo con el que se arma el folio de la consulta
     * (CONS-AAAA-NNNN), para que el nombre del archivo sea legible y
     * fácil de relacionar con la consulta a simple vista.
     *
     * FIX (rendimiento): antes esta función llamaba a
     * IAClinicaService::analizarTranscripcion() DOS VECES para archivos
     * de imagen: una vez dentro del bloque de Gemini Vision (cuyo
     * resultado se descartaba por completo, solo se usaba para obtener
     * $textoExtraido) y otra vez más abajo, en el "mismo pipeline de
     * análisis que ya usas con texto hablado". Cada llamada a
     * analizarTranscripcion() dispara internamente una llamada pesada a
     * DeepSeek (~60-90s), así que el archivo se analizaba dos veces sin
     * necesidad, duplicando el tiempo total de espera del usuario
     * (~3 minutos). Ahora solo hay UNA llamada a analizarTranscripcion(),
     * hecha después de resolver el texto final (ya sea de Gemini Vision
     * o de extracción directa) y de guardar la transcripción.
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

            // Mismo bloqueo que en store(): si la consulta ya fue
            // finalizada, no se procesan más archivos/análisis.
            //
            // FIX: misma corrección de columna que en store() y
            // finalizarConsulta() — se lee estado_consulta, no estado.
            if ($consulta->estado_consulta === 'finalizada') {
                return response()->json([
                    'success' => false,
                    'error'   => 'Esta consulta ya fue finalizada. Inicia una nueva consulta para continuar.'
                ], 409);
            }

            $archivo = $request->file('archivo');
            $nombreOriginal = $archivo->getClientOriginalName();
            $mime = $archivo->getMimeType();

            // Guardamos el archivo físico en public/archivos_clinicos, igual
            // que el flujo manual de ArchivosClinicos.vue
            // (ArchivosClinicosController@archivoclinico). Se arma un nombre
            // único para no pisar archivos existentes.
            //
            // Formato: consultaIA-0007-{timestamp}_{random}.ext
            // El "0007" sale del folio de la consulta (CONS-2026-0007),
            // tomando solo el consecutivo de 4 dígitos, para que el
            // nombre del archivo quede alineado con el folio que ya ve
            // el médico en pantalla.
           $codigoPaciente = $consulta->paciente->paciente_id ?? ('CONSULTA-' . $consulta->id);

            $nombreArchivo = $codigoPaciente . '_' . time() . '_' . Str::random(6)
                 . '.' . $archivo->getClientOriginalExtension();

            $archivo->move(public_path('archivos_clinicos'), $nombreArchivo);

            $rutaGuardada = 'archivos_clinicos/' . $nombreArchivo; // relativo a public/, se guarda en BD
            $rutaCompleta = public_path($rutaGuardada); // ruta absoluta, para el OCR/extracción de texto

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

            // --- AQUÍ DECIDIMOS EL CAMINO SEGÚN EL TIPO DE ARCHIVO ---
            // (Solo resolvemos el texto final. El análisis con IA se hace
            // UNA SOLA VEZ, más abajo, ya con la transcripción guardada.)
            if (trim($textoExtraido) === '[DOCUMENTO_ESCANEADO_O_IMAGEN]') {
                // Llamamos a Gemini para que lea la imagen usando la clave de API que generaste
                $textoVisionGemini = $this->iaClinicaService->analizarArchivoConVisionGemini(
                    $rutaCompleta, 
                    $mime, 
                    "Analiza esta imagen o estudio médico adjunto para la nota clínica."
                );

                if (empty($textoVisionGemini)) {
                    return response()->json([
                        'success' => false,
                        'error'   => 'No se pudo analizar la imagen con IA. Intenta nuevamente con otra imagen o mejor calidad.'
                    ], 422);
                }

                // Este es el texto final: lo que se guarda en la transcripción
                // y lo que se manda al pipeline de análisis (más abajo).
                $textoExtraido = "[Documento visual analizado por Gemini]\n" . $textoVisionGemini;
            }
            // Si NO es imagen (PDF con texto normal o Word), $textoExtraido ya
            // viene listo desde extraerTextoDeArchivo() y no se toca aquí.

            // Guardamos como una transcripción más, dejando rastro de que vino de un archivo
            $transcripcion = ConsultaTranscripcion::create([
                'consulta_id'    => $consulta->id,
                'consulta_folio' => $consulta->folio,
                'mensaje'        => "[Archivo adjunto: {$nombreOriginal}]\n\n" . $textoExtraido,
                'tipo_usuario'   => 'paciente',
            ]);

            // Contexto de continuidad: mensajes anteriores de ESTA misma
            // consulta (ya guardados y analizados) y la última nota PSOAPP
            // registrada.
            $historial = ConsultaTranscripcion::where('consulta_id', $consulta->id)
                ->where('id', '!=', $transcripcion->id)
                ->where('analizado_ia', 1)
                ->orderBy('created_at', 'asc')
                ->pluck('mensaje')
                ->toArray();

            $ultimaNota = NotaPsoapp::where('consulta_id', $consulta->id)
                ->orderBy('created_at', 'desc')
                ->first();

            // ÚNICA llamada al pipeline de análisis IA (antes se llamaba
            // dos veces: una dentro del bloque de imagen, cuyo resultado se
            // descartaba, y otra aquí).
            $iaData = $this->iaClinicaService->analizarTranscripcion(
                $textoExtraido,
                $consulta,
                $historial,
                $ultimaNota
            );

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

            // Registro del archivo para que aparezca en ArchivosClinicos.vue.
            // Usamos $consulta->paciente_id (no el request) porque ya sabemos
            // con certeza a qué paciente pertenece esta consulta.
            $archivoClinico = ArchivoClinico::create([
                'paciente_id'   => $consulta->paciente_id,
                'consulta_id'   => $consulta->id,
                'tipo_archivo'  => $this->tipoArchivoDesdeMime($mime),
                'archivo_url'   => $rutaGuardada,
                'descripcion'   => $nombreOriginal,
                'analisis_ia'   => $textoExtraido,
                'procesado_ia'  => $iaData ? 1 : 0,
                'Estado'        => $iaData ? 'Completado' : 'Pendiente',
            ]);

            return response()->json([
                'success'         => true,
                'consulta_id'     => $consulta->id,
                'consulta_folio'  => $consulta->folio,
                'archivo_id'      => $archivoClinico->id,
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
     * Saca el consecutivo de 4 dígitos del folio de la consulta
     * (ej. de "CONS-2026-0007" devuelve "0007"), para usarlo en el
     * nombre del archivo subido (consultaIA-0007-...).
     *
     * Si por alguna razón la consulta no tiene folio o no sigue el
     * formato esperado, usamos el id de la consulta como respaldo,
     * para que subirArchivo() nunca falle por esto.
     */
    private function extraerNumeroDeFolio(?string $folio, int $consultaId): string
    {
        if ($folio && preg_match('/(\d{4})$/', $folio, $coincidencia)) {
            return $coincidencia[1];
        }

        return str_pad((string) $consultaId, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Determina un tipo de archivo simple ('pdf' | 'word' | 'imagen' | 'otro')
     * a partir del mime real detectado por Laravel, para que el frontend
     * pueda mostrar el ícono correcto sin tener que inspeccionar extensiones.
     */
    private function tipoArchivoDesdeMime(string $mime): string
    {
        if ($mime === 'application/pdf') {
            return 'pdf';
        }

        if (in_array($mime, [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ])) {
            return 'word';
        }

        if (in_array($mime, ['image/jpeg', 'image/png'])) {
            return 'imagen';
        }

        return 'otro';
    }

    /**
     * Lista los archivos clínicos de una consulta, para el componente
     * ArchivosClinicos.vue. Devuelve ya armada la URL de descarga (que
     * pasa por descargarArchivo(), para mantener un único punto de
     * entrada aunque el archivo ya viva en un disco público).
     */
    public function listarArchivos($consultaId)
    {
        try {
            $archivos = ArchivoClinico::where('consulta_id', $consultaId)
                ->orderBy('created_at', 'desc')
                ->get(['id', 'descripcion', 'tipo_archivo', 'Estado', 'created_at']);

            $archivos = $archivos->map(function ($archivo) {
                return [
                    'id'           => $archivo->id,
                    'nombre'       => $archivo->descripcion,
                    'tipo'         => $archivo->tipo_archivo,
                    'estado'       => $archivo->Estado,
                    'fecha'        => $archivo->created_at,
                    'url_descarga' => route('consultaIA.descargarArchivo', $archivo->id),
                ];
            });

            return response()->json([
                'success'  => true,
                'archivos' => $archivos
            ]);

        } catch (\Exception $e) {
            \Log::error("Error en listarArchivos: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error'   => 'No se pudo obtener la lista de archivos.'
            ], 500);
        }
    }

    /**
     * Sirve el archivo físico para abrir/descargar desde el navegador.
     *
     * Los archivos NUEVOS viven en public/archivos_clinicos y se sirven
     * directo de disco con response()->download().
     *
     * Los archivos ANTIGUOS (subidos antes de este cambio) siguen viviendo
     * en el disco privado 'local' (storage/app/private/adjuntos_clinicos),
     * así que se mantiene ese camino como respaldo para no romper enlaces
     * ya guardados en la base de datos.
     */
    public function descargarArchivo($id)
    {
        $archivo = ArchivoClinico::find($id);

        if (!$archivo || !$archivo->archivo_url) {
            abort(404, 'Archivo no encontrado');
        }

        // Archivos nuevos: viven en public/archivos_clinicos
        $rutaPublica = public_path($archivo->archivo_url);
        if (file_exists($rutaPublica)) {
            return response()->download($rutaPublica, $archivo->descripcion);
        }

        // Compatibilidad con archivos antiguos guardados en el disco 'local'
        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($archivo->archivo_url)) {
            return \Illuminate\Support\Facades\Storage::disk('local')->response(
                $archivo->archivo_url,
                $archivo->descripcion
            );
        }

        abort(404, 'El archivo ya no existe en el servidor');
    }

    /**
     * Mapa de conversión entre las llaves internas del acordeón de
     * NotaPSOAPP.vue (P1, S, O, A, P2, P3) y las columnas reales de la
     * tabla notas_psoapp. Se usa al recibir el guardado manual/edición
     * del médico, en sentido inverso al mapeo que ya existe en el
     * frontend (NotaPSOAPP.vue -> MAPA_BACKEND_A_FRONTEND).
     */
    private function mapaFrontendABackendPsoapp(): array
    {
        return [
            'P1' => 'presentacion',
            'S'  => 'subjetivo',
            'O'  => 'objetivo',
            'A'  => 'analisis',
            'P2' => 'plan',
            'P3' => 'pronostico',
        ];
    }

    /**
     * Guarda la nota PSOAPP como 'borrador' o 'final', ya sea que el
     * contenido venga de la IA o de ediciones manuales del médico
     * (NotaPSOAPP.vue -> guardar()).
     *
     * Actualiza la nota más reciente de la consulta si ya existe (por
     * ejemplo, la que se creó automáticamente en
     * IAClinicaService::analizarTranscripcion), o crea una nueva si la
     * consulta todavía no tiene ninguna (nota 100% manual, sin pasar
     * por IA).
     */
    public function guardarPsoapp(Request $request, $consultaId)
    {
        try {
            $consulta = Consulta::find($consultaId);
            if (!$consulta) {
                return response()->json(['success' => false, 'error' => 'Consulta no encontrada'], 404);
            }

            $validated = $request->validate([
                'estado'              => 'required|in:borrador,final',
                'contenido'           => 'required|array',
                'contenido.P1.texto'  => 'nullable|string',
                'contenido.S.texto'   => 'nullable|string',
                'contenido.O.texto'   => 'nullable|string',
                'contenido.A.texto'   => 'nullable|string',
                'contenido.P2.texto'  => 'nullable|string',
                'contenido.P3.texto'  => 'nullable|string',
            ]);

            $mapa = $this->mapaFrontendABackendPsoapp();
            $datosBackend = [];

            foreach ($mapa as $claveFrontend => $claveBackend) {
                $datosBackend[$claveBackend] = $validated['contenido'][$claveFrontend]['texto'] ?? null;
            }

            $datosBackend['estado'] = $validated['estado'];

            // Reutilizamos la nota más reciente de la consulta si existe
            // (la que ya crea IAClinicaService al analizar la transcripción),
            // en vez de duplicar registros cada vez que el médico guarda.
            $nota = NotaPsoapp::where('consulta_id', $consulta->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($nota) {
                $nota->update($datosBackend);
            } else {
                $nota = NotaPsoapp::create(array_merge($datosBackend, [
                    'consulta_id'    => $consulta->id,
                    'consulta_folio' => $consulta->folio,
                    'session_uuid'   => $consulta->session_uuid,
                ]));
            }

            return response()->json([
                'success' => true,
                'nota_id' => $nota->id,
                'estado'  => $nota->estado,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Datos inválidos.',
                'detalle' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            \Log::error("Error en guardarPsoapp: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error'   => 'No se pudo guardar la nota PSOAPP.'
            ], 500);
        }
    }

    /**
     * Guarda (crea o actualiza) la receta de medicamentos de una
     * consulta, capturada en RecetaInteligente.vue -> guardarReceta().
     *
     * Usa updateOrCreate por consulta_id para no duplicar registros
     * cada vez que el médico agrega/edita medicamentos y vuelve a
     * guardar; la receta más reciente de la consulta es la que luego
     * lee generarPdf('receta').
     */
    public function guardarReceta(Request $request, $consultaId)
    {
        try {
            $consulta = Consulta::find($consultaId);
            if (!$consulta) {
                return response()->json(['success' => false, 'error' => 'Consulta no encontrada'], 404);
            }

            $validated = $request->validate([
                'medicamentos'               => 'required|array|min:1',
                'medicamentos.*.nombre'      => 'required|string',
                'medicamentos.*.dosis'       => 'nullable|string',
                'medicamentos.*.frecuencia'  => 'nullable|string',
                'medicamentos.*.duracion'    => 'nullable|string',
                'medicamentos.*.instrucciones' => 'nullable|string',
                // Recomendación general de la receta (cómo tomar los
                // medicamentos), capturada en el textarea de
                // RecetaInteligente.vue. Se guarda en la columna real
                // de la tabla: indicaciones_generales.
                'recomendacion'              => 'nullable|string',
            ]);

            $receta = Receta::updateOrCreate(
                ['consulta_id' => $consulta->id],
                [
                    'medicamentos'           => $validated['medicamentos'],
                    'indicaciones_generales' => $validated['recomendacion'] ?? null,
                    'estado'                 => 'borrador',
                ]
            );

            return response()->json([
                'success'   => true,
                'receta_id' => $receta->id,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Datos inválidos.',
                'detalle' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            \Log::error("Error en guardarReceta: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error'   => 'No se pudo guardar la receta.'
            ], 500);
        }
    }

    /**
     * Guarda (crea o actualiza) la derivación a especialidad de una
     * consulta, capturada en Derivacion.vue -> derivarPaciente().
     *
     * Usa updateOrCreate por consulta_id, igual que guardarReceta(),
     * para no duplicar registros si el médico cambia la especialidad
     * y vuelve a derivar sobre la misma consulta.
     */
    public function guardarDerivacion(Request $request, $consultaId)
    {
        try {
            $consulta = Consulta::find($consultaId);
            if (!$consulta) {
                return response()->json(['success' => false, 'error' => 'Consulta no encontrada'], 404);
            }

            $validated = $request->validate([
                'especialidad_id' => 'required|integer|exists:especialidades,id',
                'hospital'        => 'nullable|string|max:255',
                'motivo'          => 'nullable|string',
            ]);

            $derivacion = Derivacion::updateOrCreate(
                ['consulta_id' => $consulta->id],
                [
                    'especialidad_id' => $validated['especialidad_id'],
                    'hospital'        => $validated['hospital'] ?? null,
                    'motivo'          => $validated['motivo'] ?? null,
                    'estado'          => 'pendiente',
                ]
            );

            return response()->json([
                'success'    => true,
                'derivacion' => $derivacion->load('especialidad'),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Datos inválidos.',
                'detalle' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            \Log::error("Error en guardarDerivacion: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error'   => 'No se pudo guardar la derivación.'
            ], 500);
        }
    }

    /**
     * Arma el objeto Pdf (dompdf) con todos los datos de la consulta,
     * para el PDF de tipo 'receta' o 'diagnostico'. Lógica compartida
     * entre generarPdf() (descarga) y verPdf() (previsualización inline),
     * para no duplicar la carga de consulta/nota/evaluación/receta/
     * médico/triage en los dos métodos.
     */
    private function armarPdfConsulta($consultaId, $tipo)
    {
        if (!in_array($tipo, ['receta', 'diagnostico'])) {
            abort(404, 'Tipo de PDF no válido.');
        }

        $consulta = Consulta::with('paciente')->find($consultaId);
        if (!$consulta) {
            abort(404, 'Consulta no encontrada.');
        }

        $nota = NotaPsoapp::where('consulta_id', $consulta->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $evaluacion = EvaluacionIA::where('consulta_id', $consulta->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Receta guardada desde RecetaInteligente.vue (solo aplica
        // para el PDF de tipo 'receta', pero no cuesta nada cargarla
        // siempre por si la vista de diagnóstico también la usa).
        $receta = Receta::where('consulta_id', $consulta->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // El médico se toma de consultas.user_id (columna que sí
        // existe y ya se llena en store() con auth()->id() al
        // iniciar la consulta) — no de recetas, que no tiene esa
        // columna.
        $medico = \App\Models\User::find($consulta->user_id);

        // -----------------------------------------------------------
        // UBICACIÓN / SUCURSAL / LOGO DEL MÉDICO (dinámico, ya no fijo)
        // -----------------------------------------------------------
        // consultas.user_id -> users.id -> medicos.user_id ->
        // configuracion_medico_sucursal -> ubicaciones
        //
        // $medico (arriba) es el modelo User que atendió la consulta.
        // Para llegar a su sucursal/logo hay que ubicar su fila
        // correspondiente en el catálogo "medicos" y de ahí su(s)
        // configuración(es) de sucursal.
        $ubicacion = null;

        if ($medico) {
            $medicoCatalogo = \App\Models\Medico::with('configuraciones.ubicacion')
                ->where('user_id', $medico->id)
                ->first();

            // Si un médico llegara a tener varias sucursales configuradas,
            // por ahora se toma la primera. Avisar si se necesita elegir
            // la sucursal exacta de esa consulta en vez de la primera.
            $ubicacion = optional(optional($medicoCatalogo)->configuraciones)->first()?->ubicacion
                ?? null;
        }

        // Signos vitales: viven en la tabla triage, ligada por
        // paciente_id. Tomamos el triage MÁS RECIENTE de ese
        // paciente, sin filtrar por fecha contra la consulta,
        // porque en el flujo real el triage puede capturarse
        // antes o después de haberse creado la consulta.
        $triage = \App\Models\Triage::where('paciente_id', $consulta->paciente_id)
            ->orderBy('created_at', 'desc')
            ->first();

        $vista = $tipo === 'receta' ? 'pdf.receta' : 'pdf.diagnostico';

        // dompdf necesita la ruta ABSOLUTA de disco de la imagen, no una
        // URL: al generar el PDF en el servidor no hay navegador ni
        // sesión que resuelva esa ruta.
        //
        // 1) Se intenta primero el logo propio de la ubicación del
        //    médico (ubicaciones.imagen), que es el dinámico.
        // 2) Si no existe, se cae al logo genérico de siempre
        //    (public/images/logo.png), igual que antes.
        //
        // AJUSTAR: si ubicaciones.imagen NO se guarda bajo
        // storage/app/public (disco 'public' con symlink), cambiar
        // la línea de $rutaCandidata por la ubicación física real.
        $logoPath = null;

        if ($ubicacion && !empty($ubicacion->imagen)) {
            // Las imágenes de logo NO usan el disco 'storage' de Laravel;
            // viven directo en public/personalisarperfil/ (igual que
            // archivos_clinicos vive en public/archivos_clinicos).
            //
            // ubicaciones.imagen puede venir guardado de dos formas según
            // cómo se subió el archivo:
            //   a) solo el nombre:              logo-consultorio-dr-basto-2026.jfif
            //   b) con la carpeta incluida:      personalisarperfil/logo-....jfif
            // Se soportan ambos casos sin duplicar la carpeta.
            $valorImagen = ltrim($ubicacion->imagen, '/');

            $rutaCandidata = str_starts_with($valorImagen, 'personalisarperfil/')
                ? public_path($valorImagen)
                : public_path('personalisarperfil/' . $valorImagen);

            $logoPath = file_exists($rutaCandidata) ? $rutaCandidata : null;
        }

        if (!$logoPath) {
            $logoGenerico = public_path('images/logo.png');
            $logoPath = file_exists($logoGenerico) ? $logoGenerico : null;
        }

        $pdf = Pdf::loadView($vista, [
            'consulta'   => $consulta,
            'nota'       => $nota,
            'evaluacion' => $evaluacion,
            'receta'     => $receta,
            'medico'     => $medico,
            'ubicacion'  => $ubicacion,
            'triage'     => $triage,
            'logoPath'   => $logoPath,
        ]);

        $nombreArchivo = ($tipo === 'receta' ? 'receta_' : 'diagnostico_') . $consulta->folio . '.pdf';

        return [$pdf, $nombreArchivo];
    }

    /**
     * Genera y descarga un PDF ('diagnostico' o 'receta') para una
     * consulta, usado por NotaPSOAPP.vue -> descargar(). Usa la nota
     * PSOAPP, la evaluación IA y (para 'receta') la Receta guardada
     * más recientes de la consulta.
     */
    public function generarPdf($consultaId, $tipo)
    {
        try {
            [$pdf, $nombreArchivo] = $this->armarPdfConsulta($consultaId, $tipo);

            return $pdf->download($nombreArchivo);

        } catch (\Throwable $e) {
            \Log::error("Error en generarPdf: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error'   => 'No se pudo generar el PDF.'
            ], 500);
        }
    }

    /**
     * Previsualiza el PDF ('diagnostico' o 'receta') en línea (inline),
     * usado por ArchivosClinicos.vue / ExpedienteTabs.vue para mostrarlo
     * dentro de un <iframe> o modal, en vez de forzar la descarga como
     * hace generarPdf().
     */
    public function verPdf($consultaId, $tipo)
    {
        try {
            [$pdf, $nombreArchivo] = $this->armarPdfConsulta($consultaId, $tipo);

            return $pdf->stream($nombreArchivo);

        } catch (\Throwable $e) {
            \Log::error("Error en verPdf: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error'   => 'No se pudo mostrar el PDF.'
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
                    'success'                          => true,
                    'tipo'                             => 'derivacion',
                    'triage'                           => $respuestaIA['triage'] ?? null,
                    'diagnosticos_probables'           => $respuestaIA['diagnosticos_probables'] ?? [],
                    'especialidad_sugerida_ia'         => $respuestaIA['especialidad'] ?? null,
                    'motivo_derivacion'                => $respuestaIA['motivo_derivacion'] ?? null,
                    'requiere_urgencias'               => $respuestaIA['requiere_urgencias'] ?? false,
                    'justificacion'                    => $respuestaIA['justificacion'] ?? null,
                    // NUEVO: transparencia cuando la especialidad ideal no
                    // está en el catálogo de este consultorio.
                    'especialidad_fuera_catalogo'      => $respuestaIA['especialidad_fuera_catalogo'] ?? false,
                    'especialidad_ideal_no_disponible' => $respuestaIA['especialidad_ideal_no_disponible'] ?? null,
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
                'success'                    => true,
                'tipo'                       => 'receta_inteligente',
                'triage'                     => $respuestaIA['triage'] ?? null,
                'diagnosticos_probables'     => $respuestaIA['diagnosticos_probables'] ?? [],
                'medicamentos'               => $medicamentos,
                'medicamentos_sugeridos_ia'  => $medicamentosSugeridosIA, // no verificados
                // Sugerencia de la IA para el textarea de "Recomendación general"
                // del frontend (RecetaInteligente.vue). El médico puede editarla
                // libremente antes de guardar la receta.
                'recomendaciones_generales'  => $respuestaIA['recomendaciones_generales'] ?? '',
                'justificacion'              => $respuestaIA['justificacion'] ?? null,
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
     *
     * NOTA (transparencia de especialidad ideal): cuando la IA detecta
     * que la especialidad clínicamente ideal para el caso no existe en
     * el catálogo activo, lo señala mediante 'especialidad_fuera_catalogo'
     * (true/false) y 'especialidad_ideal_no_disponible' (el nombre de esa
     * especialidad ideal, ej. "Neumología"), mientras que 'especialidad'
     * sigue trayendo la mejor alternativa disponible (ej. "Medicina
     * general"). Estos dos campos solo vienen poblados cuando la fuente
     * es 'ia_triage'; el mapa de respaldo no tiene esa información, así
     * que en 'mapa_respaldo' quedan en false/null.
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

            // NUEVO: bandera + nombre de la especialidad ideal cuando no
            // está en el catálogo. Solo se poblarán si la fuente termina
            // siendo 'ia_triage'.
            $especialidadFueraCatalogo = false;
            $especialidadIdealNoDisponible = null;

            if ($respuestaIA && ($respuestaIA['tipo'] ?? null) === 'derivacion') {
                $triage = $respuestaIA['triage'] ?? null;
                $diagnosticosProbables = $respuestaIA['diagnosticos_probables'] ?? [];
                $motivoDerivacionIA = $respuestaIA['motivo_derivacion'] ?? null;
                $requiereUrgencias = $respuestaIA['requiere_urgencias'] ?? false;

                // NUEVO: tomamos directo de la respuesta de la IA
                $especialidadFueraCatalogo = $respuestaIA['especialidad_fuera_catalogo'] ?? false;
                $especialidadIdealNoDisponible = $respuestaIA['especialidad_ideal_no_disponible'] ?? null;

                $nombreSugeridoIA = trim((string) ($respuestaIA['especialidad'] ?? ''));

                if ($nombreSugeridoIA !== '') {
                    $especialidad = Specialty::where('estado', 'Activo')
                        ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombreSugeridoIA)])
                        ->first();

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

            if (!$especialidad) {
                $especialidad = $this->buscarEspecialidadPorMapaDeRespaldo($sintomas);
                $fuente = 'mapa_respaldo';

                // NUEVO: si terminamos usando el mapa de respaldo, no hay
                // forma de saber si la especialidad ideal existía o no en
                // el catálogo, así que se resetean para no mostrar un
                // aviso inventado en el frontend.
                $especialidadFueraCatalogo = false;
                $especialidadIdealNoDisponible = null;
            }

            return response()->json([
                'success'                          => true,
                'especialidad_sugerida'            => $especialidad,
                'especialidades'                   => $todasLasEspecialidades,
                'fuente'                           => $fuente,
                'triage'                           => $triage,
                'diagnosticos_probables'           => $diagnosticosProbables,
                'motivo_derivacion_ia'             => $motivoDerivacionIA,
                'requiere_urgencias'               => $requiereUrgencias,
                // NUEVO
                'especialidad_fuera_catalogo'      => $especialidadFueraCatalogo,
                'especialidad_ideal_no_disponible' => $especialidadIdealNoDisponible,
            ]);

        } catch (\Exception $e) {
            \Log::error("Error en derivacionInteligente: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'No se pudo determinar la especialidad sugerida.'
            ], 500);
        }
    }

    private function buscarEspecialidadPorMapaDeRespaldo(array $sintomas)
    {
            $textoSintomas = mb_strtolower(implode(' ', $sintomas));

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

            if (!$especialidadSugerida) {
                $especialidadSugerida = 'Medicina general';
            }

            return Specialty::where('estado', 'Activo')
                ->where('nombre', $especialidadSugerida)
                ->first();
    }

    /**
     * Historial clínico completo de un paciente (todas sus consultas +
     * transcripciones), usado por ExpedienteTabs.vue -> obtenerConsultas().
     *
     * FIX: el select() de $consultas traía la columna 'estado' (sin uso
     * real en el sistema, nunca se actualiza) en vez de 'estado_consulta'
     * (la columna que finalizarConsulta() sí marca como 'finalizada' al
     * presionar el botón "Finalizar" en ConsultaTiempoReal.vue). Sin
     * 'estado_consulta' en la respuesta, el frontend no tenía forma de
     * saber si una consulta ya había sido cerrada, y mostraba siempre
     * "En proceso" para la primera consulta del historial sin importar
     * su estado real.
     */
    public function historialClinico(Request $request)
    {
        try {
            $validated = $request->validate([
                'paciente_id' => 'required|integer|exists:pacientes,id'
            ]);

            $consultas = Consulta::where('paciente_id', $validated['paciente_id'])
                ->with(['transcripciones' => function ($query) {
                    $query->orderBy('created_at', 'asc')
                        ->select([
                            'id',
                            'consulta_id',
                            'mensaje',
                            'tipo_usuario',
                            'analizado_ia',
                            'observaciones_ia',
                            'created_at'
                        ]);
                }])
                ->orderBy('created_at', 'desc')
                ->get(['id', 'folio', 'paciente_id', 'motivo_consulta', 'estado_consulta', 'created_at']);

            $evaluaciones = EvaluacionIA::whereIn('consulta_id', $consultas->pluck('id'))
                ->orderBy('created_at', 'desc')
                ->get([
                    'id',
                    'consulta_id',
                    'sintomas_detectados',
                    'diagnostico_probable',
                    'recomendacion',
                    'confianza',
                    'created_at'
                ])
                ->groupBy('consulta_id');

            $consultas->each(function ($consulta) use ($evaluaciones) {
                $consulta->evaluaciones = $evaluaciones->get($consulta->id, collect())->values();
            });

            return response()->json([
                'success' => true,
                'consultas' => $consultas
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