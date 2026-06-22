
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

                    <tr v-for="paciente in triage" :key="paciente.id">
                        <td>
                                <!-- //Mensaje dinamico de prioridad de funcion obtener prioridad -->
                            <span
                                class="badge"
                                :class="obtenerPrioridad(paciente.triages[0]?.estado).clase">
                                {{ obtenerPrioridad(paciente.triages[0]?.estado).texto }}
                            </span>

                        </td>

                        <td>
                            {{ paciente.nombre }}
                        </td>

                        <td>{{ paciente.triages[0]?.sintomas }}</td>

                        <td>{{ paciente.triages[0]?.presion }}</td>

                        <td>{{ paciente.triages[0]?.saturacion }}</td>

                        <td>{{ paciente.triages[0]?.temperatura }}</td>

                        <td>
                            <span
                                class="badge bg-danger"
                                v-if="paciente.triages[0]?.estado === 'grave'">
                                GRAVE
                            </span>
                            <span
                                class="badge bg-warning"
                                v-else-if="paciente.triages[0]?.estado === 'moderado'">
                                MODERADO
                            </span>
                            <span
                                class="badge bg-success"
                                v-else>
                                LEVE
                            </span>
                            
                        </td>

                        <td>
                            {{ obtenerTiempoEspera(paciente.triages[0]?.created_at) }}
                        </td>

                        <td>

                            <button
                                class="btn btn-info btn-sm"
                                data-toggle="modal"
                                data-target="#modalVerTriage"
                                @click="obtenerPacientesIndividual(paciente.id)">
                                <i class="fas fa-eye"></i>
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
    class="modal fade"
    id="modalVerTriage"
    tabindex="-1"
    aria-labelledby="modalVerTriageLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 overflow-hidden rounded-5 shadow-lg">

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
                ">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div
                            class="rounded-circle d-flex justify-content-center align-items-center me-4"
                            style="
                                width:80px;
                                height:80px;
                                background:rgba(255,255,255,.2);
                            ">
                            <i
                                class="fas fa-notes-medical"
                                style="font-size:35px;"
                            ></i>
                        </div>
                        <div>

                            <h2
                                class="fw-bold mb-1"
                                id="modalVerTriageLabel">
                                Información TRIAGE
                            </h2>
                            <p class="mb-0 opacity-75">
                                Evaluación clínica del paciente
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BODY -->

            <div
                class="modal-body p-5"
                style="background:#f1f5f9;">

                <div class="row">

                    <!-- PACIENTE -->

                    <div class="col-md-6 mb-4">
                        <div class="bg-white rounded-4 shadow-sm p-4 h-100">
                            <small class="text-muted">
                                <i class="fas fa-user me-2 text-primary"></i>
                                Paciente
                            </small>
                            <h5 class="fw-bold text-primary mt-2">
                              {{detalletriage.nombre}}
                            </h5>
                        </div>
                
                    </div>

                    <!-- PRIORIDAD -->

                    <div class=" col-md-6 mb-4">
                        <div class="bg-white rounded-4 shadow-sm p-4 h-100">
                            <small class="text-muted">
                                <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                                Prioridad
                            </small>
                            <h5 class="fw-bold text-danger mt-2">
                               {{ obtenerPrioridad(detalletriage?.triages?.[0]?.estado).texto }}
                            </h5>
                        </div>
                    </div>

                    <!-- ESTADO -->

                    <div class="col-md-4 mb-4">
                        <div class="bg-white rounded-4 shadow-sm p-4 h-100">
                            <small class="text-muted">
                                <i class="fas fa-heartbeat me-2 text-success"></i>
                                Estado Clínico
                            </small>
                            <h6 class="fw-bold text-success mt-2"> 
                                <span class="fw-bold mt-2 badge bg-danger"
                                    v-if="detalletriage?.triages?.[0]?.estado === 'grave'">
                                    GRAVE
                                </span>
                                <span class="fw-bold mt-2 badge bg-warning"
                                    v-else-if="detalletriage?.triages?.[0]?.estado === 'moderado'">
                                    MODERADO
                                </span>
                                <span class="fw-bold mt-2 badge bg-success"
                                    v-else>
                                    LEVE
                                </span>               
                            </h6>
                        </div>
                    </div>

                    <!-- PRESIÓN -->

                    <div class="col-md-4 mb-4">
                        <div class="bg-white rounded-4 shadow-sm p-4 h-100">
                            <small class="text-muted">
                                <i class="fas fa-stethoscope me-2 text-dark"></i>
                                Presión
                            </small>
                            <h6 class="fw-bold mt-2">
                               {{ detalletriage?.triages?.[0]?.presion }}
                            </h6>
                        </div>
                    </div>

                    <!-- SATURACIÓN -->

                    <div class="col-md-4 mb-4">
                        <div class="bg-white rounded-4 shadow-sm p-4 h-100">
                            <small class="text-muted">
                                <i class="fas fa-lungs me-2 text-info"></i>
                                Saturación
                            </small>
                            <h6 class="fw-bold mt-2">
                               {{ detalletriage?.triages?.[0]?.saturacion }}
                            </h6>
                        </div>
                    </div>

                    <!-- TEMPERATURA -->

                    <div class="col-md-6 mb-4">
                        <div class="bg-white rounded-4 shadow-sm p-4 h-100">
                            <small class="text-muted">
                                <i class="fas fa-thermometer-half me-2 text-warning"></i>
                                Temperatura
                            </small>
                            <h6 class="fw-bold mt-2 text-warning">
                               {{ detalletriage?.triages?.[0]?.temperatura }}
                            </h6>

                        </div>

                    </div>

                    <!-- TIEMPO -->

                    <div class="col-md-6 mb-4">

                        <div class="bg-white rounded-4 shadow-sm p-4 h-100">

                            <small class="text-muted">
                                <i class="fas fa-clock me-2 text-secondary"></i>
                                Tiempo de Atención
                            </small>

                            <h6 class="fw-bold mt-2 text-info">
                                {{ obtenerTiempoEspera(detalletriage?.triages?.[0]?.created_at) }}
                            </h6>

                        </div>

                    </div>

                     <!-- SÍNTOMAS -->

                    <div class="col-12">

                        <div class="bg-white rounded-4 shadow-sm p-4">

                            <small class="text-muted">
                                <i class="fas fa-notes-medical me-2 text-primary"></i>
                                Síntomas Detectados
                            </small>

                            <p
                                class="mt-3 mb-0"
                                style="
                                    line-height:1.8;
                                    font-size:16px;
                                ">
                                {{ detalletriage?.triages?.[0]?.sintomas }}
                            </p>
                        </div>
                    </div>
                </div>  <!-- FIN row -->
            </div>
     

                    <!-- FOOTER -->

                    <div class="modal-footer bg-white border-0 px-5 pb-4">
                        <button
                            type="button"
                            class="btn btn-secondary rounded-pill px-4"
                            data-dismiss="modal"> Cerrar
                        </button>

                    </div>
        </div> <!-- FIN modal-content -->
    </div> 
</div> 
</template>


<script>
import ApiService from '../../services/ApiService.js'

export default {

    data() {

        return {

            triage: [],
            detalletriage: [],

        }

    },

    mounted() {

        this.obtenerPacientes()
        

    },

    methods: {
            //funcion para obtener datos del paciente
        async obtenerPacientes() {
            try {
                const response = await ApiService.get('/triage')
                this.triage = response.data
                console.log('triages:', this.triage)
            } catch (error) {
                console.error("error al cargar el triage:", error)
            } 

        },
        //Funcion para obtener prioridades
        obtenerPrioridad(estado) {
            switch (estado?.toLowerCase()) {
                case 'grave':
                    return {
                        texto: '🔴 Nivel 1 - Emergencia',
                        clase: 'bg-danger'
                    }
                case 'moderado':
                    return {
                        texto: '🟡 Nivel 2 - Urgente',
                        clase: 'bg-warning text-dark'
                    }
                case 'leve':
                    return {
                        texto: '🟢 Nivel 3 - No urgente',
                        clase: 'bg-success'
                    }
                default:
                    return {
                        texto: 'Sin clasificar',
                        clase: 'bg-secondary'
                    }
            }
        },
        //funcion para obtener el tiempo de acuerdo al estado
        obtenerTiempoEspera(fechaRegistro) {
            if (!fechaRegistro) return '0 min'
            const inicio = new Date(fechaRegistro)
            const ahora = new Date()
            const diferencia = ahora - inicio
            const horas = Math.floor(diferencia / 3600000)
            const minutos = Math.floor((diferencia % 3600000) / 60000)
            if (horas > 0) {
                return `${horas}h ${minutos}m`
            } 
            return `${minutos} min`
        },

        // VER INFORMACIÓN  DEL PACIENTE

       async obtenerPacientesIndividual(id) {
            try {
                const response = await ApiService.get('/triage/' + id)
                this.detalletriage = response.data
                console.log('triages:', this.detalletriage)
            } catch (error) {
                console.error("error al cargar el triage:", error)
            }

        },
       

        // GUARDAR CAMBIOS

        guardarEdicion() {
            console.log(this.triageEditar)
            this.modalEditarTriage = false
        }


        

    }

}

</script>

