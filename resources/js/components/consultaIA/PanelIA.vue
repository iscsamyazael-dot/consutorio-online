<template>
    <!-- PANEL IA -->
    <div class="card bg-dark">
        <div class="card-header border-0">
            <h3 class="card-title">
                <i class="fas fa-robot"></i>
                Asistente Clínico IA
            </h3>
        </div>
        <div class="card-body">

            <!-- AVISO DE ERROR -->
            <div
                v-if="hasError"
                class="alert alert-danger py-2 px-3 mb-3"
                style="font-size:13px;"
            >
                ⚠️ No se pudo obtener el análisis de la IA. Mostrando último dato disponible.
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h5>{{ diagnosticoPrincipal }}</h5>
                            <p>{{ compatibilidad }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-brain"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="small-box" :class="claseAlerta">
                        <div class="inner">
                            <h5>{{ alertaPrincipal }}</h5>
                            <p>{{ descripcionAlerta }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <h6 class="text-light">
                    Recomendaciones IA
                </h6>
                <ul class="list-group">
                    <li
                        class="list-group-item"
                        v-for="(rec, index) in recomendaciones"
                        :key="rec + '-' + index"
                    >
                        {{ rec }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        // Recibimos el objeto completo que viene de la IA
        iaData: {
            type: Object,
            default: null
        },
        // Indica si la última consulta a la IA falló (para no confundir
        // "esperando datos" con "hubo un error")
        hasError: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        // Si no hay datos, o vienen incompletos, mostramos estados por defecto
        diagnosticoPrincipal() {
            return this.iaData?.diagnostico_probable ?? 'Sin Diagnóstico';
        },
        compatibilidad() {
            if (this.hasError) return 'Último análisis disponible'
            return this.iaData ? 'Análisis realizado' : 'Esperando datos clínicos';
        },
        nivelRiesgo() {
            return this.iaData?.nivel_riesgo
                ? String(this.iaData.nivel_riesgo).toLowerCase()
                : null;
        },
        alertaPrincipal() {
            return this.iaData?.nivel_riesgo
                ? String(this.iaData.nivel_riesgo).toUpperCase()
                : 'Sin alertas';
        },
        descripcionAlerta() {
            return this.iaData?.nivel_riesgo
                ? `Nivel de riesgo: ${this.iaData.nivel_riesgo}`
                : 'Paciente estable';
        },
        claseAlerta() {
            switch (this.nivelRiesgo) {
                case 'alto':
                case 'crítico':
                case 'critico':
                    return 'bg-danger'
                case 'medio':
                case 'moderado':
                    return 'bg-warning'
                case 'bajo':
                    return 'bg-success'
                default:
                    return 'bg-secondary'
            }
        },
        recomendaciones() {
            return Array.isArray(this.iaData?.recomendaciones) && this.iaData.recomendaciones.length > 0
                ? this.iaData.recomendaciones
                : ['Esperando síntomas clínicos'];
        }
    }
}
</script>