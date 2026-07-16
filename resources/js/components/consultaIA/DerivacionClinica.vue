<template>
    <!-- DERIVACIÓN -->
    <div class="card card-warning card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-md"></i>
                Derivación
            </h3>
        </div>
        <div class="card-body">

            <div
                v-if="cargando"
                class="text-muted small mb-2"
            >
                <i class="fas fa-spinner fa-spin"></i> Analizando especialidad sugerida...
            </div>

            <div
                v-else-if="error"
                class="alert alert-danger py-1 px-2 small mb-2"
            >
                ⚠️ No se pudo determinar la especialidad sugerida.
            </div>

            <template v-else>

                <!-- ALERTA DE URGENCIA -->
                <div
                    v-if="requiereUrgencias"
                    class="alert alert-danger py-1 px-2 small mb-2"
                >
                    🚨 La IA marcó este caso como que <strong>requiere atención urgente</strong>.
                </div>

                <!-- BADGE DE TRIAGE -->
                <div v-if="triage && triage.nivel" class="mb-2">
                    <span
                        class="badge"
                        :class="badgeClaseTriage"
                    >
                        Triage: {{ triage.nivel }}
                    </span>
                    <div v-if="triage.justificacion" class="small text-muted mt-1">
                        {{ triage.justificacion }}
                    </div>
                </div>

                <!-- DIAGNÓSTICOS PROBABLES -->
                <div v-if="diagnosticosProbables.length > 0" class="small mb-2">
                    <strong>Diagnósticos probables (IA):</strong>
                    <ul class="mb-0 pl-3">
                        <li v-for="(dx, idx) in diagnosticosProbables" :key="idx">{{ dx }}</li>
                    </ul>
                </div>

                <!-- SUGERENCIA DE ESPECIALIDAD -->
                <div
                    v-if="especialidadSugerida"
                    class="alert alert-warning py-1 px-2 small mb-2"
                >
                    🤖 Sugerida por IA: <strong>{{ especialidadSugerida.nombre }}</strong>
                    <span
                        class="badge ml-1"
                        :class="fuente === 'ia_triage' ? 'badge-info' : 'badge-secondary'"
                    >
                        {{ fuente === 'ia_triage' ? 'triage IA' : 'respaldo por palabras clave' }}
                    </span>
                    <div v-if="motivoDerivacionIA" class="mt-1">
                        {{ motivoDerivacionIA }}
                    </div>
                </div>

            </template>

            <select
                class="form-control"
                v-model="especialidadSeleccionada"
            >
                <option
                    v-for="esp in especialidades"
                    :key="esp.id"
                    :value="esp.id"
                >
                    {{ esp.nombre }}
                </option>
            </select>

            <button
                class="btn btn-warning btn-block mt-3"
                :disabled="!especialidadSeleccionada"
                @click="derivarPaciente"
            >
                Derivar paciente
            </button>

        </div>
    </div>
</template>

<script>
import axios from 'axios'

var route = document.querySelector("[name=route]").value
var urlDerivacionInteligente = route + '/derivacionInteligente'

export default {
    props: {
        sintomas: {
            type: Array,
            default: () => []
        }
    },

    data() {
        return {
            especialidades: [],
            especialidadSugerida: null,
            especialidadSeleccionada: null,
            fuente: null,                  // 'ia_triage' | 'mapa_respaldo'
            triage: null,
            diagnosticosProbables: [],
            motivoDerivacionIA: null,
            requiereUrgencias: false,
            cargando: false,
            error: false
        }
    },

    computed: {
        badgeClaseTriage() {
            const nivel = (this.triage && this.triage.nivel) || ''
            switch (nivel.toUpperCase()) {
                case 'ROJO': return 'badge-danger'
                case 'NARANJA': return 'badge-warning'
                case 'AMARILLO': return 'badge-warning'
                case 'VERDE': return 'badge-success'
                default: return 'badge-secondary'
            }
        }
    },

    watch: {
        sintomas: {
            handler(nuevosSintomas) {
                if (nuevosSintomas.length > 0) {
                    this.buscarEspecialidad(nuevosSintomas)
                }
            },
            deep: true
        }
    },

    methods: {
        async buscarEspecialidad(sintomas) {
            this.cargando = true
            this.error = false

            try {
                const response = await axios.post(urlDerivacionInteligente, {
                    sintomas: sintomas
                })

                if (response.data.success) {
                    this.especialidades = response.data.especialidades || []
                    this.especialidadSugerida = response.data.especialidad_sugerida || null
                    this.fuente = response.data.fuente || null
                    this.triage = response.data.triage || null
                    this.diagnosticosProbables = response.data.diagnosticos_probables || []
                    this.motivoDerivacionIA = response.data.motivo_derivacion_ia || null
                    this.requiereUrgencias = response.data.requiere_urgencias || false

                    // Preseleccionamos la sugerida, si existe en la lista
                    if (this.especialidadSugerida) {
                        this.especialidadSeleccionada = this.especialidadSugerida.id
                    }
                } else {
                    this.error = true
                }

            } catch (err) {
                console.error('Error al consultar derivación inteligente:', err)
                this.error = true
            } finally {
                this.cargando = false
            }
        },

        derivarPaciente() {
            if (!this.especialidadSeleccionada) return

            const especialidad = this.especialidades.find(
                e => e.id === this.especialidadSeleccionada
            )

            // Por ahora solo emitimos el evento; falta conectar
            // al endpoint real de derivación/citas cuando lo tengas definido
            this.$emit('derivar', especialidad)
        }
    }
}
</script>