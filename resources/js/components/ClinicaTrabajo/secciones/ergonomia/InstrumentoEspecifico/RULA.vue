<template>
  <div class="rula-form border rounded-lg p-4">
    <h4 class="font-semibold mb-2">RULA – Rapid Upper Limb Assessment</h4>
    <div class="grid grid-cols-2 gap-4">
      <label class="block text-sm">Puntuación (1-7)</label>
      <input v-model.number="form.rula_puntuacion" type="number" min="1" max="7" class="input-field w-full" />
    </div>
    <div class="grid grid-cols-2 gap-4 mt-4">
      <label class="block text-sm">Nivel de Riesgo</label>
      <select v-model="form.rula_nivel_riesgo" class="input-field w-full">
        <option value="">Seleccionar...</option>
        <option value="insignificante">Insignificante</option>
        <option value="bajo">Bajo</option>
        <option value="medio">Medio</option>
        <option value="alto">Alto</option>
      </select>
    </div>
  </div>
</template>

<script>
export default {
  name: 'RULAForm',
  props: {
    modelValue: { type: Object, required: true }
  },
  emits: ['update:modelValue'],
  computed: {
    form: {
      get() { return this.modelValue },
      set(v) { this.$emit('update:modelValue', v) }
    }
  }
}
</script>