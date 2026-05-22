@extends('adminlte::page')

@section('title', 'Registrar Paciente')

@section('content_header')
    {{-- Encabezado estilizado fuera de la tarjeta principal --}}
    <div class="d-flex justify-content-between align-items-center py-2">
        <h1 class="text-dark font-weight-bold" style="font-size: 1.8rem;">
            <i class="fas fa-user-plus text-primary mr-2"></i>Alta de Nuevo Paciente
        </h1>
        <a href="{{ route('pacientes.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
        </a>
    </div>
@stop

@section('content') 
    {{-- Contenedor con padding extra abajo --}}
    <div id="app">
        <medicamentos-inventario></medicamentos-inventario>
    </div>
    <div class="container-fluid pb-5">
        <div class="card shadow border-0 rounded-lg">
            
            <form action="{{ route('pacientes.store') }}" method="POST">
                @csrf

                <div class="card-body p-4 p-md-5">
                    
                    {{-- SECCIÓN 1: DATOS PERSONALES --}}
                    <div class="mb-5">
                        <div class="d-flex align-items-center mb-4 pb-2 border-b-section">
                            <span class="icon-shape bg-primary-faded text-primary rounded-circle mr-3">
                                <i class="fas fa-address-card"></i>
                            </span>
                            <h5 class="text-uppercase text-muted font-weight-bold m-0 tracking-wider" style="font-size: 0.9rem;">
                                Información Personal y de Contacto
                            </h5>
                        </div>

                        <div class="row custom-form-group">
                            <div class="col-md-6">
                                <label for="nombre">Nombre(s) <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" 
                                    class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej. Juan" required>
                                @error('nombre') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="apellidos">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" name="apellidos" id="apellidos" value="{{ old('apellidos') }}" 
                                    class="form-control @error('apellidos') is-invalid @enderror" placeholder="Ej. Pérez Gómez" required>
                                @error('apellidos') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row custom-form-group">
                            <div class="col-md-4">
                                <label for="fecha_nacimiento">Fecha de nacimiento <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" 
                                    class="form-control @error('fecha_nacimiento') is-invalid @enderror" required>
                                @error('fecha_nacimiento') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="sexo">Sexo <span class="text-danger">*</span></label>
                                <select name="sexo" id="sexo" class="form-control @error('sexo') is-invalid @enderror" required>
                                    <option value="" disabled {{ old('sexo') ? '' : 'selected' }}>Seleccione...</option>
                                    <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Masculino</option>
                                    <option value="F" {{ old('sexo') == 'F' ? 'selected' : '' }}>Femenino</option>
                                    <option value="O" {{ old('sexo') == 'O' ? 'selected' : '' }}>Otro</option>
                                </select>
                                @error('sexo') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="telefono">Teléfono</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0 text-muted"><i class="fas fa-phone-alt"></i></span>
                                    </div>
                                    <input type="tel" name="telefono" id="telefono" value="{{ old('telefono') }}" 
                                        class="form-control pl-2 @error('telefono') is-invalid @enderror border-left-0" placeholder="999 123 4567">
                                </div>
                                @error('telefono') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row custom-form-group">
                            <div class="col-md-6">
                                <label for="email">Correo electrónico</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0 text-muted"><i class="fas fa-envelope"></i></span>
                                    </div>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" 
                                        class="form-control pl-2 @error('email') is-invalid @enderror border-left-0" placeholder="usuario@correo.com">
                                </div>
                                @error('email') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="direccion">Dirección</label>
                                <input type="text" name="direccion" id="direccion" value="{{ old('direccion') }}" 
                                    class="form-control @error('direccion') is-invalid @enderror" placeholder="Calle, Número, Colonia">
                                @error('direccion') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>


                    {{-- SECCIÓN 2: EXPEDIENTE CLÍNICO --}}
                    <div class="p-4 rounded-lg bg-gray-50 border border-gray-150 shadow-inner-sm">
                        <div class="d-flex align-items-center mb-4">
                            <span class="icon-shape bg-teal-faded text-teal rounded-circle mr-3">
                                <i class="fas fa-file-medical-alt"></i>
                            </span>
                            <div>
                                <h4 class="text-dark font-weight-bold m-0" style="font-size: 1.4rem;">Expediente Médico Inicial</h4>
                                <p class="text-muted text-sm m-0">Diagnóstico preliminar o notas de importancia clínica.</p>
                            </div>
                        </div>

                        <div class="form-group text-muted font-weight-bold" style="font-size: 0.85rem;">
                            <label for="observaciones" class="text-uppercase tracking-wide">Observaciones y notas iniciales</label>
                            <textarea name="observaciones" id="observaciones" class="form-control @error('observaciones') is-invalid @enderror" 
                                rows="5" placeholder="Escriba antecedentes médicos relevantes, alergias conocidas o el cuadro clínico actual..." style="resize: none; border-radius: 8px;"></textarea>
                            @error('observaciones') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>

                </div>

                <div class="card-footer bg-white border-top-0 p-4 p-md-5 d-flex justify-content-end align-items-center">
                    <a href="{{ route('pacientes.index') }}" class="btn btn-link text-muted mr-3 font-weight-bold text-decoration-none">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary px-5 py-2 font-weight-bold shadow-sm rounded-pill">
                        <i class="fas fa-check-circle mr-1"></i> Finalizar y Guardar Paciente
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    {{-- 
        Definimos estilos personalizados sutiles que no rompen AdminLTE 
        pero mejoran la estética visual (inspirado en Tailwind/Material).
    --}}
    <style>
        /* General y Tipografía */
        body { font-family: 'Nucleo', 'Source Sans Pro', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        .tracking-wider { letter-spacing: 0.08em; }
        .border-gray-150 { border-color: #e9ecef !important; }
        .bg-gray-50 { background-color: #f8f9fa; }
        .shadow-inner-sm { box-shadow: inset 0 1px 2px rgba(0,0,0,0.05); }

        /* Estilos de Sección */
        .border-b-section { border-bottom: 2px solid #e9ecef; }
        .icon-shape {
            width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
        }
        .bg-primary-faded { background-color: rgba(0, 123, 255, 0.1); }
        .bg-teal-faded { background-color: rgba(32, 201, 151, 0.1); }
        .text-teal { color: #20c997; }

        /* Estilos de Formulario (Labels e Inputs) */
        .custom-form-group {
            margin-bottom: 1.2rem;
            color: #6c757d;
            font-weight: 700;
            font-size: 0.85rem;
        }
        .custom-form-group label {
            text-uppercase: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border-radius: 6px;
            border-color: #d1d9e6;
            padding: 0.6rem 0.75rem;
            height: auto;
            font-size: 0.9rem;
            transition: all 0.15s ease-in-out;
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
        }
        
        /* Ajuste para inputs con íconos */
        .input-group-text {
            border-radius: 6px 0 0 6px;
            border-color: #d1d9e6;
            font-size: 0.85rem;
        }
    </style>
@stop

@section('js')
     @vite('resources/js/app.js')
    <script> console.log('Alta de pacientes optimizada visualmente.'); </script>
@stop