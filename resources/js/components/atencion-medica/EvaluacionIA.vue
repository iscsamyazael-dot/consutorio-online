<template>
    
<div class="card shadow-lg">

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">
            Evaluaciones Inteligentes
        </h5>

    </div>

    <div class="card-body table-responsive">

        <table class="table table-hover table-bordered">

            <thead class="bg-light">

                <tr>

                    <th>Paciente</th>
                    <th>Síntomas</th>
                    <th>Riesgo</th>
                    <th>Diagnóstico IA</th>
                    <th>Estado</th>
                    <th>Acciones</th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td>Juan Pérez</td>
                    <td>Dolor torácico</td>

                    <td>
                        <span class="badge badge-danger p-2">
                            ALTO
                        </span>
                    </td>

                    <td>
                        Evento cardiovascular
                    </td>

                    <td>
                        <span class="badge badge-warning">
                            En análisis
                        </span>
                    </td>

                    <td>

                        <button
    class="btn btn-sm btn-info"
    @click="verEvaluacionIA({
        paciente: 'Juan Pérez',
        sintomas: 'Dolor torácico y dificultad respiratoria',
        riesgo: 'ALTO',
        diagnostico: 'Posible síndrome coronario agudo detectado por IA',
        estado: 'Crítico',
        recomendaciones: 'Traslado inmediato a cardiología y monitoreo continuo',
        fecha: '2026-05-27'
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
MODAL VER EVALUACION IA
========================================= -->

<div
    v-if="modalEvaluacionIA"
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
                        #4f46e5,
                        #06b6d4
                    );
                "
            >

                <div
                    class="d-flex justify-content-between align-items-center"
                >

                    <div class="d-flex align-items-center">

                        <!-- ICONO -->

                        <div
                            class="rounded-circle d-flex justify-content-center align-items-center me-4"
                            style="
                                width:80px;
                                height:80px;
                                background:rgba(255,255,255,.2);
                            "
                        >

                            <i
                                class="fas fa-robot"
                                style="font-size:35px;"
                            ></i>

                        </div>

                        <!-- TEXTO -->

                        <div>

                            <h2 class="fw-bold mb-1">

                                Evaluación Médica IA

                            </h2>

                            <p class="mb-0 opacity-75">

                                Diagnóstico clínico inteligente

                            </p>

                        </div>

                    </div>

                    <!-- CERRAR -->

                    <button
                        class="btn btn-light rounded-circle"
                        @click="modalEvaluacionIA = false"
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

                    <!-- PACIENTE -->

                    <div class="col-md-6 mb-4">

                        <div class="bg-white rounded-4 shadow-sm p-4 h-100">

                            <small class="text-muted">

                                <i class="fas fa-user me-2 text-primary"></i>

                                Paciente

                            </small>

                            <h5 class="fw-bold mt-3">

                                {{ evaluacionIASeleccionada.paciente }}

                            </h5>

                        </div>

                    </div>

                    <!-- RIESGO -->

                    <div class="col-md-6 mb-4">

                        <div class="bg-white rounded-4 shadow-sm p-4 h-100">

                            <small class="text-muted">

                                <i class="fas fa-exclamation-triangle me-2 text-danger"></i>

                                Riesgo Detectado

                            </small>

                            <h5 class="fw-bold text-danger mt-3">

                                {{ evaluacionIASeleccionada.riesgo }}

                            </h5>

                        </div>

                    </div>

                    <!-- ESTADO -->

                    <div class="col-md-6 mb-4">

                        <div class="bg-white rounded-4 shadow-sm p-4 h-100">

                            <small class="text-muted">

                                <i class="fas fa-heartbeat me-2 text-success"></i>

                                Estado Clínico

                            </small>

                            <h5 class="fw-bold text-success mt-3">

                                {{ evaluacionIASeleccionada.estado }}

                            </h5>

                        </div>

                    </div>

                    <!-- FECHA -->

                    <div class="col-md-6 mb-4">

                        <div class="bg-white rounded-4 shadow-sm p-4 h-100">

                            <small class="text-muted">

                                <i class="fas fa-calendar me-2 text-info"></i>

                                Fecha Evaluación

                            </small>

                            <h5 class="fw-bold mt-3">

                                {{ evaluacionIASeleccionada.fecha }}

                            </h5>

                        </div>

                    </div>

                    <!-- SINTOMAS -->

                    <div class="col-12 mb-4">

                        <div class="bg-white rounded-4 shadow-sm p-4">

                            <small class="text-muted">

                                <i class="fas fa-notes-medical me-2 text-warning"></i>

                                Síntomas Detectados

                            </small>

                            <p class="mt-3 mb-0">

                                {{ evaluacionIASeleccionada.sintomas }}

                            </p>

                        </div>

                    </div>

                    <!-- DIAGNOSTICO -->

                    <div class="col-12 mb-4">

                        <div
                            class="rounded-4 shadow-sm p-4 text-white"
                            style="
                                background:
                                linear-gradient(
                                    135deg,
                                    #4338ca,
                                    #06b6d4
                                );
                            "
                        >

                            <small>

                                <i class="fas fa-brain me-2"></i>

                                Diagnóstico IA

                            </small>

                            <h5 class="fw-bold mt-3">

                                {{ evaluacionIASeleccionada.diagnostico }}

                            </h5>

                        </div>

                    </div>

                    <!-- RECOMENDACIONES -->

                    <div class="col-12">

                        <div class="bg-white rounded-4 shadow-sm p-4">

                            <small class="text-muted">

                                <i class="fas fa-lightbulb me-2 text-warning"></i>

                                Recomendaciones IA

                            </small>

                            <p class="mt-3 mb-0">

                                {{ evaluacionIASeleccionada.recomendaciones }}

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

            // =====================================
            // MODAL EVALUACION IA
            // =====================================

            modalEvaluacionIA: false,

            evaluacionIASeleccionada: {}

        }

    },

    methods: {

        // =====================================
        // VER EVALUACION IA
        // =====================================

        verEvaluacionIA(data) {

            this.evaluacionIASeleccionada = data

            this.modalEvaluacionIA = true

        }

    }

}

</script>