<template>
    <div class="vitals-panel">
        <div class="vitals-panel-head">
            <span>Signos vitales</span>
            <span class="vitals-panel-sub">Rangos evaluados para adulto</span>
        </div>

        <!-- NOTIFICACIÓN: paciente sin triage -->
        <div v-if="!tieneDatos" class="vitals-empty-wrap">
            <button type="button" class="vitals-notice" @click="abrirModalTriage">
                <span class="vitals-notice-icon">⚠️</span>
                <span class="vitals-notice-text">
                    <strong>Este paciente aún no tiene un triage registrado.</strong>
                    <small>Click para agregarlo ahora</small>
                </span>
                <span class="vitals-notice-arrow">
                    <i class="fas fa-chevron-right"></i>
                </span>
            </button>
        </div>

        <div v-else class="vitals-grid">

            <div class="vital-card" :class="'v-' + presionStatus" style="--delay:.03s">
                <div class="vital-label">Presión arterial</div>
                <div class="vital-readout">
                    <span class="vital-value">{{ ultimoTriage.presion || '--' }}</span>
                    <span class="vital-unit">mmHg</span>
                </div>
                <span class="vital-status-tag" v-if="presionStatus">{{ statusLabel(presionStatus) }}</span>
            </div>

            <div class="vital-card" :class="'v-' + saturacionStatus" style="--delay:.06s">
                <div class="vital-label">Saturación O₂</div>
                <div class="vital-readout">
                    <span class="vital-value">{{ formatNum(ultimoTriage.saturacion) }}</span>
                    <span class="vital-unit">%</span>
                </div>
                <span class="vital-status-tag" v-if="saturacionStatus">{{ statusLabel(saturacionStatus) }}</span>
            </div>

            <div class="vital-card" :class="'v-' + temperaturaStatus" style="--delay:.09s">
                <div class="vital-label">Temperatura</div>
                <div class="vital-readout">
                    <span class="vital-value">{{ formatNum(ultimoTriage.temperatura) }}</span>
                    <span class="vital-unit">°C</span>
                </div>
                <span class="vital-status-tag" v-if="temperaturaStatus">{{ statusLabel(temperaturaStatus) }}</span>
            </div>

            <div class="vital-card" :class="'v-' + frecuenciaCardiacaStatus" style="--delay:.12s">
                <div class="vital-label">Frec. cardíaca</div>
                <div class="vital-readout">
                    <span class="vital-value">{{ formatNum(ultimoTriage.frecuencia_cardiaca) }}</span>
                    <span class="vital-unit">lpm</span>
                </div>
                <span class="vital-status-tag" v-if="frecuenciaCardiacaStatus">{{ statusLabel(frecuenciaCardiacaStatus) }}</span>
            </div>

            <div class="vital-card" :class="'v-' + frecuenciaRespiratoriaStatus" style="--delay:.15s">
                <div class="vital-label">Frec. respiratoria</div>
                <div class="vital-readout">
                    <span class="vital-value">{{ formatNum(ultimoTriage.frecuencia_respiratoria) }}</span>
                    <span class="vital-unit">rpm</span>
                </div>
                <span class="vital-status-tag" v-if="frecuenciaRespiratoriaStatus">{{ statusLabel(frecuenciaRespiratoriaStatus) }}</span>
            </div>

            <div class="vital-card" style="--delay:.18s">
                <div class="vital-label">Peso</div>
                <div class="vital-readout">
                    <span class="vital-value">{{ formatNum(ultimoTriage.peso) }}</span>
                    <span class="vital-unit">kg</span>
                </div>
            </div>

            <div class="vital-card" style="--delay:.21s">
                <div class="vital-label">Talla</div>
                <div class="vital-readout">
                    <span class="vital-value">{{ formatNum(ultimoTriage.talla) }}</span>
                    <span class="vital-unit">cm</span>
                </div>
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
    data() {
        return {
            mostrarModalTriage: false,
            guardandoTriage: false,
            errorTriage: '',
            // Guarda el triage recién creado para reflejarlo de inmediato
            // en la UI sin depender de que el padre recargue `paciente`.
            triageGuardadoLocal: null,
            formTriage: this.formTriageVacio()
        }
    },
    computed: {
        // El backend entrega los signos vitales dentro de un arreglo
        // "triages"; tomamos el más reciente (el primero de la lista).
        // Si se acaba de guardar uno nuevo en esta sesión, ese tiene
        // prioridad mientras el padre no refresque `paciente`.
        ultimoTriage() {
            if (this.triageGuardadoLocal) return this.triageGuardadoLocal

            const triages = this.paciente?.triages;
            if (Array.isArray(triages) && triages.length > 0) {
                return triages[0];
            }
            return null;
        },
        // Hay datos si al menos un campo de triage viene con valor
        tieneDatos() {
            const t = this.ultimoTriage;
            if (!t) return false;
            return !!(
                t.presion || t.saturacion || t.temperatura ||
                t.frecuencia_cardiaca || t.frecuencia_respiratoria ||
                t.peso || t.talla
            );
        },
        presionStatus() {
            const raw = this.ultimoTriage?.presion;
            if (raw === null || raw === undefined || raw === '') return '';

            // Puede venir como "120/80" (sistólica/diastólica) o como
            // un solo número (solo sistólica, como entrega este backend).
            if (String(raw).includes('/')) {
                const [sysStr, diaStr] = String(raw).split('/');
                const sys = parseInt(sysStr, 10);
                const dia = parseInt(diaStr, 10);
                if (isNaN(sys) || isNaN(dia)) return '';
                if (sys >= 180 || dia >= 120 || sys < 90) return 'critical';
                if (sys >= 140 || dia >= 90) return 'warning';
                return 'normal';
            }

            const sys = parseInt(raw, 10);
            if (isNaN(sys)) return '';
            if (sys >= 180 || sys < 90) return 'critical';
            if (sys >= 140) return 'warning';
            return 'normal';
        },
        saturacionStatus() {
            const v = this.ultimoTriage?.saturacion;
            if (v === null || v === '' || v === undefined) return '';
            if (v < 90) return 'critical';
            if (v < 95) return 'warning';
            return 'normal';
        },
        temperaturaStatus() {
            const v = this.ultimoTriage?.temperatura;
            if (v === null || v === '' || v === undefined) return '';
            if (v >= 38.5 || v < 35.5) return 'critical';
            if (v >= 37.6) return 'warning';
            return 'normal';
        },
        frecuenciaCardiacaStatus() {
            const v = this.ultimoTriage?.frecuencia_cardiaca;
            if (v === null || v === '' || v === undefined) return '';
            if (v < 50 || v > 120) return 'critical';
            if (v < 60 || v > 100) return 'warning';
            return 'normal';
        },
        frecuenciaRespiratoriaStatus() {
            const v = this.ultimoTriage?.frecuencia_respiratoria;
            if (v === null || v === '' || v === undefined) return '';
            if (v < 8 || v > 24) return 'critical';
            if (v < 12 || v > 20) return 'warning';
            return 'normal';
        }
    },
    methods: {
        formatNum(v) {
            return (v === null || v === undefined || v === '') ? '--' : v;
        },
        statusLabel(status) {
            if (status === 'critical') return 'Fuera de rango';
            if (status === 'warning') return 'Vigilar';
            if (status === 'normal') return 'Normal';
            return '';
        },

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

/* ─── NOTIFICACIÓN: sin triage ──────────────────────────────────── */

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

.vitals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
}

.vital-card {
    background: var(--paper);
    border: 1px solid var(--line);
    border-radius: 13px;
    padding: 14px 16px;
    animation: fadeUp .4s cubic-bezier(.22,1,.36,1) both;
    animation-delay: var(--delay, 0s);
}

.vital-label {
    font-size: .68rem;
    font-weight: 600;
    color: var(--ink-faint);
    letter-spacing: .3px;
    margin-bottom: 8px;
    text-transform: uppercase;
}

.vital-readout {
    display: flex;
    align-items: baseline;
    gap: 6px;
}

.vital-value {
    font-family: 'IBM Plex Mono', monospace;
    font-weight: 600;
    font-size: 1.3rem;
    color: var(--ink);
}

.vital-unit {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .68rem;
    color: var(--ink-faint);
    flex-shrink: 0;
}

.vital-status-tag {
    display: inline-block;
    margin-top: 9px;
    font-size: .64rem;
    font-weight: 700;
    letter-spacing: .3px;
    text-transform: uppercase;
    padding: 3px 8px;
    border-radius: 999px;
}

.v-normal { background: var(--status-normal-soft); border-color: rgba(14,159,110,.3); }
.v-normal .vital-value { color: #067A56; }
.v-normal .vital-status-tag { background: rgba(14,159,110,.14); color: #067A56; }

.v-warning { background: var(--status-warning-soft); border-color: rgba(217,119,6,.3); }
.v-warning .vital-value { color: #A15A05; }
.v-warning .vital-status-tag { background: rgba(217,119,6,.16); color: #A15A05; }

.v-critical { background: var(--status-critical-soft); border-color: rgba(220,38,38,.35); }
.v-critical .vital-value { color: #B31414; }
.v-critical .vital-status-tag { background: rgba(220,38,38,.16); color: #B31414; }

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (prefers-reduced-motion: reduce) {
    .vital-card { animation: none !important; }
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