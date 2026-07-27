<template>
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

            <div v-else-if="consultas.length === 0" class="text-muted text-center py-3">
                Este paciente aún no tiene consultas registradas.
            </div>

            <div v-else class="p-2">
                <div v-for="(consulta, idx) in consultas" :key="consulta.id" class="card mb-2 shadow-none border">

                    <div
                        class="card-header p-2"
                        style="cursor:pointer; font-size:13px;"
                        @click="toggle(idx)"
                    >
                        <strong>Consulta #{{ consulta.folio || consulta.id }}</strong>
                        <span class="text-muted ml-2">{{ formatearFecha(consulta.fecha || consulta.created_at) }}</span>
                        <i class="fas float-right" :class="abiertos[idx] ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </div>

                    <div v-show="abiertos[idx]" class="timeline p-2">

                        <div v-if="(!consulta.transcripciones || consulta.transcripciones.length === 0) && (!consulta.evaluaciones || consulta.evaluaciones.length === 0)"
                             class="text-muted text-center py-2" style="font-size:13px;">
                            Sin transcripciones en esta consulta.
                        </div>

                        <div v-if="(!consulta.transcripciones || consulta.transcripciones.length === 0) && consulta.evaluaciones && consulta.evaluaciones.length > 0"
                             class="p-1">
                            <div v-for="ev in consulta.evaluaciones" :key="ev.id" class="mb-2 pb-2 border-bottom" style="font-size:13px;">
                                <div><strong>🩺 Síntomas detectados:</strong> {{ ev.sintomas_detectados || 'No disponible' }}</div>
                                <div><strong>🤖 Diagnóstico probable:</strong> {{ ev.diagnostico_probable || 'No disponible' }}</div>
                            </div>
                        </div>

                        <div v-for="item in consulta.transcripciones" :key="item.id">
                            <i class="fas" :class="iconoTipo(item.tipo_usuario)"></i>

                            <div class="timeline-item">
                                <span class="time">
                                    <i class="far fa-clock"></i>
                                    {{ formatearFecha(item.created_at) }}
                                </span>

                                <h3 class="timeline-header" style="font-size:14px;">
                                    <span class="badge" :class="badgeTipo(item.tipo_usuario)">
                                        {{ etiquetaTipo(item.tipo_usuario) }}
                                    </span>
                                    <span v-if="item.mensaje">{{ item.mensaje }}</span>
                                    <span v-else class="text-muted font-italic">Sin transcripción de texto</span>
                                </h3>

                                <div v-if="item.observaciones_ia" class="timeline-body">
                                    🤖 <strong>Observación IA:</strong> {{ item.observaciones_ia }}
                                </div>
                            </div>
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
        pacienteId: {
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
            consultas: [],
            abiertos: {},
            cargando: false,
            error: false
        }
    },
    watch: {
        // Refresca cuando la IA responde algo nuevo en la consulta activa,
        // así la consulta en curso aparece/actualiza dentro del historial
        iaData() {
            if (this.pacienteId) {
                this.cargarHistorial(this.pacienteId, { mantenerAbiertos: true })
            }
        },
        pacienteId: {
            handler(nuevoId) {
                if (nuevoId) this.cargarHistorial(nuevoId)
            },
            immediate: true
        }
    },
    methods: {
        async cargarHistorial(pacienteId, opts = {}) {
            this.cargando = !opts.mantenerAbiertos
            this.error = false

            try {
                const response = await axios.get(urlHistorialClinico, {
                    params: { paciente_id: pacienteId }
                })

                this.consultas = response.data.success ? response.data.consultas : []

                if (!opts.mantenerAbiertos) {
                    // primera consulta (la más reciente) abierta por defecto
                    this.abiertos = { 0: true }
                }

            } catch (err) {
                console.error('Error al cargar historial clínico:', err)
                this.error = true
            } finally {
                this.cargando = false
            }
        },
        toggle(idx) {
            this.abiertos[idx] = !this.abiertos[idx]
        },
        formatearFecha(fecha) {
            if (!fecha) return ''
            const d = new Date(fecha)
            return d.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' })
        },
        etiquetaTipo(tipo) {
            const mapa = { medico: 'Médico', paciente: 'Paciente', ia: 'IA', sistema: 'Sistema' }
            return mapa[tipo] || 'Desconocido'
        },
        iconoTipo(tipo) {
            const mapa = {
                medico: 'fa-user-md bg-info', paciente: 'fa-user bg-success',
                ia: 'fa-robot bg-primary', sistema: 'fa-cog bg-secondary'
            }
            return mapa[tipo] || 'fa-comment bg-secondary'
        },
        badgeTipo(tipo) {
            const mapa = {
                medico: 'badge-info', paciente: 'badge-success',
                ia: 'badge-primary', sistema: 'badge-secondary'
            }
            return mapa[tipo] || 'badge-secondary'
        }
    }
}
</script>