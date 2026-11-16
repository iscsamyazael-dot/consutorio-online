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
      <label class="block text-sm">Peso Recomendado (kg)</label>
      <div class="input-field w-full bg-light">{{ pesoRecomendadoTexto }}</div>
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
    },
    pesoRecomendadoTexto() {
      const indice = this.form.niosh_indice_levantamiento
      const peso = this.form.peso_manipulado_kg
      if (!indice || indice <= 0 || !peso) return '—'
      return Math.max(5, 25 / indice).toFixed(1)
    }
  }
}
</script>