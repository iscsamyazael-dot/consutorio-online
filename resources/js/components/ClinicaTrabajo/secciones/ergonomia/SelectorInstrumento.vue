<template>
  <div class="seccion-selector-instrumento-ergonomia">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-tool text-orange-600"></i>
        Seleccionar Instrumento de Evaluación Ergonómica
      </h3>

      <p class="text-sm text-gray-600 mb-4">
        Seleccione el instrumento de evaluación ergonómica que será utilizado. Cada instrumento tiene campos específicos.
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <label
          v-for="instrumento in instrumentosDisponibles"
          :key="instrumento.key"
          class="flex flex-col items-start border rounded-lg p-4 hover:bg-gray-50 transition cursor-pointer"
          :class="{
            'border-indigo-500 bg-indigo-50 text-indigo-700': instrumento.seleccionado,
            'border-orange-500 bg-orange-50 text-orange-700': !instrumento.seleccionado && form.instrumento_utilizado !== instrumento.key,
            'border-gray-300 bg-white text-gray-700': !instrumento.seleccionado && !form.instrumento_utilizado
          }"
        >
          <div class="text-2xl mb-2">{{ instrumento.icono }}</div>
          <div class="font-medium text-gray-800 line-clamp-1">{{ instrumento.nombre }}</div>
          <div class="text-xs text-gray-500 line-clamp-1">{{ instrumento.descripcion }}</div>
          <input
            type="radio"
            :name="grupoInstrumento"
            :value="instrumento.key"
            v-model="form.instrumento_utilizado"
            @change="onInstrumentoSeleccionado(instrumento.key)"
            class="sr-only peer"
            :checked="instrumento.seleccionado"
          />
          <div class="mt-2 pt-2 border-t border-gray-200">
            <small class="text-indigo-600">{{ instrumento.puntuacionMaxima }} pts max</small>
          </div>
        </label>
      </div>

      <!-- Mostrar formulario específico según instrumento -->
      <div v-if="form.instrumento_utilizado" class="mt-6 p-4 border-t border-indigo-200">
        <h4 class="font-semibold text-indigo-800 mb-3 flex items-center gap-2">
          <i class="icon icon-info"></i>
          Formulario Específico: {{ instrumentoSeleccionado?.nombre }}
        </h4>

        <p class="text-xs text-indigo-600 mb-3">
          Los siguientes campos se adaptarán automáticamente según el instrumento seleccionado.
        </p>

        <component
          :is="`InstrumentoEspecifico${instrumentoSeleccionado?.nombre.replace(/\s/g, '')}`"
          :modelValue="form"
          @update:modelValue="updateForm"
        />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SelectorInstrumentoErgonomia',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      instrumentosDisponibles: [
        {
          key: 'RULA',
          nombre: 'RULA',
          icono: '📊',
          descripcion: 'Rapid Upper Limb Assessment - Evaluación de extremidades superiores',
          puntuacionMaxima: 7,
          seleccionado: false
        },
        {
          key: 'REBA',
          nombre: 'REBA',
          icono: '📈',
          descripcion: 'Rapid Entire Body Assessment - Evaluación corporal completa',
          puntuacionMaxima: 15,
          seleccionado: false
        },
        {
          key: 'NIOSH',
          nombre: 'NIOSH',
          icono: '📋',
          descripcion: 'National Institute for Occupational Safety and Health - Evaluación de levantamiento',
          puntuacionMaxima: 10,
          seleccionado: false
        },
        {
          key: 'OTRO',
          nombre: 'OTRO',
          icono: '🔧',
          descripcion: 'Otro instrumento personalizado',
          puntuacionMaxima: 0,
          seleccionado: false
        }
      ]
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },
    instrumentoSeleccionado() {
      return this.instrumentosDisponibles.find(i => i.key === this.form.instrumento_utilizado)
    }
  },
  methods: {
    updateField(key, value) {
      this.$emit('update:modelValue', { ...this.form, [key]: value })
    },
    onInstrumentoSeleccionado(key) {
      // Emitir el cambio completo del formulario
      this.$emit('update:modelValue', { ...this.form, [key]: key })
    }
  }
}
</script>

<style scoped>
/* Estilos heredados */
</style>