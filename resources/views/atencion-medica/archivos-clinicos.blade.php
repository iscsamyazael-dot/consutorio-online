@extends('adminlte::page')

@section('title', 'Archivos Clínicos')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>
        <h1 class="font-weight-bold">
            Archivos Clínicos
        </h1>

        <small class="text-muted">
            Gestión de estudios médicos
        </small>
    </div>

    <button class="btn btn-success">
        <i class="fas fa-upload"></i>
        Subir Archivo
    </button>

</div>

@stop

@section('content')

<div class="card shadow-lg">

    <div class="card-header bg-success text-white">

        <h5 class="mb-0">
            Estudios Médicos
        </h5>

    </div>

    <div class="card-body table-responsive">

        <table class="table table-hover table-bordered">

            <thead class="bg-light">

                <tr>

                    <th>Paciente</th>
                    <th>Tipo</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Archivo</th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td>María López</td>
                    <td>Radiografía</td>
                    <td>20/05/2026</td>

                    <td>

                        <span class="badge badge-success">
                            Procesado
                        </span>

                    </td>

                    <td>

                        <button class="btn btn-primary btn-sm">

                            <i class="fas fa-file-medical"></i>

                        </button>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>

<div class="row mt-4">

    <div class="col-lg-6">

        <div class="card shadow">

            <div class="card-header bg-info text-white">

                <h5 class="mb-0">
                    IA de Imágenes
                </h5>

            </div>

            <div class="card-body">

                <div class="alert alert-info">

                    Posibles anomalías detectadas automáticamente.

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-6">

        <div class="card shadow">

            <div class="card-header bg-warning text-white">

                <h5 class="mb-0">
                    Recomendaciones
                </h5>

            </div>

            <div class="card-body">

                <ul>

                    <li>Revisar estudio pulmonar</li>
                    <li>Solicitar valoración médica</li>
                    <li>Monitorear evolución</li>

                </ul>

            </div>

        </div>

    </div>

</div>

@stop