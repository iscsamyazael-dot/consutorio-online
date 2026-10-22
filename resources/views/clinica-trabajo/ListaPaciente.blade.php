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

        <a href="{{ url('PacienteNuevo') }}" class="btn btn-primary shadow-sm px-4 rounded-pill">
            <i class="fas fa-user-plus"></i> Nuevo Paciente
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div id="app">
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