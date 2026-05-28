@extends('adminlte::page')

@section('title', 'Registrar Paciente - Sistema de Triage')

@section('content_header')

<div class="premium-header">

    <div>
        <h1>
            <i class="fas fa-heartbeat heartbeat-icon"></i>
            REGISTRO DE NUEVO PACIENTE
        </h1>
    </div>

    <div class="live-status">
        <span class="pulse-dot"></span>
        Consulta Activa
    </div>

</div>

@stop

@section('content')

<div class="container-fluid">

    <input type="hidden" name="route" value="{{ url('/') }}">

    <div class="row g-4">

        <!-- CARD SUPERIOR -->
        <div class="col-12">

            <div class="glass-card horizontal-profile-card">

                <!-- IZQUIERDA -->
                <div class="profile-main-section">

                    <div class="patient-avatar-horizontal">
                        <i class="fas fa-user-injured"></i>
                    </div>

                    <div class="profile-text-horizontal">

                        <h3 class="fw-bold m-0">
                            Consulta General
                        </h3>

                        <p class="profile-subtitle m-0">
                            Registro médico inteligente y monitoreo clínico
                        </p>

                    </div>

                </div>

                <!-- STATUS -->
                <div class="status-container-horizontal">

                    <div class="status-item-horizontal">

                        <div class="status-icon green">
                            <i class="fas fa-check"></i>
                        </div>

                        <div>
                            <span>Estado</span>
                            <h6>Consulta activa</h6>
                        </div>

                    </div>

                    <div class="status-item-horizontal">

                        <div class="status-icon orange">
                            <i class="fas fa-exclamation"></i>
                        </div>

                        <div>
                            <span>Prioridad</span>
                            <h6>Moderada</h6>
                        </div>

                    </div>

                </div>

                <!-- INFO -->
                <div class="info-box-horizontal">

                    <div class="info-item">

                        <div class="info-icon blue">
                            <i class="fas fa-user-md"></i>
                        </div>

                        <div>
                            <small>Médico</small>
                            <h6>Dr. Martínez</h6>
                        </div>

                    </div>

                    <div class="info-item">

                        <div class="info-icon green">
                            <i class="fas fa-calendar-alt"></i>
                        </div>

                        <div>
                            <small>Fecha</small>
                            <h6>22 Mayo 2026</h6>
                        </div>

                    </div>

                    <div class="info-item">

                        <div class="info-icon red">
                            <i class="fas fa-heartbeat"></i>
                        </div>

                        <div>
                            <small>Estado</small>
                            <h6>En evaluación</h6>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- FORM -->
        <div class="col-12">

            <div class="glass-card form-card">

                <div class="card-body p-5">

                    <form>

                        <!-- INFORMACION -->
                        <div class="section-header">

                            <div>

                                <h3>
                                    <i class="fas fa-notes-medical"></i>
                                    Información del Paciente
                                </h3>

                                <p>
                                    Complete los datos clínicos de la consulta
                                </p>

                            </div>

                        </div>

                        <!-- CAMPOS -->
                        <div class="row g-4 mt-2">

                            <div class="col-md-3">

                                <label class="form-label">
                                    Paciente
                                </label>

                                <input type="text"
                                       class="form-control premium-input no-icon"
                                       placeholder="Nombre del paciente">

                            </div>

                            <div class="col-md-3">

                                <label class="form-label">
                                    Sexo
                                </label>

                                <select class="form-control premium-input no-icon">

                                    <option selected disabled>
                                        Seleccionar...
                                    </option>

                                    <option>
                                        Masculino
                                    </option>

                                    <option>
                                        Femenino
                                    </option>

                                    <option>
                                        Otro
                                    </option>

                                </select>

                            </div>

                            <div class="col-md-3">

                                <label class="form-label">
                                    Edad
                                </label>

                                <input type="number"
                                       class="form-control premium-input no-icon"
                                       placeholder="Ej. 25">

                            </div>

                            <div class="col-md-3">

                                <label class="form-label">
                                    Fecha de Nacimiento
                                </label>

                                <input type="date"
                                       class="form-control premium-input no-icon">

                            </div>

                            <div class="col-md-4">

                                <label class="form-label">
                                    Número de Teléfono
                                </label>

                                <input type="text"
                                       class="form-control premium-input no-icon"
                                       placeholder="9999999999">

                            </div>

                            <div class="col-md-4">

                                <label class="form-label">
                                    Dirección
                                </label>

                                <input type="text"
                                       class="form-control premium-input no-icon"
                                       placeholder="Calle, Número, Colonia">

                            </div>

                        </div>

                        <div class="premium-divider"></div>

                        <!-- TRIAGE -->
                        <div class="section-header">

                            <div>

                                <h3>
                                    <i class="fas fa-procedures text-danger"></i>
                                    TRIAGE
                                </h3>

                                <p>
                                    Evaluación inicial y signos vitales del paciente
                                </p>

                            </div>

                        </div>

                        <!-- SIGNOS -->
                        <div class="row g-4">

                            <!-- PRESION -->
                            <div class="col-md-4">

                                <label class="form-label">
                                    Presión (mmHg)
                                </label>

                                <input type="text"
                                       class="form-control premium-input no-icon"
                                       placeholder="Ej. 120/80">

                            </div>

                            <!-- SATURACION -->
                            <div class="col-md-4">

                                <label class="form-label">
                                    Saturación (%)
                                </label>

                                <input type="number"
                                       class="form-control premium-input no-icon"
                                       placeholder="Ej. 98">

                            </div>

                            <!-- TEMPERATURA -->
                            <div class="col-md-4">

                                <label class="form-label">
                                    Temperatura (°C)
                                </label>

                                <input type="text"
                                       class="form-control premium-input no-icon"
                                       placeholder="Ej. 36.5">

                            </div>

                        </div>

                        <!-- SINTOMAS -->
                        <div class="mb-4">

                            <label class="form-label">
                                Síntomas
                            </label>

                            <textarea class="form-control premium-textarea"
                                      rows="4"
                                      placeholder="Ingrese síntomas del paciente"></textarea>

                        </div>

                        <!-- ESTADO -->
                        <div class="mt-4">

                            <label class="form-label">
                                Estado del paciente
                            </label>

                            <select class="form-control premium-input no-icon">

                                <option selected disabled>
                                    Seleccione el estado...
                                </option>

                                <option value="Riesgo Alto">
                                    🔴 Riesgo Alto
                                </option>

                                <option value="Observación">
                                    🟡 Observación
                                </option>

                                <option value="Estable">
                                    🟢 Estable
                                </option>

                            </select>

                        </div>

                        <!-- BOTONES -->
                        <div class="d-flex justify-content-end gap-3 mt-5 flex-wrap">

                            <button type="button" class="btn cancel-btn">
                                Cancelar
                            </button>

                            <button type="submit" class="btn save-btn">
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

/* ======================= */
/* FONDO */
/* ======================= */

body{
    background: radial-gradient(circle at top left, #dbeafe 0%, #f4f7fb 45%, #eef2ff 100%);
    overflow: hidden !important;
}

/* ======================= */
/* SIDEBAR FIJO */
/* ======================= */

.main-sidebar{
    position: fixed !important;
    top: 0;
    left: 0;
    height: 100vh !important;
    overflow-y: auto;
    overflow-x: hidden;
    z-index: 1050;
}

/* ======================= */
/* CONTENIDO */
/* ======================= */

.content-wrapper{
    height: 100vh;
    overflow-y: auto !important;
    overflow-x: hidden;
    margin-left: 250px !important;
    padding-bottom: 40px;
}

/* ======================= */
/* HEADER */
/* ======================= */

.main-header{
    position: sticky;
    top: 0;
    z-index: 1040;
    background: white;
}

.premium-header{
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 30px;
}

.premium-header h1{
    font-size: 38px;
    font-weight: 900;
    color: #111827;
    margin-bottom: 8px;
}

/* ======================= */
/* STATUS */
/* ======================= */

.live-status{
    background: white;
    padding: 14px 22px;
    border-radius: 50px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
}

.pulse-dot{
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #22c55e;
    animation: pulse 1.5s infinite;
}

@keyframes pulse{
    0%{
        transform: scale(1);
        opacity: 1;
    }

    50%{
        transform: scale(1.4);
        opacity: .5;
    }

    100%{
        transform: scale(1);
        opacity: 1;
    }
}

/* ======================= */
/* GLASS CARD */
/* ======================= */

.glass-card{
    background: rgba(255,255,255,.72);
    backdrop-filter: blur(18px);
    border-radius: 35px;
    border: 1px solid rgba(255,255,255,.5);
    box-shadow: 0 20px 50px rgba(0,0,0,.08);
    overflow: hidden;
    transition: .4s;
}

.glass-card:hover{
    transform: translateY(-5px);
}

/* ======================= */
/* CARD HORIZONTAL */
/* ======================= */

.horizontal-profile-card{
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    padding: 25px 35px;
    flex-wrap: wrap;
    gap: 25px;
}

.profile-main-section{
    display: flex;
    align-items: center;
    gap: 20px;
    flex: 1;
    min-width: 280px;
}

.patient-avatar-horizontal{
    width: 80px;
    height: 80px;
    border-radius: 24px;
    background: linear-gradient(135deg, #2563eb, #38bdf8);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 35px;
    color: white;
    box-shadow: 0 15px 30px rgba(37,99,235,.2);
}

.profile-text-horizontal h3{
    font-size: 24px;
    color: #111827;
}

.profile-subtitle{
    color: #6b7280;
    font-size: 14px;
}

/* ======================= */
/* STATUS HORIZONTAL */
/* ======================= */

.status-container-horizontal{
    display: flex;
    flex-direction: row;
    gap: 15px;
    flex-wrap: wrap;
}

.status-item-horizontal{
    background: #f8fafc;
    border-radius: 20px;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.status-item-horizontal span{
    font-size: 12px;
    color: #6b7280;
}

.status-item-horizontal h6{
    margin: 0;
    font-weight: 700;
}

.status-icon{
    width: 40px;
    height: 40px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.green{
    background: #22c55e;
}

.orange{
    background: #f59e0b;
}

.blue{
    background: #2563eb;
}

.red{
    background: #ef4444;
}

/* ======================= */
/* INFO */
/* ======================= */

.info-box-horizontal{
    display: flex;
    gap: 25px;
    border-left: 2px solid rgba(37,99,235,.1);
    padding-left: 25px;
    flex-wrap: wrap;
}

.info-item{
    display: flex;
    align-items: center;
    gap: 12px;
}

.info-icon{
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

/* ======================= */
/* FORM */
/* ======================= */

.section-header{
    margin-bottom: 25px;
}

.section-header h3{
    font-weight: 900;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-header p{
    color: #6b7280;
    margin-top: 8px;
}

/* ======================= */
/* INPUTS */
/* ======================= */

.form-label{
    font-weight: 700;
    color: #374151;
    margin-bottom: 12px;
}

.premium-input{
    height: 47px;
    border: none;
    border-radius: 20px;
    padding-left: 22px;
    background: #f8fafc;
    box-shadow: inset 0 0 0 1px #e5e7eb;
    transition: .3s;
}

.premium-input:focus{
    background: white;
    box-shadow: 0 0 0 5px rgba(37,99,235,.12);
}

/* ======================= */
/* SELECT */
/* ======================= */

select.premium-input{
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    cursor: pointer;
}

/* ======================= */
/* TEXTAREA */
/* ======================= */

.premium-textarea{
    border: none;
    border-radius: 25px;
    padding: 22px;
    background: #f8fafc;
    resize: none;
    box-shadow: inset 0 0 0 1px #e5e7eb;
    transition: .3s;
    min-height: 120px;
}

.premium-textarea:focus{
    background: white;
    box-shadow: 0 0 0 5px rgba(37,99,235,.12);
}

/* ======================= */
/* DIVIDER */
/* ======================= */

.premium-divider{
    height: 2px;
    margin: 45px 0;
    background: linear-gradient(to right, transparent, #2563eb, transparent);
    opacity: .2;
}

/* ======================= */
/* BOTONES */
/* ======================= */

.btn{
    border: none;
    border-radius: 18px;
    padding: 16px 28px;
    font-weight: 700;
    transition: .3s;
}

.btn:hover{
    transform: translateY(-4px);
}

.cancel-btn{
    background: white;
    color: #374151;
    box-shadow: 0 10px 20px rgba(0,0,0,.06);
}

.save-btn{
    background: linear-gradient(135deg, #2563eb, #38bdf8);
    color: white;
    box-shadow: 0 15px 30px rgba(37,99,235,.25);
}

/* ======================= */
/* ICON */
/* ======================= */

.heartbeat-icon{
    color: #2563eb;
    animation: heartbeat 1.5s infinite;
}

/* ======================= */
/* RESPONSIVE */
/* ======================= */

@media (max-width: 991px){

    .content-wrapper{
        margin-left: 0 !important;
    }

    body.sidebar-open .content-wrapper{
        overflow: hidden;
    }

    .horizontal-profile-card{
        flex-direction: column;
        align-items: flex-start;
    }

    .info-box-horizontal{
        border-left: none;
        padding-left: 0;
    }

}

</style>

@stop

@section('js')
    @vite('resources/js/app.js')
@stop