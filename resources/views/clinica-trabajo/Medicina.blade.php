@extends('adminlte::page')

@section('title', 'Ficha Médica Ocupacional - WORLDSTRIDE')

@section('content_header')
    <h1>
        <i class="fas fa-stethoscope mr-2 text-primary"></i>
        Evaluación Ocupacional Modular
    </h1>
@stop

@section('content')
    <div id="app">
        <master-ficha-ocupacional></master-ficha-ocupacional>
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