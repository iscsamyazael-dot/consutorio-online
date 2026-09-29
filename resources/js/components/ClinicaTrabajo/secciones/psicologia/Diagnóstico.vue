<template>
  <div class="seccion-diagnostico-psicologia">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-stethoscope text-green-600"></i>
        Diagnóstico Clínico
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Diagnóstico clínico -->
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Diagnóstico Clínico *</label>
          <textarea
            v-model="form.diagnostico_clinico"
            rows="3"
            class="input-field w-full"
            placeholder="Describa el diagnóstico clínico completo..."
            required
          ></textarea>
        </div>

        <!-- Código CIE-11 -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Código CIE-11</label>
          <input
            type="text"
            v-model="form.codigo_cie11"
            class="input-field w-full"
            placeholder="Ej: 6B40, 6B41, etc."
            maxlength="20"
          />
          <p class="text-xs text-gray-500 mt-1">Clasificación Internacional de Enfermedades 11ª Revisión</p>
        </div>

        <!-- Relacionado con el trabajo -->
        <div>
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.relacionado_con_trabajo"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              @change="updateField('relacionado_con_trabajo', $event.target.checked)"
            />
            <span class="font-semibold text-gray-700">Relacionado con el trabajo</span>
          </label>
          <p class="text-sm text-gray-500 mt-1 ml-6">Según criterios de enfermedad profesional</p>
        </div>
      </div>

      <!-- Búsqueda CIE-11 rápida -->
      <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
        <h4 class="font-semibold text-blue-800 mb-2 flex items-center gap-2">
          <i class="icon icon-search"></i>
          Códigos CIE-11 Comunes (Psicología)
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 text-sm">
          <button
            type="button"
            v-for="codigo in cie11Comunes"
            :key="codigo.codigo"
            @click="usarCie11(codigo)"
            class="text-left p-2 bg-white border border-blue-200 rounded hover:border-blue-400 hover:bg-blue-50 transition text-left"
          >
            <div class="font-mono font-semibold text-blue-700">{{ codigo.codigo }}</div>
            <div class="text-gray-600">{{ codigo.descripcion }}</div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DiagnósticoPsicologia',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      cie11Comunes: [
        { codigo: '6B40', descripcion: 'Trastorno de estrés postraumático' },
        { codigo: '6B41', descripcion: 'Trastorno de estrés agudo' },
        { codigo: '6B42', descripcion: 'Trastorno de adaptación' },
        { codigo: '6B43', descripcion: 'Trastorno de duelo prolongado' },
        { codigo: '6A70', descripcion: 'Episodio depresivo leve' },
        { codigo: '6A71', descripcion: 'Episodio depresivo moderado' },
        { codigo: '6A72', descripcion: 'Episodio depresivo grave' },
        { codigo: '6A60', descripcion: 'Trastorno de ansiedad generalizada' },
        { codigo: '6A61', descripcion: 'Trastorno de pánico' },
        { codigo: '6A62', descripcion: 'Trastorno de ansiedad social' },
        { codigo: '6A20', descripcion: 'Trastorno de estrés laboral (burnout)' },
        { codigo: 'QE84', descripcion: 'Problemas relacionados con el empleo' }
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
    updateField(key, value) {
      this.$emit('update:modelValue', { ...this.form, [key]: value })
    },
    usarCie11(codigo) {
      this.updateField('codigo_cie11', codigo.codigo)
      if (!this.form.diagnostico_clinico) {
        this.updateField('diagnostico_clinico', codigo.descripcion)
      }
    }
  }
}
</script>

<style scoped>
/* Estilos heredados */
</style>