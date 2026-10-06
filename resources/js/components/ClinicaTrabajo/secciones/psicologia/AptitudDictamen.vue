<template>
  <div class="seccion-aptitud-dictamen-psicologia">
    <!-- SECTION: APTITUD Y DICTAMEN FINAL -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #20c997 0%, #1a9b7f 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-check-circle mr-2"></i> APTITUD Y DICTAMEN FINAL
        </h5>
      </div>
      <div class="card-body">
        <!-- APTITUD (REQUERIDO - sin default) -->
        <div class="mb-4">
          <label class="font-weight-bold">Aptitud * <span class="text-danger">(Requerida, sin default)</span></label>
          <div class="row">
            <div v-for="opcion in aptitudOpciones" :key="opcion.value" class="col-md-4 mb-3">
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
            placeholder="Describa restricciones laborales, limitaciones funcionales, adaptaciones necesarias, horarios flexibles, teletrabajo parcial, etc."
            style="height: 96px;"
          ></textarea>
        </div>

        <!-- RECOMENDACIONES -->
        <div class="mb-4">
          <label class="font-weight-bold">Recomendaciones</label>
          <textarea
            class="form-control"
            rows="4"
            v-model="form.recomendaciones"
            @input="updateField('recomendaciones', $event.target.value)"
            placeholder="Recomendaciones para el trabajador, la empresa, seguimiento, tratamiento, derivaciones, capacitación..."
            style="height: 128px;"
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
                v-model="form.requiere_seguimiento"
                @change="updateField('requiere_seguimiento', $event.target.checked)"
              >
              <label class="form-check-label font-weight-bold">Requiere seguimiento</label>
            </div>
          </div>

          <!-- PRÓXIMO SEGUIMIENTO -->
          <div v-if="form.requiere_seguimiento" class="col-md-6 mb-3">
            <label class="font-weight-bold">Próximo Seguimiento *</label>
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

        <!-- RESUMEN DEL DICTAMEN -->
        <div v-if="form.aptitud" class="mt-4 p-3 bg-light border border-left border-success">
          <h5 class="font-weight-bold text-success mb-3 flex items-center gap-2">
            <i class="fas fa-file-alt mr-2"></i> RESUMEN DEL DICTAMEN PSICOLÓGICO
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
                <span class="text-muted">Relacionado con trabajo:</span>
                <span class="font-weight-bold">{{ form.relacionado_con_trabajo ? 'Sí' : 'No' }}</span>
              </div>
            </div>
            <div class="col-md-4">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Seguimiento:</span>
                <span class="font-weight-bold">{{ form.requiere_seguimiento ? 'Sí - ' + formatearFecha(form.plazo_proximo_seguimiento) : 'No requerido' }}</span>
              </div>
            </div>
          </div>
        </div>
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
    getAptitudClass() {
      switch (this.form.aptitud) {
        case 'apto': return 'text-success';
        case 'apto_con_restricciones': return 'text-warning';
        case 'no_apto': return 'text-danger';
        default: return 'text-muted';
      }
    }
  },
  methods: {
    updateField(key, value) {
      this.$emit('update:modelValue', { ...this.form, [key]: value })
    },
    formatearFecha(fecha) {
      if (!fecha) return 'sin fecha'
      return new Date(`${fecha}T00:00:00`).toLocaleDateString('es-MX')
    }
  }
}
</script>

<style scoped>
.seccion-aptitud-dictamen-psicologia {
  background: #f8f9fa;
}

.card {
  border: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.card-header {
  background: linear-gradient(135deg, #20c997 0%, #1a9b7f 100%) !important;
  color: white !important;
}

.form-control {
  border-radius: 4px;
  border: 1px solid #ced4da;
  font-size: 14px;
  padding: 10px 12px;
}

.form-control:focus {
  border-color: #20c997;
  box-shadow: 0 0 0 0.2rem rgba(32, 201, 151, 0.25);
}

.form-check-input {
  width: 1.1rem;
  height: 1.1rem;
  margin-top: 0.15rem;
}

.form-check-label {
  font-size: 0.9rem;
  cursor: pointer;
}

.badge {
  font-size: 0.75rem;
  padding: 0.375rem 0.75rem;
  border-radius: 2rem;
}

.badge-success {
  background-color: #28a745;
  color: white;
}

.badge-warning {
  background-color: #ffc107;
  color: #212529;
}

.badge-danger {
  background-color: #dc3545;
  color: white;
}

.text-success { color: #28a745 !important; }
.text-warning { color: #ffc107 !important; }
.text-danger { color: #dc3545 !important; }
.text-muted { color: #6c757d !important; }
.font-weight-bold { font-weight: 600 !important; }
.bg-light { background-color: #f8f9fa !important; }
.bg-success { background-color: #28a745 !important; }
.bg-white { background-color: #fff !important; }

.border { border: 1px solid #dee2e6 !important; }
.border-top { border-top: 1px solid #dee2e6 !important; }
.border-left { border-left: 4px solid !important; }
.border-success { border-color: #28a745 !important; }

.rounded { border-radius: 0.25rem !important; }
.p-2 { padding: 0.5rem !important; }
.p-3 { padding: 1rem !important; }
.p-4 { padding: 1.5rem !important; }
.mt-1 { margin-top: 0.25rem !important; }
.mt-2 { margin-top: 0.5rem !important; }
.mt-3 { margin-top: 1rem !important; }
.mt-4 { margin-top: 1.5rem !important; }
.mb-2 { margin-bottom: 0.5rem !important; }
.mb-3 { margin-bottom: 1rem !important; }
.mb-4 { margin-bottom: 1.5rem !important; }

.d-flex { display: flex !important; }
.flex-wrap { flex-wrap: wrap !important; }
.justify-content-between { justify-content: space-between !important; }
.align-items-center { align-items: center !important; }
.text-center { text-align: center !important; }
.w-100 { width: 100% !important; }

.text-sm { font-size: 0.875rem !important; }
.small { font-size: 0.875rem !important; }

.position-static { position: static !important; }
.cursor-pointer { cursor: pointer !important; }
</style>