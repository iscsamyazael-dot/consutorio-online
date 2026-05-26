<template>  
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
      
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h3 class="card-title fw-bold text-dark m-0">
                    <i class="fas fa-notes-medical text-primary"></i>
                    Pacientes Registrados
                </h3>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text"
                           class="form-control"
                           placeholder="Buscar paciente...">
                </div>
            </div>
        </div>
       
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Paciente</th>
                        <th>Teléfono</th>
                        <th>Edad</th>
                        <th>Sexo</th>
                        <th>Estado</th>
                        <th width="220" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                      
                        <td class="fw-bold text-primary">
                            #001
                        </td>
                      
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-circle">
                                    
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">
                                        Samy Azael Lopez Acosta
                                    </h6>
                                    <small class="text-muted">
                                        Paciente activo
                                    </small>
                                </div>
                            </div>
                        </td>
                       
                        <td>
                            9889677449
                        </td>
                        <td>
                            <span class="badge bg-info px-3 py-2 rounded-pill">
                                32 años
                            </span>
                        </td>
                        
                        <td>
                            <span class="badge bg-primary px-3 py-2 rounded-pill">
                                Masculino
                            </span>
                        </td>
                        
                        <td>
                            <span class="badge bg-success px-3 py-2 rounded-pill">
                                Consulta activa
                            </span>
                        </td>
                       
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                           
                                <a class="btn btn-info btn-sm action-btn"
                                   data-bs-toggle="modal"
                                   href="#verpacienteModal">
                                    <i class="fas fa-eye"></i>
                                </a>
                          
                                <a class="btn btn-primary btn-sm action-btn"
                                   href="{{url('ExpedientePacientes')}}">
                                    <i class="fas fa-folder-open"></i>
                                </a>
                           
                                <a class="btn btn-warning btn-sm action-btn"
                                   data-bs-toggle="modal"
                                   href="#editarpacienteModal">
                                    <i class="fas fa-edit"></i>
                                </a>
                            
                                <button class="btn btn-danger btn-sm action-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>