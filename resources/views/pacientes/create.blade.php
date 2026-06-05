@extends('adminlte::page')

@section('title', 'Registrar Paciente - Sistema de Triage')

@section('content_header')

@section('content')
<input type="hidden" name="route" value="{{ url('/') }}">
    
<div id="app">
    <master-registro-paciente></master-registro-paciente>
</div>

@stop
@section('js')
@vite('resources/js/app.js')
@stop