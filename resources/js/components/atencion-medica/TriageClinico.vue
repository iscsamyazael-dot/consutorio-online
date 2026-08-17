<template>

    <div>

        <!-- ========================================= -->
        <!-- TITULO -->
        <!-- ========================================= -->
        <div class="mb-3">

            <h1 class="font-weight-bold text-dark">
                TRIAGE Clínico
            </h1>

            <small class="text-muted">
                Clasificación médica de urgencias
            </small>

        </div>


        <!-- ========================================= -->
        <!-- TARJETAS -->
        <!-- ========================================= -->
        <div class="row mb-4">

            <!-- ===================================== -->
            <!-- 🔴 CRÍTICOS -->
            <!-- grave -->
            <!-- ===================================== -->
            <div class="col-lg-3 col-md-6 col-sm-12">

                <div
                    class="small-box bg-danger shadow cursor-pointer transition-all"
                    :class="{
                        'box-activa':
                            filtroEstado === 'critico'
                    }"
                    @click="filtrarPorEstado('critico')"
                >

                    <div class="inner">

                        <h3>
                            {{ conteoCriticos }}
                        </h3>

                        <p>
                            Críticos
                        </p>

                    </div>

                    <div class="icon">

                        <i class="fas fa-heartbeat"></i>

                    </div>

                    <a
                        href="javascript:void(0)"
                        class="small-box-footer"
                    >

                        {{
                            filtroEstado === 'critico'
                                ? 'Mostrando graves'
                                : 'Filtrar pacientes'
                        }}

                        <i
                            class="fas"
                            :class="
                                filtroEstado === 'critico'
                                    ? 'fa-check-circle'
                                    : 'fa-arrow-circle-right'
                            "
                        ></i>

                    </a>

                </div>

            </div>


            <!-- ===================================== -->
            <!-- 🟠 URGENTES -->
            <!-- urgente -->
            <!-- ===================================== -->
            <div class="col-lg-3 col-md-6 col-sm-12">

                <div
                    class="small-box bg-warning shadow cursor-pointer transition-all"
                    :class="{
                        'box-activa':
                            filtroEstado === 'urgente'
                    }"
                    @click="filtrarPorEstado('urgente')"
                >

                    <div class="inner">

                        <h3>
                            {{ conteoUrgentes }}
                        </h3>

                        <p>
                            Urgentes
                        </p>

                    </div>

                    <div class="icon">

                        <i class="fas fa-exclamation-triangle"></i>

                    </div>

                    <a
                        href="javascript:void(0)"
                        class="small-box-footer"
                    >

                        {{
                            filtroEstado === 'urgente'
                                ? 'Mostrando urgentes'
                                : 'Filtrar pacientes'
                        }}

                        <i
                            class="fas"
                            :class="
                                filtroEstado === 'urgente'
                                    ? 'fa-check-circle'
                                    : 'fa-arrow-circle-right'
                            "
                        ></i>

                    </a>

                </div>

            </div>


            <!-- ===================================== -->
            <!-- 🔵 MODERADOS -->
            <!-- leve -->
            <!-- ===================================== -->
            <div class="col-lg-3 col-md-6 col-sm-12">

                <div
                    class="small-box bg-info shadow cursor-pointer transition-all"
                    :class="{
                        'box-activa':
                            filtroEstado === 'moderado'
                    }"
                    @click="filtrarPorEstado('moderado')"
                >

                    <div class="inner">

                        <h3>
                            {{ conteoModerados }}
                        </h3>

                        <p>
                            Moderados
                        </p>

                    </div>

                    <div class="icon">

                        <i class="fas fa-procedures"></i>

                    </div>

                    <a
                        href="javascript:void(0)"
                        class="small-box-footer"
                    >

                        {{
                            filtroEstado === 'moderado'
                                ? 'Mostrando leves'
                                : 'Filtrar pacientes'
                        }}

                        <i
                            class="fas"
                            :class="
                                filtroEstado === 'moderado'
                                    ? 'fa-check-circle'
                                    : 'fa-arrow-circle-right'
                            "
                        ></i>

                    </a>

                </div>

            </div>


            <!-- ===================================== -->
            <!-- 🟢 ATENDIDOS -->
            <!-- finalizado -->
            <!-- ===================================== -->
            <div class="col-lg-3 col-md-6 col-sm-12">

                <div
                    class="small-box bg-success shadow cursor-pointer transition-all"
                    :class="{
                        'box-activa':
                            filtroEstado === 'finalizado'
                    }"
                    @click="filtrarPorEstado('finalizado')"
                >

                    <div class="inner">

                        <h3>
                            {{ conteoAtendidos }}
                        </h3>

                        <p>
                            Atendidos
                        </p>

                    </div>

                    <div class="icon">

                        <i class="fas fa-check-circle"></i>

                    </div>

                    <a
                        href="javascript:void(0)"
                        class="small-box-footer"
                    >

                        {{
                            filtroEstado === 'finalizado'
                                ? 'Mostrando finalizados'
                                : 'Filtrar pacientes'
                        }}

                        <i
                            class="fas"
                            :class="
                                filtroEstado === 'finalizado'
                                    ? 'fa-check-circle'
                                    : 'fa-arrow-circle-right'
                            "
                        ></i>

                    </a>

                </div>

            </div>

        </div>


        <!-- ========================================= -->
        <!-- INDICADOR DEL FILTRO -->
        <!-- ========================================= -->
        <div
            v-if="filtroEstado"
            class="d-flex align-items-center justify-content-between mb-3 p-2 bg-white rounded shadow-sm border"
        >

            <span class="small font-weight-bold text-secondary">

                <i class="fas fa-filter text-primary me-1"></i>

                Filtrando por:

                <span
                    class="badge text-uppercase ms-1"
                    :class="claseBadgeFiltro"
                >
                    {{ textoFiltro }}
                </span>

            </span>


            <button
                class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                @click="limpiarFiltro"
            >

                <i class="fas fa-times me-1"></i>

                Mostrar todos

            </button>

        </div>

    </div>

</template>


<script>

export default {

    name: 'TriageClinico',

    props: {
        
        conteoCriticos:  { type: Number, default: 0 },
        conteoUrgentes:  { type: Number, default: 0 },
        conteoModerados: { type: Number, default: 0 },
        conteoAtendidos: { type: Number, default: 0 },
        filtroActivo:    { type: String, default: null },

        listaPacientes: {
            type: Array,
            default: () => []
        }
    


    },


    data() {

        return {

            filtroEstado: this.filtroActivo

        };

    },

        watch: {
            filtroActivo(val) {
                this.filtroEstado = val;
            }
        },


    computed: {
        /**
         * ==========================================
         * COLOR DEL BADGE
         * ==========================================
         */
        claseBadgeFiltro() {

            switch (this.filtroEstado) {

                case 'critico':
                    return 'bg-danger';

                case 'urgente':
                    return 'bg-warning text-dark';

                case 'moderado':
                    return 'bg-info';

                case 'finalizado':
                    return 'bg-success';

                default:
                    return 'bg-secondary';

            }

        },


        /**
         * ==========================================
         * TEXTO DEL FILTRO
         * ==========================================
         */
        textoFiltro() {

            switch (this.filtroEstado) {

                case 'critico':
                    return 'CRÍTICOS — GRAVE';

                case 'urgente':
                    return 'URGENTES — URGENTE';

                case 'moderado':
                    return 'MODERADOS — LEVE';

                case 'finalizado':
                    return 'ATENDIDOS — FINALIZADO';

                default:
                    return '';

            }

        }

    },


    methods: {

        /**
         * ==========================================
         * OBTENER CATEGORÍA DE LA TARJETA
         * ==========================================
         */
        obtenerCategoria(paciente) {

            const estadoConsulta = String(
                paciente.estado_consulta || ''
            )
                .toLowerCase()
                .trim();


            // ======================================
            // ATENDIDO
            // ======================================
            if (
                estadoConsulta === 'finalizado' ||
                estadoConsulta === 'finalizada' ||
                estadoConsulta === 'atendido'
            ) {

                return 'finalizado';

            }


            // ======================================
            // ESTADO DEL TRIAGE
            // ======================================
            const estado = String(
                paciente.estado ||
                paciente.nivel_triage ||
                paciente.triages?.[0]?.estado ||
                ''
            )
                .toLowerCase()
                .trim();


            // ======================================
            // 🔴 GRAVE → CRÍTICO
            // ======================================
            if (estado === 'grave') {

                return 'critico';

            }


            // ======================================
            // 🟠 URGENTE → URGENTE
            // ======================================
            if (estado === 'urgente') {

                return 'urgente';

            }


            // ======================================
            // 🔵 LEVE → MODERADO
            // ======================================
            if (estado === 'leve') {

                return 'moderado';

            }


            return null;

        },


        /**
         * ==========================================
         * ACTIVAR / DESACTIVAR FILTRO
         * ==========================================
         */
        filtrarPorEstado(estado) {

            if (this.filtroEstado === estado) {

                this.filtroEstado = null;

            } else {

                this.filtroEstado = estado;

            }

            this.$emit(
                'cambiar-filtro',
                this.filtroEstado
            );

        },


        /**
         * ==========================================
         * LIMPIAR FILTRO
         * ==========================================
         */
        limpiarFiltro() {

            this.filtroEstado = null;

            this.$emit(
                'cambiar-filtro',
                null
            );

        }

    }

};

</script>


<style scoped>

.cursor-pointer {
    cursor: pointer;
}


.transition-all {
    transition: all 0.2s ease-in-out;
}


.small-box:hover {

    transform: translateY(-3px);

    box-shadow:
        0 0.5rem 1rem
        rgba(0, 0, 0, 0.2) !important;

}


.small-box.box-activa {

    outline: 3px solid #333;

    outline-offset: 2px;

    transform: translateY(-2px);

}

</style>