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

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/triage', [TriageController::class, 'store'])->name('triage.store');

    // Perfil de usuario
    Route::get('/perfil-usuario', [ProfileController::class, 'obtenerPerfil']);
    Route::put('/perfil-usuario', [ProfileController::class, 'actualizarPerfil']);
    Route::post('/cambiar-password', [ProfileController::class, 'updatePassword']);

    // Médicos
    Route::post('/medicos', [MedicoController::class, 'store'])->name('medicos.store');
    Route::get('/medicos-horarios', [MedicoController::class, 'index']);
    Route::get('buscarMedico', [MedicoController::class, 'filtrar_medico']);

    // API de especialidades médicas
    // NOTA: '/api/specialties' y '/api/especialidades' apuntan al mismo método.
    // Se dejan ambas por si el frontend usa las dos, pero valdría la pena
    // unificar a una sola en el futuro.
    Route::get('/api/specialties', [SpecialtyController::class, 'list']);
    Route::get('/api/especialidades', [SpecialtyController::class, 'list']);
});

Route::resource('especialidades', SpecialtyController::class);
Route::resource('pacientes', PacienteController::class);
Route::resource('consultas', ConsultaController::class);
Route::resource('medicamentos', MedicamentoController::class);
Route::resource('recetas', RecetaController::class);
Route::resource('receta-detalles', RecetaDetalleController::class);
Route::resource('usuarios', UserController::class);
Route::resource('consultaIA', ConsultaIAController::class);
Route::resource('medicos', MedicoController::class);
Route::resource('ubicaciones', UbicacionController::class);
Route::resource('movimientos', MovimientoInventarioController::class);
Route::resource('triage', TriageController::class);
Route::resource('archivoclinico', ArchivosClinicosController::class);
Route::resource('citas', CitaController::class);
Route::resource('usuarios', UserController::class);
Route::resource('medicamentos', MedicamentoController::class);
Route::resource('asistente/pacientes', PacienteController::class);
Route::resource('dashboard/citas', CitaController::class);
Route::resource('consultas', ConsultaController::class)->except(['index']);
Route::resource('consultaIA', ConsultaIAController::class);
Route::resource('medicamentos', MedicamentoController::class);
Route::resource('recetas', RecetaController::class);
Route::resource('receta-detalles', RecetaDetalleController::class);
 Route::resource('citas', CitaController::class);

Route::patch('/citas/{cita}/estado', [CitaController::class, 'actualizarEstado'])->name('citas.estado');
Route::get('/api/citas', [CitaController::class, 'getCitas']);

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

    Route::get('/dashboard', function () { return view('dashboard'); });

    // 👥 PACIENTES (Compartido)
    Route::get('asistente/pacientes.index', function () { return view('pacientes.index'); });
    Route::get('asistente/PacienteNuevo', function () { return view('pacientes.create'); });
    Route::get('asistente/ExpedientePacientes', function () { return view('pacientes.expediente'); });

    // 📅 CITAS / AGENDA (Compartido)
    Route::get('dashboard/api/citas', [CitaController::class, 'getEventos']);

    // 👁️ CONSULTAS: Lista de Consultas (Compartido)
    Route::get('asistente/ListaConsultas', function () { return view('consultas.index'); });
    Route::get('medico/HistorialConsulta', function () { return view('consultas.consultaIndividual'); });
});

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

// ==========================================
// 🌐 RUTAS GENERALES
// ==========================================

Route::post('archivoClinico', [ArchivosClinicosController::class, 'archivoclinico']);
Route::get('buscarPaciente', [PacienteController::class, 'filtrar_paciente']);
Route::view('inicio', 'dashboard');

Route::get('PacienteNuevo', function () {
    return view('pacientes.create');
});

Route::get('ExpedientePacientes/{id}', function ($id) {
    return view('pacientes.expediente');
});

Route::get('consultaNormal/{id}', function ($id) {
    return view('consultas.create');
});

Route::get('HistorialConsulta', function () {
    return view('consultas.consultaIndividual');
});

Route::get('ConsultaInteligente', function () {
    return view('consultas.consulta_inteligente');
});

Route::get('ConsultarEspecialidades', function () {
    return view('specialties.index');
});

Route::get('Medicamentos', function () {
    return view('medicamentos.index');
});

Route::get('TRIAGE', function () {
    return view('atencion-medica.triage');
});

Route::get('EvaluacionIa', function () {
    return view('atencion-medica.evaluacion-ia');
});

Route::get('ArchivosClinicos', function () {
    return view('atencion-medica.archivos-clinicos');
});

Route::get('Derivaciones', function () {
    return view('atencion-medica.derivaciones');
});

Route::get('MedicosAlta', function () {
    return view('medicos.altamedicos');
});

Route::get('RegistroMedico', function () {
    return view('medicos.medicocreate');
});

Route::get('perfil', function () {
    return view('configuracion-sistema.perfil');
});

Route::get('cambiar-contraseña', function () {
    return view('configuracion-sistema.cambiar-contraseña');
});

Route::get('/prueba', function () {
    dd('FUNCIONA');
});

Route::get('HistorialRecetas', function () {
    return view('recetas.historial-recetas');
});

// Ruta parametrizada para ver el detalle de un paciente en el expediente médico
Route::get('ExpedienteDetalle/{id}', [PacienteController::class, 'show'])
    ->name('ExpedienteDetalle');

Route::get('Sucursales', function () {
    return view('ubicaciones.index');
});

require __DIR__.'/auth.php';