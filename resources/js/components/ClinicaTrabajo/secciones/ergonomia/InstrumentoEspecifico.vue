<template>
  <div class="instrumento-especifico-container">
    <template v-if="instrumento === 'RULA'">
      <RULAForm :modelValue="form" @update:modelValue="updateForm" />
    </template>
    <template v-if="instrumento === 'REBA'">
      <REBAForm :modelValue="form" @update:modelValue="updateForm" />
    </template>
    <template v-if="instrumento === 'NIOSH'">
      <NIOSHForm :modelValue="form" @update:modelValue="updateForm" />
    </template>
    <template v-if="instrumento === 'OTRO'">
      <OtroForm :modelValue="form" @update:modelValue="updateForm" />
    </template>
    <p class="text-center text-gray-500 py-8" v-if="!instrumento">Seleccione un instrumento arriba</p>
  </div>
</template>

<script>
import RULAForm from './InstrumentoEspecifico/RULA.vue'
import REBAForm from './InstrumentoEspecifico/REBA.vue'
import NIOSHForm from './InstrumentoEspecifico/NIOSH.vue'
import OtroForm from './InstrumentoEspecifico/OTRO.vue'

export default {
  name: 'InstrumentoEspecíficoErgonomia',
  components: {
    RULAForm,
    REBAForm,
    NIOSHForm,
    OtroForm
  },
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
    instrumento() {
      return this.form.instrumento_utilizado || ''
    }
  },
  methods: {
    /**
     * Emite la actualización completa del form
     * @param {Object} nuevoForm - El nuevo estado del formulario
     */
    updateForm(nuevoForm) {
      this.$emit('update:modelValue', nuevoForm)
    },

    /**
     * Actualiza un campo específico del form
     * @param {String} key - La clave del campo a actualizar
     * @param {*} value - El nuevo valor
     */
    updateField(key, value) {
      this.$emit('update:modelValue', { ...this.form, [key]: value })
    }
  }
}
</script>

<style scoped>
/* Estilos heredados */
</style>