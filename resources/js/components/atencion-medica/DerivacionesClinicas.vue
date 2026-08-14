<template>

    <div class="d-flex justify-content-between align-items-center">
    
        <div>

            <h1 class="font-weight-bold">
                Derivaciones Médicas
            </h1>

            <small class="text-muted">
                Canalización de pacientes
            </small>

        </div>

        <button class="btn btn-warning btn-lg rounded-pill shadow" 
            @click="abrirModal">
                <i class="fas fa-share-alt mr-1">
                    </i> Derivar Paciente
            </button>

    </div>



    <div class="row g-3 mb-4">
        <!-- Tarjeta 1: Derivaciones Activas -->
        <div class="col-md-4">
            <div 
                class="card border-0 text-dark rounded-4 shadow-sm h-100"
                style="background: rgba(255, 255, 255, 0.65); backdrop-filter: blur(10px);"
            >
                <div class="card-body d-flex align-items-center">
                <div 
                    class="bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center me-3"
                    style="width: 50px; height: 50px; flex-shrink: 0;"
                >
                    <i class="fas fa-share-square fa-lg"></i>
                </div>
                <div>
                    <small class="text-muted fw-semibold d-block">Derivaciones Activas</small>
                    <h4 class="fw-bold mb-0 text-dark">
                    {{ estadisticas.total_derivaciones }}
                    </h4>
                </div>
                </div>
            </div>
        </div>

            <!-- Tarjeta 2: Alta Prioridad -->
            <div class="col-md-4">
                <div 
                    class="card border-0 text-dark rounded-4 shadow-sm h-100"
                    style="background: rgba(255, 255, 255, 0.65); backdrop-filter: blur(10px);"
                >
                    <div class="card-body d-flex align-items-center">
                    <div 
                        class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 50px; height: 50px; flex-shrink: 0;"
                    >
                        <i class="fas fa-exclamation-triangle fa-lg"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-semibold d-block">Casos críticos</small>
                        <h4 class="fw-bold mb-0 text-dark">
                        {{ estadisticas.casos_criticos }}
                        </h4>
                    </div>
                    </div>
                </div>
            </div>

        <!-- Tarjeta 3: Canalizados -->
        <div class="col-md-4">
        <div 
            class="card border-0 text-dark rounded-4 shadow-sm h-100"
            style="background: rgba(255, 255, 255, 0.65); backdrop-filter: blur(10px);"
        >
            <div class="card-body d-flex align-items-center">
            <div 
                class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center me-3"
                style="width: 50px; height: 50px; flex-shrink: 0;"
            >
                <i class="fas fa-paper-plane fa-lg"></i>
            </div>
            <div>
                <small class="text-muted fw-semibold d-block">Canalizados</small>
                <h4 class="fw-bold mb-0 text-dark">
                {{ estadisticas.canalizados }}
                </h4>
            </div>
            </div>
        </div>
        </div>
  </div>



    <!-- ── Botón para abrir el modal ── -->
    <div>
        

        <!-- ── Overlay ── -->
        <transition name="modal-fade">
            <div v-if="modalVisible" class="modal-overlay" @click.self="cerrarModal">

                <!-- ── Modal ── -->
                <div class="modal-container">

                    <!-- Header -->
                    <div class="modal-header-custom">
                        <div class="modal-header-icon">
                            <i class="fas fa-share-alt"></i>
                        </div>
                        <div class="modal-header-text">
                            <h4>Nueva Derivación</h4>
                            <p>Completa los datos para derivar al paciente</p>
                        </div>
                        <button class="modal-close-btn" @click="cerrarModal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Progreso pasos -->
                    <div class="modal-steps">
                        <div
                            v-for="(step, i) in steps"
                            :key="i"
                            class="step-item"
                            :class="{ active: pasoActual === i, done: pasoActual > i }"
                        >
                            <div class="step-circle">
                                <i v-if="pasoActual > i" class="fas fa-check"></i>
                                <span v-else>{{ i + 1 }}</span>
                            </div>
                            <span class="step-label">{{ step }}</span>
                        </div>
                        <div class="step-line"></div>
                    </div>

                    <!-- Body -->
                    <div class="modal-body-custom">

                        <!-- Paso 1: Paciente y Especialidad -->
                        <div v-if="pasoActual === 0">
                            <div class="section-title">
                                <i class="fas fa-user-injured text-warning"></i>
                                Información del Paciente
                            </div>

                            <div class="form-group-custom">
                                <label>
                                    <i class="fas fa-user"></i>
                                    Nombre del Paciente <span class="required">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-search input-icon"></i>
                                    <input
                                        type="text"
                                        class="form-input"
                                        :class="{ 'input-error': errores.paciente }"
                                        v-model="form.paciente"
                                        placeholder="Buscar paciente por nombre..."
                                        @input="errores.paciente = false"
                                    />
                                </div>
                                <p v-if="errores.paciente" class="error-msg">
                                    <i class="fas fa-exclamation-circle"></i> Campo requerido
                                </p>
                            </div>

                            <div class="section-title mt-3">
                                <i class="fas fa-stethoscope text-primary"></i>
                                Especialidad Médica
                            </div>

                            <div class="form-group-custom">
                                <label>
                                    <i class="fas fa-hospital"></i>
                                    Especialidad <span class="required">*</span>
                                </label>
                                <div class="specialties-grid">
                                    <div
                                        v-for="esp in especialidades"
                                        :key="esp.value"
                                        class="specialty-card"
                                        :class="{ selected: form.especialidad === esp.value }"
                                        @click="form.especialidad = esp.value; errores.especialidad = false"
                                    >
                                        <i :class="esp.icon"></i>
                                        <span>{{ esp.label }}</span>
                                    </div>
                                </div>
                                <p v-if="errores.especialidad" class="error-msg">
                                    <i class="fas fa-exclamation-circle"></i> Selecciona una especialidad
                                </p>
                            </div>
                        </div>

                        <!-- Paso 2: Motivo, Prioridad, Estado -->
                        <div v-if="pasoActual === 1">
                            <div class="section-title">
                                <i class="fas fa-clipboard-list text-info"></i>
                                Detalles de la Derivación
                            </div>

                            <div class="form-group-custom">
                                <label>
                                    <i class="fas fa-comment-medical"></i>
                                    Motivo de Derivación <span class="required">*</span>
                                </label>
                                <textarea
                                    class="form-input textarea"
                                    :class="{ 'input-error': errores.motivo }"
                                    v-model="form.motivo"
                                    placeholder="Describe el motivo clínico de la derivación..."
                                    rows="4"
                                    @input="errores.motivo = false"
                                ></textarea>
                                <div class="char-count" :class="{ 'char-warn': form.motivo.length > 220 }">
                                    {{ form.motivo.length }}/250
                                </div>
                                <p v-if="errores.motivo" class="error-msg">
                                    <i class="fas fa-exclamation-circle"></i> Campo requerido
                                </p>
                            </div>

                            <div class="row-two">
                                <!-- Prioridad -->
                                <div class="form-group-custom">
                                    <label>
                                        <i class="fas fa-flag"></i>
                                        Prioridad <span class="required">*</span>
                                    </label>
                                    <div class="priority-options">
                                        <div
                                            v-for="p in prioridades"
                                            :key="p.value"
                                            class="priority-btn"
                                            :class="[p.cls, { selected: form.prioridad === p.value }]"
                                            @click="form.prioridad = p.value; errores.prioridad = false"
                                        >
                                            <i :class="p.icon"></i>
                                            {{ p.label }}
                                        </div>
                                    </div>
                                    <p v-if="errores.prioridad" class="error-msg">
                                        <i class="fas fa-exclamation-circle"></i> Selecciona prioridad
                                    </p>
                                </div>

                                <!-- Estado -->
                                <div class="form-group-custom">
                                    <label>
                                        <i class="fas fa-tasks"></i>
                                        Estado Inicial <span class="required">*</span>
                                    </label>
                                    <div class="input-icon-wrap">
                                        <i class="fas fa-chevron-down input-icon" style="pointer-events:none;"></i>
                                        <select
                                            class="form-input select-input"
                                            :class="{ 'input-error': errores.estado }"
                                            v-model="form.estado"
                                            @change="errores.estado = false"
                                        >
                                            <option value="">Seleccionar estado</option>
                                            <option v-for="e in estados" :key="e.value" :value="e.value">
                                                {{ e.label }}
                                            </option>
                                        </select>
                                    </div>
                                    <p v-if="errores.estado" class="error-msg">
                                        <i class="fas fa-exclamation-circle"></i> Selecciona un estado
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 3: Confirmación -->
                        <div v-if="pasoActual === 2">
                            <div class="confirm-header">
                                <div class="confirm-icon">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                                <h5>Confirma la Derivación</h5>
                                <p>Revisa los datos antes de guardar</p>
                            </div>

                            <div class="confirm-grid">
                                <div class="confirm-item">
                                    <span class="confirm-label">
                                        <i class="fas fa-user text-warning"></i> Paciente
                                    </span>
                                    <span class="confirm-value">{{ form.paciente }}</span>
                                </div>
                                <div class="confirm-item">
                                    <span class="confirm-label">
                                        <i class="fas fa-stethoscope text-primary"></i> Especialidad
                                    </span>
                                    <span class="confirm-value">{{ form.especialidad }}</span>
                                </div>
                                <div class="confirm-item full">
                                    <span class="confirm-label">
                                        <i class="fas fa-comment-medical text-info"></i> Motivo
                                    </span>
                                    <span class="confirm-value">{{ form.motivo }}</span>
                                </div>
                                <div class="confirm-item">
                                    <span class="confirm-label">
                                        <i class="fas fa-flag"></i> Prioridad
                                    </span>
                                    <span
                                        class="badge-pill"
                                        :class="badgePrioridad"
                                    >{{ form.prioridad }}</span>
                                </div>
                                <div class="confirm-item">
                                    <span class="confirm-label">
                                        <i class="fas fa-tasks"></i> Estado
                                    </span>
                                    <span class="badge-pill badge-estado">{{ form.estado }}</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="modal-footer-custom">
                        <button class="btn-secondary-custom" @click="pasoAnterior" :disabled="pasoActual === 0">
                            <i class="fas fa-arrow-left"></i> Anterior
                        </button>
                        <div class="footer-right">
                            <button class="btn-cancel" @click="cerrarModal">
                                Cancelar
                            </button>
                            <button
                                v-if="pasoActual < 2"
                                class="btn-primary-custom"
                                @click="siguientePaso"
                            >
                                Siguiente <i class="fas fa-arrow-right"></i>
                            </button>
                            <button
                                v-else
                                class="btn-success-custom"
                                @click="guardarDerivacion"
                                :disabled="guardando"
                            >
                                <i v-if="guardando" class="fas fa-spinner fa-spin"></i>
                                <i v-else class="fas fa-check"></i>
                                {{ guardando ? 'Guardando...' : 'Confirmar Derivación' }}
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </transition>
    </div>
</template>

<script>

import ApiService from '../../services/ApiService.js' 

export default {
    name: 'ModalDerivacion',

    props: {
        pacienteInicial: { type: String, default: '' }
    },

    emits: ['derivacion-guardada'],

    data() {
        return {
            estadisticas: {
                total_derivaciones: 0,
                casos_criticos: 0,
                canalizados: 0
            },
            modalVisible: false,
            pasoActual: 0,
            guardando: false,

            steps: ['Paciente', 'Detalles', 'Confirmación'],

            form: {
                paciente:    '',
                especialidad:'',
                motivo:      '',
                prioridad:   '',
                estado:      '',
            },

            errores: {
                paciente:    false,
                especialidad:false,
                motivo:      false,
                prioridad:   false,
                estado:      false,
            },

            especialidades: [
                { value: 'Cardiología',       label: 'Cardiología',       icon: 'fas fa-heartbeat' },
                { value: 'Neurología',         label: 'Neurología',         icon: 'fas fa-brain' },
                { value: 'Gastroenterología',  label: 'Gastro',             icon: 'fas fa-procedures' },
                { value: 'Traumatología',      label: 'Traumato',           icon: 'fas fa-bone' },
                { value: 'Pediatría',          label: 'Pediatría',          icon: 'fas fa-baby' },
                { value: 'Psiquiatría',        label: 'Psiquiatría',        icon: 'fas fa-head-side-virus' },
                { value: 'Oftalmología',       label: 'Oftalmología',       icon: 'fas fa-eye' },
                { value: 'Dermatología',       label: 'Dermatología',       icon: 'fas fa-allergies' },
            ],

            prioridades: [
                { value: 'Baja',    label: 'Baja',    cls: 'prio-baja',    icon: 'fas fa-arrow-down' },
                { value: 'Media',   label: 'Media',   cls: 'prio-media',   icon: 'fas fa-equals' },
                { value: 'Alta',    label: 'Alta',    cls: 'prio-alta',    icon: 'fas fa-arrow-up' },
                { value: 'Urgente', label: 'Urgente', cls: 'prio-urgente', icon: 'fas fa-exclamation' },
            ],

            estados: [
                { value: 'Pendiente',  label: '🕐 Pendiente' },
                { value: 'En proceso', label: '🔄 En proceso' },
                { value: 'Completada', label: '✅ Completada' },
                { value: 'Cancelada',  label: '❌ Cancelada' },
            ],
        };
    },

    computed: {
        badgePrioridad() {
            const map = {
                'Baja':    'badge-baja',
                'Media':   'badge-media',
                'Alta':    'badge-alta',
                'Urgente': 'badge-urgente',
            };
            return map[this.form.prioridad] || '';
        }
    },

    mounted(){

        this.obtenerEstadisticas();

    },

    methods: {

        // Método que consulta el Backend para traer los números
        async obtenerEstadisticas() {
            try {
                const response = await ApiService.get('/derivaciones/estadisticas');
                this.estadisticas = response.data;
            } catch (error) {
                console.error(error);
            }
        },
        abrirModal() {
            if (this.pacienteInicial) this.form.paciente = this.pacienteInicial;
            this.modalVisible = true;
            document.body.style.overflow = 'hidden';
        },

        cerrarModal() {
            this.modalVisible = false;
            document.body.style.overflow = '';
            this.resetForm();
        },

        resetForm() {
            this.pasoActual = 0;
            this.guardando  = false;
            this.form       = { paciente:'', especialidad:'', motivo:'', prioridad:'', estado:'' };
            this.errores    = { paciente:false, especialidad:false, motivo:false, prioridad:false, estado:false };
        },

        siguientePaso() {
            if (this.pasoActual === 0) {
                let ok = true;
                if (!this.form.paciente.trim())  { this.errores.paciente    = true; ok = false; }
                if (!this.form.especialidad)      { this.errores.especialidad = true; ok = false; }
                if (!ok) return;
            }
            if (this.pasoActual === 1) {
                let ok = true;
                if (!this.form.motivo.trim())     { this.errores.motivo    = true; ok = false; }
                if (!this.form.prioridad)         { this.errores.prioridad = true; ok = false; }
                if (!this.form.estado)            { this.errores.estado    = true; ok = false; }
                if (!ok) return;
            }
            this.pasoActual++;
        },

        pasoAnterior() {
            if (this.pasoActual > 0) this.pasoActual--;
        },

        async guardarDerivacion() {
            this.guardando = true;
            try {
                // Ajusta la URL a tu ruta de Laravel
                const response = await fetch('/derivaciones', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(this.form),
                });

                if (response.ok) {
                    this.$emit('derivacion-guardada', this.form);
                    this.cerrarModal();
                } else {
                    alert('Error al guardar. Intenta de nuevo.');
                }
            } catch (e) {
                alert('Error de conexión. Intenta de nuevo.');
            } finally {
                this.guardando = false;
            }
        },
    },
};
</script>

<style scoped>
/* ── Overlay ── */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .55);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    backdrop-filter: blur(3px);
}

/* ── Container ── */
.modal-container {
    background: #fff;
    border-radius: 14px;
    width: 100%;
    max-width: 620px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,.3);
    display: flex;
    flex-direction: column;
}

/* ── Header ── */
.modal-header-custom {
    background: linear-gradient(135deg, #fd7e14, #e67300);
    border-radius: 14px 14px 0 0;
    padding: 22px 26px;
    display: flex;
    align-items: center;
    gap: 16px;
    position: relative;
}
.modal-header-icon {
    width: 46px; height: 46px;
    background: rgba(255,255,255,.2);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: #fff;
    flex-shrink: 0;
}
.modal-header-text h4 {
    color: #fff; font-size: 18px;
    font-weight: 700; margin: 0 0 2px;
}
.modal-header-text p {
    color: rgba(255,255,255,.8);
    font-size: 13px; margin: 0;
}
.modal-close-btn {
    margin-left: auto;
    background: rgba(255,255,255,.2);
    border: none; border-radius: 8px;
    width: 34px; height: 34px;
    color: #fff; font-size: 15px;
    cursor: pointer; transition: background .2s;
    display: flex; align-items: center; justify-content: center;
}
.modal-close-btn:hover { background: rgba(255,255,255,.35); }

/* ── Steps ── */
.modal-steps {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 18px 26px 10px;
    gap: 0;
    position: relative;
}
.step-line {
    position: absolute;
    top: 50%; left: 15%; right: 15%;
    height: 2px;
    background: #dee2e6;
    z-index: 0;
    transform: translateY(-50%);
}
.step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    flex: 1;
    z-index: 1;
}
.step-circle {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: #dee2e6;
    color: #6c757d;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700;
    transition: all .3s;
    border: 2px solid #dee2e6;
}
.step-item.active .step-circle {
    background: #fd7e14;
    border-color: #fd7e14;
    color: #fff;
    box-shadow: 0 0 0 4px rgba(253,126,20,.2);
}
.step-item.done .step-circle {
    background: #28a745;
    border-color: #28a745;
    color: #fff;
}
.step-label {
    font-size: 11px; font-weight: 600;
    color: #6c757d; text-transform: uppercase;
    letter-spacing: .05em;
}
.step-item.active .step-label { color: #fd7e14; }
.step-item.done  .step-label  { color: #28a745; }

/* ── Body ── */
.modal-body-custom {
    padding: 20px 26px;
    flex: 1;
}

.section-title {
    font-size: 13px; font-weight: 700;
    color: #343a40;
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: 14px;
    display: flex; align-items: center; gap: 8px;
    padding-bottom: 8px;
    border-bottom: 2px solid #f8f9fa;
}
.section-title.mt-3 { margin-top: 20px; }

/* Form groups */
.form-group-custom { margin-bottom: 16px; }
.form-group-custom label {
    display: flex; align-items: center; gap: 7px;
    font-size: 12px; font-weight: 700;
    color: #6c757d; margin-bottom: 7px;
    text-transform: uppercase; letter-spacing: .05em;
}
.required { color: #dc3545; }

.input-icon-wrap { position: relative; }
.input-icon {
    position: absolute; left: 13px; top: 50%;
    transform: translateY(-50%);
    color: #17a2b8; font-size: 13px;
}
.form-input {
    width: 100%;
    border: 1.5px solid #dee2e6;
    border-radius: 8px;
    padding: 10px 12px 10px 38px;
    font-size: 14px;
    color: #343a40;
    font-family: inherit;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    background: #fff;
}
.form-input:focus {
    border-color: #fd7e14;
    box-shadow: 0 0 0 3px rgba(253,126,20,.12);
}
.form-input::placeholder { color: #adb5bd; }
.form-input.input-error { border-color: #dc3545; }
.textarea {
    resize: vertical; min-height: 90px;
    padding-top: 10px; line-height: 1.5;
}
.select-input { appearance: none; cursor: pointer; }
.char-count {
    text-align: right; font-size: 11px;
    color: #adb5bd; margin-top: 3px;
}
.char-warn { color: #dc3545; }
.error-msg {
    font-size: 12px; color: #dc3545;
    margin-top: 5px; display: flex;
    align-items: center; gap: 5px;
}

/* Specialties grid */
.specialties-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
}
.specialty-card {
    border: 1.5px solid #dee2e6;
    border-radius: 8px;
    padding: 10px 6px;
    display: flex; flex-direction: column;
    align-items: center; gap: 6px;
    cursor: pointer;
    transition: all .2s;
    font-size: 12px; font-weight: 600;
    color: #6c757d;
    text-align: center;
}
.specialty-card i { font-size: 18px; color: #adb5bd; transition: color .2s; }
.specialty-card:hover {
    border-color: #fd7e14;
    background: #fff8f2;
    color: #fd7e14;
}
.specialty-card:hover i { color: #fd7e14; }
.specialty-card.selected {
    border-color: #fd7e14;
    background: linear-gradient(135deg, #fff3e6, #fff8f2);
    color: #fd7e14;
    box-shadow: 0 2px 10px rgba(253,126,20,.2);
}
.specialty-card.selected i { color: #fd7e14; }

/* Row two columns */
.row-two {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

/* Priority buttons */
.priority-options {
    display: grid;
    grid-template-columns: repeat(4,1fr);
    gap: 7px;
}
.priority-btn {
    border: 1.5px solid #dee2e6;
    border-radius: 8px;
    padding: 9px 6px;
    text-align: center;
    font-size: 12px; font-weight: 700;
    cursor: pointer;
    transition: all .2s;
    display: flex; flex-direction: column;
    align-items: center; gap: 4px;
    color: #6c757d;
}
.priority-btn i { font-size: 13px; }
.priority-btn:hover { transform: translateY(-1px); }

.prio-baja.selected   { background:#d4edda; border-color:#28a745; color:#155724; }
.prio-media.selected  { background:#fff3cd; border-color:#ffc107; color:#856404; }
.prio-alta.selected   { background:#fde8d8; border-color:#fd7e14; color:#7d3c00; }
.prio-urgente.selected{ background:#f8d7da; border-color:#dc3545; color:#721c24; }
.prio-baja:hover      { border-color:#28a745; color:#28a745; }
.prio-media:hover     { border-color:#ffc107; color:#ffc107; }
.prio-alta:hover      { border-color:#fd7e14; color:#fd7e14; }
.prio-urgente:hover   { border-color:#dc3545; color:#dc3545; }

/* ── Confirmation ── */
.confirm-header {
    text-align: center; padding: 8px 0 20px;
}
.confirm-icon {
    width: 60px; height: 60px;
    background: linear-gradient(135deg,#fd7e14,#e67300);
    border-radius: 50%;
    margin: 0 auto 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: #fff;
    box-shadow: 0 4px 16px rgba(253,126,20,.35);
}
.confirm-header h5 {
    font-size: 17px; font-weight: 700;
    color: #343a40; margin-bottom: 4px;
}
.confirm-header p { font-size: 13px; color: #6c757d; }

.confirm-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.confirm-item {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 12px 14px;
}
.confirm-item.full { grid-column: 1 / -1; }
.confirm-label {
    display: flex; align-items: center; gap: 6px;
    font-size: 11px; font-weight: 700;
    color: #6c757d; text-transform: uppercase;
    letter-spacing: .06em; margin-bottom: 5px;
}
.confirm-value {
    display: block; font-size: 14px;
    font-weight: 600; color: #343a40;
}

/* Badges */
.badge-pill {
    display: inline-flex; align-items: center;
    padding: 4px 12px; border-radius: 50px;
    font-size: 12px; font-weight: 700;
}
.badge-baja    { background:#d4edda; color:#155724; }
.badge-media   { background:#fff3cd; color:#856404; }
.badge-alta    { background:#fde8d8; color:#7d3c00; }
.badge-urgente { background:#f8d7da; color:#721c24; }
.badge-estado  { background:#cce5ff; color:#004085; }

/* ── Footer ── */
.modal-footer-custom {
    padding: 16px 26px;
    border-top: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}
.footer-right { display: flex; gap: 10px; }

.btn-secondary-custom {
    display: flex; align-items: center; gap: 7px;
    padding: 9px 18px;
    background: #f8f9fa; border: 1.5px solid #dee2e6;
    border-radius: 8px;
    font-size: 14px; font-weight: 600;
    color: #6c757d; cursor: pointer;
    transition: all .2s;
}
.btn-secondary-custom:hover:not(:disabled) {
    background: #e9ecef; color: #343a40;
}
.btn-secondary-custom:disabled { opacity: .45; cursor: not-allowed; }

.btn-cancel {
    padding: 9px 18px;
    background: transparent; border: 1.5px solid #dee2e6;
    border-radius: 8px;
    font-size: 14px; font-weight: 600;
    color: #6c757d; cursor: pointer;
    transition: all .2s;
}
.btn-cancel:hover { background: #f8f9fa; }

.btn-primary-custom {
    display: flex; align-items: center; gap: 7px;
    padding: 9px 22px;
    background: linear-gradient(135deg,#007bff,#17a2b8);
    border: none; border-radius: 8px;
    font-size: 14px; font-weight: 700;
    color: #fff; cursor: pointer;
    transition: all .2s;
    box-shadow: 0 3px 12px rgba(0,123,255,.3);
}
.btn-primary-custom:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 18px rgba(0,123,255,.4);
}

.btn-success-custom {
    display: flex; align-items: center; gap: 7px;
    padding: 9px 22px;
    background: linear-gradient(135deg,#28a745,#20c997);
    border: none; border-radius: 8px;
    font-size: 14px; font-weight: 700;
    color: #fff; cursor: pointer;
    transition: all .2s;
    box-shadow: 0 3px 12px rgba(40,167,69,.3);
}
.btn-success-custom:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 5px 18px rgba(40,167,69,.4);
}
.btn-success-custom:disabled { opacity: .65; cursor: not-allowed; }

/* ── Transition ── */
.modal-fade-enter-active,
.modal-fade-leave-active { transition: all .25s ease; }
.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
    transform: scale(.96) translateY(-12px);
}
</style>

