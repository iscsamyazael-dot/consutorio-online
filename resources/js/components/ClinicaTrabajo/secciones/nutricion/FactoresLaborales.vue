<template>
  <div class="seccion-factores-laborales-nutricion">
    <!-- SECTION: FACTORES DE RIESGO LABORAL (NOM-035 / NOM-036) -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-briefcase mr-2"></i> FACTORES DE RIESGO LABORAL (NOM-035 / NOM-036)
        </h5>
      </div>
      <div class="card-body">
        <p class="text-muted mb-4">
          Indique si el puesto de trabajo que desempeña le exige o genera alguno de los siguientes puntos.<br>
          <span class="font-weight-bold">Escala: SÍ / NO / A VECES</span>
        </p>

        <!-- FACTORES DEL FORMATO ORIGINAL (EXCEL) -->
        <div class="mb-4">
          <h6 class="font-weight-bold text-dark mb-3 flex items-center gap-2">
            <i class="fas fa-chart-line text-blue-600"></i>
            Factores de Riesgo Físico y Organizacional
          </h6>
          <div class="row">
            <div v-for="factor in factoresOriginales"
                 :key="factor.key"
                 class="col-md-6 mb-3">
              <div class="border rounded-lg p-3 hover:bg-light transition">
                <label class="font-weight-bold d-block mb-2">
                  {{ factor.label }}
                </label>
                <div class="btn-group-toggle d-flex" style="gap: 1rem;">
                  <label v-for="opcion in opcionesSI_NO_AVECES"
                         :key="opcion.value"
                         class="btn btn-outline-secondary btn-sm rounded-pill">
                    <input type="radio"
                           :name="factor.key"
                           :value="opcion.value"
                           :checked="form[factor.key] === opcion.value"
                           @change="updateField(factor.key, opcion.value)"
                           class="position-static">
                    {{ opcion.label }}
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- FACTORES NOM-035 (PSICOSOCIALES) -->
        <div class="mb-4">
          <h6 class="font-weight-bold text-dark mb-3 flex items-center gap-2">
            <i class="fas fa-brain text-purple-600"></i>
            Factores de Riesgo Psicosocial (NOM-035-STPS-2018)
          </h6>
          <div class="row">
            <div v-for="factor in factoresNOM035"
                 :key="factor.key"
                 class="col-md-6 mb-3">
              <div class="border rounded-lg p-3 hover:bg-light transition">
                <label class="font-weight-bold d-block mb-2">
                  {{ factor.label }}
                </label>
                <div class="btn-group-toggle d-flex" style="gap: 1rem;">
                  <label v-for="opcion in opcionesSI_NO_AVECES"
                         :key="opcion.value"
                         class="btn btn-outline-secondary btn-sm rounded-pill">
                    <input type="radio"
                           :name="factor.key"
                           :value="opcion.value"
                           :checked="form[factor.key] === opcion.value"
                           @change="updateField(factor.key, opcion.value)"
                           class="position-static">
                    {{ opcion.label }}
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- RESUMEN DE FACTORES POSITIVOS -->
        <div v-if="factoresPositivos.length > 0" class="mb-4 p-3 bg-light border border-left border-warning">
          <h5 class="font-weight-bold text-warning mb-3 flex items-center gap-2">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Factores de Riesgo Identificados ({{ factoresPositivos.length }})
          </h5>
          <div class="d-flex flex-wrap gap-2">
            <span v-for="f in factoresPositivos"
                  :key="f.key"
                  class="badge badge-pill badge-warning p-2 text-xs">
              {{ f.label }}: {{ form[f.key] === 'si' ? 'SÍ' : 'A VECES' }}
            </span>
          </div>
          <p class="text-xs text-muted mt-2 mb-0">
            Estos factores requieren atención prioritaria en el plan nutricional y recomendaciones ergonómicas.
          </p>
        </div>

        <!-- REFERENCIA NORMAATIVA -->
        <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded">
          <h5 class="font-weight-bold text-dark mb-2 flex items-center gap-2">
            <i class="fas fa-book mr-2"></i> REFERENCIA NORMAATIVA
          </h5>
          <div class="row text-sm text-gray-600">
            <div class="col-md-6">
              <span class="font-weight-bold">NOM-035-STPS-2018:</span><br>
              Factores de riesgo psicosocial en el trabajo - Identificación, análisis y prevención.
            </div>
            <div class="col-md-6">
              <span class="font-weight-bold">NOM-036-1-STPS-2018:</span><br>
              Factores de riesgo ergonómico en el trabajo - Identificación, análisis y prevención.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FactoresLaboralesNutricion',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      // Factores del formato Excel original
      factoresOriginales: [
        { key: 'tension_emocional', label: 'Tensión Emocional' },
        { key: 'alta_responsabilidad', label: 'Alta Responsabilidad' },
        { key: 'carga_excesiva_trabajo', label: 'Carga Excesiva de Trabajo' },
        { key: 'turno_rotativo', label: 'Turno Rotativo' },
        { key: 'turno_nocturno', label: 'Turno Nocturno' },
        { key: 'trabajo_repetitivo', label: 'Trabajo Repetitivo' }
      ],
      // Factores oficiales NOM-035-STPS-2018
      factoresNOM035: [
        { key: 'condiciones_entorno', label: 'Condiciones del Entorno de Trabajo' },
        { key: 'carga_trabajo', label: 'Carga de Trabajo' },
        { key: 'falta_control_trabajo', label: 'Falta de Control sobre el Trabajo' },
        { key: 'jornadas_trabajo', label: 'Jornadas de Trabajo' },
        { key: 'interferencia_trabajo_familia', label: 'Interferencia Trabajo-Familia' },
        { key: 'relaciones_interpersonales', label: 'Relaciones Interpersonales en el Trabajo' },
        { key: 'violencia_laboral', label: 'Violencia Laboral' },
        { key: 'acontecimientos_traumaticos', label: 'Acontecimientos Traumáticos Severos' }
      ],
      opcionesSI_NO_AVECES: [
        { value: 'si', label: 'SÍ' },
        { value: 'no', label: 'NO' },
        { value: 'a_veces', label: 'A VECES' }
      ]
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },
    factoresPositivos() {
      const todos = [...this.factoresOriginales, ...this.factoresNOM035]
      return todos.filter(f => ['si', 'a_veces'].includes(this.form[f.key]))
    }
  },
  methods: {
    updateField(key, value) {
      this.$emit('update:modelValue', { ...this.form, [key]: value })
    }
  }
}
</script>

<style scoped>
.seccion-factores-laborales-nutricion {
  background: #f8f9fa;
}

.card {
  border: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.form-control, .btn {
  border-radius: 4px;
}

.label {
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  color: #2c3e50;
}

.text-muted {
  color: #6c757d !important;
}

.cursor-pointer {
  cursor: pointer;
}

.btn-outline-secondary {
  border-width: 2px;
  padding: 0.5rem 1rem;
  font-size: 0.75rem;
}

.border-warning {
  border-color: #ffc107 !important;
}

.bg-warning {
  background-color: #fff3cd;
}

.text-warning {
  color: #856404;
}

.badge-warning {
  background-color: #ffc107;
  color: #212529;
}

.btn-group-toggle {
  display: flex;
  gap: 0.5rem;
}
</style>