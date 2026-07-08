@extends('adminlte::page')

@section('title', 'Registrar Paciente - Sistema de Triage')

@section('meta_tags')
<meta name="base-url" content="{{ url('/') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection


@section('content_header')
<div class="d-flex justify-content-between align-items-center py-2">
    <h1 class="text-dark font-weight-bold" style="font-size: 1.8rem;">
        <i class="fas fa-user-plus text-primary mr-2"></i>
        Alta de Nuevo Paciente
    </h1>

    <a href="{{ route('pacientes.index') }}" 
       class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fas fa-arrow-left mr-1"></i>
        Volver al Listado
    </a>
</div>
@stop


@section('content')

<div id="app">
    <master-registro-paciente></master-registro-paciente>
</div>

@stop


@section('js')
@vite('resources/js/app.js')
@stop


@section('css')
<style>

.main-sidebar{
    position: fixed;
    height: 100vh;
    overflow-y: auto;
}

.wrapper{
    overflow-x:hidden;
}

.content-wrapper{
    min-height:100vh;
}

</style>
@stop