@extends('adminlte::page')

@section('title', 'Buscador de pacientes/consultas')

@section('content_header')
    <h1>
        <i class="fas fa-stethoscope mr-2 text-primary"></i>
        Buscador de pacientes/Consultas Laborales
    </h1>
@stop

@section('content')
    <div id="app">
        <buscador-pacientes-clinica-trabajo></buscador-pacientes-clinica-trabajo>
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