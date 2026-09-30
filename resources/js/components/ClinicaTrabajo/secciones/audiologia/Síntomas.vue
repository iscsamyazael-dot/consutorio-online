<template>
  <div class="seccion-sintomas-audiologia">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-alert text-red-600"></i>
        Síntomas y Quejas Auditivas
      </h3>

      <p class="text-sm text-gray-600 mb-4">
        Identifique las molestias o alteraciones auditivas actuales o recientes del paciente.
      </p>

      <!-- Síntomas auditivos -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        <label
          v-for="sintoma in sintomasAuditivos"
          :key="sintoma.key"
          class="flex items-center gap-2 cursor-pointer"
        >
          <input
            type="checkbox"
            :checked="form[sintoma.key]"
            @change="updateField(sintoma.key, $event.target.checked)"
            class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500"
          />
          <span class="text-sm text-gray-700 flex-1">{{ sintoma.label }}</span>
        </label>
      </div>

      <!-- Sintomas de oído específico -->
      <div class="mt-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-eye text-indigo-600"></i>
          Sintomas por Oído
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Síntomas Oído Derecho *</label>
            <select v-model="form.otalgia_der" class="input-field w-full">
              <option value="">Seleccionar...</option>
              <option value="true">Presente</option>
              <option value="false">No presente</option>
            </select>
            <select v-model="form.otorrea_der" class="input-field w-full">
              <option value="">Seleccionar...</option>
              <option value="true">Presente</option>
              <option value="false">No presente</option>
            </select>
            <select v-model="form.prurito_der" class="input-field w-full">
              <option value="">Seleccionar...</option>
              <option value="true">Presente</option>
              <option value="false">No presente</option>
            </select>
            <select v-model="form.acufenos_der" class="input-field w-full">
              <option value="">Seleccionar...</option>
              <option value="true">Presente</option>
              <option value="false">No presente</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Síntomas Oído Izquierdo *</label>
            <select v-model="form.otalgia_izq" class="input-field w-full">
              <option value="">Seleccionar...</option>
              <option value="true">Presente</option>
              <option value="false">No presente</option>
            </select>
            <select v-model="form.otorrea_izq" class="input-field w-full">
              <option value="">Seleccionar...</option>
              <option value="true">Presente</option>
              <option value="false">No presente</option>
            </select>
            <select v-model="form.prurito_izq" class="input-field w-full">
              <option value="">Seleccionar...</option>
              <option value="true">Presente</option>
              <option value="false">No presente</option>
            </select>
            <select v-model="form.acufenos_izq" class="input-field w-full">
              <option value="">Seleccionar...</option>
              <option value="true">Presente</option>
              <option value="false">No presente</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Condiciones asociadas -->
      <div class="mt-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-medkit text-green-600"></i>
          Condiciones Asociadas
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          <label
            v-for="cond in condicionesAsociadas"
            :key="cond.key"
            class="flex items-center gap-2 cursor-pointer"
          >
            <input
              type="checkbox"
              :checked="form[cond.key]"
              @change="updateField(cond.key, $event.target.checked)"
              class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
            />
            <span class="text-sm text-gray-700">{{ cond.label }}</span>
          </label>
        </div>

        <div class="mt-3">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Infecciones / Patologías</label>
          <textarea
            v-model="form.otorrea_detalle"
            rows="2"
            class="input-field w-full"
            placeholder="Describa otitis, crónicas, quirúrgicas, etc."
          ></textarea>
        </div>
      </div>

      <!-- Advertencia -->
      <div v-if="sintomasCriticosActivos.length > 0" class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
        <h4 class="font-semibold text-red-700 mb-2 flex items-center gap-2">
          <i class="icon icon-alert text-red-600"></i>
          Síntomas Críticos Detectados
        </h4>
        <ul class="text-sm text-red-700 space-y-1">
          <li v-for="s in sintomasCriticosActivos" :key="s.key">
            <i class="icon icon-alert text-red-500 mr-1"></i> {{ s.label }}
          </li>
        </ul>
        <p class="text-xs text-red-600">
          <strong>Recomendación:</strong> Evaluación audiológica urgente y/o otorrinolaringología.
        </p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SíntomasAudiologia',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      sintomasAuditivos: [
        { key: 'niega_sintomatologia_auditiva', label: 'Niega sintomatología auditiva' },
        { key: 'otalgia_der', label: 'Otalgia (dolor oído der)' },
        { key: 'otalgia_izq', label: 'Otalgia (dolor oído izq)' },
        { key: 'otorrea_der', label: 'Otorrhea (descarga oído der)' },
        { key: 'otorrea_izq', label: 'Otorrhea (descarga oído izq)' },
        { key: 'prurito_der', label: 'Prurito (picazón oído der)' },
        { key: 'prurito_izq', label: 'Prurito (picazón oído izq)' },
        { key: 'acufenos_der', label: 'Acúfenos (tinnitus oído der)' },
        { key: 'acufenos_izq', label: 'Acúfenos (tinnitus oído izq)' },
        { key: 'vertigo_presente', label: 'Vértigo presente' },
        { key: 'otoscopia_normal', label: 'Otoscopia normal' },
        { key: 'otoscopia_patologica', label: 'Otoscopia patológica' }
      ],
      sintomasCriticos: ['otalgia_der', 'otalgia_izq', 'vertigo_presente']
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },
    sintomasCriticosActivos() {
      return this.sintomasCriticos.filter(key => this.form[key])
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