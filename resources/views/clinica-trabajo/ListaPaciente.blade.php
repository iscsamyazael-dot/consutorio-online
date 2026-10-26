@extends('adminlte::page')

@section('title', 'Lista Pacientes')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="fw-bold text-dark mb-1">
                <i class="fas fa-user-injured text-primary"></i> Lista de Pacientes
            </h1>
            <small class="text-muted">Gestión médica laboral inteligente</small>
        </div>

        <!-- Contenedor de botones para mantenerlos alineados -->
        <div class="d-flex gap-2">
            <!-- NUEVO BOTÓN: Dispara un evento global que Vue escuchará -->
            <button type="button" class="btn btn-success shadow-sm px-4 rounded-pill" 
                    onclick="window.dispatchEvent(new Event('abrir-modal-importar'))">
                <i class="fas fa-file-excel me-1"></i> Importar Excel
            </button>

            <!-- BOTÓN EXISTENTE -->
            <a href="{{ url('RegistroPacientesClinicaTrabajo') }}" class="btn btn-primary shadow-sm px-4 rounded-pill">
                <i class="fas fa-user-plus me-1"></i> Nuevo Paciente
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div id="app">
        <!-- Tu componente principal de Vue -->
        <lista-paciente-clinicatrabajo></lista-paciente-clinicatrabajo>
    </div>
</div>
@stop

@section('css')
<style>
    body { background: #f4f6f9; }
</style>
@stop

@section('js')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@stop