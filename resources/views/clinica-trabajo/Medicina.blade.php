@extends('adminlte::page')

@section('title', 'Ficha Médica Ocupacional')

@section('content_header')
    <h1>
        <i class="fas fa-stethoscope mr-2 text-primary"></i>
        Ficha Médica Ocupacional
    </h1>
@stop

@section('content')
    <div id="app">
        <master-medicina></master-medicina>
    </div>
@stop

@push('css')
    <style>
        /* Estilos específicos para medicina si es necesario */
    </style>
@endpush

@push('js')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endpush