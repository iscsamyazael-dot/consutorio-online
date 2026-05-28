<template>

    <div class="card border-0 shadow-sm rounded-4 mt-4 bg-white">

        <div class="card-body p-4">

            <!-- BUSCADOR -->
            <div class="d-flex justify-content-between align-items-center mb-4">

                <div class="search-box">

                    <i class="fas fa-search"></i>

                    <input
                        type="text"
                        class="form-control"
                        placeholder="Buscar paciente..."
                        v-model="searchQuery"
                    />

                </div>

            </div>

            <!-- TABLA -->
            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>

                            <th>Paciente</th>
                            <th>Teléfono</th>
                            <th>Edad</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>

                        </tr>

                    </thead>

                    <tbody class="table-group-divider">

                        <tr
                            v-for="paciente in filteredPacientes"
                            :key="paciente.id"
                        >

                            <!-- PACIENTE -->
                            <td>

                                <div class="d-flex align-items-center gap-3">

                                    <div class="avatar-circle">
                                        {{ paciente.nombre.charAt(0) }}
                                    </div>

                                    <div>

                                        <h6 class="fw-bold mb-0">
                                            {{ paciente.nombre }}
                                        </h6>

                                        <small class="text-muted">
                                            ID: {{ paciente.id }}
                                        </small>

                                    </div>

                                </div>

                            </td>

                            <!-- TELÉFONO -->
                            <td>
                                {{ paciente.telefono }}
                            </td>

                            <!-- EDAD -->
                            <td>
                                {{ paciente.edad }} años
                            </td>

                            <!-- ESTADO -->
                            <td>

                                <span
                                    class="badge bg-success-subtle text-success rounded-pill px-3 py-2"
                                >
                                    {{ paciente.estado }}
                                </span>

                            </td>

                            <!-- ACCIONES -->
                            <td class="text-end">

                                <!-- VER -->
                                <button
                                    class="btn btn-light btn-sm action-btn me-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#verpacienteModal"
                                >
                                    <i class="fas fa-eye text-primary"></i>
                                </button>

                                <!-- EXPEDIENTE -->
                                <a
                                    class="btn btn-light btn-sm action-btn me-2"
                                    href="/ExpedientePacientes"
                                >
                                    <i class="fas fa-folder-open text-info"></i>
                                </a>

                                <!-- EDITAR -->
                                <button
                                    class="btn btn-light btn-sm action-btn me-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editarpacienteModal"
                                >
                                    <i class="fas fa-edit text-warning"></i>
                                </button>

                                <!-- ELIMINAR -->
                                <button
                                    class="btn btn-light btn-sm action-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#eliminarpacienteModal"
                                >
                                    <i class="fas fa-trash text-danger"></i>
                                </button>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

<!-- Modalees para las acciones de los botones de un paciente (Ver, Ediitar, Eliminar)-->
<div class="modal fade" id="verpacienteModal" tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 rounded-4 overflow-hidden">

            <div class="modal-header bg-primary text-white border-0">

                <h5 class="modal-title fw-bold">
                    <i class="fas fa-user-circle"></i>
                    Información del Paciente
                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body p-4">

                <div class="patient-profile mb-4">

                    <div class="avatar-large">
                        S
                    </div>

                    <div>
                        <h3 class="fw-bold mb-1">
                            Samy Azael Lopez Acosta
                        </h3>

                        <span class="badge bg-success">
                            Consulta activa
                        </span>
                    </div>

                </div>

                <div class="row g-4">

                    <div class="col-md-6">
                        <div class="info-card">
                            <label>Teléfono</label>
                            <h6>9889677449</h6>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-card">
                            <label>Sexo</label>
                            <h6>Masculino</h6>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-card">
                            <label>Tipo de Sangre</label>
                            <h6>O Positivo</h6>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-card">
                            <label>Alergias</label>
                            <h6>Ninguna</h6>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="info-card">
                            <label>Dirección</label>
                            <h6>
                                Calle 10a x 15 y 17 Sudzal Yucatán
                            </h6>
                        </div>
                    </div>

                </div>

            </div>

        </div>

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

                    <div class="row g-4">

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

                        <div class="col-md-12">
                            <label class="form-label">Estado</label>
                            <select class="form-control">
                                <option selected>Consulta activa</option>
                                <option>Paciente activo</option>
                                <option>Pendiente</option>
                            </select>
                        </div>

                    </div>

                    <div class="text-end mt-4">
                        <button type="button"
                                class="btn btn-secondary"
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

<div class="modal fade" id="eliminarpacienteModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 rounded-4 overflow-hidden">

            <div class="modal-header bg-danger text-white border-0">

                <h5 class="modal-title fw-bold">
                    <i class="fas fa-trash"></i>
                    Eliminar Paciente
                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body p-4 text-center">

                <div class="delete-icon mb-3">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>

                <h5 class="fw-bold mb-2">
                    ¿Estás seguro de eliminar este paciente?
                </h5>

                <p class="text-muted mb-0">
                    Esta acción no se puede deshacer.
                </p>

            </div>

            <div class="modal-footer border-0 justify-content-center pb-4">

                <button type="button"
                        class="btn btn-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button type="button"
                        class="btn btn-danger rounded-pill px-4">
                    Sí, eliminar
                </button>

            </div>

        </div>

    </div>

</div>

</template>

<script setup>

import { ref, computed } from 'vue'

const searchQuery = ref('')

const pacientes = ref([

    {
        id: 1,
        nombre: 'Samuel Azael Lopez Acosta',
        telefono: '9889677449',
        edad: 32,
        estado: 'Consulta activa'
    },

    {
        id: 2,
        nombre: 'Maria Lopez',
        telefono: '9991234567',
        edad: 28,
        estado: 'Paciente activo'
    }

])

const filteredPacientes = computed(() => {

    return pacientes.value.filter(paciente =>

        paciente.nombre
            .toLowerCase()
            .includes(searchQuery.value.toLowerCase())

    )

})

</script>

<style>

.card{
    border-radius:24px !important;
    overflow:hidden;
    background:white !important;
}

/* TABLA */
.table{
    margin-bottom:0 !important;
}

.table thead th{
    border:none !important;
    padding:18px !important;
    font-weight:700 !important;
    color:#495057 !important;
    background:#eef0f3 !important;
    font-size:15px !important;
}

.table tbody td{
    padding:18px !important;
    vertical-align:middle !important;
    border-top:1px solid #1d5994 !important;
}

.table-hover tbody tr:hover{
    background:#f8fbff !important;
    transition:.3s;
}

/* AVATAR */
.avatar-circle{
    width:50px !important;
    height:50px !important;
    border-radius:50% !important;
    background:linear-gradient(135deg,#0d6efd,#00c6ff) !important;
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    color:white !important;
    font-weight:bold !important;
    font-size:20px !important;
    box-shadow:0 5px 15px rgba(0,0,0,.15) !important;
}

/* BOTONES */
.action-btn{
    border-radius:12px !important;
    transition:.3s !important;
    box-shadow:0 3px 8px rgba(0,0,0,.08) !important;
    width:38px !important;
    height:38px !important;
    border:none !important;
}

.action-btn:hover{
    transform:translateY(-3px);
}

/* BUSCADOR */
.search-box{
    position:relative;
    width:320px;
}

.search-box i{
    position:absolute;
    top:14px;
    left:15px;
    color:#999;
    z-index:10;
}

.search-box input{
    padding-left:42px !important;
    border-radius:14px !important;
    border:1px solid #e5e7eb !important;
    height:48px !important;
    box-shadow:none !important;
}

.search-box input:focus{
    border-color:#0d6efd !important;
    box-shadow:0 0 0 .2rem rgba(13,110,253,.15) !important;
}

/* BADGE */
.bg-success-subtle{
    background:#d1fae5 !important;
}

/* RESPONSIVE */
.table-responsive{
    overflow-x:auto;
}

</style>