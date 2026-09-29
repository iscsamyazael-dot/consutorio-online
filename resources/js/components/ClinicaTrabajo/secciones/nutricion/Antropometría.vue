<template>
  <div class="seccion-antropometria-nutricion">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-ruler text-indigo-600"></i>
        Antropometría y Composición Corporal
      </h3>

      <!-- Medidas principales con auto-cálculo IMC -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Estatura (m) *</label>
          <input
            type="number"
            v-model.number="form.estatura_m"
            @input="updateField('estatura_m', $event.target.value)"
            step="0.01"
            min="0.5"
            max="2.5"
            class="input-field w-full"
            placeholder="Ej: 1.70"
            required
          />
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Peso (kg) *</label>
          <input
            type="number"
            v-model.number="form.peso_kg"
            @input="updateField('peso_kg', $event.target.value)"
            step="0.1"
            min="20"
            max="300"
            class="input-field w-full"
            placeholder="Ej: 75.5"
            required
          />
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Peso Ideal (kg)</label>
          <input
            type="number"
            v-model.number="form.peso_ideal_kg"
            @input="updateField('peso_ideal_kg', $event.target.value)"
            step="0.1"
            min="20"
            max="200"
            class="input-field w-full"
            placeholder="Opcional"
          />
        </div>

        <div class="flex items-end">
          <label class="block text-sm font-semibold text-gray-700 mb-1 w-full">IMC Calculado</label>
          <div class="input-field w-full bg-gray-50 font-bold text-lg text-indigo-700 p-3" v-if="imcCalculado">
            {{ imcCalculado }} ({{ imcClasificacionAuto }})
          </div>
          <div class="input-field w-full bg-gray-100 text-gray-400 p-3" v-else>
            Complete estatura y peso
          </div>
        </div>
      </div>

      <!-- Circunferencias e índices -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Cintura (cm)</label>
          <input
            type="number"
            v-model.number="form.circunferencia_cintura_cm"
            @input="updateField('circunferencia_cintura_cm', $event.target.value)"
            step="0.1"
            min="40"
            max="200"
            class="input-field w-full"
          />
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Cadera (cm)</label>
          <input
            type="number"
            v-model.number="form.circunferencia_cadera_cm"
            @input="updateField('circunferencia_cadera_cm', $event.target.value)"
            step="0.1"
            min="40"
            max="200"
            class="input-field w-full"
          />
        </div>

        <div class="flex items-end">
          <label class="block text-sm font-semibold text-gray-700 mb-1 w-full">ICC (Cintura/Cadera)</label>
          <div class="input-field w-full bg-gray-50 font-bold text-lg text-indigo-700 p-3" v-if="iccCalculado">
            {{ iccCalculado }}
          </div>
          <div class="input-field w-full bg-gray-100 text-gray-400 p-3" v-else>
            —
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Brazo (cm)</label>
          <input
            type="number"
            v-model.number="form.circunferencia_brazo_cm"
            @input="updateField('circunferencia_brazo_cm', $event.target.value)"
            step="0.1"
            min="15"
            max="100"
            class="input-field w-full"
          />
        </div>
      </div>

      <!-- Composición corporal (bioimpedancia) -->
      <div class="mt-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-chart text-indigo-600"></i>
          Composición Corporal (Bioimpedancia)
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Grasa Corporal (%)</label>
            <input
              type="number"
              v-model.number="form.grasa_corporal_pct"
              @input="updateField('grasa_corporal_pct', $event.target.value)"
              step="0.1"
              min="0"
              max="100"
              class="input-field w-full"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Grasa Visceral (%)</label>
            <input
              type="number"
              v-model.number="form.grasa_visceral_pct"
              @input="updateField('grasa_visceral_pct', $event.target.value)"
              step="0.1"
              min="0"
              max="100"
              class="input-field w-full"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Músculo (%)</label>
            <input
              type="number"
              v-model.number="form.musculo_pct"
              @input="updateField('musculo_pct', $event.target.value)"
              step="0.1"
              min="0"
              max="100"
              class="input-field w-full"
            />
          </div>
        </div>
      </div>

      <!-- Resumen clasificación -->
      <div v-if="imcCalculado" class="mt-4 p-3 bg-indigo-50 border border-indigo-200 rounded">
        <h4 class="font-semibold text-indigo-800 mb-2">Resumen Antropométrico</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-sm">
          <div>
            <span class="font-semibold text-gray-600">IMC: </span>
            <span class="font-bold text-indigo-700">{{ imcCalculado }}</span>
            <span :class="imcBadgeClass" class="ml-2 badge">{{ imcClasificacionAuto }}</span>
          </div>
          <div>
            <span class="font-semibold text-gray-600">ICC: </span>
            <span class="font-bold">{{ iccCalculado || '—' }}</span>
            <span class="ml-2" v-if="iccCalculado">
              {{ iccRiesgo }}
            </span>
          </div>
          <div>
            <span class="font-semibold text-gray-600">Riesgo Cardiometabólico: </span>
            <span class="font-bold" :class="riesgoCardiometabolicoClass">{{ riesgoCardiometabolico }}</span>
          </div>
        </div>
      </div>

      <!-- Tablas de referencia -->
      <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded">
        <h4 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
          <i class="icon icon-table"></i>
          Referencias OMS / ATP III
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-600">
          <div>
            <p class="font-semibold text-gray-800 mb-1">Clasificación IMC (OMS)</p>
            <ul class="space-y-1 list-disc list-inside">
              <li>< 18.5: Bajo peso</li>
              <li>18.5 - 24.9: Normal</li>
              <li>25.0 - 29.9: Sobrepeso</li>
              <li>30.0 - 34.9: Obesidad I</li>
              <li>35.0 - 39.9: Obesidad II</li>
              <li>≥ 40.0: Obesidad III</li>
            </ul>
          </div>
          <div>
            <p class="font-semibold text-gray-800 mb-1">ICC - Riesgo Cardiovascular (ATP III)</p>
            <ul class="space-y-1 list-disc list-inside">
              <li>Hombres: > 0.90 = Riesgo ↑</li>
              <li>Mujeres: > 0.85 = Riesgo ↑</li>
              <li>Cintura H: > 102 cm = Riesgo ↑</li>
              <li>Cintura M: > 88 cm = Riesgo ↑</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AntropometríaNutricion',
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
    imcBadgeClass() {
      const imc = parseFloat(this.imcCalculado || 0)
      if (imc < 18.5) return 'badge-blue'
      if (imc < 25) return 'badge-green'
      if (imc < 30) return 'badge-yellow'
      if (imc < 35) return 'badge-orange'
      if (imc < 40) return 'badge-red'
      return 'badge-dark-red'
    },
    iccCalculado() {
      if (this.form.circunferencia_cintura_cm && this.form.circunferencia_cadera_cm && this.form.circunferencia_cadera_cm > 0) {
        return (this.form.circunferencia_cintura_cm / this.form.circunferencia_cadera_cm).toFixed(2)
      }
      return null
    },
    iccRiesgo() {
      if (!this.iccCalculado) return ''
      const icc = parseFloat(this.iccCalculado)
      const genero = this.form.paciente?.genero || 'M'
      if (genero === 'F' || genero === 'Femenino') {
        return icc > 0.85 ? '⚠️ Riesgo ↑' : '✅ Normal'
      }
      return icc > 0.90 ? '⚠️ Riesgo ↑' : '✅ Normal'
    },
    riesgoCardiometabolico() {
      const riesgos = []
      const imc = parseFloat(this.imcCalculado || 0)
      const cintura = this.form.circunferencia_cintura_cm
      const genero = this.form.paciente?.genero || 'M'

      if (imc >= 30) riesgos.push('Obesidad')
      if (cintura && ((genero === 'F' || genero === 'Femenino') ? cintura > 88 : cintura > 102)) {
        riesgos.push('Cintura ↑')
      }
      if (this.iccCalculado && ((genero === 'F' || genero === 'Femenino') ? parseFloat(this.iccCalculado) > 0.85 : parseFloat(this.iccCalculado) > 0.90)) {
        riesgos.push('ICC ↑')
      }

      return riesgos.length > 0 ? riesgos.join(', ') : 'Bajo'
    },
    riesgoCardiometabolicoClass() {
      return this.riesgoCardiometabolico === 'Bajo' ? 'text-green-700' : 'text-red-700'
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