<!--
    FIX MODAL DE TRIAGE (portal-vue):
    El overlay (.modal-overlay) usa position:fixed + top/left/right/bottom
    para cubrir toda la pantalla. Eso deja de funcionar si CUALQUIER
    ancestro en el árbol del DOM tiene transform, filter, perspective,
    contain, will-change o backdrop-filter -- el navegador vuelve el
    "fixed" relativo a ese ancestro en vez de a la ventana, y por eso el
    modal se veía encogido dentro del layout en vez de cubrir todo.

    En vez de perseguir cuál ancestro específico (Blade/AdminLTE/algún
    wrapper) es el culpable, se usa <Teleport> -- componente NATIVO de
    Vue 3 (core, sin instalar nada) -- para montar el modal directo
    como hijo de <body>, fuera de ese árbol. Así el overlay queda
    garantizado fuera de cualquier contenedor problemático, sin
    importar la causa, y sin agregar ninguna dependencia nueva al
    proyecto (el proyecto usa Vue 3: vue@^3.5.30 en package.json).

    El único cambio dentro de este componente es envolver el bloque del
    modal en <Teleport to="body">...</Teleport>. Todo lo demás
    (lógica, estilos, resto del template) queda igual. No requiere
    ningún paso adicional en el resto del proyecto (a diferencia de
    portal-vue, que sí necesitaba plugin + un <portal-target> en el
    layout -- eso era para Vue 2 y aquí no aplica).

    FIX (triageGuardadoLocal no se reinicia realmente):
    mounted() y el watch de `paciente` rellenaban triageGuardadoLocal
    con el último triage HISTÓRICO del paciente (ultimoTriageDelPaciente),
    contradiciendo lo que dice el comentario de la notificación más
    abajo ("se reinicia cada vez que el componente se vuelve a montar,
    sin importar los triages históricos"). Eso causaba que, en cuanto
    paciente.triages traía algo, la alerta "Agrega los signos vitales"
    desapareciera y se mostrara el panel .vitals-grid -- pero como el
    botón para abrir el modal SOLO vive dentro del v-if="!triageGuardadoLocal",
    ya no había forma de registrar un triage nuevo para esta consulta.
    Se quitaron mounted() y el watch: triageGuardadoLocal ahora solo
    cambia cuando se guarda un triage nuevo desde el modal.
-->
<template>
    <div class="vitals-panel">
        <div class="vitals-panel-head d-flex justify-content-between align-items-center">
            <div>
                <span>Signos vitales</span>
                <span class="vitals-panel-sub ms-2">Rangos evaluados para adulto</span>
            </div>
            
            <!-- Botones de Control de Edición Inline -->
            <div v-if="triageVisitaActual">
                <button v-if="!editandoSignosInline" type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" @click="activarEdicionInline">
                    <i class="fas fa-pencil-alt"></i> Editar
                </button>
                <template v-else>
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 me-1" :disabled="guardandoTriage" @click="cancelarEdicionInline">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-sm btn-success py-0 px-2" :disabled="guardandoTriage" @click="guardarEdicionInline">
                        <span v-if="guardandoTriage"><i class="fas fa-spinner fa-spin"></i></span>
                        <span v-else><i class="fas fa-check"></i> Guardar</span>
                    </button>
                </template>
            </div>
        </div>

        <!-- NOTIFICACIÓN: si no hay triage -->
        <div v-if="!triageVisitaActual" class="vitals-empty-wrap">
            <button type="button" class="vitals-notice" @click="abrirModalTriage">
                <span class="vitals-notice-icon">⚠️</span>
                <span class="vitals-notice-text">
                    <strong>Agrega los signos vitales de esta consulta.</strong>
                    <small>Click para registrarlos ahora</small>
                </span>
                <span class="vitals-notice-arrow">
                    <i class="fas fa-chevron-right"></i>
                </span>
            </button>
        </div>

        <!-- PANEL: triage ya registrado (Muestra Texto o Inputs según editandoSignosInline) -->
        <div v-else class="vitals-grid">
            <!-- Presión Arterial -->
            <div class="vital-item" :class="!editandoSignosInline ? ('vital-item--' + estadoPresion(triageVisitaActual.presion)) : ''" v-if="triageVisitaActual.presion || editandoSignosInline">
                <span class="vital-label">Presión arterial</span>
                <div v-if="!editandoSignosInline" class="vital-value-row">
                    <span class="vital-value" :class="'vital-value--' + estadoPresion(triageVisitaActual.presion)">{{ triageVisitaActual.presion }}</span>
                    <span class="vital-unit">mmHg</span>
                </div>
                <input v-else v-model="formTriage.presion" type="text" class="form-control form-control-sm mt-1" placeholder="120/80">
            </div>

            <!-- Saturación O2 -->
            <div class="vital-item" :class="!editandoSignosInline ? ('vital-item--' + estadoSaturacion(triageVisitaActual.saturacion)) : ''">
                <span class="vital-label">Saturación O₂</span>
                <div v-if="!editandoSignosInline" class="vital-value-row">
                    <span class="vital-value" :class="'vital-value--' + estadoSaturacion(triageVisitaActual.saturacion)">{{ triageVisitaActual.saturacion }}</span>
                    <span class="vital-unit">%</span>
                </div>
                <input v-else v-model.number="formTriage.saturacion" type="number" class="form-control form-control-sm mt-1" placeholder="98">
            </div>

            <!-- Temperatura -->
            <div class="vital-item" :class="!editandoSignosInline ? ('vital-item--' + estadoTemperatura(triageVisitaActual.temperatura)) : ''">
                <span class="vital-label">Temperatura</span>
                <div v-if="!editandoSignosInline" class="vital-value-row">
                    <span class="vital-value" :class="'vital-value--' + estadoTemperatura(triageVisitaActual.temperatura)">{{ triageVisitaActual.temperatura }}</span>
                    <span class="vital-unit">°C</span>
                </div>
                <input v-else v-model.number="formTriage.temperatura" type="number" step="0.1" class="form-control form-control-sm mt-1" placeholder="36.5">
            </div>

            <!-- Frecuencia Cardíaca -->
            <div class="vital-item" :class="!editandoSignosInline ? ('vital-item--' + estadoFrecuenciaCardiaca(triageVisitaActual.frecuencia_cardiaca)) : ''">
                <span class="vital-label">Frec. cardíaca</span>
                <div v-if="!editandoSignosInline" class="vital-value-row">
                    <span class="vital-value" :class="'vital-value--' + estadoFrecuenciaCardiaca(triageVisitaActual.frecuencia_cardiaca)">{{ triageVisitaActual.frecuencia_cardiaca }}</span>
                    <span class="vital-unit">lpm</span>
                </div>
                <input v-else v-model.number="formTriage.frecuencia_cardiaca" type="number" class="form-control form-control-sm mt-1" placeholder="75">
            </div>

            <!-- Frecuencia Respiratoria -->
            <div class="vital-item" :class="!editandoSignosInline ? ('vital-item--' + estadoFrecuenciaRespiratoria(triageVisitaActual.frecuencia_respiratoria)) : ''">
                <span class="vital-label">Frec. respiratoria</span>
                <div v-if="!editandoSignosInline" class="vital-value-row">
                    <span class="vital-value" :class="'vital-value--' + estadoFrecuenciaRespiratoria(triageVisitaActual.frecuencia_respiratoria)">{{ triageVisitaActual.frecuencia_respiratoria }}</span>
                    <span class="vital-unit">rpm</span>
                </div>
                <input v-else v-model.number="formTriage.frecuencia_respiratoria" type="number" class="form-control form-control-sm mt-1" placeholder="16">
            </div>

            <!-- Peso -->
            <div class="vital-item">
                <span class="vital-label">Peso</span>
                <div v-if="!editandoSignosInline" class="vital-value-row">
                    <span class="vital-value">{{ triageVisitaActual.peso }}</span>
                    <span class="vital-unit">kg</span>
                </div>
                <input v-else v-model.number="formTriage.peso" type="number" step="0.1" class="form-control form-control-sm mt-1" placeholder="70">
            </div>

            <!-- Talla -->
            <div class="vital-item">
                <span class="vital-label">Talla</span>
                <div v-if="!editandoSignosInline" class="vital-value-row">
                    <span class="vital-value">{{ triageVisitaActual.talla }}</span>
                    <span class="vital-unit">cm</span>
                </div>
                <input v-else v-model.number="formTriage.talla" type="number" class="form-control form-control-sm mt-1" placeholder="170">
            </div>

            <!-- IMC (Muestra el valor actual o la vista previa en vivo si se está editando peso/talla) -->
            <div class="vital-item vital-item-imc" v-if="imcGuardado || editandoSignosInline">
                <span class="vital-label">
                    IMC
                    <span v-if="(editandoSignosInline ? imcModalPreview : imcGuardado)?.tipo === 'pediatrico'" class="vital-imc-percentil">
                        Percentil {{ (editandoSignosInline ? imcModalPreview : imcGuardado)?.percentil }}
                    </span>
                </span>
                <span class="vital-value">
                    {{ (editandoSignosInline ? imcModalPreview : imcGuardado)?.bmi || '---' }}
                    <span class="vital-imc-badge" :class="claseImc(editandoSignosInline ? imcModalPreview : imcGuardado)">
                        {{ (editandoSignosInline ? imcModalPreview : imcGuardado)?.clasificacion || 'Calculando...' }}
                    </span>
                </span>
            </div>
        </div>

        <!-- MODAL: agregar triage nuevo (sin cambios respecto al que ya tienes) -->
        <Teleport to="body">
            <transition name="modal-fade">
                <div v-if="mostrarModalTriage" class="modal-overlay" @click.self="cerrarModalTriage">
                    <div class="modal-triage">

                        <div class="modal-triage-head">
                            <h5>Agregar triage</h5>
                            <button type="button" class="modal-triage-close" :disabled="guardandoTriage" @click="cerrarModalTriage">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <p class="modal-triage-sub">Registra los signos vitales del paciente.</p>

                        <div v-if="errorTriage" class="modal-triage-error">
                            <i class="fas fa-times-circle mr-1"></i>
                            {{ errorTriage }}
                        </div>

                        <form class="modal-triage-grid" @submit.prevent="guardarTriage">

                            <label class="campo-triage">
                                <span>Presión arterial</span>
                                <input v-model="formTriage.presion" type="text" placeholder="120/80" :disabled="guardandoTriage">
                            </label>

                            <label class="campo-triage">
                                <span>Saturación O₂ (%)</span>
                                <input v-model.number="formTriage.saturacion" type="number" step="1" placeholder="98" :disabled="guardandoTriage">
                            </label>

                            <label class="campo-triage">
                                <span>Temperatura (°C)</span>
                                <input v-model.number="formTriage.temperatura" type="number" step="0.1" placeholder="36.5" :disabled="guardandoTriage">
                            </label>

                            <label class="campo-triage">
                                <span>Frec. cardíaca (lpm)</span>
                                <input v-model.number="formTriage.frecuencia_cardiaca" type="number" step="1" placeholder="75" :disabled="guardandoTriage">
                            </label>

                            <label class="campo-triage">
                                <span>Frec. respiratoria (rpm)</span>
                                <input v-model.number="formTriage.frecuencia_respiratoria" type="number" step="1" placeholder="16" :disabled="guardandoTriage">
                            </label>

                            <label class="campo-triage">
                                <span>Peso (kg)</span>
                                <input v-model.number="formTriage.peso" type="number" step="0.1" placeholder="70" :disabled="guardandoTriage">
                            </label>

                            <label class="campo-triage">
                                <span>Talla (cm)</span>
                                <input v-model.number="formTriage.talla" type="number" step="1" placeholder="170" :disabled="guardandoTriage">
                            </label>

                            <div class="campo-triage campo-triage-imc" v-if="imcModalPreview">
                                <span>IMC (calculado)</span>
                                <div class="imc-preview">
                                    <strong class="imc-preview-valor">{{ imcModalPreview.bmi }}</strong>
                                    <span class="imc-preview-clasif" :class="claseImc(imcModalPreview)">
                                        {{ imcModalPreview.clasificacion }}
                                    </span>
                                    <small v-if="imcModalPreview.tipo === 'pediatrico'" class="imc-preview-percentil">
                                        Percentil {{ imcModalPreview.percentil }} (tabla CDC)
                                    </small>
                                </div>
                            </div>
                            <div class="campo-triage campo-triage-imc campo-triage-imc-vacio" v-else-if="formTriage.peso || formTriage.talla">
                                <span>IMC (calculado)</span>
                                <small class="imc-preview-hint">Captura peso y talla para calcularlo</small>
                            </div>

                        </form>

                        <div class="modal-triage-actions">
                            <button type="button" class="btn-modal btn-modal-secundario" :disabled="guardandoTriage" @click="cerrarModalTriage">
                                Cancelar
                            </button>
                            <button type="button" class="btn-modal btn-modal-primario" :disabled="guardandoTriage" @click="guardarTriage">
                                <span v-if="guardandoTriage"><i class="fas fa-spinner fa-spin"></i> Guardando...</span>
                                <span v-else><i class="fas fa-check"></i> Guardar triage</span>
                            </button>
                        </div>

                    </div>
                </div>
            </transition>
        </Teleport>

    </div>
</template>
<script>
import ApiService from '../../services/ApiService.js'
import { evaluarIMC } from '@/utils/bmiPercentile.js'
import lmsTable from '@/data/bmi-lms-cdc.json'

// Mismo patrón de rutas que usa el componente de chat de consulta IA.
var route = document.querySelector("[name=route]").value

// Ruta confirmada: TriageController@guardarTriageRapido
// POST /triage/guardar/{id?} (routes/web.php). El {id?} es opcional;
// como aquí siempre se crea un triage NUEVO (paciente sin triage
// previo), se omite y se llama sin id: POST {route}/triage/guardar
// con { paciente_id, presion, saturacion, temperatura,
// frecuencia_cardiaca, frecuencia_respiratoria, peso, talla }.
// Shape de la respuesta asumido: { success: true, triage: {...} }
// — si el controlador devuelve algo distinto, ajustar en guardarTriage().

export default {
    name: 'SignosVitales',
    props: {
        paciente: {
            type: Object,
            required: false,
            default: () => ({})
        },
        listaEsperaId: { 
            type: [Number, String], 
            required: false, 
            default: null 
        }
    },

    data() {
        return {
            mostrarModalTriage: false,
            guardandoTriage: false,
            errorTriage: '',
            // Guarda el triage recién creado en ESTA visita a la vista.
            // Se reinicia a null cada vez que el componente se monta de
            // nuevo (al salir y volver a entrar), sin importar los
            // triages históricos que ya existan en paciente.triages.
            // Esto es lo que controla si se muestra el aviso o el panel.
            // IMPORTANTE: no se rellena en mounted()/watch a partir del
            // historial (paciente.triages) -- eso era el bug: ocultaba
            // el botón de "Agregar triage" en cuanto el paciente ya
            // tenía algún triage viejo, sin dar forma de registrar uno
            // nuevo para esta consulta.
            triageGuardadoLocal: null,
            formTriage: this.formTriageVacio(),
            editandoTriage: false,
            editandoSignosInline: false
        }
    },

    computed: {
        // Edad del paciente en meses. Preferimos fecha_nacimiento (más
        // precisa, necesaria para el percentil pediátrico); si no está
        // disponible, caemos a la columna `edad` (años) del paciente.
        edadPacienteMeses() {
            if (this.paciente?.fecha_nacimiento) {
                const nacimiento = new Date(this.paciente.fecha_nacimiento)
                if (!isNaN(nacimiento.getTime())) {
                    const hoy = new Date()
                    let meses = (hoy.getFullYear() - nacimiento.getFullYear()) * 12
                    meses += hoy.getMonth() - nacimiento.getMonth()
                    if (hoy.getDate() < nacimiento.getDate()) meses -= 1
                    return Math.max(meses, 0)
                }
            }
            if (this.paciente?.edad !== null && this.paciente?.edad !== undefined && this.paciente?.edad !== '') {
                return Number(this.paciente.edad) * 12
            }
            return null
        },

        // Normaliza el campo `sexo` (texto libre en la BD) a 'M' / 'F'.
        sexoPacienteNormalizado() {
            const s = (this.paciente?.sexo || '').toString().trim().toLowerCase()
            if (!s) return null
            if (s.startsWith('m')) return 'M'
            if (s.startsWith('f')) return 'F'
            return null
        },

        // Vista previa en vivo del IMC mientras se llena el modal
        imcModalPreview() {
            return this.calcularIMCInfo(this.formTriage.peso, this.formTriage.talla)
        },

        // IMC del triage ya guardado, para mostrar en el panel
        imcGuardado() {
            if (!this.triageVisitaActual) return null
            return this.calcularIMCInfo(this.triageVisitaActual.peso, this.triageVisitaActual.talla)
        },
        // NUEVO: el triage a mostrar. Prioriza el recién guardado en ESTA
        // sesión (triageGuardadoLocal); si no hay uno nuevo, busca en el
        // historial (paciente.triages) el que pertenezca a la visita de
        // hoy, comparando por lista_espera_id.
        triageVisitaActual() {
            if (this.triageGuardadoLocal) return this.triageGuardadoLocal
            if (!this.listaEsperaId) return null
            const triages = this.paciente?.triages || []
            return triages.find(t => t.lista_espera_id == this.listaEsperaId) || null
        }
    },
    methods: {
        formTriageVacio() {
            return {
                presion: '',
                saturacion: null,
                temperatura: null,
                frecuencia_cardiaca: null,
                frecuencia_respiratoria: null,
                peso: null,
                talla: null
            }
        },

        // Calcula el IMC y su clasificación a partir de peso/talla,
        // usando la edad y sexo del paciente para decidir si aplica
        // la tabla pediátrica (percentil CDC) o el rango fijo de adulto (OMS).
        calcularIMCInfo(peso, talla) {
            const pesoKg = Number(peso)
            const tallaCm = Number(talla)
            if (!pesoKg || !tallaCm) return null

            const agemos = this.edadPacienteMeses
            const sexo = this.sexoPacienteNormalizado

            if (agemos === null || !sexo) {
                // No hay edad o sexo confiables: mostramos el IMC crudo, sin clasificar
                const bmi = pesoKg / Math.pow(tallaCm / 100, 2)
                return {
                    bmi: Number(bmi.toFixed(2)),
                    tipo: 'sin_clasificar',
                    clasificacion: 'Falta edad o sexo del paciente'
                }
            }

            return evaluarIMC({
                pesoKg,
                tallaCm,
                edadAnios: Math.floor(agemos / 12),
                edadMeses: agemos % 12,
                sexo
            }, lmsTable)
        },

        // Color del badge de clasificación según severidad
        claseImc(info) {
            if (!info) return ''
            if (info.tipo === 'sin_clasificar') return 'imc-badge-neutro'
            const c = (info.clasificacion || '').toLowerCase()
            if (c.includes('normal')) return 'imc-badge-normal'
            if (c.includes('bajo')) return 'imc-badge-warning'
            if (c.includes('sobrepeso')) return 'imc-badge-warning'
            if (c.includes('obesidad')) return 'imc-badge-critical'
            return 'imc-badge-neutro'
        },

        // ─── Clasificación clínica (verde / amarillo / rojo) ────────
        // Rangos de referencia para ADULTO en reposo. Devuelve
        // 'normal' | 'alerta' | 'critico' | '' (sin clasificar, para
        // peso/talla que no tienen un rango de alerta universal sin
        // más contexto como edad o IMC objetivo).
        // Ajustar aquí si la clínica maneja rangos propios.
        estadoPresion(presion) {
            if (!presion || typeof presion !== 'string' || !presion.includes('/')) return ''
            const partes = presion.split('/')
            const sistolica = parseFloat(partes[0])
            const diastolica = parseFloat(partes[1])
            if (isNaN(sistolica) || isNaN(diastolica)) return ''

            if (sistolica >= 140 || sistolica < 90 || diastolica >= 90 || diastolica < 60) {
                return 'critico'
            }
            if (sistolica >= 121 || diastolica >= 81) {
                return 'alerta'
            }
            return 'normal'
        },

        estadoSaturacion(valor) {
            const n = parseFloat(valor)
            if (isNaN(n)) return ''
            if (n < 90) return 'critico'
            if (n < 95) return 'alerta'
            return 'normal'
        },

        estadoTemperatura(valor) {
            const n = parseFloat(valor)
            if (isNaN(n)) return ''
            if (n >= 38.5 || n <= 35) return 'critico'
            if (n >= 37.6 || n < 36.1) return 'alerta'
            return 'normal'
        },

        estadoFrecuenciaCardiaca(valor) {
            const n = parseFloat(valor)
            if (isNaN(n)) return ''
            if (n >= 120 || n < 50) return 'critico'
            if (n >= 101 || n < 60) return 'alerta'
            return 'normal'
        },

        estadoFrecuenciaRespiratoria(valor) {
            const n = parseFloat(valor)
            if (isNaN(n)) return ''
            if (n >= 25 || n < 8) return 'critico'
            if (n >= 21 || n < 12) return 'alerta'
            return 'normal'
        },

        abrirModalTriage() {
            this.errorTriage = ''
            this.formTriage = this.formTriageVacio()
            this.mostrarModalTriage = true
        },

        cerrarModalTriage() {
            if (this.guardandoTriage) return // no se cierra a media petición
            this.mostrarModalTriage = false
        },

        async guardarTriage() {
            if (this.guardandoTriage) return
            this.guardandoTriage = true
            this.errorTriage = ''

            try {
                // Preparamos el payload incluyendo el lista_espera_id obligatorio para asociar la visita
                const payload = {
                    paciente_id: this.paciente?.id,
                    lista_espera_id: this.listaEsperaId, // <-- Aseguramos el envío de este ID
                    ...this.formTriage
                }

                let response

                // Usamos ApiService según corresponda (Crear o Editar)
                if (this.editandoTriage && this.triageVisitaActual?.id) {
                    // Si prefieres usar PUT para actualizar (o la ruta con ID)
                    response = await ApiService.put(`/triage/${this.triageVisitaActual.id}`, payload)
                } else {
                    // Ruta base que me compartiste: /triage/guardar
                    response = await ApiService.post('/triage/guardar', payload)
                }

                if (response.data.success === false) {
                    this.errorTriage = response.data.error || 'No se pudo guardar el triage.'
                    return
                }

                const triageActualizado = response.data.triage || { ...payload }
                this.triageGuardadoLocal = triageActualizado
                this.mostrarModalTriage = false
                this.editandoTriage = false

                this.$emit('triage-agregado', triageActualizado)
            } catch (error) {
                console.error('Error al guardar triage:', error)
                this.errorTriage = error.response?.data?.message || error.response?.data?.error
                    || 'No se pudo guardar el triage. Intenta de nuevo.'
            } finally {
                this.guardandoTriage = false
            }
        },
        activarEdicionInline() {
            if (!this.triageVisitaActual) return
            this.editandoSignosInline = true
            // Cargamos los datos actuales en el formulario reactivo
            this.formTriage = {
                presion: this.triageVisitaActual.presion || '',
                saturacion: this.triageVisitaActual.saturacion,
                temperatura: this.triageVisitaActual.temperatura,
                frecuencia_cardiaca: this.triageVisitaActual.frecuencia_cardiaca,
                frecuencia_respiratoria: this.triageVisitaActual.frecuencia_respiratoria,
                peso: this.triageVisitaActual.peso,
                talla: this.triageVisitaActual.talla
            }
        },
        cancelarEdicionInline() {
            this.editandoSignosInline = false
            this.formTriage = this.formTriageVacio()
        },

        async guardarEdicionInline() {
            if (this.guardandoTriage) return
            this.guardandoTriage = true

            try {
                const payload = {
                    paciente_id: this.paciente?.id,
                    lista_espera_id: this.listaEsperaId,
                    ...this.formTriage
                }

                // Llamada PUT al backend usando el ID del triage actual
                const response = await ApiService.put(`/triage/${this.triageVisitaActual.id}`, payload)

                if (response.data.success) {
                    const triageActualizado = response.data.triage || { ...payload }
                    this.triageGuardadoLocal = triageActualizado
                    this.editandoSignosInline = false
                    this.editandoTriage = false
                    
                    // Notificamos al componente padre por si lo requiere
                    this.$emit('triage-agregado', triageActualizado)
                } else {
                    alert(response.data.message || 'No se pudo actualizar el triage.')
                }
            } catch (error) {
                console.error('Error al actualizar signos vitales:', error)
                alert('Ocurrió un error al guardar los cambios.')
            } finally {
                this.guardandoTriage = false
            }
        }
    }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap');

.vitals-panel {
    --ink: #0F172A;
    --ink-soft: #51607A;
    --ink-faint: #94A3B8;
    --paper: #F5F7FA;
    --surface: #FFFFFF;
    --line: #E3E8EF;
    --status-normal: #0E9F6E;
    --status-normal-soft: #E4F7EF;
    --status-warning: #D97706;
    --status-warning-soft: #FDF1DF;
    --status-critical: #DC2626;
    --status-critical-soft: #FCE8E8;

    font-family: 'Inter', system-ui, sans-serif;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 14px;
    padding: 14px 16px;
    margin-bottom: 1rem;
    box-shadow: 0 2px 10px rgba(15,23,42,.05);

    position: sticky;
}

.vitals-panel-head {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    margin-bottom: 10px;
    padding: 0 2px;
}

.vitals-panel-head > span:first-child {
    font-family: 'Sora', sans-serif;
    font-weight: 700;
    font-size: .82rem;
    color: var(--ink);
    letter-spacing: .3px;
}

.vitals-panel-sub {
    font-size: .65rem;
    color: var(--ink-faint);
}

.vitals-empty-wrap {
    padding: 4px 2px 2px;
}

.vitals-notice {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 10px;
    text-align: left;
    background: var(--status-warning-soft);
    border: 1px solid rgba(217,119,6,.3);
    border-radius: 12px;
    padding: 10px 12px;
    cursor: pointer;
    transition: background .18s ease, box-shadow .18s ease, transform .12s ease;
    animation: noticePulse 2.2s ease-in-out infinite;
}

.vitals-notice:hover {
    background: #FCE9C7;
    box-shadow: 0 4px 14px rgba(217,119,6,.18);
}

.vitals-notice:active {
    transform: translateY(1px);
}

.vitals-notice-icon {
    font-size: 1rem;
    flex-shrink: 0;
}

.vitals-notice-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex-grow: 1;
}

.vitals-notice-text strong {
    font-size: .78rem;
    color: #7C4A05;
}

.vitals-notice-text small {
    font-size: .68rem;
    color: #A15A05;
}

.vitals-notice-arrow {
    color: #A15A05;
    flex-shrink: 0;
}

@keyframes noticePulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(217,119,6,.18); }
    50% { box-shadow: 0 0 0 6px rgba(217,119,6,0); }
}

@media (prefers-reduced-motion: reduce) {
    .vitals-notice { animation: none !important; }
}

.vitals-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 6px;
    padding: 0 2px;
}

@media (max-width: 900px) {
    .vitals-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (max-width: 500px) {
    .vitals-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.vital-item {
    background: var(--paper);
    border: 1px solid var(--line);
    border-radius: 9px;
    padding: 8px 4px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
    text-align: center;
    min-height: 78px;
}

.vital-label {
    font-size: .52rem;
    font-weight: 600;
    color: var(--ink-soft);
    text-transform: uppercase;
    letter-spacing: .2px;

    line-height: 1.1;
}

.vital-value-row {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0;
}

.vital-value {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .92rem;
    font-weight: 600;
    color: var(--ink);
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.vital-item-imc {
    grid-column: span 2;
}

.vital-imc-percentil {
    font-size: .64rem;
    font-weight: 600;
    text-transform: none;
    letter-spacing: 0;
    color: var(--ink-faint);
}

.vital-imc-badge {
    font-family: 'Inter', sans-serif;
    font-size: .68rem;
    font-weight: 700;
    padding: 2px 9px;
    border-radius: 999px;
    letter-spacing: .2px;
}

.imc-badge-normal {
    background: var(--status-normal-soft);
    color: var(--status-normal);
}

.imc-badge-warning {
    background: var(--status-warning-soft);
    color: var(--status-warning);
}

.imc-badge-critical {
    background: var(--status-critical-soft);
    color: var(--status-critical);
}

.imc-badge-neutro {
    background: var(--paper);
    color: var(--ink-faint);
    color: #A0A9BD;
}

.vital-unit {
    font-family: 'Inter', system-ui, sans-serif;
    font-size: .55rem;
    font-weight: 500;
    color: #C2C9D6;
}

.vital-item--normal {
    background: var(--status-normal-soft);
    border-color: rgba(14,159,110,.35);
}

.vital-item--alerta {
    background: var(--status-warning-soft);
    border-color: rgba(217,119,6,.35);
}

.vital-item--critico {
    background: var(--status-critical-soft);
    border-color: rgba(220,38,38,.4);
}

.vital-value--normal {
    color: var(--status-normal);
}

.vital-value--alerta {
    color: var(--status-warning);
}

.vital-value--critico {
    color: var(--status-critical);
}

.modal-overlay {
    position: fixed;
    top: var(--header-height, 160px);
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, .5);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    padding: 16px;
    overflow-y: auto;
}

.modal-triage {
    background: #fff;
    border-radius: 18px;
    padding: 24px 26px 22px;
    max-width: 480px;
    width: 100%;
    box-shadow: 0 20px 50px rgba(0,0,0,.25);
    animation: modalPop .25s cubic-bezier(.22,1,.36,1) both;
    font-family: 'Inter', system-ui, sans-serif;
}

@keyframes modalPop {
    from { opacity: 0; transform: scale(.92) translateY(6px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}

.modal-triage-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2px;
}

.modal-triage-head h5 {
    font-family: 'Sora', sans-serif;
    font-weight: 700;
    font-size: 1.05rem;
    color: var(--ink, #0F172A);
    margin: 0;
}

.modal-triage-close {
    border: none;
    background: transparent;
    color: #94A3B8;
    font-size: 1rem;
    cursor: pointer;
    padding: 4px;
}

.modal-triage-close:disabled {
    opacity: .5;
    cursor: not-allowed;
}

.modal-triage-sub {
    font-size: .82rem;
    color: #6b7280;
    margin-bottom: 16px;
}

.modal-triage-error {
    background: #fdecea;
    color: #b31414;
    font-size: .8rem;
    border-radius: 10px;
    padding: 8px 12px;
    margin-bottom: 14px;
}

.modal-triage-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}

.campo-triage {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.campo-triage span {
    font-size: .7rem;
    font-weight: 600;
    color: #51607A;
    text-transform: uppercase;
    letter-spacing: .3px;
}

.campo-triage input {
    border: 1px solid #E3E8EF;
    border-radius: 10px;
    padding: 9px 11px;
    font-size: .88rem;
    font-family: 'IBM Plex Mono', monospace;
    color: #0F172A;
    background: #F8FAFC;
    transition: border-color .15s ease, box-shadow .15s ease;
}

.campo-triage input:focus {
    outline: none;
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.15);
    background: #fff;
}

.campo-triage input:disabled {
    opacity: .6;
}

.campo-triage-imc {
    grid-column: span 2;
    background: var(--paper, #F5F7FA);
    border: 1px solid var(--line, #E3E8EF);
    border-radius: 10px;
    padding: 10px 12px;
}

.imc-preview {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.imc-preview-valor {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 1rem;
    color: var(--ink, #0F172A);
}

.imc-preview-clasif {
    font-family: 'Inter', sans-serif;
    font-size: .7rem;
    font-weight: 700;
    padding: 2px 9px;
    border-radius: 999px;
}

.imc-preview-percentil {
    font-size: .7rem;
    color: var(--ink-faint, #94A3B8);
    width: 100%;
}

.imc-preview-hint {
    font-size: .74rem;
    color: var(--ink-faint, #94A3B8);
    font-weight: 400;
    text-transform: none;
}

.modal-triage-actions {
    display: flex;
    gap: 10px;
}

.btn-modal {
    flex: 1;
    border: none;
    border-radius: 12px;
    padding: 11px 14px;
    font-weight: 700;
    font-size: .86rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: background .18s ease, transform .12s ease, box-shadow .18s ease;
}

.btn-modal:active:not(:disabled) {
    transform: translateY(1px);
}

.btn-modal:disabled {
    opacity: .6;
    cursor: not-allowed;
}

.btn-modal-secundario {
    background: #f1f3f5;
    color: #495057;
}

.btn-modal-secundario:hover:not(:disabled) {
    background: #e5e7eb;
}

.btn-modal-primario {
    background: #0E9F6E;
    color: #fff;
    box-shadow: 0 6px 16px rgba(14,159,110,.28);
}

.btn-modal-primario:hover:not(:disabled) {
    background: #0c8a5f;
}

.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity .2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}
</style>