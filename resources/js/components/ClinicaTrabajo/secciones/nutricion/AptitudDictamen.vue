<template>
  <div class="seccion-aptitud-dictamen-nutricion">
    <!-- SECTION: APTITUD, DICTAMEN Y PLAN ALIMENTARIO -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-check-circle mr-2"></i> APTITUD, DICTAMEN Y PLAN ALIMENTARIO
        </h5>
      </div>
      <div class="card-body">
        <!-- APTITUD (REQUERIDO - sin default) -->
        <div class="mb-4">
          <label class="font-weight-bold">Aptitud * <span class="text-danger">(Requerida, sin default)</span></label>
          <div class="row">
            <div v-for="opcion in aptitudOpciones"
                 :key="opcion.value"
                 class="col-md-4 mb-3">
              <div class="border rounded p-3 text-center hover:bg-light transition-all cursor-pointer"
                   :class="[
                     'border-' + (form.aptitud === opcion.value ? 'success' : 'secondary'),
                     'bg-' + (form.aptitud === opcion.value ? 'success' : 'light'),
                     'text-' + (form.aptitud === opcion.value ? 'white' : 'dark')
                   ]">
                <input
                  type="radio"
                  name="aptitud"
                  :value="opcion.value"
                  :checked="form.aptitud === opcion.value"
                  @change="updateField('aptitud', opcion.value)"
                  class="position-static"
                  required
                />
                <div class="mt-2">
                  <div class="h4 mb-1">{{ opcion.icon }}</div>
                  <div class="font-weight-bold">{{ opcion.label }}</div>
                  <div class="small text-muted">{{ opcion.descripcion }}</div>
                </div>
              </div>
            </div>
          </div>
          <small class="text-danger d-block mt-2">
            Seleccione una opción antes de continuar.
          </small>
        </div>

        <!-- RESTRICTIONES -->
        <div class="mb-4">
          <label class="font-weight-bold">Restricciones / Limitaciones</label>
          <textarea
            class="form-control"
            rows="3"
            v-model="form.restricciones"
            @input="updateField('restricciones', $event.target.value)"
            placeholder="Restricciones dietéticas, limitaciones funcionales, adaptaciones en comedores, turnos, etc."
            style="height: 96px;" <!-- 32px * 3 -->
          ></textarea>
        </div>

        <!-- RECOMENDACIONES -->
        <div class="mb-4">
          <label class="font-weight-bold">Recomendaciones Nutricionales y Laborales</label>
          <textarea
            class="form-control"
            rows="4"
            v-model="form.recomendaciones"
            @input="updateField('recomendaciones', $event.target.value)"
            placeholder="Plan alimentario, suplementación, hidratación, horarios de comida, pausas activas, educación nutricional..."
            style="height: 128px;" <!-- 32px * 4 -->
          ></textarea>
        </div>

        <!-- SEGUIMIENTO -->
        <div class="row mb-4">
          <!-- SEGUIMIENTO REQUERIDO -->
          <div class="col-md-6 mb-3">
            <div class="form-check">
              <input
                class="form-check-input"
                type="checkbox"
                v-model="form.seguimiento_requerido"
                @change="updateField('seguimiento_requerido', $event.target.checked)"
              >
              <label class="form-check-label font-weight-bold">Requiere seguimiento nutricional</label>
            </div>
          </div>

          <!-- PRÓXIMO CONTROL -->
          <div v-if="form.seguimiento_requerido" class="col-md-6 mb-3">
            <label class="font-weight-bold">Próximo Control *</label>
            <input
              type="date"
              class="form-control"
              :value="form.plazo_proximo_seguimiento"
              @input="updateField('plazo_proximo_seguimiento', $event.target.value)"
              :min="form.fecha_valoracion || fechaHoy"
              style="height: 38px;"
            >
          </div>
        </div>

        <!-- ALIMENTOS FRECUENTES -->
        <div v-if="form.alimentos_ids && form.alimentos_ids.length > 0" class="mb-4">
          <div class="card-header pb-0 pt-0">
            <h5 class="mb-0">
              <i class="fas fa-apple-alt mr-2"></i> ALIMENTOS DE CONSUMO FRECUENTE ({{ form.alimentos_ids.length }})
            </h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm table-bordered">
                <thead class="bg-light">
                  <tr>
                    <th class="text-center">Alimento</th>
                    <th class="text-center">Frecuencia</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(alimentoId, index) in form.alimentos_ids" :key="index" class="border-top">
                    <td>{{ getAlimentoNombre(alimentoId) }}</td>
                    <td>{{ form.alimentos_frecuencias?.[index] || '—' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- RESUMEN DEL DICTAMEN -->
        <div v-if="form.aptitud" class="mt-4 p-3 bg-light border border-left border-success">
          <h5 class="font-weight-bold text-success mb-3 flex items-center gap-2">
            <i class="fas fa-file-alt mr-2"></i> RESUMEN DEL DICTAMEN NUTRICIONAL
          </h5>
          <div class="row">
            <div class="col-md-4">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Aptitud:</span>
                <span class="font-weight-bold" :class="getAptitudClass()">{{ aptitudLabel }}</span>
              </div>
            </div>
            <div class="col-md-4">
              <div class="d-flex justify-content-between">
                <span class="text-muted">IMC:</span>
                <span class="font-weight-bold">{{ imcCalculado || '—' }}</span> ({{ form.imc_clasificacion || '—' }})
              </div>
            </div>
            <div class="col-md-4">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Seguimiento:</span>
                <span class="font-weight-bold">
                  {{ form.seguimiento_requerido ? 'Sí - ' + formatearFecha(form.plazo_proximo_seguimiento) : 'No requerido' }}
                </span>
              </div>
            </div>
          </div>
        </div>
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
    getAptitudClass() {
      switch (this.form.aptitud) {
        case 'apto': return 'text-success';
        case 'apto_con_restricciones': return 'text-warning';
        case 'no_apto': return 'text-danger';
        default: return 'text-muted';
      }
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
.seccion-aptitud-dictamen-nutricion {
  background: #f8f9fa;
}

.card {
  border: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.form-control {
  border-radius: 4px;
  border: 1px solid #ced4da;
  font-size: 14px;
  padding: 10px 12px;
}

.form-control:focus {
  border-color: #5F6E7E;
  box-shadow: 0 0 0 0.2rem rgba(95, 110, 126, 0.25);
}

.form-check-input {
  width: 1.25rem;
  height: 1.25rem;
  margin-top: 0.2rem;
}

.label {
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  color: #2c3e50;
}

.text-muted {
  color: #6c757d !important;
}

/* Estilos para las opciones de aptitud */
.aptitud-option {
  border: 2px solid #ced4da;
  border-radius: 0.5rem;
  padding: 1rem;
  transition: all 0.2s;
  cursor: pointer;
}

.aptitud-option:hover {
  border-color: #adb5bd;
  background-color: #e9ecef;
}

.aptitud-option.selected {
  border-color: #28a745;
  background-color: #d4edda;
  color: #155724;
}

/* Estilo para tarjetas de resumen */
.card-header {
  background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%) !important;
  color: white !important;
}
</style>