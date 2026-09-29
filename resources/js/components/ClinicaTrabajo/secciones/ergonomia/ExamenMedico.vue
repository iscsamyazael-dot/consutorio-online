<template>
  <div class="seccion-examen-medico-ergonomia">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-eye text-purple-600"></i>
        Examen Médico Ergonómico
      </h3>

      <p class="text-sm text-gray-600 mb-4">
        Registro de hallazgos clínicos relevantes (dolor, lesiones, etc.) y la decisión sobre seguimiento.
      </p>

      <!-- Examen médico -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <label class="block text-sm font-semibold text-gray-700 mb-1">Examen médico realizado *</label>
        <input v-model="form.examen_medico_realizado" type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" />
      </div>

      <!-- Seguimiento requerido -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4" v-if="form.examen_medico_realizado">
        <label class="block text-sm font-semibold text-gray-700 mb-1">Requiere seguimiento*</label>
        <input v-model="form.seguimiento_requerido" type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" />
      </div>

      <div class="mt-4 p-3 bg-indigo-50 border border-indigo-200 rounded" v-if="form.seguimiento_requerido">
        <h4 class="font-semibold text-indigo-800 mb-2">Próximo Control</h4>
        <input v-model="form.plazo_proximo_seguimiento" type="date" class="input-field w-full" />
      </div>

      <div class="mt-4 p-3 border rounded-lg bg-gray-50">
        <h4 class="font-semibold text-gray-700 mb-2">Reseña de Examen</h4>
        <textarea v-model="form.detalles_examen" rows="4" class="input-field w-full" placeholder="Detalles del examen médico, hallazgos."></textarea>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ExamenMedicoErgonomia',
  props: {
    modelValue: { type: Object, required: true }
  },
  emits: ['update:modelValue'],
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    }
  }
}
</script>

<style scoped>
/* Estilos heredados */
</style>