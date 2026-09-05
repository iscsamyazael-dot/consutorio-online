<template>
    <!-- RECETA -->
    <div class="card card-success card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-prescription"></i>
                Receta Inteligente
            </h3>
        </div>
        <div class="card-body">
            <!-- SUGERENCIAS IA (desde inventario) -->
            <div class="mb-3">
                <h6 class="text-success">
                    🤖 Sugerencias IA
                </h6>
                 <!-- NUEVO: botón para refrescar receta con el diagnóstico ya confirmado -->
            <button
                v-if="diagnosticoConfirmado"
                class="btn btn-outline-primary btn-sm mb-2"
                :disabled="cargandoSugerencias"
                @click="buscarSugerencias(sintomas)"
            >
                <i class="fas fa-sync" :class="{ 'fa-spin': cargandoSugerencias }"></i>
                Actualizar con diagnóstico confirmado
            </button>
            <div v-else class="small text-muted mb-2">
                <i class="fas fa-info-circle"></i>
                Confirma el diagnóstico en el panel de IA para anclar la receta a él.
            </div>

                <div v-if="cargandoSugerencias" class="text-muted small">
                    <i class="fas fa-spinner fa-spin"></i> Analizando triage clínico...
                </div>

                <div v-else-if="errorSugerencias" class="alert alert-danger py-1 px-2 small mb-2">
                    ⚠️ No se pudo consultar la base de medicamentos.
                </div>

                <div v-else-if="sintomas.length === 0" class="text-muted small">
                    Esperando síntomas clínicos...
                </div>

                <!-- CASO: la IA determinó que este caso requiere DERIVACIÓN, no receta -->
                <div v-else-if="tipoRespuesta === 'derivacion'">
                    <div class="alert alert-warning py-2 px-3 small mb-2">
                        ⚠️ Según el triage de la IA
                        <span v-if="triage && triage.nivel">
                            (nivel <strong>{{ triage.nivel }}</strong>)
                        </span>
                        este caso requiere <strong>derivación</strong>, no receta.
                        <div v-if="triage && triage.justificacion" class="mt-1">
                            {{ triage.justificacion }}
                        </div>
                        <div class="mt-1 font-italic">
                            Revisa el panel de Derivación para continuar con este flujo.
                        </div>
                    </div>
                </div>

                <!-- CASO: hay coincidencias reales en el inventario -->
                <div v-else-if="medicamentosSugeridos.length > 0">
                    <button
                        v-for="med in medicamentosSugeridos"
                        :key="med.id"
                        class="btn btn-outline-success btn-sm mr-2 mb-2"
                        :title="med.contraindicaciones ? 'Contraindicaciones: ' + med.contraindicaciones : ''"
                        @click="agregarMedicamentoIA(med)"
                    >
                        <i class="fas fa-plus"></i>
                        {{ med.nombre }}
                        <span v-if="med.requiere_receta" class="badge badge-warning ml-1">Rx</span>
                    </button>
                </div>

                <!-- CASO: no hay match en inventario -> sugerencias genéricas de la IA -->
                <div v-else-if="medicamentosSugeridosIA.length > 0">
                    <div class="alert alert-warning py-2 px-3 small mb-2">
                        ⚠️ <strong>No hay medicamentos en tu inventario</strong> asociados a estos síntomas.
                        La IA sugiere considerar las siguientes opciones generales:
                    </div>

                    <div
                        v-for="(med, idx) in medicamentosSugeridosIA"
                        :key="idx"
                        class="border rounded p-2 mb-2"
                    >
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ med.nombre }}</strong>
                                <span class="badge badge-secondary ml-1">no verificado</span>
                                <div class="small text-muted mt-1">{{ med.descripcion }}</div>
                            </div>
                            <button
                                class="btn btn-outline-secondary btn-sm ml-2"
                                @click="agregarMedicamentoIA(med)"
                            >
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="font-italic small text-muted">
                        Estas sugerencias NO fueron verificadas contra tu inventario ni contraindicaciones.
                        No incluyen dosis — decisión y prescripción exclusiva del médico.
                    </div>
                </div>

                <div v-else class="text-muted small">
                    No se encontraron medicamentos asociados a los síntomas actuales.
                </div>
            </div>
            <hr>

            <!-- BUSCADOR CIMA (AEMPS) -->
            <div class="mb-3">
                <h6 class="text-primary">
                    🔎 Buscar en base de medicamentos (CIMA - AEMPS)
                </h6>

                <input
                    type="text"
                    class="form-control form-control-sm mb-2"
                    v-model="cimaQuery"
                    placeholder="Ej. Paracetamol, Ibuprofeno..."
                    autocomplete="off"
                >

                <div v-if="cimaBuscando" class="text-muted small">
                    <i class="fas fa-spinner fa-spin"></i> Buscando en CIMA...
                </div>

                <div v-else-if="cimaError" class="alert alert-danger py-1 px-2 small mb-2">
                    ⚠️ {{ cimaError }}
                </div>

                <div v-else-if="cimaResultados.length > 0" class="list-group mb-2" style="max-height: 220px; overflow-y: auto;">
                    <button
                        v-for="r in cimaResultados"
                        :key="r.nregistro"
                        type="button"
                        class="list-group-item list-group-item-action py-1 px-2 small"
                        @click="verDetalleCima(r)"
                    >
                        {{ r.nombre }}
                        <span v-if="r.requiere_receta" class="badge badge-warning ml-1">Rx</span>
                        <span v-if="r.generico" class="badge badge-info ml-1">EFG</span>
                    </button>
                </div>

                <div v-else-if="cimaQuery.trim().length >= 3 && !cimaBuscando" class="text-muted small mb-2">
                    Sin resultados en CIMA para "{{ cimaQuery }}".
                </div>

                <!-- Ficha del medicamento seleccionado -->
                <div v-if="cimaCargandoDetalle" class="text-muted small">
                    <i class="fas fa-spinner fa-spin"></i> Cargando ficha...
                </div>

                <div v-else-if="cimaSeleccionado" class="border rounded p-2 mb-2 bg-light">
                    <div class="d-flex justify-content-between align-items-start">
                        <strong class="small">{{ cimaSeleccionado.nombre }}</strong>
                        <button class="btn btn-outline-secondary btn-sm py-0 px-1" @click="cimaSeleccionado = null">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div v-if="cimaSeleccionado.principios_activos && cimaSeleccionado.principios_activos.length" class="small mt-1">
                        <strong>Principio activo:</strong>
                        {{ textoPrincipiosActivos(cimaSeleccionado.principios_activos) }}
                    </div>

                    <div v-if="cimaSeleccionado.via_administracion && cimaSeleccionado.via_administracion.length" class="small">
                        <strong>Vía:</strong> {{ cimaSeleccionado.via_administracion.join(', ') }}
                    </div>

                    <!-- Posología (ficha técnica 4.2): mismo texto que se usará
                         para autocompletar "Indicaciones" y "Recomendación general"
                         al presionar "Agregar a la receta". Se muestra aquí para
                         que el médico lo vea antes de decidir. -->
                    <div v-if="cimaSeleccionado.posologia" class="small mt-1">
                        <strong>Posología (ficha técnica):</strong>
                        {{ recortarTexto(cimaSeleccionado.posologia, 300) }}
                    </div>

                    <div v-if="cimaSeleccionado.notas_seguridad && cimaSeleccionado.notas_seguridad.length" class="small text-danger mt-1">
                        ⚠️ Tiene notas de seguridad publicadas por la AEMPS — revisar ficha técnica.
                    </div>

                    <div class="mt-1" v-if="cimaSeleccionado.documentos && cimaSeleccionado.documentos.length">
                        <a
                            v-for="doc in cimaSeleccionado.documentos"
                            :key="doc.url"
                            :href="doc.url"
                            target="_blank"
                            rel="noopener"
                            class="badge badge-secondary mr-1"
                        >
                            {{ doc.tipo }}
                        </a>
                    </div>

                    <div class="small font-italic text-muted mt-1">
                        Contraindicaciones, interacciones y dosis exactas: consulta la ficha técnica antes de prescribir.
                    </div>

                    <button class="btn btn-outline-primary btn-sm mt-2" @click="agregarDesdeCimaAReceta">
                        <i class="fas fa-plus"></i> Agregar a la receta
                    </button>
                </div>
            </div>
            <hr>

            <!-- LISTA DE MEDICAMENTOS -->
            <div class="medicamentos-lista">
                <div
                    v-for="(med, index) in medicamentos"
                    :key="index"
                    class="medicamento-item border rounded p-2 mb-2"
                >
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small font-weight-bold text-muted">Medicamento {{ index + 1 }}</span>
                        <button class="btn btn-outline-danger btn-sm py-0 px-1" @click="eliminarMedicamento(index)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>

                    <div class="form-group mb-1">
                        <input type="text" class="form-control form-control-sm" v-model="med.nombre" placeholder="Nombre del medicamento">
                    </div>

                    <div class="row-2col mb-1">
                        <input type="text" class="form-control form-control-sm" v-model="med.dosis" placeholder="Dosis">
                        <input type="text" class="form-control form-control-sm" v-model="med.frecuencia" placeholder="Frecuencia">
                    </div>

                    <div class="row-2col mb-1">
                        <input type="text" class="form-control form-control-sm" v-model="med.duracion" placeholder="Duración">
                        <input type="text" class="form-control form-control-sm" v-model="med.instrucciones" placeholder="Indicaciones">
                    </div>
                </div>

                <div v-if="medicamentos.length === 0" class="text-muted small text-center py-2">
                    Aún no hay medicamentos agregados.
                </div>
            </div>

            <div class="form-group mt-2">
                <label class="small font-weight-bold mb-1">Recomendación general (cómo tomar los medicamentos)</label>
                <textarea
                    class="form-control form-control-sm"
                    rows="2"
                    v-model="recomendacionGeneral"
                    placeholder="Ej. Tomar los medicamentos con alimentos, evitar alcohol durante el tratamiento..."
                ></textarea>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm flex-fill" @click="agregarMedicamento">
                    <i class="fas fa-plus"></i>
                    Agregar medicamento
                </button>

                <button
                    class="btn btn-success btn-sm flex-fill"
                    :disabled="!puedeGuardar || guardando"
                    @click="guardarReceta"
                >
                    <i class="fas fa-spinner fa-spin" v-if="guardando"></i>
                    <i class="fas fa-save" v-else></i>
                    {{ guardando ? 'Guardando...' : 'Guardar receta' }}
                </button>
            </div>

            <div v-if="errorGuardar" class="alert alert-danger py-1 px-2 small mt-2 mb-0">
                ⚠️ {{ errorGuardar }}
            </div>

            <!-- TOAST -->
            <transition name="fade">
                <div v-if="toastMsg" class="receta-toast">{{ toastMsg }}</div>
            </transition>
        </div>
    </div>
</template>

<script>
import axios from 'axios'

var route = document.querySelector("[name=route]").value
var urlRecetaInteligente = route + '/recetaInteligente'
var urlCima = route + '/cima'

export default {
    props: {
        sintomas: {
            type: Array,
            default: () => []
        },
        consultaId: {
            type: [String, Number],
            default: null
        },
        diagnosticoConfirmado: { 
            type: String, 
            default: null 
        }
    },

    data() {
        return {
            medicamentos: [],
            recomendacionGeneral: '',
            medicamentosSugeridos: [],       
            medicamentosSugeridosIA: [],     
            tipoRespuesta: null,            
            triage: null,
            cargandoSugerencias: false,
            errorSugerencias: false,

            // Recomendación general sugerida por la IA (recetaInteligente()).
            // Se usa para autocompletar el textarea "Recomendación general"
            // solo si el médico aún no escribió nada ahí.
            recomendacionesGeneralesIA: '',

            // Buscador CIMA (AEMPS)
            cimaQuery: '',
            cimaResultados: [],
            cimaBuscando: false,
            cimaError: null,
            cimaSeleccionado: null,
            cimaCargandoDetalle: false,
            cimaDebounceTimer: null,

            guardando: false,
            errorGuardar: null,
            toastMsg: '',
            toastTimer: null
        }
    },

    computed: {
        puedeGuardar() {
            if (!this.consultaId) return false
            if (this.medicamentos.length === 0) return false
            return this.medicamentos.some(m => m.nombre && m.nombre.trim().length > 0)
        }
    },

    watch: {
        sintomas: {
            handler(nuevosSintomas) {
                if (nuevosSintomas.length > 0) {
                    this.buscarSugerencias(nuevosSintomas)
                } else {
                    this.resetSugerencias()
                }
            },
            deep: true
        },

        cimaQuery(nuevoValor) {
            clearTimeout(this.cimaDebounceTimer)
            this.cimaError = null

            const texto = nuevoValor.trim()

            if (texto.length < 3) {
                this.cimaResultados = []
                this.cimaBuscando = false
                return
            }

            // Debounce de 400ms: evita golpear el backend en cada tecla
            this.cimaDebounceTimer = setTimeout(() => {
                this.ejecutarBusquedaCima(texto)
            }, 400)
        }
    },

    methods: {
        resetSugerencias() {
            this.medicamentosSugeridos = []
            this.medicamentosSugeridosIA = []
            this.tipoRespuesta = null
            this.triage = null
            this.recomendacionesGeneralesIA = ''
        },

        async buscarSugerencias(sintomas) {
            this.cargandoSugerencias = true
            this.errorSugerencias = false

            try {
                const response = await axios.post(urlRecetaInteligente, {
                    sintomas: sintomas,
                    consulta_id: this.consultaId 
                })

                if (response.data.success) {
                    this.tipoRespuesta = response.data.tipo || null
                    this.triage = response.data.triage || null
                    this.medicamentosSugeridos = response.data.medicamentos || []
                    this.medicamentosSugeridosIA = response.data.medicamentos_sugeridos_ia || []
                    this.recomendacionesGeneralesIA = response.data.recomendaciones_generales || ''
                } else {
                    this.resetSugerencias()
                }

            } catch (error) {
                console.error('Error al consultar receta inteligente:', error)
                this.errorSugerencias = true
                this.resetSugerencias()
            } finally {
                this.cargandoSugerencias = false
            }
        },

        // ---- Buscador CIMA (AEMPS) ----

        async ejecutarBusquedaCima(texto) {
            this.cimaBuscando = true
            this.cimaError = null

            try {
                const response = await axios.get(`${urlCima}/buscar`, {
                    params: { q: texto }
                })

                this.cimaResultados = (response.data && response.data.resultados) || []

            } catch (error) {
                console.error('Error al buscar en CIMA:', error)
                this.cimaError = 'No se pudo consultar CIMA en este momento.'
                this.cimaResultados = []
            } finally {
                this.cimaBuscando = false
            }
        },

        async verDetalleCima(resultado) {
            this.cimaCargandoDetalle = true
            this.cimaSeleccionado = null
            this.cimaResultados = []
            this.cimaError = null

            try {
                const response = await axios.get(`${urlCima}/${resultado.nregistro}`)
                this.cimaSeleccionado = response.data

            } catch (error) {
                console.error('Error al obtener ficha CIMA:', error)
                this.cimaError = 'No se pudo cargar la ficha de este medicamento.'
            } finally {
                this.cimaCargandoDetalle = false
            }
        },

        textoPrincipiosActivos(principios) {
            return principios
                .map(p => [p.nombre, p.cantidad, p.unidad].filter(Boolean).join(' '))
                .join(', ')
        },

        // Recorta un texto largo (ej. la posología de la ficha técnica) a
        // maxLen caracteres, para que quepa razonablemente en "Indicaciones"
        // o en la vista previa de la ficha CIMA. El médico puede editarlo
        // o completarlo libremente después.
        recortarTexto(texto, maxLen) {
            if (!texto) return ''
            if (texto.length <= maxLen) return texto
            return texto.slice(0, maxLen).trim() + '…'
        },

        agregarDesdeCimaAReceta() {
            if (!this.cimaSeleccionado) return

            const existe = this.medicamentos.find(m => m.nombre === this.cimaSeleccionado.nombre)
            if (existe) {
                this.mostrarToast('Ese medicamento ya está en la receta')
                return
            }

            const dosisSugerida = this.cimaSeleccionado.principios_activos && this.cimaSeleccionado.principios_activos.length
                ? this.textoPrincipiosActivos(this.cimaSeleccionado.principios_activos)
                : ''

            // Indicaciones sugeridas: sección 4.2 (posología y forma de
            // administración) de la ficha técnica de CIMA. Queda editable.
            const indicacionesSugeridas = this.cimaSeleccionado.posologia
                ? this.recortarTexto(this.cimaSeleccionado.posologia, 300)
                : ''

            this.medicamentos.push({
                nombre: this.cimaSeleccionado.nombre,
                dosis: dosisSugerida,
                frecuencia: '',
                duracion: '',
                instrucciones: indicacionesSugeridas
            })

            // Solo autocompletamos la recomendación general si el médico
            // todavía no escribió nada ahí, para no pisarle el texto.
            if (!this.recomendacionGeneral.trim() && indicacionesSugeridas) {
                this.recomendacionGeneral = indicacionesSugeridas
            }

            this.mostrarToast('Medicamento agregado desde CIMA ✓')
            this.cimaSeleccionado = null
            this.cimaQuery = ''
        },

        // ---- Lista de medicamentos manual ----

        agregarMedicamento() {
            this.medicamentos.push({ nombre: '', dosis: '', frecuencia: '', duracion: '', instrucciones: '' })
        },

        eliminarMedicamento(index) {
            this.medicamentos.splice(index, 1)
        },

        agregarMedicamentoIA(med) {
            const existe = this.medicamentos.find(m => m.nombre === med.nombre)
            if (existe) return

            this.medicamentos.push({
                nombre: med.nombre,
                dosis: med.concentracion || '',
                frecuencia: '',
                duracion: '',
                // Los medicamentos del inventario (Medicamento::$fillable)
                // ya traen su propio campo "indicaciones" desde
                // recetaInteligente(); los sugeridos genéricos de la IA
                // (no verificados) no lo traen, así que queda vacío.
                instrucciones: med.indicaciones || ''
            })

            // Autocompletamos la recomendación general sugerida por la IA
            // solo si el médico aún no escribió nada.
            if (!this.recomendacionGeneral.trim() && this.recomendacionesGeneralesIA) {
                this.recomendacionGeneral = this.recomendacionesGeneralesIA
            }
        },

        async guardarReceta() {
            if (!this.puedeGuardar || this.guardando) return

            this.guardando = true
            this.errorGuardar = null

            try {
                const url = `${route}/consultaIA/${this.consultaId}/receta`

                const response = await axios.post(url, {
                    medicamentos: this.medicamentos,
                    recomendacion: this.recomendacionGeneral
                })

                if (response.data && response.data.success) {
                    this.mostrarToast('Receta guardada ✓')
                    this.$emit('receta-guardada', {
                        consultaId: this.consultaId,
                        medicamentos: this.medicamentos,
                        recomendacion: this.recomendacionGeneral,
                        recetaId: response.data.receta_id || null
                    })
                } else {
                    this.errorGuardar = (response.data && response.data.error)
                        || 'No se pudo guardar la receta'
                }

            } catch (error) {
                console.error('Error al guardar la receta:', error)
                this.errorGuardar = error?.response?.data?.error || 'No se pudo guardar la receta'
            } finally {
                this.guardando = false
            }
        },

        mostrarToast(msg) {
            this.toastMsg = msg
            clearTimeout(this.toastTimer)
            this.toastTimer = setTimeout(() => { this.toastMsg = '' }, 2200)
        }
    }
}
</script>

<style scoped>
.gap-2 { gap: 0.5rem; }

.row-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
}

.medicamento-item {
    background: #fafafa;
}

.receta-toast {
    position: absolute;
    bottom: 14px;
    left: 50%;
    transform: translateX(-50%);
    background: #111827;
    color: #fff;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 600;
}
.fade-enter-active, .fade-leave-active { transition: opacity .2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>