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

        <a href="{{ url('PacienteNuevo') }}" class="btn btn-primary shadow-sm px-4 rounded-pill">
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
                    <p>pendientes</p>
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
@stop

@section('css')

<style>
body{
    background:#f4f6f9;
}

.card{
    border-radius:24px;
}

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

.action-btn{
    border-radius:12px;
    transition:.3s;
    box-shadow:0 3px 8px rgba(0,0,0,.1);
}

.action-btn:hover{
    transform:translateY(-3px);
}

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