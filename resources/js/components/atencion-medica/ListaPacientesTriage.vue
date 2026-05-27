
<template>

<!-- Lista -->

<div class="card card-outline card-primary shadow-lg">

    <div class="card-header">

        <h3 class="card-title font-weight-bold">
            Lista de Pacientes TRIAGE
        </h3>

    </div>

    <div class="card-body table-responsive">

        <table class="table table-hover table-bordered">

            <thead class="bg-light">

                <tr>

                    <th>Prioridad</th>
                    <th>Paciente</th>
                    <th>Síntomas</th>
                    <th>Presión</th>
                    <th>Saturación</th>
                    <th>Temperatura</th>
                    <th>Estado</th>
                    <th>Tiempo</th>
                    <th>Acciones</th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td>
                        <span class="badge badge-danger p-2">
                            CRÍTICO
                        </span>
                    </td>

                    <td>
                        Juan Pérez
                    </td>

                    <td>
                        Dolor torácico
                    </td>

                    <td>
                        180/120
                    </td>

                    <td>
                        86%
                    </td>

                    <td>
                        39°C
                    </td>

                    <td>
                        <span class="badge badge-danger">
                            Riesgo Alto
                        </span>
                    </td>

                    <td>
                        2 min
                    </td>

                    <td>

                      <button
    class="btn btn-sm btn-info"
    @click="verTriage({
        paciente: 'Juan Pérez',
        sintomas: 'Dolor torácico',
        presion: '180/120',
        saturacion: '86%',
        temperatura: '39°C',
        estado: 'Riesgo Alto',
        prioridad: 'CRÍTICO',
        tiempo: '2 min'
    })"
>

    <i class="fas fa-eye"></i>

</button>

                        <button
    class="btn btn-sm btn-warning"
    @click="editarTriage({
        paciente: 'Juan Pérez',
        sintomas: 'Dolor torácico',
        presion: '180/120',
        saturacion: '86%',
        temperatura: '39°C',
        estado: 'Riesgo Alto',
        prioridad: 'CRÍTICO',
        tiempo: '2 min'
    })"
>

    <i class="fas fa-edit"></i>

</button>

                    </td>

                </tr>

                <tr>

                    <td>
                        <span class="badge badge-warning p-2">
                            URGENTE
                        </span>
                    </td>

                    <td>
                        María López
                    </td>

                    <td>
                        Fiebre alta
                    </td>

                    <td>
                        140/90
                    </td>

                    <td>
                        94%
                    </td>

                    <td>
                        38°C
                    </td>

                    <td>
                        <span class="badge badge-warning">
                            Observación
                        </span>
                    </td>

                    <td>
                        10 min
                    </td>

                    <td>

                        <button
    class="btn btn-sm btn-info"
    @click="verTriage({
        paciente: 'Maria Lopez',
        sintomas: 'Fiebre Alta',
        presion: '140/90',
        saturacion: '94%',
        temperatura: '38°C',
        estado: 'Observacion',
        prioridad: 'URGENTE',
        tiempo: '10 min'
    })"
>

    <i class="fas fa-eye"></i>

</button>

                        <button class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </button>

                    </td>

                </tr>

                <tr>

                    <td>
                        <span class="badge badge-info p-2">
                            MODERADO
                        </span>
                    </td>

                    <td>
                        Carlos Méndez
                    </td>

                    <td>
                        Dolor abdominal
                    </td>

                    <td>
                        120/80
                    </td>

                    <td>
                        98%
                    </td>

                    <td>
                        37°C
                    </td>

                    <td>
                        <span class="badge badge-info">
                            Estable
                        </span>
                    </td>

                    <td>
                        20 min
                    </td>

                    <td>

                         <button
                                class="btn btn-sm btn-info"
                                @click="verTriage({
                                    paciente: 'Carlos Mendez',
                                    sintomas: 'Dolor Abdominal',
                                    presion: '140/90',
                                    saturacion: '94%',
                                    temperatura: '38°C',
                                    estado: 'Observacion',
                                    prioridad: 'URGENTE',
                                    tiempo: '10 min'
                                })"
                            >

                                <i class="fas fa-eye"></i>

                            </button>

                        <button class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </button>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>


<!-- =========================================
MODAL VER TRIAGE PREMIUM
========================================= -->

<div
    v-if="modalVerTriage"
    class="modal fade show d-block"
    tabindex="-1"
    style="
        background: rgba(15,23,42,.75);
        backdrop-filter: blur(6px);
    "
>

    <div class="modal-dialog modal-xl modal-dialog-centered">

        <div
            class="modal-content border-0 overflow-hidden rounded-5 shadow-lg"
        >

            <!-- HEADER -->

            <div
                class="p-4 text-white"
                style="
                    background:
                    linear-gradient(
                        135deg,
                        #2563eb,
                        #0ea5e9
                    );
                "
            >

                <div
                    class="d-flex justify-content-between align-items-center"
                >

                    <!-- IZQUIERDA -->

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
                                class="fas fa-notes-medical"
                                style="font-size:35px;"
                            ></i>

                        </div>

                        <!-- TEXTO -->

                        <div>

                            <h2 class="fw-bold mb-1">

                                Información TRIAGE

                            </h2>

                            <p class="mb-0 opacity-75">

                                Evaluación clínica del paciente

                            </p>

                        </div>

                    </div>

                    <!-- BOTON -->

                    <button
                        class="btn btn-light rounded-circle shadow-sm"
                        @click="modalVerTriage = false"
                    >

                        <i class="fas fa-times"></i>

                    </button>

                </div>

            </div>

            <!-- BODY -->

            <div class="modal-body p-5"
                style="background:#f1f5f9;">

                <!-- CARDS -->

                <div class="row">

                    <!-- PACIENTE -->

                    <div class="col-md-6 mb-4">
                        

                        <div
                            class="bg-white rounded-4 shadow-sm p-4 h-100"
                        >

                            <small class="text-muted">
                                 <i class="fas fa-user me-2 text-primary"></i>
                                Paciente

                            </small>
                            

                            <h5 class="fw-bold text-primary mt-2">

                                {{ triageSeleccionado.paciente }}

                            </h5>

                        </div>

                    </div>

                    <!-- PRIORIDAD -->

                    <div class="col-md-6 mb-4">

                        <div
                            class="bg-white rounded-4 shadow-sm p-4 h-100"
                        >

                            <small class="text-muted">
                                <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                                Prioridad

                            </small>

                            <h5 class="fw-bold text-danger mt-2">

                                {{ triageSeleccionado.prioridad }}

                            </h5>

                        </div>

                    </div>

                    <!-- ESTADO -->

                    <div class="col-md-4 mb-4">

                        <div
                            class="bg-white rounded-4 shadow-sm p-4 h-100"
                        >

                            <small class="text-muted">
                                <i class="fas fa-heartbeat me-2 text-success"></i>
                                Estado Clínico

                            </small>

                            <h6 class="fw-bold text-success mt-2">

                                {{ triageSeleccionado.estado }}

                            </h6>

                        </div>

                    </div>

                    <!-- PRESION -->

                    <div class="col-md-4 mb-4">

                        <div
                            class="bg-white rounded-4 shadow-sm p-4 h-100"
                        >

                            <small class="text-muted">
                                <i class="fas fa-stethoscope me-2 text-dark"></i>
                                Presión

                            </small>

                            <h6 class="fw-bold mt-2">

                                {{ triageSeleccionado.presion }}

                            </h6>

                        </div>

                    </div>

                    <!-- SATURACION -->

                    <div class="col-md-4 mb-4">

                        <div
                            class="bg-white rounded-4 shadow-sm p-4 h-100"
                        >

                            <small class="text-muted">
                                <i class="fas fa-lungs me-2 text-info"></i>
                                Saturación

                            </small>

                            <h6 class="fw-bold mt-2">

                                {{ triageSeleccionado.saturacion }}

                            </h6>

                        </div>

                    </div>

                    <!-- TEMPERATURA -->

                    <div class="col-md-6 mb-4">

                        <div
                            class="bg-white rounded-4 shadow-sm p-4 h-100"
                        >

                            <small class="text-muted">
                                <i class="fas fa-thermometer-half me-2 text-warning"></i>
                                Temperatura

                            </small>

                            <h6 class="fw-bold mt-2 text-warning">

                                {{ triageSeleccionado.temperatura }}

                            </h6>

                        </div>

                    </div>

                    <!-- TIEMPO -->

                    <div class="col-md-6 mb-4">

                        <div
                            class="bg-white rounded-4 shadow-sm p-4 h-100"
                        >

                            <small class="text-muted">
                                <i class="fas fa-clock me-2 text-secondary"></i>
                                Tiempo de Atención

                            </small>

                            <h6 class="fw-bold mt-2 text-info">

                                {{ triageSeleccionado.tiempo }}

                            </h6>

                        </div>

                    </div>

                    <!-- SINTOMAS -->

                    <div class="col-12">

                        <div
                            class="bg-white rounded-4 shadow-sm p-4"
                        >

                            <small class="text-muted">
                                <i class="fas fa-notes-medical me-2 text-primary"></i>
                                Síntomas Detectados

                            </small>

                            <p
                                class="mt-3 mb-0"
                                style="
                                    line-height:1.8;
                                    font-size:16px;
                                "
                            >

                                {{ triageSeleccionado.sintomas }}

                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <!-- FOOTER -->

            <div
                class="modal-footer bg-white border-0 px-5 pb-4"
            >

                <button
                    class="btn btn-secondary rounded-pill px-4"
                    @click="modalVerTriage = false"
                >

                    Cerrar

                </button>

            </div>

        </div>

    </div>

</div>


<!-- MODAL EDITAR -->

<div
    v-if="modalEditarTriage"
    class="modal fade show d-block"
    tabindex="-1"
    style="background: rgba(0,0,0,.5);"
>

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <!-- HEADER -->

            <div class="modal-header bg-warning">

                <h5 class="modal-title text-white">

                    Editar Triage

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    @click="modalEditarTriage = false"
                ></button>

            </div>

            <!-- BODY -->

            <div class="modal-body">

                <!-- PACIENTE -->

                <div class="mb-3">

                    <label class="form-label">
                        <i class="fas fa-user me-2 text-primary"></i>
                        Paciente

                    </label>

                    <input
                        type="text"
                        class="form-control"
                        v-model="triageEditar.paciente"
                    >

                </div>

                <!-- SINTOMAS -->

                <div class="mb-3">

                    <label class="form-label">
                        <i class="fas fa-notes-medical me-2 text-primary"></i>
                        Síntomas

                    </label>

                    <textarea
                        class="form-control"
                        rows="3"
                        v-model="triageEditar.sintomas"
                    ></textarea>

                </div>

                <!-- ESTADO -->

                <div class="mb-3">

                    <label class="form-label">
                        <i class="fas fa-heartbeat me-2 text-success"></i>
                        Estado

                    </label>

                    <select
                        class="form-control"
                        v-model="triageEditar.estado"
                    >

                        <option>

                            Riesgo Alto

                        </option>

                        <option>

                            Observación

                        </option>

                        <option>

                            Estable

                        </option>

                    </select>

                </div>

                <!-- PRIORIDAD -->

                <div class="mb-3">

                      <label class="form-label">
                        <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                            Prioridad
                                    </label>

    <select
        class="form-control"
        v-model="triageEditar.prioridad"
    >

        <option>

            CRÍTICO

        </option>

        <option>

            URGENTE

        </option>

        <option>

            MODERADO

        </option>

    </select>

</div>


    <!-- PRESION -->

        <div class="mb-3">

    <label class="form-label">
        <i class="fas fa-stethoscope me-2 text-dark"></i>
        Presión

    </label>

    <input
        type="text"
        class="form-control"
        v-model="triageEditar.presion"
    >

</div>

        <!-- SATURACION -->

        <div class="mb-3">

    <label class="form-label">
        <i class="fas fa-lungs me-2 text-info"></i>
        Saturación

    </label>

    <input
        type="text"
        class="form-control"
        v-model="triageEditar.saturacion"
    >

</div>

    <!-- TEMPERATURA -->

    <div class="mb-3">

    <label class="form-label">
        <i class="fas fa-thermometer-half me-2 text-warning"></i>
        Temperatura

    </label>

    <input
        type="text"
        class="form-control"
        v-model="triageEditar.temperatura"
    >

</div>


    <!-- TIEMPO -->

    <div class="mb-3">

    <label class="form-label">
        <i class="fas fa-clock me-2 text-secondary"></i>
        Tiempo de Atención

    </label>

    <input
        type="text"
        class="form-control"
        v-model="triageEditar.tiempo"
    >

</div>



            </div>

            <!-- FOOTER -->

            <div class="modal-footer">

                <button
                    class="btn btn-secondary"
                    @click="modalEditarTriage = false"
                >

                    Cancelar

                </button>

                <button
                    class="btn btn-warning text-white"
                    @click="guardarEdicion"
                >

                    Guardar Cambios

                </button>

            </div>

        </div>

    </div>

</div>



</template>



<script>

export default {

    data() {

        return {

            modalVerTriage: false,
            modalEditarTriage: false,
            

            triageSeleccionado: {

                triageEditar: {

                paciente: '',

                sintomas: '',

                presion: '',

                saturacion: '',

                temperatura: '',

                estado: '',

                prioridad: '',

                tiempo: ''

            }

        }

        }

    },

    methods: {

         // VER INFORMACIÓN

        verTriage(data) {

            this.triageSeleccionado = data

            this.modalVerTriage = true

        },

        // EDITAR INFORMACIÓN

        editarTriage(data) {

            // CLONAR DATOS

            this.triageEditar = { ...data }

            // ABRIR MODAL

            this.modalEditarTriage = true

        },

        // GUARDAR CAMBIOS

        guardarEdicion() {

            console.log(this.triageEditar)

            // CERRAR MODAL

            this.modalEditarTriage = false

        }

    }

}

</script>

