<template>
  <div class="seccion-factores-laborales-nutricion">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-briefcase text-blue-600"></i>
        Factores de Riesgo Laboral (NOM-035 / NOM-036)
      </h3>

      <p class="text-sm text-gray-600 mb-4">
        Evalúe la exposición a factores de riesgo psicosocial y ergonómico.
        <span class="font-semibold">Escala: Bajo / Medio / Alto / Muy Alto</span>
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="factor in factoresLaborales"
          :key="factor.key"
          class="border rounded-lg p-3 hover:bg-gray-50 transition"
        >
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            {{ factor.label }}
          </label>
          <select
            v-model="form[factor.key]"
            @change="updateField(factor.key, $event.target.value)"
            class="input-field w-full"
          >
            <option value="">Seleccionar...</option>
            <option value="bajo">Bajo</option>
            <option value="medio">Medio</option>
            <option value="alto">Alto</option>
            <option value="muy_alto">Muy Alto</option>
          </select>
        </div>
      </div>

      <!-- Resumen de factores altos -->
      <div v-if="factoresAltos.length > 0" class="mt-4 p-3 bg-orange-50 border border-orange-200 rounded">
        <h4 class="font-semibold text-orange-800 mb-2 flex items-center gap-2">
          <i class="icon icon-alert"></i>
          Factores con Nivel Alto/Muy Alto ({{ factoresAltos.length }})
        </h4>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="f in factoresAltos"
            :key="f.key"
            class="badge badge-orange text-xs"
          >
            {{ f.label }}: {{ form[f.key] }}
          </span>
        </div>
        <p class="text-xs text-orange-700 mt-2">
          Estos factores requieren atención prioritaria en el plan nutricional y recomendaciones ergonómicas.
        </p>
      </div>

      <!-- Referencia normativa -->
      <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded">
        <h4 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
          <i class="icon icon-book"></i>
          Referencia Normativa
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-600">
          <div><span class="font-semibold">NOM-035-STPS-2018:</span> Factores de riesgo psicosocial</div>
          <div><span class="font-semibold">NOM-036-1-STPS-2018:</span> Factores de riesgo ergonómico</div>
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
      factoresLaborales: [
        { key: 'tension_emocional', label: 'Tensión Emocional' },
        { key: 'alta_responsabilidad', label: 'Alta Responsabilidad' },
        { key: 'carga_excesiva_trabajo', label: 'Carga Excesiva de Trabajo' },
        { key: 'turno_rotativo', label: 'Turno Rotativo' },
        { key: 'turno_nocturno', label: 'Turno Nocturno' },
        { key: 'trabajo_repetitivo', label: 'Trabajo Repetitivo' },
        { key: 'actividad_rapida_variable', label: 'Actividad Rápida/Variable' },
        { key: 'actividad_monotona_lenta', label: 'Actividad Monotona/Lenta' },
        { key: 'exp_temperatura_elevada_baja', label: 'Exposición Temperatura Extrema' }
      ]
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },
    factoresAltos() {
      return this.factoresLaborales.filter(f =>
        ['alto', 'muy_alto'].includes(this.form[f.key])
      )
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
.badge-orange {
  background: #ffedd5;
  color: #c2410c;
}
</style>