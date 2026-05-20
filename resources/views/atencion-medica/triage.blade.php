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

<div class="row mb-4">

    <div class="col-lg-3 col-md-6 col-sm-12">

        <div class="small-box bg-danger shadow">

            <div class="inner">
                <h3>2</h3>
                <p>Críticos</p>
            </div>

            <div class="icon">
                <i class="fas fa-heartbeat"></i>
            </div>

        </div>

    </div>

    <div class="col-lg-3 col-md-6 col-sm-12">

        <div class="small-box bg-warning shadow">

            <div class="inner">
                <h3>5</h3>
                <p>Urgentes</p>
            </div>

            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>

        </div>

    </div>

    <div class="col-lg-3 col-md-6 col-sm-12">

        <div class="small-box bg-info shadow">

            <div class="inner">
                <h3>8</h3>
                <p>Moderados</p>
            </div>

            <div class="icon">
                <i class="fas fa-procedures"></i>
            </div>

        </div>

    </div>

    <div class="col-lg-3 col-md-6 col-sm-12">

        <div class="small-box bg-success shadow">

            <div class="inner">
                <h3>15</h3>
                <p>Atendidos</p>
            </div>

            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>

        </div>

    </div>

</div>

<div class="card card-outline card-primary shadow-lg">

    <div class="card-header">

        <h3 class="card-title font-weight-bold">
            Lista de Pacientes TRIAGE
        </h3>

    </div>

    <div class="card-body table-responsive">

        <table class="table table-hover table-bordered">

            <thead class="bg-light">

                <tr>

                    <th>Prioridad</th>
                    <th>Paciente</th>
                    <th>Síntomas</th>
                    <th>Presión</th>
                    <th>Saturación</th>
                    <th>Temperatura</th>
                    <th>Estado</th>
                    <th>Tiempo</th>
                    <th>Acciones</th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td>
                        <span class="badge badge-danger p-2">
                            CRÍTICO
                        </span>
                    </td>

                    <td>
                        Juan Pérez
                    </td>

                    <td>
                        Dolor torácico
                    </td>

                    <td>
                        180/120
                    </td>

                    <td>
                        86%
                    </td>

                    <td>
                        39°C
                    </td>

                    <td>
                        <span class="badge badge-danger">
                            Riesgo Alto
                        </span>
                    </td>

                    <td>
                        2 min
                    </td>

                    <td>

                        <button class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i>
                        </button>

                        <button class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </button>

                    </td>

                </tr>

                <tr>

                    <td>
                        <span class="badge badge-warning p-2">
                            URGENTE
                        </span>
                    </td>

                    <td>
                        María López
                    </td>

                    <td>
                        Fiebre alta
                    </td>

                    <td>
                        140/90
                    </td>

                    <td>
                        94%
                    </td>

                    <td>
                        38°C
                    </td>

                    <td>
                        <span class="badge badge-warning">
                            Observación
                        </span>
                    </td>

                    <td>
                        10 min
                    </td>

                    <td>

                        <button class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i>
                        </button>

                        <button class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </button>

                    </td>

                </tr>

                <tr>

                    <td>
                        <span class="badge badge-info p-2">
                            MODERADO
                        </span>
                    </td>

                    <td>
                        Carlos Méndez
                    </td>

                    <td>
                        Dolor abdominal
                    </td>

                    <td>
                        120/80
                    </td>

                    <td>
                        98%
                    </td>

                    <td>
                        37°C
                    </td>

                    <td>
                        <span class="badge badge-info">
                            Estable
                        </span>
                    </td>

                    <td>
                        20 min
                    </td>

                    <td>

                        <button class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i>
                        </button>

                        <button class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </button>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>

<div class="row mt-4">

    <div class="col-lg-6">

        <div class="card card-outline card-danger shadow">

            <div class="card-header">

                <h3 class="card-title font-weight-bold">
                    ⚠ Alertas Clínicas IA
                </h3>

            </div>

            <div class="card-body">

                <div class="alert alert-danger">

                    <h5 class="font-weight-bold">
                        Posible evento cardiovascular
                    </h5>

                    <p class="mb-0">
                        Se detectó presión arterial crítica y saturación baja.
                    </p>

                </div>

                <div class="alert alert-warning">

                    <h5 class="font-weight-bold">
                        Riesgo de infección
                    </h5>

                    <p class="mb-0">
                        Temperatura elevada persistente detectada.
                    </p>

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-6">

        <div class="card card-outline card-success shadow">

            <div class="card-header">

                <h3 class="card-title font-weight-bold">
                    Recomendaciones IA
                </h3>

            </div>

            <div class="card-body">

                <div class="info-box">

                    <span class="info-box-icon bg-primary">
                        <i class="fas fa-robot"></i>
                    </span>

                    <div class="info-box-content">

                        <span class="info-box-text">
                            Recomendación
                        </span>

                        <span class="info-box-number">
                            Canalización inmediata
                        </span>

                    </div>

                </div>

                <div class="info-box">

                    <span class="info-box-icon bg-warning">
                        <i class="fas fa-user-md"></i>
                    </span>

                    <div class="info-box-content">

                        <span class="info-box-text">
                            Especialidad
                        </span>

                        <span class="info-box-number">
                            Cardiología
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@stop