@extends('adminlte::page')

@section('title', 'Registrar Paciente')

@section('content_header')
    <h1>Registro de nuevos Pacientes</h1>
@stop

@section('content') 
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                Datos del Paciente
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Nombre(s)</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Apellidos</label>
                        <input type="text" name="apellidos" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Fecha de nacimiento</label>
                        <input type="date" name="fecha_nacimiento" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Sexo</label>
                            <select name="sexo" class="form-select">
                                <option value="">Seleccione</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="form-control">
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Correo electrónico</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Dirección</label>
                        <input type="text" name="direccion" class="form-control">
                    </div>
                </div>
                <hr>
                <h1 class="mb-4">Expediente Médico Inicial</h1>
                <div class="mb-3">
                    <label>Observaciones iniciales</label>
                    <textarea name="observaciones" class="form-control" rows="3"></textarea>
                </div>
                <div class="text-end">
                    <a href="#" class="btn btn-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-success">
                        Guardar Paciente
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script> console.log('Hi!'); </script>
@stop
