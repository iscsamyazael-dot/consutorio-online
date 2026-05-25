@extends('adminlte::page')

@section('title', 'Nueva Consulta')

@section('content_header')
    {{-- Encabezado estilizado fuera de la tarjeta principal --}}
    <div class="d-flex justify-content-between align-items-center py-2">
        <h1 class="text-dark font-weight-bold" style="font-size: 1.8rem;">
            <i class="fas fa-user-plus text-primary mr-2"></i>Alta de Nuevo Paciente
        </h1>
        <a href="{{ route('pacientes.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
        </a>
    </div>
<div class="premium-header">
    <div>
        <h1>
            <i class="fas fa-heartbeat heartbeat-icon"></i>
            Nueva Consulta Médica
        </h1>
        <p>
            Plataforma clínica inteligente • Sistema Hospitalario Premium
        </p>
    </div>
    <div class="live-status">
        <span class="pulse-dot"></span>
        Consulta Activa
    </div>
</div>
@stop
<input type="hidden" name="route" value="{{ url('/') }}">
@section('content')
<div class="container-fluid">
    <div class="row g-4">

@section('content') 
    {{-- Contenedor con padding extra abajo --}}
    <div id="app">
        <medicamentos-inventario></medicamentos-inventario>
    </div>
    <div class="container-fluid pb-5">
        <div class="card shadow border-0 rounded-lg">
            
            <form action="{{ route('pacientes.store') }}" method="POST">
                @csrf

                <div class="card-body p-4 p-md-5">
                    
                    {{-- SECCIÓN 1: DATOS PERSONALES --}}
                    <div class="mb-5">
                        <div class="d-flex align-items-center mb-4 pb-2 border-b-section">
                            <span class="icon-shape bg-primary-faded text-primary rounded-circle mr-3">
                                <i class="fas fa-address-card"></i>
                            </span>
                            <h5 class="text-uppercase text-muted font-weight-bold m-0 tracking-wider" style="font-size: 0.9rem;">
                                Información Personal y de Contacto
                            </h5>
                        </div>

                        <div class="row custom-form-group">
                            <div class="col-md-6">
                                <label for="nombre">Nombre(s) <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" 
                                    class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej. Juan" required>
                                @error('nombre') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="apellidos">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" name="apellidos" id="apellidos" value="{{ old('apellidos') }}" 
                                    class="form-control @error('apellidos') is-invalid @enderror" placeholder="Ej. Pérez Gómez" required>
                                @error('apellidos') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row custom-form-group">
                            <div class="col-md-4">
                                <label for="fecha_nacimiento">Fecha de nacimiento <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" 
                                    class="form-control @error('fecha_nacimiento') is-invalid @enderror" required>
                                @error('fecha_nacimiento') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="sexo">Sexo <span class="text-danger">*</span></label>
                                <select name="sexo" id="sexo" class="form-control @error('sexo') is-invalid @enderror" required>
                                    <option value="" disabled {{ old('sexo') ? '' : 'selected' }}>Seleccione...</option>
                                    <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Masculino</option>
                                    <option value="F" {{ old('sexo') == 'F' ? 'selected' : '' }}>Femenino</option>
                                    <option value="O" {{ old('sexo') == 'O' ? 'selected' : '' }}>Otro</option>
                                </select>
                                @error('sexo') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="telefono">Teléfono</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0 text-muted"><i class="fas fa-phone-alt"></i></span>
                                    </div>
                                    <input type="tel" name="telefono" id="telefono" value="{{ old('telefono') }}" 
                                        class="form-control pl-2 @error('telefono') is-invalid @enderror border-left-0" placeholder="999 123 4567">
                                </div>
                                @error('telefono') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row custom-form-group">
                            <div class="col-md-6">
                                <label for="email">Correo electrónico</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0 text-muted"><i class="fas fa-envelope"></i></span>
                                    </div>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" 
                                        class="form-control pl-2 @error('email') is-invalid @enderror border-left-0" placeholder="usuario@correo.com">
                                </div>
                                @error('email') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="direccion">Dirección</label>
                                <input type="text" name="direccion" id="direccion" value="{{ old('direccion') }}" 
                                    class="form-control @error('direccion') is-invalid @enderror" placeholder="Calle, Número, Colonia">
                                @error('direccion') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>


                    {{-- SECCIÓN 2: EXPEDIENTE CLÍNICO --}}
                    <div class="p-4 rounded-lg bg-gray-50 border border-gray-150 shadow-inner-sm">
                        <div class="d-flex align-items-center mb-4">
                            <span class="icon-shape bg-teal-faded text-teal rounded-circle mr-3">
                                <i class="fas fa-file-medical-alt"></i>
                            </span>
                            <div>
                                <h4 class="text-dark font-weight-bold m-0" style="font-size: 1.4rem;">Expediente Médico Inicial</h4>
                                <p class="text-muted text-sm m-0">Diagnóstico preliminar o notas de importancia clínica.</p>
                            </div>
                        </div>

                        <div class="form-group text-muted font-weight-bold" style="font-size: 0.85rem;">
                            <label for="observaciones" class="text-uppercase tracking-wide">Observaciones y notas iniciales</label>
                            <textarea name="observaciones" id="observaciones" class="form-control @error('observaciones') is-invalid @enderror" 
                                rows="5" placeholder="Escriba antecedentes médicos relevantes, alergias conocidas o el cuadro clínico actual..." style="resize: none; border-radius: 8px;"></textarea>
                            @error('observaciones') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>

                </div>

                <div class="card-footer bg-white border-top-0 p-4 p-md-5 d-flex justify-content-end align-items-center">
                    <a href="{{ route('pacientes.index') }}" class="btn btn-link text-muted mr-3 font-weight-bold text-decoration-none">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary px-5 py-2 font-weight-bold shadow-sm rounded-pill">
                        <i class="fas fa-check-circle mr-1"></i> Finalizar y Guardar Paciente
                    </button>
        {{-- SIDEBAR --}}
        <div class="col-xl-4">
            {{-- PERFIL --}}
            <div class="glass-card profile-card">
                <div class="profile-bg"></div>
                <div class="card-body text-center position-relative">
                    <div class="patient-avatar mx-auto">
                        <i class="fas fa-user-injured"></i>
                    </div>
                    <h3 class="mt-4 fw-bold">
                        Consulta General
                    </h3>
                    <p class="profile-subtitle">
                        Registro médico inteligente y monitoreo clínico
                    </p>

                    {{-- STATUS --}}
                    <div class="status-container">
                        <div class="status-item">
                            <div class="status-icon green">
                                <i class="fas fa-check"></i>
                            </div>
                            <div>
                                <span>Estado</span>
                                <h6>Consulta activa</h6>
                            </div>
                        </div>
                        <div class="status-item">
                            <div class="status-icon orange">
                                <i class="fas fa-exclamation"></i>
                            </div>
                            <div>
                                <span>Prioridad</span>
                                <h6>Moderada</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- INFO --}}
            <div class="glass-card info-card mt-4">
                <div class="card-body">
                    <div class="info-title">
                        <i class="fas fa-chart-line"></i>
                        Información Clínica
                    </div>
                    <div class="info-box">
                        <div class="info-item">
                            <div class="info-icon blue">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <div>
                                <small>Médico</small>
                                <h6>Dr. Martínez</h6>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon green">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div>
                                <small>Fecha</small>
                                <h6>22 Mayo 2026</h6>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon red">
                                <i class="fas fa-heartbeat"></i>
                            </div>
                            <div>
                                <small>Estado</small>
                                <h6>En evaluación</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- FORMULARIO --}}
        <div class="col-xl-8">
            <div class="glass-card form-card">
                <div class="card-body p-5">
                    <form>
                        {{-- HEADER --}}
                        <div class="section-header">
                            <div>
                                <h3>
                                    <i class="fas fa-notes-medical"></i>
                                    Información del Paciente
                                </h3>
                                <p>
                                    Complete los datos clínicos de la consulta
                                </p>
                            </div>
                        </div>

                        {{-- FORM --}}
                        <div class="row g-4 mt-2">
                            {{-- PACIENTE --}}
                            <div class="col-md-6">
                                <label class="form-label">Paciente</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" name="paciente"
                                           class="form-control premium-input"
                                           placeholder="Nombre del paciente">
                                </div>
                            </div>

                            {{-- SEXO --}}
                            <div class="col-md-3">
                                <label class="form-label">Sexo</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-venus-mars input-icon"></i>
                                    <select name="sexo" class="form-control premium-input" style="padding-right: 15px;">
                                        <option value="" disabled selected>Seleccionar...</option>
                                        <option value="Masculino">Masculino</option>
                                        <option value="Femenino">Femenino</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                            </div>

                            {{-- EDAD --}}
                            <div class="col-md-3">
                                <label class="form-label">Edad</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-baby-carriage input-icon"></i>
                                    <input type="number" name="edad" min="0"
                                           class="form-control premium-input"
                                           placeholder="Ej. 25">
                                </div>
                            </div>

                            {{-- FECHA DE NACIMIENTO --}}
                            <div class="col-md-4">
                                <label class="form-label">Fecha de Nacimiento</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-birthday-cake input-icon"></i>
                                    <input type="date" name="fecha_nacimiento"
                                           class="form-control premium-input">
                                </div>
                            </div>

                            {{-- NÚMERO DE TELÉFONO --}}
                            <div class="col-md-4">
                                <label class="form-label">Número de Teléfono</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-phone input-icon"></i>
                                    <input type="tel" name="telefono"
                                           class="form-control premium-input"
                                           placeholder="9999999999">
                                </div>
                            </div>

                            {{-- DIRECCIÓN --}}
                            <div class="col-md-4">
                                <label class="form-label">Dirección</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-map-marker-alt input-icon"></i>
                                    <input type="text" name="direccion"
                                           class="form-control premium-input"
                                           placeholder="Calle, Número, Colonia">
                                </div>
                            </div>
                        </div>

                        {{-- DIVIDER --}}
                        <div class="premium-divider"></div>

                        {{-- CLINICO --}}
                        <div class="section-header">
                            <div>
                                <h3>
                                    <i class="fas fa-file-medical-alt text-danger"></i>
                                    Evaluación Clínica
                                </h3>
                                <p>
                                    Información médica detallada del paciente
                                </p>
                            </div>
                        </div>

                        {{-- SINTOMAS --}}
                        <div class="mb-4">
                            <label class="form-label">Síntomas</label>
                            <textarea class="form-control premium-textarea"
                                      rows="4"
                                      placeholder="Ingrese síntomas del paciente"></textarea>
                        </div>

                        {{-- DIAGNOSTICO --}}
                        <div class="mb-4">
                            <label class="form-label">Diagnóstico</label>
                            <textarea class="form-control premium-textarea"
                                      rows="4"
                                      placeholder="Diagnóstico médico"></textarea>
                        </div>

                        {{-- TRATAMIENTO --}}
                        <div class="mb-4">
                            <label class="form-label">Tratamiento</label>
                            <textarea class="form-control premium-textarea"
                                      rows="4"
                                      placeholder="Tratamiento médico"></textarea>
                        </div>

                        {{-- BOTONES --}}
                        <div class="d-flex justify-content-end gap-3 mt-5 flex-wrap">
                            <button type="button" class="btn cancel-btn">
                                Cancelar
                            </button>
                            <button type="submit" class="btn save-btn">
                                <i class="fas fa-save"></i>
                                Guardar Consulta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@stop

@section('css')
    {{-- 
        Definimos estilos personalizados sutiles que no rompen AdminLTE 
        pero mejoran la estética visual (inspirado en Tailwind/Material).
    --}}
    <style>
        /* General y Tipografía */
        body { font-family: 'Nucleo', 'Source Sans Pro', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        .tracking-wider { letter-spacing: 0.08em; }
        .border-gray-150 { border-color: #e9ecef !important; }
        .bg-gray-50 { background-color: #f8f9fa; }
        .shadow-inner-sm { box-shadow: inset 0 1px 2px rgba(0,0,0,0.05); }

        /* Estilos de Sección */
        .border-b-section { border-bottom: 2px solid #e9ecef; }
        .icon-shape {
            width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
        }
        .bg-primary-faded { background-color: rgba(0, 123, 255, 0.1); }
        .bg-teal-faded { background-color: rgba(32, 201, 151, 0.1); }
        .text-teal { color: #20c997; }

        /* Estilos de Formulario (Labels e Inputs) */
        .custom-form-group {
            margin-bottom: 1.2rem;
            color: #6c757d;
            font-weight: 700;
            font-size: 0.85rem;
        }
        .custom-form-group label {
            text-uppercase: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border-radius: 6px;
            border-color: #d1d9e6;
            padding: 0.6rem 0.75rem;
            height: auto;
            font-size: 0.9rem;
            transition: all 0.15s ease-in-out;
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
        }
        
        /* Ajuste para inputs con íconos */
        .input-group-text {
            border-radius: 6px 0 0 6px;
            border-color: #d1d9e6;
            font-size: 0.85rem;
        }
    </style>
@stop

@section('js')
     @vite('resources/js/app.js')
    <script> console.log('Alta de pacientes optimizada visualmente.'); </script>
<style>
/* ======================= */
/* FONDO */
/* ======================= */
body {
    background: radial-gradient(circle at top left, #dbeafe 0%, #f4f7fb 45%, #eef2ff 100%);
}

/* ======================= */
/* HEADER */
/* ======================= */
.premium-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 30px;
}

.premium-header h1 {
    font-size: 38px;
    font-weight: 900;
    color: #111827;
    margin-bottom: 8px;
}

.premium-header p {
    color: #6b7280;
    font-size: 15px;
}

/* ======================= */
/* STATUS */
/* ======================= */
.live-status {
    background: white;
    padding: 14px 22px;
    border-radius: 50px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
}

.pulse-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #22c55e;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.4); opacity: .5; }
    100% { transform: scale(1); opacity: 1; }
}

/* ======================= */
/* GLASS CARD */
/* ======================= */
.glass-card {
    background: rgba(255,255,255,.72);
    backdrop-filter: blur(18px);
    border-radius: 35px;
    border: 1px solid rgba(255,255,255,.5);
    box-shadow: 0 20px 50px rgba(0,0,0,.08);
    overflow: hidden;
    transition: .4s;
}

.glass-card:hover {
    transform: translateY(-5px);
}

/* ======================= */
/* PROFILE */
/* ======================= */
.profile-card {
    position: relative;
}

.profile-bg {
    height: 160px;
    background: linear-gradient(135deg, #2563eb, #38bdf8);
}

/* ======================= */
/* AVATAR */
/* ======================= */
.patient-avatar {
    width: 130px;
    height: 130px;
    border-radius: 35px;
    background: white;
    margin-top: -65px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 55px;
    color: #2563eb;
    box-shadow: 0 20px 40px rgba(37,99,235,.25);
}

/* ======================= */
/* SUBTITLE */
/* ======================= */
.profile-subtitle {
    color: #6b7280;
    margin-top: 10px;
}

/* ======================= */
/* STATUS BOX */
/* ======================= */
.status-container {
    margin-top: 30px;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.status-item {
    background: #f8fafc;
    border-radius: 22px;
    padding: 18px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.status-icon {
    width: 50px;
    height: 50px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.green { background: #22c55e; }
.orange { background: #f59e0b; }

/* ======================= */
/* INFO */
/* ======================= */
.info-title {
    font-size: 22px;
    font-weight: 800;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.info-box {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 15px;
}

.info-icon {
    width: 55px;
    height: 55px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
}

.blue { background: #2563eb; }
.green { background: #22c55e; }
.red { background: #ef4444; }

/* ======================= */
/* FORM */
/* ======================= */
.section-header {
    margin-bottom: 25px;
}

.section-header h3 {
    font-weight: 900;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-header p {
    color: #6b7280;
    margin-top: 8px;
}

/* ======================= */
/* INPUTS */
/* ======================= */
.form-label {
    font-weight: 700;
    color: #374151;
    margin-bottom: 12px;
}

.input-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    top: 20px;
    left: 20px;
    color: #9ca3af;
    z-index: 2;
}

.premium-input {
    height: 62px;
    border: none;
    border-radius: 20px;
    padding-left: 55px;
    background: #f8fafc;
    box-shadow: inset 0 0 0 1px #e5e7eb;
    transition: .3s;
}

.premium-input:focus {
    background: white;
    box-shadow: 0 0 0 5px rgba(37,99,235,.12);
}

/* ======================= */
/* TEXTAREA */
/* ======================= */
.premium-textarea {
    border: none;
    border-radius: 25px;
    padding: 22px;
    background: #f8fafc;
    resize: none;
    box-shadow: inset 0 0 0 1px #e5e7eb;
    transition: .3s;
}

.premium-textarea:focus {
    background: white;
    box-shadow: 0 0 0 5px rgba(37,99,235,.12);
}

/* ======================= */
/* DIVIDER */
/* ======================= */
.premium-divider {
    height: 2px;
    margin: 45px 0;
    background: linear-gradient(to right, transparent, #2563eb, transparent);
    opacity: .2;
}

/* ======================= */
/* BOTONES */
/* ======================= */
.btn {
    border: none;
    border-radius: 18px;
    padding: 16px 28px;
    font-weight: 700;
    transition: .3s;
}

.btn:hover {
    transform: translateY(-4px);
}

.cancel-btn {
    background: white;
    color: #374151;
    box-shadow: 0 10px 20px rgba(0,0,0,.06);
}

.save-btn {
    background: linear-gradient(135deg, #2563eb, #38bdf8);
    color: white;
    box-shadow: 0 15px 30px rgba(37,99,235,.25);
}

/* ======================= */
/* ICON */
/* ======================= */
.heartbeat-icon {
    color: #2563eb;
    animation: heartbeat 1.5s infinite;
}

@keyframes heartbeat {
    0% { transform: scale(1); }
    25% { transform: scale(1.12); }
    50% { transform: scale(1); }
    75% { transform: scale(1.12); }
    100% { transform: scale(1); }
}
</style>
@ vite('resources/css/app.css') --- IGNORE ---
@stop