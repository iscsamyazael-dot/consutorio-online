@extends('adminlte::page')

@section('title', 'Evaluación IA')

@section('content_header')
@section('content')
<meta name="base-url" content="{{ url('/') }}">
<input type="hidden" name="route" value="{{ url('/') }}">

<div id="app">
    <atencion-medica-evaluacionia
        :especialidades-json="{{ $especialidadesJs }}"
        :medicos-json="{{ $medicosJs }}"
    ></atencion-medica-evaluacionia>
</div>

@stop

@section('js')
    @vite('resources/js/app.js')
@stop