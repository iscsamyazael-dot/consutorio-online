<template>
<div class="container">
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        <!-- HEADER -->
        <div class="card-header bg-white border-0 py-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="fas fa-notes-medical text-primary me-2"></i>
                        Consultas Médicas
                    </h4>
                    <small class="text-muted">
                        Historial clínico del paciente
                    </small>
                </div>
                <a
                    href="/consultas/create"
                    class="btn btn-primary rounded-pill px-4"
                >
                    <i class="fas fa-plus me-1"></i>
                    Nueva Consulta
                </a>
            </div>
        </div>
        <!-- BODY -->
        <div class="card-body p-4">
            <!-- CARD CONSULTA -->
            <div class="record-card">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                        <h6 class="fw-bold mb-0">
                            12 Marzo 2026
                        </h6>
                        <span class="badge bg-success rounded-pill px-3 py-2">
                            Finalizada
                        </span>
                    </div>
                    <p class="mb-1">
                        <strong>Motivo:</strong>
                        Dolor abdominal
                    </p>
                    <p class="text-muted mb-2">
                        Diagnóstico: Gastritis aguda
                    </p>
                    <small class="text-muted">
                        Médico: Dr. López
                    </small>
                </div>
                <!-- ACCIONES -->
                <div class="d-flex gap-2 flex-wrap">
                    <button
                        class="btn btn-sm btn-outline-info rounded-pill px-3"
                        data-bs-toggle="modal"
                        data-bs-target="#detalleConsultaModal"
                    >
                        <i class="fas fa-eye"></i>
                    </button>
                    <button
                        class="btn btn-sm btn-outline-warning rounded-pill px-3"
                        data-bs-toggle="modal"
                        data-bs-target="#recetaModal"
                    >
                        <i class="fas fa-prescription"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- MODAL DETALLE -->
<div class="modal fade" id="detalleConsultaModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title">
                    <i class="fas fa-notes-medical me-2"></i>
                    Detalle de la Consulta
                </h5>
                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                ></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="fw-bold">Fecha</label>
                        <p class="text-muted">12/03/2026</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Médico</label>
                        <p class="text-muted">Dr. López</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Motivo</label>
                        <p class="text-muted">
                            Dolor abdominal persistente
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Exploración Física</label>
                        <p class="text-muted">
                            Sensibilidad epigástrica leve
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Diagnóstico</label>
                        <p class="text-danger fw-semibold">
                            Gastritis aguda
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Tratamiento</label>
                        <p class="text-muted">
                            Omeprazol 20mg cada 24h
                        </p>
                    </div>
                </div>
                <hr>
                <div>
                    <label class="fw-bold">
                        Observaciones médicas
                    </label>
                    <p class="text-muted mb-0">
                        Evitar alimentos irritantes.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- MODAL RECETA -->
<div class="modal fade" id="recetaModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title">
                    <i class="fas fa-prescription me-2"></i>
                    Receta Médica
                </h5>
                <button
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                ></button>
            </div>
            <div class="modal-body p-4">
                <div class="section-title mb-4">
                    <h5>Medicamentos</h5>
                    <small>
                        Agrega los medicamentos del tratamiento
                    </small>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Medicamento</th>
                                <th>Dosis</th>
                                <th>Frecuencia</th>
                                <th>Duración</th>
                                <th width="120">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(med, index) in medicamentos"
                                :key="index"
                            >
                                <td>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="med.nombre"
                                    >
                                </td>
                                <td>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="med.dosis"
                                    >
                                </td>
                                <td>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="med.frecuencia"
                                    >
                                </td>
                                <td>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="med.duracion"
                                    >
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button
                                            type="button"
                                            class="btn btn-danger btn-sm"
                                            @click="eliminarMedicamento(index)"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-success btn-sm"
                                            @click="guardarReceta"
                                        >
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button
                    type="button"
                    class="btn btn-outline-primary rounded-pill px-4"
                    @click="agregarMedicamento"
                >
                    <i class="fas fa-plus me-1"></i>
                    Agregar medicamento
                </button>
            </div>
        </div>
    </div>
</div>
</template>
<style scoped>
.rounded-4 {
    border-radius: 24px !important;
}
.section-title {
    margin-bottom: 20px;
}
.section-title h5 {
    font-weight: 800;
    margin-bottom: 2px;
}
.section-title small {
    color: #6c757d;
}
.record-card {
    background: #fff;
    border: 1px solid #edf0f4;
    border-radius: 18px;
    padding: 20px;
    margin-bottom: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.04);
}
@media (max-width: 768px) {
    .record-card {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
<script>

export default {
    data() {
        return {
            medicamentos: [

                {
                    nombre: '',
                    dosis: '',
                    frecuencia: '',
                    duracion: ''
                }
            ]
        }
    },
    methods: {
        agregarMedicamento() {

            this.medicamentos.push({

                nombre: '',
                dosis: '',
                frecuencia: '',
                duracion: ''

            })
        },

        eliminarMedicamento(index) {

            this.medicamentos.splice(index, 1)
        },

        guardarReceta() {
            console.log(this.medicamentos)
            alert('Receta guardada correctamente')
        }
    }
}

</script>