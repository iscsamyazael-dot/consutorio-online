<template>
  <div class="card card-outline card-primary">
    <!-- Header -->
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-file-medical-alt mr-1"></i> Informe Completo de Evaluación IA
      </h3>
      <div class="card-tools">
        <button type="button" class="btn btn-sm btn-secondary" @click="$emit('volver')">
          <i class="fas fa-arrow-left mr-1"></i> Volver al listado
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="card-body text-center py-5">
      <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
      <p class="mt-3 text-muted">Cargando informe clínico...</p>
    </div>

    <div v-else-if="detalle" class="card-body">
      <!-- 1. RESUMEN DEL PACIENTE -->
      <div class="callout callout-info mb-4">
        <h5 class="font-weight-bold text-dark mb-0">
          <i class="fas fa-user-injured mr-2 text-info"></i>{{ detalle.paciente?.nombre }}
        </h5>
        <div class="row text-muted mt-3">
          <div class="col-md-3">
            <strong>ID Paciente:</strong> {{ detalle.paciente?.id || 'Sin ID' }}
          </div>
          <div class="col-md-3">
            <strong>Edad:</strong> 
            {{ detalle.paciente?.edad ? `${detalle.paciente.edad} años` : 'Sin edad registrada' }}
          </div>
          <div class="col-md-3">
            <strong>Sexo:</strong> 
            {{ detalle.paciente?.sexo || 'Sin sexo registrado' }}
          </div>
          <div class="col-md-3">
            <strong>Fecha de Atención:</strong> {{ detalle.fecha }}
          </div>
        </div>
      </div>

      <div class="row">
        <!-- COLUMNA IZQUIERDA -->
        <div class="col-md-7">
          <!-- IA CLÍNICA -->
          <div class="card card-outline card-warning mb-3">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-robot mr-1"></i> IA Clínica & Diagnóstico</h3>
            </div>
            <div class="card-body">
              <p><strong>Diagnóstico Probable:</strong> {{ detalle.diagnostico_probable || 'Sin diagnóstico registrado' }}</p>

              <div class="mb-3">
                <label class="d-block font-weight-bold mb-1">Síntomas Detectados:</label>
                <span 
                  v-for="(sintoma, idx) in detalle.sintomas_array" 
                  :key="idx" 
                  class="badge badge-warning mr-1 mb-1 p-2"
                >
                  {{ sintoma }}
                </span>
                <span v-if="!detalle.sintomas_array?.length" class="text-muted">No detectados</span>
              </div>

              <div class="pt-2">
                <!-- NIVEL DE RIESGO -->
                <div class="mb-4">
                  <span class="d-block text-muted small font-weight-bold mb-2">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Nivel de Riesgo
                  </span>

                  <span
                    class="badge px-3 py-2"
                    :class="`badge-${riesgoColor(detalle.riesgo)}`"
                    style="font-size: 0.9rem;"
                  >
                    {{ detalle.riesgo }}
                  </span>
                </div>

                <!-- RECOMENDACIONES + CONFIANZA -->
                <div>
                  <span class="d-block text-muted small font-weight-bold mb-3">
                    <i class="fas fa-lightbulb mr-1 text-warning"></i>
                    Recomendación IA y nivel de confianza
                  </span>

                  <div
                    v-for="(evaluacion, index) in detalle.evaluaciones"
                    :key="evaluacion.id || index"
                    class="ia-recomendacion mb-3"
                  >
                    <!-- TEXTO DE LA RECOMENDACIÓN -->
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <div class="d-flex align-items-start">
                        <span class="badge badge-light-primary mr-2">
                          EVA. {{ index + 1 }}
                        </span>

                        <span class="text-dark">
                          {{ evaluacion.recomendacion || 'Sin recomendación registrada.' }}
                        </span>
                      </div>

                      <span class="font-weight-bold text-primary ml-2">
                        {{ evaluacion.confianza }}%
                      </span>
                    </div>

                    <!-- BARRA DE CONFIANZA -->
                    <div class="progress" style="height: 9px; border-radius: 10px; background-color: #e9ecef;">
                      <div
                        class="progress-bar"
                        role="progressbar"
                        :style="{ width: `${evaluacion.confianza}%` }"
                        :class="confianzaColor(evaluacion.confianza)"
                        :aria-valuenow="evaluacion.confianza"
                        aria-valuemin="0"
                        aria-valuemax="100"
                      ></div>
                    </div>

                    <div class="d-flex justify-content-between mt-1">
                      <small class="text-muted">
                        Nivel de confianza
                      </small>

                      <small
                        class="font-weight-bold"
                        :class="confianzaTextoColor(evaluacion.confianza)"
                      >
                        {{ confianzaTexto(evaluacion.confianza) }}
                      </small>
                    </div>

                  </div>
                  <!-- SIN EVALUACIONES -->
                  <div
                    v-if="!detalle.evaluaciones?.length"
                    class="text-muted small"
                  >
                    No existen recomendaciones de IA registradas.
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ALERTAS CLÍNICAS -->
          <div class="card card-outline card-danger mb-3" v-if="detalle.alertas_clinicas?.length">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-1"></i> Alertas Clínicas</h3>
            </div>
            <div class="card-body p-0">
              <ul class="list-group list-group-flush">
                <li v-for="alerta in detalle.alertas_clinicas" :key="alerta.id" class="list-group-item">
                  <div class="d-flex justify-content-between align-items-center">
                    <strong class="text-danger"><i class="fas fa-bell mr-1"></i>{{ alerta.titulo }}</strong>
                    <span class="badge" :class="`badge-${alertaColor(alerta.nivel)}`">{{ alerta.nivel }}</span>
                  </div>
                  <p class="mb-0 text-muted small mt-1">{{ alerta.descripcion }}</p>
                  <p v-if="alerta.observaciones" class="mb-0 text-secondary small italic mt-1">
                    <em>Obs: {{ alerta.observaciones }}</em>
                  </p>
                </li>
              </ul>
            </div>
          </div>

          <!-- RECOMENDACIÓN IA -->
          <div class="card card-outline card-info mb-3">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-lightbulb mr-1"></i> Recomendación IA</h3>
            </div>
            <div class="card-body">
              <p class="mb-0 text-justify">{{ detalle.recomendacion || 'Sin recomendaciones adicionales.' }}</p>
            </div>
          </div>

          <!-- DERIVACIÓN (Modo Lectura) -->
          <div class="card card-outline card-primary mb-3" v-if="detalle.derivacion">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-hospital-user mr-1"></i> Derivación Médica</h3>
            </div>
            <div class="card-body">
              <div class="row mb-2">
                <div class="col-md-6"><strong>Especialidad:</strong> {{ detalle.derivacion.especialidad }}</div>
                <div class="col-md-6">
                  <strong>Prioridad:</strong> 
                  <span class="badge" :class="`badge-${riesgoColor(detalle.derivacion.prioridad)}`">
                    {{ detalle.derivacion.prioridad }}
                  </span>
                </div>
              </div>
              <p v-if="detalle.derivacion.hospital" class="mb-1"><strong>Hospital / Destino:</strong> {{ detalle.derivacion.hospital }}</p>
              <p class="mb-0"><strong>Motivo:</strong> {{ detalle.derivacion.motivo || 'Sin motivo especificado.' }}</p>
            </div>
          </div>
        </div>

        <!-- COLUMNA DERECHA -->
        <div class="col-md-5">
          <!-- NOTA PSOAPP -->
          <div class="card card-outline card-secondary mb-3">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-notes-medical mr-1"></i> Nota PSOAPP</h3>
            </div>
            <div class="card-body" style="max-height: 320px; overflow-y: auto;">
              <div v-if="detalle.nota_psoapp">
                <p v-if="detalle.nota_psoapp.presentacion"><strong>P (Presentación):</strong> {{ detalle.nota_psoapp.presentacion }}</p>
                <p v-if="detalle.nota_psoapp.subjetivo"><strong>S (Subjetivo):</strong> {{ detalle.nota_psoapp.subjetivo }}</p>
                <p v-if="detalle.nota_psoapp.objetivo"><strong>O (Objetivo):</strong> {{ detalle.nota_psoapp.objetivo }}</p>
                <p v-if="detalle.nota_psoapp.analisis"><strong>A (Análisis):</strong> {{ detalle.nota_psoapp.analisis }}</p>
                <p v-if="detalle.nota_psoapp.plan"><strong>P (Plan):</strong> {{ detalle.nota_psoapp.plan }}</p>
                <p v-if="detalle.nota_psoapp.pronostico"><strong>P (Pronóstico):</strong> {{ detalle.nota_psoapp.pronostico }}</p>
              </div>
              <p v-else class="text-muted mb-0">No se ha registrado nota PSOAPP.</p>
            </div>
          </div>

          <!-- RECETA INTELIGENTE -->
            <div class="card card-outline card-success mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-prescription-bottle-alt mr-1"></i> Receta Inteligente</h3>
                </div>
                <div class="card-body">
                    <div v-if="detalle.receta">
                    <!-- Lista de Medicamentos -->
                    <div v-if="detalle.receta.medicamentos && detalle.receta.medicamentos.length" class="mb-3">
                        <label class="font-weight-bold small text-muted">MEDICAMENTOS PRESCRITOS:</label>
                        <ul class="pl-3 mb-0">
                        <li v-for="(med, i) in detalle.receta.medicamentos" :key="i" class="mb-1">
                            <!-- Soporta si es string directo o un objeto con propiedades -->
                            <template v-if="typeof med === 'string'">
                            <strong>{{ med }}</strong>
                            </template>
                            <template v-else>
                            <strong>{{ med.nombre || med.medicamento || med.nombre_comercial || 'Medicamento' }}</strong>
                            <span v-if="med.dosis || med.gramaje"> - {{ med.dosis || med.gramaje }}</span>
                            <span v-if="med.frecuencia || med.indicacion"> ({{ med.frecuencia || med.indicacion }})</span>
                            </template>
                        </li>
                        </ul>
                    </div>

                    <div v-if="detalle.receta.indicaciones_generales" class="mt-2">
                        <label class="font-weight-bold small text-muted">INDICACIONES GENERALES:</label>
                        <p class="mb-2 text-justify">{{ detalle.receta.indicaciones_generales }}</p>
                    </div>

                    <div v-if="detalle.receta.observaciones_ia" class="mt-2">
                        <label class="font-weight-bold small text-muted">OBSERVACIONES IA:</label>
                        <p class="mb-0 text-muted small italic">{{ detalle.receta.observaciones_ia }}</p>
                    </div>
                    </div>
                    <p v-else class="text-muted mb-0">Sin prescripción registrada para esta consulta.</p>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ApiService from '../../services/ApiService.js';

export default {
  name: 'InformeCompletoIA',

  props: {
    folio: {
      type: String,
      required: true,
    },
  },

  emits: ['volver'],

  data() {
    return {
      detalle: null,
      loading: true,
    };
  },

  mounted() {
    this.obtenerDetalle();
  },

  methods: {
    async obtenerDetalle() {
      this.loading = true;
      try {
        const response = await ApiService.get(`/api/evaluaciones-ia/${this.folio}`);
        this.detalle = response.data || response;
      } catch (error) {
        console.error('Error al cargar el detalle de la evaluación:', error);
      } finally {
        this.loading = false;
      }
    },

    riesgoColor(riesgo) {
      const colores = { ALTO: 'danger', MEDIO: 'warning', BAJO: 'success' };
      return colores[riesgo] || 'secondary';
    },

    alertaColor(nivel) {
      const colores = { CRITICO: 'danger', ALTO: 'danger', MEDIO: 'warning', BAJO: 'info' };
      return colores[nivel] || 'secondary';
    },

    confianzaColor(confianza) {
      if (confianza >= 80) return 'bg-success';
      if (confianza >= 60) return 'bg-warning';
      return 'bg-danger';
    },

    confianzaTextoColor(confianza) {
      if (confianza >= 80) return 'text-success';
      if (confianza >= 60) return 'text-warning';
      return 'text-danger';
    },

    confianzaTexto(confianza) {
      if (confianza >= 80) return 'Alta confianza';
      if (confianza >= 60) return 'Confianza moderada';
      return 'Baja confianza';
    },

  },
};
</script>