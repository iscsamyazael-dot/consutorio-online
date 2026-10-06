<template>
  <div class="seccion-diagnostico-nutricion">
    <!-- SECTION: DIAGNÓSTICO NUTRICIONAL -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-stethoscope mr-2"></i> DIAGNÓSTICO NUTRICIONAL
        </h5>
      </div>
      <div class="card-body">
        <!-- DIAGNÓSTICO / PROBLEMA NUTRICIONAL -->
        <div class="mb-4">
          <label class="font-weight-bold">Diagnóstico / Problema Nutricional <span class="text-danger">*</span></label>
          <textarea
            class="form-control"
            rows="4"
            v-model="form.diagnostico_problema"
            @input="updateField('diagnostico_problema', $event.target.value)"
            placeholder="Describa el diagnóstico nutricional (ej: Obesidad grado II, Desnutrición proteico-calórica, Dislipidemia mixta, Anemia ferropénica, etc.)"
            required
            style="height: 128px;"
          ></textarea>
        </div>

        <!-- CLASIFICACIÓN IMC (AUTO-CALCULADA) -->
        <div v-if="imcClasificacionAuto" class="mb-4 p-3 bg-light border border-left border-success">
          <label class="font-weight-bold d-block mb-2">Clasificación IMC (Auto-calculada)</label>
          <div class="d-flex align-items-center gap-3">
            <span class="h1 font-weight-bold text-success">{{ imcCalculado }}</span>
            <span class="badge badge-pill badge-success p-2" style="font-size: 1rem;">{{ imcClasificacionAuto }}</span>
          </div>
          <p class="text-xs text-muted mt-1 mb-0">Basado en estatura: {{ form.estatura_m }}m y peso: {{ form.peso_kg }}kg</p>
        </div>

        <!-- CLASIFICACIÓN IMC MANUAL (EDITABLE) -->
        <div class="mb-4">
          <label class="font-weight-bold">Clasificación IMC (Manual)</label>
          <select class="form-control"
                  v-model="form.imc_clasificacion"
                  @change="updateField('imc_clasificacion', $event.target.value)"
                  style="height: 38px;"
          >
            <option value="">Seleccionar...</option>
            <option value="Bajo peso">Bajo peso</option>
            <option value="Normal">Normal</option>
            <option value="Sobrepeso">Sobrepeso</option>
            <option value="Obesidad I">Obesidad I</option>
            <option value="Obesidad II">Obesidad II</option>
            <option value="Obesidad III">Obesidad III</option>
          </select>
        </div>

        <!-- DIAGNÓSTICOS COMUNES RÁPIDOS -->
        <div class="mb-4 p-3 bg-light border border-left border-info">
          <h5 class="font-weight-bold text-info mb-2 flex items-center gap-2">
            <i class="fas fa-bolt mr-2"></i>
            DIAGNÓSTICOS NUTRICIONALES COMUNES
          </h5>
          <div class="d-flex flex-wrap gap-2">
            <button
              type="button"
              v-for="dx in diagnosticosComunes"
              :key="dx"
              @click="usarDiagnostico(dx)"
              class="btn btn-outline-secondary btn-sm rounded-pill"
              style="padding: 0.25rem 0.75rem; font-size: 0.875rem;"
            >
              {{ dx }}
            </button>
          </div>
        </div>

        <!-- RESUMEN DE HALLAZGOS BIOQUÍMICOS -->
        <div v-if="hallazgosBioquimicos.length > 0" class="mt-4 p-3 bg-light border border-left border-purple">
          <h5 class="font-weight-bold text-purple mb-2 flex items-center gap-2">
            <i class="fas fa-flask mr-2"></i>
            HALLAZGOS BIOQUÍMICOS RELEVANTES
          </h5>
          <div class="d-flex flex-wrap gap-2">
            <span
              v-for="h in hallazgosBioquimicos"
              :key="h"
              class="badge badge-pill badge-purple p-2 text-xs"
            >
              {{ h }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DiagnosticoNutricion',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      diagnosticosComunes: [
        'Obesidad grado I',
        'Obesidad grado II',
        'Obesidad grado III',
        'Sobrepeso',
        'Bajo peso',
        'Desnutrición proteico-calórica leve',
        'Desnutrición proteico-calórica moderada',
        'Desnutrición proteico-calórica severa',
        'Dislipidemia mixta',
        'Hipertrigliceridemia',
        'Hipercolesterolemia',
        'Bajo HDL (hipoalfa lipoproteinemia)',
        'Anemia ferropénica',
        'Anemia por enfermedad crónica',
        'Hiperuricemia / Gota',
        'Resistencia a la insulina / Prediabetes',
        'Diabetes mellitus tipo 2',
        'Síndrome metabólico',
        'Hipervitaminosis / Deficiencia vit. D',
        'Deficiencia de hierro',
        'Deficiencia de B12 / Ácido fólico'
      ]
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },
    imcCalculado() {
      if (this.form.peso_kg && this.form.estatura_m && this.form.estatura_m > 0) {
        return (this.form.peso_kg / (this.form.estatura_m ** 2)).toFixed(2)
      }
      return null
    },
    imcClasificacionAuto() {
      if (!this.imcCalculado) return ''
      const imc = parseFloat(this.imcCalculado)
      if (imc < 18.5) return 'Bajo peso'
      if (imc < 25) return 'Normal'
      if (imc < 30) return 'Sobrepeso'
      if (imc < 35) return 'Obesidad I'
      if (imc < 40) return 'Obesidad II'
      return 'Obesidad III'
    },
    hallazgosBioquimicos() {
      const hallazgos = []
      if (this.form.glucosa_mg_dl >= 100) hallazgos.push(`Glucosa ${this.form.glucosa_mg_dl} mg/dL`)
      if (this.form.trigliceridos_mg_dl >= 150) hallazgos.push(`TG ${this.form.trigliceridos_mg_dl} mg/dL`)
      if (this.form.colesterol_total_mg_dl >= 200) hallazgos.push(`Col Total ${this.form.colesterol_total_mg_dl} mg/dL`)
      if (this.form.hdl_mg_dl) {
        const genero = this.form.paciente?.genero || 'M'
        const limite = (genero === 'F' || genero === 'Femenino') ? 50 : 40
        if (this.form.hdl_mg_dl < limite) hallazgos.push(`HDL bajo ${this.form.hdl_mg_dl} mg/dL`)
      }
      if (this.form.ldl_mg_dl >= 130) hallazgos.push(`LDL ${this.form.ldl_mg_dl} mg/dL`)
      if (this.form.acido_urico_mg_dl) {
        const genero = this.form.paciente?.genero || 'M'
        const max = (genero === 'F' || genero === 'Femenino') ? 6.0 : 7.0
        if (this.form.acido_urico_mg_dl > max) hallazgos.push(`Ác. Úrico ${this.form.acido_urico_mg_dl} mg/dL`)
      }
      if (this.form.hemoglobina_g_dl) {
        const genero = this.form.paciente?.genero || 'M'
        const min = (genero === 'F' || genero === 'Femenino') ? 12.0 : 13.5
        if (this.form.hemoglobina_g_dl < min) hallazgos.push(`Hb baja ${this.form.hemoglobina_g_dl} g/dL`)
      }
      return hallazgos
    }
  },
  methods: {
    updateField(key, value) {
      this.$emit('update:modelValue', { ...this.form, [key]: value })
    },
    usarDiagnostico(dx) {
      this.updateField('diagnostico_problema', dx)
      // Auto-seleccionar clasificación IMC si coincide
      if (dx.includes('Obesidad I')) this.updateField('imc_clasificacion', 'Obesidad I')
      else if (dx.includes('Obesidad II')) this.updateField('imc_clasificacion', 'Obesidad II')
      else if (dx.includes('Obesidad III')) this.updateField('imc_clasificacion', 'Obesidad III')
      else if (dx.includes('Sobrepeso')) this.updateField('imc_clasificacion', 'Sobrepeso')
      else if (dx.includes('Bajo peso')) this.updateField('imc_clasificacion', 'Bajo peso')
      else if (dx.includes('Desnutrición')) this.updateField('imc_clasificacion', 'Bajo peso')
    }
  }
}
</script>

<style scoped>
.seccion-diagnostico-nutricion {
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

.label {
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  color: #2c3e50;
}

.text-muted {
  color: #6c757d !important;
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
  background-color: #e9ecef;
}

.badge {
  font-size: 0.875rem;
  padding: 0.5rem 1rem;
  border-radius: 2rem;
}

.badge-primary {
  background-color: #5F6E7E;
  color: white;
}

.badge-success {
  background-color: #28a745;
  color: white;
}

.badge-info {
  background-color: #17a2b8;
  color: white;
}

.badge-purple {
  background-color: #6f42c1;
  color: white;
}

.text-success { color: #28a745 !important; }
.text-info { color: #17a2b8 !important; }
.text-purple { color: #6f42c1 !important; }

.border-left {
  border-left: 4px solid !important;
}

.border-success { border-color: #28a745 !important; }
.border-info { border-color: #17a2b8 !important; }
.border-purple { border-color: #6f42c1 !important; }

.border-left.border-success { border-left-color: #28a745 !important; }
.border-left.border-info { border-left-color: #17a2b8 !important; }
.border-left.border-purple { border-left-color: #6f42c1 !important; }

.bg-light { background-color: #f8f9fa !important; }
</style>