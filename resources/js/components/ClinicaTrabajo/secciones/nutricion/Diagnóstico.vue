<template>
  <div class="seccion-diagnostico-nutricion">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-stethoscope text-green-600"></i>
        Diagnóstico Nutricional
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Diagnóstico / Problema nutricional -->
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Diagnóstico / Problema Nutricional *</label>
          <textarea
            v-model="form.diagnostico_problema"
            rows="4"
            class="input-field w-full"
            placeholder="Describa el diagnóstico nutricional (ej: Obesidad grado II, Desnutrición proteico-calórica, Dislipidemia mixta, Anemia ferropénica, etc.)"
            required
          ></textarea>
        </div>

        <!-- Clasificación IMC (auto-calculada) -->
        <div v-if="imcClasificacionAuto" class="bg-green-50 border border-green-200 rounded-lg p-3">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Clasificación IMC (Auto-calculada)</label>
          <div class="flex items-center gap-3">
            <span class="text-2xl font-bold text-green-700">{{ imcCalculado }}</span>
            <span class="badge badge-green text-lg px-3 py-1">{{ imcClasificacionAuto }}</span>
          </div>
          <p class="text-xs text-green-700 mt-1">Basado en estatura: {{ form.estatura_m }}m y peso: {{ form.peso_kg }}kg</p>
        </div>

        <!-- Clasificación IMC manual (editable) -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Clasificación IMC (Manual)</label>
          <select v-model="form.imc_clasificacion" @change="updateField('imc_clasificacion', $event.target.value)" class="input-field w-full">
            <option value="">Seleccionar...</option>
            <option value="Bajo peso">Bajo peso</option>
            <option value="Normal">Normal</option>
            <option value="Sobrepeso">Sobrepeso</option>
            <option value="Obesidad I">Obesidad I</option>
            <option value="Obesidad II">Obesidad II</option>
            <option value="Obesidad III">Obesidad III</option>
          </select>
        </div>
      </div>

      <!-- Diagnósticos comunes rápidos -->
      <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
        <h4 class="font-semibold text-blue-800 mb-2 flex items-center gap-2">
          <i class="icon icon-zap"></i>
          Diagnósticos Nutricionales Comunes
        </h4>
        <div class="flex flex-wrap gap-2">
          <button
            type="button"
            v-for="dx in diagnosticosComunes"
            :key="dx"
            @click="usarDiagnostico(dx)"
            class="px-3 py-1 bg-white border border-blue-300 rounded text-sm text-blue-700 hover:bg-blue-50 transition"
          >
            {{ dx }}
          </button>
        </div>
      </div>

      <!-- Resumen de hallazgos bioquímicos -->
      <div v-if="hallazgosBioquimicos.length > 0" class="mt-4 p-3 bg-purple-50 border border-purple-200 rounded">
        <h4 class="font-semibold text-purple-800 mb-2 flex items-center gap-2">
          <i class="icon icon-flask"></i>
          Hallazgos Bioquímicos Relevantes
        </h4>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="h in hallazgosBioquimicos"
            :key="h"
            class="badge badge-purple text-xs"
          >
            {{ h }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DiagnósticoNutricion',
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
.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.7rem;
  font-weight: 600;
}
.badge-green { background: #dcfce7; color: #166534; }
.badge-purple { background: #f3e8ff; color: #7e22ce; }
</style>