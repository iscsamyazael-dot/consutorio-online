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

                <!-- AVISO: la especialidad ideal no está en el catálogo del consultorio.
                     Se muestra ANTES de la sugerencia normal para que quede claro que lo
                     que sigue es un plan B, no la especialidad médicamente ideal. -->
                <div
                    v-if="especialidadFueraCatalogo && especialidadIdealNoDisponible"
                    class="alert alert-secondary py-1 px-2 small mb-2"
                >
                    ℹ️ No se cuenta con la especialidad de
                    <strong>{{ especialidadIdealNoDisponible }}</strong>
                    en el catálogo de este consultorio.
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
                    <span
                        v-if="especialidadFueraCatalogo"
                        class="badge badge-secondary ml-1"
                    >
                        fuera del catálogo ideal
                    </span>
                    <div v-if="motivoDerivacionIA" class="mt-1">
                        {{ motivoDerivacionIA }}
                    </div>
                </div>

            </template>

            <select
                class="form-control"
                v-model="especialidadSeleccionada"
                :disabled="guardando"
            >
                <option
                    v-for="esp in especialidades"
                    :key="esp.id"
                    :value="esp.id"
                >
                    {{ esp.nombre }}
                </option>
            </select>

            <div
                v-if="errorGuardado"
                class="alert alert-danger py-1 px-2 small mt-2 mb-0"
            >
                ⚠️ No se pudo guardar la derivación. Intenta nuevamente.
            </div>

            <div
                v-if="derivacionGuardada"
                class="alert alert-success py-1 px-2 small mt-2 mb-0"
            >
                ✅ Derivación guardada correctamente.
            </div>

            <button
                class="btn btn-warning btn-block mt-3"
                :disabled="!especialidadSeleccionada || guardando || derivacionGuardada"
                @click="derivarPaciente"
            >
                <i v-if="guardando" class="fas fa-spinner fa-spin mr-1"></i>
                {{ guardando ? 'Guardando...' : 'Derivar paciente' }}
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
        },
        // id de la consulta actual (Consulta.id); requerido para armar
        // la URL de guardado: consultaIA/{consultaId}/derivar
        consultaId: {
            type: [Number, String],
            required: true
        },
        // hospital al que se deriva (opcional, si ya lo manejas en otra parte del flujo)
        hospital: {
            type: String,
            default: null
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
            // Nuevo: cuando la IA no encontró en el catálogo la especialidad
            // médicamente ideal y tuvo que sugerir la mejor alternativa
            // disponible (ver IAClinicaService::sugerirMedicamentoLibre,
            // campos especialidad_fuera_catalogo / especialidad_ideal_no_disponible).
            especialidadFueraCatalogo: false,
            especialidadIdealNoDisponible: null,
            cargando: false,
            error: false,

            // estado del guardado
            guardando: false,
            errorGuardado: false,
            derivacionGuardada: false
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
        },

        // Arma el texto que se guarda en el campo "motivo"
        motivoParaGuardar() {
            const partes = []

            if (this.triage && this.triage.nivel) {
                partes.push(`Triage: ${this.triage.nivel}.`)
            }
            if (this.triage && this.triage.justificacion) {
                partes.push(this.triage.justificacion)
            }
            if (this.diagnosticosProbables.length > 0) {
                partes.push(`Diagnósticos probables (IA): ${this.diagnosticosProbables.join(', ')}.`)
            }
            if (this.especialidadFueraCatalogo && this.especialidadIdealNoDisponible) {
                partes.push(`No se contaba con la especialidad de ${this.especialidadIdealNoDisponible} en el catálogo del consultorio.`)
            }
            if (this.motivoDerivacionIA) {
                partes.push(this.motivoDerivacionIA)
            }

            return partes.join(' ')
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
                    this.especialidadFueraCatalogo = response.data.especialidad_fuera_catalogo || false
                    this.especialidadIdealNoDisponible = response.data.especialidad_ideal_no_disponible || null

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

        async derivarPaciente() {
            if (!this.especialidadSeleccionada || this.guardando) return

            this.guardando = true
            this.errorGuardado = false

            try {
                const urlDerivar = `${route}/consultaIA/${this.consultaId}/derivar`

                const response = await axios.post(urlDerivar, {
                    especialidad_id: this.especialidadSeleccionada,
                    hospital: this.hospital,
                    motivo: this.motivoParaGuardar
                })

                if (response.data.success) {
                    this.derivacionGuardada = true
                    this.$emit('derivar', response.data.derivacion)
                } else {
                    this.errorGuardado = true
                }

            } catch (err) {
                console.error('Error al guardar la derivación:', err)
                this.errorGuardado = true
            } finally {
                this.guardando = false
            }
        }
    }
}
</script>