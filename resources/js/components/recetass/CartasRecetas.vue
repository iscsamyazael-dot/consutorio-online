<template>

     <!-- CARDS -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1">
                                Recetas Activas
                            </p>
                            <h2 class="fw-bold text-success">
                                {{ recetasActivasHoy }}
                            </h2>
                        </div>
                        <div class="icon-circle bg-success">
                            <i class="fas fa-file-medical text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1">
                                Recetas Finalizadas
                            </p>
                            <h2 class="fw-bold text-primary">
                                {{ recetasFinalizadas }}
                            </h2>
                        </div>
                        <div class="icon-circle bg-primary">

                            <i class="fas fa-check-circle text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
// AJUSTA esta ruta según dónde quede finalmente este archivo dentro de
// resources/js (mismo patrón que usa ExpedienteTabs.vue / HistorialRecetas.vue).
import ApiService from '../../services/ApiService.js'

export default {
    name: 'RecetasResumenCards',
    data() {
        return {
            recetas: []
        }
    },
    mounted() {
        this.obtenerRecetas()
    },
    computed: {
        // Cuenta las recetas creadas EL DÍA DE HOY. Al ser un computed
        // sobre la fecha actual + los datos ya cargados, se recalcula
        // solo cada vez que se recarga la vista (mañana, sin recetas
        // nuevas, dará 0 automáticamente).
        recetasActivasHoy() {
            const hoy = new Date().toISOString().slice(0, 10) // YYYY-MM-DD

            return this.recetas.filter(r => {
                const fecha = r.fecha || r.created_at
                return fecha && fecha.slice(0, 10) === hoy
            }).length
        },

        // 'borrador' es el estado real que la BD usa para una receta ya
        // generada / finalizada (ver HistorialRecetas.vue).
        recetasFinalizadas() {
            return this.recetas.filter(r => (r.estado || '').toLowerCase() === 'borrador').length
        }
    },
    methods: {
        async obtenerRecetas() {
            try {
                const response = await ApiService.get('/recetas')
                this.recetas = Array.isArray(response.data) ? response.data : []
            } catch (error) {
                console.error('Error al obtener recetas para las tarjetas:', error)
            }
        }
    }
}
</script>

<style scoped>

.icon-circle{

    width: 55px;
    height: 55px;

    border-radius: 50%;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 22px;
}

</style>