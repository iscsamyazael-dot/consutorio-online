@extends('adminlte::page')

@section('title', 'Valoración Nutricional Ocupacional')

@section('content_header')
    <h1>
        <i class="fas fa-apple-alt mr-2 text-success"></i>
        Valoración Nutricional Ocupacional
    </h1>
@stop

@section('content')
    <div id="app">
        <master-nutricion></master-nutricion>
    </div>
@stop

@push('css')
    <style>
        /* Estilos específicos para nutrición si es necesario */
    </style>
@endpush

@push('js')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endpush