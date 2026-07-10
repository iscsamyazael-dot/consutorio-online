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
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CitaController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PerfilController;


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
    Route::get('/perfil-usuario', [ProfileController::class, 'obtenerPerfil']);
    //ACTUALIZA DATOS DEL PERFIL
    Route::put('/perfil-usuario', [ProfileController::class, 'actualizarPerfil']);
    Route::post('/cambiar-password', [ProfileController::class, 'updatePassword']);

    Route::post('/triage', [TriageController::class, 'store'])->name('triage.store');
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


   
// ==========================================
// 🛡️ SECCIÓN / PREFIJO PARA ADMINISTRADOR
// ==========================================
Route::prefix('admin')->middleware(['auth', 'rol:admin'])->group(function () {

    Route::get('/', fn() => view('dashboard'))->name('admin.dashboard');

    Route::view('Medicamentos', 'medicamentos.index');

    // El Administrador gestiona los usuarios del sistema
    Route::resource('usuarios', UserController::class);
    
  
});


// ==========================================
// 🩺 SECCIÓN / PREFIJO PARA MÉDICO
// ==========================================
Route::prefix('medico')->middleware(['auth', 'rol:medico'])->group(function() {
    
    // Vista principal del Médico (puedes apuntarla a una vista de inicio médica)
    Route::get('/', function() {
        return view('dashboard'); 
    });

    // Recursos compartidos pero con su flujo de médico
    Route::resource('pacientes', PacienteController::class);
    Route::resource('citas', CitaController::class);
    Route::get('/api/citas', [CitaController::class, 'getEventos']);

    // Módulo Consultas Completo (Los 2 submódulos + IA)
    Route::resource('consultas', ConsultaController::class);
    Route::resource('consultaIA', ConsultaIAController::class);
    Route::get('ListaConsultas', function () { return view('consultas.index'); }); // Submódulo 1
    Route::get('NuevaConsulta', function () { return view('consultas.create'); });   // Submódulo 2
    Route::get('HistorialConsulta', function() { return view('consultas.consultaIndividual'); });
    Route::get('ConsultaInteligente', function() { return view('consultas.consulta_inteligente'); });

    // Recetas y Medicamentos
    Route::resource('medicamentos', MedicamentoController::class);
    Route::resource('recetas', RecetaController::class);
    Route::resource('receta-detalles', RecetaDetalleController::class);
    Route::get('Medicamentos', function() { return view('medicamentos.index'); });

    // Atención Médica (Triage, Eval IA, Archivos, Derivaciones)
    Route::get('TRIAGE', function() { return view('atencion-medica.triage'); });
    Route::get('EvaluacionIa', function() { return view('atencion-medica.evaluacion-ia'); });
    Route::get('ArchivosClinicos', function() { return view('atencion-medica.archivos-clinicos'); });
    Route::get('Derivaciones', function() { return view('atencion-medica.derivaciones'); });
    
    // (Si tienes un controlador de especialidades, lo agregarías aquí)
});

// ==========================================
// 📋 SECCIÓN / PREFIJO PARA ASISTENTE
// ==========================================
Route::prefix('asistente')->middleware(['auth', 'rol:asistente'])->group(function() {
    
    // Vista principal del Asistente
    Route::get('/', function() {
        return view('dashboard'); 
    });

    // 👁️ ÚNICO submódulo de consultas permitido: Lista de Consultas
    Route::get('ListaConsultas', function () { return view('consultas.index'); });
    
    // Soporte e historial básico
    Route::get('PacienteNuevo', function() { return view('pacientes.create'); });
    Route::get('PacienteNuevo', function() { return view('pacientes.index'); });
    Route::get('ExpedientePacientes', function() { return view('pacientes.expediente'); });
    
});





// Ruta inteligente para el listado de Citas
Route::get('dashboard/citas', function () {
    $rol = auth()->user()->rol;
    return redirect()->to($rol . '/citas');
})->middleware('auth');

// Ruta inteligente para Crear Cita
Route::get('dashboard/citas/create', function () {
    $rol = auth()->user()->rol;
    return redirect()->to($rol . '/citas/create');
})->middleware('auth');

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

//Codigo que lleva a PERFIL
Route::get('agregar-usuario',function(){
          return view('configuracion-sistema.agregar-usuario');
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


require __DIR__.'/auth.php';


// Route::get('/consultas', function () {
//     return view('consultas.index');
// });


