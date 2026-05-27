@extends('adminlte::page')

@section('title', 'Nueva Consulta')

@section('content_header')
<div class="mb-4 page-title">
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

<div class="container-fluid main-wrapper">

    <div class="row g-4">

        {{-- PANEL IZQUIERDO FIJO --}}
        <div class="col-lg-4">

            <div class="fixed-panel">

                {{-- CARD PERFIL --}}
                <div class="card side-card border-0">

                    <div class="card-body text-center p-5">

                        <div class="patient-avatar mx-auto mb-4">
                            <i class="fas fa-user-injured"></i>
                        </div>

                        <h4 class="fw-bold">
                            Consulta General
                        </h4>

                        <p class="side-subtitle">
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

        </div>

        {{-- FORMULARIO CON SCROLL --}}
        <div class="col-lg-8">

            <div class="form-scroll">

                <div class="card main-card border-0">

                    <div class="card-body p-5">

                        <form>

                            {{-- PACIENTE --}}
                            <div class="mb-5 animated-section">

                                <h4 class="section-title">
                                    <i class="fas fa-user text-primary"></i>
                                    Información del Paciente
                                </h4>

                            </div>

                            <div class="row g-4 animated-section">

                                <div class="col-md-6">
                                    <label class="form-label">
                                        Paciente
                                    </label>

                                    <div class="input-icon-box">
                                        <i class="fas fa-user"></i>
                                        <input type="text"
                                               class="form-control custom-input with-icon"
                                               placeholder="Nombre del paciente">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">
                                        Doctor
                                    </label>

                                    <div class="input-icon-box">
                                        <i class="fas fa-user-md"></i>
                                        <input type="text"
                                               class="form-control custom-input with-icon"
                                               placeholder="Doctor responsable">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">
                                        Fecha
                                    </label>

                                    <div class="input-icon-box">
                                        <i class="fas fa-calendar-alt"></i>
                                        <input type="date"
                                               class="form-control custom-input with-icon">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">
                                        Hora
                                    </label>

                                    <div class="input-icon-box">
                                        <i class="fas fa-clock"></i>
                                        <input type="time"
                                               class="form-control custom-input with-icon">
                                    </div>
                                </div>

                            </div>

                            {{-- DIVIDER --}}
                            <div class="divider my-5"></div>

                            {{-- CLINICO --}}
                            <div class="mb-5 animated-section delay-1">

                                <h4 class="section-title">
                                    <i class="fas fa-file-medical text-danger"></i>
                                    Evaluación Clínica
                                </h4>

                            </div>

                            {{-- SINTOMAS --}}
                            <div class="mb-4 animated-section delay-1">

                                <label class="form-label">
                                    Síntomas
                                </label>

                                <div class="textarea-icon-box">
                                    <i class="fas fa-notes-medical"></i>
                                    <textarea class="form-control custom-textarea with-icon"
                                              rows="4"
                                              placeholder="Ingrese síntomas"></textarea>
                                </div>

                            </div>

                            {{-- DIAGNOSTICO --}}
                            <div class="mb-4 animated-section delay-2">

                                <label class="form-label">
                                    Diagnóstico
                                </label>

                                <div class="textarea-icon-box">
                                    <i class="fas fa-diagnoses"></i>
                                    <textarea class="form-control custom-textarea with-icon"
                                              rows="4"
                                              placeholder="Diagnóstico médico"></textarea>
                                </div>

                            </div>

                            {{-- TRATAMIENTO --}}
                            <div class="mb-4 animated-section delay-3">

                                <label class="form-label">
                                    Tratamiento
                                </label>

                                <div class="textarea-icon-box">
                                    <i class="fas fa-pills"></i>
                                    <textarea class="form-control custom-textarea with-icon"
                                              rows="4"
                                              placeholder="Tratamiento médico"></textarea>
                                </div>

                            </div>

                            {{-- BOTONES --}}
                            <div class="d-flex justify-content-end gap-3 mt-5 animated-section delay-3">

                                <button type="button"
        class="btn btn-cancel btn-lg rounded-pill px-4">
    Cancelar
</button>

                                <button type="submit"
                                        class="btn btn-primary btn-lg rounded-pill px-5 save-btn">
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

</div>

@stop

@section('css')

<style>
body{
    background:#f4f7fb;
}

.content-wrapper{
    overflow:hidden;
}
.btn-cancel{
    background:#fee2e2;
    color:#dc2626;
    border:none;
    font-weight:700;
}

.btn-cancel:hover{
    background:#dc2626;
    color:white;
}

.page-title{
    animation:fadeDown .6s ease both;
}

.fixed-panel{
    position:sticky;
    top:20px;
}

.form-scroll{
    height:calc(100vh - 120px);
    overflow-y:auto;
    padding-right:10px;
}

.form-scroll::-webkit-scrollbar{
    width:8px;
}

.form-scroll::-webkit-scrollbar-thumb{
    background:#0d6efd;
    border-radius:20px;
}

.main-card,
.side-card,
.info-card{
    border-radius:30px;
    box-shadow:0 15px 40px rgba(0,0,0,.06);
    overflow:hidden;
    animation:cardEnter .7s ease both;
}

.side-card{
    background:linear-gradient(135deg,#0d6efd,#00c6ff);
    color:white;
}

.side-subtitle{
    color:rgba(255,255,255,.82);
}

.patient-avatar{
    width:120px;
    height:120px;
    border-radius:50%;
    background:rgba(255,255,255,.15);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:50px;
    animation:pulseSoft 2.5s ease-in-out infinite;
}

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
    transition:.3s ease;
}

.status-dot{
    width:12px;
    height:12px;
    border-radius:50%;
}

.quick-item{
    display:flex;
    align-items:center;
    gap:15px;
    margin-bottom:25px;
    transition:.3s ease;
}

.quick-item i{
    font-size:22px;
}

.quick-item small{
    color:#6b7280;
}

.quick-item h6{
    margin:0;
    font-weight:700;
}

.section-title{
    font-weight:800;
    color:#1f2937;
}

.form-label{
    font-weight:700;
    color:#374151;
    margin-bottom:8px;
}

.input-icon-box,
.textarea-icon-box{
    position:relative;
}

.input-icon-box i{
    position:absolute;
    top:50%;
    left:18px;
    transform:translateY(-50%);
    color:#0d6efd;
    font-size:16px;
    z-index:2;
    transition:.3s ease;
}

.textarea-icon-box i{
    position:absolute;
    top:20px;
    left:18px;
    color:#0d6efd;
    font-size:17px;
    z-index:2;
    transition:.3s ease;
}

.custom-input{
    height:46px;
    border:none;
    border-radius:14px;
    padding:10px 16px;
    background:#f8fafc;
    box-shadow:inset 0 0 0 1px #e5e7eb;
    transition:.3s ease;
}

.custom-input.with-icon{
    padding-left:50px;
}

.custom-input:focus{
    background:white;
    box-shadow:0 0 0 4px rgba(13,110,253,.10);
}

.custom-textarea{
    border:none;
    border-radius:16px;
    padding:16px;
    background:#f8fafc;
    resize:none;
    box-shadow:inset 0 0 0 1px #e5e7eb;
    transition:.3s ease;
}

.custom-textarea.with-icon{
    padding-left:50px;
}

.custom-textarea:focus{
    background:white;
    box-shadow:0 0 0 4px rgba(13,110,253,.10);
}

.input-icon-box:hover .custom-input,
.textarea-icon-box:hover .custom-textarea{
    transform:translateY(-2px);
    box-shadow:0 8px 22px rgba(13,110,253,.10), inset 0 0 0 1px #0d6efd;
}

.input-icon-box:hover i,
.textarea-icon-box:hover i{
    color:#00aaff;
}

.divider{
    height:2px;
    background:linear-gradient(to right, transparent, #0d6efd, transparent);
    opacity:.2;
}

.save-btn{
    background:linear-gradient(135deg,#0d6efd,#00c6ff);
    border:none;
}

.btn{
    transition:.3s ease;
}

.btn:hover{
    transform:translateY(-3px);
}

.quick-item:hover,
.status-item:hover{
    transform:translateX(6px);
}

.animated-section{
    animation:fadeUp .6s ease both;
}

.delay-1{
    animation-delay:.1s;
}

.delay-2{
    animation-delay:.2s;
}

.delay-3{
    animation-delay:.3s;
}

@keyframes fadeUp{
    from{
        opacity:0;
        transform:translateY(18px);
    }

    to{
        opacity:1;
        transform:translateY(0);
    }
}

@keyframes fadeDown{
    from{
        opacity:0;
        transform:translateY(-12px);
    }

    to{
        opacity:1;
        transform:translateY(0);
    }
}

@keyframes cardEnter{
    from{
        opacity:0;
        transform:scale(.97);
    }

    to{
        opacity:1;
        transform:scale(1);
    }
}

@keyframes pulseSoft{
    0%{
        box-shadow:0 0 0 0 rgba(255,255,255,.25);
    }

    70%{
        box-shadow:0 0 0 18px rgba(255,255,255,0);
    }

    100%{
        box-shadow:0 0 0 0 rgba(255,255,255,0);
    }
}

@media(max-width:768px){
    .content-wrapper{
        overflow:auto;
    }

    .form-scroll{
        height:auto;
        overflow:visible;
        padding-right:0;
    }

    .fixed-panel{
        position:relative;
        top:0;
    }

    .card-body.p-5{
        padding:24px !important;
    }


}

</style>

@stop