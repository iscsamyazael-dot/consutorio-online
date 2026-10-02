@extends('adminlte::page')

@section('title', 'Ficha Médica UADIOLOGIA - TR')

@section('content_header')
    <h1>
        <i class="fas fa-stethoscope mr-2 text-primary"></i>
        Ficha Audiologia -  Trabajo de Alto Riesgo 
    </h1>
@stop

@section('content')
    <div id="app">
        <master-examen-trabajo-tr></master-examen-trabajo-tr>
    </div>
@stop

@push('css')
    <style>
        #app {
            padding: 0;
        }
    </style>
@endpush

@push('js')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endpush