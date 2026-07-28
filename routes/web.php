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
use App\Http\Controllers\UserRegisterController;
use App\Models\Paciente;
use App\Http\Controllers\CitaController;//agenda        
use App\Http\Controllers\UbicacionController;//agenda 
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api_Ionic\AuthController; //Login APP-IONIC
use App\Http\Controllers\NotificacionController;


Route::get('/', function () { return view('auth.login'); });

Route::middleware('auth')->group(function () {
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
        // Ruta para procesar el formulario y guardar el registro en las tablas
        Route::get('/api/specialties', [SpecialtyController::class, 'list']);// Agenda: filtro por especialidad
        Route::get('pacientes/buscar', [PacienteController::class, 'buscar'])->name('pacientes.buscar');
        //para traer actualizar y eliminar medicos
        // Route::get('buscarMedico/{id}', [MedicoController::class, 'show']);
        // Route::put('actualizarMedico/{id}', [MedicoController::class, 'update']);
        // Route::delete('eliminarMedico/{id}', [MedicoController::class, 'destroy']);

        Route::get('medicoEstadistica', [MedicoController::class, 'obtenerEstadisticas']);
        Route::get('listaUbicaciones', [UbicacionController::class, 'listar']);// Agenda: filtro por ubicación/sucursal
        Route::post('/medicos', [MedicoController::class, 'store'])->name('medicos.store');
        Route::get('/medicos-horarios', [MedicoController::class, 'index']);
        //ruta que filtra los medicos locales de la tabla 
        Route::get('buscarMedico', [MedicoController::class, 'filtrar_medico']);// Agenda: filtro por médico

        Route::resource('especialidades', SpecialtyController::class);
        Route::resource('pacientes', PacienteController::class);
        Route::resource('consultas', ConsultaController::class);
        Route::resource('medicamentos', MedicamentoController::class);
        Route::resource('recetas', RecetaController::class);
        Route::resource('receta-detalles', RecetaDetalleController::class);
        Route::resource('usuarios', UserController::class);

        // IMPORTANTE: estas rutas deben ir ANTES de Route::resource('consultaIA', ...)
        // y deben coincidir EXACTAMENTE con la URL que llama el frontend
        // (urlArchivoIA = route + '/consultaIA/archivo' en TranscripcionLive.vue).
        // Antes decía 'consulta-ia/archivo' (con guión), por eso el POST no
        // coincidía con esa ruta y caía en la ruta GET /consultaIA/{consultaIA}
        Route::post('consultaIA/archivo', [ConsultaIAController::class, 'subirArchivo'])->name('consultaIA.subirArchivo'); // IA: sube archivo de audio/documento a la consulta con IA
        // Listado y descarga de archivos clínicos para ArchivosClinicos.vue.
        // Mismo motivo que la de arriba: deben ir antes del resource para
        // no caer en la ruta GET /consultaIA/{consultaIA} del resource.
        Route::get('consultaIA/archivos/{consultaId}', [ConsultaIAController::class, 'listarArchivos'])->name('consultaIA.listarArchivos'); // IA: lista archivos asociados a una consulta con IA
        Route::get('consultaIA/archivo/{id}/descargar', [ConsultaIAController::class, 'descargarArchivo'])->name('consultaIA.descargarArchivo'); // IA: descarga un archivo de la consulta con IA
        // Guarda la nota PSOAPP (borrador o final) y genera el PDF de
        // diagnóstico/receta. Igual que las de arriba, deben ir antes del
        // resource para que no las intercepte la ruta GET /consultaIA/{consultaIA}.
        Route::post('consultaIA/{consultaId}/psoapp', [ConsultaIAController::class, 'guardarPsoapp'])->name('consultaIA.guardarPsoapp'); // NUEVO // IA: guarda la nota PSOAPP generada/editada
        Route::get('consultaIA/{consultaId}/pdf/{tipo}', [ConsultaIAController::class, 'generarPdf'])->name('consultaIA.generarPdf'); // NUEVO // IA: genera PDF de diagnóstico/receta de la consulta con IA
        Route::post('consultaIA/{consultaId}/receta', [ConsultaIAController::class, 'guardarReceta'])->name('consultaIA.guardarReceta'); // ← NUEVA: guarda la receta de RecetaInteligente.vu
        // Historial clínico completo de un paciente (todas sus consultas +
        // transcripciones), usado por HistorialClinico.vue. Debe ser una ruta
        // top-level porque el frontend arma la URL como `route + '/historialClinico'`
        // (sin el prefijo consultaIA), así que no importa el orden respecto al
        // resource de abajo, pero se deja agrupada aquí por claridad.
        Route::get('historialClinico', [ConsultaIAController::class, 'historialClinico'])->name('consultaIA.historialClinico'); // IA: historial clínico completo generado por el módulo de IA
        Route::post('consultaIA/{consultaId}/finalizar', [ConsultaIAController::class, 'finalizarConsulta'])->name('consultaIA.finalizarConsulta'); // IA: cierra la consulta y bloquea más mensajes
        Route::resource('consultaIA', ConsultaIAController::class); // IA: CRUD principal del módulo de Consulta Inteligente (IA)
        Route::post('recetaInteligente', [ConsultaIAController::class, 'recetaInteligente'])->name('recetaInteligente'); // IA: genera receta con apoyo de IA
        Route::post('derivacionInteligente', [ConsultaIAController::class, 'derivacionInteligente'])->name('derivacionInteligente'); // IA: genera derivación con apoyo de IA
        Route::resource('medicos', MedicoController::class);
        Route::resource('ubicaciones', UbicacionController::class);// Agenda: CRUD de ubicaciones/sucursales
        Route::resource('movimientos',MovimientoInventarioController::class);
        Route::resource('triage', TriageController::class);
        Route::resource('archivoclinico', ArchivosClinicosController::class);
        //Route::resource('dashboard/citas', CitaController::class hola);
        // Route::get('dashboard/api/citas', [CitaController::class, 'getEventos']);//COMNTDAAAAA
        //Route::resource('dashboard/citas', CitaController::class);
        Route::get('dashboard/api/citas', [CitaController::class, 'getEventos']);// Agenda: eventos del calenda
        //Route::resource('consultas', ConsultaController::class)->except(['index']);
        Route::resource('citas', CitaController::class);// Agenda: CRUD de citas
        Route::get('/api/citas', [CitaController::class, 'getEventos']);// Agenda: eventos del calendario
        // Cambias 'SubirArchivosControlador' por el que ya tengas
        Route::post('archivoClinico', [ArchivosClinicosController::class, 'archivoclinico']);
        //Código para hacer el filtro de un paciente mediante un input //
        Route::get('buscarPaciente',[PacienteController::class,'filtrar_paciente']);
        //Codigo para las vistas y que son usadas en el menú de adminlte"
        //codigo  de las citas //
        //actualiza el estado 
        Route::patch('/citas/{cita}/estado', [App\Http\Controllers\CitaController::class, 'actualizarEstado'])->name('citas.estado');// Agenda: cambiar estado de cita
        // api de calendario//
        Route::get('/api/citas', [App\Http\Controllers\CitaController::class, 'getCitas']);
        //Ruta parametrizada para ver el detalle de un paciente en el expediente médico//
        Route::get('ExpedienteDetalle/{id}', [PacienteController::class, 'show'])
            ->name('ExpedienteDetalle');

        ///SECCION DE ACCESO A LAS VISTAS PARA ADMINISTRADOR - MEDICO - ASISTENTE ///
        Route::middleware(['auth', 'can:acceso-general'])->group(function() {
            // Antes: closure que solo hacía "return view('pacientes.index')" sin datos.
            // Ahora: pasa por el controlador para inyectar totalPacientes / totalPendientes / pacientesPendientes.
            Route::get('ListaPacientes', [PacienteController::class, 'lista'])->name('pacientes.index');
             Route::get('PacienteNuevo/{id?}', [PacienteController::class, 'create'])
           ->name('pacientes.create');
            Route::get('ExpedientePacientes', function() { return view('pacientes.expediente'); })->name('pacientes.create');
            
        });

        ///SECCION DE ACCESO A LAS VISTAS PARA ADMINISTRADOR - MEDICO///
            Route::middleware(['auth', 'can:acceso-medico-admin'])->group(function() {
            Route::get('/', function() { return view('dashboard'); })->name('dashboard');
            Route::get('Medicamentos', function() { return view('medicamentos.index'); })->name('medicamentos.index');
            Route::get('agregar-usuario',function(){ return view('configuracion-sistema.agregar-usuario');});
            Route::get('ExpedientePacientes', function() { return view('pacientes.expediente'); })->name('pacientes.expediente');
            // Ahora acepta un {id?} opcional: si viene, es el id de la consulta
            // a mostrar (usado por ExpedienteTabs.vue -> "Ver consulta completa").
            // Se dejó opcional para no romper otros lugares que ya enlazan a
            // esta ruta sin id.
            Route::get('HistorialConsulta/{id?}', function($id = null) { return view('consultas.consultaIndividual', compact('id')); })->name('consultas.consultaIndividual');
            Route::get('NuevaConsulta', [ConsultaController::class, 'create'])->name('consultas.create');
            //Route::get('NuevaConsulta', function () { return view('consultas.create'); })->name('consultas.create');
            Route::get('ConsultaInteligenteNueva', function() { 
                return view('consultas.consulta_inteligente', ['paciente' => null]);
            })->name('consultas.consulta_inteligente.nueva'); // IA: vista de Consulta Inteligente sin paciente asociado (nueva)
            Route::get('MedicosAlta',function(){return view('medicos.altamedicos'); })->name('medicos.altamedicos');
            Route::get('HistorialRecetas',function(){ return view('recetas.historial-recetas');})->name('recetas.historial-recetas');
            Route::get('TRIAGES', function() { return view('atencion-medica.triage'); })->name('atencion-medica.triage');
            Route::get('EvaluacionIa', function() { return view('atencion-medica.evaluacion-ia'); })->name('atencion-medica.evaluacion-ia'); // IA: vista de Evaluación con IA
            Route::get('ArchivosClinicos', function() { return view('atencion-medica.archivos-clinicos'); })->name('atencion-medica.archivos-clinicos');
            Route::get('Derivaciones', function() { return view('atencion-medica.derivaciones'); })->name('atencion-medica.derivaciones');
            Route::get('ListaConsultas', function () { return view('consultas.index'); })->name('consultas.index');
            Route::get('ConsultarEspecialidades',function(){ return view('specialties.index'); })->name('specialties.index');
            Route::get('RegistroMedico', function (){ return view('medicos.medicocreate'); })->name('medicos.medicocreate');
            Route::get('perfil',function(){ return view('configuracion-sistema.perfil'); })->name('configuracion-sistema.perfil');
            Route::get('cambiar-contraseña', function () { return view('configuracion-sistema.cambiar-contraseña'); })->name('configuracion-sistema.cambiar-contraseña');
            Route::get('Sucursales',function(){ return view('Ubicaciones.index'); })->name('Ubicaciones.index');
            Route::get('Agenda',function(){ return view('Citas.index'); })->name('Citas.index');
            Route::get('AgendarCitas',function(){ $datos = (new CitaController())->create(); return view('Citas.create',$datos); });
            Route::get('ExpedientePacientes/{id}', function ($id) {
                return view('pacientes.expediente');
            })->name('pacientes.expediente');
            Route::get('consultaNormal/{id}', function ($id) {
                return view('consultas.create');
            })->name('consultas.create');
            Route::get('ConsultaInteligente/{id}', function ($id) { 
                $paciente = Paciente::findOrFail($id);
                return view('consultas.consulta_inteligente', compact('paciente'));
            })->name('consultas.consulta_inteligente'); // IA: vista de Consulta Inteligente para un paciente específico
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

require __DIR__.'/auth.php';
