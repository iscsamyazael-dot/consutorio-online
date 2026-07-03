@extends('adminlte::page')

@section('title', 'Registrar Paciente - Sistema de Triage')

@section('content_header')

@section('content')
<meta name="base-url" content="{{ url('/') }}">
<input type="hidden" name="route" value="{{ url('/') }}">
    
<div id="app">
    <master-registro-paciente></master-registro-paciente>
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