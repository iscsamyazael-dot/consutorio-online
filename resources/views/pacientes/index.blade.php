@extends('adminlte::page')

@section('title', 'Lista Pacientes')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <input type="hidden" name="route" value="{{ url('/') }}">
        <div>
            <h1 class="fw-bold text-dark mb-1">
                <i class="fas fa-user-injured text-primary"></i>
                Lista de Pacientes
            </h1>

            <small class="text-muted">
                Gestión médica inteligente
            </small>
        </div>

       <a href="{{ url('pacientes/create') }}" class="btn btn-primary shadow-sm px-4 rounded-pill">
    <i class="fas fa-user-plus"></i>
    Nuevo Paciente
</a>

    </div>
@stop

@section('content')

<div class="container-fluid">

    {{-- TARJETAS SUPERIORES --}}
    <div class="row mb-4">

        <div class="col-md-3">
            <div class="small-box bg-primary shadow border-0 rounded-4">
                <div class="inner">
                    <h3>245</h3>
                    <p>Total Pacientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-success shadow border-0 rounded-4">
                <div class="inner">
                    <h3>34</h3>
                    <p>Consultas Hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-stethoscope"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning shadow border-0 rounded-4">
                <div class="inner">
                    <h3>12</h3>
                    <p>Urgencias</p>
                </div>
                <div class="icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-danger shadow border-0 rounded-4">
                <div class="inner">
                    <h3>5</h3>
                    <p>Hospitalizados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-procedures"></i>
                </div>
            </div>
        </div>

    </div>
<div id="app">
    <pacientes-index></pacientes-index>
</div>
    
    
</div>

{{-- MODAL VER --}}
<div class="modal fade" id="verpacienteModal" tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 rounded-4 overflow-hidden">

            <div class="modal-header bg-primary text-white border-0">

                <h5 class="modal-title fw-bold">
                    <i class="fas fa-user-circle"></i>
                    Información del Paciente
                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body p-4">

                <div class="patient-profile mb-4">

                    <div class="avatar-large">
                        S
                    </div>

                    <div>
                        <h3 class="fw-bold mb-1">
                            Samy Azael Lopez Acosta
                        </h3>

                        <span class="badge bg-success">
                            Consulta activa
                        </span>
                    </div>

                </div>

                <div class="row g-4">

                    <div class="col-md-6">
                        <div class="info-card">
                            <label>Teléfono</label>
                            <h6>9889677449</h6>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-card">
                            <label>Sexo</label>
                            <h6>Masculino</h6>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-card">
                            <label>Tipo de Sangre</label>
                            <h6>O Positivo</h6>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-card">
                            <label>Alergias</label>
                            <h6>Ninguna</h6>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="info-card">
                            <label>Dirección</label>
                            <h6>
                                Calle 10a x 15 y 17 Sudzal Yucatán
                            </h6>
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

/* CARD */
.card{
    border-radius:24px;
}

/* TABLA */
.table thead th{
    border:none;
    padding:18px;
    font-weight:700;
    color:#495057;
}

.table tbody td{
    padding:18px;
    vertical-align:middle;
}

.table-hover tbody tr:hover{
    background:#f8fbff;
    transition:.3s;
}

/* AVATAR */
.avatar-circle{
    width:50px;
    height:50px;
    border-radius:50%;
    background:linear-gradient(135deg,#0d6efd,#00c6ff);
    display:flex;
    align-items:center;
    justify-content:center;
    color:white;
    font-weight:bold;
    font-size:20px;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
}

.avatar-large{
    width:80px;
    height:80px;
    border-radius:50%;
    background:linear-gradient(135deg,#0d6efd,#00c6ff);
    display:flex;
    align-items:center;
    justify-content:center;
    color:white;
    font-size:30px;
    font-weight:bold;
}

/* BOTONES */
.action-btn{
    border-radius:12px;
    transition:.3s;
    box-shadow:0 3px 8px rgba(0,0,0,.1);
}

.action-btn:hover{
    transform:translateY(-3px);
}

/* SEARCH */
.search-box{
    position:relative;
    width:300px;
}

.search-box i{
    position:absolute;
    top:13px;
    left:15px;
    color:#999;
}

.search-box input{
    padding-left:40px;
    border-radius:14px;
    border:1px solid #e5e7eb;
    height:45px;
}

/* MODAL */
.modal-content{
    box-shadow:0 10px 40px rgba(0,0,0,.15);
}

.patient-profile{
    display:flex;
    align-items:center;
    gap:20px;
}

.info-card{
    background:#f8fafc;
    border-radius:16px;
    padding:18px;
}

.info-card label{
    color:#6c757d;
    font-size:14px;
}

.info-card h6{
    margin-top:8px;
    font-weight:700;
}

/* TARJETAS */
.small-box{
    border-radius:22px !important;
}

.small-box .icon{
    top:10px;
}

.small-box:hover{
    transform:translateY(-5px);
    transition:.3s;
}

</style>

@stop



@section('js')
<script>
    console.log('Vista premium cargada');
</script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
@stop