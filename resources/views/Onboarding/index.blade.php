@extends('adminlte::page')

@section('title', 'Configuración inicial - Consultorio Online')

@section('content_header')
@stop

@section('content')
<meta name="base-url" content="{{ url('/') }}">
<input type="hidden" name="route" value="{{ url('/') }}">

<div id="app">
    <admin-onboarding></admin-onboarding>
</div>
@stop

@section('js')
    @vite('resources/js/app.js')
@stop

@section('css')
<style>
    /* Oculta el sidebar y el navbar mientras el admin no ha
       completado el wizard de bienvenida. Solo aplica a esta
       vista: en cuanto termine y lo redirijamos al dashboard,
       esa página carga sin este bloque y el menú vuelve a
       aparecer normalmente. */
    .main-sidebar,
    .main-header,
    .brand-link {
        display: none !important;
    }

    .content-wrapper,
    .content-wrapper > .content {
        margin-left: 0 !important;
    }

    body.sidebar-mini.layout-fixed .content-wrapper {
        margin-left: 0 !important;
    }

    .content-wrapper {
        background: #f4faf9;
        min-height: 100vh;
        padding-top: 30px;
    }
</style>
@stop