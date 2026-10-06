@extends('adminlte::page')

@section('title', 'Ficha Médica Espirometria')

@section('content_header')
    <h1>
        <i class="fas fa-stethoscope mr-2 text-primary"></i>
        Evaluación Modulo Espirometria
    </h1>
@stop

@section('content')
    <div id="app">
        <master-espirometria></master-espirometria>
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