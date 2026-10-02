<template>
  <div class="seccion-habitos-consumo-nutricion">
    <!-- SECTION: HÁBITOS Y CONSUMO -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-apple-alt mr-2"></i> HÁBITOS Y CONSUMO
        </h5>
      </div>
      <div class="card-body">
        <!-- TABAQUISMO Y ALCOHOLISMO -->
        <div class="mb-4">
          <h6 class="font-weight-bold text-dark mb-3">Tabaquismo y Alcoholismo</h6>
          <div class="row">
            <div class="col-md-6 mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox"
                       v-model="form.tabaquismo"
                       @change="updateField('tabaquismo', $event.target.checked)"
                >
                <label class="form-check-label font-weight-bold">Tabaquismo</label>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox"
                       v-model="form.alcoholismo"
                       @change="updateField('alcoholismo', $event.target.checked)"
                >
                <label class="form-check-label font-weight-bold">Alcoholismo</label>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox"
                       v-model="form.varia_consumo_estres"
                       @change="updateField('varia_consumo_estres', $event.target.checked)"
                >
                <label class="form-check-label font-weight-bold">Varía consumo por estrés</label>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox"
                       v-model="form.varia_consumo_tristeza"
                       @change="updateField('varia_consumo_tristeza', $event.target.checked)"
                >
                <label class="form-check-label font-weight-bold">Varía consumo por tristeza</label>
              </div>
            </div>
          </div>
        </div>

        <!-- ESCALAS VALIDADAS -->
        <div class="mb-4">
          <h6 class="font-weight-bold text-dark mb-3">Escalas Validadas</h6>
          <div class="row">
            <!-- ESCALA FAGERSTRÖM -->
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold">Escala Fagerström (0-10)</label>
              <input
                type="number"
                class="form-control"
                v-model.number="form.escala_fagerstrm"
                @input="updateField('escala_fagerstrm', $event.target.value)"
                min="0"
                max="10"
                placeholder="0-10: Dependencia a nicotina"
                style="height: 38px;"
              >
              <small class="text-muted">≥5: Dependencia moderada-severa</small>
            </div>

            <!-- ESCALA AUDIT -->
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold">Escala AUDIT (0-40)</label>
              <input
                type="number"
                class="form-control"
                v-model.number="form.escala_audit"
                @input="updateField('escala_audit', $event.target.value)"
                min="0"
                max="40"
                placeholder="0-40: Consumo riesgo alcohol"
                style="height: 38px;"
              >
              <small class="text-muted">≥8: Consumo riesgoso; ≥20: Dependencia</small>
            </div>

            <!-- FRECUENCIA CONSUMO ALCOHOL -->
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold">Frecuencia Consumo Alcohol</label>
              <select
                class="form-control"
                v-model="form.frecuencia_consumo_alcohol"
                @change="updateField('frecuencia_consumo_alcohol', $event.target.value)"
                style="height: 38px;"
              >
                <option value="">Seleccionar...</option>
                <option value="nunca">Nunca</option>
                <option value="mensual">Mensual</option>
                <option value="semanal">Semanal</option>
                <option value="diario">Diario</option>
              </select>
            </div>
          </div>
        </div>

        <!-- SÍNTOMAS GASTROINTESTINALES -->
        <div class="mb-4">
          <h6 class="font-weight-bold text-dark mb-3 flex items-center gap-2">
            <i class="fas fa-exclamation-triangle text-red-600"></i>
            Síntomas Gastrointestinales
          </h6>
          <div class="row">
            <div v-for="sintoma in sintomasGI"
                 :key="sintoma.key"
                 class="col-md-4 mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox"
                       :checked="form[sintoma.key]"
                       @change="updateField(sintoma.key, $event.target.checked)"
                >
                <label class="form-check-label">{{ sintoma.label }}</label>
              </div>
            </div>
          </div>
        </div>

        <!-- AUTO-CÁLCULO TABAQUISMO/ALCOHOLISMO DESDE ESCALAS -->
        <div v-if="form.escala_fagerstrm > 0 || form.escala_audit > 0" class="mt-4 p-3 bg-light border border-left border-info">
          <h5 class="font-weight-bold text-info mb-2">Auto-detección:</h5>
          <div class="text-sm text-dark">
            <div v-if="form.escala_fagerstrm > 0">
              <span class="font-weight-bold">Fagerström {{ form.escala_fagerstrm }}/10: </span>
              {{ form.escala_fagerstrm >= 5 ? 'Dependencia moderada-severa → Tabaquismo = SÍ' : 'Dependencia leve' }}
            </div>
            <div v-if="form.escala_audit > 0">
              <span class="font-weight-bold">AUDIT {{ form.escala_audit }}/40: </span>
              {{ form.escala_audit >= 20 ? 'Dependencia probable → Alcoholismo = SÍ' : form.escala_audit >= 8 ? 'Consumo riesgoso' : 'Consumo de bajo riesgo' }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HabitosConsumoNutricion',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      sintomasGI: [
        { key: 'refiere_diarrea', label: 'Diarrea' },
        { key: 'refiere_estrenimiento', label: 'Estreñimiento' },
        { key: 'refiere_gastritis', label: 'Gastritis' },
        { key: 'refiere_ulcera', label: 'Úlcera' },
        { key: 'refiere_nauseas', label: 'Náuseas' },
        { key: 'refiere_reflujo', label: 'Reflujo' },
        { key: 'refiere_vomitos', label: 'Vómitos' },
        { key: 'refiere_colitis', label: 'Colitis' }
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
  },
  watch: {
    'form.escala_fagerstrm'(val) {
      if (val > 0 && !this.form.tabaquismo) {
        this.updateField('tabaquismo', true)
      }
    },
    'form.escala_audit'(val) {
      if (val > 0 && !this.form.alcoholismo) {
        this.updateField('alcoholismo', true)
      }
    }
  }
}
</script>

<style scoped>
.seccion-habitos-consumo-nutricion {
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

.form-control:focus {
  border-color: #5F6E7E;
  box-shadow: 0 0 0 0.2rem rgba(95, 110, 126, 0.25);
}

.form-check-input {
  width: 1.25rem;
  height: 1.25rem;
  margin-top: 0.2rem;
}

.form-check-label {
  font-size: 0.875rem;
  color: #2c3e50;
}

.label {
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  color: #2c3e50;
}

.text-muted {
  color: #6c757d !important;
}

.text-info {
  color: #17a2b8 !important;
}

.text-warning {
  color: #856404 !important;
}

.border-left {
  border-left: 4px solid !important;
}

.border-info {
  border-color: #17a2b8 !important;
}

.border-left.border-info {
  border-left-color: #17a2b8 !important;
}

.bg-light {
  background-color: #f8f9fa !important;
}
</style>