@extends('adminlte::page')

@section('title', 'Nueva Consulta')

@section('content_header')

<div class="mb-4">

    <h1 class="fw-bold text-dark">

        <i class="fas fa-stethoscope text-primary"></i>
        Nueva Consulta Médica

    </h1>

    <small class="text-muted">

        Panel clínico avanzado

    </small>

</div>

@stop

@section('content')

<div class="container-fluid">

    <div class="row g-4">

        {{-- PANEL IZQUIERDO --}}
        <div class="col-lg-4">

            {{-- CARD PERFIL --}}
            <div class="card side-card border-0">

                <div class="card-body text-center p-5">

                    <div class="patient-avatar mx-auto mb-4">

                        <i class="fas fa-user-injured"></i>

                    </div>

                    <h4 class="fw-bold">

                        Consulta General

                    </h4>

                    <p class="text-muted">

                        Registro clínico inteligente

                    </p>

                    <div class="status-box mt-4">

                        <div class="status-item">

                            <span class="status-dot bg-success"></span>

                            Consulta activa

                        </div>

                        <div class="status-item">

                            <span class="status-dot bg-warning"></span>

                            Prioridad moderada

                        </div>

                    </div>

                </div>

            </div>

            {{-- CARD INFO --}}
            <div class="card border-0 info-card mt-4">

                <div class="card-body">

                    <h5 class="fw-bold mb-4">

                        <i class="fas fa-chart-line text-primary"></i>
                        Información rápida

                    </h5>

                    <div class="quick-info">

                        <div class="quick-item">

                            <i class="fas fa-user-md text-primary"></i>

                            <div>

                                <small>Médico</small>

                                <h6>Dr. Martínez</h6>

                            </div>

                        </div>

                        <div class="quick-item">

                            <i class="fas fa-calendar text-success"></i>

                            <div>

                                <small>Fecha</small>

                                <h6>22 Mayo 2026</h6>

                            </div>

                        </div>

                        <div class="quick-item">

                            <i class="fas fa-heartbeat text-danger"></i>

                            <div>

                                <small>Estado</small>

                                <h6>En evaluación</h6>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- FORMULARIO --}}
        <div class="col-lg-8">

            <div class="card main-card border-0">

                <div class="card-body p-5">

                    <form>

                        {{-- PACIENTE --}}
                        <div class="mb-5">

                            <h4 class="section-title">

                                <i class="fas fa-user text-primary"></i>
                                Información del Paciente

                            </h4>

                        </div>

                        <div class="row g-4">

                            <div class="col-md-6">

                                <label class="form-label">
                                    Paciente
                                </label>

                                <input type="text"
                                       class="form-control custom-input"
                                       placeholder="Nombre del paciente">

                            </div>

                            <div class="col-md-6">

                                <label class="form-label">
                                    Doctor
                                </label>

                                <input type="text"
                                       class="form-control custom-input"
                                       placeholder="Doctor responsable">

                            </div>

                            <div class="col-md-6">

                                <label class="form-label">
                                    Fecha
                                </label>

                                <input type="date"
                                       class="form-control custom-input">

                            </div>

                            <div class="col-md-6">

                                <label class="form-label">
                                    Hora
                                </label>

                                <input type="time"
                                       class="form-control custom-input">

                            </div>

                        </div>

                        {{-- DIVIDER --}}
                        <div class="divider my-5"></div>

                        {{-- CLINICO --}}
                        <div class="mb-5">

                            <h4 class="section-title">

                                <i class="fas fa-file-medical text-danger"></i>
                                Evaluación Clínica

                            </h4>

                        </div>

                        {{-- SINTOMAS --}}
                        <div class="mb-4">

                            <label class="form-label">
                                Síntomas
                            </label>

                            <textarea class="form-control custom-textarea"
                                      rows="4"
                                      placeholder="Ingrese síntomas"></textarea>

                        </div>

                        {{-- DIAGNOSTICO --}}
                        <div class="mb-4">

                            <label class="form-label">
                                Diagnóstico
                            </label>

                            <textarea class="form-control custom-textarea"
                                      rows="4"
                                      placeholder="Diagnóstico médico"></textarea>

                        </div>

                        {{-- TRATAMIENTO --}}
                        <div class="mb-4">

                            <label class="form-label">
                                Tratamiento
                            </label>

                            <textarea class="form-control custom-textarea"
                                      rows="4"
                                      placeholder="Tratamiento médico"></textarea>

                        </div>

                        {{-- BOTONES --}}
                        <div class="d-flex justify-content-end gap-3 mt-5">

                            <button class="btn btn-light btn-lg rounded-pill px-4">

                                Cancelar

                            </button>

                            <button class="btn btn-primary btn-lg rounded-pill px-5 save-btn">

                                <i class="fas fa-save"></i>
                                Guardar Consulta

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@stop

@section('css')

<style>

/* FONDO */

body{

    background:#f4f7fb;
}

/* CARDS */

.main-card,
.side-card,
.info-card{

    border-radius:30px;

    box-shadow:
    0 15px 40px rgba(0,0,0,.06);

    overflow:hidden;
}

/* SIDE */

.side-card{

    background:
    linear-gradient(135deg,#0d6efd,#00c6ff);

    color:white;
}

/* AVATAR */

.patient-avatar{

    width:120px;
    height:120px;

    border-radius:50%;

    background:rgba(255,255,255,.15);

    display:flex;
    align-items:center;
    justify-content:center;

    font-size:50px;
}

/* STATUS */

.status-box{

    display:flex;
    flex-direction:column;
    gap:15px;
}

.status-item{

    background:rgba(255,255,255,.15);

    padding:14px 18px;

    border-radius:15px;

    display:flex;
    align-items:center;
    gap:12px;
}

.status-dot{

    width:12px;
    height:12px;

    border-radius:50%;
}

/* QUICK */

.quick-item{

    display:flex;
    align-items:center;
    gap:15px;

    margin-bottom:25px;
}

.quick-item i{

    font-size:22px;
}

/* TITULOS */

.section-title{

    font-weight:800;

    color:#1f2937;
}

/* INPUTS */

.custom-input{

    height:58px;

    border:none;

    border-radius:18px;

    padding:15px 20px;

    background:#f8fafc;

    box-shadow:
    inset 0 0 0 1px #e5e7eb;
}

.custom-input:focus{

    background:white;

    box-shadow:
    0 0 0 4px rgba(13,110,253,.10);
}

/* TEXTAREA */

.custom-textarea{

    border:none;

    border-radius:20px;

    padding:20px;

    background:#f8fafc;

    resize:none;

    box-shadow:
    inset 0 0 0 1px #e5e7eb;
}

.custom-textarea:focus{

    background:white;

    box-shadow:
    0 0 0 4px rgba(13,110,253,.10);
}

/* DIVIDER */

.divider{

    height:2px;

    background:
    linear-gradient(to right,
    transparent,
    #0d6efd,
    transparent);

    opacity:.2;
}

/* BOTON */

.save-btn{

    background:
    linear-gradient(135deg,#0d6efd,#00c6ff);

    border:none;
}

/* LABEL */

.form-label{

    font-weight:700;

    color:#374151;

    margin-bottom:12px;
}

/* HOVER */

.btn{

    transition:.3s;
}

.btn:hover{

    transform:translateY(-3px);
}

</style>

@stop