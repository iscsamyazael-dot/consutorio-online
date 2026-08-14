<template>
    <!-- ALERTAS -->
    <div class="card card-outline card-danger">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-exclamation-triangle"></i>
                Alertas Clínicas
            </h3>
        </div>
        <div class="card-body">

            <div
                v-if="alertas.length === 0"
                class="text-muted small"
            >
                Sin alertas clínicas detectadas por ahora.
            </div>

            <div
                v-for="(alerta, index) in alertas"
                :key="index"
                class="alert"
                :class="claseAlerta(alerta.nivel)"
            >
                <strong>{{ alerta.titulo }}</strong>
                <div
                    v-if="alerta.descripcion"
                    class="small"
                >
                    {{ alerta.descripcion }}
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
        alertas() {
            return Array.isArray(this.iaData?.alertas) ? this.iaData.alertas : []
        }
    },
    methods: {
        claseAlerta(nivel) {
            switch ((nivel || '').toLowerCase()) {
                case 'alto':
                    return 'alert-danger'
                case 'medio':
                    return 'alert-warning'
                case 'bajo':
                default:
                    return 'alert-info'
            }
        }
    }
}
</script>