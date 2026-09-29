@extends('adminlte::page')

@section('title', 'Valoración Psicológica Ocupacional')

@section('content_header')
    <h1>
        <i class="fas fa-brain mr-2 text-info"></i>
        Valoración Psicológica Ocupacional
    </h1>
@stop

@section('content')
    <div id="app">
        <master-psicologia></master-psicologia>
    </div>
@stop

@push('css')
    <style>
        /* Estilos específicos para psicología si es necesario */
    </style>
@endpush

@push('js')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endpush