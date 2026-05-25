@extends('adminlte::page')

@section('title', isset($cita) ? 'Editar cita' : 'Programar cita')

@section('content_header')
    <h1>{{ isset($cita) ? 'Editar cita' : 'Programar cita' }}</h1>
@stop

@section('content')
    <div class="content">
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ isset($cita) ? 'Editar cita' : 'Nueva cita' }}</h3>
                    </div>
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ isset($cita) ? route('citas.update', $cita) : route('citas.store') }}" method="POST">
                            @csrf
                            @if(isset($cita))
                                @method('PUT')
                            @endif

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="paciente_id">Paciente</label>
                                    <select name="paciente_id" id="paciente_id" class="form-control" required>
                                        <option value="">Seleccionar paciente</option>
                                        @foreach($pacientes as $paciente)
                                            <option value="{{ $paciente->id }}" {{ old('paciente_id', $cita->paciente_id ?? '') == $paciente->id ? 'selected' : '' }}>
                                                {{ $paciente->nombre }} {{ $paciente->apellido_paterno }} {{ $paciente->apellido_materno }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="fecha_hora">Fecha y hora</label>
                                    <input type="datetime-local" name="fecha_hora" id="fecha_hora" class="form-control" value="{{ old('fecha_hora', isset($cita) ? $cita->fecha_hora->format('Y-m-d\TH:i') : '') }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="tipo_cita">Tipo de cita</label>
                                    <input type="text" name="tipo_cita" id="tipo_cita" class="form-control" value="{{ old('tipo_cita', $cita->tipo_cita ?? '') }}" placeholder="Presencial / Virtual">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="estado">Estado</label>
                                    <select name="estado" id="estado" class="form-control" required>
                                        @php
                                            $estados = ['pendiente', 'confirmada', 'cancelada', 'atendida'];
                                        @endphp
                                        @foreach($estados as $estado)
                                            <option value="{{ $estado }}" {{ old('estado', $cita->estado ?? 'pendiente') === $estado ? 'selected' : '' }}>{{ ucfirst($estado) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="motivo">Motivo</label>
                                <input type="text" name="motivo" id="motivo" class="form-control" value="{{ old('motivo', $cita->motivo ?? '') }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="duracion">Duración</label>
                                <input type="text" name="duracion" id="duracion" class="form-control" value="{{ old('duracion', $cita->duracion ?? '') }}" placeholder="Ej. 30 minutos">
                            </div>

                            <div class="mb-3">
                                <label for="ubicacion">Ubicación</label>
                                <input type="text" name="ubicacion" id="ubicacion" class="form-control" value="{{ old('ubicacion', $cita->ubicacion ?? '') }}" placeholder="Consultorio, Teleconsulta...">
                            </div>

                            <div class="mb-3">
                                <label for="notas">Notas</label>
                                <textarea name="notas" id="notas" class="form-control" rows="4">{{ old('notas', $cita->notas ?? '') }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-success">{{ isset($cita) ? 'Guardar cambios' : 'Programar cita' }}</button>
                            <a href="{{ route('citas.index') }}" class="btn btn-secondary">Cancelar</a>
                        </form>
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