<template>
    <div class="col-lg-6">
        <div class="card card-outline card-success shadow">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    Recomendaciones IA
                </h3>
            </div>

            <div class="card-body">
                <div v-if="!tieneDatos" class="text-muted text-center py-4 small">
                    Sin recomendaciones clínicas generadas por ahora. Seleccione un paciente.
                </div>

                <div v-else>
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-primary">
                            <i class="fas fa-robot"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">
                                Recomendación
                            </span>
                            <span class="info-box-number font-weight-normal text-wrap">
                                {{ recomendacionPrincipal }}
                            </span>
                        </div>
                    </div>

                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-user-md"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">
                                Especialidad sugerida
                            </span>
                            <span class="info-box-number font-weight-bold text-wrap text-uppercase">
                                {{ especialidadSugerida }}
                            </span>
                        </div>
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
        // Valida si el objeto tiene recomendaciones válidas dentro de la DB
        tieneDatos() {
            const recs = this.iaData?.recomendaciones;
            return Array.isArray(recs) && recs.length > 0;
        },
        // Extrae el texto principal de la recomendación
        recomendacionPrincipal() {
            if (this.tieneDatos) {
                return this.iaData.recomendaciones[0].recomendacion;
            }
            return '';
        },
        // Extrae la especialidad guardada en observaciones (ej: Cardiología, Pediatría)
        especialidadSugerida() {
            if (this.tieneDatos) {
                return this.iaData.recomendaciones[0].observaciones || 'Medicina General';
            }
            return '';
        }
    }
}
</script>

<style scoped>
/* Un pequeño ajuste de estilo por si la recomendación es larga, no se corte el texto */
.text-wrap {
    white-space: normal !important;
}
</style>