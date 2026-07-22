<template>
    
    <div class="card shadow-lg">
        
            <div class="card-header bg-warning text-white">

        <h5 class="mb-0">
            Lista de Derivaciones
        </h5>

    </div>

    <div class="card-body table-responsive">

        <table class="table table-hover table-bordered">

            <thead class="bg-light">

                <tr>

                    <th>Paciente</th>
                    <th>Especialidad</th>
                    <th>Motivo</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Acciones</th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td>Juan Pérez</td>

                    <td>Cardiología</td>

                    <td>
                        Sospecha cardiovascular
                    </td>

                    <td>

                        <span class="badge badge-danger p-2">
                            ALTA
                        </span>

                    </td>

                    <td>

                        <span class="badge badge-warning">
                            Pendiente
                        </span>

                    </td>

                    <td>

                            <button
    class="btn btn-sm btn-info"
    @click="verDerivacion({
        paciente: 'Juan Pérez',
        especialidad: 'Cardiología',
        estado: 'Urgente',
        fecha: '2026-05-27',
        motivo: 'Posible infarto agudo detectado por IA'
    })"
>

    <i class="fas fa-eye"></i>

</button>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>


<!-- =========================================
MODAL VER DERIVACION
========================================= -->

<div
    v-if="modalDerivacion"
    class="modal fade show d-block"
    tabindex="-1"
    style="
        background: rgba(15,23,42,.75);
        backdrop-filter: blur(6px);
    "
>

    <div class="modal-dialog modal-xl modal-dialog-centered">

        <div
            class="modal-content border-0 rounded-5 overflow-hidden shadow-lg"
        >

            <!-- HEADER -->

            <div
                class="p-4 text-white"
                style="
                    background:
                    linear-gradient(
                        135deg,
                        #0f766e,
                        #14b8a6
                    );
                "
            >

                <div
                    class="d-flex justify-content-between align-items-center"
                >

                    <div class="d-flex align-items-center">

                        <div
                            class="rounded-circle d-flex justify-content-center align-items-center me-4"
                            style="
                                width:80px;
                                height:80px;
                                background:rgba(255,255,255,.2);
                            "
                        >

                            <i
                                class="fas fa-ambulance"
                                style="font-size:35px;"
                            ></i>

                        </div>

                        <div>

                            <h2 class="fw-bold mb-1">

                                Derivación Médica

                            </h2>

                            <p class="mb-0 opacity-75">

                                Información de traslado clínico

                            </p>

                        </div>

                    </div>

                    <button
                        class="btn btn-light rounded-circle"
                        @click="modalDerivacion = false"
                    >

                        <i class="fas fa-times"></i>

                    </button>

                </div>

            </div>

            <!-- BODY -->

            <div
                class="modal-body p-5"
                style="background:#f1f5f9;"
            >

                <div class="row">

                    <div class="col-md-6 mb-4">

                        <div class="bg-white rounded-4 shadow-sm p-4">

                            <small class="text-muted">

                                <i class="fas fa-user me-2 text-primary"></i>

                                Paciente

                            </small>

                            <h5 class="fw-bold mt-3">

                                {{ derivacionSeleccionada.paciente }}

                            </h5>

                        </div>

                    </div>

                    <div class="col-md-6 mb-4">

                        <div class="bg-white rounded-4 shadow-sm p-4">

                            <small class="text-muted">

                                <i class="fas fa-hospital me-2 text-danger"></i>

                                Especialidad

                            </small>

                            <h5 class="fw-bold mt-3">

                                {{ derivacionSeleccionada.especialidad }}

                            </h5>

                        </div>

                    </div>

                    <div class="col-md-6 mb-4">

                        <div class="bg-white rounded-4 shadow-sm p-4">

                            <small class="text-muted">

                                <i class="fas fa-heartbeat me-2 text-success"></i>

                                Estado

                            </small>

                            <h5 class="fw-bold mt-3">

                                {{ derivacionSeleccionada.estado }}

                            </h5>

                        </div>

                    </div>

                    <div class="col-md-6 mb-4">

                        <div class="bg-white rounded-4 shadow-sm p-4">

                            <small class="text-muted">

                                <i class="fas fa-calendar me-2 text-info"></i>

                                Fecha

                            </small>

                            <h5 class="fw-bold mt-3">

                                {{ derivacionSeleccionada.fecha }}

                            </h5>

                        </div>

                    </div>

                    <div class="col-12">

                        <div class="bg-white rounded-4 shadow-sm p-4">

                            <small class="text-muted">

                                <i class="fas fa-notes-medical me-2 text-warning"></i>

                                Motivo de Derivación

                            </small>

                            <p class="mt-3 mb-0">

                                {{ derivacionSeleccionada.motivo }}

                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>



</template>


<script>

export default {

    data() {

        return {

            modalDerivacion: false,

            derivacionSeleccionada: {}

        }

    },

    methods: {

        verDerivacion(data) {

            this.derivacionSeleccionada = data

            this.modalDerivacion = true

        }

    }

}

</script>