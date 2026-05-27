@extends('adminlte::page')

@section('title', 'Agenda - Citas')

@section('content_header')
    <h1>Agenda / Citas</h1>
@stop

@section('content')
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-3">
                    <div class="col text-end">
                        <a href="{{ route('citas.create') }}" class="btn btn-primary"><i class="fas fa-user-clock"></i> Programar cita</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-calendar-check"></i> Lista de citas</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Paciente</th>
                                    <th>Fecha y hora</th>
                                    <th>Motivo</th>
                                    <th>Estado</th>
                                    <th>Doctor</th>
                                    <th width="180">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($citas as $cita)
                                    <tr>
                                        <td>{{ $cita->id }}</td>
                                        <td>{{ $cita->paciente->nombre ?? 'Sin paciente' }}</td>
                                        <td>{{ optional($cita->fecha_hora)->format('d/m/Y H:i') }}</td>
                                        <td>{{ $cita->motivo }}</td>
                                        <td>{{ ucfirst($cita->estado) }}</td>
                                        <td>{{ $cita->tipo_cita }}</td>
                                        <td>
                                            <a href="{{ route('citas.edit', $cita) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                            <form action="{{ route('citas.destroy', $cita) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Eliminar esta cita?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No hay citas registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
