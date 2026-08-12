<template>
    <div class="col-lg-6">
        <div class="card card-outline card-danger shadow">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title font-weight-bold m-0">
                    ⚠️ Alertas Clínicas IA
                </h3>
                <span v-if="alertas.length > 0" class="badge bg-danger">
                    {{ alertas.length }}
                </span>
            </div>

            <div class="card-body">
                <!-- Estado sin alertas -->
                <div v-if="alertas.length === 0" class="text-muted text-center py-4 small">
                    Sin alertas clínicas detectadas por ahora. Seleccione un paciente.
                </div>

                <!-- Lista de Alertas -->
                <div v-else class="space-y-3">
                    <div
                        v-for="(alerta, index) in alertasVisibles"
                        :key="index"
                        class="alert mb-0"
                        :class="claseAlerta(alerta.nivel)"
                    >
                        <!-- Título en negritas (Elemento Principal) -->
                        <h5 class="font-weight-bold mb-1" style="font-size: 1.05rem;">
                            {{ alerta.titulo }}
                        </h5>
                        
                        <!-- Descripción opcional por si trae detalle en la BD -->
                        <p v-if="alerta.descripcion" class="mb-0 small opacity-90">
                            {{ alerta.descripcion }}
                        </p>
                    </div>

                    <!-- Botón "Ver más" / "Ver menos" (Solo si supera el límite) -->
                    <div v-if="alertas.length > limiteInicial" class="text-center pt-2">
                        <button 
                            type="button" 
                            class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold"
                            style="border-radius: 50px !important;"
                            @click="mostrarTodas = !mostrarTodas"
                        >
                            <i class="fas me-1" :class="mostrarTodas ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                            {{ mostrarTodas ? 'Ver menos' : `Ver más (${alertas.length - limiteInicial} más)` }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AlertasClinicasIA',
    props: {
        iaData: {
            type: Object,
            default: null
        }
    },
    data() {
        return {
            limiteInicial: 3, // Cantidad de alertas visibles por defecto
            mostrarTodas: false
        }
    },
    computed: {
        // Extraemos de forma segura el array de alertas clínicas desde la base de datos
        alertas() {
            return Array.isArray(this.iaData?.alertas) ? this.iaData.alertas : []
        },
        // Filtra las alertas a mostrar según el estado de "mostrarTodas"
        alertasVisibles() {
            if (this.mostrarTodas) {
                return this.alertas
            }
            return this.alertas.slice(0, this.limiteInicial)
        }
    },
    methods: {
        // Asignación de la clase de alerta de Bootstrap según el nivel del ENUM de tu DB
        claseAlerta(nivel) {
            const nivelNormalizado = (nivel || '').toLowerCase().trim();

            switch (nivelNormalizado) {
                case 'critico':
                case 'alto':
                    // Rojo (Danger) para eventos críticos/altos
                    return 'alert-danger';
                case 'medio':
                    // Amarillo (Warning) para eventos de riesgo medio
                    return 'alert-warning';
                case 'bajo':
                default:
                    // Azul (Info) para alertas de bajo riesgo o informativas
                    return 'alert-info';
            }
        }
    }
}
</script>

<style scoped>
/* Espaciado elegante entre los bloques de alerta en AdminLTE */
.space-y-3 > * + * {
    margin-top: 0.75rem !important;
}

.opacity-90 {
    opacity: 0.9;
}
</style>