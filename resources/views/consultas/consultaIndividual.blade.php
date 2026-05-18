@extends('adminlte::page')

@section('title', 'Registrar Paciente')

@section('content_header')
    <h1>Historial de las consultas del Paciente</h1>
@stop

@section('content') 

    <div class="container">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-notes-medical text-primary"></i>
                    Consultas Médicas
                </h6>
                <a href="#" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Nueva Consulta
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Motivo</th>
                                <th>Diagnóstico</th>
                                <th>Médico</th>
                                <th>Estado</th>
                                <th width="180">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>12/03/2026</td>
                                <td>Dolor abdominal</td>
                                <td>Gastritis aguda</td>
                                <td>Dr. López</td>
                                <td>
                                    <span class="badge bg-success">
                                        Finalizada
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#detalleConsultaModal">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#recetaModal">
                                        <i class="fas fa-prescription"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#archivosModal">
                                        <i class="fas fa-file-medical"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<!-- Modal para ver la consulta del paciente -->
 <!-- MODAL DETALLE CONSULTA -->
<div class="modal fade" id="detalleConsultaModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-notes-medical"></i>
                    Detalle de la Consulta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="fw-bold">Fecha</label>
                        <p>12/03/2026</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Médico</label>
                        <p>Dr. López</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                         <label class="fw-bold">Motivo de consulta</label>
                        <p>Dolor abdominal persistente</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Exploración física</label>
                        <p>Sensibilidad epigástrica leve.</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <label class="fw-bold">Diagnóstico</label>
                        <p class="text-danger fw-semibold">
                        Gastritis aguda
                        </p>
                    </div>
                    <div class="col-md-6">
                         <label class="fw-bold">Tratamiento indicado</label>
                         <p>Omeprazol 20mg cada 24h.</p>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <label class="fw-bold">Observaciones médicas</label>
                    <p>Evitar alimentos irritantes.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-warning" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#editarConsultaModal">
                    <i class="fas fa-edit"></i> Editar Consulta
                </button>
                <button class="btn btn-secondary">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL RECETA -->
<div class="modal fade" id="recetaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-prescription"></i>
                    Receta Médica
                </h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
               <div id="recetaApp">
                    <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Medicamento</th>
                            <th>Dosis</th>
                            <th>Frecuencia</th>
                            <th>Duración</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(med, index) in medicamentos" :key="index">
                            <td>
                                 <input type="text" class="form-control" v-model="med.nombre" :name="'medicamentos['+index+'][nombre]'">
                            </td>
                            <td>
                                <input type="text" class="form-control" v-model="med.dosis" :name="'medicamentos['+index+'][dosis]'">
                            </td>
                            <td>
                                <input type="text" class="form-control" v-model="med.frecuencia" :name="'medicamentos['+index+'][frecuencia]'">
                            </td>
                            <td>
                                <input type="text" class="form-control" v-model="med.duracion" :name="'medicamentos['+index+'][duracion]'">
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm" @click="eliminarMedicamento(index)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <button class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-plus"></i> Agregar medicamento
                </button>
               </div>
            </div>

        </div>
    </div>
</div>

<!-- MODAL ARCHIVOS CLINICOS -->
<div class="modal fade" id="archivosModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-file-medical"></i>
                    Archivos Clínicos
                </h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <ul class="list-group mb-3">
                    <li class="list-group-item d-flex justify-content-between">
                        Laboratorio.pdf
                        <button class="btn btn-sm btn-outline-primary">
                            Ver
                        </button>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        Ultrasonido.jpg
                        <button class="btn btn-sm btn-outline-primary">
                            Ver
                        </button>
                    </li>
                </ul>
                <div>
                    <label class="fw-bold">Nota médica</label>
                    <textarea class="form-control" rows="3"></textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDITAR CONSULTA -->
<div class="modal fade" id="editarConsultaModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i>
                    Editar Consulta Médica
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="#" action="#">
                <!-- Laravel -->
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Fecha</label>
                            <input type="date" class="form-control" name="fecha">
                        </div>
                        <div class="col-md-6">
                            <label>Estado</label>
                            <select class="form-select" name="estado">
                                <option>Pendiente</option>
                                <option>En proceso</option>
                                <option selected>Finalizada</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Motivo de consulta</label>
                        <textarea class="form-control" name="motivo" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Exploración física</label>
                        <textarea class="form-control" name="exploracion" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Diagnóstico</label>
                        <textarea class="form-control" name="diagnostico" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Tratamiento</label>
                        <textarea class="form-control" name="tratamiento" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Observaciones médicas</label>
                        <textarea class="form-control" name="observaciones" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script> console.log('Hi!'); </script>
     @vite('resources/js/app.js')

@stop