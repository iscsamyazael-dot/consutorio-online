<?php

use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedicamentoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\RecetaDetalleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ConsultaIAController;

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;

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
});

Route::resource('pacientes', PacienteController::class);
Route::resource('consultas', ConsultaController::class);
Route::resource('medicamentos', MedicamentoController::class);
Route::resource('recetas', RecetaController::class);
Route::resource('receta-detalles', RecetaDetalleController::class);
Route::resource('usuarios', UserController::class);
Route::resource('consultaIA', ConsultaIAController::class);



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
Route::get('/api/citas', [App\Http\Controllers\CitaController::class, 'getCitas']);

require __DIR__.'/auth.php';

Route::get('/test-whatsapp', function () {

    WhatsAppService::enviar(
        '529991234567',
        'Hola, prueba de WhatsApp desde Laravel'
    );

    return 'Mensaje enviado';
});
Route::get('/probar-n8n', function () {

    $response = Http::withoutVerifying()->post(
        'https://luna110604.app.n8n.cloud/webhook-test/d5aca0e1-0db0-4105-9712-cafc5e5d947c',
        [
            'mensaje' => 'Hola desde Laravel',
            'modulo' => 'agendas',
            'estado' => 'funcionando'
        ]
    );

    return $response->json();
});
