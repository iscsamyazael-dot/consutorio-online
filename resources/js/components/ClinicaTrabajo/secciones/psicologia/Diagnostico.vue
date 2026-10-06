<template>
  <div class="seccion-diagnostico-psicologia">
    <!-- SECTION: DIAGNÓSTICO CLÍNICO -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-stethoscope mr-2"></i> DIAGNÓSTICO CLÍNICO
        </h5>
      </div>
      <div class="card-body">
        <div class="row mb-4">
          <!-- DIAGNÓSTICO CLÍNICO -->
          <div class="col-md-12 mb-3">
            <label class="font-weight-bold">Diagnóstico Clínico <span class="text-danger">*</span></label>
            <textarea v-model="form.diagnostico_clinico" rows="3" class="form-control" placeholder="Describa el diagnóstico clínico completo..." style="height: 96px;" required></textarea>
          </div>

          <!-- CÓDIGO CIE-11 -->
          <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Código CIE-11</label>
            <input type="text" v-model="form.codigo_cie11" class="form-control" placeholder="Ej: 6B40, 6B41, etc." maxlength="20" style="height: 38px;" />
            <p class="text-xs text-muted mt-1">Clasificación Internacional de Enfermedades 11ª Revisión</p>
          </div>

          <!-- RELACIONADO CON EL TRABAJO -->
          <div class="col-md-6 mb-3">
            <div class="form-check mt-4">
              <input class="form-check-input" type="checkbox" v-model="form.relacionado_con_trabajo" id="relacionadoTrabajo" @change="updateField('relacionado_con_trabajo', $event.target.checked)" />
              <label class="form-check-label font-weight-bold" for="relacionadoTrabajo">
                <i class="fas fa-industry mr-1 text-primary"></i> Relacionado con el trabajo
              </label>
              <p class="text-sm text-muted mt-1 ml-4">Según criterios de enfermedad profesional</p>
            </div>
          </div>
        </div>

        <!-- BÚSQUEDA CIE-11 RÁPIDA (ESTILO TABLAS DE REFERENCIA) -->
        <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded">
          <h5 class="font-weight-bold text-success mb-3 flex items-center gap-2">
            <i class="fas fa-search mr-2"></i> CÓDIGOS CIE-11 COMUNES (PSICOLOGÍA)
          </h5>
          <div class="row">
            <div class="col-12">
              <button
                type="button"
                v-for="codigo in cie11Comunes"
                :key="codigo.codigo"
                @click="usarCie11(codigo)"
                class="btn btn-outline-success btn-sm m-1 text-left"
                style="min-width: 200px;"
              >
                <div class="font-mono font-weight-bold text-success">{{ codigo.codigo }}</div>
                <div class="text-muted small">{{ codigo.descripcion }}</div>
              </button>
            </div>
          </div>
        </div>

        <!-- TABLA DE REFERENCIA CIE-11 -->
        <div class="mt-4 pt-3 border-top">
          <div class="card-header pb-0 pt-0">
            <h5 class="mb-0">
              <i class="fas fa-book-medical mr-2"></i> REFERENCIA CIE-11 CAPÍTULO 6 (TRASTORNOS MENTALES)
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <h6 class="font-weight-bold text-dark mb-2">Trastornos relacionados con el estrés</h6>
                <ul class="list-unstyled text-sm">
                  <li><i class="fas fa-circle text-success mr-1"></i> <strong>6B40:</strong> Trastorno de estrés postraumático (TEPT)</li>
                  <li><i class="fas fa-circle text-success mr-1"></i> <strong>6B41:</strong> Trastorno de estrés agudo</li>
                  <li><i class="fas fa-circle text-success mr-1"></i> <strong>6B42:</strong> Trastorno de adaptación</li>
                  <li><i class="fas fa-circle text-success mr-1"></i> <strong>6B43:</strong> Trastorno de duelo prolongado</li>
                </ul>
              </div>
              <div class="col-md-6">
                <h6 class="font-weight-bold text-dark mb-2">Trastornos del estado de ánimo y ansiedad</h6>
                <ul class="list-unstyled text-sm">
                  <li><i class="fas fa-circle text-info mr-1"></i> <strong>6A70-6A72:</strong> Episodio depresivo (leve/moderado/grave)</li>
                  <li><i class="fas fa-circle text-info mr-1"></i> <strong>6A60:</strong> Trastorno de ansiedad generalizada</li>
                  <li><i class="fas fa-circle text-info mr-1"></i> <strong>6A61:</strong> Trastorno de pánico</li>
                  <li><i class="fas fa-circle text-info mr-1"></i> <strong>6A62:</strong> Trastorno de ansiedad social</li>
                </ul>
              </div>
            </div>
            <div class="row mt-3">
              <div class="col-md-6">
                <h6 class="font-weight-bold text-dark mb-2">Problemas asociados al empleo</h6>
                <ul class="list-unstyled text-sm">
                  <li><i class="fas fa-circle text-warning mr-1"></i> <strong>6A20:</strong> Burnout (síndrome de desgaste profesional)</li>
                  <li><i class="fas fa-circle text-warning mr-1"></i> <strong>QE84:</strong> Problemas relacionados con el empleo</li>
                  <li><i class="fas fa-circle text-warning mr-1"></i> <strong>QE83:</strong> Problemas relacionados con el desempleo</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- RESUMEN DIAGNÓSTICO -->
        <div v-if="form.diagnostico_clinico || form.codigo_cie11" class="mt-4 p-3 bg-light border border-left border-success">
          <h5 class="font-weight-bold text-success mb-3 flex items-center gap-2">
            <i class="fas fa-file-medical mr-2"></i> RESUMEN DIAGNÓSTICO
          </h5>
          <div class="row">
            <div class="col-md-6">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Diagnóstico:</span>
                <span class="font-weight-bold">{{ form.diagnostico_clinico || '—' }}</span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex justify-content-between">
                <span class="text-muted">CIE-11:</span>
                <span class="font-weight-bold">{{ form.codigo_cie11 || '—' }}</span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Relacionado con trabajo:</span>
                <span class="font-weight-bold" :class="form.relacionado_con_trabajo ? 'text-success' : 'text-muted'">
                  {{ form.relacionado_con_trabajo ? 'Sí' : 'No' }}
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
export default {
  name: 'DiagnosticoPsicologia',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      cie11Comunes: [
        { codigo: '6B40', descripcion: 'Trastorno de estrés postraumático' },
        { codigo: '6B41', descripcion: 'Trastorno de estrés agudo' },
        { codigo: '6B42', descripcion: 'Trastorno de adaptación' },
        { codigo: '6B43', descripcion: 'Trastorno de duelo prolongado' },
        { codigo: '6A70', descripcion: 'Episodio depresivo leve' },
        { codigo: '6A71', descripcion: 'Episodio depresivo moderado' },
        { codigo: '6A72', descripcion: 'Episodio depresivo grave' },
        { codigo: '6A60', descripcion: 'Trastorno de ansiedad generalizada' },
        { codigo: '6A61', descripcion: 'Trastorno de pánico' },
        { codigo: '6A62', descripcion: 'Trastorno de ansiedad social' },
        { codigo: '6A20', descripcion: 'Burnout (síndrome de desgaste profesional)' },
        { codigo: 'QE84', descripcion: 'Problemas relacionados con el empleo' }
      ]
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
    },
    usarCie11(codigo) {
      this.updateField('codigo_cie11', codigo.codigo)
      if (!this.form.diagnostico_clinico) {
        this.updateField('diagnostico_clinico', codigo.descripcion)
      }
    }
  }
}
</script>

<style scoped>
.seccion-diagnostico-psicologia {
  background: #f8f9fa;
}

.card {
  border: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.card-header {
  background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
  color: white !important;
}

.form-control {
  border-radius: 4px;
  border: 1px solid #ced4da;
  font-size: 14px;
  padding: 10px 12px;
}

.form-control:focus {
  border-color: #28a745;
  box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
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

.btn-outline-success {
  border-color: #28a745;
  color: #28a745;
  background: white;
}

.btn-outline-success:hover {
  background-color: #28a745;
  color: white;
}

.btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
}

.text-muted { color: #6c757d !important; }
.text-success { color: #28a745 !important; }
.text-info { color: #17a2b8 !important; }
.text-warning { color: #ffc107 !important; }
.text-danger { color: #dc3545 !important; }
.font-weight-bold { font-weight: 600 !important; }
.font-mono { font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important; }
.bg-light { background-color: #f8f9fa !important; }
.bg-white { background-color: #fff !important; }
.bg-green-50 { background-color: #d4edda !important; }
.bg-success { background-color: #28a745 !important; }

.border { border: 1px solid #dee2e6 !important; }
.border-top { border-top: 1px solid #dee2e6 !important; }
.border-left { border-left: 4px solid !important; }
.border-success { border-color: #28a745 !important; }
.border-green-200 { border-color: #c3e6cb !important; }

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
.m-1 { margin: 0.25rem !important; }

.d-flex { display: flex !important; }
.flex-wrap { flex-wrap: wrap !important; }
.justify-content-between { justify-content: space-between !important; }
.align-items-center { align-items: center !important; }
.text-center { text-align: center !important; }
.w-100 { width: 100% !important; }

.text-sm { font-size: 0.875rem !important; }
.small { font-size: 0.875rem !important; }
.text-xs { font-size: 0.75rem !important; }

.list-unstyled { padding-left: 0; list-style: none; }
</style>