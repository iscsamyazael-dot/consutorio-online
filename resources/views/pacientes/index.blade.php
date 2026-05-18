@extends('adminlte::page')

@section('title', 'Lista Pacientes')

@section('content_header')
    <h1>Lista de Pacientes</h1>
@stop

@section('content') 
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col text-end">
                        <a href="" class="btn btn-primary"><i class="fas fa-user-plus"></i> Lista de Paciente</a>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user-injured"></i> Lista de Pacientes</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Teléfono</th>
                                    <th>Edad</th>
                                    <th>Sexo</th>
                                    <th width="220">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>001</td>
                                    <td>Samy Azael Lopez Acosta</td>
                                    <td>9889677449</td>
                                    <td>32</td>
                                    <td>Masculino</td>
                                    <td>
                                        <a class="btn btn-info btn-sm" data-bs-toggle="modal" href="#verpacienteModal" role="button"><i class="fas fa-eye"></i>
                                        </a>
                                        <a class="btn btn-primary btn-sm" href="{{url('ExpedientePacientes')}}" role="button"><i class="fas fa-folder-open"></i>
                                        </a>
                                        <a class="btn btn-warning btn-sm"  data-bs-toggle="modal" href="#editarpacienteModal" role="button"><i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>

                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
 <!-- Modal para ver la información de un paciente  -->
    <div class="modal fade" id="verpacienteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-user"></i> Información del Paciente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 class="text-primary">Datos Personales</h6>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <label for=""><strong>Nombre:</strong></label>
                            <p id="ver_nombre">Samy Azael Lopez Acosta</p>
                        </div>
                        <div class="col-md-6">
                            <label for=""><strong>Telefono:</strong></label>
                            <p id="ver_nombre">9889677449</p>
                        </div>
                        <div class="col-md-6">
                            <label for=""><strong>Sexo:</strong></label>
                            <p id="ver_nombre">Masculino</p>
                        </div>
                        <div class="col-md-6">
                            <label for=""><strong>Fecha de nacimiento:</strong></label>
                            <p id="ver_nombre">25 de noviembre 1992</p>
                        </div>
                        <div class="col-md-12">
                            <label for=""><strong>Dirección:</strong></label>
                            <p id="ver_nombre">Calle 10a x 15 y 17 Sudzal Yucatán</p>
                        </div>
                    </div>
                    <h6 class="text-danger mt-3">Información médica</h6>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <label for=""><strong>Tipo de sangre</strong></label>
                            <p id="ver_tipo_sangre">O Positivo</p>
                        </div>
                        <div class="col-md-6">
                            <label for=""><strong>Alergias</strong></label>
                            <p id="ver_alergias">Ninguna</p>
                        </div>
                        <div class="col-md-6">
                            <label for=""><strong>Enfermedades crónicas</strong></label>
                            <p id="ver_enfermedades">Sindrome de colon irritable</p>
                        </div>
                        <div class="col-md-6">
                            <label for=""><strong>Antecedentes</strong></label>
                            <p id="ver_antecedentes">Ninguna</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">cerrar</button>
                </div>
            </div>
        </div>
    </div>
<!-- Modal para editar la información de un paciente -->
    <div class="modal fade" id="editarpacienteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-user"></i> Información del Paciente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 class="text-primary">Datos Personales</h6>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <label for=""><strong>Nombre:</strong></label>
                            <input class="form-control" type="text" placeholder="Nombre Completo" aria-label="default input example">
                        </div>
                        <div class="col-md-6">
                            <label for=""><strong>Telefono:</strong></label>
                            <input class="form-control" type="text" placeholder="Numero de Telefono" aria-label="default input example">
                        </div>
                        <div class="col-md-6">
                            <label for=""><strong>Sexo:</strong></label>
                            <select class="form-select" aria-label="Default select example">
                                <option selected>Selecciona Uno</option>
                                <option value="1">Masculino</option>
                                <option value="2">Femenino</option>
                                <option value="3">Binario</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for=""><strong>Fecha de nacimiento:</strong></label>
                            <input type="date" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label for=""><strong>Dirección:</strong></label>
                            <input class="form-control" type="text" placeholder="Dirección completa" aria-label="default input example">
                        </div>
                    </div>
                    <h6 class="text-danger mt-3">Información médica</h6>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <label for=""><strong>Tipo de sangre</strong></label>
                             <input class="form-control" type="text" placeholder="Tipo de Sangre" aria-label="default input example">
                        </div>
                        <div class="col-md-6">
                            <label for=""><strong>Alergias</strong></label>
                             <input class="form-control" type="text" placeholder="Alergias" aria-label="default input example">
                        </div>
                        <div class="col-md-6">
                            <label for=""><strong>Enfermedades crónicas</strong></label>
                             <input class="form-control" type="text" placeholder="Enfermedades crónicas" aria-label="default input example">
                        </div>
                        <div class="col-md-6">
                            <label for=""><strong>Antecedentes</strong></label>
                             <input class="form-control" type="text" placeholder="Antecedentes médicos" aria-label="default input example">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">cerrar</button>
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
