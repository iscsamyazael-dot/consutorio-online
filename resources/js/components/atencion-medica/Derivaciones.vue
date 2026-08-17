<template>

    <DerivacionesClinicas
    @filtro-derivaciones="filtroDerivaciones"
    />

    <FiltrosDerivaciones
        :especialidades="especialidades"
        @filtrar="aplicarFiltros"
        @restablecer="cargarDerivacionesIniciales"
    />

    <ListaDerivaciones
            :derivaciones="derivaciones"
            :loading="loading" 
            :filtro-activo="filtroActivo"
    />


</template>



<script>
import ApiService from '../../services/ApiService.js'

import DerivacionesClinicas from './DerivacionesClinicas.vue'
import FiltrosDerivaciones from './FiltrosDerivaciones.vue'
import ListaDerivaciones from './ListaDerivaciones.vue'

export default {

    components: {
        DerivacionesClinicas,
        FiltrosDerivaciones,
        ListaDerivaciones
    },

    data() {
        return {
            filtroActivo: 'todas',
            especialidades: [],
            derivaciones: [],
            loading: false
            
        }
    },

    async mounted() {
        await this.cargarEspecialidades()
        await this.cargarDerivacionesHoy()
    },

    methods: {

        async cargarEspecialidades() {
            try {
                const response = await ApiService.get('/api/specialties')

                this.especialidades = response.data

            } catch (error) {
                console.error(
                    'Error al cargar especialidades:',
                    error
                )
            }
        },

        async cargarDerivacionesHoy() {

            this.loading = true

            try {

                const response = await ApiService.get(
                    '/derivaciones'
                )

                this.derivaciones = response.data

            } catch (error) {

                console.error(
                    'Error al cargar derivaciones:',
                    error
                )

            } finally {

                this.loading = false
            }
        },

        filtroDerivaciones(filtro) {
            this.filtroActivo = filtro
        },

        async cargarDerivacionesIniciales() {

            this.loading = true

            try {

                const response = await ApiService.get(
                    '/api/derivaciones'
                )

                this.derivaciones = response.data

            } catch (error) {

                console.error(
                    'Error al cargar derivaciones:',
                    error
                )

            } finally {

                this.loading = false
            }
        },

        async aplicarFiltros(filtros) {

            this.loading = true

            try {

                const params = {}

                if (filtros.fecha) {
                    params.fecha = filtros.fecha
                }

                if (filtros.especialidad_id) {
                    params.especialidad_id =
                        filtros.especialidad_id
                }

                const response = await ApiService.get(
                    '/derivaciones',
                    {
                        params
                    }
                )

                this.derivaciones = response.data

            } catch (error) {

                console.error(
                    'Error al filtrar derivaciones:',
                    error
                )

            } finally {

                this.loading = false
            }
        },

        obtenerFechaHoy() {

            const fecha = new Date()

            const anio = fecha.getFullYear()

            const mes = String(
                fecha.getMonth() + 1
            ).padStart(2, '0')

            const dia = String(
                fecha.getDate()
            ).padStart(2, '0')

            return `${anio}-${mes}-${dia}`
        }
    }
}
</script>