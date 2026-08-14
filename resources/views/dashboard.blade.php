@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Panel de administración Médico Online</h1>
@stop

@section('content')

    <div id="app">
        <dashboard-home></dashboard-home>
    </div>

@stop

{{-- 👇 Usamos @push en lugar de @section para no romper el stack global de estilos --}}
@push('css')
    {{-- Los estilos del dashboard ya vienen "scoped" dentro de Home.vue --}}
@endpush

{{-- 👇 Igual con los scripts: @push agrega al stack sin sobreescribir --}}
@push('js')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endpush