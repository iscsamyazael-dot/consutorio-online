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
use App\Http\Controllers\ConsultaIAController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\UserRegisterController;
use App\Http\Controllers\UbicacionController;
use App\Models\Paciente;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;


Route::get('/', function () { return view('auth.login'); });

Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::post('/triage', [TriageController::class, 'store'])->name('triage.store');
        Route::get('/api/specialties', [SpecialtyController::class, 'list']);
        //Código para hacer el filtro de un paciente mediante un input //
        //Route::get('buscarPaciente',[PacienteController::class,'filtrar_paciente']);
        Route::get('/perfil-usuario', [ProfileController::class, 'obtenerPerfil']);
        //ACTUALIZA DATOS DEL PERFIL
        Route::put('/perfil-usuario', [ProfileController::class, 'actualizarPerfil']);
        Route::post('/cambiar-password', [ProfileController::class, 'updatePassword']);
        Route::get('/api/specialties', [SpecialtyController::class, 'list']);// Ruta API que obtiene la lista de especialidades médicas
        Route::post('/usuarios/registro', UserRegisterController::class);
        Route::get('/usuarios', [UserController::class, 'index']);
        Route::delete('usuarios/{id}', [UserController::class, 'destroy']);
        Route::get('/dashboard', function () { return view('dashboard'); })->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::post('/triage', [TriageController::class, 'store'])->name('triage.store');
        Route::get('/api/specialties', [SpecialtyController::class, 'list']);
        //Código para hacer el filtro de un paciente mediante un input //
        //Route::get('buscarPaciente',[PacienteController::class,'filtrar_paciente']);
        Route::get('/perfil-usuario', [ProfileController::class, 'obtenerPerfil']);
        //ACTUALIZA DATOS DEL PERFIL
        Route::put('/perfil-usuario', [ProfileController::class, 'actualizarPerfil']);
        Route::post('/cambiar-password', [ProfileController::class, 'updatePassword']);
        // Ruta para procesar el formulario y guardar el registro en las tablas
        
        //para traer actualizar y eliminar medicos
        // Route::get('buscarMedico/{id}', [MedicoController::class, 'show']);
        // Route::put('actualizarMedico/{id}', [MedicoController::class, 'update']);
        // Route::delete('eliminarMedico/{id}', [MedicoController::class, 'destroy']);



        ///*** RUTAS PARA LAS APIS Y CONSUMO DE DATOS */
        // Ruta API que obtiene la lista de especialidades médicas
        Route::get('medicoEstadistica', [MedicoController::class, 'obtenerEstadisticas']);
        Route::get('listaUbicaciones', [UbicacionController::class, 'listar']);
        Route::get('/api/especialidades', [SpecialtyController::class, 'list']); 
        Route::post('/medicos', [MedicoController::class, 'store'])->name('medicos.store');
        Route::get('/medicos-horarios', [MedicoController::class, 'index']);
        //ruta que filtra los medicos locales de la tabla 
        Route::get('buscarMedico', [MedicoController::class, 'filtrar_medico']);
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
        // que genera el resource de abajo -> Laravel respondía 405 Method Not Allowed.
        Route::post('consultaIA/archivo', [ConsultaIAController::class, 'subirArchivo'])->name('consultaIA.subirArchivo');
        // Listado y descarga de archivos clínicos para ArchivosClinicos.vue.
        // Mismo motivo que la de arriba: deben ir antes del resource para
        // no caer en la ruta GET /consultaIA/{consultaIA} del resource.
        Route::get('consultaIA/archivos/{consultaId}', [ConsultaIAController::class, 'listarArchivos'])->name('consultaIA.listarArchivos');
        Route::get('consultaIA/archivo/{id}/descargar', [ConsultaIAController::class, 'descargarArchivo'])->name('consultaIA.descargarArchivo');
        Route::resource('consultaIA', ConsultaIAController::class);
        Route::post('recetaInteligente', [ConsultaIAController::class, 'recetaInteligente'])->name('recetaInteligente');
        Route::post('derivacionInteligente', [ConsultaIAController::class, 'derivacionInteligente'])->name('derivacionInteligente');
        Route::resource('medicos', MedicoController::class);
        Route::resource('ubicaciones', UbicacionController::class);
        Route::resource('movimientos',MovimientoInventarioController::class);
        Route::resource('triage', TriageController::class);
        Route::resource('archivoclinico', ArchivosClinicosController::class);
        //Route::resource('dashboard/citas', CitaController::class hola);
        Route::get('dashboard/api/citas', [CitaController::class, 'getEventos']);
        //Route::resource('consultas', ConsultaController::class)->except(['index']);
        Route::resource('citas', CitaController::class);
        Route::get('/api/citas', [CitaController::class, 'getEventos']);
        // Cambias 'SubirArchivosControlador' por el que ya tengas
        Route::post('archivoClinico', [ArchivosClinicosController::class, 'archivoclinico']);
        //Código para hacer el filtro de un paciente mediante un input //
        Route::get('buscarPaciente',[PacienteController::class,'filtrar_paciente']);
        //Codigo para las vistas y que son usadas en el menú de adminlte"
        //codigo  de las citas //
        //actualiza el estado 
        Route::patch('/citas/{cita}/estado', [App\Http\Controllers\CitaController::class, 'actualizarEstado'])->name('citas.estado');
        // api de calendario//
        Route::get('/api/citas', [App\Http\Controllers\CitaController::class, 'getCitas']);
        //Ruta parametrizada para ver el detalle de un paciente en el expediente médico//
        Route::get('ExpedienteDetalle/{id}', [PacienteController::class, 'show'])
            ->name('ExpedienteDetalle');
        ///*** AQUI TERMINA LAS RUTAS DE LAS LAS APIS Y CONSUMO DE DATOS */

        //**INICIA LAS RUTAS PARA LAS VISTAS DE ACUERDO AL ACESSO DE CADA USUARIO *//

        ///SECCION DE ACCESO A LAS VISTAS PARA ADMINISTRADOR - MEDICO - ASISTENTE ///
        Route::middleware(['auth', 'can:acceso-general'])->group(function() {
            Route::get('ListaPacientes', function () { return view('pacientes.index'); })->name('pacientes.index');
            Route::get('PacienteNuevo', function() { return view('pacientes.create'); })->name('pacientes.create');
            Route::get('ExpedientePacientes', function() { return view('pacientes.expediente'); })->name('pacientes.create');
            
        });

        ///SECCION DE ACCESO A LAS VISTAS PARA ADMINISTRADOR - MEDICO///
            Route::middleware(['auth', 'can:acceso-medico-admin'])->group(function() {
            Route::get('/', function() { return view('dashboard'); })->name('dashboard');
            Route::get('Medicamentos', function() { return view('medicamentos.index'); })->name('medicamentos.index');
            Route::get('agregar-usuario',function(){ return view('configuracion-sistema.agregar-usuario');});
            Route::get('ExpedientePacientes', function() { return view('pacientes.expediente'); })->name('pacientes.expediente');
            Route::get('HistorialConsulta', function() { return view('consultas.consultaIndividual'); })->name('consultas.consultaIndividual');
            Route::get('NuevaConsulta', function () { return view('consultas.create'); })->name('consultas.create');
            Route::get('ConsultaInteligenteNueva', function() { 
                return view('consultas.consulta_inteligente', ['paciente' => null]);
            })->name('consultas.consulta_inteligente.nueva');
            Route::get('MedicosAlta',function(){return view('medicos.altamedicos'); })->name('medicos.altamedicos');
            Route::get('HistorialRecetas',function(){ return view('recetas.historial-recetas');})->name('recetas.historial-recetas');
            Route::get('TRIAGES', function() { return view('atencion-medica.triage'); })->name('atencion-medica.triage');
            Route::get('EvaluacionIa', function() { return view('atencion-medica.evaluacion-ia'); })->name('atencion-medica.evaluacion-ia');
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
            })->name('consultas.consulta_inteligente');
        });

});

Route::view('inicio', 'dashboard');

require __DIR__.'/auth.php';