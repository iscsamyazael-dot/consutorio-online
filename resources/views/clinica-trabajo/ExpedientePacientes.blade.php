@extends('adminlte::page')

@section('title', 'Expediente Paciente')

@section('content_header')
<div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="fw-bold text-dark mb-1">
                <i class="fas fa-folder-open text-primary"></i>
                Expediente Clinica Trabajo Pacientes
            </h1>

            <small class="text-muted">
                Historial clínico y valoraciones médicas laborales
            </small>
        </div>

        <a href="{{ url('/ListaPacientesClinicaTrabajo') }}" class="btn btn-outline-primary rounded-pill px-4">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
</div>
@stop
@section('content')
        <meta name="base-url" content="{{ url('/') }}">
        <input type="hidden" name="route" value="{{ url('/') }}">

        <div id="app">
            <master-expediente-clinica-trabajo></master-expediente-clinica-trabajo>
        </div>

@stop

@section('js')
    @vite('resources/js/app.js')
@stop

