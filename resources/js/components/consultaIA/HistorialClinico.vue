<template>
    <!-- HISTORIAL -->
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history"></i>
                Historial Clínico
            </h3>
        </div>

        <div class="card-body p-0" style="height: 350px; overflow-y:auto;">

            <div v-if="cargando" class="text-center text-muted py-3">
                <i class="fas fa-spinner fa-spin"></i> Cargando historial...
            </div>

            <div v-else-if="error" class="alert alert-danger m-3 py-2 px-3">
                ⚠️ No se pudo cargar el historial clínico.
            </div>

            <div v-else-if="historial.length === 0" class="text-muted text-center py-3">
                Aún no hay intercambios registrados en esta consulta.
            </div>

            <div v-else class="timeline p-3">
                <div v-for="item in historial" :key="item.id">

                    <i
                        class="fas"
                        :class="item.analizado_ia ? 'fa-robot bg-primary' : 'fa-comment bg-secondary'"
                    ></i>

                    <div class="timeline-item">
                        <span class="time">
                            <i class="far fa-clock"></i>
                            {{ formatearFecha(item.created_at) }}
                        </span>

                        <h3 class="timeline-header">
                            Paciente: {{ item.mensaje }}
                        </h3>

                        <div
                            v-if="item.observaciones_ia"
                            class="timeline-body"
                        >
                            🤖 {{ item.observaciones_ia }}
                        </div>
                        <div
                            v-else
                            class="timeline-body text-muted"
                        >
                            Sin análisis de IA todavía.
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</template>

<script>
import axios from 'axios'

var route = document.querySelector("[name=route]").value
var urlHistorialClinico = route + '/historialClinico'

export default {
    props: {
        consultaId: {
            type: [Number, String],
            default: null
        },
        iaData: {
            type: Object,
            default: null
        }
    },
    data() {
        return {
            historial: [],
            cargando: false,
            error: false
        }
    },
    watch: {
        // Se recarga cada vez que hay una respuesta nueva de la IA
        iaData() {
            if (this.consultaId) {
                this.cargarHistorial(this.consultaId)
            }
        },
        consultaId: {
            handler(nuevoId) {
                if (nuevoId) {
                    this.cargarHistorial(nuevoId)
                }
            },
            immediate: true
        }
    },
    methods: {
        async cargarHistorial(consultaId) {
            this.cargando = true
            this.error = false

            try {
                const response = await axios.get(urlHistorialClinico, {
                    params: { consulta_id: consultaId }
                })

                this.historial = response.data.success ? response.data.historial : []

            } catch (err) {
                console.error('Error al cargar historial clínico:', err)
                this.error = true
            } finally {
                this.cargando = false
            }
        },
        formatearFecha(fecha) {
            if (!fecha) return ''
            const d = new Date(fecha)
            return d.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' })
        }
    }
}
</script>