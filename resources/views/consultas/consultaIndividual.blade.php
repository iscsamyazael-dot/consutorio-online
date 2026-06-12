@extends('adminlte::page')

@section('title', 'Registrar Paciente')

@section('content_header')
    <h1>Historial de las consultas del Paciente</h1>

    
            @stop
@section('content')
        <meta name="base-url" content="{{ url('/') }}">
        <input type="hidden" name="route" value="{{ url('/') }}">

        <div id="app">
            <consultapaciente ></consultapaciente>
        </div>

@stop

@section('js')
    @vite('resources/js/app.js')
@stop
