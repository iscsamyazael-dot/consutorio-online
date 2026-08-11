<template>
    <div>
        <!-- ========================================= -->
        <!-- PANEL DE ARRIBA -->
        <!-- ========================================= -->
        <TriageClinico />

        <!-- ========================================= -->
        <!-- TABLA DE FILTROS -->
        <!-- ========================================= -->
        <FiltrosTriage @filtrar="filtrarPorFecha" />

        <!-- ========================================= -->
        <!-- PANEL CENTRAL (LISTA DE PACIENTES) -->
        <!-- ========================================= -->
        <ListaPacientesTriage 
            :triages="triages" 
            :loading="loading" 
        />

        <!-- ========================================= -->
        <!-- ALERTAS Y RECOMENDACIONES IA -->
        <!-- ========================================= -->
        <div class="row">
            <AlertasClinicasIA />
            <RecomendacionesIA />
        </div>
    </div>
</template>

<script>
import ApiService from '../../services/ApiService.js';

import TriageClinico from './TriageClinico.vue';
import FiltrosTriage from './FiltrosTriage.vue';
import ListaPacientesTriage from './ListaPacientesTriage.vue';
import AlertasClinicasIA from './AlertasClinicasIA.vue';
import RecomendacionesIA from './RecomendacionesIA.vue';

export default {
    name: 'ModuloTriage',
    components: {
        TriageClinico,
        FiltrosTriage,
        ListaPacientesTriage,
        AlertasClinicasIA,
        RecomendacionesIA
    },

    data() {
        return {
            triages: [],
            loading: false
        };
    },

    async mounted() {
        // Carga inicial filtrando por el día de hoy
        await this.filtrarPorFecha({ fecha: this.obtenerFechaHoy() });
    },

    methods: {
        /**
         * Filtra la lista de triages usando ApiService
         */
        async filtrarPorFecha(payload = {}) {
            this.loading = true;
            try {
                const params = {};
                if (payload && payload.fecha) {
                    params.fecha = payload.fecha;
                }
                
                const response = await ApiService.get('/triage', { params });
                this.triages = response.data.data || response.data;
            } catch (error) {
                console.error("Error al filtrar triages:", error);
                this.triages = [];
            } finally {
                this.loading = false;
            }
        },

        /**
         * Retorna la fecha de hoy en formato YYYY-MM-DD
         */
        obtenerFechaHoy() {
            const fecha = new Date();
            const anio = fecha.getFullYear();
            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
            const dia = String(fecha.getDate()).padStart(2, '0');
            return `${anio}-${mes}-${dia}`;
        }
    }
};
</script>