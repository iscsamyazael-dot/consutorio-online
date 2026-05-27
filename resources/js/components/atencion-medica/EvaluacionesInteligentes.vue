<template>

<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="font-weight-bold">
            Evaluación IA
        </h1>
        <small class="text-muted">
            Análisis clínico inteligente
        </small>
    </div>
    <button class=" btn btn-primary btn-lg rounded-pill shadow mb-4"
        @click="abrirModalEvaluacionIA">
        <i class="fas fa-robot me-2"></i>
        Nueva Evaluación
    </button>
</div>


<div class="row mb-4">
    <div class="col-lg-4">
        <div class="small-box bg-danger shadow">
            <div class="inner">
                <h3>4</h3>
                <p>Alertas Críticas</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="small-box bg-warning shadow">
            <div class="inner">
                <h3>12</h3>
                <p>Evaluaciones Activas</p>
            </div>
            <div class="icon">
                <i class="fas fa-brain"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="small-box bg-success shadow">
            <div class="inner">
                <h3>28</h3>
                <p>Diagnósticos IA</p>
            </div>
            <div class="icon">
                <i class="fas fa-stethoscope"></i>
            </div>
        </div>
    </div>
</div>




<!-- =========================================
MODAL BOOTSTRAP
========================================= -->

<div
    class="modal fade"
    id="modalEvaluacionIA"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content border-0 shadow-lg rounded-4">

            <!-- =========================================
            HEADER
            ========================================== -->

            <div
                class="modal-header border-0 text-white p-4"
                style="
                    background:
                    linear-gradient(
                        135deg,
                        #2563eb,
                        #0ea5e9
                    );
                "
            >

                <div class="d-flex align-items-center">

                    

                    <div>

                        <h3 class="fw-bold mb-1">   
                            <i class="fas fa-brain me-2"></i>

                            Nueva Evaluación IA

                        </h3>

                        <small class="opacity-75">

                            Sistema Inteligente Clínico

                        </small>

                    </div>

                </div>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                ></button>

            </div>

            <!-- =========================================
            BODY
            ========================================== -->

            <div class="modal-body bg-light p-4">

                <div class="row">

                    <!-- =========================================
                    PANEL IZQUIERDO
                    ========================================== -->

                    <div class="col-lg-4 mb-4">

                        <div class="card border-0 shadow rounded-4 h-100">

                            <div
                                class="card-body text-white p-4"
                                style="
                                    background:
                                    linear-gradient(
                                        180deg,
                                        #1e293b,
                                        #2563eb
                                    );
                                    border-radius:1rem;
                                "
                            >

                                <div class="text-center mb-5">

                                    <div
                                        class="bg-white bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center shadow"
                                        style="
                                            width:90px;
                                            height:90px;
                                        "
                                    >

                                        <i
                                            class="fas fa-robot text-blue"
                                            style="
                                                font-size:38px;
                                            "
                                        ></i>

                                    </div>

                                </div>

                                <h4 class="fw-bold text-center mb-3">

                                    

                                    Evaluación IA

                                </h4>

                                <p
                                    class="text-center opacity-75"
                                    style="
                                        line-height:1.8;
                                    "
                                >

                                    Plataforma médica inteligente
                                    para monitoreo clínico y
                                    análisis de riesgos.

                                </p>

                                <!-- INFO -->

                                <div class="mt-5">

                                    <div
                                        
                                             class="card border-0 text-white rounded-4"
                                                style="
                                                   background: rgba(255,255,255,0.15);
                                                    backdrop-filter: blur(10px);"                                                                                      >

                                        <div class="card-body d-flex align-items-center">

                                            <div
                                                class="bg-success rounded-circle d-flex align-items-center justify-content-center me-3"
                                                style="
                                                    width:50px;
                                                    height:50px;
                                                "
                                            >

                                                <i class="fas fa-heartbeat"></i>

                                            </div>

                                            <div>

                                                <small class="opacity-75">

                                                    Estado Clínico

                                                </small>

                                                <h6 class="fw-bold mb-0">

                                                    Monitoreo IA

                                                </h6>

                                            </div>

                                        </div>

                                    </div>

                                    <div
                                          class="card border-0 text-white rounded-4"
                                                style="
                                                   background: rgba(255,255,255,0.15);
                                                    backdrop-filter: blur(10px);">

                                        <div class="card-body d-flex align-items-center">

                                            <div
                                                class="bg-warning rounded-circle d-flex align-items-center justify-content-center me-3"
                                                style="
                                                    width:50px;
                                                    height:50px;
                                                "
                                            >

                                                <i class="fas fa-exclamation-triangle"></i>

                                            </div>

                                            <div>

                                                <small class="opacity-75">

                                                    Riesgo Detectado

                                                </small>

                                                <h6 class="fw-bold mb-0">

                                                    Análisis Inteligente

                                                </h6>

                                            </div>

                                        </div>

                                    </div>

                                    <div
                                          class="card border-0 text-white rounded-4"
                                                style="
                                                   background: rgba(255,255,255,0.15);
                                                    backdrop-filter: blur(10px);"
                                    >

                                        <div class="card-body d-flex align-items-center">

                                            <div
                                                class="bg-info rounded-circle d-flex align-items-center justify-content-center me-3"
                                                style="
                                                    width:50px;
                                                    height:50px;
                                                "
                                            >

                                                <i class="fas fa-user-md"></i>

                                            </div>

                                            <div>

                                                <small class="opacity-75">

                                                    Diagnóstico IA

                                                </small>

                                                <h6 class="fw-bold mb-0">

                                                    Evaluación Médica

                                                </h6>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- =========================================
                    PANEL DERECHO
                    ========================================== -->

                    <div class="col-lg-8">

                        <!-- BUSCAR -->

                        <div class="card border-0 shadow rounded-4 mb-4">

                            <div class="card-body p-4">

                                <div class="mb-4">

                                    <h5 class="fw-bold mb-1">

                                        <i class="fas fa-user-injured text-primary me-2"></i>

                                        Buscar Paciente

                                    </h5>

                                    <small class="text-muted">

                                        Seleccione un paciente registrado

                                    </small>

                                </div>

                                <div class="input-group input-group-lg">

                                    <span
                                        class="input-group-text border-0 bg-white shadow-sm"
                                    >

                                        <i class="fas fa-search text-primary"></i>

                                    </span>

                                    <input
                                        type="text"
                                        class="form-control border-0 shadow-sm"
                                        placeholder="Buscar paciente..."
                                        v-model="buscarPaciente"
                                    >

                                </div>

                            </div>

                        </div>

                        <!-- FORMULARIO -->

                        <div class="card border-0 shadow rounded-4">

                            <div class="card-body p-4">

                                <div class="row">

                                    <!-- SINTOMAS -->

                                    <div class="col-12 mb-4">

                                        <label class="fw-bold mb-2">

                                            <i class="fas fa-notes-medical text-primary me-2"></i>

                                            Síntomas

                                        </label>

                                        <textarea
                                            class="form-control border-0 shadow-sm rounded-4 p-3"
                                            rows="4"
                                            placeholder="Describa síntomas..."
                                            v-model="form.sintomas"
                                        ></textarea>

                                    </div>

                                    <!-- RIESGO -->

                                    <div class="col-md-6 mb-4">

                                        <label class="fw-bold mb-2">

                                            <i class="fas fa-radiation text-danger me-2"></i>

                                            Nivel de Riesgo

                                        </label>

                                        <select
                                            class="form-select border-0 shadow-sm rounded-4 p-3"
                                            v-model="form.riesgo"
                                        >

                                            <option value="">

                                                Seleccionar...

                                            </option>

                                            <option value="Bajo">

                                                🟢 Riesgo Bajo

                                            </option>

                                            <option value="Moderado">

                                                🟡 Riesgo Moderado

                                            </option>

                                            <option value="Alto">

                                                🔴 Riesgo Alto

                                            </option>

                                        </select>

                                    </div>

                                    <!-- ESTADO -->

                                    <div class="col-md-6 mb-4">

                                        <label class="fw-bold mb-2">

                                            <i class="fas fa-heartbeat text-success me-2"></i>

                                            Estado

                                        </label>

                                        <select
                                            class="form-select border-0 shadow-sm rounded-4 p-3"
                                            v-model="form.estado"
                                        >

                                            <option value="">

                                                Seleccionar...

                                            </option>

                                            <option value="Estable">

                                                Estable

                                            </option>

                                            <option value="Observación">

                                                Observación

                                            </option>

                                            <option value="Crítico">

                                                Crítico

                                            </option>

                                        </select>

                                    </div>

                                    <!-- DIAGNOSTICO -->

                                    <div class="col-12">

                                        <label class="fw-bold mb-2">

                                            <i class="fas fa-brain text-info me-2"></i>

                                            Diagnóstico IA

                                        </label>

                                        <textarea
                                            class="form-control border-0 shadow-sm rounded-4 p-3"
                                            rows="5"
                                            placeholder="Resultado generado por IA..."
                                            v-model="form.diagnostico"
                                        ></textarea>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- =========================================
            FOOTER
            ========================================== -->

            <div class="modal-footer border-0 bg-white p-4">

                <button
                    class="btn btn-light rounded-pill px-4 shadow-sm"
                    data-bs-dismiss="modal"
                >

                    <i class="fas fa-times me-2"></i>

                    Cancelar

                </button>

                <button
                    class="btn btn-primary rounded-pill px-5 shadow"
                    @click="guardarEvaluacionIA"
                >

                    <i class="fas fa-save me-2"></i>

                    Guardar Evaluación

                </button>

            </div>

        </div>

    </div>

</div>



</template>

<script>

import * as bootstrap from 'bootstrap'


export default {

    data(){

    return{

        modalEvaluacionIA: null,

        buscarPaciente: '',

        pacientes: [],

        pacientesFiltrados: [],

        pacienteSeleccionado: null,

        form:{

            sintomas: '',

            riesgo: '',

            diagnostico: '',

            estado: ''

        }

    }

},

mounted(){

    this.modalEvaluacionIA =
        new bootstrap.Modal(
            document.getElementById('modalEvaluacionIA')
        )

},



methods:{
    abrirModalEvaluacionIA(){

    this.modalEvaluacionIA.show()

}, 
    

filtrarPacientes(){

    this.pacientesFiltrados =
        this.pacientes.filter(p =>
            p.nombre.toLowerCase()
            .includes(this.buscarPaciente.toLowerCase())
        )

},

    seleccionarPaciente(paciente){

    this.pacienteSeleccionado = paciente

},


    guardarEvaluacionIA(){

    console.log(this.form)

    this.modalEvaluacionIA.hide()

},

}

}


</script>