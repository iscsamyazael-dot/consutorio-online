<template>
  <div class="seccion-aptitud-dictamen-ergonomia">
    <div class="card mb-4">
      <div class="card-header" style="background: linear-gradient(135deg, #2E8B57 0%, #228B22 100%); color: white;">
        <h3 class="mb-0">
          <i class="fas fa-check-circle mr-2"></i>
          Aptitud y Dictamen Ergonómico
        </h3>
      </div>
      <div class="card-body">
        <div v-if="sugerencias.aptitud" class="alert alert-info">
          Sugerencia de la IA: <strong>{{ sugerencias.aptitud }}</strong>. Selecciónala solo si estás de acuerdo.
        </div>
        <!-- Aptitud (REQUERIDO) -->
        <div class="mb-4">
          <label class="font-weight-bold d-block mb-2">
            Aptitud * <span class="text-danger">(Requerida, sin default)</span>
          </label>
          <div class="row">
            <div v-for="opcion in aptitudOpciones" :key="opcion.value" class="col-md-4 mb-3">
              <label class="aptitud-card d-block p-3 border rounded text-center"
                     :class="{
                       'aptitud-selected': form.aptitud === opcion.value,
                       [opcion.colorClass]: true
                     }"
                     style="cursor: pointer; transition: all 0.2s;">
                <input type="radio" :value="opcion.value" :checked="form.aptitud === opcion.value"
                  @change="updateField('aptitud', opcion.value)"
                  style="position: absolute; opacity: 0;" required />
                <div class="text-3xl mb-1" style="font-size: 2rem;">{{ opcion.icon }}</div>
                <div class="font-weight-bold">{{ opcion.label }}</div>
                <div class="text-muted small mt-1">{{ opcion.descripcion }}</div>
                <span v-if="opcion.value === sugerencias.aptitud" class="badge badge-info mt-2">Sugerido por IA</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Fila 1: Restricciones y Recomendaciones -->
        <div class="row mb-4">
          <div class="col-md-6 mb-3">
            <label class="font-weight-bold d-block mb-1">
              Restricciones / Limitaciones Ergonómicas
              <span v-if="sugerencias.restricciones && form.restricciones === sugerencias.restricciones" class="badge badge-info ml-2">Sugerido por IA</span>
            </label>
            <textarea v-model="form.restricciones" rows="3" class="form-control"
                      placeholder="Restricciones de postura, fuerza, ..."></textarea>
          </div>
          <div class="col-md-6 mb-3">
            <label class="font-weight-bold d-block mb-1">
              Recomendaciones
              <span v-if="sugerencias.recomendaciones && form.recomendaciones === sugerencias.recomendaciones" class="badge badge-info ml-2">Sugerido por IA</span>
            </label>
            <textarea v-model="form.recomendaciones" rows="3" class="form-control"
                      placeholder="Recomendaciones de ergonomía, cambios de puesto, etc."></textarea>
          </div>
        </div>

        <!-- Fila 2: Plan de Seguimiento -->
        <div class="mt-4 pt-3 border-top mb-4">
          <h4 class="font-weight-bold mb-2">Seguimiento</h4>
          <p class="mb-0">
            Requiere seguimiento: <strong>{{ form.examenMedico?.seguimiento_requerido ? 'Sí' : 'No' }}</strong>
            · Próximo control: <strong>{{ form.examenMedico?.plazo_proximo_seguimiento || '—' }}</strong>
            · Tipo: <strong>{{ form.examenMedico?.tipo_seguimiento || '—' }}</strong>
          </p>
          <small class="text-muted">Se define en la hoja 4, Examen Médico.</small>
        </div>

        <div v-if="hayPropuestaIA" class="mt-4 p-3 border rounded">
          <label class="d-flex align-items-center mb-0">
            <input type="checkbox" class="mr-2" :checked="confirmado"
                   @change="$emit('update:confirmado', $event.target.checked)" />
            <span class="font-weight-bold">Confirmo que revisé el dictamen sugerido por la IA.</span>
          </label>
        </div>

        <!-- Resumen del dictamen -->
        <div v-if="form.aptitud" class="mt-4 p-3" style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px;">
          <h4 class="font-weight-bold mb-2">Resumen del Dictamen Ergonómico</h4>
          <div class="row">
            <div class="col-md-3">
              <span class="font-weight-bold">Aptitud: </span>
              <span :class="aptitudBadgeClass">{{ aptitudLabel }}</span>
            </div>
            <div class="col-md-3">
              <span class="font-weight-bold">Seguimiento: </span>
              {{ form.examenMedico?.seguimiento_requerido ? 'Sí' : 'No' }}
            </div>
            <div class="col-md-3">
              <span class="font-weight-bold">Próximo control: </span>
              {{ form.examenMedico?.plazo_proximo_seguimiento || '—' }}
            </div>
            <div class="col-md-3">
              <span class="font-weight-bold">Tipo: </span>
              {{ form.examenMedico?.tipo_seguimiento || '—' }}
            </div>
          </div>
        </div>

        <!-- Advertencia si no hay aptitud -->
        <div v-if="!form.aptitud" class="mt-4 p-3" style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px;">
          <div class="d-flex align-items-center" style="color: #b91c1c;">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <span class="font-weight-bold">La APTITUD es obligatoria para poder guardar la valoración.</span>
          </div>
          <p class="text-danger small mt-1 mb-0">Seleccione una opción arriba antes de continuar.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AptitudDictamenErgonomia',
  props: {
    modelValue: { type: Object, required: true },
    sugerencias: { type: Object, default: () => ({}) },
    confirmado: { type: Boolean, default: false },
    cargandoIA: { type: Boolean, default: false }
  },
  emits: ['update:modelValue', 'update:confirmado'],
  data() {
    return {
      aptitudOpciones: [
        { value: 'Apto', label: 'APTO', icon: '✅', descripcion: 'Sin limitaciones ergonómicas', colorClass: 'bg-green-50' },
        { value: 'Apto con restricciones', label: 'APTO CON RESTRICCIONES', icon: '⚠️', descripcion: 'Apto con adaptaciones', colorClass: 'bg-yellow-50' },
        { value: 'No apto', label: 'NO APTO', icon: '❌', descripcion: 'No apto por ergonomía severa', colorClass: 'bg-red-50' }
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
        'Apto': 'badge badge-success',
        'Apto con restricciones': 'badge badge-warning',
        'No apto': 'badge badge-danger'
      }
      return clases[this.form.aptitud] || 'badge badge-secondary'
    },
    hayPropuestaIA() {
      return Object.keys(this.sugerencias || {}).length > 0
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
.seccion-aptitud-dictamen-ergonomia {
  background: #f8f9fa;
}

.card {
  border: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.aptitud-card {
  border: 2px solid #dee2e6;
  border-radius: 8px;
  transition: all 0.2s;
}

.aptitud-card:hover {
  border-color: #adb5bd;
  transform: translateY(-2px);
}

.aptitud-selected {
  border-color: #2E8B57 !important;
  background-color: #f0fdf4 !important;
}

.bg-green-50 { background-color: #f0fdf4; }
.bg-yellow-50 { background-color: #fefce8; }
.bg-red-50 { background-color: #fef2f2; }

.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-success { background: #dcfce7; color: #166534; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-danger { background: #fee2e2; color: #991b1b; }
.badge-secondary { background: #f3f4f6; color: #374151; }
.badge-info { background: #dbeafe; color: #1e40af; }

.form-control {
  border-radius: 4px;
  border: 1px solid #ced4da;
  font-size: 14px;
  padding: 10px 12px;
}

.font-weight-bold {
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  color: #2c3e50;
}

.text-muted {
  color: #6c757d !important;
}
</style>