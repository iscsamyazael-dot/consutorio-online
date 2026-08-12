<template>
    <div>

        <!-- ========================================= -->
        <!-- PANEL DE ARRIBA -->
        <!-- ========================================= -->
        <TriageClinico 
            :listaPacientes="triages"
            :conteo-criticos="conteoCriticos"
            :conteo-urgentes="conteoUrgentes"
            :conteo-moderados="conteoModerados"
            :conteo-atendidos="conteoAtendidos"
            :filtro-activo="filtroActivo"
            @cambiar-filtro="filtroActivo = $event"
        />
        

        <!-- ========================================= -->
        <!-- FILTROS DE FECHA -->
        <!-- ========================================= -->
        <FiltrosTriage 
            @filtrar="filtrarPorFecha"
        />

        <!-- ========================================= -->
        <!-- LISTA DE PACIENTES -->
        <!-- ========================================= -->
        <ListaPacientesTriage 
            :triages="triages"
            :filtro-activo="filtroActivo"
            @actualizar-conteos="actualizarTarjetas"
            :loading="loading"
        />

    </div>
</template>


<script>
import ApiService from '../../services/ApiService.js';

import TriageClinico from './TriageClinico.vue';
import FiltrosTriage from './FiltrosTriage.vue';
import ListaPacientesTriage from './ListaPacientesTriage.vue';

export default {
    name: 'ModuloTriage',

    components: {
        TriageClinico,
        FiltrosTriage,
        ListaPacientesTriage,
    },

    data() {
        return {
            triages: [],
            filtroActivo: null,
            loading: false,

            // Conteos
            conteoCriticos: 0,
            conteoUrgentes: 0,
            conteoModerados: 0,
            conteoAtendidos: 0
        };
    },

    async mounted() {
        // Carga inicial del día actual
        await this.filtrarPorFecha({
            fecha: this.obtenerFechaHoy()
        });
    },

    methods: {

        /**
         * ==========================================
         * FILTRAR TRIAGES POR FECHA
         * ==========================================
         */
        async filtrarPorFecha(payload = {}) {

            this.loading = true;

            try {

                const params = {};

                if (payload && payload.fecha) {
                    params.fecha = payload.fecha;
                }

                const response = await ApiService.get('/triage', {
                    params
                });

                this.triages = response.data.data || response.data;

                // Cuando cambia la fecha quitamos el filtro
                this.filtroActivo = null;

            } catch (error) {

                console.error(
                    'Error al filtrar triages:',
                    error
                );

                this.triages = [];

            } finally {

                this.loading = false;

            }
        },


        /**
         * ==========================================
         * FECHA ACTUAL
         * ==========================================
         */
        obtenerFechaHoy() {

            const fecha = new Date();

            const anio = fecha.getFullYear();

            const mes = String(
                fecha.getMonth() + 1
            ).padStart(2, '0');

            const dia = String(
                fecha.getDate()
            ).padStart(2, '0');

            return `${anio}-${mes}-${dia}`;
        },


        /**
         * ==========================================
         * ACTUALIZAR CONTADORES
         * ==========================================
         */
        actualizarTarjetas(conteos) {

            this.conteoCriticos =
                conteos.critico || 0;

            this.conteoUrgentes =
                conteos.urgente || 0;

            this.conteoModerados =
                conteos.leve || 0;

            this.conteoAtendidos =
                conteos.finalizado || 0;
        }

    }
};
</script>