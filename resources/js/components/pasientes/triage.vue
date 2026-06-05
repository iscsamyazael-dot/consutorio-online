<template>
    <div>

        <!-- DIVIDER -->
        <div class="premium-divider">
            <span class="divider-label">
                <svg viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                </svg>
                Triage
            </span>
        </div>

        <!-- TRIAGE HEADER -->
        <div class="section-header triage-header">
            <div class="triage-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <path d="M9 12h6M12 9v6M4.5 12a7.5 7.5 0 1115 0 7.5 7.5 0 01-15 0z" stroke-linecap="round"/>
                </svg>
                <div class="triage-icon-pulse"></div>
            </div>
            <div>
                <h3>TRIAGE</h3>
                <p>Evaluación inicial y signos vitales del paciente</p>
            </div>
        </div>

        <!-- SIGNOS VITALES -->
        <div class="row g-4 vitals-row">

            <!-- PRESIÓN -->
            <div class="col-md-4 vital-field" style="--vdelay:.05s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                    </svg>
                    Presión (mmHg)
                </label>
                <div class="vital-card" data-color="blue">
                    <div class="vital-input-wrap">
                        <input v-model="triage.presion" type="text"
                               class="vital-input"
                               placeholder="120/80">
                    </div>
                    <div class="vital-wave">
                        <svg viewBox="0 0 80 28" preserveAspectRatio="none">
                            <polyline points="0,14 10,14 16,4 22,24 28,14 38,14 44,2 50,26 56,14 80,14"
                                      fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- SATURACIÓN -->
            <div class="col-md-4 vital-field" style="--vdelay:.12s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"/>
                    </svg>
                    Saturación (%)
                </label>
                <div class="vital-card" data-color="green">
                    <div class="vital-input-wrap">
                        <input v-model="triage.saturacion" type="number"
                               class="vital-input"
                               placeholder="98">
                    </div>
                    <div class="vital-wave green-wave">
                        <svg viewBox="0 0 80 28" preserveAspectRatio="none">
                            <polyline points="0,14 12,14 18,6 24,22 30,14 42,14 48,8 54,20 60,14 80,14"
                                      fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- TEMPERATURA -->
            <div class="col-md-4 vital-field" style="--vdelay:.2s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 00-1 1v7.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V3a1 1 0 00-1-1zM5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9z" clip-rule="evenodd"/>
                    </svg>
                    Temperatura (°C)
                </label>
                <div class="vital-card" data-color="orange">
                    <div class="vital-input-wrap">
                        <input v-model="triage.temperatura" type="text"
                               class="vital-input"
                               placeholder="36.5">
                    </div>
                    <div class="vital-wave orange-wave">
                        <svg viewBox="0 0 80 28" preserveAspectRatio="none">
                            <polyline points="0,18 15,18 22,8 30,18 80,18"
                                      fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
            </div>

        </div>

        <!-- SÍNTOMAS -->
        <div class="vital-field mt-4" style="--vdelay:.28s">
            <label class="form-label">
                <svg viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                </svg>
                Síntomas
            </label>
            <div class="textarea-wrap">
                <textarea v-model="triage.sintomas"
                          class="premium-textarea" rows="4"
                          placeholder="Ingrese síntomas del paciente..."></textarea>
                <span class="textarea-line"></span>
            </div>
        </div>

        <!-- ESTADO DEL PACIENTE -->
        <div class="vital-field mt-4" style="--vdelay:.36s">
            <label class="form-label">
                <svg viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                Estado del paciente
            </label>
            <div class="estado-grid">
                <label v-for="opt in estadoOptions" :key="opt.value"
                       class="estado-option"
                       :class="[opt.color, { active: triage.estado === opt.value }]">
                    <input type="radio" v-model="triage.estado" :value="opt.value" hidden>
                    <div class="estado-dot"></div>
                    <span class="estado-emoji">{{ opt.emoji }}</span>
                    <span class="estado-label">{{ opt.label }}</span>
                    <svg class="estado-check" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </label>
            </div>
        </div>

        <!-- BOTONES -->
        <div class="action-row vital-field mt-5" style="--vdelay:.44s">

            <button type="button" class="btn cancel-btn">
                <svg viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
                Cancelar
            </button>

            <button type="submit" class="btn save-btn" :class="{ saving: isSaving }" @click="handleSave">
                <svg v-if="!isSaving" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293z"/>
                </svg>
                <svg v-if="isSaving" class="spin-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" stroke-linecap="round"/>
                </svg>
                {{ isSaving ? 'Guardando...' : 'Guardar Consulta' }}
            </button>

        </div>

    </div>
</template>

<script>
export default {
    name: 'TriageSection',
    data() {
        return {
            isSaving: false,
            triage: {
                presion: '',
                saturacion: '',
                temperatura: '',
                sintomas: '',
                estado: ''
            },
            estadoOptions: [
                { value: 'Riesgo Alto', label: 'Riesgo Alto', emoji: '🔴', color: 'opt-red' },
                { value: 'Observación', label: 'Observación',  emoji: '🟡', color: 'opt-yellow' },
                { value: 'Estable',     label: 'Estable',      emoji: '🟢', color: 'opt-green' },
            ]
        }
    },
    methods: {
    async handleSave() {

        this.isSaving = true;

        try {

            const response = await axios.post('/triage', {
                presion: this.triage.presion,
                saturacion: this.triage.saturacion,
                temperatura: this.triage.temperatura,
                sintomas: this.triage.sintomas,
                estado: this.triage.estado
            });

            alert('Datos guardados correctamente');

            console.log(response.data);

        } catch (error) {

            console.error(error);

            alert('Error al guardar');

        } finally {

            this.isSaving = false;

        }
    }
}
}
</script>

<style scoped>

/* ══════════════════════
   DIVIDER
══════════════════════ */
.premium-divider {
    position: relative;
    height: 2px;
    margin: 45px 0;
    background: linear-gradient(to right, transparent, rgba(37,99,235,.25), transparent);
    display: flex;
    align-items: center;
    justify-content: center;
}

.divider-label {
    position: absolute;
    background: #fff;
    padding: 0 14px;
    font-size: .7rem;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #2563eb;
    display: flex;
    align-items: center;
    gap: 6px;
}

.divider-label svg {
    width: 13px;
    height: 13px;
    animation: heartbeat 1.5s ease-in-out infinite;
}

@keyframes heartbeat {
    0%, 100% { transform: scale(1); }
    14%       { transform: scale(1.25); }
    28%       { transform: scale(1); }
    42%       { transform: scale(1.15); }
    70%       { transform: scale(1); }
}

/* ══════════════════════
   TRIAGE HEADER
══════════════════════ */
.triage-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 28px;
    animation: slideDown .5s cubic-bezier(.22,1,.36,1) both;
}

.triage-icon-wrap {
    position: relative;
    width: 52px;
    height: 52px;
    background: linear-gradient(135deg, #ef4444, #f87171);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 20px rgba(239,68,68,.3);
    flex-shrink: 0;
}

.triage-icon-wrap svg {
    width: 26px;
    height: 26px;
    color: #fff;
}

.triage-icon-pulse {
    position: absolute;
    inset: -5px;
    border-radius: 20px;
    border: 2px solid rgba(239,68,68,.3);
    animation: triagePulse 2s ease-out infinite;
}

@keyframes triagePulse {
    0%   { transform: scale(1); opacity: .7; }
    100% { transform: scale(1.3); opacity: 0; }
}

.section-header h3 {
    font-size: 1.25rem;
    font-weight: 900;
    color: #111827;
    margin: 0;
    letter-spacing: -.2px;
}

.section-header p {
    color: #6b7280;
    font-size: .875rem;
    margin: 4px 0 0;
}

/* ══════════════════════
   FIELD ENTRANCE
══════════════════════ */
.vital-field {
    animation: fadeUp .45s cubic-bezier(.22,1,.36,1) both;
    animation-delay: var(--vdelay, 0s);
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ══════════════════════
   LABELS
══════════════════════ */
.form-label {
    font-weight: 700;
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #374151;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.form-label svg {
    width: 14px;
    height: 14px;
    color: #ef4444;
    flex-shrink: 0;
}

/* ══════════════════════
   VITAL CARD
══════════════════════ */
.vital-card {
    position: relative;
    border-radius: 18px;
    background: #f8fafc;
    box-shadow: inset 0 0 0 1.5px #e5e7eb;
    overflow: hidden;
    transition: box-shadow .25s, transform .2s;
}

.vital-card:focus-within {
    background: #fff;
    box-shadow: 0 0 0 4px rgba(37,99,235,.12), inset 0 0 0 1.5px transparent;
    transform: translateY(-2px);
}

.vital-card:hover:not(:focus-within) {
    box-shadow: inset 0 0 0 1.5px #93c5fd;
}

.vital-input-wrap {
    position: relative;
    z-index: 2;
}

.vital-input {
    width: 100%;
    height: 52px;
    background: transparent;
    border: none;
    outline: none;
    padding: 0 18px;
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
}

.vital-input::placeholder {
    color: #9ca3af;
    font-weight: 400;
}

/* Wave decoration */
.vital-wave {
    position: absolute;
    bottom: 6px;
    right: 10px;
    width: 70px;
    height: 22px;
    color: #2563eb;
    opacity: .18;
    z-index: 1;
    animation: waveScroll 3s linear infinite;
}

.green-wave  { color: #16a34a; }
.orange-wave { color: #ea580c; }

@keyframes waveScroll {
    0%   { transform: translateX(0); }
    50%  { transform: translateX(-4px); }
    100% { transform: translateX(0); }
}

/* ══════════════════════
   TEXTAREA
══════════════════════ */
.textarea-wrap {
    position: relative;
}

.premium-textarea {
    width: 100%;
    border: none;
    border-radius: 18px;
    padding: 16px 20px;
    background: #f8fafc;
    box-shadow: inset 0 0 0 1.5px #e5e7eb;
    resize: none;
    font-size: .92rem;
    font-family: inherit;
    color: #111827;
    line-height: 1.6;
    min-height: 120px;
    outline: none;
    transition: background .25s, box-shadow .25s, transform .2s;
}

.premium-textarea::placeholder { color: #9ca3af; }

.premium-textarea:focus {
    background: #fff;
    box-shadow: 0 0 0 4px rgba(37,99,235,.12), inset 0 0 0 1.5px transparent;
    transform: translateY(-1px);
}

.textarea-line {
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    border-radius: 0 0 18px 18px;
    background: linear-gradient(90deg, #ef4444, #f87171);
    transition: width .35s cubic-bezier(.22,1,.36,1), left .35s;
    pointer-events: none;
}

.textarea-wrap:focus-within .textarea-line {
    width: 100%;
    left: 0;
}

/* ══════════════════════
   ESTADO OPTIONS
══════════════════════ */
.estado-grid {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
}

.estado-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 13px 20px;
    border-radius: 16px;
    background: #f8fafc;
    border: 2px solid #e5e7eb;
    cursor: pointer;
    font-weight: 600;
    font-size: .9rem;
    color: #374151;
    transition: all .22s cubic-bezier(.22,1,.36,1);
    user-select: none;
    flex: 1;
    min-width: 140px;
}

.estado-option:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,.07);
}

.estado-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #d1d5db;
    transition: background .2s, transform .2s;
    flex-shrink: 0;
}

.estado-emoji { font-size: 1.1rem; }
.estado-label { flex: 1; }

.estado-check {
    width: 16px;
    height: 16px;
    opacity: 0;
    transform: scale(0);
    transition: opacity .2s, transform .25s cubic-bezier(.22,1,.36,1);
    flex-shrink: 0;
}

/* Active states */
.estado-option.active { border-width: 2px; }

.opt-red.active  { background: #fef2f2; border-color: #ef4444; color: #b91c1c; }
.opt-red.active .estado-dot  { background: #ef4444; transform: scale(1.3); }
.opt-red.active .estado-check { opacity: 1; transform: scale(1); color: #ef4444; }

.opt-yellow.active { background: #fffbeb; border-color: #f59e0b; color: #92400e; }
.opt-yellow.active .estado-dot  { background: #f59e0b; transform: scale(1.3); }
.opt-yellow.active .estado-check { opacity: 1; transform: scale(1); color: #f59e0b; }

.opt-green.active { background: #f0fdf4; border-color: #22c55e; color: #166534; }
.opt-green.active .estado-dot  { background: #22c55e; transform: scale(1.3); }
.opt-green.active .estado-check { opacity: 1; transform: scale(1); color: #22c55e; }

/* ══════════════════════
   BOTONES
══════════════════════ */
.action-row {
    display: flex;
    justify-content: flex-end;
    gap: 14px;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: none;
    border-radius: 16px;
    padding: 14px 26px;
    font-weight: 700;
    font-size: .9rem;
    cursor: pointer;
    transition: transform .2s, box-shadow .2s;
}

.btn svg { width: 17px; height: 17px; }

.btn:hover { transform: translateY(-3px); }
.btn:active { transform: translateY(0); }

.cancel-btn {
    background: #fff;
    color: #374151;
    box-shadow: 0 4px 14px rgba(0,0,0,.07);
}
.cancel-btn:hover { box-shadow: 0 8px 20px rgba(0,0,0,.1); }

.save-btn {
    background: linear-gradient(135deg, #2563eb, #38bdf8);
    color: #fff;
    box-shadow: 0 8px 24px rgba(37,99,235,.3);
    position: relative;
    overflow: hidden;
}

.save-btn::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #1d4ed8, #0ea5e9);
    opacity: 0;
    transition: opacity .25s;
}

.save-btn:hover::after { opacity: 1; }
.save-btn:hover { box-shadow: 0 12px 30px rgba(37,99,235,.4); }
.save-btn > * { position: relative; z-index: 1; }

.save-btn.saving {
    pointer-events: none;
    opacity: .85;
}

.spin-icon {
    animation: spin .8s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}
</style>