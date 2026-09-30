<template>
  <div class="seccion-guia-iii-factores">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
          <i class="icon icon-chart text-purple-600"></i>
          Guía III: Factores Psicosociales (NOM-035)
        </h3>
        <span class="badge badge-purple text-xs">Múltiple selección</span>
      </div>

      <!-- Guía III aplicada -->
      <div class="mb-4">
        <label class="flex items-center gap-2 cursor-pointer">
          <input
            type="checkbox"
            v-model="form.guia_ref_iii_aplicada"
            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            @change="updateField('guia_ref_iii_aplicada', $event.target.checked)"
          />
          <span class="font-semibold text-gray-700">Aplicar Guía de Referencia III (Identificación de Factores Psicosociales)</span>
        </label>
      </div>

      <div v-if="form.guia_ref_iii_aplicada">
        <!-- Ambiente laboral -->
        <div class="mb-4">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Descripción del Ambiente Laboral</label>
          <textarea
            v-model="form.ambiente_laboral_descripcion"
            rows="2"
            class="input-field w-full"
            placeholder="Describa las condiciones generales del ambiente de trabajo..."
          ></textarea>
        </div>

        <!-- Puntuación riesgo texto -->
        <div class="mb-4">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Puntuación de Riesgo (Texto)</label>
          <input
            type="text"
            v-model="form.puntuacion_riesgo_texto"
            class="input-field w-full"
            placeholder="Ej: Alto, Medio, Bajo, o puntuación numérica"
          />
        </div>

        <!-- Factores psicosociales del catálogo -->
        <div class="mb-4">
          <label class="block text-sm font-semibold text-gray-700 mb-2">Factores Psicosociales Identificados</label>

          <div v-if="factoresDisponibles.length === 0" class="text-center py-4 text-gray-500">
            <p>Cargando catálogo de factores...</p>
          </div>

          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <div
              v-for="factor in factoresDisponibles"
              :key="factor.id"
              class="border rounded-lg p-3 hover:bg-gray-50 transition"
              :class="{ 'ring-2 ring-purple-500 bg-purple-50': isFactorSeleccionado(factor.id) }"
            >
              <label class="flex items-start gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  :checked="isFactorSeleccionado(factor.id)"
                  @change="toggleFactor(factor.id, $event.target.checked)"
                  class="w-4 h-4 mt-0.5 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                />
                <div class="flex-1 min-w-0">
                  <div class="font-medium text-gray-800">{{ factor.nombre }}</div>
                  <div class="text-xs text-gray-500 truncate">{{ factor.descripcion }}</div>
                  <div class="flex items-center gap-2 mt-2" v-if="isFactorSeleccionado(factor.id)">
                    <span class="text-xs font-semibold text-gray-600">Severidad:</span>
                    <select
                      :value="getSeveridad(factor.id)"
                      @change="updateSeveridad(factor.id, $event.target.value)"
                      class="input-field text-xs py-1 px-2 w-auto"
                    >
                      <option value="leve">Leve</option>
                      <option value="moderado">Moderado</option>
                      <option value="severo">Severo</option>
                    </select>
                  </div>
                </div>
              </label>
            </div>
          </div>

          <div v-if="factoresSeleccionados.length > 0" class="mt-4 p-3 bg-purple-50 border border-purple-200 rounded">
            <h4 class="font-semibold text-purple-800 mb-2">Factores Seleccionados ({{ factoresSeleccionados.length }})</h4>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="f in factoresSeleccionados"
                :key="f.id"
                class="badge badge-purple text-xs"
              >
                {{ f.nombre }} ({{ getSeveridad(f.id) }})
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Referencia NOM-035 -->
      <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded">
        <h4 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
          <i class="icon icon-book"></i>
          Categorías NOM-035 (Guía III)
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-600">
          <div>
            <span class="font-semibold">Condiciones del ambiente de trabajo:</span> iluminación, ruido, temperatura, ventilación
          </div>
          <div>
            <span class="font-semibold">Factores de la tarea:</span> carga mental, control, variedad, significado
          </div>
          <div>
            <span class="font-semibold">Organización del tiempo de trabajo:</span> jornada, turnos, descansos, horas extra
          </div>
          <div>
            <span class="font-semibold">Liderazgo y relaciones:</span> supervisión, apoyo social, violencia, acoso
          </div>
          <div>
            <span class="font-semibold">Ambiente organizacional:</span> comunicación, participación, justicia, recompensa
          </div>
          <div>
            <span class="font-semibold">Vida trabajo - vida personal:</span> interferencia, flexibilidad, conciliación
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'GuíaIIIFactores',
  props: {
    modelValue: {
      type: Object,
      required: true
    },
    factores: {
      type: Array,
      default: () => []
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      factoresDisponibles: [],
      factoresIds: [], // IDs seleccionados
      factoresSeveridad: {} // factor_id -> severidad
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },
    factoresSeleccionados() {
      return this.factoresDisponibles.filter(f => this.factoresIds.includes(f.id))
    }
  },
  methods: {
    updateField(key, value) {
      this.$emit('update:modelValue', { ...this.form, [key]: value })
    },
    isFactorSeleccionado(factorId) {
      return this.factoresIds.includes(factorId)
    },
    toggleFactor(factorId, checked) {
      if (checked) {
        this.factoresIds = [...this.factoresIds, factorId]
        this.factoresSeveridad = { ...this.factoresSeveridad, [factorId]: 'moderado' }
      } else {
        this.factoresIds = this.factoresIds.filter(id => id !== factorId)
        const { [factorId]: removed, ...rest } = this.factoresSeveridad
        this.factoresSeveridad = rest
      }
      this.syncFactoresConForm()
    },
    getSeveridad(factorId) {
      return this.factoresSeveridad[factorId] || 'moderado'
    },
    updateSeveridad(factorId, value) {
      this.factoresSeveridad = { ...this.factoresSeveridad, [factorId]: value }
      this.syncFactoresConForm()
    },
    syncFactoresConForm() {
      // Actualizar form.factores_ids y form.factores_severidad para el backend
      this.updateField('factores_ids', [...this.factoresIds])
      this.updateField('factores_severidad', this.factoresIds.map(id => this.factoresSeveridad[id]))
    },
    cargarFactores() {
      this.factoresDisponibles = this.factores
      // Restaurar selección previa del form
      if (this.form.factores_ids && Array.isArray(this.form.factores_ids)) {
        this.factoresIds = [...this.form.factores_ids]
        this.form.factores_severidad?.forEach((sev, index) => {
          if (this.form.factores_ids[index]) {
            this.factoresSeveridad[this.form.factores_ids[index]] = sev
          }
        })
      }
    }
  },
  watch: {
    factores: {
      handler(newVal) {
        this.factoresDisponibles = newVal
        this.cargarFactores()
      },
      immediate: true
    }
  },
  mounted() {
    this.cargarFactores()
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
.badge-purple {
  background: #f3e8ff;
  color: #7e22ce;
}
</style>