@extends('adminlte::page')

@section('title', 'Valoración Audiológica Ocupacional')

@section('content_header')
    <h1>
        <i class="fas fa-ear mr-2 text-warning"></i>
        Valoración Audiológica Ocupacional
    </h1>
@stop

@section('content')
    <div id="app">
        <master-audiologia></master-audiologia>
    </div>
@stop

@push('css')
    <style>
        /* Estilos específicos para audiología si es necesario */
    </style>
@endpush

@push('js')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endpush