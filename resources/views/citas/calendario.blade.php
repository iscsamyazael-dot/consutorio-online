@extends('adminlte::page')

@section('title', 'Agenda - Calendario')

@section('content_header')
    <h1>Agenda Médica</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Calendario -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Calendario de Citas</h3>
                    </div>
                    <div class="card-body">
                        <div id="calendar" class="bg-white rounded-lg shadow"></div>
                    </div>
                </div>
            </div>

            <!-- Panel Lateral -->
            <div class="col-lg-4">
                <!-- Citas del Día -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-list"></i> Citas de Hoy</h3>
                    </div>
                    <div class="card-body p-0">
                        <div id="citasHoy" class="list-group list-group-flush">
                            <div class="list-group-item text-muted text-center py-4">
                                Cargando citas...
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario Rápido -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-plus-circle"></i> Nueva Cita</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="pacienteSearch" class="form-label">Paciente</label>
                            <input type="text" id="pacienteSearch" class="form-control" placeholder="Buscar paciente...">
                            <div id="pacientesResultados" class="list-group mt-2" style="display: none;"></div>
                        </div>

                        <div class="mb-3">
                            <label for="medicoSelect" class="form-label">Médico</label>
                            <select id="medicoSelect" class="form-select">
                                <option value="">Seleccionar médico</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="fechaHoraInput" class="form-label">Fecha y Hora</label>
                            <input type="datetime-local" id="fechaHoraInput" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="duracionInput" class="form-label">Duración (minutos)</label>
                            <input type="number" id="duracionInput" class="form-control" value="30" min="15" max="480">
                        </div>

                        <div class="mb-3">
                            <label for="motivoInput" class="form-label">Motivo</label>
                            <input type="text" id="motivoInput" class="form-control" placeholder="Consulta general...">
                        </div>

                        <button id="crearCitaBtn" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Programar Cita
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para detalles de cita -->
    <div class="modal fade" id="citaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles de Cita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="citaDetails"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" id="editarCitaBtn" class="btn btn-warning">Editar</button>
                    <button type="button" id="cancelarCitaBtn" class="btn btn-danger">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/index.global.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.10/index.global.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.10/index.global.min.css" rel="stylesheet" />
    <style>
        .fc {
            font-family: inherit;
        }
        .fc .fc-button-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .fc .fc-button-primary:hover {
            background-color: #0056b3;
        }
        .fc-event-past {
            opacity: 0.7;
        }
        #calendar {
            min-height: 600px;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.10/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.10/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@6.1.10/index.global.min.js"></script>

    <script>
        const calendarEl = document.getElementById('calendar');
        let currentCita = null;
        let pacienteSeleccionado = null;

        // Inicializar FullCalendar
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            editable: true,
            selectable: true,
            selectConstraint: 'businessHours',
            eventClick: handleEventClick,
            select: handleDateSelect,
            events: function(info, successCallback, failureCallback) {
                fetch(`/api/citas/rango?start=${info.start.toISOString()}&end=${info.end.toISOString()}`)
                    .then(res => res.json())
                    .then(data => successCallback(data))
                    .catch(err => failureCallback(err));
            },
            locale: 'es',
            nowIndicator: true,
            slotLabelFormat: {
                hour: 'numeric',
                meridiem: 'short',
                meridiem: 'short'
            }
        });

        calendar.render();

        // Cargar citas del día
        function cargarCitasHoy() {
            const hoy = new Date().toISOString().split('T')[0];
            fetch(`/api/citas/rango?start=${hoy}&end=${hoy}`)
                .then(res => res.json())
                .then(data => {
                    const container = document.getElementById('citasHoy');
                    if (data.length === 0) {
                        container.innerHTML = '<div class="list-group-item text-muted text-center py-4">Sin citas hoy</div>';
                        return;
                    }
                    container.innerHTML = data.map(cita => `
                        <div class="list-group-item list-group-item-action cursor-pointer" onclick="mostrarDetalleCita(${cita.id})">
                            <div class="d-flex justify-content-between">
                                <strong>${cita.extendedProps.paciente}</strong>
                                <small class="text-muted">${new Date(cita.start).toLocaleTimeString()}</small>
                            </div>
                            <small class="text-secondary">${cita.extendedProps.motivo}</small>
                        </div>
                    `).join('');
                });
        }

        // Cargar médicos
        function cargarMedicos() {
            fetch('/api/medicos')
                .then(res => res.json())
                .then(data => {
                    const select = document.getElementById('medicoSelect');
                    select.innerHTML = '<option value="">Seleccionar médico</option>' +
                        data.map(m => `<option value="${m.id}">${m.name}</option>`).join('');
                });
        }

        // Búsqueda de pacientes
        document.getElementById('pacienteSearch').addEventListener('input', function(e) {
            if (e.target.value.length < 2) {
                document.getElementById('pacientesResultados').style.display = 'none';
                return;
            }

            fetch(`/api/pacientes/buscar?q=${e.target.value}`)
                .then(res => res.json())
                .then(data => {
                    const container = document.getElementById('pacientesResultados');
                    container.innerHTML = data.map(p => `
                        <button type="button" class="list-group-item list-group-item-action text-start" 
                                onclick="seleccionarPaciente(${p.id}, '${p.nombre} ${p.apellido_paterno}')">
                            ${p.nombre} ${p.apellido_paterno} - ${p.numero_cedula}
                        </button>
                    `).join('');
                    container.style.display = 'block';
                });
        });

        function seleccionarPaciente(id, nombre) {
            pacienteSeleccionado = id;
            document.getElementById('pacienteSearch').value = nombre;
            document.getElementById('pacientesResultados').style.display = 'none';
        }

        // Crear cita
        document.getElementById('crearCitaBtn').addEventListener('click', function() {
            const medicoId = document.getElementById('medicoSelect').value;
            const fechaHora = document.getElementById('fechaHoraInput').value;
            const duracion = document.getElementById('duracionInput').value;
            const motivo = document.getElementById('motivoInput').value;

            if (!pacienteSeleccionado || !medicoId || !fechaHora || !motivo) {
                alert('Complete todos los campos requeridos');
                return;
            }

            fetch('/api/citas', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('[name="_token"]')?.value || ''
                },
                body: JSON.stringify({
                    paciente_id: pacienteSeleccionado,
                    user_id: medicoId,
                    fecha_hora: new Date(fechaHora).toISOString(),
                    duracion_minutos: parseInt(duracion),
                    motivo: motivo,
                    tipo_cita: 'Presencial'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Cita programada exitosamente');
                    calendar.refetchEvents();
                    cargarCitasHoy();
                    // Limpiar formulario
                    document.getElementById('pacienteSearch').value = '';
                    document.getElementById('medicoSelect').value = '';
                    document.getElementById('fechaHoraInput').value = '';
                    document.getElementById('motivoInput').value = '';
                    pacienteSeleccionado = null;
                } else {
                    alert('Error: ' + data.mensaje);
                }
            })
            .catch(err => alert('Error al crear cita: ' + err.message));
        });

        function handleEventClick(info) {
            currentCita = info.event;
            mostrarDetalleCita(info.event.id);
        }

        function handleDateSelect(info) {
            document.getElementById('fechaHoraInput').value = info.start.toISOString().slice(0, 16);
        }

        function mostrarDetalleCita(citaId) {
            fetch(`/citas/${citaId}`)
                .then(res => res.json())
                .then(cita => {
                    const modal = new bootstrap.Modal(document.getElementById('citaModal'));
                    document.getElementById('citaDetails').innerHTML = `
                        <p><strong>Paciente:</strong> ${cita.paciente?.nombre || 'N/A'}</p>
                        <p><strong>Médico:</strong> ${cita.medico?.name || 'Sin asignar'}</p>
                        <p><strong>Fecha:</strong> ${new Date(cita.fecha_hora).toLocaleString()}</p>
                        <p><strong>Motivo:</strong> ${cita.motivo}</p>
                        <p><strong>Estado:</strong> <span class="badge bg-info">${cita.estado}</span></p>
                    `;
                    modal.show();
                });
        }

        // Cargar datos iniciales
        cargarMedicos();
        cargarCitasHoy();
    </script>

    <csrf token="{{ csrf_token() }}">
@stop
