<?php

use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedicamentoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\RecetaDetalleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ConsultaIAController;
use App\Http\Controllers\CitaController;

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('/dashboard', 'dashboard')->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::resource('pacientes', PacienteController::class);
Route::resource('citas', CitaController::class);
Route::resource('consultas', ConsultaController::class);
Route::resource('medicamentos', MedicamentoController::class);
Route::resource('recetas', RecetaController::class);
Route::resource('receta-detalles', RecetaDetalleController::class);
Route::resource('usuarios', UserController::class);
Route::resource('consultaIA', ConsultaIAController::class);



//Codigo para las vistas y que son usadas en el menú de adminlte"
Route::view('inicio','dashboard');
//Código que lleva a la vista para crear un nuevo paciente de forma manual///
Route::view('PacienteNuevo', 'pacientes.create');
//Código que lleva a la vista del expediente de un paciente//
Route::view('ExpedientePacientes', 'pacientes.expediente');

//Código que lleva a la vista de la consulta individual de un paciente//
Route::view('HistorialConsulta', 'consultas.consultaIndividual');

//Código que lleva a la consulta inteligente con el apoyo de la IA//
Route::view('ConsultaInteligente', 'consultas.consulta_inteligente');

Route::view('citas', 'agenda.citas')->middleware('auth');
Route::view('programar-cita', 'agenda.programar_cita')->middleware('auth');

require __DIR__.'/auth.php';
