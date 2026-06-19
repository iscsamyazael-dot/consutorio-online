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

use App\Http\Controllers\ProfileController;
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
    Route::post('/triage', [TriageController::class, 'store']) ->name('triage.store');
});

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

//Código para hacer el filtro de un paciente mediante un input //
//Route::get('buscarPaciente',[PacienteController::class,'filtrar_paciente']);


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


Route::get('HistorialRecetas',function(){
          return view('recetas.historial-recetas');
});


Route::get('ListaConsultas', function () {
    return view('consultas.index');
});
//Rutas parametrizadas (Sirve para hacer consultas entre diferentes 
// componentes es decir enviar datos entre la URL)//

//Ruta parametrizada para ver el detalle de un paciente en el expediente médico//
Route::get('ExpedienteDetalle/{id}', [PacienteController::class, 'show'])
       ->name('ExpedienteDetalle');
//Aqui termina la ruta parametrizada //


require __DIR__.'/auth.php';

Route::get('NuevaConsulta', function () {
    return view('consultas.create');
});

//ruta de nuevo paciente //
Route::get('/pacientes/create', function () {
    return view('pacientes.create');
});

Route::get('/pacientes.index', function () {
    return view('pacientes.index');
});

