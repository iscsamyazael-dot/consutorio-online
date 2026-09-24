<?php

use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedicamentoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\RecetaDetalleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\TriageController;
use App\Http\Controllers\ArchivosClinicosController;
use App\Http\Controllers\ConsultaIAController; // IA
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DerivacionController;
use App\Http\Controllers\UserRegisterController;
use App\Http\Controllers\ListaEsperaController;
use App\Models\Paciente;
use App\Http\Controllers\CitaController;//agenda
use App\Http\Controllers\UbicacionController;//agenda
use App\Http\Controllers\Api_Ionic\AuthController; //Login APP-IONIC
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\CimaMedicamentoController;
use App\Http\Controllers\EvaluacionesIAController;
use App\Http\Controllers\DispositivoController;
use App\Http\Controllers\ConfiguracionEmpresaController;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ConfiguracionImpresoraController;
use App\Http\Controllers\ImpresionTicketController;
use App\Http\Controllers\ConfiguracionCorreoController;
use App\Http\Controllers\WahaController;
use App\Http\Controllers\Icd11Controller;
use App\Http\Controllers\ExpedienteClinicoController;
use App\Http\Controllers\ClinicaTrabajo\FichaOcupacionalController;


Route::get('/', function () { return view('auth.login'); });

Route::middleware('auth')->group(function () {

        // ═════════════════════════════════════════════════════════════
        // CLÍNICA DE TRABAJO
        // ═════════════════════════════════════════════════════════════
        Route::get('/ficha-ocupacional', [FichaOcupacionalController::class, 'vista'])->name('clinica-trabajo.ficha-ocupacional.index');
        Route::get('/api/clinica-trabajo/ficha-ocupacional', [FichaOcupacionalController::class, 'index'])->name('api.clinica-trabajo.ficha-ocupacional.index');
        Route::post('/api/clinica-trabajo/ficha-ocupacional', [FichaOcupacionalController::class, 'store'])->name('api.clinica-trabajo.ficha-ocupacional.store');
        Route::post('/api/clinica-trabajo/ficha-ocupacional/analizar', [FichaOcupacionalController::class, 'analizar']);

        // ═════════════════════════════════════════════════════════════
        // BLOQUE 1: RUTAS GENERALES (sin datos clínicos sensibles)
        // Accesibles por cualquier usuario autenticado del tenant.
        // ═════════════════════════════════════════════════════════════

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::post('/triage', [TriageController::class, 'store'])->name('triage.store');
        //Código para hacer el filtro de un paciente mediante un input //
        //Route::get('buscarPaciente',[PacienteController::class,'filtrar_paciente']);
        Route::get('/perfil-usuario', [ProfileController::class, 'obtenerPerfil']);
        //ACTUALIZA DATOS DEL PERFIL
        Route::post('/cambiar-password', [ProfileController::class, 'updatePassword']);
        Route::post('/usuarios/registro', UserRegisterController::class);
        Route::get('/usuarios', [UserController::class, 'index']);
        Route::delete('usuarios/{id}', [UserController::class, 'destroy']);
        Route::get('/dashboard', function () { return view('dashboard'); })->name('dashboard');
        //Código para hacer el filtro de un paciente mediante un input //
        //Route::get('buscarPaciente',[PacienteController::class,'filtrar_paciente']);
        //ACTUALIZA DATOS DEL PERFIL
        Route::put('/perfil-usuario', [ProfileController::class, 'actualizarPerfil']);
        //Actualizacion Medico Especialidad//
        Route::put('/medicos/{id}/especialidad', [MedicoController::class, 'actualizarEspecialidad']);
        // Ruta para procesar el formulario y guardar el registro en las tablas
        Route::get('/api/specialties', [SpecialtyController::class, 'list']);// Agenda: filtro por especialidad
        Route::get('pacientes/buscar', [PacienteController::class, 'filtrar_paciente'])->name('pacientes.filtrar_paciente');

        //para traer actualizar y eliminar medicos
        // Route::get('buscarMedico/{id}', [MedicoController::class, 'show']);
        // Route::put('actualizarMedico/{id}', [MedicoController::class, 'update']);
        // Route::delete('eliminarMedico/{id}', [MedicoController::class, 'destroy']);

        ///*** RUTAS PARA LAS APIS Y CONSUMO DE DATOS */
        // Vista principal (Blade)

        // Endpoints consumidos por el API Service
       Route::prefix('api')->group(function () {
            Route::get('/evaluaciones-ia', [EvaluacionesIAController::class, 'api'])->name('api.evaluaciones-ia.index');
            Route::get('/evaluaciones-ia-opciones', [EvaluacionesIAController::class, 'opcionesFiltros']);
            Route::get('/evaluaciones-ia/indicadores', [EvaluacionesIAController::class, 'indicadores'])->name('api.evaluaciones-ia.indicadores');
            Route::get('/evaluaciones-ia/{folio}', [EvaluacionesIAController::class, 'show'])->name('api.evaluaciones-ia.show');
        });
        //RUTA PARA ACTUALIZAR EL ESTADO DE CONSULTA
        Route::patch('/pacientes/{paciente}/estado-consulta', [ConsultaController::class, 'actualizarEstadoConsulta']);
        //RUTA PARA ACTUALIZAR EL ESTADO DE DERIVACION
        Route::put('/derivaciones/{id}/estado', [DerivacionController::class, 'actualizarEstado']);
        //Ruta para obtener estadisticas de las cartas de derivacion
        Route::get('/derivaciones/estadisticas', [DerivacionController::class, 'obtenerEstadisticas']);
        //RUTA QUE OBTIENE TODAS LAS DERIVACIONES
        Route::get('/derivaciones', [DerivacionController::class, 'index']);
        Route::get('medicoEstadistica', [MedicoController::class, 'obtenerEstadisticas']);
        Route::get('listaUbicaciones', [UbicacionController::class, 'listar']);// Agenda: filtro por ubicación/sucursal
        Route::post('/medicos', [MedicoController::class, 'store'])->name('medicos.store');
        Route::get('/medicos-horarios', [MedicoController::class, 'index']);
        //ruta que filtra los medicos locales de la tabla
        Route::get('buscarMedico', [MedicoController::class, 'filtrar_medico']);// Agenda: filtro por médico
        //metodo de buscara medicamento
        Route::prefix('cima')->group(function () {
            Route::get('/buscar', [CimaMedicamentoController::class, 'buscar']);
            Route::get('/{nregistro}', [CimaMedicamentoController::class, 'detalle']);
        });
        Route::resource('especialidades', SpecialtyController::class);
        // NOTA DE SEGURIDAD: se excluye 'show' del resource de pacientes porque
        // ese endpoint expone el expediente/datos clínicos del paciente. Se
        // redefine más abajo, dentro del bloque protegido con
        // can:acceso-medico-admin, para que 'asistente' no pueda consultarlo
        // directamente por URL/ID aunque no tenga el link en su menú.
        Route::resource('pacientes', PacienteController::class)->except(['show']);
        Route::get('medicamentos/resumen', [MedicamentoController::class, 'resumen']);
        // rutas nuevas de medicamentoa
        Route::resource('medicamentos', MedicamentoController::class);
        Route::resource('usuarios', UserController::class);

        //Ruta para ver el total de las consultas finalizadas el día de hoy
        Route::get('total-consultas-finalizadas', [TriageController::class, 'totalFinalizadasHoy']);

        // IMPORTANTE: estas rutas deben ir ANTES de Route::resource('consultaIA', ...)
        // y deben coincidir EXACTAMENTE con la URL que llama el frontend
        // (urlArchivoIA = route + '/consultaIA/archivo' en TranscripcionLive.vue).
        // Antes decía 'consulta-ia/archivo' (con guión), por eso el POST no
        // coincidía con esa ruta y caía en la ruta GET /consultaIA/{consultaIA}
        Route::post('consultaIA/archivo', [ConsultaIAController::class, 'subirArchivo'])->name('consultaIA.subirArchivo'); // IA: sube archivo de audio/documento a la consulta con IA
        Route::get('medicamentos/prediccion', [MedicamentoController::class, 'prediccion']);// chat de medicamento con IA
        Route::post('derivacionInteligente', [ConsultaIAController::class, 'derivacionInteligente'])->name('derivacionInteligente'); // IA: genera derivación con apoyo de IA
        Route::post('consultaIA/{consultaId}/derivar', [ConsultaIAController::class, 'guardarDerivacion'])
        ->name('consultaIA.guardarDerivacion'); // IA: guarda la derivación generada en Derivacion.vue

        Route::resource('medicos', MedicoController::class);
        Route::resource('ubicaciones', UbicacionController::class);// Agenda: CRUD de ubicaciones/sucursales
        Route::resource('movimientos',MovimientoInventarioController::class);
        Route::resource('triage', TriageController::class);
        // Route::get('dashboard/api/citas', [CitaController::class, 'getEventos']);//COMNTDAAAAA
        //Route::resource('dashboard/citas', CitaController::class);
        Route::get('dashboard/api/citas', [CitaController::class, 'getEventos']);// Agenda: eventos del calendario (usada por el calendario del dashboard, distinta de /api/citas)
        //Route::resource('consultas', ConsultaController::class)->except(['index']);
        Route::resource('citas', CitaController::class);// Agenda: CRUD de citas
        Route::post('/api/citas', [CitaController::class, 'store']);// Agenda: crear cita desde el calendario / lista de espera

        // Ruta para cambiar el estado de una consulta (ej. En proceso -> Finalizada)
        Route::patch('ActualizarEstadoConsulta/{id}', [ConsultaController::class, 'actualizarEstado']);
        Route::patch('/api/consultas/{id}/estado', [ConsultaController::class, 'actualizarEstado'])->name('consultas.estado.api');// Historial: cambiar estado de consulta tradicional

        //Rutas para la lista de espera ///
        // Dentro del grupo protegido por auth (mismo patrón que ya usas para 'citas')
        Route::resource('lista-espera', ListaEsperaController::class)
             ->parameters(['lista-espera' => 'listaEspera']);

        Route::patch('lista-espera/{listaEspera}/estado', [ListaEsperaController::class, 'actualizarEstado'])
            ->name('lista-espera.estado');

        //Ruta para mostrar el resumen de las tarjetas
        Route::get('Resumen-listaEspera-consultaFinalizadas', [ListaEsperaController::class, 'resumen']);

        //RUTAS PARA GENERAR EL TOKENS PARA LA VINCULACIÓN DE LOS DISPOSITIVOS //
         Route::get('dispositivos', [DispositivoController::class, 'index']);
        //Ruta para generar codigo de emparejamiento//
        Route::post('dispositivos/generar-codigo', [DispositivoController::class, 'generarCodigoEmparejamiento']);
        Route::delete('dispositivos/{id}', [DispositivoController::class, 'destroy']);
        Route::post('dispositivos/{id}/regenerar-token', [DispositivoController::class, 'regenerarToken']);
        Route::get('/dispositivos/verificar-codigo/{codigo}', [DispositivoController::class, 'verificarCodigo']);
        //AQUI TERMINAN LAS RUTAS PARA LA VINCULACION DE DISPOSITIVOS ///

        //RUTAS PARA CONFIGURAR LA IMPRESORA DEL KIOSCO//
        Route::get('/configuracion-impresora', [ConfiguracionImpresoraController::class, 'show']);
        Route::post('/configuracion-impresora', [ConfiguracionImpresoraController::class, 'update']);
        //AQUI TERMINA LAS RUTAS PARA LA CONFIGURACION DE LA IMPRESORA DEL KIOSCO//

        // Onboarding: marca el onboarding como completado (POST /onboarding/completar)
        Route::post('/onboarding/completar', [OnboardingController::class, 'completar'])->name('onboarding.completar');
       // Onboarding: vista del wizard de bienvenida (GET /onboarding)
       Route::get('conf-admin', function () { return view('Onboarding.index'); })->name('configuracion-sistema.conf-admin');
       // Onboarding: vista del wizard de bienvenida (GET /onboarding)
       Route::get('/onboarding', function () { return view('Onboarding.index'); })->name('onboarding.index');
       // Onboarding: Actualizar datos del correo electronico para envio de correos //
       Route::patch('actualizar-correo', [ConfiguracionEmpresaController::class, 'actualizarCorreo']);

       //Rutas para usar los servicios del AUTH de google o Microsoft
       // Verificar si ya hay una cuenta vinculada y el email
        Route::get('estatus-correo', [ConfiguracionCorreoController::class, 'estatus']);

        // Redirigir al usuario al proveedor de OAuth (ej. Google)
        Route::get('/auth/google/redirect', [ConfiguracionCorreoController::class, 'redirectToGoogle']);

        // Callback que responde Google tras la autorización exitosa
        Route::get('/auth/google/callback', [ConfiguracionCorreoController::class, 'handleGoogleCallback']);

        // Desconectar / limpiar los datos de correo de la fila existente
        Route::post('desconectar-correo', [ConfiguracionCorreoController::class, 'desconectar']);

        //Ruta para el consumo de las APIS ICD 11 International Classification of Diseasses 11th Revision//
        Route::get('/icd11/buscar', [Icd11Controller::class, 'buscar']);
        ////////////////////////////////////////////////////////////////////////////////////////////////////////

        //Ruta para cuando el triage se guarda directamente de la lista de espera //
        Route::post('/triage/vincular-consulta', [TriageController::class, 'vincularConsulta']);
        ///////////////////////////////////////////////////////////////////////////////////////

        // ─────────────────────────────────────────────────────────────
        // ÚNICA definición de GET /api/citas. Antes existían DOS rutas
        // GET /api/citas apuntando a controladores distintos
        // (getEventos y getCitas); Laravel resolvía la ambigüedad usando
        // la última definida, lo cual es frágil (cualquier reordenamiento
        // futuro del archivo puede cambiar en silencio qué método
        // responde). getCitas() es el que trae la estructura completa
        // (paciente, medico, especialidad anidados) que consumen
        // ConsultaClinica.vue y ConsultaInteligente.vue, así que es el
        // que se conserva aquí.
        // ─────────────────────────────────────────────────────────────
        Route::get('/api/citas', [CitaController::class, 'getCitas']);// Agenda: lista de citas con paciente/medico/especialidad (ConsultaClinica.vue, ConsultaInteligente.vue)
        Route::get('/api/dashboard/consultas-hoy', [DashboardController::class, 'consultasHoy']); // Dashboard SPA: consultas usadas y finalizadas hoy (Home.vue)
        //Resumen del dia Consultas finalizadas, Lista de espera, Consultas realizadas del día de hoy
        Route::get('resumen-consultas-finalizadas-pendientes', [DashboardController::class, 'resumenHoy']);
        // Agenda: actualizar datos del paciente
        // Cambias 'SubirArchivosControlador' por el que ya tengas
        Route::post('archivoClinico', [ArchivosClinicosController::class, 'archivoclinico']);
        //Código para hacer el filtro de un paciente mediante un input //
        Route::get('buscarPaciente',[PacienteController::class,'filtrar_paciente']);
        //Codigo para las vistas y que son usadas en el menú de adminlte"

        // ─────────────────────────────────────────────────────────────
        // ÚNICA definición de PATCH .../citas/{cita}/estado. Antes había
        // dos rutas casi idénticas (con y sin prefijo /api) apuntando al
        // mismo método actualizarEstado. Se deja solo la que realmente
        // usa el frontend: axios.patch('/api/citas/{id}/estado', ...)
        // en ConsultaInteligente.vue.
        // ─────────────────────────────────────────────────────────────
        Route::patch('/api/citas/{cita}/estado', [CitaController::class, 'actualizarEstado'])->name('citas.estado.api');// Agenda: cambiar estado de cita (usada por ConsultaInteligente.vue)

        Route::get('/triage', [TriageController::class, 'index']);
        // ← NUEVA, debe ir ANTES de /{id}
        Route::get('/triage/{pacienteId}/analizar-ia', [TriageController::class, 'analizarIA']);
        Route::get('/triage/{id}', [TriageController::class, 'show']);
        ///*** AQUI TERMINA LAS RUTAS DE LAS LAS APIS Y CONSUMO DE DATOS */
        // Ruta explícita para manejar la petición POST desde panelatencion.vue
        Route::post('/triage/guardar/{id?}', [TriageController::class, 'guardarTriageRapido'])->name('triage.guardarRapido');
        Route::put('/pacientes/{id}', [PacienteController::class, 'update'])->name('pacientes.update');


        // ═════════════════════════════════════════════════════════════
        // BLOQUE 2: DATOS CLÍNICOS SENSIBLES (protegido)
        // Expediente, historia clínica (NOM-004), notas PSOAPP, recetas,
        // archivos clínicos, diagnósticos y consultas.
        // Gate::acceso-medico-admin => solo 'medico' y 'admin'.
        // El rol 'asistente' NUNCA debe tener acceso a este bloque, ni
        // por vista ni por llamada directa a la API/URL.
        //
        // ANTES: estas rutas vivían sueltas dentro del grupo 'auth'
        // general (BLOQUE 1), sin ningún Gate — cualquier usuario
        // autenticado del tenant podía pedirlas directamente por
        // URL/ID sin pasar por el menú, aunque su rol no tuviera el
        // link visible. Esto se corrige agrupándolas aquí.
        // ═════════════════════════════════════════════════════════════
        Route::middleware(['can:acceso-medico-admin'])->group(function () {

            // Detalle / expediente del paciente (antes era pacientes.show,
            // parte del Route::resource('pacientes', ...) sin protección)
            Route::get('pacientes/{paciente}', [PacienteController::class, 'show'])->name('pacientes.show');
            //Ruta parametrizada para ver el detalle de un paciente en el expediente médico//
            Route::get('ExpedienteDetalle/{id}', [PacienteController::class, 'show'])
                ->name('ExpedienteDetalle');
            //Ruta para descargar o imprimir el pdf expediente del paciente//
            Route::get('Descargar-Expediente-pdf/{paciente}', [PacienteController::class, 'descargarExpedientePdf'])
                    ->name('pacientes.descargarExpedientePdf');

            //Ruta para el uso del Expediente Clinico ó Historia Clinica //
            Route::get('/expedienteClinico/{pacienteId}', [ExpedienteClinicoController::class, 'obtener']);
            Route::post('/expedienteClinico/{pacienteId}', [ExpedienteClinicoController::class, 'guardar']);

            Route::resource('consultas', ConsultaController::class);
            //Ruta para ver el historial de una consulta de una determinada fecha //
            Route::get('VerHistorialConsultas', [ConsultaController::class, 'historial']);
            Route::post('/consultaIA/{consultaId}/diagnostico', [ConsultaIAController::class, 'guardarDiagnostico']);

            // Listado y descarga de archivos clínicos para ArchivosClinicos.vue.
            // Deben ir antes del resource para no caer en la ruta GET
            // /consultaIA/{consultaIA} del resource.
            Route::get('consultaIA/archivos/{consultaId}', [ConsultaIAController::class, 'listarArchivos'])->name('consultaIA.listarArchivos'); // IA: lista archivos asociados a una consulta con IA
            Route::get('consultaIA/archivo/{id}/descargar', [ConsultaIAController::class, 'descargarArchivo'])->name('consultaIA.descargarArchivo'); // IA: descarga un archivo de la consulta con IA
            // Guarda la nota PSOAPP (borrador o final) y genera el PDF de
            // diagnóstico/receta. Igual que las de arriba, deben ir antes del
            // resource para que no las intercepte la ruta GET /consultaIA/{consultaIA}.
            Route::post('consultaIA/{consultaId}/psoapp', [ConsultaIAController::class, 'guardarPsoapp'])->name('consultaIA.guardarPsoapp'); // NUEVO // IA: guarda la nota PSOAPP generada/editada
            Route::get('consultaIA/{consultaId}/pdf/{tipo}', [ConsultaIAController::class, 'generarPdf'])->name('consultaIA.generarPdf'); // NUEVO // IA: genera PDF de diagnóstico/receta de la consulta con IA
            Route::get('consultaIA/{consultaId}/pdf/{tipo}/ver', [ConsultaIAController::class, 'verPdf'])->name('consultaIA.verPdf'); // IA: previsualiza el PDF (inline) en el modal de ExpedienteTabs.vue
            Route::post('consultaIA/{consultaId}/evaluacion', [ConsultaIAController::class, 'guardarEvaluacion'])->name('consultaIA.guardarEvaluacion'); // IA: edita diagnóstico/recomendación de la evaluación IA desde el historial (ExpedienteTabs.vue)
            // Historial clínico completo de un paciente (todas sus consultas +
            // transcripciones), usado por HistorialClinico.vue.
            Route::get('historialClinico', [ConsultaIAController::class, 'historialClinico'])->name('consultaIA.historialClinico'); // IA: historial clínico completo generado por el módulo de IA
            Route::post('consultaIA/{consultaId}/finalizar', [ConsultaIAController::class, 'finalizarConsulta'])->name('consultaIA.finalizarConsulta'); // IA: cierra la consulta y bloquea más mensajes
            Route::get('consultaIA/paciente/{pacienteId}/psoapp', [ConsultaIAController::class, 'notasPsoapp'])->name('consultaIA.notasPsoapp'); // IA: lista todas las notas PSOAPP de un paciente, para ExpedienteTabs.vue
            Route::resource('consultaIA', ConsultaIAController::class); // IA: CRUD principal del módulo de Consulta Inteligente (IA)

            Route::post('recetaInteligente', [ConsultaIAController::class, 'recetaInteligente']); // IA: genera receta con apoyo de IA
            Route::resource('recetas', RecetaController::class);
            Route::resource('receta-detalles', RecetaDetalleController::class);
            Route::post('consultaIA/{consultaId}/receta', [ConsultaIAController::class, 'guardarReceta']); // ← NUEVA: guarda la receta de RecetaInteligente.vu

            //RUTA PARA ACTUALIZAR TIPO Y ESTADO DE ARCHIVO
            Route::put('/archivos-clinicos/{id}', [ArchivosClinicosController::class, 'update']);
            Route::resource('archivoclinico', ArchivosClinicosController::class);
            //RUTA PARA REAUNUDAR LA CONSULTA CONSERVANDO LOS DIAGNOSTICOS DETECTADOS//
            Route::get('consultaIA/{consultaId}/sesion', [ConsultaIAController::class, 'obtenerHistorialSesion'])
             ->name('consultaIA.obtenerHistorialSesion');
        });


        //**INICIA LAS RUTAS PARA LAS VISTAS DE ACUERDO AL ACESSO DE CADA USUARIO *//

        ///SECCION DE ACCESO A LAS VISTAS PARA ADMINISTRADOR - MEDICO - ASISTENTE ///
        // Route::middleware(['auth', 'can:acceso-general'])->group(function() {
        Route::middleware(['auth', 'can:acceso-general', 'onboarding.check'])->group(function() {// Se agrega el middleware onboarding.check para verificar si el onboarding está completado
            // Antes: closure que solo hacía "return view('pacientes.index')" sin datos.
            // Ahora: pasa por el controlador para inyectar totalPacientes / totalPendientes / pacientesPendientes.
            Route::get('ListaPacientes', [PacienteController::class, 'lista'])->name('pacientes.index');
             Route::get('PacienteNuevo/{id?}', [PacienteController::class, 'create'])
           ->name('pacientes.create');
            // NOTA DE SEGURIDAD: aquí existía una segunda ruta 'ExpedientePacientes'
            // (->name('pacientes.create')) que duplicaba, con otro nombre, la
            // ruta de vista de expediente definida abajo en el bloque
            // acceso-medico-admin. Al estar dentro de acceso-general, le daba
            // a 'asistente' acceso a la vista de expediente completo. Se
            // eliminó de aquí; la única definición válida vive en el bloque
            // médico-admin, más abajo.
        });

        ///SECCION DE ACCESO A LAS VISTAS PARA ADMINISTRADOR - MEDICO///
            // Route::middleware(['auth', 'can:acceso-medico-admin'])->group(function() {
            Route::middleware(['auth', 'can:acceso-medico-admin', 'onboarding.check'])->group(function() {//
            Route::get('/', function() { return view('dashboard'); })->name('dashboard');
            Route::get('Medicamentos', function() { return view('medicamentos.index'); })->name('medicamentos.index');
            Route::get('agregar-usuario',function(){ return view('configuracion-sistema.agregar-usuario');});
            Route::get('ExpedientePacientes', function() { return view('pacientes.expediente'); })->name('pacientes.expediente');
            // Ahora acepta un {id?} opcional: si viene, es el id de la consulta
            // a mostrar (usado por ExpedienteTabs.vue -> "Ver consulta completa").
            // Se dejó opcional para no romper otros lugares que ya enlazan a
            // esta ruta sin id.
            Route::get('HistorialConsulta/{id?}', function($id = null) { return view('consultas.consultaIndividual', compact('id')); })->name('consultas.consultaIndividual');
            Route::get('HistorialConsultas', function () {
                return view('historialconsultas.index');
            });
            Route::get('NuevaConsulta', [ConsultaController::class, 'create'])->name('consultas.create');
            //Route::get('NuevaConsulta', function () { return view('consultas.create'); })->name('consultas.create');
            Route::get('ConsultaInteligenteNueva', function() {
                return view('consultas.consulta_inteligente', ['paciente' => null]);
            })->name('consultas.consulta_inteligente.nueva'); // IA: vista de Consulta Inteligente sin paciente asociado (nueva)
            Route::get('MedicosAlta',function(){return view('medicos.altamedicos'); })->name('medicos.altamedicos');
            Route::get('HistorialRecetas',function(){ return view('recetas.historial-recetas');})->name('recetas.historial-recetas');
            Route::get('TRIAGES', function() { return view('atencion-medica.triage'); })->name('atencion-medica.triage');
            Route::get('EvaluacionIa', [EvaluacionesIAController::class, 'index'])->name('atencion-medica.evaluacion-ia');
            Route::get('ArchivosClinicos', function() { return view('atencion-medica.archivos-clinicos'); })->name('atencion-medica.archivos-clinicos');
            Route::get('Derivaciones', function() { return view('atencion-medica.derivaciones'); })->name('atencion-medica.derivaciones');
            Route::get('ListaConsultas', function () { return view('consultas.index'); })->name('consultas.index');
            Route::get('ConsultarEspecialidades',function(){ return view('specialties.index'); })->name('specialties.index');
            Route::get('RegistroMedico', function (){ return view('medicos.medicocreate'); })->name('medicos.medicocreate');
            Route::get('perfil',function(){ return view('configuracion-sistema.perfil'); })->name('configuracion-sistema.perfil');
            Route::get('cambiar-contraseña', function () { return view('configuracion-sistema.cambiar-contraseña'); })->name('configuracion-sistema.cambiar-contraseña');
            //NUEVAS RUTAS DE CONFIGURACION EMPRESA
            Route::get('/configuracion-empresa', [ConfiguracionEmpresaController::class, 'show']);
            Route::put('/configuracion-empresa/config-correo', [ConfiguracionEmpresaController::class, 'guardarConfigCorreo']);

            //RUTAS PARA LAS SESSIONES DE WAHA //
            Route::prefix('waha')->group(function () {
                Route::post('/iniciar', [WahaController::class, 'iniciarSesion']);
                Route::get('/qr', [WahaController::class, 'obtenerQr']);
                Route::get('/estatus', [WahaController::class, 'estatus']);
            });

            Route::get('Sucursales',function(){ return view('Ubicaciones.index'); })->name('Ubicaciones.index');
            Route::get('Agenda',function(){ return view('Citas.index'); })->name('Citas.index');
            Route::get('AgendarCitas',function(){ $datos = (new CitaController())->create(); return view('Citas.create',$datos); });
            Route::get('ExpedientePacientes/{id}', function ($id) {
                return view('pacientes.expediente');
            })->name('pacientes.expediente.detalle');
           Route::get('consultaNormal/{id}', [ConsultaController::class, 'create']);
            Route::get('ConsultaInteligente/{id}', function ($id) {
                $paciente = Paciente::findOrFail($id);
                return view('consultas.consulta_inteligente', compact('paciente'));
            })->name('consultas.consulta_inteligente'); // IA: vista de Consulta Inteligente para un paciente específico
            //Vista para agregar un dispositivo al kiosco//
              Route::get('agrega-dispositivo', function () { return view('Kiosco.dispositivo');})->name('agrega-dispositivo');
            //Vista para agregar la impresora que imprimira el Ticket//
            Route::get('agregar-impresora', function () { return view('Kiosco.impresora');})->name('agregar-impresora');
            //Vista para vincular correo electronico como parte de la configuracion Omboarding//
            Route::get('vincular-correo', function () { return view('configuracion-sistema.vincularCorreo');})->name('vincular-correo');
            Route::get('vinculacion-whatsapp', function () { return view('configuracion-sistema.VincularWhatsaap');})->name('vinculacion-whatsapp');
        });

});


Route::view('inicio', 'dashboard');

//Pruebas para las APIS DE IONIC///
Route::get('MedicoPerfil/{userId}', [MedicoController::class, 'getPerfilMedico']);

// Login: sin autenticación
Route::prefix('api/ionic')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);

});



// Rutas protegidas: requieren token Sanctum
Route::prefix('api/ionic')
    ->middleware('auth:sanctum')
    ->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::get('MedicoPerfil', [MedicoController::class, 'getPerfilMedico']);
        Route::get('MedicoConfiguracion', [MedicoController::class, 'getMedicoConfiguracion']);
        Route::get('ResumenCitasHoy', [CitaController::class, 'getDashboardStats']);
        Route::get('CitasDelDia', [CitaController::class, 'getCitasDelDia']);
        Route::get('ResumenCitas', [CitaController::class, 'getResumenCitas']);
        Route::get('ListaCitasHoyMañanaSemana', [CitaController::class, 'getListaCitas']);
        Route::get('CitasPorFecha', [CitaController::class, 'getCitasPorFecha']);
        Route::get('DetalleCita', [CitaController::class, 'getDetalleCita']);
        Route::get('HistorialCitas', [CitaController::class, 'getHistorialCitas']);
        Route::get('TotalCitasPorDiayMes', [CitaController::class, 'citasPorMes']);
        Route::get('notificacionesRecientes', [NotificacionController::class, 'index']);
        Route::post('notificaciones/{id}/leer', [NotificacionController::class, 'marcarLeida']);
        Route::post('actualizarCita/{id}/estado', [CitaController::class, 'actualizarEstadoCita']);
        Route::put('ActualizarContrasenia', [AuthController::class, 'updatePassword']);
    });


// Rutas protegidas del kiosco/TV: requieren token Sanctum del dispositivo
Route::prefix('api/kiosco')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::post('lista-espera/buscar-paciente', [ListaEsperaController::class, 'buscarParaKiosco'])
            ->middleware('throttle:30,1')
            ->name('lista-espera.buscar-kiosco');

        Route::post('lista-espera/registrar-desde-kiosco', [ListaEsperaController::class, 'registrarDesdeKiosco'])
            ->middleware('throttle:30,1')
            ->name('lista-espera.registrar-kiosco');

        Route::get('lista-espera-pantalla', [ListaEsperaController::class, 'pantalla'])
            ->middleware('throttle:30,1')
            ->name('lista-espera.pantalla');

        Route::post('/api/kiosco/imprimir-ticket', [ImpresionTicketController::class, 'imprimirTicket']);

        //RUTA TEMPORAL PARA VER COMO QUEDA EL DISEÑO DEL TICKET//
        Route::post('previsualizar-ticket', [ImpresionTicketController::class, 'previewTextoPlano']);


    });

//Ruta para la vista del Kiosco
Route::get('kiosco', function () { return view('Kiosco.Index'); })->name('Kiosco.index');
//Ruta para la TV del Kiosco//
Route::get('TVKiosco', function () { return view('Kiosco.kioscoTV'); })->name('Kiosco.kioscoTV');

// Pública, sin auth — la tablet/TV solo la usa una vez para emparejarse:
Route::post('api/kiosco/dispositivos/emparejar', [DispositivoController::class, 'emparejar'])
    ->middleware('throttle:5,1');


require __DIR__.'/auth.php';