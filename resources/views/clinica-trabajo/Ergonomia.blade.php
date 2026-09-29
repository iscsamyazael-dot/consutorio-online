@extends('adminlte::page')

@section('title', 'Valoración Ergonómica Ocupacional')

@section('content_header')
    <h1>
        <i class="fas fa-chair mr-2 text-danger"></i>
        Valoración Ergonómica Ocupacional
    </h1>
@stop

@section('content')
    <div id="app">
        <master-ergonomia></master-ergonomia>
    </div>
@stop

@push('css')
    <style>
        /* Estilos específicos para ergonomía si es necesario */
    </style>
@endpush

@push('js')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endpush