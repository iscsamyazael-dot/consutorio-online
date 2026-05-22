@extends('adminlte::page')

@section('title', 'Agenda de citas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Agenda de citas</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#crearCitaModal" data-bs-toggle="modal" data-bs-target="#crearCitaModal" data-agenda-modal="#crearCitaModal">
            <i class="fas fa-user-clock"></i> Programar cita
        </button>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <form action="{{ route('agenda.index') }}" method="GET" class="row">
                <div class="col-md-4 mb-2 mb-md-0">
                    <input type="date" name="fecha" class="form-control" value="{{ request('fecha') }}">
                </div>
                <div class="col-md-4 mb-2 mb-md-0">
                    <select name="estado" class="form-control">
                        <option value="">Todos los estados</option>
                        <option value="programada" @selected(request('estado') === 'programada')>Programada</option>
                        <option value="confirmada" @selected(request('estado') === 'confirmada')>Confirmada</option>
                        <option value="atendida" @selected(request('estado') === 'atendida')>Atendida</option>
                        <option value="cancelada" @selected(request('estado') === 'cancelada')>Cancelada</option>
                    </select>
                </div>
                <div class="col-md-4 text-md-right">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="{{ route('agenda.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-eraser"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Paciente</th>
                        <th>Medico</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th width="170">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($citas as $cita)
                        <tr>
                            <td>{{ $cita->fecha->format('d/m/Y') }}</td>
                            <td>
                                {{ substr($cita->hora_inicio, 0, 5) }}
                                @if ($cita->hora_fin)
                                    - {{ substr($cita->hora_fin, 0, 5) }}
                                @endif
                            </td>
                            <td>{{ $cita->paciente->nombre }} {{ $cita->paciente->apellido_paterno }}</td>
                            <td>{{ optional($cita->medico)->name ?? 'Sin asignar' }}</td>
                            <td>{{ $cita->motivo }}</td>
                            <td>
                                <span class="badge badge-{{ $cita->estado === 'cancelada' ? 'danger' : ($cita->estado === 'atendida' ? 'success' : 'info') }}">
                                    {{ ucfirst($cita->estado) }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-info btn-sm" title="Ver" data-toggle="modal" data-target="#verCitaModal{{ $cita->id }}" data-bs-toggle="modal" data-bs-target="#verCitaModal{{ $cita->id }}" data-agenda-modal="#verCitaModal{{ $cita->id }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-warning btn-sm" title="Editar" data-toggle="modal" data-target="#editarCitaModal{{ $cita->id }}" data-bs-toggle="modal" data-bs-target="#editarCitaModal{{ $cita->id }}" data-agenda-modal="#editarCitaModal{{ $cita->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('agenda.destroy', $cita) }}" method="POST" class="d-inline" onsubmit="return confirm('Desea eliminar esta cita?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay citas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($citas->hasPages())
            <div class="card-footer">
                {{ $citas->links() }}
            </div>
        @endif
    </div>

    <div class="modal fade" id="crearCitaModal" tabindex="-1" role="dialog" aria-labelledby="crearCitaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="crearCitaModalLabel">
                        <i class="fas fa-user-clock"></i> Programar cita
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" data-agenda-dismiss aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('agenda.store') }}" method="POST">
                    <div class="modal-body">
                        @include('agenda._form', ['cita' => $nuevaCita, 'formId' => 'crear_cita', 'cancelAsButton' => true])
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach ($citas as $cita)
        <div class="modal fade" id="verCitaModal{{ $cita->id }}" tabindex="-1" role="dialog" aria-labelledby="verCitaModalLabel{{ $cita->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="verCitaModalLabel{{ $cita->id }}">
                            <i class="fas fa-calendar-check"></i> Detalle de cita
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" data-agenda-dismiss aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">Paciente</dt>
                            <dd class="col-sm-9">{{ $cita->paciente->nombre }} {{ $cita->paciente->apellido_paterno }} {{ $cita->paciente->apellido_materno }}</dd>

                            <dt class="col-sm-3">Medico</dt>
                            <dd class="col-sm-9">{{ optional($cita->medico)->name ?? 'Sin asignar' }}</dd>

                            <dt class="col-sm-3">Fecha y hora</dt>
                            <dd class="col-sm-9">
                                {{ $cita->fecha->format('d/m/Y') }} {{ substr($cita->hora_inicio, 0, 5) }}
                                @if ($cita->hora_fin)
                                    - {{ substr($cita->hora_fin, 0, 5) }}
                                @endif
                            </dd>

                            <dt class="col-sm-3">Estado</dt>
                            <dd class="col-sm-9">{{ ucfirst($cita->estado) }}</dd>

                            <dt class="col-sm-3">Motivo</dt>
                            <dd class="col-sm-9">{{ $cita->motivo }}</dd>

                            <dt class="col-sm-3">Notas</dt>
                            <dd class="col-sm-9">{{ $cita->notas ?: 'Sin notas' }}</dd>
                        </dl>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal" data-agenda-dismiss>
                            <i class="fas fa-times"></i> Cerrar
                        </button>
                        <button type="button" class="btn btn-warning" data-dismiss="modal" data-bs-dismiss="modal" data-toggle="modal" data-target="#editarCitaModal{{ $cita->id }}" data-bs-toggle="modal" data-bs-target="#editarCitaModal{{ $cita->id }}" data-agenda-modal="#editarCitaModal{{ $cita->id }}">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editarCitaModal{{ $cita->id }}" tabindex="-1" role="dialog" aria-labelledby="editarCitaModalLabel{{ $cita->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title" id="editarCitaModalLabel{{ $cita->id }}">
                            <i class="fas fa-edit"></i> Editar cita
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" data-agenda-dismiss aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('agenda.update', $cita) }}" method="POST">
                        @method('PUT')
                        <div class="modal-body">
                            @include('agenda._form', ['cita' => $cita, 'formId' => 'editar_cita_'.$cita->id, 'cancelAsButton' => true])
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-agenda-modal]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const selector = button.getAttribute('data-agenda-modal');
                    const modal = document.querySelector(selector);

                    if (!modal) {
                        return;
                    }

                    if (window.bootstrap && window.bootstrap.Modal) {
                        window.bootstrap.Modal.getOrCreateInstance(modal).show();
                        return;
                    }

                    if (window.jQuery && window.jQuery.fn.modal) {
                        window.jQuery(modal).modal('show');
                        return;
                    }

                    modal.classList.add('show');
                    modal.style.display = 'block';
                    modal.removeAttribute('aria-hidden');
                    modal.setAttribute('aria-modal', 'true');
                    document.body.classList.add('modal-open');

                    if (!document.querySelector('.modal-backdrop')) {
                        const backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        document.body.appendChild(backdrop);
                    }
                });
            });

            document.querySelectorAll('[data-agenda-dismiss]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const modal = button.closest('.modal');

                    if (!modal) {
                        return;
                    }

                    if (window.bootstrap && window.bootstrap.Modal) {
                        window.bootstrap.Modal.getOrCreateInstance(modal).hide();
                        return;
                    }

                    if (window.jQuery && window.jQuery.fn.modal) {
                        window.jQuery(modal).modal('hide');
                        return;
                    }

                    modal.classList.remove('show');
                    modal.style.display = 'none';
                    modal.setAttribute('aria-hidden', 'true');
                    modal.removeAttribute('aria-modal');
                    document.body.classList.remove('modal-open');
                    document.querySelectorAll('.modal-backdrop').forEach((backdrop) => backdrop.remove());
                });
            });
        });
    </script>
@stop
