<template>
  <div class="seccion-habitos-consumo-nutricion">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-apple text-orange-600"></i>
        Hábitos y Consumo
      </h3>

      <!-- Tabaquismo y Alcoholismo -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.tabaquismo"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              @change="updateField('tabaquismo', $event.target.checked)"
            />
            <span class="font-semibold text-gray-700">Tabaquismo</span>
          </label>
        </div>

        <div>
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.alcoholismo"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              @change="updateField('alcoholismo', $event.target.checked)"
            />
            <span class="font-semibold text-gray-700">Alcoholismo</span>
          </label>
        </div>

        <div>
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.varia_consumo_estres"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              @change="updateField('varia_consumo_estres', $event.target.checked)"
            />
            <span class="font-semibold text-gray-700">Varía consumo por estrés</span>
          </label>
        </div>

        <div>
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.varia_consumo_tristeza"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              @change="updateField('varia_consumo_tristeza', $event.target.checked)"
            />
            <span class="font-semibold text-gray-700">Varía consumo por tristeza</span>
          </label>
        </div>
      </div>

      <!-- Escalas validadas -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Escala Fagerström (0-10)</label>
          <input
            type="number"
            v-model.number="form.escala_fagerstrm"
            @input="updateField('escala_fagerstrm', $event.target.value)"
            min="0"
            max="10"
            class="input-field w-full"
            placeholder="0-10: Dependencia a nicotina"
          />
          <p class="text-xs text-gray-500 mt-1">≥5: Dependencia moderada-severa</p>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Escala AUDIT (0-40)</label>
          <input
            type="number"
            v-model.number="form.escala_audit"
            @input="updateField('escala_audit', $event.target.value)"
            min="0"
            max="40"
            class="input-field w-full"
            placeholder="0-40: Consumo riesgo alcohol"
          />
          <p class="text-xs text-gray-500 mt-1">≥8: Consumo riesgoso; ≥20: Dependencia</p>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Frecuencia Consumo Alcohol</label>
          <select v-model="form.frecuencia_consumo_alcohol" @change="updateField('frecuencia_consumo_alcohol', $event.target.value)" class="input-field w-full">
            <option value="">Seleccionar...</option>
            <option value="nunca">Nunca</option>
            <option value="mensual">Mensual</option>
            <option value="semanal">Semanal</option>
            <option value="diario">Diario</option>
          </select>
        </div>
      </div>

      <!-- Síntomas gastrointestinales -->
      <div class="mt-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-alert text-red-600"></i>
          Síntomas Gastrointestinales
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          <label
            v-for="sintoma in sintomasGI"
            :key="sintoma.key"
            class="flex items-center gap-2 cursor-pointer"
          >
            <input
              type="checkbox"
              :checked="form[sintoma.key]"
              @change="updateField(sintoma.key, $event.target.checked)"
              class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500"
            />
            <span class="text-sm text-gray-700">{{ sintoma.label }}</span>
          </label>
        </div>
      </div>

      <!-- Auto-cálculo tabaquismo/alcoholismo desde escalas -->
      <div v-if="form.escala_fagerstrm > 0 || form.escala_audit > 0" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
        <h4 class="font-semibold text-blue-800 mb-1">Auto-detección:</h4>
        <div class="text-sm text-blue-700 space-y-1">
          <div v-if="form.escala_fagerstrm > 0">
            <span class="font-semibold">Fagerström {{ form.escala_fagerstrm }}/10: </span>
            {{ form.escala_fagerstrm >= 5 ? 'Dependencia moderada-severa → Tabaquismo = SÍ' : 'Dependencia leve' }}
          </div>
          <div v-if="form.escala_audit > 0">
            <span class="font-semibold">AUDIT {{ form.escala_audit }}/40: </span>
            {{ form.escala_audit >= 20 ? 'Dependencia probable → Alcoholismo = SÍ' : form.escala_audit >= 8 ? 'Consumo riesgoso' : 'Consumo de bajo riesgo' }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HábitosConsumoNutricion',
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
/* Estilos heredados */
</style>