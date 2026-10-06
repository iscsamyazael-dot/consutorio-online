<template>
  <div class="seccion-habitos-consumo-ergonomia">
    <!-- SECTION: HÁBITOS DE CONSUMO -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #2E8B57 0%, #228B22 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-smoking-ban mr-2"></i> HÁBITOS DE CONSUMO
        </h5>
      </div>
      <div class="card-body">
        <p class="text-muted mb-4">
          Indique si el paciente presenta los siguientes hábitos de consumo que pueden afectar su salud y desempeño laboral.
        </p>

        <!-- Fila 1: Hábitos principales -->
        <div class="row mb-4">
          <div v-for="(habito, index) in habitosConsumo.slice(0, 2)"
               :key="habito.key"
               class="col-md-6">
            <div class="border rounded p-3 hover:bg-light transition">
              <label class="font-weight-bold d-block mb-2">
                {{ habito.label }}
              </label>
              <div class="btn-group-toggle" data-toggle="buttons">
                <label v-for="opcion in opcionesSI_NO"
                       :key="opcion.value"
                       class="btn btn-outline-secondary btn-sm m-1 rounded-pill"
                       :class="[form[habito.key] === opcion.value ? 'active' : '']">
                  <input type="radio"
                         :name="habito.key"
                         :value="opcion.value"
                         :checked="form[habito.key] === opcion.value"
                         @change="updateField(habito.key, opcion.value)"
                         class="position-static">
                  {{ opcion.label }}
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Fila 2: Hábitos restantes -->
        <div class="row mb-4">
          <div v-for="(habito, index) in habitosConsumo.slice(2, 4)"
               :key="habito.key"
               class="col-md-6">
            <div class="border rounded p-3 hover:bg-light transition">
              <label class="font-weight-bold d-block mb-2">
                {{ habito.label }}
              </label>
              <div class="btn-group-toggle" data-toggle="buttons">
                <label v-for="opcion in opcionesSI_NO"
                       :key="opcion.value"
                       class="btn btn-outline-secondary btn-sm m-1 rounded-pill"
                       :class="[form[habito.key] === opcion.value ? 'active' : '']">
                  <input type="radio"
                         :name="habito.key"
                         :value="opcion.value"
                         :checked="form[habito.key] === opcion.value"
                         @change="updateField(habito.key, opcion.value)"
                         class="position-static">
                  {{ opcion.label }}
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- ESCALAS DE EVALUACIÓN -->
        <div v-if="form.tabaquismo || form.alcoholismo" class="mt-4 pt-3 border-top border-top">
          <div class="card-header pb-0 pt-0">
            <h5 class="mb-0">
              <i class="fas fa-weight-hanging mr-2"></i> ESCALAS DE EVALUACIÓN
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <!-- ESCALA FAGERSTRÖM -->
              <div v-if="form.tabaquismo" class="col-md-6 mb-3">
                <label class="font-weight-bold d-block mb-2">Escala de Fagerström para Dependencia a la Nicotina (0-10)</label>
                <input type="number"
                       class="form-control"
                       v-model.number="form.escala_fagerstrm"
                       min="0" max="10"
                       placeholder="Puntuación según escáler"
                />
                <small class="text-muted">
                  0-2: Muy baja • 3-4: Baja • 5: Media • 6-7: Alta • 8-10: Muy alta
                </small>
              </div>

              <!-- ESCALA AUDIT -->
              <div v-if="form.alcoholismo" class="col-md-6 mb-3">
                <label class="font-weight-bold d-block mb-2">Escala AUDIT para Consumo de Alcohol (0-40)</label>
                <input type="number"
                       class="form-control"
                       v-model.number="form.escala_audit"
                       min="0" max="40"
                       placeholder="Puntuación según escáler"
                />
                <small class="text-muted">
                  0-7: Bajo riesgo • 8-15: Riesgo medio • 16-19: Riesgo alto • 20-40: Dependencia probable
                </small>
              </div>
            </div>
          </div>
        </div>

        <!-- CONSUMO DE ALCOHOL -->
        <div v-if="form.alcoholismo" class="mt-4 pt-3 border-top border-top">
          <div class="card-header pb-0 pt-0">
            <h5 class="mb-0">
              <i class="fas fa-beer mr-2"></i> CONSUMO DE ALCOHOL
            </h5>
          </div>
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="font-weight-bold d-block mb-2">Frecuencia de consumo de alcohol:</label>
                <select class="form-control"
                        v-model="form.frecuencia_consumo_alcohol">
                  <option value="">Seleccionar...</option>
                  <option value="nunca">Nunca</option>
                  <option value="ocasional">Ocasional (menos de 1 vez/semana)</option>
                  <option value="semanal">Semanal (1-3 veces/semana)</option>
                  <option value="varios_semanal">Varios días/semana (4-6 días)</option>
                  <option value="diario">Diario</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- SÍNTOMAS ASOCIADOS -->
        <div class="mt-4 pt-3 border-top border-top">
          <div class="card-header pb-0 pt-0">
            <h5 class="mb-0">
              <i class="fas fa-stomach mr-2"></i> SÍNTOMAS GASTROINTESTINALES ASOCIADOS
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <!-- ITERAR SOBRE LOS SÍNTOMAS EN FILAS -->
              <div v-for="(sintoma, index) in sintomasGI.slice(0, 4)"
                   :key="sintoma.key"
                   class="col-md-3 mb-3">
                <div class="border rounded p-2">
                  <label class="font-weight-bold d-block mb-1">
                    {{ sintoma.label }}
                  </label>
                  <div class="form-check">
                    <input type="checkbox"
                           class="form-check-input"
                           :id="sintoma.key"
                           v-model="form[sintoma.key]"
                    >
                    <label class="form-check-label" :for="sintoma.key">
                      {{ sintoma.label }}
                    </label>
                  </div>
                </div>
              </div>
              <div v-for="(sintoma, index) in sintomasGI.slice(4, 8)"
                   :key="sintoma.key"
                   class="col-md-3 mb-3">
                <div class="border rounded p-2">
                  <label class="font-weight-bold d-block mb-1">
                    {{ sintoma.label }}
                  </label>
                  <div class="form-check">
                    <input type="checkbox"
                           class="form-check-input"
                           :id="sintoma.key"
                           v-model="form[sintoma.key]"
                    >
                    <label class="form-check-label" :for="sintoma.key">
                      {{ sintoma.label }}
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- RESUMEN DE HÁBITOS -->
        <div v-if="[form.tabaquismo, form.alcoholismo, form.varia_consumo_estres, form.varia_consumo_tristeza].includes(true) || Object.values(form).some(v => v === true && ['refiere_diarrea','refiere_estrenimiento','refiere_gastritis','refiere_ulcera','refiere_nauseas','refiere_reflujo','refiere_vomitos','refiere_colitis'].includes(v))" class="mt-4 p-3 bg-light border border-left border-success">
          <h5 class="font-weight-bold text-success mb-3 flex items-center gap-2">
            <i class="fas fa-info-circle mr-2"></i>
            Hábitos Identificados
          </h5>
          <div class="d-flex flex-wrap gap-2">
            <span v-if="form.tabaquismo" class="badge badge-pill badge-success p-2">Tabaquismo</span>
            <span v-if="form.alcoholismo" class="badge badge-pill badge-success p-2">Alcoholismo</span>
            <span v-if="form.varia_consumo_estres" class="badge badge-pill badge-success p-2">Consumo por estrés</span>
            <span v-if="form.varia_consumo_tristeza" class="badge badge-pill badge-success p-2">Consumo por tristeza</span>
            <span v-if="form.refiere_diarrea" class="badge badge-pill badge-success p-2">Diarrea</span>
            <span v-if="form.refiere_estrenimiento" class="badge badge-pill badge-success p-2">Estreñimiento</span>
            <span v-if="form.refiere_gastritis" class="badge badge-pill badge-success p-2">Gastritis</span>
            <span v-if="form.refiere_ulcera" class="badge badge-pill badge-success p-2">Ulcera</span>
            <span v-if="form.refiere_nauseas" class="badge badge-pill badge-success p-2">Náuseas</span>
            <span v-if="form.refiere_reflujo" class="badge badge-pill badge-success p-2">Reflujo</span>
            <span v-if="form.refiere_vomitos" class="badge badge-pill badge-success p-2">Vómitos</span>
            <span v-if="form.refiere_colitis" class="badge badge-pill badge-success p-2">Colitis</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HabitosConsumoErgonomia',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      habitosConsumo: [
        { key: 'tabaquismo', label: 'Tabaquismo' },
        { key: 'alcoholismo', label: 'Alcoholismo' },
        { key: 'varia_consumo_estres', label: 'Consumo variable por estrés' },
        { key: 'varia_consumo_tristeza', label: 'Consumo variable por tristeza/animo bajo' }
      ],
      opcionesSI_NO: [
        { value: true, label: 'SÍ' },
        { value: false, label: 'NO' }
      ],
      sintomasGI: [
        { key: 'refiere_diarrea', label: 'Diarrea frecuente' },
        { key: 'refiere_estrenimiento', label: 'Estreñimiento frecuente' },
        { key: 'refiere_gastritis', label: 'Gastritis o acidez' },
        { key: 'refiere_ulcera', label: 'Ulcera péptica' },
        { key: 'refiere_nauseas', label: 'Náuseas/vómitos' },
        { key: 'refiere_reflujo', label: 'Reflujo gastroesofágico' },
        { key: 'refiere_vomitos', label: 'Vómitos recurrentes' },
        { key: 'refiere_colitis', label: 'Colitis o enfermedad inflamatoria intestinal' }
      ]
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
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
.seccion-habitos-consumo-ergonomia {
  background: #f8f9fa;
}

.card {
  border: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.btn-outline-secondary {
  border-color: #ced4da;
  color: #495057;
  border-radius: 2rem;
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.btn-outline-secondary:hover {
  border-color: #adb5bd;
}

.btn-outline-secondary.active {
  background-color: #2E8B57;
  border-color: #2E8B57;
  color: white;
}

.form-control {
  border-radius: 4px;
  border: 1px solid #ced4da;
  font-size: 14px;
  padding: 10px 12px;
}

.form-check-input {
  margin-top: 0.25rem;
  margin-left: -1.25rem;
}

.label {
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  color: #2c3e50;
}

.text-muted {
  color: #6c757d !important;
}
</style>