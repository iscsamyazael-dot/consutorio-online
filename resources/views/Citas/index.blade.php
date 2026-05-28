@extends('adminlte::page')

@section('title', 'Agenda Médica')

@section('content_header')

<div class="planner-header">

    <div>
        <h1 class="planner-title">
            Agenda Médica
        </h1>

        <p class="planner-subtitle">
            Sistema profesional de planificación de citas
        </p>
    </div>

    <div class="planner-actions">

        <div class="live-date" id="liveDate"></div>

        <a href="{{ route('citas.create') }}" class="btn-create">

            <i class="fas fa-plus"></i>
            Nueva Cita

        </a>

    </div>

</div>

@stop


@section('content')

<div class="planner-layout">

    <!-- SIDEBAR -->
    <aside class="planner-sidebar">

        <!-- PERFIL -->
        <div class="sidebar-card doctor-card">

            <div class="doctor-avatar">
                <i class="fas fa-user-md"></i>
            </div>

            <h4>Dr. Administrador</h4>

            <p>
                Consultorio Médico
            </p>

            <div class="doctor-status">

                <span class="status-dot"></span>
                Activo

            </div>

        </div>

        <!-- NUEVO PANEL -->
        <div class="sidebar-card">

            <div class="sidebar-title">
                Resumen Médico
            </div>

            <div class="stats-box">

                <div class="stat-item stat-blue">

                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>

                    <div class="stat-info">
                        <h5>{{ $citas->count() }}</h5>
                        <span>Total Citas</span>
                    </div>

                </div>

                <div class="stat-item stat-green">

                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>

                    <div class="stat-info">
                        <h5>
                            {{ $citas->where('estado','Completada')->count() }}
                        </h5>

                        <span>Completadas</span>
                    </div>

                </div>

                <div class="stat-item stat-orange">

                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>

                    <div class="stat-info">
                        <h5>
                            {{ $citas->where('estado','Pendiente')->count() }}
                        </h5>

                        <span>Pendientes</span>
                    </div>

                </div>

            </div>

        </div>

        <!-- PRÓXIMAS CITAS -->
        <div class="sidebar-card">

            <div class="sidebar-title">
                Próximas Citas
            </div>

            @foreach($citas->take(5) as $cita)

            <div class="appointment-card">

                <div class="appointment-color {{ strtolower($cita->estado) }}"></div>

                <div class="appointment-content">

                    <strong>
                        {{ $cita->paciente->nombre ?? 'Paciente' }}
                    </strong>

                    <small>
                        {{ $cita->fecha_cita }}
                    </small>

                </div>

                <div class="appointment-hour">

                    {{ \Carbon\Carbon::parse($cita->hora_cita)->format('H:i') }}

                </div>

            </div>

            @endforeach

        </div>

    </aside>


    <!-- MAIN -->
    <main class="planner-main">

        <div class="calendar-container">

            <div class="calendar-topbar">

                <div>

                    <h3>
                        Calendario
                    </h3>

                    <p>
                        Planificación inteligente de citas médicas
                    </p>

                </div>

                <!-- BUSCADOR -->
                <div class="calendar-search">

                    <i class="fas fa-search"></i>

                    <input
                        type="text"
                        id="searchAppointments"
                        placeholder="Buscar paciente..."
                    >

                </div>

            </div>

            <div id="calendar"></div>

        </div>

    </main>

</div>

@stop


@section('css')

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css' rel='stylesheet' />

<style>

/* ===================================================
   ESTILO MODERNO COMPACTO
=================================================== */

body{
    background:#f1f5f9;
    font-family:'Inter',sans-serif;
}

.content-wrapper{
    background:#f1f5f9;
}

/* HEADER */

.planner-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    flex-wrap:wrap;

    background:linear-gradient(135deg,#0f172a,#1e293b);

    border-radius:18px;

    padding:16px 20px;

    margin-bottom:16px;

    box-shadow:0 6px 20px rgba(15,23,42,.08);
}

.planner-title{
    color:white;
    font-size:1.6rem;
    font-weight:800;
    margin:0;
}

.planner-subtitle{
    color:rgba(255,255,255,.7);
    margin-top:4px;
    font-size:.82rem;
}

.planner-actions{
    display:flex;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
}

.live-date{
    background:rgba(255,255,255,.08);

    border:1px solid rgba(255,255,255,.08);

    color:white;

    padding:9px 14px;

    border-radius:12px;

    font-size:.82rem;
    font-weight:600;
}

.btn-create{
    background:white;
    color:#0f172a;

    padding:10px 14px;

    border-radius:12px;

    font-size:.85rem;

    font-weight:700;

    text-decoration:none;

    transition:.2s ease;
}

.btn-create:hover{
    background:#e2e8f0;
    color:#0f172a;
    text-decoration:none;
}

/* LAYOUT */

.planner-layout{
    display:grid;
    grid-template-columns:220px 1fr;
    gap:14px;
    align-items:start;
}

/* SIDEBAR */

.planner-sidebar{
    display:flex;
    flex-direction:column;
    gap:14px;
}

.sidebar-card{
    background:rgba(255,255,255,.88);

    backdrop-filter:blur(12px);

    border-radius:18px;

    border:1px solid rgba(255,255,255,.8);

    padding:16px;

    box-shadow:0 4px 16px rgba(15,23,42,.04);
}

/* PERFIL */

.doctor-card{
    text-align:center;
}

.doctor-avatar{
    width:58px;
    height:58px;

    border-radius:50%;

    background:linear-gradient(135deg,#3b82f6,#6366f1);

    display:flex;
    align-items:center;
    justify-content:center;

    color:white;
    font-size:22px;

    margin:auto auto 12px;

    box-shadow:0 8px 18px rgba(59,130,246,.25);
}

.doctor-card h4{
    margin:0;
    font-size:1.05rem;
    font-weight:700;
    color:#0f172a;
}

.doctor-card p{
    color:#64748b;
    margin-top:5px;
    font-size:.82rem;
}

.doctor-status{
    display:inline-flex;
    align-items:center;
    gap:7px;

    margin-top:10px;

    background:#eef2ff;
    color:#4338ca;

    padding:7px 14px;

    border-radius:30px;

    font-size:.8rem;
    font-weight:600;
}

.status-dot{
    width:8px;
    height:8px;

    border-radius:50%;

    background:#6366f1;
}

/* ESTADÍSTICAS */

.sidebar-title{
    font-size:.95rem;
    font-weight:700;
    margin-bottom:12px;
}

.stats-box{
    display:flex;
    flex-direction:column;
    gap:12px;
}

.stat-item{
    display:flex;
    align-items:center;
    gap:12px;

    padding:12px;

    border-radius:14px;

    color:white;
}

.stat-blue{
    background:linear-gradient(135deg,#3b82f6,#2563eb);
}

.stat-green{
    background:linear-gradient(135deg,#10b981,#059669);
}

.stat-orange{
    background:linear-gradient(135deg,#f97316,#ea580c);
}

.stat-icon{
    width:42px;
    height:42px;

    border-radius:12px;

    background:rgba(255,255,255,.15);

    display:flex;
    align-items:center;
    justify-content:center;

    font-size:1rem;
}

.stat-info h5{
    margin:0;
    font-size:1.2rem;
    font-weight:800;
}

.stat-info span{
    font-size:.78rem;
}

/* APPOINTMENTS */

.appointment-card{
    display:flex;
    align-items:center;
    gap:8px;

    padding:8px;

    border-radius:12px;

    margin-bottom:8px;

    background:#f8fafc;

    border:1px solid #e2e8f0;
}

.appointment-color{
    width:5px;
    height:38px;
    border-radius:10px;
}

.appointment-color.pendiente{
    background:#f59e0b;
}

.appointment-color.completada{
    background:#10b981;
}

.appointment-color.cancelada{
    background:#ef4444;
}

.appointment-content{
    flex:1;
    display:flex;
    flex-direction:column;
}

.appointment-content strong{
    color:#0f172a;
    font-size:.80rem;
}

.appointment-content small{
    color:#64748b;
    font-size:.70rem;
}

.appointment-hour{
    font-size:.72rem;
    font-weight:700;
    color:#334155;
}

/* MAIN */

.calendar-container{
    background:rgba(255,255,255,.88);

    backdrop-filter:blur(14px);

    border-radius:20px;

    border:1px solid rgba(255,255,255,.8);

    padding:16px;

    overflow:hidden;

    box-shadow:0 8px 24px rgba(15,23,42,.05);
}

/* TOPBAR */

.calendar-topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;

    gap:16px;

    flex-wrap:wrap;

    margin-bottom:14px;
}

.calendar-topbar h3{
    margin:0;
    font-size:1.3rem;
    font-weight:800;
    color:#0f172a;
}

.calendar-topbar p{
    margin:4px 0 0;
    color:#64748b;
    font-size:.82rem;
}

/* SEARCH */

.calendar-search{
    position:relative;
    width:220px;
}

.calendar-search i{
    position:absolute;

    left:14px;
    top:50%;

    transform:translateY(-50%);

    color:#94a3b8;
}

.calendar-search input{
    width:100%;
    height:40px;

    border-radius:12px;

    border:1px solid #dbe4ee;

    background:white;

    padding:0 16px 0 40px;

    outline:none;

    font-size:.82rem;
}

/* CALENDAR */

#calendar{
    min-height:580px;
}

.fc{
    font-family:inherit;
}

.fc-toolbar{
    margin-bottom:14px !important;
}

.fc-toolbar-title{
    font-size:1.1rem !important;
    font-weight:800 !important;
}

.fc .fc-button{
    background:white !important;

    border:1px solid #e2e8f0 !important;

    color:#334155 !important;

    border-radius:10px !important;

    padding:6px 12px !important;

    font-size:.78rem !important;

    font-weight:700 !important;
}

.fc .fc-button-primary:not(:disabled).fc-button-active{
    background:linear-gradient(135deg,#3b82f6,#6366f1) !important;

    border:none !important;

    color:white !important;
}

.fc-scrollgrid{
    border:none !important;
    border-radius:18px;
    overflow:hidden;
}

.fc-col-header-cell{
    background:#f8fafc;
    border:none !important;
    padding:12px 0 !important;
}

.fc-col-header-cell-cushion{
    color:#64748b !important;
    font-size:.82rem !important;
    font-weight:700 !important;
    text-decoration:none !important;
}

.fc-daygrid-day-frame{
    min-height:75px !important;
    padding:4px !important;
}

.fc-daygrid-day-number{
    color:#0f172a !important;
    font-size:.78rem;
    font-weight:700;
    text-decoration:none !important;
}

.fc-day-today{
    background:#eef2ff !important;
}

.fc-daygrid-event{
    border:none !important;

    border-radius:8px !important;

    padding:3px 6px !important;

    font-size:.65rem !important;

    font-weight:700 !important;
}

/* RESPONSIVE */

@media(max-width:1100px){

    .planner-layout{
        grid-template-columns:1fr;
    }

}

@media(max-width:768px){

    .planner-header{
        flex-direction:column;
        align-items:flex-start;
    }

    .planner-actions{
        width:100%;
        justify-content:space-between;
    }

    .calendar-search{
        width:100%;
    }

    #calendar{
        min-height:500px;
    }

}

</style>

@stop


@section('js')

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>

<script>

function updateDate(){

    const now = new Date();

    document.getElementById('liveDate').innerHTML =
        now.toLocaleDateString('es-MX', {

            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'

        });

}

updateDate();

document.addEventListener('DOMContentLoaded', function(){

    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {

        initialView: 'dayGridMonth',

        locale: 'es',

        height: 'auto',

        headerToolbar: {

            left: 'prev,next today',

            center: 'title',

            right: 'dayGridMonth,timeGridWeek,timeGridDay'

        },

        buttonText: {

            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día'

        },

        events: [

            @foreach($citas as $cita)

            {

                title: '{{ $cita->paciente->nombre ?? "Paciente" }}',

                start: '{{ $cita->fecha_cita }}T{{ $cita->hora_cita }}',

                color:
                '{{ $cita->estado == "Pendiente"
                    ? "#f59e0b"
                    : ($cita->estado == "Completada"
                    ? "#10b981"
                    : "#ef4444") }}',

                extendedProps: {

                    estado: '{{ $cita->estado }}',
                    hora: '{{ $cita->hora_cita }}'

                }

            },

            @endforeach

        ]

    });

    calendar.render();

    const searchInput = document.getElementById('searchAppointments');

    searchInput.addEventListener('keyup', function(){

        const value = this.value.toLowerCase();

        const events = calendar.getEvents();

        events.forEach(event => {

            const title = event.title.toLowerCase();

            if(title.includes(value)){

                event.setProp('display', 'auto');

            }else{

                event.setProp('display', 'none');

            }

        });

    });

});

</script>

@stop