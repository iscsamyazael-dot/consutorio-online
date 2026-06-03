@extends('adminlte::page')

@section('title', 'Médicamentos e Inventario')

@section('content')
    <div class="content">
        <input type="hidden" name="route" value="{{ url('/') }}">
        <meta name="base-url" content="{{ url('/') }}">
        <div id="app">
            <medicamentos-inventario></medicamentos-inventario>
        </div>
    </div>
@stop
@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    @vite('resources/js/app.js')
    <script> console.log('Hi!'); </script>
@stop