@extends('adminlte::page')

@section('title', 'Consultas Médicas')

@section('content_header')

<div class="top-header">

    <div>

        <h1>

            <i class="fas fa-heartbeat"></i>
            Centro de Consultas

        </h1>

        <p>
            Panel médico inteligente y monitoreo clínico
        </p>

    </div>

    <a href="{{ url('consultas/create') }}"
       class="new-btn">

        <i class="fas fa-plus"></i>
        Nueva Consulta

    </a>

</div>

@stop

@section('content')

<div class="container-fluid">

    {{-- STATS --}}
    <div class="row mb-4">

        <div class="col-lg-3 col-md-6 mb-4">

            <div class="stats-card blue-card">

                <div>

                    <span>Consultas Hoy</span>

                    <h2>24</h2>

                </div>

                <i class="fas fa-stethoscope"></i>

            </div>

        </div>

        <div class="col-lg-3 col-md-6 mb-4">

            <div class="stats-card green-card">

                <div>

                    <span>Pacientes Activos</span>

                    <h2>12</h2>

                </div>

                <i class="fas fa-user-injured"></i>

            </div>

        </div>

        <div class="col-lg-3 col-md-6 mb-4">

            <div class="stats-card orange-card">

                <div>

                    <span>Pendientes</span>

                    <h2>5</h2>

                </div>

                <i class="fas fa-clock"></i>

            </div>

        </div>

        <div class="col-lg-3 col-md-6 mb-4">

            <div class="stats-card red-card">

                <div>

                    <span>Urgencias</span>

                    <h2>3</h2>

                </div>

                <i class="fas fa-ambulance"></i>

            </div>

        </div>

    </div>

    {{-- CONSULTAS --}}
    <div class="row g-4">

        {{-- CONSULTA CARD --}}
        <div class="col-xl-4 col-lg-6">

            <div class="consulta-card">

                {{-- TOP --}}
                <div class="consulta-top">

                    <div class="patient-box">

                        <div class="patient-avatar">

                            J

                        </div>

                        <div>

                            <h4>
                                Juan Pérez
                            </h4>

                            <span>
                                Dolor abdominal
                            </span>

                        </div>

                    </div>

                    <span class="status active-status">

                        En Consulta

                    </span>

                </div>

                {{-- BODY --}}
                <div class="consulta-body">

                    <div class="info-item">

                        <i class="fas fa-user-md"></i>

                        <span>
                            Dr. Martínez
                        </span>

                    </div>

                    <div class="info-item">

                        <i class="fas fa-calendar"></i>

                        <span>
                            22 Mayo 2026
                        </span>

                    </div>

                    <div class="info-item">

                        <i class="fas fa-layer-group"></i>

                        <span>
                            Prioridad Moderada
                        </span>

                    </div>

                    <div class="symptoms-box">

                        <strong>Síntomas:</strong>

                        <p>

                            Dolor abdominal, mareos y fiebre ligera.

                        </p>

                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="consulta-footer">

                    <button class="action-btn blue-btn">

                        <i class="fas fa-eye"></i>

                    </button>

                    <button class="action-btn yellow-btn">

                        <i class="fas fa-edit"></i>

                    </button>

                    <button class="action-btn green-btn">

                        <i class="fas fa-file-medical"></i>

                    </button>

                    <button class="action-btn red-btn">

                        <i class="fas fa-trash"></i>

                    </button>

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

    background:
    linear-gradient(to bottom right,
    #f4f7fb,
    #edf4ff);
}

/* HEADER */

.top-header{

    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;

    margin-bottom:30px;
}

.top-header h1{

    font-weight:900;

    color:#111827;

    margin-bottom:5px;
}

.top-header p{

    color:#6b7280;
}

/* BOTON */

.new-btn{

    background:
    linear-gradient(135deg,#0d6efd,#00c6ff);

    color:white;

    padding:14px 28px;

    border-radius:18px;

    text-decoration:none;

    font-weight:700;

    box-shadow:
    0 10px 25px rgba(13,110,253,.25);

    transition:.3s;
}

.new-btn:hover{

    transform:translateY(-4px);

    color:white;
}

/* STATS */

.stats-card{

    border-radius:30px;

    padding:30px;

    color:white;

    display:flex;
    justify-content:space-between;
    align-items:center;

    box-shadow:
    0 20px 40px rgba(0,0,0,.10);

    transition:.3s;
}

.stats-card:hover{

    transform:translateY(-6px);
}

.stats-card span{

    opacity:.8;
}

.stats-card h2{

    font-size:40px;

    font-weight:900;

    margin-top:10px;
}

.stats-card i{

    font-size:45px;

    opacity:.3;
}

/* COLORES */

.blue-card{

    background:
    linear-gradient(135deg,#2563eb,#38bdf8);
}

.green-card{

    background:
    linear-gradient(135deg,#16a34a,#4ade80);
}

.orange-card{

    background:
    linear-gradient(135deg,#f59e0b,#fbbf24);
}

.red-card{

    background:
    linear-gradient(135deg,#dc2626,#fb7185);
}

/* CONSULTA CARD */

.consulta-card{

    background:rgba(255,255,255,.75);

    backdrop-filter:blur(16px);

    border-radius:35px;

    overflow:hidden;

    padding:30px;

    border:1px solid rgba(255,255,255,.6);

    box-shadow:
    0 20px 50px rgba(0,0,0,.08);

    transition:.4s;
}

.consulta-card:hover{

    transform:
    translateY(-8px)
    scale(1.01);
}

/* TOP */

.consulta-top{

    display:flex;
    justify-content:space-between;
    align-items:center;

    margin-bottom:25px;
}

/* AVATAR */

.patient-box{

    display:flex;
    align-items:center;
    gap:15px;
}

.patient-avatar{

    width:70px;
    height:70px;

    border-radius:24px;

    background:
    linear-gradient(135deg,#2563eb,#38bdf8);

    color:white;

    display:flex;
    align-items:center;
    justify-content:center;

    font-size:28px;
    font-weight:900;

    box-shadow:
    0 10px 25px rgba(37,99,235,.30);
}

/* NOMBRE */

.patient-box h4{

    margin:0;

    font-weight:800;

    color:#111827;
}

.patient-box span{

    color:#6b7280;
}

/* STATUS */

.status{

    padding:10px 16px;

    border-radius:50px;

    font-size:13px;

    font-weight:700;
}

.active-status{

    background:#dcfce7;

    color:#166534;
}

/* BODY */

.info-item{

    display:flex;
    align-items:center;
    gap:14px;

    margin-bottom:18px;

    color:#374151;
}

.info-item i{

    width:42px;
    height:42px;

    border-radius:14px;

    background:#eff6ff;

    color:#2563eb;

    display:flex;
    align-items:center;
    justify-content:center;
}

/* SINTOMAS */

.symptoms-box{

    background:#f8fafc;

    padding:18px;

    border-radius:20px;

    margin-top:20px;
}

.symptoms-box p{

    margin-top:10px;

    color:#4b5563;
}

/* FOOTER */

.consulta-footer{

    display:flex;
    justify-content:space-between;

    margin-top:30px;
}

/* BOTONES */

.action-btn{

    width:52px;
    height:52px;

    border:none;

    border-radius:18px;

    color:white;

    font-size:18px;

    transition:.3s;
}

.action-btn:hover{

    transform:translateY(-4px);
}

/* COLORES BOTONES */

.blue-btn{

    background:#3b82f6;
}

.yellow-btn{

    background:#f59e0b;
}

.green-btn{

    background:#10b981;
}

.red-btn{

    background:#ef4444;
}

</style>

@stop