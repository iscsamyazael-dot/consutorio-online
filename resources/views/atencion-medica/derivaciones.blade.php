@extends('adminlte::page')

@section('title', 'Derivaciones')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h1 class="font-weight-bold">
            Derivaciones Médicas
        </h1>

        <small class="text-muted">
            Canalización de pacientes
        </small>

    </div>

    <button class="btn btn-warning text-white">

        <i class="fas fa-random"></i>
        Nueva Derivación

    </button>

</div>

@stop

@section('content')

<div class="row mb-4">

    <div class="col-lg-4">

        <div class="small-box bg-warning shadow">

            <div class="inner">

                <h3>8</h3>
                <p>Derivaciones Activas</p>

            </div>

            <div class="icon">

                <i class="fas fa-exchange-alt"></i>

            </div>

        </div>

    </div>

    <div class="col-lg-4">

        <div class="small-box bg-danger shadow">

            <div class="inner">

                <h3>2</h3>
                <p>Alta Prioridad</p>

            </div>

            <div class="icon">

                <i class="fas fa-heartbeat"></i>

            </div>

        </div>

    </div>

    <div class="col-lg-4">

        <div class="small-box bg-success shadow">

            <div class="inner">

                <h3>15</h3>
                <p>Canalizados</p>

            </div>

            <div class="icon">

                <i class="fas fa-check-circle"></i>

            </div>

        </div>

    </div>

</div>

<div class="card shadow-lg">

    <div class="card-header bg-warning text-white">

        <h5 class="mb-0">
            Lista de Derivaciones
        </h5>

    </div>

    <div class="card-body table-responsive">

        <table class="table table-hover table-bordered">

            <thead class="bg-light">

                <tr>

                    <th>Paciente</th>
                    <th>Especialidad</th>
                    <th>Motivo</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Acciones</th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td>Juan Pérez</td>

                    <td>Cardiología</td>

                    <td>
                        Sospecha cardiovascular
                    </td>

                    <td>

                        <span class="badge badge-danger p-2">
                            ALTA
                        </span>

                    </td>

                    <td>

                        <span class="badge badge-warning">
                            Pendiente
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