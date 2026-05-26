@extends('adminlte::page')

@section('title', 'Consultas Médicas')

@section('content_header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <div>
        <h1 class="fw-black text-dark m-0 d-flex align-items-center gap-2 tracking-tight">
            <span class="p-2 bg-primary-soft rounded-3 d-inline-flex align-items-center justify-content-center border border-primary-subtle">
                <i class="fas fa-heartbeat text-primary animate__animated animate__pulse animate__infinite"></i>
            </span>
            Centro de Consultas
        </h1>
        <p class="text-muted m-0 mt-2 fs-6">
            Plataforma médica inteligente de monitoreo clínico y flujos en tiempo real.
        </p>
    </div>
    
    <div class="d-flex align-items-center gap-3">
        <div class="bg-white px-3 py-2 rounded-pill shadow-xs d-flex align-items-center gap-2 border border-light-subtle">
            <span class="live-dot"></span>
            <small class="fw-bold text-secondary text-uppercase tracking-wider" style="font-size: 0.75rem;">Sistema Activo</small>
        </div>
        <a href="{{ url('consultas/create') }}" class="btn btn-primary rounded-3 px-4 py-2 shadow-sm fw-bold d-flex align-items-center gap-2 btn-hover-transform">
            <i class="fas fa-plus-circle"></i> Nueva Consulta
        </a>
    </div>
</div>
@stop
<input type="hidden" name="route" value="{{ url('/') }}">
@section('content')
<div class="container-fluid content-wrapper-custom">

    {{-- KANBAN/STATS PREMIUM --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="modern-stat-card border-start-blue shadow-sm">
                <div class="card-body-custom">
                    <div>
                        <span class="text-muted fw-semibold text-primary tracking-wider small">Consultas Hoy</span>
                        <h2 class="fw-black text-dark mt-1 mb-0" id="stat-hoy">24</h2>
                    </div>
                    <div class="stat-icon-box bg-blue-soft text-blue">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="modern-stat-card border-start-green shadow-sm">
                <div class="card-body-custom">
                    <div>
                        <span class="text-muted fw-semibold text-uppercase tracking-wider small">Pacientes Activos</span>
                        <h2 class="fw-black text-dark mt-1 mb-0" id="stat-activos">12</h2>
                    </div>
                    <div class="stat-icon-box bg-green-soft text-green">
                        <i class="fas fa-user-injured"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="modern-stat-card border-start-orange shadow-sm">
                <div class="card-body-custom">
                    <div>
                        <span class="text-muted fw-semibold text-uppercase tracking-wider small">Pendientes</span>
                        <h2 class="fw-black text-dark mt-1 mb-0" id="stat-pendientes">2</h2>
                    </div>
                    <div class="stat-icon-box bg-orange-soft text-orange">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="modern-stat-card border-start-red shadow-sm">
                <div class="card-body-custom">
                    <div>
                        <span class="text-muted fw-semibold text-uppercase tracking-wider small">Urgencias</span>
                        <h2 class="fw-black text-dark mt-1 mb-0" id="stat-urgencias">3</h2>
                    </div>
                    <div class="stat-icon-box bg-red-soft text-red">
                        <i class="fas fa-ambulance"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CONTENIDO PRINCIPAL --}}
    <div class="row">
        
        {{-- PANEL IZQUIERDO: DETALLE DE CONSULTA ACTIVA --}}
        <div class="col-xl-4 mb-4">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100" id="active-panel">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                        <span class="badge-dot bg-primary"></span> Panel de Atención
                    </h5>
                </div>
                <div class="card-body px-4 py-3">
                    
                    <div class="patient-hero-card p-3 rounded-3 mb-4 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="avatar-premium bg-primary text-white shadow-sm flex-shrink-0" id="side-avatar">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <div class="ms-3 text-start">
                                <h5 class="fw-bold mb-0 text-dark" id="side-nombre">Juan Pérez</h5>
                                <span class="text-muted small">Folio: <code class="text-primary fw-bold" id="side-folio-code">FOL-001</code></span>
                            </div>
                        </div>
                        <span class="badge badge-premium bg-success-soft text-success border border-success-subtle flex-shrink-0" id="side-estado">
                            En Consulta
                        </span>
                    </div>

                    <div class="mb-4">
                        <table class="table table-borderless align-middle m-0 panel-details-table">
                            <tbody>
                                <tr>
                                    <td class="ps-0 py-2 text-muted fw-medium" style="width: 45%;">
                                        <i class="fas fa-fingerprint text-secondary opacity-75 me-2"></i>Diagnóstico Prev.
                                    </td>
                                    <td class="pe-0 py-2 text-end text-dark fw-semibold small" id="side-diagnostico">
                                        Dolor abdominal
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-0 py-2 text-muted fw-medium">
                                        <i class="fas fa-calendar-check text-secondary opacity-75 me-2"></i>Fecha
                                    </td>
                                    <td class="pe-0 py-2 text-end text-dark fw-semibold" id="side-fecha">
                                        22 Mayo, 2026
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-0 py-2 text-muted fw-medium">
                                        <i class="fas fa-shield-virus text-secondary opacity-75 me-2"></i>Triage
                                    </td>
                                    <td class="pe-0 py-2 text-end">
                                        <span class="badge badge-premium bg-danger text-white fw-bold" id="side-triage">Grave</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="symptoms-alert p-3 rounded-3">
                        <h6 class="fw-bold text-danger mb-1 d-flex align-items-center gap-2">
                            <i class="fas fa-notes-medical"></i> Sintomatología Reportada
                        </h6>
                        <p class="text-dark m-0 small lh-base" id="side-sintomas">
                            Dolor abdominal agudo focalizado, mareos y fiebre persistente. Requiere evaluación prioritaria.
                        </p>
                    </div>
                </div>
                <div class="card-footer bg-light border-top-0 d-flex gap-2 justify-content-stretch px-4 py-3">
                    <button class="btn btn-light border flex-grow-1 btn-hover-transform btn-view-active" title="Ver Detalles">
                        <i class="fas fa-eye text-info"></i> Detalle
                    </button>
                    <a href="#" id="link-editar-panel" class="btn btn-light border flex-grow-1 btn-hover-transform" title="Editar Registro">
                        <i class="fas fa-pen text-warning"></i> Editar
                    </a>
                    <button class="btn btn-primary flex-grow-2 btn-hover-transform fw-bold btn-expediente" title="Historial Médico">
                        <i class="fas fa-file-medical me-1"></i> Expediente
                    </button>
                </div>
            </div>
        </div>

        {{-- TABLA DE CONSULTAS DEL DÍA --}}
        <div class="col-xl-8 mb-4">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-4 px-4 border-bottom-0">
                    <div>
                        <h5 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
                            <span class="badge-dot bg-secondary"></span> Lista de Espera del Día
                        </h5>
                    </div>
                    <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill fw-bold" id="total-badge">2 Pacientes</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table align-middle mb-0" id="tabla-consultas">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-muted fw-semibold text-uppercase tracking-wider small">Paciente / Diagnóstico Preliminar</th>
                                    <th class="py-3 text-muted fw-semibold text-uppercase tracking-wider small">Folio</th>
                                    <th class="py-3 text-muted fw-semibold text-uppercase tracking-wider small">Estado</th>
                                    <th class="py-3 text-muted fw-semibold text-uppercase tracking-wider small">Urgencia</th>
                                    <th class="text-end px-4 py-3 text-muted fw-semibold text-uppercase tracking-wider small">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="premium-row" id="fila-fol001"
                                    data-nombre="Juan Pérez"
                                    data-diagnostico="Dolor abdominal"
                                    data-folio="FOL-001"
                                    data-estado="En consulta"
                                    data-triage="Grave"
                                    data-fecha="22 Mayo 2026"
                                    data-sintomas="Dolor abdominal agudo focalizado en fosa ilíaca derecha, náuseas, mareos esporádicos y fiebre ligera cuantificada en 38.2°C.">
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="mini-avatar-premium bg-primary text-white flex-shrink-0">
                                                <i class="fas fa-user-md"></i>
                                            </div>
                                            <div class="ms-3 text-start">
                                                <div class="fw-bold text-dark mb-0 row-nombre">Juan Pérez</div>
                                                <div class="text-muted small lh-sm">Dolor abdominal</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border font-monospace px-2 py-1">FOL-001</span></td>
                                    <td><span class="badge badge-premium bg-success-soft text-success border border-success-subtle row-estado">En consulta</span></td>
                                    <td><span class="badge badge-premium bg-danger text-white row-triage">Grave</span></td>
                                    <td class="text-end px-4 py-3">
                                        <div class="d-flex justify-content-end gap-1">
                                            <button class="btn btn-icon-premium text-primary btn-select-active" title="Atender ahora"><i class="fas fa-play"></i></button>
                                            <button class="btn btn-icon-premium text-info btn-modal-trigger"><i class="fas fa-eye"></i></button>
                                            <button class="btn btn-icon-premium text-danger btn-delete-row"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="premium-row" id="fila-fol002"
                                    data-nombre="María López"
                                    data-diagnostico="Fiebre y tos severa"
                                    data-folio="FOL-002"
                                    data-estado="Esperando"
                                    data-triage="Normal"
                                    data-fecha="25 Mayo 2026"
                                    data-sintomas="Tos seca persistente desde hace 4 días, disnea leve al caminar rápido, temperatura corporal controlada en 37.8°C. Sin alergias declaradas.">
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="mini-avatar-premium bg-success text-white flex-shrink-0">
                                                <i class="fas fa-user-md"></i>
                                            </div>
                                            <div class="ms-3 text-start">
                                                <div class="fw-bold text-dark mb-0 row-nombre">María López</div>
                                                <div class="text-muted small lh-sm">Fiebre y tos severa</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border font-monospace px-2 py-1">FOL-002</span></td>
                                    <td><span class="badge badge-premium bg-primary-soft text-primary border border-primary-subtle row-estado">Esperando</span></td>
                                    <td><span class="badge badge-premium bg-light text-secondary border row-triage">Normal</span></td>
                                    <td class="text-end px-4 py-3">
                                        <div class="d-flex justify-content-end gap-1">
                                            <button class="btn btn-icon-premium text-primary btn-select-active" title="Atender ahora"><i class="fas fa-play"></i></button>
                                            <button class="btn btn-icon-premium text-info btn-modal-trigger"><i class="fas fa-eye"></i></button>
                                            <button class="btn btn-icon-premium text-danger btn-delete-row"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- MODAL 1: DETALLES PREMIUM --}}
<div class="modal fade" id="verPacienteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-dark py-4 px-4 d-flex align-items-center justify-content-between">
                <h5 class="modal-title fw-bold text-white m-0 d-flex align-items-center gap-2">
                    <i class="fas fa-shield-alt text-primary"></i> Ficha Clínica Digital
                </h5>
                <button type="button" class="btn-close btn-close-white opacity-75" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <div class="text-center mb-4">
                    <div class="avatar-premium bg-primary text-white shadow mx-auto mb-2" id="modal-avatar" style="width: 65px; height: 65px;">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h4 class="fw-black text-dark mb-1" id="modal-nombre">Juan Pérez</h4>
                    <span class="badge bg-light text-secondary border font-monospace px-2.5 py-1.5" id="modal-folio">ID REGISTRO: FOL-001</span>
                </div>
                
                <div class="row g-3 bg-light p-3 rounded-3 border border-light-subtle">
                    <div class="col-6">
                        <small class="text-muted d-block fw-semibold text-uppercase tracking-wider" style="font-size:0.65rem">Estado Actual</small>
                        <div id="modal-estado-container" class="mt-1"></div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block fw-semibold text-uppercase tracking-wider" style="font-size:0.65rem">Prioridad / Triage</small>
                        <div id="modal-triage-container" class="mt-1"></div>
                    </div>
                    <div class="col-12"><hr class="my-1 opacity-25"></div>
                    <div class="col-12">
                        <small class="text-muted d-block fw-semibold text-uppercase tracking-wider" style="font-size:0.65rem"><i class="fas fa-clock me-1"></i> Fecha de Atención</small>
                        <span class="text-dark fw-bold d-block mt-1" id="modal-fecha">22 Mayo 2026</span>
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block fw-semibold text-uppercase tracking-wider" style="font-size:0.65rem"><i class="fas fa-comment-medical me-1"></i> Síntomas y Notas de Admisión</small>
                        <p class="text-secondary m-0 small mt-1 lh-base" id="modal-sintomas">Detalles...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0 d-flex justify-content-between p-3 px-4">
                <button type="button" class="btn btn-light border px-4 rounded-3 fw-bold" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" id="modal-btn-action" class="btn btn-primary px-4 rounded-3 fw-bold d-flex align-items-center gap-2 btn-hover-transform">
                    <i class="fas fa-folder-open"></i> Abrir Expediente Completo
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 2: NUEVO MODAL DE EXPEDIENTE (DATOS BÁSICOS + RADIOGRAFÍAS) --}}
<div class="modal fade" id="expedientePacienteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form id="form-expediente-rapido" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0 bg-primary py-4 px-4 d-flex align-items-center justify-content-between">
                    <h5 class="modal-title fw-bold text-white m-0 d-flex align-items-center gap-2">
                        <i class="fas fa-file-medical"></i> Apertura de Expediente Clínico
                    </h5>
                    <button type="button" class="btn-close btn-close-white opacity-75" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-white">
                    <div class="d-flex align-items-center mb-4 p-3 rounded-3 bg-light border">
                        <div class="mini-avatar-premium bg-primary text-white flex-shrink-0">
                            <i class="fas fa-id-card-alt"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-black text-dark mb-0" id="exp-modal-nombre">Paciente</h5>
                            <span class="text-muted small">Folio Asociado: <code class="text-primary fw-bold" id="exp-modal-folio">FOL-000</code></span>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="fas fa-heartbeat text-danger"></i> 1. Signos Vitales y Somatometría
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-semibold small">Peso (kg)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted small"><i class="fas fa-weight"></i></span>
                                <input type="number" step="0.1" name="peso" class="form-control border-start-0" placeholder="0.0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-semibold small">Talla / Estatura (cm)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted small"><i class="fas fa-ruler-vertical"></i></span>
                                <input type="number" name="talla" class="form-control border-start-0" placeholder="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-semibold small">Presión Arterial (mm Hg)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted small"><i class="fas fa-heart"></i></span>
                                <input type="text" name="presion_arterial" class="form-control border-start-0" placeholder="120/80" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-secondary fw-semibold small">Diagnóstico / Observaciones Clínicas Iniciales</label>
                            <textarea name="diagnostico_observaciones" class="form-control" rows="3" placeholder="Escriba las conclusiones o anotaciones físicas del paciente..." required></textarea>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="fas fa-x-ray text-primary"></i> 2. Carga de Radiografías / Estudios Visuales
                    </h6>
                    <div class="upload-drag-zone p-4 rounded-3 text-center border-dashed position-relative mb-2">
                        <input type="file" name="radiografias[]" id="input-radiografias" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" multiple accept="image/*">
                        <div class="py-2">
                            <i class="fas fa-cloud-upload-alt text-primary display-4 mb-2 opacity-75"></i>
                            <p class="fw-bold text-dark mb-1 small">Arrastra aquí tus archivos o haz clic para buscar</p>
                            <p class="text-muted m-0 extra-small">Formatos permitidos: JPG, PNG. Puedes subir varias imágenes a la vez.</p>
                        </div>
                    </div>
                    <div id="preview-files-box" class="d-flex flex-wrap gap-2 mt-2"></div>

                </div>
                <div class="modal-footer bg-light border-top-0 d-flex justify-content-end p-3 px-4 gap-2">
                    <button type="button" class="btn btn-light border px-4 rounded-3 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4 rounded-3 fw-bold d-flex align-items-center gap-2 btn-hover-transform">
                        <i class="fas fa-save"></i> Guardar e Inicializar Expediente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    .content-wrapper-custom, .content-header {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }

    .fw-black { font-weight: 800 !important; }
    .tracking-tight { tracking: -0.025em; }
    .tracking-wider { letter-spacing: 0.05em; }
    .flex-grow-2 { flex-grow: 2 !important; }
    .cursor-pointer { cursor: pointer; }
    .extra-small { font-size: 0.75rem; }

    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.08) !important; }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.08) !important; }
    .bg-blue-soft { background-color: rgba(59, 130, 246, 0.1) !important; }
    .text-blue { color: #2563eb !important; }
    .bg-green-soft { background-color: rgba(34, 197, 94, 0.1) !important; }
    .text-green { color: #16a34a !important; }
    .bg-orange-soft { background-color: rgba(245, 158, 11, 0.1) !important; }
    .text-orange { color: #d97706 !important; }
    .bg-red-soft { background-color: rgba(239, 68, 68, 0.1) !important; }
    .text-red { color: #dc2626 !important; }

    .modern-stat-card {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .modern-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.04) !important;
    }
    .card-body-custom {
        padding: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .stat-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .border-start-blue { border-left: 4px solid #3b82f6; }
    .border-start-green { border-left: 4px solid #22c55e; }
    .border-start-orange { border-left: 4px solid #f59e0b; }
    .border-start-red { border-left: 4px solid #ef4444; }

    .badge-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }
    .patient-hero-card {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
    }

    .avatar-premium {
        width: 42px !important;
        height: 42px !important;
        border-radius: 10px;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .avatar-premium i {
        font-size: 1.1rem !important;
        line-height: 1 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .mini-avatar-premium {
        width: 34px !important;
        height: 34px !important;
        border-radius: 8px;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .mini-avatar-premium i {
        font-size: 0.95rem !important;
        line-height: 1 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    #modal-avatar i {
        font-size: 1.5rem !important;
    }

    .badge-premium {
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.8rem;
        display: inline-block;
        text-align: center;
    }
    
    .panel-details-table td {
        font-size: 0.9rem;
    }

    .symptoms-alert {
        background-color: rgba(220, 53, 69, 0.03);
        border: 1px dashed rgba(220, 53, 69, 0.25);
    }

    .premium-table th {
        font-size: 0.75rem !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    .premium-row {
        transition: background-color 0.2s ease;
    }
    .premium-row:hover {
        background-color: #f8fafc !important;
    }
    
    .btn-icon-premium {
        background: transparent;
        border: none;
        padding: 0.4rem;
        border-radius: 6px;
        transition: all 0.2s;
    }
    .btn-icon-premium:hover {
        background: #f1f5f9;
        transform: scale(1.15);
    }

    .btn-hover-transform {
        transition: all 0.2s ease;
    }
    .btn-hover-transform:hover {
        transform: scale(1.02);
    }
    .live-dot {
        width: 8px;
        height: 8px;
        background: #22c55e;
        border-radius: 50%;
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.5);
        animation: pulseDot 2s infinite;
    }

    /* ESTILO PREMIUM ZONA DRAG & DROP */
    .upload-drag-zone {
        border: 2px dashed #cbd5e1;
        background-color: #f8fafc;
        transition: all 0.2s ease;
    }
    .upload-drag-zone:hover {
        border-color: #3b82f6;
        background-color: rgba(59, 130, 246, 0.02);
    }

    @keyframes pulseDot {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.5); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    
    // Configuración de Instancias de Modales
    const modalDetalles = new bootstrap.Modal(document.getElementById('verPacienteModal'));
    const modalExpediente = new bootstrap.Modal(document.getElementById('expedientePacienteModal'));

    // Al dar clic en 'Expediente' del Panel Izquierdo
    jQuery('.btn-expediente').on('click', function(){
        const currentNombre = jQuery('#side-nombre').text();
        const currentFolio = jQuery('#side-folio-code').text();
        
        abrirModalExpediente(currentNombre, currentFolio);
    });

    // Al dar clic en 'Abrir Expediente Completo' desde dentro del Modal 1
    jQuery('#modal-btn-action').on('click', function() {
        const nombre = document.getElementById('modal-nombre').innerText;
        const rawFolio = document.getElementById('modal-folio').innerText;
        const folio = rawFolio.replace('ID REGISTRO: ', '');

        modalDetalles.hide(); // Ocultamos el primero
        abrirModalExpediente(nombre, folio); // Lanzamos el segundo
    });

    // Función unificada para configurar y abrir el modal del expediente
    function abrirModalExpediente(nombre, folio) {
        document.getElementById('exp-modal-nombre').innerText = nombre;
        document.getElementById('exp-modal-folio').innerText = folio;
        
        // Seteamos dinámicamente el action del form por si usas una ruta Laravel RESTful
        document.getElementById('form-expediente-rapido').action = `{{ url('consultas') }}/${folio}/expediente`;
        
        // Limpiamos campos previos
        document.getElementById('form-expediente-rapido').reset();
        document.getElementById('preview-files-box').innerHTML = '';

        modalExpediente.show();
    }

    // Lector de Previsualización de Imágenes en tiempo real
    document.getElementById('input-radiografias').addEventListener('change', function(e) {
        const previewBox = document.getElementById('preview-files-box');
        previewBox.innerHTML = ''; // Limpiar anteriores
        
        if (this.files) {
            Array.from(this.files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const div = document.createElement('div');
                        div.className = "position-relative border rounded p-1 bg-light";
                        div.style.width = "75px";
                        div.style.height = "75px";
                        div.innerHTML = `
                            <img src="${event.target.result}" class="w-100 h-100 object-fit-cover rounded" style="object-fit: cover;">
                            <span class="position-absolute top-0 end-0 badge bg-dark opacity-75 cursor-pointer rounded-circle m-1" onclick="this.parentElement.remove()" style="padding: 2px 5px; font-size:10px;">&times;</span>
                        `;
                        previewBox.appendChild(div);
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    });

    // TRIGER MODAL DETALLES DESDE LA TABLA
    jQuery('#tabla-consultas').on('click', '.btn-modal-trigger', function () {
        const fila = jQuery(this).closest('tr');
        cargarModalDesdeRow(fila);
    });

    jQuery('.btn-view-active').on('click', function() {
        const dataActive = {
            nombre: jQuery('#side-nombre').text(),
            folio: jQuery('#side-folio-code').text(),
            estado: jQuery('#side-estado').text().trim(),
            triage: jQuery('#side-triage').text().trim(),
            fecha: jQuery('#side-fecha').text(),
            sintomas: jQuery('#side-sintomas').text().trim()
        };
        rellenarModal(dataActive);
        modalDetalles.show();
    });

    function cargarModalDesdeRow(fila) {
        const data = {
            nombre: fila.data('nombre'),
            folio: fila.data('folio'),
            estado: fila.data('estado'),
            triage: fila.data('triage'),
            fecha: fila.data('fecha'),
            sintomas: fila.data('sintomas')
        };
        rellenarModal(data);
        modalDetalles.show();
    }

    function rellenarModal(data) {
        document.getElementById('modal-nombre').innerText = data.nombre;
        document.getElementById('modal-folio').innerText = `ID REGISTRO: ${data.folio}`;
        document.getElementById('modal-fecha').innerText = data.fecha;
        document.getElementById('modal-sintomas').innerText = data.sintomas;

        const estadoContainer = document.getElementById('modal-estado-container');
        if(data.estado.toLowerCase() === 'en consulta') {
            estadoContainer.innerHTML = `<span class="badge badge-premium bg-success-soft text-success border border-success-subtle w-100 py-2">En consulta</span>`;
        } else {
            estadoContainer.innerHTML = `<span class="badge badge-premium bg-primary-soft text-primary border border-primary-subtle w-100 py-2">Esperando</span>`;
        }

        const triageContainer = document.getElementById('modal-triage-container');
        if(data.triage.toLowerCase() === 'grave') {
            triageContainer.innerHTML = `<span class="badge badge-premium bg-danger text-white w-100 py-2">Grave</span>`;
        } else {
            triageContainer.innerHTML = `<span class="badge badge-premium bg-light text-secondary border w-100 py-2">Normal</span>`;
        }
    }

    // CAMBIAR PACIENTE ACTIVO EN PANEL IZQUIERDO
    jQuery('#tabla-consultas').on('click', '.btn-select-active', function() {
        const fila = jQuery(this).closest('tr');
        
        const nombre = fila.data('nombre');
        const diagnostico = fila.data('diagnostico');
        const folio = fila.data('folio');
        const estado = fila.data('estado');
        const triage = fila.data('triage');
        const fecha = fila.data('fecha');
        const sintomas = fila.data('sintomas');

        const panel = jQuery('#active-panel');
        panel.addClass('animate__animated animate__fadeIn');
        setTimeout(() => panel.removeClass('animate__animated animate__fadeIn'), 600);

        jQuery('#side-nombre').text(nombre);
        jQuery('#side-diagnostico').text(diagnostico);
        jQuery('#side-folio-code').text(folio);
        jQuery('#side-fecha').text(fecha);
        jQuery('#side-sintomas').text(sintomas);
        jQuery('#link-editar-panel').attr('href', `{{ url('consultas') }}/${folio}/edit`);

        const sideEstado = jQuery('#side-estado');
        sideEstado.text(estado);
        if(estado.toLowerCase() === 'en consulta') {
            sideEstado.removeClass('bg-primary-soft text-primary border-primary-subtle').addClass('bg-success-soft text-success border-success-subtle');
        } else {
            sideEstado.removeClass('bg-success-soft text-success border-success-subtle').addClass('bg-primary-soft text-primary border-primary-subtle');
        }

        const sideTriage = jQuery('#side-triage');
        sideTriage.text(triage);
        if(triage.toLowerCase() === 'grave') {
            sideTriage.removeClass('bg-light text-secondary border').addClass('bg-danger text-white');
        } else {
            sideTriage.removeClass('bg-danger text-white').addClass('bg-light text-secondary border');
        }
    });

    // REMOVER REGISTROS
    jQuery('#tabla-consultas').on('click', '.btn-delete-row', function() {
        const fila = jQuery(this).closest('tr');
        const triage = fila.data('triage').toLowerCase();
        const estado = fila.data('estado').toLowerCase();

        if (confirm('¿Está seguro de remover a este paciente de la lista de espera?')) {
            fila.addClass('animate__animated animate__fadeOutRight');
            setTimeout(() => {
                fila.remove();
                recalcularContadores(triage, estado);
            }, 500);
        }
    });

    function recalcularContadores(triage, estado) {
        let hoy = parseInt(jQuery('#stat-hoy').text());
        if(hoy > 0) jQuery('#stat-hoy').text(hoy - 1);

        if(estado === 'en consulta') {
            let activos = parseInt(jQuery('#stat-activos').text());
            if(activos > 0) jQuery('#stat-activos').text(activos - 1);
        } else if(estado === 'esperando') {
            let pendientes = parseInt(jQuery('#stat-pendientes').text());
            if(pendientes > 0) jQuery('#stat-pendientes').text(pendientes - 1);
        }

        if(triage === 'grave') {
            let urgencias = parseInt(jQuery('#stat-urgencias').text());
            if(urgencias > 0) jQuery('#stat-urgencias').text(urgencias - 1);
        }

        const numeroFilas = jQuery('#tabla-consultas tbody tr').length;
        jQuery('#total-badge').text(`${numeroFilas} Pacientes`);
    }
});
</script>
@stop