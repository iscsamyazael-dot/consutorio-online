<template>
  <div class="seccion-otoscopia-audiologia">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-eye text-indigo-600"></i>
        Otoscopía y Hallazgos Físicos
      </h3>

      <p class="text-sm text-gray-600 mb-4">
        Examen visual del conducto auditivo externo y tímpano.
      </p>

      <!-- Hallazgos otoscopia derecho -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Otoscopia Derecho *</label>
          <select v-model="form.otoscopia_der" class="input-field w-full">
            <option value="">Seleccionar...</option>
            <option value="normal">Normal</option>
            <option value="patologica">Patológica</option>
          </select>
          <div v-if="form.otoscopia_der !== 'normal' && form.otoscopia_der" class="mt-2">
            <p class="text-xs text-gray-500">Hallazgos:</p>
            <textarea
              v-model="form.otorrea_detalle"
              rows="2"
              class="input-field w-full"
              placeholder="Perforación, cérumen impactado, ectasia, etc."
            ></textarea>
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Otoscopia Izquierdo *</label>
          <select v-model="form.otoscopia_izq" class="input-field w-full">
            <option value="">Seleccionar...</option>
            <option value="normal">Normal</option>
            <option value="patologica">Patológica</option>
          </select>
          <div v-if="form.otoscopia_izq !== 'normal' && form.otoscopia_izq" class="mt-2">
            <p class="text-xs text-gray-500">Hallazgos:</p>
            <textarea
              v-model="form.otorrea_detalle"
              rows="2"
              class="input-field w-full"
              placeholder="Perforación, cérumen impactado, ectasia, etc."
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Hallazgos adicionales -->
      <div class="mt-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-medkit text-green-600"></i>
          Hallazgos Adicionales
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.canal_uditario_obstruido"
              class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
              @change="updateField('canal_uditario_obstruido', $event.target.checked)"
            />
            <span class="text-sm text-gray-700">Conducto obstruido (cérumen)</span>
          </label>

          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.exuda_presente"
              class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
              @change="updateField('exuda_presente', $event.target.checked)"
            />
            <span class="text-sm text-gray-700">Exudado presente</span>
          </label>

          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.perforacion_timpano"
              class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
              @change="updateField('perforacion_timpano', $event.target.checked)"
            />
            <span class="text-sm text-gray-700">Perforación timpánico</span>
          </label>

          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.ceratocoele"
              class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
              @change="updateField('ceratocoele', $event.target.checked)"
            />
            <span class="text-sm text-gray-700">Cératocoèle</span>
          </label>
        </div>

        <div class="mt-3">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Hallazgos Textuales</label>
          <textarea
            v-model="form.hallazgos_texto"
            rows="3"
            class="input-field w-full"
            placeholder="Describa hallazgos detallados: color timpáno, movilidad, etc."
          ></textarea>
        </div>
      </div>

      <!-- Nivel de riesgo -->
      <div v-if="form.otoscopia_der === 'patologica' || form.otoscopia_izq === 'patologica'" class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
        <h4 class="font-semibold text-red-700 mb-2 flex items-center gap-2">
          <i class="icon icon-alert text-red-600"></i>
          Hallazgos Patológicos
        </h4>
        <p class="text-sm text-red-700">
          Paciente con hallazgos otoscópicos patológicos. Se recomienda derivación a otorrinolaringología.
        </p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OtoscopíaAudiologia',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return { /* sin datos extra */ }
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
    }
  }
}
</script>

<style scoped>
/* Estilos heredados */
</style>