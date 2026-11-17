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
                <option value="normal">Normal</option>
                <option value="flexion">Flexión</option>
                <option value="extension">Extensión</option>
                <option value="rotacion">Rotación</option>
                <option value="inclinacion_lateral">Inclinación Lateral</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold d-block mb-1">Postura Hombros</label>
              <select v-model="form.postura_hombros" class="form-control">
                <option value="neutral">Neutral</option>
                <option value="elevacion">Elevación</option>
                <option value="depresion">Depresión</option>
                <option value="protraction">Protracción</option>
                <option value="retraction">Retracción</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold d-block mb-1">Postura Muñeca</label>
              <select v-model="form.postura_muneca" class="form-control">
                <option value="neutral">Neutral</option>
                <option value="flexion">Flexión</option>
                <option value="extension">Extensión</option>
                <option value="desviacion_radial">Desviación Radial</option>
                <option value="desviacion_ulnar">Desviación Ulnar</option>
              </select>
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
    modelValue: { type: Object, required: true },
    instrumento: { type: Object, default: () => ({}) }
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
      const puntuacion = this.instrumento.rula_puntuacion
      if (!puntuacion) return 'Sin valorar'
      if (puntuacion <= 2) return 'Negligible'
      if (puntuacion <= 4) return 'Bajo'
      if (puntuacion <= 6) return 'Moderado'
      return 'Alto'
    },
    getRebaNivel() {
      const puntuacion = this.instrumento.reba_puntuacion
      if (!puntuacion) return 'Sin valorar'
      if (puntuacion <= 1) return 'Negligible'
      if (puntuacion <= 3) return 'Bajo'
      if (puntuacion <= 7) return 'Moderado'
      if (puntuacion <= 10) return 'Alto'
      return 'Muy Alto'
    },
    
    pesoRecomendado() {
      if (!this.instrumento.niosh_indice_levantamiento) return 'N/A'
      const indice = this.instrumento.niosh_indice_levantamiento
      if (indice <= 0) return 0.0
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