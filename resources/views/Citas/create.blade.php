@extends('adminlte::page')

@section('title', 'Programar Cita')

@section('content_header')
@stop

@section('content')

<div class="modern-wrapper">

    <!-- HEADER SIMPLE -->
    <div class="page-header">

        <div>
            <h1>
                Nueva Consulta Médica
            </h1>

            <p>
                Registra y organiza una nueva consulta para el paciente.
            </p>
        </div>

        <div class="header-badge">

            <i class="fas fa-stethoscope"></i>
            Consultorio Médico

        </div>

    </div>

    <!-- STATS -->
    <div class="top-stats">

        <div class="stat-card blue">

            <div class="stat-icon">
                <i class="fas fa-calendar-day"></i>
            </div>

            <div>
                <h2>12</h2>
                <p>Citas registradas hoy</p>
            </div>

        </div>

        <div class="stat-card green">

            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>

            <div>
                <h2>8</h2>
                <p>Confirmadas</p>
            </div>

        </div>

        <div class="stat-card orange">

            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>

            <div>
                <h2>4</h2>
                <p>Canceladas</p>
            </div>

        </div>

    </div>

    <!-- FORMULARIO -->
    <div class="form-card">

        <!-- TOP -->
        <div class="form-top">

            <div>

                <h2>
                    Registrar Consulta
                </h2>

                <p>
                    Completa la información médica del paciente.
                </p>

            </div>

            <div class="top-badge">

                <i class="fas fa-calendar-check"></i>
                Agenda activa

            </div>

        </div>

        <form action="{{ route('citas.store') }}" method="POST">

            @csrf

            <div class="form-grid">

                <!-- PACIENTE -->
                <div class="form-group full">

                    <label>Paciente</label>

                    <div class="input-modern">

                        <i class="fas fa-user"></i>

                        <select name="paciente_id" required>

                            <option value="">
                                Seleccionar paciente
                            </option>

                            @foreach($pacientes as $paciente)

                                <option value="{{ $paciente->id }}">
                                    {{ $paciente->nombre }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>

                <!-- FECHA -->
                <div class="form-group">

                    <label>Fecha</label>

                    <div class="input-modern">

                        <i class="fas fa-calendar"></i>

                        <input
                            type="date"
                            name="fecha_cita"
                            required
                        >

                    </div>

                </div>

                <!-- HORA -->
                <div class="form-group">

                    <label>Hora</label>

                    <div class="input-modern">

                        <i class="fas fa-clock"></i>

                        <input
                            type="time"
                            name="hora_cita"
                            required
                        >

                    </div>

                </div>

                <!-- ESTADO -->
                <div class="form-group">

                    <label>Estado</label>

                    <div class="input-modern">

                        <i class="fas fa-check-circle"></i>

                        <select name="estado">

                            <option value="Pendiente">
                                Pendiente
                            </option>

                            <option value="Completada">
                                Completada
                            </option>

                            <option value="Cancelada">
                                Cancelada
                            </option>

                        </select>

                    </div>

                </div>

                <!-- OBSERVACIONES -->
                <div class="form-group full">

                    <label>Observaciones</label>

                    <textarea
                        name="observaciones"
                        placeholder="Agregar observaciones médicas..."
                    ></textarea>

                </div>

            </div>

            <!-- ACTIONS -->
            <div class="actions">

                <a href="{{ route('citas.index') }}" class="btn-cancel">
                    Cancelar
                </a>

                <button type="submit" class="btn-save">

                    <i class="fas fa-save"></i>
                    Guardar Consulta

                </button>

            </div>

        </form>

    </div>

</div>

@stop


@section('css')

<style>

/* FONDO */

body{
    background:
    linear-gradient(
        180deg,
        #f8fafc,
        #eef2ff
    );

    font-family:'Inter',sans-serif;
}

.content-wrapper{
    background:transparent;
}

/* WRAPPER */

.modern-wrapper{
    padding:5px 5px 25px;
}

/* HEADER */

.page-header{
    background:
    linear-gradient(
        135deg,
        #0f172a,
        #1e293b
    );

    border-radius:24px;

    padding:22px 28px;

    margin-bottom:18px;

    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    flex-wrap:wrap;

    box-shadow:
    0 10px 25px rgba(15,23,42,.08);
}

.page-header h1{
    color:white;
    margin:0;

    font-size:2rem;
    font-weight:800;
}

.page-header p{
    color:rgba(255,255,255,.7);
    margin:6px 0 0;
}

.header-badge{
    background:
    rgba(255,255,255,.08);

    border:1px solid rgba(255,255,255,.08);

    color:white;

    padding:12px 18px;

    border-radius:14px;

    font-weight:700;

    display:flex;
    align-items:center;
    gap:10px;
}

/* TOP STATS */

.top-stats{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:16px;

    margin-bottom:20px;
}

.stat-card{
    border-radius:22px;

    padding:18px 22px;

    display:flex;
    align-items:center;
    gap:15px;

    color:white;

    transition:.25s ease;

    box-shadow:
    0 10px 25px rgba(0,0,0,.08);
}

.stat-card:hover{
    transform:translateY(-3px);
}

.stat-card.blue{
    background:
    linear-gradient(135deg,#2563eb,#3b82f6);
}

.stat-card.green{
    background:
    linear-gradient(135deg,#059669,#10b981);
}

.stat-card.orange{
    background:
    linear-gradient(135deg,#ea580c,#f97316);
}

.stat-icon{
    width:54px;
    height:54px;

    border-radius:16px;

    background:
    rgba(255,255,255,.15);

    display:flex;
    align-items:center;
    justify-content:center;

    font-size:1.25rem;
}

.stat-card h2{
    margin:0;

    font-size:2rem;
    font-weight:800;
}

.stat-card p{
    margin:0;
    opacity:.92;
}

/* FORM CARD */

.form-card{
    background:
    rgba(255,255,255,.78);

    backdrop-filter:blur(18px);

    border-radius:28px;

    padding:30px;

    border:1px solid rgba(255,255,255,.6);

    box-shadow:
    0 12px 30px rgba(15,23,42,.05);
}

/* TOP */

.form-top{
    display:flex;
    justify-content:space-between;
    align-items:center;

    margin-bottom:28px;

    gap:15px;

    flex-wrap:wrap;
}

.form-top h2{
    margin:0;

    font-size:2rem;
    font-weight:800;

    color:#0f172a;
}

.form-top p{
    margin:6px 0 0;
    color:#64748b;
}

.top-badge{
    background:
    linear-gradient(
        135deg,
        #dbeafe,
        #eef2ff
    );

    color:#3730a3;

    padding:12px 16px;

    border-radius:14px;

    font-weight:700;

    display:flex;
    align-items:center;
    gap:8px;
}

/* GRID */

.form-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:22px;
}

.full{
    grid-column:span 2;
}

/* GROUP */

.form-group{
    display:flex;
    flex-direction:column;
}

.form-group label{
    margin-bottom:10px;

    font-weight:700;
    color:#334155;
}

/* INPUT */

.input-modern{
    position:relative;
}

.input-modern i{
    position:absolute;

    left:16px;
    top:50%;

    transform:translateY(-50%);

    color:#94a3b8;
}

.input-modern input,
.input-modern select{
    width:100%;
    height:56px;

    border-radius:18px;

    border:1px solid #dbe4ee;

    background:white;

    padding:0 18px 0 48px;

    outline:none;

    transition:.25s ease;
}

.input-modern input:focus,
.input-modern select:focus{
    border-color:#3b82f6;

    box-shadow:
    0 0 0 5px rgba(59,130,246,.10);
}

/* TEXTAREA */

textarea{
    width:100%;
    min-height:130px;

    border-radius:20px;

    border:1px solid #dbe4ee;

    background:white;

    padding:18px;

    resize:vertical;

    outline:none;

    transition:.25s ease;
}

textarea:focus{
    border-color:#3b82f6;

    box-shadow:
    0 0 0 5px rgba(59,130,246,.10);
}

/* ACTIONS */

.actions{
    display:flex;
    justify-content:flex-end;
    gap:14px;

    margin-top:30px;
}

/* BUTTONS */

.btn-cancel{
    background:white;

    border:1px solid #dbe4ee;

    padding:13px 22px;

    border-radius:16px;

    font-weight:700;

    color:#334155;

    text-decoration:none;

    transition:.25s ease;
}

.btn-cancel:hover{
    background:#f8fafc;

    text-decoration:none;

    color:#0f172a;
}

.btn-save{
    border:none;

    background:
    linear-gradient(
        135deg,
        #2563eb,
        #3b82f6
    );

    color:white;

    padding:13px 24px;

    border-radius:16px;

    font-weight:700;

    transition:.25s ease;

    box-shadow:
    0 10px 25px rgba(37,99,235,.25);
}

.btn-save:hover{
    transform:translateY(-2px);

    box-shadow:
    0 14px 28px rgba(37,99,235,.35);
}

/* RESPONSIVE */

@media(max-width:768px){

    .top-stats{
        grid-template-columns:1fr;
    }

    .form-grid{
        grid-template-columns:1fr;
    }

    .full{
        grid-column:span 1;
    }

    .form-card{
        padding:22px;
    }

    .actions{
        flex-direction:column;
    }

    .btn-save,
    .btn-cancel{
        width:100%;
        text-align:center;
    }

    .page-header h1{
        font-size:1.5rem;
    }

}

/* FIX SIDEBAR ADMINLTE */

.main-sidebar{
    position: fixed !important;
    height: 100vh !important;
    overflow-y: auto;
}

.content-wrapper,
.main-footer,
.main-header{
    margin-left: 250px !important;
}

/* EVITA QUE BAJE EL MENÚ */

.wrapper{
    overflow-x: hidden;
}

/* SCROLL SOLO EN CONTENIDO */

.content-wrapper{
    min-height: 100vh;
}
</style>

@stop