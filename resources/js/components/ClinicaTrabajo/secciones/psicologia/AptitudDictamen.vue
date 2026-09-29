<template>
  <div class="seccion-aptitud-dictamen-psicologia">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-check text-emerald-600"></i>
        Aptitud y Dictamen Final
      </h3>

      <!-- Aptitud (REQUERIDO - sin default) -->
      <div class="mb-4">
        <label class="block text-sm font-semibold text-gray-700 mb-2">Aptitud * <span class="text-red-500">(Requerida, sin default)</span></label>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <label
            v-for="opcion in aptitudOpciones"
            :key="opcion.value"
            class="relative cursor-pointer"
          >
            <input
              type="radio"
              :value="opcion.value"
              v-model="form.aptitud"
              @change="updateField('aptitud', opcion.value)"
              class="sr-only peer"
              required
            />
            <div
              class="p-4 border-2 rounded-lg text-center transition-all"
              :class="[
                'peer-checked:border-emerald-500 peer-checked:bg-emerald-50',
                'peer-focus:ring-2 peer-focus:ring-emerald-500',
                'hover:border-gray-400',
                opcion.color
              ]"
            >
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
          <label class="block text-sm font-semibold text-gray-700 mb-1">Restricciones / Limitaciones</label>
          <textarea
            v-model="form.restricciones"
            rows="3"
            class="input-field w-full"
            placeholder="Describa restricciones laborales, limitaciones funcionales, adaptaciones necesarias..."
          ></textarea>
        </div>

        <!-- Recomendaciones -->
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Recomendaciones</label>
          <textarea
            v-model="form.recomendaciones"
            rows="3"
            class="input-field w-full"
            placeholder="Recomendaciones para el trabajador, la empresa, seguimiento, tratamiento..."
          ></textarea>
        </div>
      </div>

      <!-- Seguimiento -->
      <div class="mt-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-calendar text-blue-600"></i>
          Plan de Seguimiento
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                v-model="form.requiere_seguimiento"
                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                @change="updateField('requiere_seguimiento', $event.target.checked)"
              />
              <span class="font-semibold text-gray-700">Requiere seguimiento</span>
            </label>
          </div>

          <div v-if="form.requiere_seguimiento">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Próximo Seguimiento *</label>
            <input
              type="date"
              v-model="form.plazo_proximo_seguimiento"
              class="input-field w-full"
              :min="form.fecha_valoracion || fechaHoy"
              required
            />
          </div>

          <div v-if="form.requiere_seguimiento" class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Canalizado a</label>
            <select v-model="form.canalizado_a" class="input-field w-full">
              <option value="">Seleccionar...</option>
              <option value="psicologia_externa">Psicología Externa</option>
              <option value="imss">IMSS</option>
              <option value="otro">Otro</option>
              <option value="ninguno">Ninguno</option>
            </select>
          </div>

          <div v-if="form.requiere_seguimiento && form.canalizado_a === 'otro'" class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Lugar de Canalización</label>
            <input
              type="text"
              v-model="form.lugar_canalizacion"
              class="input-field w-full"
              placeholder="Especifique institución o servicio"
            />
          </div>

          <div v-if="form.requiere_seguimiento" class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Notas de Seguimiento</label>
            <textarea
              v-model="form.notas_seguimiento"
              rows="2"
              class="input-field w-full"
              placeholder="Observaciones adicionales para el seguimiento..."
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Resumen del dictamen -->
      <div v-if="form.aptitud" class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded">
        <h4 class="font-semibold text-gray-700 mb-2">Resumen del Dictamen</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-sm">
          <div>
            <span class="font-semibold text-gray-600">Aptitud: </span>
            <span :class="aptitudBadgeClass">{{ aptitudLabel }}</span>
          </div>
          <div>
            <span class="font-semibold text-gray-600">Relacionado con trabajo: </span>
            {{ form.relacionado_con_trabajo ? 'Sí' : 'No' }}
          </div>
          <div>
            <span class="font-semibold text-gray-600">Requiere seguimiento: </span>
            {{ form.requiere_seguimiento ? 'Sí' : 'No' }}
          </div>
          <div class="md:col-span-3" v-if="form.requiere_seguimiento && form.plazo_proximo_seguimiento">
            <span class="font-semibold text-gray-600">Próxima cita: </span>
            {{ formatearFecha(form.plazo_proximo_seguimiento) }}
          </div>
          <div class="md:col-span-3" v-if="form.canalizado_a && form.canalizado_a !== 'ninguno'">
            <span class="font-semibold text-gray-600">Canalizado a: </span>
            {{ canalizadoLabel }}
            <span v-if="form.lugar_canalizacion"> - {{ form.lugar_canalizacion }}</span>
          </div>
        </div>
      </div>

      <!-- Advertencia si no hay aptitud -->
      <div v-if="!form.aptitud" class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
        <div class="flex items-center gap-2 text-red-700">
          <i class="icon icon-alert text-lg"></i>
          <span class="font-semibold">La APTITUD es obligatoria para poder guardar la valoración.</span>
        </div>
        <p class="text-sm text-red-600 mt-1">Seleccione una opción arriba antes de continuar.</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AptitudDictamenPsicologia',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      fechaHoy: new Date().toISOString().split('T')[0],
      aptitudOpciones: [
        {
          value: 'apto',
          label: 'APTO',
          icon: '✅',
          descripcion: 'Sin restricciones para el puesto',
          color: 'bg-green-50 border-green-200'
        },
        {
          value: 'apto_con_restricciones',
          label: 'APTO CON RESTRICCIONES',
          icon: '⚠️',
          descripcion: 'Apto con limitaciones o adaptaciones',
          color: 'bg-yellow-50 border-yellow-200'
        },
        {
          value: 'no_apto',
          label: 'NO APTO',
          icon: '❌',
          descripcion: 'No apto para el puesto evaluado',
          color: 'bg-red-50 border-red-200'
        }
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
    },
    canalizadoLabel() {
      const labels = {
        psicologia_externa: 'Psicología Externa',
        imss: 'IMSS',
        otro: 'Otro',
        ninguno: 'Ninguno'
      }
      return labels[this.form.canalizado_a] || this.form.canalizado_a
    }
  },
  methods: {
    updateField(key, value) {
      this.$emit('update:modelValue', { ...this.form, [key]: value })
    },
    formatearFecha(fecha) {
      return new Date(fecha).toLocaleDateString('es-MX')
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
.badge-green { background: #dcfce7; color: #166534; }
.badge-yellow { background: #fef3c7; color: #92400e; }
.badge-red { background: #fee2e2; color: #991b1b; }
.badge-gray { background: #f3f4f6; color: #374151; }
</style>