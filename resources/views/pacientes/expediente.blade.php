@extends('adminlte::page')

@section('title', 'Expediente Paciente')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="fw-bold text-dark mb-1">
                <i class="fas fa-folder-open text-primary"></i>
                Expediente del Paciente
            </h1>

            <small class="text-muted">
                Historial clínico, recetas, archivos y notas médicas
            </small>
        </div>

        <a href="{{ url('pacientes') }}" class="btn btn-outline-primary rounded-pill px-4">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
@stop

@section('content')

<div class="container-fluid">

    <nav class="mb-3 small text-muted">
        <i class="fas fa-home"></i>
        Pacientes /
        <strong class="text-dark">Juan Pérez García</strong>
    </nav>

    {{-- HEADER PACIENTE --}}
    <div class="patient-hero shadow-lg mb-4">
        <div class="d-flex align-items-center flex-wrap gap-3">
            <div class="avatar-xl">JP</div>

            <div class="flex-grow-1">
                <h3 class="fw-bold mb-1">Juan Pérez García</h3>

                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge bg-primary rounded-pill px-3 py-2">Expediente #0001</span>
                    <span class="badge bg-info rounded-pill px-3 py-2">35 años</span>
                    <span class="badge bg-secondary rounded-pill px-3 py-2">Masculino</span>
                    <span class="badge bg-success rounded-pill px-3 py-2">Paciente activo</span>
                </div>
            </div>

            <a href="{{ url('consultas/create') }}" class="btn btn-success rounded-pill px-4 shadow-sm">
                <i class="fas fa-stethoscope"></i>
                Nueva consulta
            </a>
        </div>
    </div>

    <div class="row">

        {{-- COLUMNA IZQUIERDA --}}
        <div class="col-lg-3">

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-id-card text-primary"></i>
                        Información básica
                    </h6>
                </div>

                <div class="card-body">
                    <div class="info-item">
                        <span>Teléfono</span>
                        <strong>555-123-4567</strong>
                    </div>

                    <div class="info-item">
                        <span>Correo</span>
                        <strong>paciente@email.com</strong>
                    </div>

                    <div class="info-item">
                        <span>Tipo sangre</span>
                        <strong>O+</strong>
                    </div>

                    <div class="info-item">
                        <span>Alergias</span>
                        <strong>Penicilina</strong>
                    </div>

                    <div class="info-item mb-0">
                        <span>Fecha nacimiento</span>
                        <strong>12/03/1990</strong>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-triangle-exclamation text-danger"></i>
                        Alertas médicas
                    </h6>
                </div>

                <div class="card-body">
                    <div class="medical-alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <strong>Alergia a penicilina</strong>
                            <small>Evitar medicamentos relacionados.</small>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- COLUMNA DERECHA --}}
        <div class="col-lg-9">

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">

                <div class="card-header bg-white border-0 pt-3 pb-0">
                    <ul class="nav nav-tabs custom-tabs border-0">

                        <li class="nav-item">
                            <button class="nav-link active"
                                    data-toggle="tab"
                                    data-target="#consultas"
                                    type="button">
                                <i class="fas fa-stethoscope"></i>
                                Consultas
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link"
                                    data-toggle="tab"
                                    data-target="#recetas"
                                    type="button">
                                <i class="fas fa-prescription"></i>
                                Recetas
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link"
                                    data-toggle="tab"
                                    data-target="#archivos"
                                    type="button">
                                <i class="fas fa-file-medical"></i>
                                Archivos
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link"
                                    data-toggle="tab"
                                    data-target="#notas"
                                    type="button">
                                <i class="fas fa-notes-medical"></i>
                                Notas
                            </button>
                        </li>

                    </ul>
                </div>

                <div class="card-body tab-content p-4">

                    {{-- CONSULTAS --}}
                    <div class="tab-pane fade show active" id="consultas">

                        <div class="section-title">
                            <h5>Historial de consultas</h5>
                            <small>Registro cronológico de atención médica</small>
                        </div>

                        <div class="timeline-card">
                            <div class="timeline-dot bg-success"></div>

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between flex-wrap gap-2">
                                    <h6 class="fw-bold mb-0">10 Mayo 2026</h6>
                                    <span class="badge bg-success rounded-pill px-3 py-2">
                                        Consulta general
                                    </span>
                                </div>

                                <p class="mt-2 mb-1">
                                    <strong>Motivo:</strong> Dolor de garganta
                                </p>

                                <p class="text-muted small mb-3">
                                    Diagnóstico: Faringitis leve.
                                </p>

                                <a href="{{ url('HistorialConsulta') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    Ver consulta completa
                                </a>
                            </div>
                        </div>

                        <div class="timeline-card">
                            <div class="timeline-dot bg-warning"></div>

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between flex-wrap gap-2">
                                    <h6 class="fw-bold mb-0">02 Abril 2026</h6>
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                        Seguimiento
                                    </span>
                                </div>

                                <p class="mt-2 mb-0 text-muted">
                                    Control general del paciente.
                                </p>
                            </div>
                        </div>

                    </div>

                    {{-- RECETAS --}}
                    <div class="tab-pane fade" id="recetas">

                        <div class="section-title">
                            <h5>Historial de recetas</h5>
                            <small>Tratamientos indicados al paciente</small>
                        </div>

                        <div class="record-card">
                            <div>
                                <h6 class="fw-bold mb-1">10 Mayo 2026</h6>
                                <p class="mb-0 text-muted">
                                    Amoxicilina 500mg, cada 8 horas por 7 días.
                                </p>
                            </div>

                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                Ver receta
                            </button>
                        </div>

                        <div class="record-card">
                            <div>
                                <h6 class="fw-bold mb-1">02 Abril 2026</h6>
                                <p class="mb-0 text-muted">
                                    Ibuprofeno 400mg, cada 12 horas por 3 días.
                                </p>
                            </div>

                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                Ver receta
                            </button>
                        </div>

                    </div>

                    {{-- ARCHIVOS --}}
                    <div class="tab-pane fade" id="archivos">

                        <div class="section-title">
                            <h5>Archivos clínicos</h5>
                            <small>Estudios, análisis e imágenes médicas</small>
                        </div>

                        <div class="row">

                            <div class="col-md-4">
                                <div class="file-card">
                                    <i class="fas fa-file-pdf text-danger"></i>

                                    <h6>Laboratorio_sangre.pdf</h6>

                                    <small class="text-muted">
                                        02 Abril 2026
                                    </small>

                                    <button class="btn btn-sm btn-outline-primary rounded-pill mt-3">
                                        Ver archivo
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="file-card">
                                    <i class="fas fa-file-image text-primary"></i>

                                    <h6>Radiografia_torax.jpg</h6>

                                    <small class="text-muted">
                                        18 Marzo 2026
                                    </small>

                                    <button class="btn btn-sm btn-outline-primary rounded-pill mt-3">
                                        Ver archivo
                                    </button>
                                </div>
                            </div>

                        </div>

                    </div>

                    {{-- NOTAS --}}
                    <div class="tab-pane fade" id="notas">

                        <div class="section-title">
                            <h5>Notas médicas</h5>
                            <small>Observaciones generales del paciente</small>
                        </div>

                        <div class="note-card">
                            <small class="text-muted">
                                15 Mayo 2026
                            </small>

                            <p class="mb-0 mt-2">
                                Paciente reporta mejoría notable.
                                Continuar tratamiento actual.
                            </p>
                        </div>

                        <div class="note-card">
                            <small class="text-muted">
                                03 Abril 2026
                            </small>

                            <p class="mb-0 mt-2">
                                Se recomienda mantener hidratación y vigilancia de síntomas.
                            </p>
                        </div>

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
    background:#f4f6f9;
}

.patient-hero{
    background:linear-gradient(135deg,#ffffff,#eef6ff);
    border-radius:24px;
    padding:24px;
    border:1px solid #e5edf7;
}

.avatar-xl{
    width:90px;
    height:90px;
    border-radius:50%;
    background:linear-gradient(135deg,#0d6efd,#00c6ff);
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:30px;
    font-weight:800;
    box-shadow:0 10px 25px rgba(13,110,253,.25);
}

.rounded-4{
    border-radius:24px !important;
}

.info-item{
    display:flex;
    justify-content:space-between;
    gap:12px;
    padding:12px 0;
    border-bottom:1px solid #edf0f4;
}

.info-item span{
    color:#6c757d;
    font-size:13px;
}

.info-item strong{
    color:#1f2937;
    font-size:13px;
    text-align:right;
}

.medical-alert{
    background:#fff5f5;
    color:#dc3545;
    border:1px solid #ffd2d2;
    border-radius:16px;
    padding:14px;
    display:flex;
    gap:12px;
    align-items:flex-start;
}

.medical-alert i{
    font-size:22px;
    margin-top:3px;
}

.medical-alert small{
    display:block;
    color:#8a3a3a;
}

.custom-tabs .nav-link{
    border:none;
    color:#6c757d;
    font-weight:700;
    padding:14px 18px;
    border-radius:16px 16px 0 0;
}

.custom-tabs .nav-link.active{
    background:#f4f8ff;
    color:#0d6efd;
}

.section-title{
    margin-bottom:20px;
}

.section-title h5{
    font-weight:800;
    margin-bottom:2px;
}

.section-title small{
    color:#6c757d;
}

.timeline-card,
.record-card,
.note-card{
    background:#fff;
    border:1px solid #edf0f4;
    border-radius:18px;
    padding:18px;
    margin-bottom:16px;
    box-shadow:0 8px 20px rgba(0,0,0,.04);
}

.timeline-card{
    display:flex;
    gap:16px;
}

.timeline-dot{
    width:14px;
    height:14px;
    border-radius:50%;
    margin-top:5px;
    flex-shrink:0;
}

.record-card{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:16px;
}

.file-card{
    border:1px solid #edf0f4;
    border-radius:20px;
    padding:22px;
    text-align:center;
    box-shadow:0 8px 20px rgba(0,0,0,.04);
    margin-bottom:16px;
}

.file-card i{
    font-size:42px;
    margin-bottom:14px;
}

.file-card h6{
    font-weight:700;
    word-break:break-word;
}

@media(max-width:768px){
    .record-card{
        flex-direction:column;
        align-items:flex-start;
    }

    .custom-tabs .nav-link{
        padding:10px 12px;
        font-size:13px;
    }

    .patient-hero{
        padding:18px;
    }
}
</style>
@stop

@section('js')
<script>
    console.log('Expediente premium cargado');
</script>
@stop