@extends('adminlte::page') <!-- O tu plantilla base -->

@section('title', 'Registrar Doctor')

@section('content')
<div class="container-fluid py-4">
    
    <!-- ENCABEZADO PREMIUM -->
    <div class="row mb-5">
        <div class="col-12 text-center text-md-left d-md-flex align-items-center justify-content-between header-custom-container p-4 rounded shadow-sm bg-white">
            <div>
                <h1 class="font-weight-black text-dark mb-1 tracking-tight">
                    <span class="badge badge-primary-gradient p-2 mr-2 rounded-lg">
                        <i class="fas fa-user-md text-white animate-pulse"></i>
                    </span> 
                    Registrar Nuevo Médico
                </h1>
                <p class="text-muted mb-0 ml-md-5 pl-md-2 font-weight-light">Alta y asignación de horarios para el personal de salud.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="/MedicosAlta" class="btn btn-white btn-light border-2 text-secondary rounded-pill px-4 py-2 shadow-sm d-inline-flex align-items-center font-weight-bold btn-back-hover">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver a la tabla
                </a>
            </div>
        </div>
    </div>

    <!-- FORMULARIO PRINCIPAL -->
    <form action="{{ route('medicos.store') }}" method="POST">
    @csrf

        <div class="row">
            
            <!-- COLUMNA IZQUIERDA: DATOS GENERALES -->
            <div class="col-lg-8">
                
                <!-- SECCIÓN: INFORMACIÓN PERSONAL -->
                <div class="card card-custom shadow-lg mb-4 border-0 overflow-hidden">
                    <div class="card-decor-line bg-primary-gradient"></div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon-shape bg-primary-light text-primary rounded-circle mr-3">
                                <i class="fas fa-id-card fa-lg"></i>
                            </div>
                            <h4 class="mb-0 font-weight-bold text-dark-blue">Información Personal</h4>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="nombre" class="form-label-custom">Nombre Completo <span class="text-danger">*</span></label>
                                <div class="input-group-custom">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" name="nombre" id="nombre" class="form-control-custom" placeholder="Ej. Dr. Alejandro Ríos" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="email" class="form-label-custom">Correo Electrónico <span class="text-danger">*</span></label>
                                <div class="input-group-custom">
                                    <i class="fas fa-envelope input-icon"></i>
                                    <input type="email" name="email" id="email" class="form-control-custom" placeholder="doctor@hospital.com" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN: INFORMACIÓN PROFESIONAL -->
                <div class="card card-custom shadow-lg mb-4 border-0 overflow-hidden">
                    <div class="card-decor-line bg-success-gradient"></div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon-shape bg-success-light text-success rounded-circle mr-3">
                                <i class="fas fa-graduation-cap fa-lg"></i>
                            </div>
                            <h4 class="mb-0 font-weight-bold text-dark-blue">Información Profesional</h4>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="cedula" class="form-label-custom">Cédula Profesional <span class="text-danger">*</span></label>
                                <div class="input-group-custom">
                                    <i class="fas fa-file-medical input-icon"></i>
                                    <input type="text" name="cedula_profesional" id="cedula" class="form-control-custom" placeholder="Ingrese número de cédula" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="especialidad" class="form-label-custom">Especialidad Médica <span class="text-danger">*</span></label>
                                <div class="input-group-custom">
                                    <i class="fas fa-stethoscope input-icon"></i>
                                    <select name="especialidad" id="especialidad" class="form-control-custom select-custom" required>
                                        <option value="" disabled selected>Seleccione una especialidad...</option>
                                        <option value="1">Medicina General</option>
                                        <option value="2">Pediatría</option>
                                        <option value="3">Cardiología</option>
                                        <option value="4">Ginecología</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: HORARIOS (Diseño Dashboard) -->
            <div class="col-lg-4">
                <div class="card card-custom shadow-lg mb-4 border-0 overflow-hidden h-100">
                    <div class="card-decor-line bg-warning-gradient"></div>
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center mb-4">
                                <div class="icon-shape bg-warning-light text-warning rounded-circle mr-3">
                                    <i class="fas fa-calendar-alt fa-lg"></i>
                                </div>
                                <h4 class="mb-0 font-weight-bold text-dark-blue">Disponibilidad</h4>
                            </div>

                            <!-- Horas -->
                            <div class="mb-4">
                                <label for="hora_entrada" class="form-label-custom">Hora de Entrada</label>
                                <div class="input-group-custom">
                                    <i class="fas fa-clock input-icon text-warning"></i>
                                    <input type="time" name="hora_entrada" id="hora_entrada" class="form-control-custom" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="hora_salida" class="form-label-custom">Hora de Salida</label>
                                <div class="input-group-custom">
                                    <i class="fas fa-history input-icon text-warning"></i>
                                    <input type="time" name="hora_salida" id="hora_salida" class="form-control-custom" required>
                                </div>
                            </div>

                            <hr class="my-4 border-light">

                            <!-- Días Semanales estilo Grid Selector -->
                            <label class="form-label-custom mb-3">Días Laborales</label>
                            <div class="days-grid-selector">
                                @foreach(['Lunes' => 'L', 'Martes' => 'MA', 'Miércoles' => 'MI', 'Jueves' => 'J', 'Viernes' => 'V', 'Sábado' => 'S', 'Domingo' => 'D'] as $diaCompleto => $inicial)
                                    <label class="day-btn-checkbox">
                                        <input type="checkbox" name="dias_laborales[]" value="{{ $diaCompleto }}">
                                        <span class="day-box" title="{{ $diaCompleto }}">{{ $inicial }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <small class="text-muted d-block mt-2 text-center">Presione los días para activarlos</small>
                        </div>

                        <!-- BOTONES INTEGRADOS EN LA TARJETA LATERAL -->
                        <div class="mt-5 pt-4 border-top border-light">
                            <button type="submit" class="btn btn-primary-gradient btn-block btn-lg rounded-pill shadow mb-2">
                                <i class="fas fa-save mr-2"></i> Guardar Registro
                            </button>
                            
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
//toast de confirmacion
<div class="position-fixed p-3" style="z-index: 9999; right: 20px; top: 20px; min-width: 300px;">
    
    @if(session('success'))
        <div id="toast-success" class="toast show border-0 shadow-lg text-white" role="alert" aria-live="assertive" aria-atomic="true" data-delay="4000" style="background: linear-gradient(135deg, #00bfa5 0%, #00897b 100%); border-radius: 12px; cubic-bezier(0.16, 1, 0.3, 1); animation: slideInMedico 0.4s ease-out;">
            <div class="d-flex align-items-center px-3 py-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" style="background-color: rgba(255, 255, 255, 0.2); width: 30px; height: 30px;">
                    <i class="fas fa-check-circle text-white"></i>
                </div>
                <div class="toast-body p-0 font-weight-bold flex-grow-1">
                    {{ session('success') }}
                </div>
                <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close" style="outline: none; opacity: 0.8;">
                    <span aria-hidden="true" class="text-white">&times;</span>
                </button>
            </div>
        </div>
    @endif

</div>
@endsection

<script>

$(document).ready(function() {
        // Si el toast existe en pantalla, programar su desaparición en 4 segundos (4000ms)
        if ($('#toast-success').length > 0) {
            setTimeout(function() {
                $('#toast-success').toast('hide');
            }, 4000);
        }
    });
</script>






@section('css')
<style>

    @keyframes slideInMedico {
        from {
          transform: translateX(120%);
          opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    /* VARIABLES DE COLOR MODERNAS */
    :root {
        --primary-gradient: linear-gradient(135deg, #0061f2 0%, #00ba94 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --bg-light-blue: #f8faff;
        --dark-blue: #1e293b;
    }

    body {
        background-color: var(--bg-light-blue) !important;
    }

    /* Fuentes y Estilo General */
    .font-weight-black { font-weight: 900; }
    .text-dark-blue { color: var(--dark-blue); }
    
    /* Contenedor del header animado */
    .header-custom-container {
        border-left: 5px solid #0061f2;
        background: #ffffff;
    }

    .badge-primary-gradient {
        background: var(--primary-gradient);
    }

    /* TARJETAS PREMIUM (Cards) */
    .card-custom {
        border-radius: 16px !important;
        background: #ffffff;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 1rem 3rem rgba(31, 45, 65, 0.08) !important;
    }
    .card-decor-line {
        height: 5px;
        width: 100%;
    }
    .bg-primary-gradient { background: var(--primary-gradient); }
    .bg-success-gradient { background: var(--success-gradient); }
    .bg-warning-gradient { background: var(--warning-gradient); }

    /* ICONOS DE SECCIÓN */
    .icon-shape {
        width: 48px;
        height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .bg-primary-light { background-color: rgba(0, 97, 242, 0.1); }
    .bg-success-light { background-color: rgba(16, 185, 129, 0.1); }
    .bg-warning-light { background-color: rgba(245, 158, 11, 0.1); }

    /* INPUTS TOTALMENTE RESTRUCTURADOS UI/UX */
    .form-label-custom {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 8px;
    }

    .input-group-custom {
        position: relative;
        display: flex;
        align-items: center;
    }

    .form-control-custom {
        width: 100%;
        padding: 14px 16px 14px 45px;
        font-size: 0.95rem;
        background-color: #f1f5f9;
        border: 2px solid transparent;
        border-radius: 12px;
        color: #334155;
        transition: all 0.25s ease;
    }

    .form-control-custom:focus {
        background-color: #ffffff;
        border-color: #0061f2;
        box-shadow: 0 0 0 4px rgba(0, 97, 242, 0.15);
        outline: none;
    }

    .input-icon {
        position: absolute;
        left: 16px;
        color: #94a3b8;
        font-size: 1.1rem;
        transition: color 0.25s ease;
    }

    .form-control-custom:focus + .input-icon {
        color: #0061f2;
    }

    .select-custom {
        appearance: none;
        cursor: pointer;
    }

    /* BOTONES INTERACTIVOS PARA DÍAS DE LA SEMANA (Estilo App Móvil) */
    .days-grid-selector {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 8px;
    }

    .day-btn-checkbox input[type="checkbox"] {
        display: none;
    }

    .day-btn-checkbox .day-box {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 42px;
        border-radius: 10px;
        background-color: #f1f5f9;
        color: #475569;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
        user-select: none;
    }

    .day-btn-checkbox input[type="checkbox"]:checked + .day-box {
        background: var(--primary-gradient);
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(0, 97, 242, 0.3);
        transform: scale(1.05);
    }

    .day-btn-checkbox:hover .day-box {
        background-color: #e2e8f0;
    }

    /* BOTONES PREMIUM GRADIENTES */
    .btn-primary-gradient {
        background: var(--primary-gradient);
        color: white;
        border: none;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }
    .btn-primary-gradient:hover {
        opacity: 0.95;
        box-shadow: 0 6px 20px rgba(0, 97, 242, 0.4) !important;
        transform: translateY(-1px);
        color: white;
    }

    /* Animaciones */
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.08); }
    }
    .animate-pulse {
        animation: pulse 2s infinite ease-in-out;
    }
</style>
@endsection