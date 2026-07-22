@extends('adminlte::page')

@section('title', 'Nueva Consulta')

@section('content')
    <input type="hidden" name="route" value="{{ url('/') }}">
    <meta name="base-url" content="{{ url('/') }}">
    <div id="app">
        
        <nuevaconsultamedica
            :doctor="{{ Js::from($doctor) }}"
            :paciente-id="{{ Js::from($pacienteId) }}"
        ></nuevaconsultamedica>
    </div>
@stop

@section('js')
    @vite('resources/js/app.js')
@stop