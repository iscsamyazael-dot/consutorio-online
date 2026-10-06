<template>
  <div class="seccion-clinico-examen-medicina">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-stethoscope text-green-600"></i>
        Examen Clínico y Funcional
      </h3>

      <!-- Signos Vitales -->
      <div class="mb-4">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-heart text-red-500"></i>
          Signos Vitales
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">TA Sistólica (mmHg)</label>
            <input type="number" v-model.number="form.ta_sistolica" @input="updateField('ta_sistolica', $event.target.value)" min="50" max="250" class="input-field w-full" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">TA Diastólica (mmHg)</label>
            <input type="number" v-model.number="form.ta_diastolica" @input="updateField('ta_diastolica', $event.target.value)" min="30" max="150" class="input-field w-full" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">FC (lpm)</label>
            <input type="number" v-model.number="form.fc" @input="updateField('fc', $event.target.value)" min="30" max="250" class="input-field w-full" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">FR (rpm)</label>
            <input type="number" v-model.number="form.fr" @input="updateField('fr', $event.target.value)" min="5" max="40" class="input-field w-full" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">SpO2 (%)</label>
            <input type="number" v-model.number="form.spo2" @input="updateField('spo2', $event.target.value)" min="70" max="100" step="0.1" class="input-field w-full" />
          </div>
        </div>

        <div v-if="form.ta_sistolica && form.ta_diastolica" class="mt-3 p-3 bg-gray-50 rounded">
          <span class="font-semibold text-gray-700">Presión Arterial: </span>
          <span class="font-mono text-lg text-blue-700">{{ form.ta_sistolica }} / {{ form.ta_diastolica }} mmHg</span>
          <span class="ml-3" :class="clasificacionTAClass">{{ clasificacionTA }}</span>
        </div>
      </div>

      <!-- Antropometría -->
      <div class="mb-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-ruler text-indigo-500"></i>
          Antropometría
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Estatura (cm)</label>
            <input type="number" v-model.number="form.estatura_cm" @input="updateField('estatura_cm', $event.target.value)" min="50" max="250" step="0.1" class="input-field w-full" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Peso (kg)</label>
            <input type="number" v-model.number="form.peso_kg" @input="updateField('peso_kg', $event.target.value)" min="2" max="300" step="0.1" class="input-field w-full" />
          </div>
          <div class="flex items-end">
            <label class="block text-sm font-semibold text-gray-700 mb-1 w-full">IMC</label>
            <div class="input-field w-full bg-indigo-50 text-indigo-700 font-bold text-lg py-3 px-4 rounded" v-if="imcCalculado">
              {{ imcCalculado }} ({{ imcClasificacionAuto }})
            </div>
            <div class="input-field w-full bg-gray-100 text-gray-400 py-3 px-4 rounded" v-else>
              Complete estatura y peso
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Circ. Abdominal (cm)</label>
            <input type="number" v-model.number="form.talla_abdomen_cm" @input="updateField('talla_abdomen_cm', $event.target.value)" min="40" max="200" step="0.1" class="input-field w-full" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4" v-if="imcCalculado || form.grasa_visceral || form.grasa_corporal_pct || form.musculo_pct">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Grasa Visceral</label>
            <input type="number" v-model.number="form.grasa_visceral" @input="updateField('grasa_visceral', $event.target.value)" min="0" max="50" step="0.1" class="input-field w-full" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Grasa Corporal (%)</label>
            <input type="number" v-model.number="form.grasa_corporal_pct" @input="updateField('grasa_corporal_pct', $event.target.value)" min="0" max="100" step="0.1" class="input-field w-full" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Músculo (%)</label>
            <input type="number" v-model.number="form.musculo_pct" @input="updateField('musculo_pct', $event.target.value)" min="0" max="100" step="0.1" class="input-field w-full" />
          </div>
        </div>

        <div v-if="imcCalculado" class="mt-3 p-3 bg-indigo-50 border border-indigo-200 rounded">
          <span class="font-semibold text-indigo-800">Resumen: </span>
          <span class="mx-2" :class="imcBadgeClass">{{ imcClasificacionAuto }}</span>
          <span class="text-indigo-700" v-if="form.talla_abdomen_cm">| Circ. Abdominal: {{ form.talla_abdomen_cm }} cm {{ riesgoAbdominal }}</span>
        </div>
      </div>

      <!-- Antecedentes -->
      <div class="mb-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-history text-purple-500"></i>
          Antecedentes
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Patológicos Activos</label>
            <textarea v-model="form.antecedentes_patologicos_activos" rows="3" class="input-field w-full" placeholder="HTA, DM, dislipidemia, cardiopatías, etc."></textarea>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Heredofamiliares</label>
            <textarea v-model="form.antecedentes_heredofamiliares" rows="3" class="input-field w-full" placeholder="Antecedentes familiares relevantes..."></textarea>
          </div>
        </div>
        <div class="mt-3">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Estilo de Vida y Hábitos</label>
          <textarea v-model="form.estilo_vida_habitos" rows="2" class="input-field w-full" placeholder="Tabaquismo, alcohol, ejercicio, sueño, alimentación..."></textarea>
        </div>
      </div>

      <!-- Exploración Física Funcional -->
      <div class="mb-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-activity text-orange-500"></i>
          Exploración Física Funcional
        </h4>
        <textarea v-model="form.exploracion_fisica_funcional" rows="4" class="input-field w-full" placeholder="Aparatos y sistemas, rangos de movimiento, fuerza muscular, pruebas funcionales específicas del puesto..."></textarea>
      </div>

      <!-- Estudios Paraclínicos -->
      <div class="mb-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-file-text text-blue-500"></i>
          Estudios Paraclínicos (Resumen)
        </h4>
        <textarea v-model="form.estudios_paraclinicos_resumen" rows="3" class="input-field w-full" placeholder="Laboratorio, gabinete, estudios de imagen, pruebas especiales..."></textarea>
      </div>

      <!-- Análisis Correlación Riesgos -->
      <div class="pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-link text-teal-500"></i>
          Análisis de Correlación Riesgo-Salud
        </h4>
        <textarea v-model="form.analisis_correlacion_riesgos" rows="3" class="input-field w-full" placeholder="Relación entre riesgos identificados en Exposición & Riesgos y hallazgos clínicos..."></textarea>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ClinicoExamenMedicina',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },
    imcCalculado() {
      if (this.form.peso_kg && this.form.estatura_cm && this.form.estatura_cm > 0) {
        return (this.form.peso_kg / ((this.form.estatura_cm / 100) ** 2)).toFixed(1)
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
    imcBadgeClass() {
      const imc = parseFloat(this.imcCalculado || 0)
      if (imc < 18.5) return 'badge-blue'
      if (imc < 25) return 'badge-green'
      if (imc < 30) return 'badge-yellow'
      if (imc < 35) return 'badge-orange'
      if (imc < 40) return 'badge-red'
      return 'badge-dark-red'
    },
    clasificacionTA() {
      if (!this.form.ta_sistolica || !this.form.ta_diastolica) return ''
      const sis = this.form.ta_sistolica
      const dia = this.form.ta_diastolica
      if (sis < 120 && dia < 80) return 'Normal'
      if (sis < 130 && dia < 80) return 'Elevada'
      if ((sis >= 130 && sis < 140) || (dia >= 80 && dia < 90)) return 'HTA Estadio 1'
      if (sis >= 140 || dia >= 90) return 'HTA Estadio 2'
      if (sis > 180 || dia > 120) return 'Crisis Hipertensiva'
      return ''
    },
    clasificacionTAClass() {
      const cls = {
        'Normal': 'text-green-600',
        'Elevada': 'text-yellow-600',
        'HTA Estadio 1': 'text-orange-600',
        'HTA Estadio 2': 'text-red-600',
        'Crisis Hipertensiva': 'text-red-800 font-bold',
      }
      return cls[this.clasificacionTA] || ''
    },
    riesgoAbdominal() {
      if (!this.form.talla_abdomen_cm) return ''
      const valor = this.form.talla_abdomen_cm
      // Valores de riesgo según IDF (varía por género, aquí general)
      if (valor > 102) return '⚠️ Riesgo cardiovascular aumentado (H)'
      if (valor > 88) return '⚠️ Riesgo cardiovascular aumentado (M)'
      return '✅ Normal'
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
.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.7rem;
  font-weight: 600;
}
.badge-blue { background: #dbeafe; color: #1e40af; }
.badge-green { background: #dcfce7; color: #166534; }
.badge-yellow { background: #fef3c7; color: #92400e; }
.badge-orange { background: #ffedd5; color: #c2410c; }
.badge-red { background: #fee2e2; color: #991b1b; }
.badge-dark-red { background: #7f1d1d; color: #fecaca; }
</style>