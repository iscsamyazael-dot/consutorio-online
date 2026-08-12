<template>
    <div class="col-lg-6">
        <div class="card card-outline card-success shadow">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title font-weight-bold m-0">
                    💡 Recomendaciones IA
                </h3>
                <span v-if="tieneDatos" class="badge bg-success">
                    {{ recomendaciones.length }}
                </span>
            </div>

            <div class="card-body">
                <!-- Sin datos -->
                <div v-if="!tieneDatos" class="text-muted text-center py-4 small">
                    Sin recomendaciones clínicas generadas por ahora. Seleccione un paciente.
                </div>

                <!-- Con datos -->
                <div v-else class="space-y-3">
                    
                    <!-- 1. Especialidad Sugerida (Destacada primero) -->
                    <div class="info-box shadow-sm mb-2">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-user-md"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text text-muted font-weight-bold">
                                Especialidad Sugerida
                            </span>
                            <span class="info-box-number font-weight-bold text-wrap text-uppercase text-dark" style="font-size: 1.1rem;">
                                {{ especialidadSugerida }}
                            </span>
                        </div>
                    </div>

                    <!-- 2. Lista de Recomendaciones Clínicas (Limitada) -->
                    <div 
                        v-for="(rec, index) in recomendacionesVisibles" 
                        :key="index"
                        class="info-box shadow-sm mb-0"
                    >
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-notes-medical"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text text-muted font-weight-bold">
                                {{ rec.titulo || `Recomendación #${index + 1}` }}
                            </span>
                            <span class="info-box-number font-weight-normal text-wrap text-secondary" style="font-size: 0.95rem;">
                                {{ obtenerTextoRecomendacion(rec) }}
                            </span>
                        </div>
                    </div>

                    <!-- 3. Botón "Ver más" / "Ver menos" si hay más de 2 recomendaciones -->
                    <div v-if="recomendaciones.length > limiteInicial" class="text-center pt-2">
                        <button 
                            type="button" 
                            class="btn btn-sm btn-outline-secondary rounded-pill px-3 font-weight-bold"
                            style="border-radius: 50px !important;"
                            @click="mostrarTodas = !mostrarTodas"
                        >
                            <i class="fas me-1" :class="mostrarTodas ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                            {{ mostrarTodas ? 'Ver menos' : `Ver más (${recomendaciones.length - limiteInicial} más)` }}
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'RecomendacionesIA',
    props: {
        iaData: {
            type: Object,
            default: null
        }
    },
    data() {
        return {
            limiteInicial: 2, // Muestra 2 recomendaciones iniciales + la especialidad
            mostrarTodas: false
        }
    },
    computed: {
        // Normaliza el array de recomendaciones desde la DB o prop
        recomendaciones() {
            const recs = this.iaData?.recomendaciones;
            if (Array.isArray(recs)) return recs;
            if (typeof recs === 'string') return [{ recomendacion: recs }];
            return [];
        },
        tieneDatos() {
            return this.recomendaciones.length > 0;
        },
        // Obtiene las recomendaciones limitadas para no descompensar la altura
        recomendacionesVisibles() {
            if (this.mostrarTodas) {
                return this.recomendaciones;
            }
            return this.recomendaciones.slice(0, this.limiteInicial);
        },
        // Extrae la especialidad guardada en observaciones
        especialidadSugerida() {
            if (this.tieneDatos) {
                const primera = this.recomendaciones[0];
                return primera.observaciones || primera.especialidad || 'Medicina General';
            }
            return 'Medicina General';
        }
    },
    methods: {
        // Soporta que el objeto traiga la propiedad .recomendacion, .descripcion o un string directo
        obtenerTextoRecomendacion(rec) {
            if (typeof rec === 'string') return rec;
            return rec.recomendacion || rec.descripcion || rec.detalle || 'Sin detalle';
        }
    }
}
</script>

<style scoped>
.space-y-3 > * + * {
    margin-top: 0.75rem !important;
}

.text-wrap {
    white-space: normal !important;
    word-break: break-word;
}
</style>