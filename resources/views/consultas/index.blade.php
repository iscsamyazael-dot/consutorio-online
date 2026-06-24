@extends('adminlte::page')

@section('title', 'Lista de Consultas - Sistema de Triage')

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
@stop

@section('content')
<meta name="base-url" content="{{ url('/') }}">
<input type="hidden" name="route" value="{{ url('/') }}">

<div id="app">
    <centroconsultas></centroconsultas>
</div>
@stop

@section('js')
    @vite('resources/js/app.js')
@stop

@section('css')
<style>

    /* FIX SIDEBAR ADMINLTE */

.main-sidebar{
    position: fixed !important;
    height: 100vh !important;
    overflow-y: auto;
}

.content-wrapper,
.main-footer,
.main-header{
    margin-left: 250px !important;
}

/* EVITA QUE BAJE EL MENÚ */

.wrapper{
    overflow-x: hidden;
}

/* SCROLL SOLO EN CONTENIDO */

.content-wrapper{
    min-height: 100vh;
}
</style>
@stop