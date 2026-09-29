<template>
  <div class="nioshl-form border rounded-lg p-4">
    <h4 class="font-semibold mb-2">NIOSH – Índice de Levantamiento</h4>
    <div class="grid grid-cols-2 gap-4">
      <label class="block text-sm">Índice (0-10)</label>
      <input v-model.number="form.niosh_indice_levantamiento" type="number" min="0" max="10" step="0.1" class="input-field w-full" />
    </div>
    <div class="grid grid-cols-2 gap-4 mt-4">
      <label class="block text-sm">¿Es Seguro?</label>
      <input v-model="form.niosh_es_seguro" type="checkbox" class="w-4 h-4" />
    </div>
    <div class="grid grid-cols-2 gap-4 mt-4">
      <label class="block text-sm">Peso Real (kg)</label>
      <input v-model.number="form.niosh_peso_real_kg" type="number" min="0" step="0.1" class="input-field w-full" />
      <label class="block text-sm">Peso Recomendado (kg)</label>
      <input v-model.number="form.niosh_peso_recomendado_kg" type="number" min="0" step="0.1" class="input-field w-full" />
    </div>
  </div>
</template>

<script>
export default {
  name: 'NIOSHForm',
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