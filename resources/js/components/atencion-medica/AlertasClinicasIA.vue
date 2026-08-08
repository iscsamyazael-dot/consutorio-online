<template>
    <div class="col-lg-6">
        <div class="card card-outline card-danger shadow">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    ⚠️ Alertas Clínicas IA
                </h3>
            </div>

            <div class="card-body">
                <div v-if="alertas.length === 0" class="text-muted text-center py-4 small">
                    Sin alertas clínicas detectadas por ahora. Seleccione un paciente.
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="(alerta, index) in alertas"
                        :key="index"
                        class="alert"
                        :class="claseAlerta(alerta.nivel)"
                    >
                        <h5 class="font-weight-bold mb-1">
                            {{ alerta.titulo }}
                        </h5>
                        
                        <p v-if="alerta.descripcion" class="mb-0 small">
                            {{ alerta.descripcion }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        iaData: {
            type: Object,
            default: null
        }
    },
    computed: {
        // Extraemos de forma segura el array de alertas clínicas desde la base de datos
        alertas() {
            return Array.isArray(this.iaData?.alertas) ? this.iaData.alertas : []
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
    margin-top: 1rem !important;
}
</style>