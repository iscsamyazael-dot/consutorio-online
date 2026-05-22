@extends('adminlte::page')

@section('title', 'Médicamentos e Inventario')

@section('content')
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <input type="hidden" name="route" value="{{ url('/') }}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h3 class="mb-0">
                            <i class="fas fa-pills text-primary"></i>
                            Medicamentos e Inventario
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">Inicio</li>
                                <li class="breadcrumb-item active">Medicamentos</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i>
                            Nuevo Medicamento
                        </button>
                        <button class="btn btn-success">
                            <i class="fas fa-boxes"></i>
                            Entrada Inventario
                        </button>
                        <button class="btn btn-warning text-dark">
                            <i class="fas fa-exclamation-triangle"></i>
                            Alertas
                        </button>
                    </div>
                </div>
            </div>
        </section>
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