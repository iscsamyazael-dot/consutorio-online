<?php

use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedicamentoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\RecetaDetalleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ConsultaIAController; 
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\SpecialtyController;

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::resource('especialidades', SpecialtyController::class);

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/api/specialties', [SpecialtyController::class, 'list']);// Ruta API que obtiene la lista de especialidades médicas
    // Ruta para procesar el formulario y guardar el registro en las tablas
    Route::post('/medicos', [MedicoController::class, 'store'])->name('medicos.store');
    Route::get('/medicos-horarios', [MedicoController::class, 'index']);
    //ruta que filtra los medicos locales de la tabla 
    Route::get('buscarMedico', [MedicoController::class, 'filtrar_medico']);
    //para traer actualizar y eliminar medicos
    // Route::get('buscarMedico/{id}', [MedicoController::class, 'show']);
    // Route::put('actualizarMedico/{id}', [MedicoController::class, 'update']);
    // Route::delete('eliminarMedico/{id}', [MedicoController::class, 'destroy']);
    Route::get('/api/specialties', [SpecialtyController::class, 'list']); // Ruta API que obtiene la lista de especialidades médicas
});

Route::resource('pacientes', PacienteController::class);
Route::resource('consultas', ConsultaController::class);
Route::resource('medicamentos', MedicamentoController::class);
Route::resource('recetas', RecetaController::class);
Route::resource('receta-detalles', RecetaDetalleController::class);
Route::resource('usuarios', UserController::class);
Route::resource('consultaIA', ConsultaIAController::class);
Route::resource('medicos', MedicoController::class);
Route::resource('especialidades', SpecialtyController::class);




//Codigo para las vistas y que son usadas en el menú de adminlte"
Route::view('inicio','dashboard');
//Código que lleva a la vista para crear un nuevo paciente de forma manual///
Route::get('PacienteNuevo',function(){
          return view('pacientes.create');
});
//Código que lleva a la vista del expediente de un paciente//
Route::get('ExpedientePacientes',function(){
          return view('pacientes.expediente');
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

//  especialidades — CRUD vía JSON, todo bajo /api //
Route::prefix('api')->group(function () {
    Route::get('/specialties', [SpecialtyController::class, 'list']);
    Route::post('/specialties', [SpecialtyController::class, 'store']);
    Route::put('/specialties/{specialty}', [SpecialtyController::class, 'update']);
    Route::delete('/specialties/{specialty}', [SpecialtyController::class, 'destroy']);
});

//Código que lleva a la vita de medicamentos e inventario//
Route::get('Medicamentos',function(){
          return view('medicamentos.index');
});



//Codigo que lleva a TRIAGE
Route::get('TRIAGE',function(){
          return view('atencion-medica.triage');
});

Route::get('EvaluacionIa',function(){
          return view('atencion-medica.evaluacion-ia');
});


Route::get('ArchivosClinicos',function(){
          return view('atencion-medica.archivos-clinicos');
});


Route::get('Derivaciones',function(){
          return view('atencion-medica.derivaciones');
});
//RUTA QUE LLEVA A MEDICOS
Route::get('MedicosAlta',function(){
          return view('medicos.altamedicos');
});

// RUTA QUE LLEVA A LA VISTA DE REGISTRAR UN MEDICO
Route::get('RegistroMedico', function () {
    return view('medicos.medicocreate');
});




require __DIR__.'/auth.php';


// Route::get('/consultas', function () {
//     return view('consultas.index');
// });

Route::get('ListaConsultas', function () {
    return view('consultas.index');
});

Route::get('NuevaConsulta', function () {
    return view('consultas.create');
});

//ruta de nuevo paciente //
Route::get('/pacientes/create', function () {
    return view('pacientes.create');
});