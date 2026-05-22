@extends('adminlte::page')

@section('title', 'TRIAGE')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="font-weight-bold text-dark">
            TRIAGE Clínico
        </h1>
        <small class="text-muted">
            Clasificación médica de urgencias
        </small>
    </div>
    <button class="btn btn-primary shadow">
        <i class="fas fa-user-plus"></i>
        Nuevo TRIAGE
    </button>
</div>
@stop
@section('content')
<div id="app">
    <atencion-medica></atencion-medica>
</div>
</div>

@stop

@section ('js') 
    @vite ('resources/js/app.js')
@stop