<template>
  <div class="seccion-interpretacion-espirometria">
    <!-- SECTION: INTERPRETACIÓN CLÍNICA Y DICTAMEN -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #1E88E5 0%, #1565C0 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-stethoscope mr-2"></i> INTERPRETACIÓN CLÍNICA Y DICTAMEN
        </h5>
      </div>
      <div class="card-body">
        <!-- RESUMEN AUTOMÁTICO DE LA PRUEBA (ESTILO NUTRICIÓN) -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
          <h5 class="font-weight-bold text-info mb-3 flex items-center gap-2">
            <i class="fas fa-clipboard-check mr-2"></i> RESUMEN AUTOMÁTICO DE LA PRUEBA
          </h5>
          <div class="row">
            <div class="col-md-4">
              <div class="d-flex justify-content-between">
                <span class="text-muted small">FEV1 / Predicho:</span>
                <span class="font-weight-bold" :class="form.fev1_porcentaje_predicho >= 80 ? 'text-success' : 'text-danger'">
                  {{ form.fev1_porcentaje_predicho || '—' }}%
                </span>
              </div>
              <div class="d-flex justify-content-between mt-2">
                <span class="text-muted small">Clasificación FEV1:</span>
                <span :class="getFev1BadgeClass" class="badge badge-pill">{{ clasificacionFev1 }}</span>
              </div>
            </div>
            <div class="col-md-4">
              <div class="d-flex justify-content-between">
                <span class="text-muted small">Relación FEV1/FVC:</span>
                <span class="font-weight-bold" :class="form.fev1_fvc_ratio >= 0.70 ? 'text-success' : 'text-danger'">
                  {{ form.fev1_fvc_ratio || '—' }}
                </span>
              </div>
              <div class="d-flex justify-content-between mt-2">
                <span class="text-muted small">Patrón:</span>
                <span :class="getPatronColorClass" class="badge badge-pill">{{ patronSugerido || '—' }}</span>
              </div>
            </div>
            <div class="col-md-4">
              <div class="d-flex justify-content-between">
                <span class="text-muted small">Edad Pulmonar Estimada:</span>
                <span class="font-weight-bold text-primary">{{ form.edad_pulmonar || '—' }} años</span>
              </div>
              <div class="d-flex justify-content-between mt-2">
                <span class="text-muted small">FVC / Predicho:</span>
                <span class="font-weight-bold" :class="form.fvc_porcentaje_predicho >= 80 ? 'text-success' : 'text-danger'">
                  {{ form.fvc_porcentaje_predicho || '—' }}%
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- CALIDAD DE LA SESIÓN Y PATRÓN CLÍNICO -->
        <div class="row mb-4">
          <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Calidad de la Sesión (ATS/ERS) <span class="text-danger">*</span></label>
            <select v-model="form.calidad_sesion" class="form-control" style="height: 38px;">
              <option value="">Seleccionar Grado...</option>
              <option value="A">Grado A (Excelente) - Cumple todos los criterios</option>
              <option value="B">Grado B (Bueno) - Cumple la mayoría de criterios</option>
              <option value="C">Grado C (Aceptable) - Cumple mínimos, algunos criterios fallan</option>
              <option value="D">Grado D (Marginal) - No cumple mínimos, interpretación limitada</option>
              <option value="F">Grado F (No útil) - Prueba inválida, repetir</option>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Interpretación del Sistema / Patrón <span class="text-danger">*</span></label>
            <select v-model="form.interpretacion_sistema" class="form-control" style="height: 38px;">
              <option value="Espirometría Normal">Espirometría Normal</option>
              <option value="Patrón Obstructivo">Patrón Obstructivo</option>
              <option value="Patrón Restrictivo">Patrón Restrictivo</option>
              <option value="Patrón Mixto">Patrón Mixto</option>
              <option value="Prueba no válida">Prueba no válida</option>
            </select>
          </div>
        </div>

        <!-- DIAGNÓSTICO Y APTITUD -->
        <div class="row mb-4">
          <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Diagnóstico Médico *</label>
            <textarea v-model="form.diagnostico_medico" rows="4" class="form-control"
              placeholder="Describir hallazgos espirométricos, patrón (obstructivo/restrictivo), gravedad y sugerencias..."
              style="height: 128px;"></textarea>
          </div>
          <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Aptitud para el puesto <span class="text-danger">*</span></label>
            <div class="row">
              <div v-for="opcion in aptitudOpciones" :key="opcion.value" class="col-12 mb-2">
                <div class="border rounded p-3 text-center cursor-pointer transition-all"
                  :class="[
                    'border-' + (form.aptitud === opcion.value ? 'success' : 'secondary'),
                    'bg-' + (form.aptitud === opcion.value ? 'success' : 'light'),
                    'text-' + (form.aptitud === opcion.value ? 'white' : 'dark')
                  ]">
                  <input type="radio" name="aptitud" :value="opcion.value"
                    :checked="form.aptitud === opcion.value"
                    @change="updateField('aptitud', opcion.value)"
                    class="position-static mr-2" />
                  <div class="font-weight-bold d-inline">
                    {{ opcion.icon }} {{ opcion.label }}
                  </div>
                  <div class="small mt-1" :class="form.aptitud === opcion.value ? 'text-white' : 'text-muted'">
                    {{ opcion.descripcion }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- RECOMENDACIONES -->
        <div class="mb-4">
          <label class="font-weight-bold">Recomendaciones y Observaciones Finales</label>
          <textarea v-model="form.recomendaciones" rows="3" class="form-control"
            placeholder="Ej: Se recomienda control anual, cesación tabáquica, tratamiento de asma, ..."
            style="height: 96px;"></textarea>
        </div>

        <!-- RESUMEN DEL DICTAMEN FINAL -->
        <div v-if="form.aptitud && form.interpretacion_sistema" class="mt-4 p-3 bg-light border border-left border-success">
          <h5 class="font-weight-bold text-success mb-3 flex items-center gap-2">
            <i class="fas fa-file-medical-alt mr-2"></i> RESUMEN DEL DICTAMEN
          </h5>
          <div class="row">
            <div class="col-md-3">
              <div class="d-flex justify-content-between">
                <span class="text-muted small">Patrón:</span>
                <span class="font-weight-bold">{{ form.interpretacion_sistema }}</span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="d-flex justify-content-between">
                <span class="text-muted small">Calidad:</span>
                <span class="font-weight-bold">{{ form.calidad_sesion }} {{ calidadCompleto(form.calidad_sesion) }}</span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="d-flex justify-content-between">
                <span class="text-muted small">Aptitud:</span>
                <span class="font-weight-bold" :class="getAptitudClass">{{ aptitudLabel }}</span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="d-flex justify-content-between">
                <span class="text-muted small">FEV1%:</span>
                <span class="font-weight-bold" :class="form.fev1_porcentaje_predicho >= 80 ? 'text-success' : 'text-danger'">
                  {{ form.fev1_porcentaje_predicho || '—' }}%
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- TABLAS DE REFERENCIA -->
        <div class="mt-4 pt-3 border-top">
          <div class="card-header pb-0 pt-0">
            <h5 class="mb-0">
              <i class="fas fa-table mr-2"></i> REFERENCIAS DE INTERPRETACIÓN ATS/ERS Y CLASIFICACIÓN DE GRAVEDAD
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <h6 class="font-weight-bold text-dark mb-2">Criterios ATS/ERS de Calidad de la Sesión</h6>
                <ul class="list-unstyled text-sm">
                  <li><i class="fas fa-check-circle text-success mr-1"></i> <strong>A:</strong> ≥3 pruebas aceptables + variabilidad FEV1 ≤0.150 L</li>
                  <li><i class="fas fa-check-circle text-success mr-1"></i> <strong>B:</strong> ≥2 pruebas aceptables + repetición FEV1/FVC concordante</li>
                  <li><i class="fas fa-check-circle text-info mr-1"></i> <strong>C:</strong> 1 prueba aceptable, criterios mínimos cumplidos</li>
                  <li><i class="fas fa-exclamation-triangle text-warning mr-1"></i> <strong>D:</strong> ≤0.150 L de variabilidad, calidad limitada</li>
                  <li><i class="fas fa-times-circle text-danger mr-1"></i> <strong>F:</strong> No se cumplen criterios mínimos, repetir</li>
                </ul>
              </div>
              <div class="col-md-6">
                <h6 class="font-weight-bold text-dark mb-2">Clasificación Gravedad Obstructivo (GOLD/ATS)</h6>
                <ul class="list-unstyled text-sm">
                  <li><i class="fas fa-circle text-success mr-1"></i> <strong>FEV1 ≥ 80%:</strong> Normal / Leve</li>
                  <li><i class="fas fa-circle text-warning mr-1"></i> <strong>50-79%:</strong> Moderado</li>
                  <li><i class="fas fa-circle text-orange mr-1"></i> <strong>30-49%:</strong> Grave</li>
                  <li><i class="fas fa-circle text-danger mr-1"></i> <strong>&lt; 30%:</strong> Muy grave / Crítico</li>
                </ul>
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
  name: 'InterpretacionEspirometria',
  props: {
    modelValue: { type: Object, required: true }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      aptitudOpciones: [
        {
          value: 'Apto',
          label: 'APTO',
          icon: '✅',
          descripcion: 'Sin restricciones para el puesto de trabajo'
        },
        {
          value: 'Apto con restricciones',
          label: 'APTO CON RESTRICCIONES',
          icon: '⚠️',
          descripcion: 'Apto con limitaciones o seguimiento médico'
        },
        {
          value: 'No apto',
          label: 'NO APTO',
          icon: '❌',
          descripcion: 'No apto para actividades de riesgo respiratorio'
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
        case 'Apto': return 'text-success';
        case 'Apto con restricciones': return 'text-warning';
        case 'No apto': return 'text-danger';
        default: return 'text-muted';
      }
    },
    clasificacionFev1() {
      const pct = this.form.fev1_porcentaje_predicho
      if (!pct) return '—'
      if (pct >= 80) return 'Normal'
      if (pct >= 70) return 'Límite inferior normal'
      if (pct >= 60) return 'Moderado'
      if (pct >= 50) return 'Moderado-severo'
      if (pct >= 30) return 'Severo'
      return 'Muy severo'
    },
    getFev1BadgeClass() {
      const pct = this.form.fev1_porcentaje_predicho
      if (!pct) return 'text-muted'
      if (pct >= 80) return 'badge-success'
      if (pct >= 70) return 'badge-warning'
      return 'badge-danger'
    },
    patronSugerido() {
      const ratio = this.form.fev1_fvc_ratio
      const fev1Pct = this.form.fev1_porcentaje_predicho
      const fvcPct = this.form.fvc_porcentaje_predicho
      if (!ratio) return ''
      if (ratio < 0.70) {
        if (fvcPct < 80) return 'Patrón Mixto'
        return 'Patrón Obstructivo'
      }
      if (fvcPct < 80) return 'Patrón Restrictivo'
      return 'Patrón Normal'
    },
    getPatronColorClass() {
      const patron = this.patronSugerido
      if (!patron) return 'text-muted'
      if (patron.includes('Normal')) return 'badge-success'
      if (patron.includes('Obstructivo')) return 'badge-warning'
      if (patron.includes('Restrictivo')) return 'badge-info'
      if (patron.includes('Mixto')) return 'badge-danger'
      return 'text-muted'
    }
  },
  methods: {
    updateField(key, value) {
      this.$emit('update:modelValue', { ...this.form, [key]: value })
    },
    calidadCompleto(grado) {
      const map = {
        'A': '(Excelente)',
        'B': '(Bueno)',
        'C': '(Aceptable)',
        'D': '(Marginal)',
        'F': '(No útil)'
      }
      return map[grado] || ''
    }
  }
}
</script>

<style scoped>
.seccion-interpretacion-espirometria {
  background: #f8f9fa;
}

.card {
  border: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.card-header {
  background: linear-gradient(135deg, #1E88E5 0%, #1565C0 100%) !important;
  color: white !important;
  border-bottom: none;
  padding: 0.75rem 1.25rem;
}

.card-body {
  padding: 1.25rem;
}

.form-control {
  border-radius: 4px;
  border: 1px solid #ced4da;
  font-size: 14px;
  padding: 10px 12px;
}

.form-control:focus {
  border-color: #1E88E5;
  box-shadow: 0 0 0 0.2rem rgba(30, 136, 229, 0.25);
}

.form-check-label {
  font-size: 0.9rem;
  cursor: pointer;
}

.badge {
  font-size: 0.8125rem;
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

.badge-info {
  background-color: #17a2b8;
  color: white;
}

.badge-danger {
  background-color: #dc3545;
  color: white;
}

.text-success { color: #28a745 !important; }
.text-warning { color: #ffc107 !important; }
.text-info { color: #17a2b8 !important; }
.text-danger { color: #dc3545 !important; }
.text-primary { color: #1E88E5 !important; }
.text-muted { color: #6c757d !important; }
.text-orange { color: #fd7e14 !important; }

.font-weight-bold { font-weight: 600 !important; }
.bg-light { background-color: #f8f9fa !important; }
.bg-success { background-color: #28a745 !important; }
.bg-white { background-color: #fff !important; }

.border { border: 1px solid #dee2e6 !important; }
.border-top { border-top: 1px solid #dee2e6 !important; }
.border-left { border-left: 4px solid !important; }
.border-success { border-color: #28a745 !important; }
.border-secondary { border-color: #6c757d !important; }

.rounded { border-radius: 0.25rem !important; }
.p-2 { padding: 0.5rem !important; }
.p-3 { padding: 1rem !important; }
.p-4 { padding: 1.5rem !important; }
.mt-2 { margin-top: 0.5rem !important; }
.mt-3 { margin-top: 1rem !important; }
.mt-4 { margin-top: 1.5rem !important; }
.mb-2 { margin-bottom: 0.5rem !important; }
.mb-3 { margin-bottom: 1rem !important; }
.mb-4 { margin-bottom: 1.5rem !important; }
.mb-6 { margin-bottom: 3rem !important; }
.ml-2 { margin-left: 0.5rem !important; }

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

.bg-blue-50 { background-color: #dbeafe !important; }
.border-blue-200 { border-color: #bfdbfe !important; }

.transition-all { transition: all 0.2s !important; }

.list-unstyled { padding-left: 0; list-style: none; }

.row { display: flex; flex-wrap: wrap; margin-right: -0.75rem; margin-left: -0.75rem; }
.col-md-3 { flex: 0 0 25%; max-width: 25%; }
.col-md-4 { flex: 0 0 33.333%; max-width: 33.333%; }
.col-md-6 { flex: 0 0 50%; max-width: 50%; }
.col-md-12 { flex: 0 0 100%; max-width: 100%; }

@media (max-width: 768px) {
  .col-md-3, .col-md-4, .col-md-6 { flex: 0 0 100%; max-width: 100%; }
}
</style>
