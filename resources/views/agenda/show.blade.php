@extends('adminlte::page')

@section('title', 'Detalle de cita')

@section('content_header')
    <h1>Detalle de cita</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header bg-info text-white">
            <i class="fas fa-calendar-check"></i> Cita #{{ $cita->id }}
        </div>
        <div class="card-body">
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
        <div class="card-footer text-right">
            <a href="{{ route('agenda.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="{{ route('agenda.edit', $cita) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
        </div>
    </div>
@stop
