<template>
  <div class="seccion-aptitud-dictamen-nutricion">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-check text-emerald-600"></i>
        Aptitud, Dictamen y Plan Alimentario
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
              name="aptitud"
              :value="opcion.value"
              :checked="form.aptitud === opcion.value"
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
            :value="form.restricciones"
            @input="updateField('restricciones', $event.target.value)"
            rows="3"
            class="input-field w-full"
            placeholder="Restricciones dietéticas, limitaciones funcionales, adaptaciones en comedores, turnos, etc."
          ></textarea>
        </div>

        <!-- Recomendaciones -->
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Recomendaciones Nutricionales y Laborales</label>
          <textarea
            :value="form.recomendaciones"
            @input="updateField('recomendaciones', $event.target.value)"
            rows="4"
            class="input-field w-full"
            placeholder="Plan alimentario, suplementación, hidratación, horarios de comida, pausas activas, educación nutricional..."
          ></textarea>
        </div>
      </div>

      <!-- Seguimiento -->
      <div class="mt-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-calendar text-blue-600"></i>
          Plan de Seguimiento Nutricional
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                :checked="!!form.seguimiento_requerido"
                @change="updateField('seguimiento_requerido', $event.target.checked)"
                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <span class="font-semibold text-gray-700">Requiere seguimiento nutricional</span>
            </label>
          </div>

          <div v-if="form.seguimiento_requerido">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Próximo Control *</label>
            <input
              type="date"
              :value="form.plazo_proximo_seguimiento"
              @input="updateField('plazo_proximo_seguimiento', $event.target.value)"
              class="input-field w-full"
              :min="form.fecha_valoracion || fechaHoy"
              required
            />
          </div>
        </div>
      </div>

      <!-- Alimentos frecuentes (desde catálogo) -->
      <div v-if="form.alimentos_ids && form.alimentos_ids.length > 0" class="mt-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-apple text-orange-600"></i>
          Alimentos de Consumo Frecuente ({{ form.alimentos_ids.length }})
        </h4>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-100">
                <th class="text-left p-2">Alimento</th>
                <th class="text-left p-2">Frecuencia</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(alimentoId, index) in form.alimentos_ids" :key="index" class="border-b border-gray-200">
                <td class="p-2">{{ getAlimentoNombre(alimentoId) }}</td>
                <td class="p-2">{{ form.alimentos_frecuencias?.[index] || '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Resumen del dictamen -->
      <div v-if="form.aptitud" class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded">
        <h4 class="font-semibold text-gray-700 mb-2">Resumen del Dictamen Nutricional</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-sm">
          <div>
            <span class="font-semibold text-gray-600">Aptitud: </span>
            <span :class="aptitudBadgeClass">{{ aptitudLabel }}</span>
          </div>
          <div>
            <span class="font-semibold text-gray-600">IMC: </span>
            {{ imcCalculado || '—' }} ({{ form.imc_clasificacion || '—' }})
          </div>
          <div>
            <span class="font-semibold text-gray-600">Seguimiento: </span>
            {{ form.seguimiento_requerido ? 'Sí - ' + formatearFecha(form.plazo_proximo_seguimiento) : 'No requerido' }}
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
import ApiService from '../../../../services/ApiService'

// AJUSTA AQUÍ la ruta según `php artisan route:list --path=alimentos`
const RUTAS = {
  catalogoAlimentos: '/catalogo/alimentos'
}

export default {
  name: 'AptitudDictamenNutricion',
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
          descripcion: 'Sin restricciones nutricionales para el puesto',
          color: 'bg-green-50 border-green-200'
        },
        {
          value: 'apto_con_restricciones',
          label: 'APTO CON RESTRICCIONES',
          icon: '⚠️',
          descripcion: 'Apto con adaptaciones dietéticas/horarios',
          color: 'bg-yellow-50 border-yellow-200'
        },
        {
          value: 'no_apto',
          label: 'NO APTO',
          icon: '❌',
          descripcion: 'No apto por condición nutricional severa',
          color: 'bg-red-50 border-red-200'
        }
      ],
      catalogoAlimentos: []
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
    imcCalculado() {
      if (this.form.peso_kg && this.form.estatura_m && this.form.estatura_m > 0) {
        return (this.form.peso_kg / (this.form.estatura_m ** 2)).toFixed(2)
      }
      return null
    }
  },
  methods: {
    updateField(key, value) {
      this.$emit('update:modelValue', { ...this.form, [key]: value })
    },
    formatearFecha(fecha) {
      if (!fecha) return 'sin fecha'
      // Agrega hora para evitar que la zona horaria corra la fecha un día
      return new Date(`${fecha}T00:00:00`).toLocaleDateString('es-MX')
    },
    getAlimentoNombre(id) {
      const alimento = this.catalogoAlimentos.find(a => String(a.id) === String(id))
      return alimento ? alimento.nombre : `ID: ${id}`
    },
    // Laravel puede devolver [], { data: [] }, { alimentos: [] } o paginado { data: { data: [] } }
    extraerLista(res) {
      const body = res?.data ?? res
      if (Array.isArray(body)) return body
      if (Array.isArray(body?.data)) return body.data
      if (Array.isArray(body?.alimentos)) return body.alimentos
      return []
    },
    async cargarCatalogoAlimentos() {
      try {
        this.catalogoAlimentos = this.extraerLista(await ApiService.get(RUTAS.catalogoAlimentos))
      } catch (error) {
        console.error('Error cargando catálogo alimentos:', error)
      }
    }
  },
  mounted() {
    this.cargarCatalogoAlimentos()
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