@extends('adminlte::page')

@section('title', 'Ficha Médica UADIOLOGIA - DELLI')

@section('content_header')
    <h1>
        <i class="fas fa-stethoscope mr-2 text-primary"></i>
        Ficha Audiologia - Historia Clinica
    </h1>
@stop

@section('content')
    <div id="app">
        <master-ficha-audiologica-delli></master-ficha-audiologica-delli>
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