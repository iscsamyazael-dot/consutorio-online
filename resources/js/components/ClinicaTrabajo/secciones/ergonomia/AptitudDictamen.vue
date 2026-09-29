<template>
  <div class="seccion-aptitud-dictamen-ergonomia">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-check text-indigo-600"></i>
        Aptitud y Dictamen Ergonómico
      </h3>

      <!-- Aptitud (REQUERIDO) -->
      <div class="mb-4">
        <label class="block text-sm font-semibold text-gray-700 mb-2">Aptitud * <span class="text-red-500">(Requerida, sin default)</span></label>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <label v-for="opcion in aptitudOpciones" :key="opcion.value" class="relative cursor-pointer">
            <input type="radio" :value="opcion.value" v-model="form.aptitud" @change="updateField('aptitud', opcion.value)" class="sr-only peer" required />
            <div :class="['p-4 border-2 rounded-lg text-center transition-all', 'peer-checked:border-indigo-500 peer-checked:bg-indigo-50', 'peer-focus:ring-2 peer-focus:ring-indigo-500', 'hover:border-gray-400', opcion.color]">
              <div class="text-2xl mb-1">{{ opcion.icon }}</div>
              <div class="font-semibold text-gray-800">{{ opcion.label }}</div>
              <div class="text-xs text-gray-500 mt-1">{{ opcion.descripcion }}</div>
            </div>
          </label>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Restricciones -->
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Restricciones / Limitaciones Ergonómicas</label>
          <textarea v-model="form.restricciones" rows="3" class="input-field w-full" placeholder="Restricciones de postura, fuerza, ..."></textarea>
        </div>

        <!-- Recomendaciones -->
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Recomendaciones</label>
          <textarea v-model="form.recomendaciones" rows="4" class="input-field w-full" placeholder="Recomendaciones de ergonomía, cambios de puesto, etc."></textarea>
        </div>
      </div>

      <!-- Plan de Seguimiento -->
      <div class="mt-4 pt-2 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
          <i class="icon icon-calendar text-indigo-600"></i>
          Plan de Seguimiento Ergonómico
        </h4>
        <div class="grid grid-cols-2 gap-4">
          <label class="flex items-center gap-2">
            <input v-model="form.seguimiento_requerido" type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" />
            <span class="font-semibold text-gray-700">Requiere seguimiento</span>
          </label>
          <div v-if="form.seguimiento_requerido">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Próximo Control *</label>
            <input v-model="form.plazo_proximo_seguimiento" type="date" class="input-field w-full" :min="fechaHoy" required />
          </div>
        </div>
      </div>

      <!-- Resumen del dictamen -->
      <div v-if="form.aptitud" class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded">
        <h4 class="font-semibold text-gray-700 mb-2">Resumen del Dictamen Ergonómico</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-sm">
          <div>
            <span class="font-semibold text-gray-600">Aptitud: </span>
            <span :class="aptitudBadgeClass">{{ aptitudLabel }}</span>
          </div>
          <div>
            <span class="font-semibold text-gray-600">Seguimiento requerido: </span>
            {{ form.seguimiento_requerido ? 'Sí' : 'No' }}
          </div>
        </div>
      </div>

      <!-- Advertencia si no hay aptitud -->
      <div v-if="!form.aptitud" class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
        <div class="flex items-center gap-2 text-red-700"><i class="icon icon-alert text-lg"></i><span class="font-semibold">La APTITUD es obligatoria para poder guardar la valoración.</span></div>
        <p class="text-sm text-red-600 mt-1">Seleccione una opción arriba antes de continuar.</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AptitudDictamenErgonomia',
  props: {
    modelValue: { type: Object, required: true }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      fechaHoy: new Date().toISOString().split('T')[0],
      aptitudOpciones: [
        { value: 'apto', label: 'APTO', icon: '✅', descripcion: 'Sin limitaciones ergonomicas', color: 'bg-green-50 border-green-200' },
        { value: 'apto_con_restricciones', label: 'APTO CON RESTRICCIONES', icon: '⚠️', descripcion: 'Apto con adaptaciones', color: 'bg-yellow-50 border-yellow-200' },
        { value: 'no_apto', label: 'NO APTO', icon: '❌', descripcion: 'No apto por ergonomía severa', color: 'bg-red-50 border-red-200' }
      ]
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },
    aptitudLabel() {
      const opcion = this.aptitudOpciones.find(o => o.value === this.form.aptitud)
      return opcion ? opcion.label : '—'
    },
    aptitudBadgeClass() {
      const clases = {
        apto: 'badge badge-green',
        apto_con_restricciones: 'badge badge-yellow',
        no_apto: 'badge badge-red'
      }
      return clases[this.form.aptitud] || 'badge badge-gray'
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
.badge {display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600;}
.badge-green {background: #dcfce7; color: #166534;}
.badge-yellow {background: #fef3c7; color: #92400e;}
.badge-red {background: #fee2e2; color: #991b1b;}
.badge-gray {background: #f3f4f6; color: #374151;}
</style>