@extends('adminlte::page')

@section('title', 'Ficha Médica Ocupacional')

@section('content_header')
    <h1>Ficha Médica Ocupacional</h1>
@stop

@section('content')
    <div id="app">
        <master-ficha-ocupacional></master-ficha-ocupacional>
    </div>
@stop

@push('css')
    <style>
        /* Estilos base si son requeridos */
    </style>
@endpush

@push('js')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endpush
