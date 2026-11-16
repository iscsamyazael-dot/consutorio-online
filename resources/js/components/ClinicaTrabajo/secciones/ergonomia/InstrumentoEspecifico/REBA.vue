<template>
  <div class="reba-form border rounded-lg p-4">
    <h4 class="font-semibold mb-2">REBA – Rapid Entire Body Assessment</h4>
    <div class="grid grid-cols-2 gap-4">
      <label class="block text-sm">Puntuación (1-15)</label>
      <input v-model.number="form.reba_puntuacion" type="number" min="1" max="15" class="input-field w-full" />
    </div>
    <div class="grid grid-cols-2 gap-4 mt-4">
      <label class="block text-sm">Nivel de Acción</label>
      <select v-model="form.reba_nivel_accion" class="input-field w-full">
        <option value="">Seleccionar...</option>
        <option value="aceptable">Aceptable</option>
        <option value="investigar">Investigar</option>
        <option value="cambios_pronto">Cambios pronto</option>
        <option value="cambios_inmediatos">Cambios inmediatos</option>
      </select>
    </div>
  </div>
</template>

<script>
export default {
  name: 'REBAForm',
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