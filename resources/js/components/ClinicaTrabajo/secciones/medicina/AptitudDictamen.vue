<template>
  <div class="seccion-aptitud-dictamen-medicina">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-check text-emerald-600"></i>
        Dictamen de Aptitud Laboral
      </h3>

      <!-- Aptitud / Dictamen (REQUERIDO - sin default) -->
      <div class="mb-4">
        <label class="block text-sm font-semibold text-gray-700 mb-2">Dictamen * <span class="text-red-500">(Requerido, sin default)</span></label>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
          <label
            v-for="opcion in dictamenOpciones"
            :key="opcion.value"
            class="relative cursor-pointer"
          >
            <input
              type="radio"
              :value="opcion.value"
              v-model="form.dictamen"
              @change="updateField('dictamen', opcion.value); updateField('aptitud', opcion.aptitudEquivalente)"
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
          <label class="block text-sm font-semibold text-gray-700 mb-1">Restricciones / Limitaciones Laborales</label>
          <textarea
            v-model="form.restricciones"
            rows="3"
            class="input-field w-full"
            placeholder="Restricciones específicas: no trabajo en alturas, no exposición a ruido >85dB, no manipulación de cargas >10kg, adaptaciones de puesto..."
          ></textarea>
        </div>

        <!-- Justificación del dictamen -->
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Justificación del Dictamen</label>
          <textarea
            v-model="form.dictamen_justificacion"
            rows="3"
            class="input-field w-full"
            placeholder="Fundamento médico-legal del dictamen: relación riesgo-salud, normas aplicables, criterios clínicos..."
          ></textarea>
        </div>

        <!-- Plan de intervención -->
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Plan de Intervención / Recomendaciones</label>
          <textarea
            v-model="form.plan_intervencion"
            rows="4"
            class="input-field w-full"
            placeholder="Vigilancia de la salud, controles periódicos, derivaciones, adaptaciones ergonómicas, educación en salud, EPP..."
          ></textarea>
        </div>
      </div>

      <!-- Vigencia y Firmas -->
      <div class="mt-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-calendar text-blue-600"></i>
          Vigencia y Firmas
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Vigencia Hasta *</label>
            <input
              type="date"
              v-model="form.vigencia_hasta"
              class="input-field w-full"
              :min="form.fecha_valoracion || fechaHoy"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Firmado por Médico</label>
            <input
              type="datetime-local"
              v-model="form.firmado_medico_en"
              class="input-field w-full"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Firmado por Trabajador</label>
            <input
              type="datetime-local"
              v-model="form.firmado_trabajador_en"
              class="input-field w-full"
            />
          </div>
        </div>

        <div v-if="form.firmado_medico_en || form.firmado_trabajador_en" class="mt-3 p-3 bg-green-50 border border-green-200 rounded">
          <h5 class="font-semibold text-green-800 mb-1">Estado de Firmas</h5>
          <div class="grid grid-cols-2 gap-2 text-sm">
            <div>
              <span class="font-semibold text-gray-600">Médico: </span>
              <span :class="form.firmado_medico_en ? 'text-green-600' : 'text-gray-400'">
                {{ form.firmado_medico_en ? '✅ Firmado ' + formatearFechaHora(form.firmado_medico_en) : '⏳ Pendiente' }}
              </span>
            </div>
            <div>
              <span class="font-semibold text-gray-600">Trabajador: </span>
              <span :class="form.firmado_trabajador_en ? 'text-green-600' : 'text-gray-400'">
                {{ form.firmado_trabajador_en ? '✅ Firmado ' + formatearFechaHora(form.firmado_trabajador_en) : '⏳ Pendiente' }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Resumen del dictamen -->
      <div v-if="form.dictamen" class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded">
        <h4 class="font-semibold text-gray-700 mb-2">Resumen del Dictamen</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-sm">
          <div>
            <span class="font-semibold text-gray-600">Dictamen: </span>
            <span :class="dictamenBadgeClass">{{ dictamenLabel }}</span>
          </div>
          <div>
            <span class="font-semibold text-gray-600">Vigencia: </span>
            {{ form.vigencia_hasta ? formatearFecha(form.vigencia_hasta) : 'Sin definir' }}
          </div>
          <div>
            <span class="font-semibold text-gray-600">Firmas: </span>
            {{ (form.firmado_medico_en ? 'Médico ✓' : 'Médico ✗') }} / {{ (form.firmado_trabajador_en ? 'Trabajador ✓' : 'Trabajador ✗') }}
          </div>
        </div>
      </div>

      <!-- Advertencia si no hay dictamen -->
      <div v-if="!form.dictamen" class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
        <div class="flex items-center gap-2 text-red-700">
          <i class="icon icon-alert text-lg"></i>
          <span class="font-semibold">El DICTAMEN DE APTITUD es obligatorio para poder guardar la valoración.</span>
        </div>
        <p class="text-sm text-red-600 mt-1">Seleccione una opción arriba antes de continuar.</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AptitudDictamenMedicina',
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
      dictamenOpciones: [
        {
          value: 'APTO',
          label: 'APTO',
          icon: '✅',
          descripcion: 'Sin restricciones para el puesto evaluado',
          color: 'bg-green-50 border-green-200',
          aptitudEquivalente: 'apto'
        },
        {
          value: 'APTO_CON_RESTRICCIONES',
          label: 'APTO CON RESTRICCIONES',
          icon: '⚠️',
          descripcion: 'Apto con adaptaciones o limitaciones específicas',
          color: 'bg-yellow-50 border-yellow-200',
          aptitudEquivalente: 'apto_con_restricciones'
        },
        {
          value: 'NO_APTO',
          label: 'NO APTO',
          icon: '❌',
          descripcion: 'No apto para el puesto evaluado por riesgo inaceptable',
          color: 'bg-red-50 border-red-200',
          aptitudEquivalente: 'no_apto'
        },
        {
          value: 'SUSPENDIDO',
          label: 'SUSPENDIDO (Pendiente estudios)',
          icon: '⏸️',
          descripcion: 'Dictamen suspendido hasta completar estudios/valoraciones',
          color: 'bg-blue-50 border-blue-200',
          aptitudEquivalente: 'suspendido'
        }
      ]
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },
    dictamenLabel() {
      const opcion = this.dictamenOpciones.find(o => o.value === this.form.dictamen)
      return opcion ? opcion.label : '—'
    },
    dictamenBadgeClass() {
      const clases = {
        APTO: 'badge badge-green',
        APTO_CON_RESTRICCIONES: 'badge badge-yellow',
        NO_APTO: 'badge badge-red',
        SUSPENDIDO: 'badge badge-blue'
      }
      return clases[this.form.dictamen] || 'badge badge-gray'
    }
  },
  methods: {
    updateField(key, value) {
      this.$emit('update:modelValue', { ...this.form, [key]: value })
    },
    formatearFecha(fecha) {
      return new Date(fecha).toLocaleDateString('es-MX')
    },
    formatearFechaHora(fecha) {
      return new Date(fecha).toLocaleString('es-MX')
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
.badge-blue { background: #dbeafe; color: #1e40af; }
.badge-gray { background: #f3f4f6; color: #374151; }
</style>