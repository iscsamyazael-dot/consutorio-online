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
    
    <div class="d-flex align-items-center gap-3 header-actions">
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

{{-- MODAL 2: EXPEDIENTE --}}
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
                    <button type="button" class="btn btn-light border px-4 rounded-3 fw-bold" data-close-modal="expedientePacienteModal">Cancelar</button>
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

/* =========================
   FIX BUSCADOR SIDEBAR
   Input + lupa en una sola pastilla
========================= */

.main-sidebar .sidebar-search,
.main-sidebar form.form-inline {
    padding: 8px 14px !important;
}

.main-sidebar .input-group {
    background: rgba(255,255,255,0.10) !important;
    border-radius: 10px !important;
    border: 1px solid rgba(255,255,255,0.15) !important;
    overflow: hidden !important;
    flex-wrap: nowrap !important;
    align-items: center !important;
}

.main-sidebar .input-group .form-control {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    color: #fff !important;
    font-size: 13px !important;
    height: 36px !important;
    padding: 6px 10px !important;
    border-radius: 0 !important;
    outline: none !important;
    min-height: unset !important;
}

.main-sidebar .input-group .form-control::placeholder {
    color: rgba(255,255,255,0.40) !important;
}

.main-sidebar .input-group-append {
    display: flex !important;
    align-items: center !important;
}

.main-sidebar .input-group-append .btn,
.main-sidebar .input-group .btn {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    color: rgba(255,255,255,0.60) !important;
    height: 36px !important;
    padding: 0 12px !important;
    border-radius: 0 !important;
    display: flex !important;
    align-items: center !important;
    min-height: unset !important;
    transition: color .2s !important;
}

.main-sidebar .input-group .btn:hover {
    color: #fff !important;
    background: transparent !important;
}

/* =========================
   FONDO GENERAL
========================= */

body,
.content-wrapper,
.wrapper,
.content {
    background: #f3f7fb !important;
    font-family: 'Plus Jakarta Sans', sans-serif !important;
}

/* =========================
   TITULOS
========================= */

.fw-black { font-weight: 800 !important; }
.tracking-tight { letter-spacing: -0.03em; }
.tracking-wider { letter-spacing: .08em; }

/* =========================
   CARDS PREMIUM
========================= */

.card {
    border: none !important;
    border-radius: 24px !important;
    overflow: hidden;
    background: rgba(255,255,255,.92) !important;
    backdrop-filter: blur(18px);
    box-shadow: 0 10px 30px rgba(15,23,42,.05), inset 0 1px 0 rgba(255,255,255,.6) !important;
}

.card-header { background: transparent !important; border: none !important; }

/* =========================
   STATS
========================= */

.modern-stat-card {
    background: rgba(255,255,255,.95);
    border-radius: 24px;
    overflow: hidden;
    position: relative;
    transition: .35s ease;
    border: none !important;
    box-shadow: 0 12px 24px rgba(0,0,0,.04);
}

.modern-stat-card:hover { transform: translateY(-6px); box-shadow: 0 20px 35px rgba(0,0,0,.08); }

.card-body-custom { padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; }

.border-start-blue  { border-left: 5px solid #3b82f6 !important; }
.border-start-green { border-left: 5px solid #22c55e !important; }
.border-start-orange{ border-left: 5px solid #f59e0b !important; }
.border-start-red   { border-left: 5px solid #ef4444 !important; }

.stat-icon-box { width: 58px; height: 58px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
.bg-blue-soft   { background: rgba(59,130,246,.12) !important; }
.bg-green-soft  { background: rgba(34,197,94,.12) !important; }
.bg-orange-soft { background: rgba(245,158,11,.12) !important; }
.bg-red-soft    { background: rgba(239,68,68,.12) !important; }
.text-blue   { color: #2563eb !important; }
.text-green  { color: #16a34a !important; }
.text-orange { color: #d97706 !important; }
.text-red    { color: #dc2626 !important; }

/* =========================
   AVATARS
========================= */

.avatar-premium { width:55px!important; height:55px!important; border-radius:18px; display:flex!important; align-items:center!important; justify-content:center!important; box-shadow:0 10px 20px rgba(59,130,246,.25); }
.avatar-premium i { font-size: 1.2rem !important; }
.mini-avatar-premium { width:42px!important; height:42px!important; border-radius:14px; display:flex!important; align-items:center!important; justify-content:center!important; }
.mini-avatar-premium i { font-size: 1rem !important; }

/* =========================
   PANEL PACIENTE
========================= */

.patient-hero-card { background: linear-gradient(135deg, rgba(59,130,246,.08), rgba(255,255,255,.9)); border: 1px solid #e2e8f0; border-radius: 22px !important; }
.panel-details-table td { border-bottom: 1px dashed #e5e7eb; padding: .9rem 0 !important; }
.symptoms-alert { background: rgba(239,68,68,.05); border: 1px solid rgba(239,68,68,.12); }

/* =========================
   BADGES
========================= */

.badge-premium { padding: .6rem .9rem; border-radius: 12px; font-weight: 700; letter-spacing: .03em; }
.badge-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }

/* =========================
   TABLA
========================= */

.premium-table { border-collapse: separate; border-spacing: 0 12px; }
.premium-table thead th { border: none !important; font-size: .72rem !important; }
.premium-row { transition: .25s ease; }
.premium-row td { background: #fff !important; border-top: 1px solid #eef2f7 !important; border-bottom: 1px solid #eef2f7 !important; }
.premium-row td:first-child { border-left: 1px solid #eef2f7 !important; border-top-left-radius: 18px; border-bottom-left-radius: 18px; }
.premium-row td:last-child  { border-right: 1px solid #eef2f7 !important; border-top-right-radius: 18px; border-bottom-right-radius: 18px; }
.premium-row:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(0,0,0,.06); }

/* =========================
   BOTONES
========================= */

.btn { border-radius: 14px !important; font-weight: 700 !important; }
.btn-primary { border: none !important; background: linear-gradient(135deg, #2563eb, #3b82f6) !important; box-shadow: 0 10px 20px rgba(37,99,235,.25); }
.btn-primary:hover { transform: translateY(-2px); }
.btn-icon-premium { width: 38px; height: 38px; border: none; border-radius: 12px; background: #f8fafc; transition: .2s; }
.btn-icon-premium:hover { transform: scale(1.1); background: #eef2ff; }

/* =========================
   MODALES
========================= */

.modal-content { border: none !important; border-radius: 28px !important; overflow: hidden; background: rgba(255,255,255,.96) !important; backdrop-filter: blur(20px); }
.modal-header.bg-dark    { background: linear-gradient(135deg, #0f172a, #1e293b) !important; }
.modal-header.bg-primary { background: linear-gradient(135deg, #2563eb, #3b82f6) !important; }

/* =========================
   INPUTS
========================= */

.form-control,
.input-group-text {
    border-radius: 14px !important;
    border-color: #e2e8f0 !important;
    min-height: 48px;
}

.form-control:focus { border-color: #3b82f6 !important; box-shadow: 0 0 0 .2rem rgba(37,99,235,.12) !important; }

/* =========================
   DRAG ZONE
========================= */

.upload-drag-zone { border: 2px dashed #cbd5e1; background: #f8fafc; border-radius: 22px; transition: .25s ease; }
.upload-drag-zone:hover { border-color: #3b82f6; background: #eff6ff; }

/* =========================
   LIVE DOT
========================= */

.live-dot { width: 10px; height: 10px; background: #22c55e; border-radius: 999px; animation: pulseDot 2s infinite; }

@keyframes pulseDot {
    0%   { box-shadow: 0 0 0 0 rgba(34,197,94,.5); }
    70%  { box-shadow: 0 0 0 10px rgba(34,197,94,0); }
    100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
}

/* =========================
   LAYOUT ESTABLE
========================= */

.main-sidebar { position: fixed !important; top: 0; left: 0; height: 100vh; overflow-y: auto; overflow-x: hidden; z-index: 1035; }
.main-header  { position: sticky; top: 0; z-index: 1030; background: rgba(255,255,255,.92) !important; backdrop-filter: blur(10px); border-bottom: 1px solid rgba(226,232,240,.7); }
.content-wrapper { margin-left: 250px !important; min-height: 100vh; overflow-x: hidden; background: #f3f7fb !important; padding-bottom: 30px; }
.content { padding-top: 1rem; padding-bottom: 2rem; }
.table-responsive { overflow-x: auto; }
.modal { z-index: 9999 !important; }
.row { --bs-gutter-x: 1.5rem; }

/* =========================
   RESPONSIVE
========================= */

@media (max-width: 991px) {
    .content-wrapper { margin-left: 0 !important; }
    .main-sidebar { position: fixed !important; transform: translateX(-100%); transition: .3s ease; }
    .sidebar-open .main-sidebar { transform: translateX(0); }
    .modern-stat-card { margin-bottom: 1rem; }
    .card-body-custom { padding: 1.2rem; }
    .patient-hero-card { flex-direction: column; align-items: flex-start !important; gap: 1rem; }
    .table-responsive { border-radius: 18px; }
}

/* =========================
   SCROLLBAR
========================= */

::-webkit-scrollbar { width: 8px; height: 8px; }
::-webkit-scrollbar-thumb { background: rgba(5,6,8,0.35); border-radius: 20px; }
::-webkit-scrollbar-track { background: transparent; }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
@stop

@section('js')
<script>
document.addEventListener("DOMContentLoaded", function () {

    const modalDetalles   = document.getElementById('verPacienteModal');
    const modalExpediente = document.getElementById('expedientePacienteModal');

    function abrirModal(modal) {
        modal.style.display = 'block';
        setTimeout(() => modal.classList.add('show'), 10);
        modal.setAttribute('aria-modal', 'true');
        modal.removeAttribute('aria-hidden');
        document.body.classList.add('modal-open');
        document.body.style.overflow = 'hidden';
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'custom-backdrop';
        document.body.appendChild(backdrop);
    }

    function cerrarModal(modal) {
        modal.classList.remove('show');
        setTimeout(() => modal.style.display = 'none', 200);
        modal.setAttribute('aria-hidden', 'true');
        modal.removeAttribute('aria-modal');
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
    }

    document.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', function () {
            cerrarModal(document.getElementById(this.dataset.closeModal));
        });
    });

    document.querySelectorAll('.btn-close').forEach(btn => {
        btn.addEventListener('click', function () {
            cerrarModal(this.closest('.modal'));
        });
    });

    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) cerrarModal(modal);
        });
    });

    document.querySelectorAll('.btn-select-active').forEach(btn => {
        btn.addEventListener('click', function () {
            const f = this.closest('tr');
            document.getElementById('side-nombre').innerText      = f.dataset.nombre;
            document.getElementById('side-diagnostico').innerText = f.dataset.diagnostico;
            document.getElementById('side-folio-code').innerText  = f.dataset.folio;
            document.getElementById('side-fecha').innerText       = f.dataset.fecha;
            document.getElementById('side-sintomas').innerText    = f.dataset.sintomas;

            const ep = document.getElementById('side-estado');
            ep.innerText  = f.dataset.estado;
            ep.className  = f.dataset.estado.toLowerCase() === 'en consulta'
                ? 'badge badge-premium bg-success-soft text-success border border-success-subtle'
                : 'badge badge-premium bg-primary-soft text-primary border border-primary-subtle';

            const tp = document.getElementById('side-triage');
            tp.innerText = f.dataset.triage;
            tp.className = f.dataset.triage.toLowerCase() === 'grave'
                ? 'badge badge-premium bg-danger text-white'
                : 'badge badge-premium bg-light text-secondary border';

            const panel = document.getElementById('active-panel');
            panel.classList.add('animate__animated','animate__pulse');
            setTimeout(() => panel.classList.remove('animate__animated','animate__pulse'), 800);
        });
    });

    document.querySelectorAll('.btn-modal-trigger').forEach(btn => {
        btn.addEventListener('click', function () {
            cargarModal(this.closest('tr'));
            abrirModal(modalDetalles);
        });
    });

    function cargarModal(fila) {
        document.getElementById('modal-nombre').innerText  = fila.dataset.nombre;
        document.getElementById('modal-folio').innerText   = 'ID REGISTRO: ' + fila.dataset.folio;
        document.getElementById('modal-fecha').innerText   = fila.dataset.fecha;
        document.getElementById('modal-sintomas').innerText = fila.dataset.sintomas;

        document.getElementById('modal-estado-container').innerHTML =
            fila.dataset.estado.toLowerCase() === 'en consulta'
            ? `<span class="badge badge-premium bg-success-soft text-success border border-success-subtle w-100">En consulta</span>`
            : `<span class="badge badge-premium bg-primary-soft text-primary border border-primary-subtle w-100">Esperando</span>`;

        document.getElementById('modal-triage-container').innerHTML =
            fila.dataset.triage.toLowerCase() === 'grave'
            ? `<span class="badge badge-premium bg-danger text-white w-100">Grave</span>`
            : `<span class="badge badge-premium bg-light text-secondary border w-100">Normal</span>`;
    }

    document.querySelector('.btn-view-active').addEventListener('click', function () {
        document.getElementById('modal-nombre').innerText   = document.getElementById('side-nombre').innerText;
        document.getElementById('modal-folio').innerText    = 'ID REGISTRO: ' + document.getElementById('side-folio-code').innerText;
        document.getElementById('modal-fecha').innerText    = document.getElementById('side-fecha').innerText;
        document.getElementById('modal-sintomas').innerText = document.getElementById('side-sintomas').innerText;
        abrirModal(modalDetalles);
    });

    document.querySelector('.btn-expediente').addEventListener('click', function () {
        abrirExpediente(
            document.getElementById('side-nombre').innerText,
            document.getElementById('side-folio-code').innerText
        );
    });

    document.getElementById('modal-btn-action').addEventListener('click', function () {
        const nombre = document.getElementById('modal-nombre').innerText;
        const folio  = document.getElementById('modal-folio').innerText.replace('ID REGISTRO: ', '');
        cerrarModal(modalDetalles);
        abrirExpediente(nombre, folio);
    });

    function abrirExpediente(nombre, folio) {
        document.getElementById('exp-modal-nombre').innerText = nombre;
        document.getElementById('exp-modal-folio').innerText  = folio;
        document.getElementById('form-expediente-rapido').reset();
        document.getElementById('preview-files-box').innerHTML = '';
        abrirModal(modalExpediente);
    }

    document.getElementById('input-radiografias').addEventListener('change', function () {
        const preview = document.getElementById('preview-files-box');
        preview.innerHTML = '';
        Array.from(this.files).forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const div = document.createElement('div');
                    div.style.position = 'relative';
                    div.className = 'preview-image';
                    div.innerHTML = `
                        <img src="${e.target.result}" style="width:90px;height:90px;object-fit:cover;border-radius:14px;border:2px solid #e2e8f0;">
                        <button type="button" class="remove-preview btn btn-sm btn-danger"
                            style="position:absolute;top:-8px;right:-8px;border-radius:50%;width:24px;height:24px;padding:0;">×</button>`;
                    div.querySelector('button').addEventListener('click', () => div.remove());
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        });
    });

    document.querySelectorAll('.btn-delete-row').forEach(btn => {
        btn.addEventListener('click', function () {
            const fila   = this.closest('tr');
            const triage = fila.dataset.triage.toLowerCase();
            const estado = fila.dataset.estado.toLowerCase();
            if (confirm('¿Deseas eliminar este paciente?')) {
                fila.style.transition = '.3s ease';
                fila.style.opacity    = '0';
                fila.style.transform  = 'translateX(120px)';
                setTimeout(() => { fila.remove(); actualizarContadores(triage, estado); }, 300);
            }
        });
    });

    function actualizarContadores(triage, estado) {
        const dec = id => { const el = document.getElementById(id); const v = parseInt(el.innerText); if (v > 0) el.innerText = v - 1; };
        dec('stat-hoy');
        if (estado === 'en consulta') dec('stat-activos');
        else dec('stat-pendientes');
        if (triage === 'grave') dec('stat-urgencias');
        document.getElementById('total-badge').innerText =
            document.querySelectorAll('#tabla-consultas tbody tr').length + ' Pacientes';
    }
});
</script>
@stop