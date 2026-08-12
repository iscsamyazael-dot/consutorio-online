<template>
    <div class="stats">

        <!-- ==========================================
             CLIENTES REGISTRADOS
        =========================================== -->
        <div
            class="stat-card stat-card-primary"
            style="--stat-color: var(--teal); --chip-bg: var(--teal-light); --chip-fg: var(--teal-dark);"
        >

            <div class="chip">

                <svg
                    width="21"
                    height="21"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M3 21h18" />
                    <path d="M6 21V7l6-4 6 4v14" />
                    <path d="M9 21v-6h6v6" />
                    <path d="M9 10h.01" />
                    <path d="M15 10h.01" />
                </svg>

            </div>

            <div class="stat-content">

                <div class="stat-label">
                    Clientes registrados
                </div>

                <div class="stat-number">
                    {{ total }}
                </div>

                <div class="stat-description">
                    Consultorios en la plataforma
                </div>

            </div>

        </div>


        <!-- ==========================================
             ACTIVOS
        =========================================== -->
        <div
            class="stat-card stat-card-active"
            style="--stat-color: var(--teal); --chip-bg: var(--teal-light); --chip-fg: var(--teal-dark);"
        >

            <div class="chip">

                <svg
                    width="21"
                    height="21"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.2"
                >
                    <path d="M20 6L9 17l-5-5" />
                </svg>

            </div>

            <div class="stat-content">

                <div class="stat-label">
                    Activos
                </div>

                <div class="stat-number">
                    {{ activos }}
                </div>

                <div class="stat-description stat-success">
                    Consultorios operativos
                </div>

            </div>

        </div>


        <!-- ==========================================
             INACTIVOS
        =========================================== -->
        <div
            class="stat-card stat-card-inactive"
            style="--stat-color: var(--coral); --chip-bg: var(--coral-light); --chip-fg: var(--coral-dark);"
        >

            <div class="chip">

                <svg
                    width="21"
                    height="21"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <circle
                        cx="12"
                        cy="12"
                        r="9"
                    />

                    <path d="M12 7v5l3 3" />
                </svg>

            </div>

            <div class="stat-content">

                <div class="stat-label">
                    Inactivos
                </div>

                <div class="stat-number">
                    {{ suspendidos }}
                </div>

                <div class="stat-description stat-danger">
                    Requieren atención
                </div>

            </div>

        </div>

    </div>
</template>


<script>
import ApiService from '../../services/ApiService.js'
export default {

    name: 'StatsCounters',

    props: {

        total: {
            type: Number,
            default: 0
        },

        activos: {
            type: Number,
            default: 0
        },

        suspendido: {
            type: Number,
            default: 0
        }

    },

      data() {
        return {
            total:0,
            activos:0,
            suspendidos:0
        }
    },

     mounted(){
        this.cargarEstadisticas();
    },

    methods:{
        cargarEstadisticas(){
            this.obtenerTotalClientes();
            this.obtenerTotalActivos();
            this.obtenerTotalSuspendidos();
        },
        async obtenerTotalClientes() {
            try {
                const response = await ApiService.get('TotalInquilinos');
                // Asignas directamente la lista que te da el servidor
                this.total = response.data;
                console.log('Total de clientes:', this.total);
            } catch (error) {
                console.error('Error al obtener el de clientes:', error);
            }
        },
         async obtenerTotalActivos() {
            try {
                const response = await ApiService.get('InquilinosActivos');
                // Asignas directamente la lista que te da el servidor
                this.activos = response.data;
                console.log('Clientes Activos:', this.activos);
            } catch (error) {
                console.error('Error al obtener lista de clientes activos:', error);
            }
        },
         async obtenerTotalSuspendidos() {
            try {
                const response = await ApiService.get('InquilinosSuspendidos');
                // Asignas directamente la lista que te da el servidor
                this.suspendidos = response.data;
                console.log('Clientes Suspendidos:', this.suspendidos);
            } catch (error) {
                console.error('Error al obtener lista de clientes suspendidos:', error);
            }
        },
    }

}
</script>