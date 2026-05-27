<template>
    <div>
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">

            <div class="card-header bg-white border-0 py-3">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text"
                           class="form-control"
                           placeholder="Buscar paciente...">
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
                            <td class="fw-bold text-primary">#001</td>

                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-circle"></div>
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

                            <td>9889677449</td>

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
                                       href="/ExpedientePacientes">
                                        <i class="fas fa-folder-open"></i>
                                    </a>

                                    <a class="btn btn-warning btn-sm action-btn"
                                       data-bs-toggle="modal"
                                       href="#editarpacienteModal">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <button type="button"
        class="btn btn-danger btn-sm action-btn"
        data-bs-toggle="modal"
        data-bs-target="#eliminarpacienteModal">
    <i class="fas fa-trash"></i>
</button>

                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <div class="modal fade" id="editarpacienteModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 overflow-hidden">

                    <div class="modal-header bg-warning border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="fas fa-edit"></i>
                            Editar Paciente
                        </h5>

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <form>
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label">Nombre</label>
                                    <input type="text"
                                           class="form-control"
                                           value="Samy Azael Lopez Acosta">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Teléfono</label>
                                    <input type="text"
                                           class="form-control"
                                           value="9889677449">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Edad</label>
                                    <input type="number"
                                           class="form-control"
                                           value="32">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Sexo</label>
                                    <select class="form-control">
                                        <option selected>Masculino</option>
                                        <option>Femenino</option>
                                    </select>
                                </div>

                            </div>

                            <div class="text-end mt-4">
                                <button type="button"
                                        class="btn btn-secondary me-2"
                                        data-bs-dismiss="modal">
                                    Cancelar
                                </button>

                                <button type="button"
                                        class="btn btn-warning">
                                    Guardar cambios
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
const mensajeEliminar = () => {
    alert('Para eliminar, primero hay que conectar esta tabla con pacientes reales de la base de datos.')
}
</script>