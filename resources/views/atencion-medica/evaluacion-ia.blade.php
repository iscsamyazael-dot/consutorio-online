@extends('adminlte::page')

@section('title', 'Evaluación IA')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>
        <h1 class="font-weight-bold">
            Evaluación IA
        </h1>

        <small class="text-muted">
            Análisis clínico inteligente
        </small>
    </div>

    <button class="btn btn-primary">
        <i class="fas fa-robot"></i>
        Nueva Evaluación
    </button>

</div>

@stop

@section('content')

<div class="row mb-4">

    <div class="col-lg-4">

        <div class="small-box bg-danger shadow">

            <div class="inner">
                <h3>4</h3>
                <p>Alertas Críticas</p>
            </div>

            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>

        </div>

    </div>

    <div class="col-lg-4">

        <div class="small-box bg-warning shadow">

            <div class="inner">
                <h3>12</h3>
                <p>Evaluaciones Activas</p>
            </div>

            <div class="icon">
                <i class="fas fa-brain"></i>
            </div>

        </div>

    </div>

    <div class="col-lg-4">

        <div class="small-box bg-success shadow">

            <div class="inner">
                <h3>28</h3>
                <p>Diagnósticos IA</p>
            </div>

            <div class="icon">
                <i class="fas fa-stethoscope"></i>
            </div>

        </div>

    </div>

</div>

<div class="card shadow-lg">

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">
            Evaluaciones Inteligentes
        </h5>

    </div>

    <div class="card-body table-responsive">

        <table class="table table-hover table-bordered">

            <thead class="bg-light">

                <tr>

                    <th>Paciente</th>
                    <th>Síntomas</th>
                    <th>Riesgo</th>
                    <th>Diagnóstico IA</th>
                    <th>Estado</th>
                    <th>Acciones</th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td>Juan Pérez</td>
                    <td>Dolor torácico</td>

                    <td>
                        <span class="badge badge-danger p-2">
                            ALTO
                        </span>
                    </td>

                    <td>
                        Evento cardiovascular
                    </td>

                    <td>
                        <span class="badge badge-warning">
                            En análisis
                        </span>
                    </td>

                    <td>

                        <button class="btn btn-primary btn-sm">
                            <i class="fas fa-eye"></i>
                        </button>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>

@stop