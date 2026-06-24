<?php

use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedicamentoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\RecetaDetalleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ConsultaIAController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\TriageController;
use App\Http\Controllers\ArchivosClinicosController;
use App\Http\Controllers\SpecialtyController; // <--- AGREGA ESTA LÍNEA
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CitaController;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return view('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
});

Route::resource('especialidades', SpecialtyController::class);
Route::resource('pacientes', PacienteController::class);
Route::resource('consultas', ConsultaController::class);
Route::resource('medicamentos', MedicamentoController::class);
Route::resource('recetas', RecetaController::class);
Route::resource('receta-detalles', RecetaDetalleController::class);
Route::resource('usuarios', UserController::class);
Route::resource('consultaIA', ConsultaIAController::class);
Route::resource('movimientos',MovimientoInventarioController::class);
Route::resource('triage', TriageController::class);
Route::resource('archivoclinico', ArchivosClinicosController::class);

   
// ==========================================
// 🛡️ SECCIÓN / PREFIJO PARA ADMINISTRADOR
// ==========================================
Route::prefix('admin')->middleware(['auth', 'rol:admin'])->group(function() {
    Route::get('/', function() { return view('dashboard'); })->name('dashboard');
    Route::resource('usuarios', UserController::class);
    Route::resource('medicamentos', MedicamentoController::class);
    Route::get('Medicamentos', function() { return view('medicamentos.index'); });
});


// ==========================================
// 📋 🩺 SECCIÓN COMPARTIDA (MÉDICO Y ASISTENTE)
// ==========================================
// 💡 Usamos 'can:rol-asistente-medico' para dar acceso a ambos roles sin romper tu middleware actual
Route::middleware(['auth', 'can:rol-asistente-medico'])->group(function() {
    
    Route::get('/dashboard', function() { return view('dashboard'); });

    // 👥 PACIENTES (Compartido)
    Route::resource('asistente/pacientes', PacienteController::class);
    Route::get('asistente/pacientes.index', function () {return view('pacientes.index');});
    Route::get('asistente/PacienteNuevo', function() { return view('pacientes.create'); });
    Route::get('asistente/ExpedientePacientes', function() { return view('pacientes.expediente'); });

    // 📅 CITAS / AGENDA (Compartido)
    Route::resource('dashboard/citas', CitaController::class);
    Route::get('dashboard/api/citas', [CitaController::class, 'getEventos']);

    // 👁️ CONSULTAS: Lista de Consultas (Compartido)
    Route::get('asistente/ListaConsultas', function () { return view('consultas.index'); });
    Route::get('medico/HistorialConsulta', function() { return view('consultas.consultaIndividual'); });
});


// ==========================================
// 🔒 SECCIÓN EXCLUSIVA PARA MÉDICO
// ==========================================
Route::prefix('medico')->middleware(['auth', 'rol:medico'])->group(function() {
    
    Route::resource('consultas', ConsultaController::class)->except(['index']);
    Route::resource('consultaIA', ConsultaIAController::class);
    
    Route::get('NuevaConsulta', function () { return view('consultas.create'); });
    Route::get('ConsultaInteligente', function() { return view('consultas.consulta_inteligente'); });

    Route::resource('medicamentos', MedicamentoController::class);
    Route::resource('recetas', RecetaController::class);
    Route::resource('receta-detalles', RecetaDetalleController::class);
    Route::get('Medicamentos', function() { return view('medicamentos.index'); });
    Route::resource('especialidades', SpecialtyController::class);
    
    //Codigo que lleva a HISTORIAL DE RECETAS
    Route::get('HistorialRecetas',function(){return view('recetas.historial-recetas');});
    Route::get('TRIAGE', function() { return view('atencion-medica.triage'); });
    Route::get('EvaluacionIa', function() { return view('atencion-medica.evaluacion-ia'); });
    Route::get('ArchivosClinicos', function() { return view('atencion-medica.archivos-clinicos'); });
    Route::get('Derivaciones', function() { return view('atencion-medica.derivaciones'); });
});

// ==========================================
// 📋 SECCIÓN / PREFIJO PARA ASISTENTE
// ==========================================
Route::prefix('asistente')->middleware(['auth', 'rol:asistente'])->group(function() {
    
    // Vista principal del Asistente
    Route::get('/', function() {
        return view('dashboard'); 
    });

    // Pacientes y Citas (Agenda)
    Route::resource('pacientes', PacienteController::class);
    Route::resource('citas', CitaController::class);
    Route::get('/api/citas', [CitaController::class, 'getEventos']);

    // 👁️ ÚNICO submódulo de consultas permitido: Lista de Consultas
    Route::get('ListaConsultas', function () { return view('consultas.index'); });
    
    // Soporte e historial básico
    //ruta que te dirije a lista de pacientes//
    Route::get('pacientes.index', function () {return view('pacientes.index');});
    Route::get('PacienteNuevo', function() { return view('pacientes.create'); });
    Route::get('ExpedientePacientes', function() { return view('pacientes.expediente'); });
    
});





// Cambias 'SubirArchivosControlador' por el que ya tengas
Route::post('archivoClinico', [ArchivosClinicosController::class, 'archivoclinico']);
//Código para hacer el filtro de un paciente mediante un input //
Route::get('buscarPaciente',[PacienteController::class,'filtrar_paciente']);
//Codigo para las vistas y que son usadas en el menú de adminlte"
Route::view('inicio','dashboard');
//Código que lleva a la vista para crear un nuevo paciente de forma manual///
Route::get('PacienteNuevo',function(){
          return view('pacientes.create');
});

//Código que lleva a la vista del expediente de un paciente//
Route::get('ExpedientePacientes/{id}', function ($id) {
    return view('pacientes.expediente');
});

//codigo para ruta parametrisada para consultanormal//
Route::get('consultaNormal/{id}', function ($id) {
    return view('consultas.create');
});

//Código que lleva a la vista de la consulta individual de un paciente//
Route::get('HistorialConsulta',function(){
          return view('consultas.consultaIndividual');
});

//Código que lleva a la consulta inteligente con el apoyo de la IA//
Route::get('ConsultaInteligente',function(){
          return view('consultas.consulta_inteligente');
});
//codigo  de las citas //
Route::resource('citas', App\Http\Controllers\CitaController::class);
// api de calendario//
Route::get('/api/citas', [App\Http\Controllers\CitaController::class, 'getEventos']);

//  especialidades //
Route::resource('specialties', SpecialtyController::class);

//Código que lleva a la vita de medicamentos e inventario//
Route::get('Medicamentos',function(){
          return view('medicamentos.index');
});

//Codigo que lleva a TRIAGE
Route::get('TRIAGE',function(){
          return view('atencion-medica.triage');
});
//Codigo que lleva a EVALUACION IA
Route::get('EvaluacionIa',function(){
          return view('atencion-medica.evaluacion-ia');
});

//Codigo que lleva a ARCHIVOS CLINICOS
Route::get('ArchivosClinicos',function(){
          return view('atencion-medica.archivos-clinicos');
});

//Codigo que lleva a DERIVACIONES
Route::get('Derivaciones',function(){
          return view('atencion-medica.derivaciones');
});
//Codigo que lleva a PERFIL
Route::get('perfil',function(){
          return view('configuracion-sistema.perfil');
});

//Codigo que lleva a CAMBIAR CONTRASEÑA
Route::get('cambiar-contraseña', function () {
    return view('configuracion-sistema.cambiar-contraseña');
});


Route::get('/prueba', function () {
    dd('FUNCIONA');
});

//Codigo que lleva a HISTORIAL DE RECETAS
Route::get('HistorialRecetas',function(){
          return view('recetas.historial-recetas');
});

//ruta que te dirige a  lista de consutas //
Route::get('consultas', function () {
    return view('consultas.index');
});

//Rutas parametrizadas (Sirve para hacer consultas entre diferentes 
// componentes es decir enviar datos entre la URL)//

//Ruta parametrizada para ver el detalle de un paciente en el expediente médico//
Route::get('ExpedienteDetalle/{id}', [PacienteController::class, 'show'])
       ->name('ExpedienteDetalle');
       
//Aqui termina la ruta parametrizada //


require __DIR__.'/auth.php';

Route::get('ListaConsultas', function () {
    return view('consultas.index');
});

// Route::get('NuevaConsulta', function () {
//     return view('consultas.create');
// });

//ruta de nuevo paciente //
Route::get('/pacientes/create', function () {
    return view('pacientes.create');
});



//ruta que te dirije a nueva consulta //
Route::get('/consultas/create', function () {
    return view('consultas.create');
});

require __DIR__.'/auth.php';


// Route::get('/consultas', function () {
//     return view('consultas.index');
// });


