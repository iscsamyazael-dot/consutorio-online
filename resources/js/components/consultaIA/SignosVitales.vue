<template>
    <div class="vitals-panel">
        <div class="vitals-panel-head">
            <span>Signos vitales</span>
            <span class="vitals-panel-sub">Rangos evaluados para adulto</span>
        </div>

        <div v-if="!tieneDatos" class="vitals-empty">
            Este paciente aún no tiene un triage registrado.
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
    </div>
</template>

<script>
export default {
    name: 'SignosVitales',
    props: {
        paciente: {
            type: Object,
            required: false,
            default: () => ({})
        }
    },
    computed: {
        // El backend entrega los signos vitales dentro de un arreglo
        // "triages"; tomamos el más reciente (el primero de la lista).
        // Si en el futuro el backend cambia y regresa el objeto de
        // triage ya "aplanado" dentro de paciente, este es el único
        // lugar que habría que ajustar.
        ultimoTriage() {
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

.vitals-empty {
    text-align: center;
    color: var(--ink-faint);
    font-size: .86rem;
    padding: 24px 8px;
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
</style>