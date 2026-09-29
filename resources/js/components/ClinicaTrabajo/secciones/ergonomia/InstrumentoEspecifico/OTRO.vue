<template>
  <div class="otro-form border rounded-lg p-4">
    <h4 class="font-semibold mb-2">Otro Instrumento</h4>
    <div class="grid grid-cols-2 gap-4">
      <label class="block text-sm">Descripción del instrumento</label>
      <input v-model="form.otro_descripcion" class="input-field w-full" />
    </div>
    <div class="grid grid-cols-2 gap-4 mt-4">
      <label class="block text-sm">Puntuación (num)</label>
      <input v-model.number="form.otro_puntuacion" type="number" class="input-field w-full" />
    </div>
  </div>
</template>

<script>
export default {
  name: 'OTROForm',
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