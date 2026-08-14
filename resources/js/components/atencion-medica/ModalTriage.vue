<template>
  <div class="clinical-dashboard fade-in">

    <!-- =====================================================
         HEADER DEL PACIENTE
    ====================================================== -->
    <div class="patient-header mb-4">

      <div class="patient-header-main">

            <div class="patient-avatar">
                <i class="fas fa-user"></i>
            </div>

            <div class="patient-info">

                <div class="patient-name-row">

                    <h2 class="patient-name">
                    {{ datosPaciente.nombre || datosPaciente.paciente?.nombre || 'Paciente Sin Nombre' }}
                    </h2>

                    <span class="patient-id">
                    <i class="fas fa-id-card me-1"></i>
                    EXP #{{ datosPaciente.id || 'N/A' }}
                    </span>

                </div>

                <div class="patient-meta">

                    <span>
                    <i class="far fa-calendar me-1"></i>
                    {{ fechaFormateada }}
                    </span>

                    <span class="separator">•</span>

                    <span>
                    <i class="fas fa-stethoscope me-1"></i>
                    Evaluación de Triage
                    </span>

                </div>
            </div>
        </div>
    </div>


    <!-- =====================================================
         CARGANDO
    ====================================================== -->
    <div
      v-if="cargando"
      class="loading-card"
    >

      <div class="loading-icon">
        <i class="fas fa-heart-pulse"></i>
      </div>

      <h5>Preparando expediente clínico</h5>

      <p>
        Sincronizando signos vitales y evaluación inteligente...
      </p>

      <div class="loading-bar">
        <div></div>
      </div>

    </div>


    <!-- =====================================================
         CONTENIDO
    ====================================================== -->
    <div v-else>

      <!-- =================================================
           RESUMEN CLÍNICO
      ================================================== -->
      <div class="summary-grid mb-4">

        <!-- PRIORIDAD IA -->
        <div
          class="summary-card priority-card"
          :class="claseBordeIA"
        >

          <div class="summary-label">
            <span class="label-icon ia">
              <i class="fas fa-brain"></i>
            </span>

            <span>PRIORIDAD SUGERIDA POR IA</span>
          </div>

          <div class="summary-value-row">

            <div>

              <div class="priority-value">
                {{ prioridadTexto }}
              </div>

              <div class="summary-description">
                Clasificación automática del paciente
              </div>

            </div>

            <div class="priority-indicator">
              <span></span>
            </div>

          </div>

        </div>


        <!-- ESTADO -->
        <div class="summary-card">

        <div class="summary-label">
            <span class="label-icon status">
            <i class="fas fa-heart-pulse fa-heartbeat"></i>
            </span>
            <span>ESTADO GENERAL</span>
        </div>

        <div class="clinical-status">

            <!-- Normalizamos el texto quitando espacios y pasándolo a minúsculas -->
            <span
            v-if="String(datosTriage.estado || '').toLowerCase().trim() === 'grave'"
            class="status-badge grave"
            >
            <span class="status-dot"></span>
            GRAVE
            </span>

            <span
            v-else-if="String(datosTriage.estado || '').toLowerCase().trim() === 'moderado'"
            class="status-badge moderado"
            >
            <span class="status-dot"></span>
            MODERADO
            </span>

            <span
            v-else
            class="status-badge leve"
            >
            <span class="status-dot"></span>
            {{ (datosTriage.estado || 'LEVE').toUpperCase() }}
            </span>

        </div>

        <div class="summary-description">
            Estado registrado durante el triage
        </div>

        </div>


        <!-- TIEMPO -->
        <div class="summary-card">

          <div class="summary-label">
            <span class="label-icon time">
              <i class="fas fa-stopwatch"></i>
            </span>

            <span>TIEMPO DE ESPERA</span>
          </div>

          <div
            v-if="esperaData"
            class="waiting-time"
            :class="esperaData.claseCss"
          >
            {{ esperaData.texto }}
          </div>

          <div class="summary-description">
            Tiempo máximo recomendado de atención
          </div>

        </div>

      </div>


      <!-- =================================================
           SIGNOS VITALES
      ================================================== -->
      <section class="clinical-section mb-4">

        <div class="section-header">

          <div>

            <div class="section-title">
              <i class="fas fa-wave-square"></i>
              Signos vitales
            </div>

            <div class="section-subtitle">
              Constantes registradas durante el triage
            </div>

          </div>

          <span class="units-badge">
            <i class="fas fa-ruler-combined me-1"></i>
            Sistema métrico
          </span>

        </div>


        <div class="vitals-grid">


          <!-- PRESIÓN -->
          <div class="vital-card pressure">

            <div class="vital-top">

              <div class="vital-icon">
                <i class="fas fa-heartbeat"></i>
              </div>

              <div class="vital-title">
                <span>Presión arterial</span>
                <small>PA</small>
              </div>

            </div>

            <div class="vital-value">

              <strong>
                {{ datosTriage.presion || 'N/R' }}
              </strong>

              <span>mmHg</span>

            </div>

            <div class="vital-reference">

              <span>
                <i class="fas fa-circle-check"></i>
                Referencia
              </span>

              <strong>120/80</strong>

            </div>

          </div>


          <!-- SATURACIÓN -->
          <div class="vital-card oxygen">

            <div class="vital-top">

              <div class="vital-icon">
                <i class="fas fa-lungs"></i>
              </div>

              <div class="vital-title">
                <span>Saturación de oxígeno</span>
                <small>SpO₂</small>
              </div>

            </div>

            <div class="vital-value">

              <strong>
                {{ datosTriage.saturacion ? datosTriage.saturacion : 'N/R' }}
              </strong>

              <span>%</span>

            </div>

            <div class="vital-progress">

              <div
                class="progress-fill"
                :class="porcentajeSaturacion < 90 ? 'danger' : 'normal'"
                :style="{
                  width: Math.min(porcentajeSaturacion, 100) + '%'
                }"
              ></div>

            </div>

            <div class="vital-reference">

              <span>
                <i class="fas fa-circle-check"></i>
                Normal
              </span>

              <strong>95–100%</strong>

            </div>

          </div>


          <!-- TEMPERATURA -->
          <div class="vital-card temperature">

            <div class="vital-top">

              <div class="vital-icon">
                <i class="fas fa-thermometer-half"></i>
              </div>

              <div class="vital-title">
                <span>Temperatura corporal</span>
                <small>TEMP</small>
              </div>

            </div>

            <div class="vital-value">

              <strong>
                {{ datosTriage.temperatura || 'N/R' }}
              </strong>

              <span>°C</span>

            </div>

            <div class="vital-progress">

              <div
                class="progress-fill temperature-fill"
                :style="{
                  width: calcularPorcentajeTemp(datosTriage.temperatura) + '%'
                }"
              ></div>

            </div>

            <div class="vital-reference">

              <span>
                <i class="fas fa-circle-check"></i>
                Normal
              </span>

              <strong>36.5–37.5 °C</strong>

            </div>

          </div>

        </div>

      </section>


        <!-- =================================================
        MOTIVO DE CONSULTA
        ================================================== -->
        <section class="clinical-section mb-4">

            <div class="section-header">

                <div>

                    <div class="section-title">
                        <i class="fas fa-notes-medical"></i>
                        Motivo de consulta
                    </div>

                    <div class="section-subtitle">
                        Sintomatología reportada por el paciente
                    </div>

                </div>

            </div>

            <div class="symptoms-box">

                <div class="symptoms-icon">
                    <i class="fas fa-quote-left"></i>
                </div>

                <p>
                    {{
                        datosTriage.sintomas ||
                        'No se registraron síntomas descriptivos para este paciente.'
                    }}
                </p>

            </div>

        </section>


      <!-- =================================================
           INTELIGENCIA CLÍNICA
      ================================================== -->
        <section class="clinical-section w-100">
            <div class="section-header d-flex justify-content-between align-items-center mb-3">
                <div>
                <div class="section-title fw-bold">
                    <i class="fas fa-brain me-2"></i>
                    Inteligencia clínica
                </div>
                <div class="section-subtitle text-muted small">
                    Análisis y recomendaciones generadas por el sistema
                </div>
                </div>

                <span class="ai-badge">
                <i class="fas fa-sparkles me-1"></i>
                IA ACTIVA
                </span>
            </div>

            <!-- Reemplazamos ai-grid por una fila fluida de Bootstrap -->
            <div class="row g-3 w-100 m-0">

                <div class="col-12 col-md-6 p-0 pe-md-2">
                <AlertasClinicasIA
                    class="w-100"
                    :iaData="{
                    alertas: detalletriage.alertas || []
                    }"
                />
                </div>

                <div class="col-12 col-md-6 p-0 ps-md-2">
                <RecomendacionesIA
                    class="w-100"
                    :iaData="{
                    recomendaciones: detalletriage.recomendaciones || []
                    }"
                />
                </div>
            </div>
        </section>

    </div>
  </div>
</template>


<script>

import AlertasClinicasIA from './AlertasClinicasIA.vue'
import RecomendacionesIA from './RecomendacionesIA.vue'

export default {

  name: 'ModalTriage',

  components: {
    AlertasClinicasIA,
    RecomendacionesIA
  },

  props: {

    detalletriage: {
      type: Object,
      default: () => ({})
    },

    cargando: {
      type: Boolean,
      default: false
    },

    obtenerPrioridad: {
      type: Function,
      required: true
    },

    mapearEstadoAPrioridad: {
      type: Function,
      required: true
    },

    obtenerEspera: {
      type: Function,
      required: true
    }

  },

  computed: {

    datosPaciente() {
      return this.detalletriage || {}
    },

    datosTriage() {

      if (
        this.detalletriage.triages &&
        this.detalletriage.triages.length > 0
      ) {
        return this.detalletriage.triages[0]
      }

      return this.detalletriage || {}
    },

    prioridadTexto() {

      const prio =
        this.detalletriage._ia?.prioridad ??
        this.mapearEstadoAPrioridad(this.datosTriage.estado)

      return this.obtenerPrioridad(prio)?.texto || 'Sin definir'
    },

    claseBordeIA() {

      const prio = (
        this.detalletriage._ia?.prioridad ??
        this.mapearEstadoAPrioridad(this.datosTriage.estado)
      )?.toLowerCase()

      const map = {

        rojo: 'ia-rojo',
        naranja: 'ia-naranja',
        amarillo: 'ia-amarillo',
        verde: 'ia-verde',
        azul: 'ia-azul'

      }

      return map[prio] || 'ia-verde'
    },

    esperaData() {

      return this.obtenerEspera(
        this.datosTriage.estado,
        this.datosTriage.created_at,
        this.detalletriage.estado_consulta ??
        this.datosTriage.estado_consulta
      )

    },

    porcentajeSaturacion() {

      return parseFloat(
        this.datosTriage.saturacion
      ) || 0

    },

    fechaFormateada() {

      const fecha = this.datosTriage.created_at

      if (!fecha) {
        return 'Fecha no disponible'
      }

      return new Date(fecha).toLocaleString(
        'es-MX',
        {
          dateStyle: 'medium',
          timeStyle: 'short'
        }
      )

    }

  },

  methods: {

    calcularPorcentajeTemp(temp) {

      const t = parseFloat(temp)

      if (isNaN(t)) {
        return 0
      }

      const min = 35
      const max = 41

      const pct =
        ((t - min) / (max - min)) * 100

      return Math.min(
        Math.max(pct, 5),
        100
      )

    }

  }

}

</script>


<style scoped>

/* =========================================================
   BASE
========================================================= */

.clinical-dashboard {

  --primary: #2563eb;
  --primary-light: #eff6ff;

  --text: #0f172a;
  --muted: #64748b;

  --border: #e2e8f0;
  --surface: #ffffff;
  --background: #f8fafc;

  color: var(--text);

}


/* =========================================================
   ANIMACIÓN
========================================================= */

.fade-in {

  animation:
    fadeIn .35s ease-out;

}

@keyframes fadeIn {

  from {

    opacity: 0;
    transform: translateY(8px);

  }

  to {

    opacity: 1;
    transform: translateY(0);

  }

}


/* =========================================================
   HEADER PACIENTE
========================================================= */

.patient-header {

  background:
    linear-gradient(
      135deg,
      #0f172a,
      #172554
    );

  border-radius: 18px;

  padding: 20px 24px;

  color: white;

  box-shadow:
    0 10px 30px rgba(15, 23, 42, .12);

}

.patient-header-main {

  display: flex;
  align-items: center;
  gap: 16px;

}

.patient-avatar {

  width: 58px;
  height: 58px;

  border-radius: 16px;

  display: flex;
  align-items: center;
  justify-content: center;

  background:
    rgba(255,255,255,.08);

  border:
    1px solid rgba(255,255,255,.15);

  color: #60a5fa;

  font-size: 23px;

}

.patient-info {

  flex: 1;

  min-width: 0;

}

.patient-name-row {

  display: flex;
  align-items: center;
  flex-wrap: wrap;

  gap: 10px;

}

.patient-name {

  font-size: 21px;
  font-weight: 800;

  margin: 0;

  letter-spacing: -.025em;

}

.patient-id {

  font-family:
    SFMono-Regular,
    Consolas,
    monospace;

  font-size: 11px;

  padding:
    5px 9px;

  border-radius: 7px;

  color: #93c5fd;

  background:
    rgba(59,130,246,.12);

  border:
    1px solid rgba(96,165,250,.2);

}

.patient-meta {

  display: flex;
  align-items: center;

  gap: 10px;

  margin-top: 5px;

  color: #94a3b8;

  font-size: 12px;

}

.patient-meta i {

  color: #60a5fa;

}

.separator {

  opacity: .5;

}

.btn-back {

  display: flex;
  align-items: center;
  gap: 8px;

  padding:
    9px 16px;

  border-radius: 10px;

  background:
    rgba(255,255,255,.08);

  border:
    1px solid rgba(255,255,255,.15);

  color: white;

  font-size: 13px;
  font-weight: 600;

  transition:
    all .2s ease;

}

.btn-back:hover {

  background:
    rgba(255,255,255,.15);

  transform:
    translateX(-2px);

}


/* =========================================================
   SUMMARY
========================================================= */

.summary-grid {

  display: grid;

  grid-template-columns:
    1.4fr
    1fr
    1fr;

  gap: 14px;

}

.summary-card {

  min-height: 145px;

  padding: 20px;

  background:
    var(--surface);

  border:
    1px solid var(--border);

  border-radius: 16px;

  box-shadow:
    0 4px 14px rgba(15,23,42,.035);

  transition:
    transform .2s ease,
    box-shadow .2s ease;

}

.summary-card:hover {

  transform:
    translateY(-2px);

  box-shadow:
    0 10px 25px rgba(15,23,42,.07);

}

.summary-label {

  display: flex;
  align-items: center;

  gap: 9px;

  color: #64748b;

  font-size: 10px;

  font-weight: 800;

  letter-spacing: .07em;

}

.label-icon {

  width: 30px;
  height: 30px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 9px;

}

.label-icon.ia {

  background: #eef2ff;
  color: #6366f1;

}

.label-icon.status {

  background: #ecfdf5;
  color: #10b981;

}

.label-icon.time {

  background: #fff7ed;
  color: #f97316;

}

.summary-value-row {

  display: flex;
  justify-content: space-between;
  align-items: center;

  margin-top: 15px;

}

.priority-value {

  font-size: 22px;

  font-weight: 800;

  letter-spacing: -.025em;

}

.summary-description {

  color: #94a3b8;

  font-size: 11px;

  margin-top: 6px;

}

.priority-indicator {

  width: 42px;
  height: 42px;

  border-radius: 12px;

  display: flex;
  align-items: center;
  justify-content: center;

  background:
    rgba(15,23,42,.04);

}

.priority-indicator span {

  width: 13px;
  height: 13px;

  border-radius: 50%;

}


/* =========================================================
   PRIORIDADES
========================================================= */

.ia-rojo {

  border-left:
    4px solid #ef4444;

}

.ia-rojo .priority-indicator span {

  background: #ef4444;

  box-shadow:
    0 0 0 6px #fee2e2;

}

.ia-naranja {

  border-left:
    4px solid #f97316;

}

.ia-naranja .priority-indicator span {

  background: #f97316;

  box-shadow:
    0 0 0 6px #ffedd5;

}

.ia-amarillo {

  border-left:
    4px solid #eab308;

}

.ia-amarillo .priority-indicator span {

  background: #eab308;

  box-shadow:
    0 0 0 6px #fef9c3;

}

.ia-verde {

  border-left:
    4px solid #22c55e;

}

.ia-verde .priority-indicator span {

  background: #22c55e;

  box-shadow:
    0 0 0 6px #dcfce7;

}

.ia-azul {

  border-left:
    4px solid #3b82f6;

}

.ia-azul .priority-indicator span {

  background: #3b82f6;

  box-shadow:
    0 0 0 6px #dbeafe;

}


/* =========================================================
   ESTADO
========================================================= */

.clinical-status {

  margin-top: 17px;

}

.status-badge {

  display: inline-flex;
  align-items: center;
  gap: 8px;

  padding:
    8px 14px;

  border-radius: 9px;

  font-size: 12px;

  font-weight: 800;

}

.status-dot {

  width: 7px;
  height: 7px;

  border-radius: 50%;

}

.status-badge.grave {

  color: #991b1b;
  background: #fef2f2;

}

.status-badge.grave .status-dot {

  background: #ef4444;

}

.status-badge.moderado {

  color: #92400e;
  background: #fffbeb;

}

.status-badge.moderado .status-dot {

  background: #f59e0b;

}

.status-badge.leve {

  color: #166534;
  background: #f0fdf4;

}

.status-badge.leve .status-dot {

  background: #22c55e;

}


/* =========================================================
   TIEMPO
========================================================= */

.waiting-time {

  display: inline-flex;

  margin-top: 17px;

  padding:
    8px 14px;

  border-radius: 9px;

  font-size: 14px;

  font-weight: 800;

}


/* =========================================================
   SECCIONES
========================================================= */

.clinical-section {

  background:
    var(--surface);

  border:
    1px solid var(--border);

  border-radius: 17px;

  padding: 20px;

  box-shadow:
    0 4px 14px rgba(15,23,42,.035);

}

.section-header {

  display: flex;
  justify-content: space-between;
  align-items: center;

  margin-bottom: 18px;

}

.section-title {

  display: flex;
  align-items: center;

  gap: 9px;

  font-size: 14px;

  font-weight: 800;

  color: #0f172a;

}

.section-title i {

  color: var(--primary);

}

.section-subtitle {

  margin-top: 3px;

  color: #94a3b8;

  font-size: 11px;

}

.units-badge {

  padding:
    6px 10px;

  border-radius: 8px;

  background: #f8fafc;

  border:
    1px solid #e2e8f0;

  color: #64748b;

  font-size: 10px;

  font-weight: 700;

}


/* =========================================================
   SIGNOS VITALES
========================================================= */

.vitals-grid {

  display: grid;

  grid-template-columns:
    repeat(3, 1fr);

  gap: 14px;

}

.vital-card {

  padding: 18px;

  border-radius: 14px;

  border:
    1px solid #e2e8f0;

  background: #fafbfc;

  transition:
    all .2s ease;

}

.vital-card:hover {

  background: white;

  transform:
    translateY(-2px);

  box-shadow:
    0 8px 20px rgba(15,23,42,.06);

}

.vital-top {

  display: flex;
  align-items: center;

  gap: 10px;

}

.vital-icon {

  width: 38px;
  height: 38px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 10px;

}

.pressure .vital-icon {

  background: #fef2f2;
  color: #ef4444;

}

.oxygen .vital-icon {

  background: #eff6ff;
  color: #2563eb;

}

.temperature .vital-icon {

  background: #fff7ed;
  color: #f97316;

}

.vital-title {

  display: flex;
  flex-direction: column;

  gap: 1px;

}

.vital-title span {

  font-size: 12px;

  color: #475569;

  font-weight: 700;

}

.vital-title small {

  color: #94a3b8;

  font-size: 9px;

  font-weight: 700;

  letter-spacing: .05em;

}

.vital-value {

  display: flex;
  align-items: baseline;

  gap: 5px;

  margin-top: 19px;

}

.vital-value strong {

  font-family:
    'JetBrains Mono',
    SFMono-Regular,
    Consolas,
    monospace;

  font-size: 29px;

  font-weight: 800;

  letter-spacing: -.04em;

  color: #0f172a;

}

.vital-value span {

  color: #94a3b8;

  font-size: 11px;

  font-weight: 600;

}

.vital-progress {

  height: 5px;

  background: #e2e8f0;

  border-radius: 10px;

  overflow: hidden;

  margin-top: 15px;

}

.progress-fill {

  height: 100%;

  border-radius: inherit;

  transition:
    width .5s ease;

}

.progress-fill.normal {

  background: #3b82f6;

}

.progress-fill.danger {

  background: #ef4444;

}

.temperature-fill {

  background: #f59e0b;

}

.vital-reference {

  display: flex;
  justify-content: space-between;

  margin-top: 9px;

  font-size: 10px;

  color: #94a3b8;

}

.vital-reference i {

  color: #22c55e;

  margin-right: 3px;

}

.vital-reference strong {

  color: #64748b;

  font-weight: 700;

}


/* =========================================================
   SÍNTOMAS
========================================================= */

.symptoms-box {

  display: flex;

  gap: 15px;

  padding: 18px;

  border-radius: 12px;

  background:
    #f8fafc;

  border:
    1px solid #e2e8f0;

}

.symptoms-icon {

  flex-shrink: 0;

  width: 34px;
  height: 34px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 9px;

  background:
    #eff6ff;

  color:
    #2563eb;

  font-size: 12px;

}

.symptoms-box p {

  margin: 0;

  color: #334155;

  font-size: 14px;

  line-height: 1.7;

}


/* =========================================================
   IA
========================================================= */

/* Forzar a la sección a usar el 100% real del ancho contenedor */
.clinical-section {
  width: 100% !important;
}

/* Redefinir la cuadrícula para que ocupe todo el ancho al 50% cada una */
.ai-grid {
  display: grid !important;
  grid-template-columns: repeat(2, minmax(0, 1fr)) !important; /* Dos columnas iguales */
  gap: 1.5rem !important; /* Espacio entre tarjetas */
  width: 100% !important;
}

/* Asegurar que los contenedores hijos de la cuadrícula también se expandan */
.ai-grid > div {
  width: 100% !important;
}

/* Adaptabilidad para pantallas móviles */
@media (max-width: 768px) {
  .ai-grid {
    grid-template-columns: 1fr !important;
  }
}


/* =========================================================
   LOADING
========================================================= */

.loading-card {

  padding:
    60px 30px;

  text-align: center;

  background: white;

  border:
    1px solid #e2e8f0;

  border-radius: 18px;

}

.loading-icon {

  width: 58px;
  height: 58px;

  margin:
    0 auto 18px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 16px;

  background:
    #eff6ff;

  color:
    #2563eb;

  font-size: 23px;

}

.loading-card h5 {

  margin-bottom: 5px;

  font-weight: 800;

}

.loading-card p {

  margin-bottom: 18px;

  color: #94a3b8;

  font-size: 12px;

}

.loading-bar {

  width: 220px;

  height: 4px;

  margin: auto;

  overflow: hidden;

  background: #e2e8f0;

  border-radius: 10px;

}

.loading-bar div {

  width: 45%;
  height: 100%;

  background: #2563eb;

  border-radius: inherit;

  animation:
    loading 1.3s infinite ease-in-out;

}

@keyframes loading {

  0% {
    transform: translateX(-100%);
  }

  100% {
    transform: translateX(500%);
  }

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 992px) {

  .summary-grid {

    grid-template-columns:
      repeat(2, 1fr);

  }

  .priority-card {

    grid-column:
      span 2;

  }

  .vitals-grid {

    grid-template-columns:
      repeat(2, 1fr);

  }

}

@media (max-width: 768px) {

  .patient-header {

    padding: 17px;

  }

  .patient-header-main {

    flex-wrap: wrap;

  }

  .patient-info {

    flex:
      1 1 calc(100% - 75px);

  }

  .btn-back {

    width: 100%;

    justify-content: center;

  }

  .summary-grid {

    grid-template-columns:
      1fr;

  }

  .priority-card {

    grid-column:
      auto;

  }

  .vitals-grid {

    grid-template-columns:
      1fr;

  }

  .ai-grid {

    grid-template-columns:
      1fr;

  }

  .section-header {

    align-items: flex-start;

    gap: 10px;

  }

  .units-badge {

    display: none;

  }

}

@media (max-width: 480px) {

  .patient-name {

    font-size: 18px;

  }

  .patient-meta {

    flex-direction: column;

    align-items: flex-start;

    gap: 3px;

  }

  .separator {

    display: none;

  }

  .clinical-section {

    padding: 15px;

  }

  .summary-card {

    padding: 17px;

  }

}

</style>

