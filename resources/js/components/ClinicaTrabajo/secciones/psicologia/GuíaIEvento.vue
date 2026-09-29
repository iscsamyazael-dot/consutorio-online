<template>
  <div class="seccion-guia-i-evento">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
          <i class="icon icon-alert text-orange-600"></i>
          Guía I: Evento Traumático (NOM-035)
        </h3>
        <span class="badge badge-info text-xs">Opcional - Solo si tipo = seguimiento_trauma</span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Guía I aplicada -->
        <div class="md:col-span-2">
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.guia_ref_i_aplicada"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              @change="updateField('guia_ref_i_aplicada', $event.target.checked)"
            />
            <span class="font-semibold text-gray-700">Aplicar Guía de Referencia I (Eventos Traumáticos)</span>
          </label>
          <p class="text-sm text-gray-500 mt-1 ml-6">Marcar para habilitar campos de evento traumático</p>
        </div>

        <!-- Ha presenciado evento traumático -->
        <div v-if="form.guia_ref_i_aplicada" class="md:col-span-2">
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.ha_presenciado_evento_traumatico"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              @change="updateField('ha_presenciado_evento_traumatico', $event.target.checked)"
            />
            <span class="font-semibold text-gray-700">Ha presenciado o experimentado evento traumático</span>
          </label>
        </div>

        <!-- Descripción del evento -->
        <div v-if="form.guia_ref_i_aplicada && form.ha_presenciado_evento_traumatico" class="md:col-span-2">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Descripción del Evento Traumático *</label>
          <textarea
            v-model="form.descripcion_evento_traumatico"
            rows="3"
            class="input-field w-full"
            placeholder="Describa brevemente el evento traumático presenciado o experimentado..."
            required
          ></textarea>
        </div>

        <!-- Fecha del evento -->
        <div v-if="form.guia_ref_i_aplicada && form.ha_presenciado_evento_traumatico">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha del Evento *</label>
          <input
            type="date"
            v-model="form.fecha_evento_traumatico"
            class="input-field w-full"
            :max="fechaHoy"
            required
          />
        </div>

        <!-- Requiere canalización IMSS -->
        <div v-if="form.guia_ref_i_aplicada" class="md:col-span-2">
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.requiere_canalizacion_imss"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              @change="updateField('requiere_canalizacion_imss', $event.target.checked)"
            />
            <span class="font-semibold text-gray-700 text-red-600">Requiere canalización al IMSS</span>
          </label>
          <p class="text-sm text-gray-500 mt-1 ml-6">Según criterios NOM-035-STPS-2018</p>
        </div>
      </div>

      <!-- Información NOM-035 -->
      <div v-if="form.guia_ref_i_aplicada" class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
        <h4 class="font-semibold text-yellow-800 mb-2 flex items-center gap-2">
          <i class="icon icon-info"></i>
          Criterios NOM-035-STPS-2018
        </h4>
        <ul class="text-sm text-yellow-700 space-y-1 ml-4 list-disc">
          <li>Evento que pone en riesgo la vida o integridad física</li>
          <li>Testigo de muerte, lesión grave o violencia</li>
          <li>Síntomas de estrés postraumático (revive, evita, hiperactivación)</li>
          <li>Duración > 1 mes y deterioro funcional significativo</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'GuíaIEvento',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      fechaHoy: new Date().toISOString().split('T')[0]
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
    }
  }
}
</script>

<style scoped>
.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.7rem;
  font-weight: 600;
}
.badge-info {
  background: #dbeafe;
  color: #1e40af;
}
</style>