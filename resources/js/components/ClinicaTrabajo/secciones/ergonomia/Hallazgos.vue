<template>
  <div class="seccion-hallazgos-ergonomia">
    <div class="card mb-4">
      <div class="card-header" style="background: linear-gradient(135deg, #2E8B57 0%, #228B22 100%); color: white;">
        <h3 class="mb-0">
          <i class="fas fa-clipboard-list mr-2"></i>
          Hallazgos Ergonómicos
        </h3>
      </div>
      <div class="card-body">
        <p class="text-muted mb-4">
          Registro de factores de riesgo ergonómicos observados durante la evaluación. Cada factor tiene una puntuación de riesgo.
        </p>

        <!-- Fila 1: Puntuaciones de instrumentos principales -->
        <div class="row mb-4">
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold d-block mb-1">RULA Puntuación (1-7)</label>
            <input v-model.number="form.rula_puntuacion" type="number" min="1" max="7" class="form-control" />
            <small class="text-muted">Nivel: {{ getRulaNivel }}</small>
          </div>
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold d-block mb-1">REBA Puntuación (1-15)</label>
            <input v-model.number="form.reba_puntuacion" type="number" min="1" max="15" class="form-control" />
            <small class="text-muted">Nivel: {{ getRebaNivel }}</small>
          </div>
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold d-block mb-1">NIOSH Índice (0-10)</label>
            <input v-model.number="form.niosh_indice_levantamiento" type="number" min="0" max="10" step="0.1" class="form-control" />
            <small class="text-muted">Seguro: {{ form.niosh_es_seguro ? 'Sí' : 'No' }}</small>
          </div>
        </div>

        <!-- Fila 2: Pesos y cargas -->
        <div class="row mb-4">
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold d-block mb-1">Peso Manipulado (kg)</label>
            <input v-model.number="form.peso_manipulado_kg" type="number" min="0" step="0.1" class="form-control" />
            <small class="text-muted">Peso recomendado: {{ pesoRecomendado }} kg</small>
          </div>
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold d-block mb-1">Altura Trabajador (cm)</label>
            <input v-model.number="form.altura_trabajador_cm" type="number" min="50" max="250" class="form-control" />
            <small class="text-muted">Fórmula: {{ calcularAlturaIdeal }} cm</small>
          </div>
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold d-block mb-1">Frecuencia Levantamiento</label>
            <select v-model="form.frecuencia_levantamiento" class="form-control">
              <option value="">Seleccionar...</option>
              <option value="muy_baja">Muy Baja (1-5 veces/hora)</option>
              <option value="baja">Baja (6-15 veces/hora)</option>
              <option value="media">Media (16-30 veces/hora)</option>
              <option value="alta">Alta (31-60 veces/hora)</option>
              <option value="muy_alta">Muy Alta (&gt;60 veces/hora)</option>
            </select>
          </div>
        </div>

        <!-- Fila 3: Factores de riesgo -->
        <div class="mb-4">
          <h4 class="font-weight-bold mb-3">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Factores de Riesgo Observados
          </h4>
          <div class="row">
            <div v-for="riesgo in riesgosErgonomicos" :key="riesgo.key" class="col-md-3 mb-2">
              <label class="d-flex align-items-center" style="cursor: pointer;">
                <input
                  type="checkbox"
                  :checked="getRiesgo(riesgo.key)"
                  @change="setRiesgo(riesgo.key, $event.target.checked)"
                  style="width: 16px; height: 16px; margin-right: 8px;"
                />
                <span class="text-sm">{{ riesgo.label }}</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Fila 4: Posturas y movimientos -->
        <div class="mb-4">
          <h4 class="font-weight-bold mb-3">
            <i class="fas fa-walking mr-2"></i>
            Posturas y Movimientos Evaluados
          </h4>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold d-block mb-1">Postura Cabeza/Cuello</label>
              <select v-model="form.postura_cuello" class="form-control">
                <option value="">Normal</option>
                <option value="flexion">Flexión</option>
                <option value="extension">Extensión</option>
                <option value="rotacion">Rotación</option>
                <option value="inclinacion_lateral">Inclinación Lateral</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold d-block mb-1">Postura Hombros</label>
              <select v-model="form.postura_hombros" class="form-control">
                <option value="">Neutral</option>
                <option value="elevacion">Elevación</option>
                <option value="depresion">Depresión</option>
                <option value="protraction">Protracción</option>
                <option value="retraction">Retracción</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold d-block mb-1">Postura Muñeca</label>
              <select v-model="form.postura_muneca" class="form-control">
                <option value="">Neutral</option>
                <option value="flexion">Flexión</option>
                <option value="extension">Extensión</option>
                <option value="desviacion_radial">Desviación Radial</option>
                <option value="desviacion_ulnar">Desviación Ulnar</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Fila 5: Resumen y conclusión -->
        <div class="mt-4 p-4" style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px;">
          <h4 class="font-weight-bold mb-3" style="color: #9a3412;">
            <i class="fas fa-chart-bar mr-2"></i>
            Evaluación de Riesgo Total
          </h4>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold d-block mb-1">Nivel de Riesgo Total</label>
              <div class="d-flex align-items-center">
                <span class="badge-riesgo"
                  :class="{
                    'badge-bajo': nivelRiesgoTotal === 'bajo',
                    'badge-medio': nivelRiesgoTotal === 'medio',
                    'badge-alto': nivelRiesgoTotal === 'alto',
                    'badge-muy-alto': nivelRiesgoTotal === 'muy_alto'
                  }">
                  {{ nivelRiesgoTotal }}
                </span>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold d-block mb-1">Acciones Requeridas</label>
              <div class="d-flex align-items-center">
                <span class="badge-accion"
                  :class="{
                    'badge-ninguna': accionRequerida === 'ninguna',
                    'badge-monitoreo': accionRequerida === 'monitoreo',
                    'badge-mejora': accionRequerida === 'mejora',
                    'badge-intervencion': accionRequerida === 'intervencion'
                  }">
                  {{ accionRequerida }}
                </span>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold d-block mb-1">Prioridad de Intervención</label>
              <div class="d-flex align-items-center">
                <span class="badge-prioridad"
                  :class="{
                    'badge-baja': prioridadIntervencion === 'baja',
                    'badge-media': prioridadIntervencion === 'media',
                    'badge-alta-p': prioridadIntervencion === 'alta',
                    'badge-urgente': prioridadIntervencion === 'urgente'
                  }">
                  {{ prioridadIntervencion }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HallazgosErgonomia',
  props: {
    modelValue: { type: Object, required: true }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      riesgosErgonomicos: [
        { key: 'posturas_forzadas', label: 'Posturas forzadas' },
        { key: 'movimientos_repetitivos', label: 'Movimientos repetitivos' },
        { key: 'manipulacion_cargas', label: 'Manipulación de cargas' },
        { key: 'cargas_maximas_manipuladas', label: 'Cargas máximas manipuladas' },
        { key: 'exposicion_calor', label: 'Exposición al calor' },
        { key: 'exposicion_vibraciones', label: 'Exposición a vibraciones' },
        { key: 'iluminacion_insuficiente', label: 'Iluminación insuficiente' }
      ]
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },
    getRulaNivel() {
      if (!this.form.rula_puntuacion) return 'Sin valorar'
      const puntuacion = this.form.rula_puntuacion
      if (puntuacion <= 2) return 'Insignificante'
      if (puntuacion <= 4) return 'Bajo'
      if (puntuacion <= 6) return 'Medio'
      return 'Alto'
    },
    getRebaNivel() {
      if (!this.form.reba_puntuacion) return 'Sin valorar'
      const puntuacion = this.form.reba_puntuacion
      if (puntuacion <= 3) return 'Insignificante'
      if (puntuacion <= 6) return 'Bajo'
      if (puntuacion <= 9) return 'Medio'
      if (puntuacion <= 12) return 'Alto'
      return 'Muy Alto'
    },
    nivelRiesgoTotal() {
      const rula = this.form.rula_puntuacion || 0
      const reba = this.form.reba_puntuacion || 0
      const niosh = this.form.niosh_indice_levantamiento || 0

      let riesgoScore = 0
      if (rula >= 6) riesgoScore += 3
      else if (rula >= 4) riesgoScore += 2
      else if (rula >= 2) riesgoScore += 1

      if (reba >= 11) riesgoScore += 3
      else if (reba >= 8) riesgoScore += 2
      else if (reba >= 5) riesgoScore += 1

      if (niosh >= 7) riesgoScore += 3
      else if (niosh >= 4) riesgoScore += 2
      else if (niosh >= 1) riesgoScore += 1

      const riesgosSeleccionados = this.riesgosErgonomicos.filter(r => this.form[r.key]).length
      riesgoScore += Math.min(riesgosSeleccionados, 3)

      if (riesgoScore <= 3) return 'bajo'
      if (riesgoScore <= 6) return 'medio'
      if (riesgoScore <= 9) return 'alto'
      return 'muy_alto'
    },
    accionRequerida() {
      const riesgo = this.nivelRiesgoTotal
      switch (riesgo) {
        case 'bajo': return 'ninguna'
        case 'medio': return 'monitoreo'
        case 'alto': return 'mejora'
        case 'muy_alto': return 'intervencion'
        default: return 'ninguna'
      }
    },
    prioridadIntervencion() {
      const riesgo = this.nivelRiesgoTotal
      switch (riesgo) {
        case 'bajo': return 'baja'
        case 'medio': return 'media'
        case 'alto': return 'alta'
        case 'muy_alto': return 'urgente'
        default: return 'baja'
      }
    },
    pesoRecomendado() {
      if (!this.form.niosh_indice_levantamiento) return 'N/A'
      const indice = this.form.niosh_indice_levantamiento
      if (indice <= 0) return 0
      return Math.max(5, 25 / indice).toFixed(1)
    },
    calcularAlturaIdeal() {
      if (!this.form.altura_trabajador_cm) return 'N/A'
      const altura = this.form.altura_trabajador_cm
      return Math.max(0, altura - 5).toFixed(0)
    }
  },
  methods: {
    getRiesgo(key) {
      if (!this.form) return false
      return this.form[key] || false
    },
    setRiesgo(key, checked) {
      this.$emit('update:modelValue', {
        ...this.form,
        [key]: checked
      })
    }
  }
}
</script>

<style scoped>
.seccion-hallazgos-ergonomia {
  background: #f8f9fa;
}

.card {
  border: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.form-control {
  border-radius: 4px;
  border: 1px solid #ced4da;
  font-size: 14px;
  padding: 10px 12px;
}

.font-weight-bold {
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  color: #2c3e50;
}

.text-muted {
  color: #6c757d !important;
}

.text-sm {
  font-size: 0.875rem;
  color: #495057;
}

.badge-riesgo, .badge-accion, .badge-prioridad {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-bajo { background: #dcfce7; color: #166534; }
.badge-medio { background: #fef3c7; color: #92400e; }
.badge-alto { background: #ffedd5; color: #9a3412; }
.badge-muy-alto { background: #fee2e2; color: #991b1b; }

.badge-ninguna { background: #dcfce7; color: #166534; }
.badge-monitoreo { background: #fef3c7; color: #92400e; }
.badge-mejora { background: #ffedd5; color: #9a3412; }
.badge-intervencion { background: #fee2e2; color: #991b1b; }

.badge-baja { background: #dcfce7; color: #166534; }
.badge-media { background: #fef3c7; color: #92400e; }
.badge-alta-p { background: #ffedd5; color: #9a3412; }
.badge-urgente { background: #fee2e2; color: #991b1b; }
</style>