<template>
    <div class="vitals-panel">
        <div class="vitals-panel-head">
            <span>Signos vitales</span>
            <span class="vitals-panel-sub">Rangos evaluados para adulto</span>
        </div>

        <!-- NOTIFICACIÓN: solo se muestra si NO hay triage guardado
             en esta visita a la vista (triageGuardadoLocal === null).
             Se reinicia cada vez que el componente se vuelve a montar,
             sin importar los triages históricos que ya existan en
             paciente.triages. -->
        <div v-if="!triageGuardadoLocal" class="vitals-empty-wrap">
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

        <!-- PANEL: triage ya registrado en esta visita -->
        <div v-else class="vitals-grid">
            <div class="vital-item" v-if="triageGuardadoLocal.presion">
                <span class="vital-label">Presión arterial</span>
                <span class="vital-value">{{ triageGuardadoLocal.presion }}</span>
            </div>
            <div class="vital-item" v-if="triageGuardadoLocal.saturacion !== null && triageGuardadoLocal.saturacion !== undefined && triageGuardadoLocal.saturacion !== ''">
                <span class="vital-label">Saturación O₂</span>
                <span class="vital-value">{{ triageGuardadoLocal.saturacion }}%</span>
            </div>
            <div class="vital-item" v-if="triageGuardadoLocal.temperatura !== null && triageGuardadoLocal.temperatura !== undefined && triageGuardadoLocal.temperatura !== ''">
                <span class="vital-label">Temperatura</span>
                <span class="vital-value">{{ triageGuardadoLocal.temperatura }}°C</span>
            </div>
            <div class="vital-item" v-if="triageGuardadoLocal.frecuencia_cardiaca !== null && triageGuardadoLocal.frecuencia_cardiaca !== undefined && triageGuardadoLocal.frecuencia_cardiaca !== ''">
                <span class="vital-label">Frec. cardíaca</span>
                <span class="vital-value">{{ triageGuardadoLocal.frecuencia_cardiaca }} lpm</span>
            </div>
            <div class="vital-item" v-if="triageGuardadoLocal.frecuencia_respiratoria !== null && triageGuardadoLocal.frecuencia_respiratoria !== undefined && triageGuardadoLocal.frecuencia_respiratoria !== ''">
                <span class="vital-label">Frec. respiratoria</span>
                <span class="vital-value">{{ triageGuardadoLocal.frecuencia_respiratoria }} rpm</span>
            </div>
            <div class="vital-item" v-if="triageGuardadoLocal.peso !== null && triageGuardadoLocal.peso !== undefined && triageGuardadoLocal.peso !== ''">
                <span class="vital-label">Peso</span>
                <span class="vital-value">{{ triageGuardadoLocal.peso }} kg</span>
            </div>
            <div class="vital-item" v-if="triageGuardadoLocal.talla !== null && triageGuardadoLocal.talla !== undefined && triageGuardadoLocal.talla !== ''">
                <span class="vital-label">Talla</span>
                <span class="vital-value">{{ triageGuardadoLocal.talla }} cm</span>
            </div>

            <!-- NUEVO: IMC calculado, con clasificación pediátrica (percentil CDC)
                 o de adulto (rangos OMS) según la edad del paciente -->
            <div class="vital-item vital-item-imc" v-if="imcGuardado">
                <span class="vital-label">
                    IMC
                    <span v-if="imcGuardado.tipo === 'pediatrico'" class="vital-imc-percentil">Percentil {{ imcGuardado.percentil }}</span>
                </span>
                <span class="vital-value">
                    {{ imcGuardado.bmi }}
                    <span class="vital-imc-badge" :class="claseImc(imcGuardado)">{{ imcGuardado.clasificacion }}</span>
                </span>
            </div>
        </div>

        <!-- MODAL: agregar triage nuevo -->
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

                        <!-- NUEVO: vista previa del IMC en vivo mientras se captura peso/talla -->
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

    </div>
</template>

<script>
import axios from 'axios'
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
var urlTriage = route + '/triage/guardar'

export default {
    name: 'SignosVitales',
    props: {
        paciente: {
            type: Object,
            required: false,
            default: () => ({})
        }
    },
    
    computed:{
        //Lee el último triage del historial que llega por prop
      // (paciente.triages), independientemente de si se guardó
      // en esta sesión o en una anterior (ej. desde la tabla de
      // ConsultaClinica.vue).
     ultimoTriageDelPaciente() {
        const triages = this.paciente?.triages
        if (!Array.isArray(triages) || !triages.length) return null
                      return triages[triages.length - 1]  }
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
            triageGuardadoLocal: null,
            formTriage: this.formTriageVacio()
        }
    },

    mounted() {
       // Inicializa con el último triage histórico del paciente
       // (si existe), en vez de arrancar siempre vacío.
       this.triageGuardadoLocal = this.ultimoTriageDelPaciente
    },
    watch: {
        // Si el padre (ConsultaInteligente.vue) refresca `paciente`
        // (ej. tras @triage-agregado → obtenerPaciente()), reflejamos
        // el triage más reciente que venga del backend.
        paciente: {
            deep: true,
            handler() {
                this.triageGuardadoLocal = this.ultimoTriageDelPaciente
            }
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
            if (s.startsWith('m')) return 'M' // masculino
            if (s.startsWith('f')) return 'F' // femenino
            return null
        },

        // Vista previa en vivo del IMC mientras se llena el modal
        imcModalPreview() {
            return this.calcularIMCInfo(this.formTriage.peso, this.formTriage.talla)
        },

        // IMC del triage ya guardado, para mostrar en el panel
        imcGuardado() {
            if (!this.triageGuardadoLocal) return null
            return this.calcularIMCInfo(this.triageGuardadoLocal.peso, this.triageGuardadoLocal.talla)
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
                const response = await axios.post(urlTriage, {
                    paciente_id: this.paciente?.id,
                    ...this.formTriage
                })

                if (response.data.success === false) {
                    this.errorTriage = response.data.error || 'No se pudo guardar el triage.'
                    return
                }

                // Si el backend regresa el triage guardado lo usamos tal
                // cual; si no, reflejamos localmente lo que se envió.
                const nuevoTriage = response.data.triage || { ...this.formTriage }

                this.triageGuardadoLocal = nuevoTriage
                this.mostrarModalTriage = false

                // Avisamos al padre por si necesita refrescar `paciente`
                // (por ejemplo, para que el próximo fetch ya traiga este
                // triage dentro de paciente.triages).
                this.$emit('triage-agregado', nuevoTriage)

            } catch (error) {
                console.error('Error al guardar triage:', error)
                this.errorTriage = error.response?.data?.error
                    || 'No se pudo guardar el triage. Intenta de nuevo.'
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
    border-radius: 18px;
    padding: 22px;
    margin-bottom: 1rem;
    box-shadow: 0 2px 10px rgba(15,23,42,.05);
}

.vitals-panel-head {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    margin-bottom: 16px;
    padding: 0 2px;
}

.vitals-panel-head > span:first-child {
    font-family: 'Sora', sans-serif;
    font-weight: 700;
    font-size: .95rem;
    color: var(--ink);
    letter-spacing: .3px;
}

.vitals-panel-sub {
    font-size: .72rem;
    color: var(--ink-faint);
}

/* ─── NOTIFICACIÓN: agregar triage nuevo ────────────────────────── */

.vitals-empty-wrap {
    padding: 4px 2px 2px;
}

.vitals-notice {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 14px;
    text-align: left;
    background: var(--status-warning-soft);
    border: 1px solid rgba(217,119,6,.3);
    border-radius: 14px;
    padding: 14px 16px;
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
    font-size: 1.2rem;
    flex-shrink: 0;
}

.vitals-notice-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex-grow: 1;
}

.vitals-notice-text strong {
    font-size: .86rem;
    color: #7C4A05;
}

.vitals-notice-text small {
    font-size: .74rem;
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

/* ─── PANEL: triage guardado en esta visita ─────────────────────── */

.vitals-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    padding: 4px 2px 2px;
}

.vital-item {
    background: var(--paper);
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 10px 12px;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.vital-label {
    font-size: .68rem;
    font-weight: 600;
    color: var(--ink-soft);
    text-transform: uppercase;
    letter-spacing: .3px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
}

.vital-value {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .95rem;
    font-weight: 600;
    color: var(--ink);
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

/* NUEVO: tarjeta de IMC dentro del panel guardado */
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
}

/* ─── MODAL: agregar triage ──────────────────────────────────────── */

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, .5);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    padding: 16px;
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

/* NUEVO: vista previa de IMC dentro del modal */
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

/* Transición de entrada/salida del overlay */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity .2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}
</style>