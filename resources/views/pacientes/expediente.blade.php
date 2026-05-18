@extends('adminlte::page')

@section('title', 'Expediente Paciente')

@section('content_header')
    <h1>Expediente del Paciente</h1>
@stop

@section('content') 

<div class="container-fluid">
     <!-- BREADCRUMB -->
    <nav class="mb-3 small text-muted">
        Pacientes / <strong>Juan Pérez García</strong>
    </nav>
    
    <!-- HEADER PACIENTE -->
    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex align-items-center">

            <img src="https://via.placeholder.com/90"
                 class="rounded-circle me-3">
            <div class="flex-grow-1">
                <h4 class="mb-0">Juan Pérez García</h4>
                <small class="text-muted">
                    Expediente #0001 · 35 años · Masculino
                </small>
            </div>
            <div>
                <button class="btn btn-success">
                    <i class="fas fa-stethoscope"></i>
                    Nueva consulta
                </button>
            </div>

        </div>
    </div>
    <div class="row">
        <!-- COLUMNA IZQUIERDA -->
        <div class="col-md-3">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light">
                    Información básica
                </div>
                <div class="card-body small">
                    <p><strong>Teléfono:</strong> 555-123-4567</p>
                    <p><strong>Correo:</strong> paciente@email.com</p>
                    <p><strong>Tipo sangre:</strong> O+</p>
                    <p><strong>Alergias:</strong> Penicilina</p>
                    <p><strong>Fecha nacimiento:</strong> 12/03/1990</p>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    Alertas médicas
                </div>

                <div class="card-body">
                    <span class="badge bg-danger">
                        Alergia a penicilina
                    </span>
                </div>
            </div>
        </div>
        <!-- COLUMNA DERECHA -->
        <div class="col-md-9">

            <div class="card shadow-sm">

                <!-- TABS -->
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">

                        <li class="nav-item">
                            <button class="nav-link active"
                                data-bs-toggle="tab"
                                data-bs-target="#consultas">
                                Consultas
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link"
                                data-bs-toggle="tab"
                                data-bs-target="#recetas">
                                Recetas
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link"
                                data-bs-toggle="tab"
                                data-bs-target="#archivos">
                                Archivos clínicos
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link"
                                data-bs-toggle="tab"
                                data-bs-target="#notas">
                                Notas médicas
                            </button>
                        </li>

                    </ul>
                </div>

                <!-- CONTENIDO -->
                <div class="card-body tab-content">

                    <div class="tab-pane fade show active" id="consultas">
                        <p class="text-muted">
                            Historial de consultas médicas del paciente.
                        </p>
                        <div class="timeline">

                        <!-- Consulta -->
                        <div class="card mb-3 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <strong>10 Mayo 2026</strong>
                                    <span class="badge bg-success">
                                        Consulta general
                                    </span>
                                </div>
                                <p class="mt-2 mb-1">
                                    <strong>Motivo:</strong> Dolor de garganta
                                </p>
                                <p class="text-muted small">
                                    Diagnóstico: Faringitis leve.
                                </p>
                                <a href="{{url('HistorialConsulta')}}" class="btn btn-sm btn-outline-primary">
                                    Ver consulta completa
                                </a>
                            </div>
                        </div>

                        <!-- Consulta -->
                        <div class="card mb-3 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <strong>02 Abril 2026</strong>
                                    <span class="badge bg-warning text-dark">
                                        Seguimiento
                                    </span>
                                </div>
                                <p class="mt-2 mb-1">
                                    Control general del paciente.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                    <div class="tab-pane fade" id="recetas">
                       <p class="text-muted">
                            Historial de recetas médicas del paciente.
                        </p>
                        <div class="card shadow-sm mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <strong>10 Mayo 2026</strong>
                                    <span class="badge bg-primary">Receta médica</span>
                                </div>
                                <p class="mt-2 mb-1">
                                    Amoxicilina 500mg – cada 8 horas por 7 días.
                                </p>
                                <button class="btn btn-sm btn-outline-primary">
                                    Ver receta completa
                                </button>
                            </div>
                         </div>
                    </div>

                    <div class="tab-pane fade" id="archivos">
                        Estudios clínicos e imágenes.
                        <div class="row">

                            <div class="col-md-4">
                                <div class="card shadow-sm mb-3">
                                    <div class="card-body text-center">
                                        <i class="fas fa-file-medical fa-2x text-primary"></i>
                                        <p class="mt-2 mb-1">
                                            Laboratorio_sangre.pdf
                                        </p>
                                        <small class="text-muted">
                                            02 Abril 2026
                                        </small>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-outline-primary">
                                                Ver archivo
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="notas">
                        Notas médicas generales.
                        <div class="card shadow-sm mb-3">
                            <div class="card-body">
                                <small class="text-muted">
                                    15 Mayo 2026
                                </small>
                                <p class="mb-0">
                                    Paciente reporta mejoría notable.
                                    Continuar tratamiento actual.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script> console.log('Hi!'); </script>
@stop