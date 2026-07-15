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
use App\Http\Controllers\UbicacionController;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () { return view('auth.login'); });

Route::middleware('auth')->group(function () {
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
        Route::resource('consultaIA', ConsultaIAController::class);
        Route::post('recetaInteligente', [ConsultaIAController::class, 'recetaInteligente'])->name('recetaInteligente');
        Route::post('derivacionInteligente', [ConsultaIAController::class, 'derivacionInteligente'])->name('derivacionInteligente');
        Route::resource('medicos', MedicoController::class);
        Route::resource('ubicaciones', UbicacionController::class);
        Route::resource('movimientos',MovimientoInventarioController::class);
        Route::resource('triage', TriageController::class);
        Route::resource('archivoclinico', ArchivosClinicosController::class);
        //Route::resource('dashboard/citas', CitaController::class);
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
            Route::get('ExpedientePacientes', function() { return view('pacientes.expediente'); })->name('pacientes.expediente');
            Route::get('HistorialConsulta', function() { return view('consultas.consultaIndividual'); })->name('consultas.consultaIndividual');
            Route::get('NuevaConsulta', function () { return view('consultas.create'); })->name('consultas.create');
            Route::get('ConsultaInteligente', function() { return view('consultas.consulta_inteligente'); })->name('consultas.consulta_inteligente');
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
        });

});

// Route::prefix('medico')->middleware(['auth', 'rol:medico'])->group(function() {
//      Route::get('/dashboard', function() { return view('dashboard'); })->name('dashboard');
// });

// ==========================================
// 🛡️ SECCIÓN / PREFIJO PARA ADMINISTRADOR
// ==========================================
Route::prefix('admin')->middleware(['auth', 'rol:admin'])->group(function () {
    Route::get('/', function () { return view('dashboard'); })->name('dashboard');
    Route::get('Medicamentos', function () { return view('medicamentos.index'); });
});


// ==========================================
// 📋 🩺 SECCIÓN COMPARTIDA (MÉDICO Y ASISTENTE)
// ==========================================
// Ruta "oficial" para pacientes / consultas compartidas entre médico y asistente.
Route::middleware(['auth', 'can:rol-asistente-medico'])->group(function () {

    // 👥 PACIENTES (Compartido)
    Route::get('asistente/pacientes.index', function () { return view('pacientes.index'); });
    Route::get('asistente/PacienteNuevo', function () { return view('pacientes.create'); });
    Route::get('asistente/ExpedientePacientes', function () { return view('pacientes.expediente'); });

    // 👁️ CONSULTAS: Lista de Consultas (Compartido)
    Route::get('asistente/ListaConsultas', function () { return view('consultas.index'); });
    Route::get('medico/HistorialConsulta', function () { return view('consultas.consultaIndividual'); });
});
// 💡 Usamos 'can:rol-asistente-medico' para dar acceso a ambos roles sin romper tu middleware actual
// Route::middleware(['auth', 'can:rol-asistente-medico'])->group(function() {
//     Route::get('/dashboard', function() { return view('dashboard'); });
//     Route::get('asistente/pacientes.index', function () {return view('pacientes.index');});
//     Route::get('asistente/PacienteNuevo', function() { return view('pacientes.create'); });
//     Route::get('asistente/ExpedientePacientes', function() { return view('pacientes.expediente'); });
//     Route::get('asistente/ListaConsultas', function () { return view('consultas.index'); });
//     Route::get('medico/HistorialConsulta', function() { return view('consultas.consultaIndividual'); });
// });

// ==========================================
// 🔒 SECCIÓN EXCLUSIVA PARA MÉDICO
// ==========================================
Route::prefix('medico')->middleware(['auth', 'rol:medico'])->group(function () {
    Route::get('NuevaConsulta', function () { return view('consultas.create'); });
    Route::get('ConsultaInteligente', function () { return view('consultas.consulta_inteligente'); });
    Route::get('Medicamentos', function () { return view('medicamentos.index'); });
    Route::get('MedicosAlta', function () { return view('medicos.altamedicos'); });
    Route::get('HistorialRecetas', function () { return view('recetas.historial-recetas'); });
    Route::get('TRIAGE', function () { return view('atencion-medica.triage'); });
    Route::get('EvaluacionIa', function () { return view('atencion-medica.evaluacion-ia'); });
    Route::get('ArchivosClinicos', function () { return view('atencion-medica.archivos-clinicos'); });
    Route::get('Derivaciones', function () { return view('atencion-medica.derivaciones'); });
});


// ==========================================
// 📋 SECCIÓN / PREFIJO PARA ASISTENTE
// ==========================================
// NOTA: Los módulos de pacientes / lista de consultas / expediente ahora
// viven únicamente en el grupo compartido de arriba (can:rol-asistente-medico)
// para evitar rutas duplicadas. El asistente ya tiene acceso a ellas por ese grupo.
Route::prefix('asistente')->middleware(['auth', 'rol:asistente'])->group(function () {

    // Vista principal del Asistente
    Route::get('/', function () {
        return view('dashboard');
    });

    // Citas (Agenda) — exclusivo de este grupo
    Route::get('/api/citas', [CitaController::class, 'getEventos']);
});
// Route::prefix('asistente')->middleware(['auth', 'rol:asistente'])->group(function() {
//     // Vista principal del Asistente
//     Route::get('/', function() {return view('dashboard');});
//     // Pacientes y Citas (Agenda)
//     // 👁️ ÚNICO submódulo de consultas permitido: Lista de Consultas
//     Route::get('ListaConsultas', function () { return view('consultas.index'); });
//     //ruta que te dirije a lista de pacientes//
//     Route::get('PacienteNuevo', function() { return view('pacientes.create'); });
//     Route::get('ExpedientePacientes', function() { return view('pacientes.expediente'); });
    
// });


// ==========================================
// 🌐 RUTAS GENERALES
// ==========================================

Route::view('inicio', 'dashboard');

Route::get('/prueba', function () {
    dd('FUNCIONA');
});

Route::get('Sucursales', function () {
    return view('ubicaciones.index');
});

// //Código que lleva a la vista para crear un nuevo paciente de forma manual///
// Route::get('PacienteNuevo',function(){
//           return view('pacientes.create');
// });

//Código que lleva a la vista del expediente de un paciente//
// Route::get('ExpedientePacientes/{id}', function ($id) {
//     return view('pacientes.expediente');
// });

//codigo para ruta parametrisada para consultanormal//
// Route::get('consultaNormal/{id}', function ($id) {
//     return view('consultas.create');
// });


//Código que lleva a la vista de la consulta individual de un paciente//
// Route::get('HistorialConsulta',function(){
//           return view('consultas.consultaIndividual');
// });

//Código que lleva a la consulta inteligente con el apoyo de la IA//
// Route::get('ConsultaInteligente',function(){
//           return view('consultas.consulta_inteligente');
// });

//Código que lleva a la vita de medicamentos e inventario//
// Route::get('Medicamentos',function(){
//           return view('medicamentos.index');
// });

//Codigo que lleva a TRIAGE
// Route::get('TRIAGES',function(){
//           return view('atencion-medica.triage');
// });
//Codigo que lleva a EVALUACION IA
// Route::get('EvaluacionIa',function(){
//           return view('atencion-medica.evaluacion-ia');
// });

//Codigo que lleva a ARCHIVOS CLINICOS
// Route::get('ArchivosClinicos',function(){
//           return view('atencion-medica.archivos-clinicos');
// });

//Codigo que lleva a DERIVACIONES
// Route::get('Derivaciones',function(){
//           return view('atencion-medica.derivaciones');
// });
//RUTA QUE LLEVA A MEDICOS
// Route::get('MedicosAlta',function(){
//           return view('medicos.altamedicos');
// });

// RUTA QUE LLEVA A LA VISTA DE REGISTRAR UN MEDICO
// Route::get('RegistroMedico', function (){
//     return view('medicos.medicocreate');
// });

//Codigo que lleva a PERFIL
// Route::get('perfil',function(){
//           return view('configuracion-sistema.perfil');
// });

//Codigo que lleva a CAMBIAR CONTRASEÑA
// Route::get('cambiar-contraseña', function () {
//     return view('configuracion-sistema.cambiar-contraseña');
// });

//Codigo que lleva a HISTORIAL DE RECETAS
// Route::get('HistorialRecetas',function(){
//           return view('recetas.historial-recetas');
// });

//ruta que te dirige a  lista de consutas //
// Route::get('ListaConsultas', function () {
//     return view('consultas.index');
// });

//Rutas parametrizadas (Sirve para hacer consultas entre diferentes 
// componentes es decir enviar datos entre la URL)//


//Aqui termina la ruta parametrizada //

//ruta de nuevo paciente //
// Route::get('/pacientes/create', function () {
//     return view('pacientes.create');
// });


//ruta que te dirije a nueva consulta //
// Route::get('/consultas/create', function () {
//     return view('consultas.create');
// });


//codigo de ubicaciones nuevas rutas  (sucursales)//
// Route::get('/ubicaciones/listar', [App\Http\Controllers\UbicacionController::class, 'listar'])
//     ->name('ubicaciones.listar');


require __DIR__.'/auth.php';