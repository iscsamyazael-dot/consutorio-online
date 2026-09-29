<template>
  <div class="seccion-hallazgos-ergonomia">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-list text-orange-600"></i>
        Hallazgos Ergonómicos
      </h3>

      <p class="text-sm text-gray-600 mb-4">
        Registro de factores de riesgo ergonómicos observados durante la evaluación. Cada factor tiene una puntuación de riesgo.
      </p>

      <!-- Tabla de riesgo -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <label class="block text-sm font-semibold text-gray-700 mb-1">RULA Puntuación (1-7)</label>
        <input v-model.number="form.rula_puntuacion" type="number" min="1" max="7" class="input-field w-full" />
        <label class="block text-sm font-semibold text-gray-700 mb-1">RULA Nivel (Insignificante/ Bajo/ Medio/ Alto)</label>
        <select v-model="form.rula_nivel_riesgo" class="input-field w-full">
          <option value="">Seleccionar...</option>
          <option value="insignificante">Insignificante</option>
          <option value="bajo">Bajo</option>
          <option value="medio">Medio</option>
          <option value="alto">Alto</option>
        </select>
        <label class="block text-sm font-semibold text-gray-700 mb-1">REBA Puntuación (1-15)</label>
        <input v-model.number="form.reba_puntuacion" type="number" min="1" max="15" class="input-field w-full" />
        <label class="block text-sm font-semibold text-gray-700 mb-1">REBA Nivel (Instig)</label>
        <select v-model="form.reba_nivel_accion" class="input-field w-full">
          <option value="">Seleccionar...</option>
          <option value="insignificante">Insignificante</option>
          <option value="bajo">Bajo</option>
          <option value="medio">Medio</option>
          <option value="alto">Alto</option>
          <option value="muy_alto">Muy Alto</option>
          <option value="cambios_inmediatos">Cambios Inmediatos</option>
        </select>
        <label class="block text-sm font-semibold text-gray-700 mb-1">NIOSH Índice (0-10)</label>
        <input v-model.number="form.niosh_indice_levantamiento" type="number" min="0" max="10" step="0.1" class="input-field w-full" />
        <label class="block text-sm font-semibold text-gray-700 mb-1">NIOSH Seguro?</label>
        <input v-model="form.niosh_es_seguro" type="checkbox" class="w-4 h-4" />
      </div>

      <div class="mb-4">
        <label class="block text-sm font-semibold text-gray-700 mb-1">Riesgos Ergonómicos (Checklist)</label>
        <ul class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2">
          <li v-for="risgo in riesgosErgonomicos" :key="risgo">
            <label class="flex items-center gap-2">
              <input type="checkbox" :checked="form[risgo]" @change="setRiesgo(risgo, $event.target.checked)" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
              />
              {{ risgo }}
            </label>
          </li>
        </ul>
      </div>

      <div class="mt-4 p-3 border rounded-lg bg-gray-50">
        <h4 class="font-semibold text-gray-700 mb-2">Resumen</h4>
        <p class="text-sm text-gray-600">El nivel de riesgo total se evalúa combinando RULA, REBA y NIOSH y considerando los riesgos ergonómicos seleccionados.</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HallazgosErgonomia',
  props: {
    modelValue: { type: Object, required: true }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      riesgosErgonomicos: [
        'Posturas forzadas',
        'Movimientos repetitivos',
        'Manipulación de cargas',
        'Cargas máximas manipuladas',
        'Exposición al calor',
        'Exposición a vibraciones',
        'Iluminación insuficiente'
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
    setRiesgo(risk, checked) {
      this.$emit('update:modelValue', { ...this.form, [risk]: checked })
    }
  }
}
</script>

<style scoped>
/* Estilos heredados */
</style>