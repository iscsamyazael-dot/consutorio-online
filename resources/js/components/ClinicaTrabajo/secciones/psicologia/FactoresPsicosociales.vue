<template>
  <div class="seccion-guia-iii-factores-psicologia">
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-brain mr-2"></i> GUÍA III: FACTORES PSICOSOCIALES (NOM-035-STPS-2018)
        </h5>
      </div>
      <div class="card-body">
        <div class="mb-4">
          <div class="border rounded p-3 bg-light">
            <div class="form-check">
              <input 
                class="form-check-input" 
                type="checkbox" 
                v-model="form.guia_ref_iii_aplicada" 
                id="guiaIII" 
                @change="updateField('guia_ref_iii_aplicada', $event.target.checked)" 
              />
              <label class="form-check-label font-weight-bold text-dark" for="guiaIII">
                <i class="fas fa-chart-bar mr-2 text-purple"></i> Aplicar Guía de Referencia III (Identificación de Factores Psicosociales)
              </label>
            </div>
            <p class="text-sm text-muted mt-1 ml-4">Seleccione los factores psicosociales presentes en el ambiente laboral</p>
          </div>
        </div>

        <div v-if="form.guia_ref_iii_aplicada">
          <div class="mb-4">
            <label class="font-weight-bold">Descripción del Ambiente Laboral</label>
            <textarea 
              v-model="form.ambiente_laboral_descripcion" 
              rows="2" 
              class="form-control" 
              placeholder="Describa las condiciones generales del ambiente de trabajo..." 
              style="height: 68px;"
            ></textarea>
          </div>

          <div class="row mb-4">
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold">Puntuación de Riesgo (Texto)</label>
              <input 
                type="text" 
                v-model="form.puntuacion_riesgo_texto" 
                class="form-control" 
                placeholder="Ej: Alto, Medio, Bajo, o puntuación numérica" 
                style="height: 38px;" 
              />
            </div>
          </div>

          <div class="mb-4">
            <label class="font-weight-bold">Factores Psicosociales Identificados</label>
            <div v-if="factoresDisponibles.length === 0" class="text-center py-4 text-muted">
              <p>Cargando catálogo de factores...</p>
            </div>
            <div v-else class="row">
              <div
                v-for="factor in factoresDisponibles"
                :key="factor.id"
                class="col-md-4 mb-3"
              >
                <div 
                  class="border rounded p-3 transition"
                  :class="{ 'border-2 border-primary bg-light': isFactorSeleccionado(factor.id) }"
                >
                  <label class="form-check-label cursor-pointer">
                    <input
                      type="checkbox"
                      class="form-check-input"
                      :checked="isFactorSeleccionado(factor.id)"
                      @change="toggleFactor(factor.id, $event.target.checked)"
                    />
                    <span class="font-weight-bold text-dark ml-2">{{ factor.nombre }}</span>
                  </label>
                  <div class="text-xs text-muted mt-1">{{ factor.descripcion }}</div>
                  
                  <div v-if="isFactorSeleccionado(factor.id)" class="mt-2">
                    <label class="text-xs font-weight-bold text-gray-600">Severidad:</label>
                    <select
                      :value="getSeveridad(factor.id)"
                      @change="updateSeveridad(factor.id, $event.target.value)"
                      class="form-control form-control-sm"
                      style="height: 30px; font-size: 0.8rem;"
                    >
                      <option value="leve">Leve</option>
                      <option value="moderado">Moderado</option>
                      <option value="severo">Severo</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="factoresSeleccionados.length > 0" class="mt-4 p-3 bg-light border border-primary rounded">
              <h5 class="font-weight-bold text-primary mb-2">
                <i class="fas fa-tags mr-2"></i> Factores Seleccionados ({{ factoresSeleccionados.length }})
              </h5>
              <div class="d-flex flex-wrap gap-2">
                <span v-for="f in factoresSeleccionados" :key="f.id" class="badge badge-primary">
                  {{ f.nombre }} ({{ getSeveridad(f.id) }})
                </span>
              </div>
            </div>
          </div>

          <div class="mt-4 pt-3 border-top">
            <div class="card-header pb-0 pt-0">
              <h5 class="mb-0"><i class="fas fa-book mr-2"></i> CATEGORÍAS NOM-035 (GUÍA III)</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-4">
                  <h6 class="font-weight-bold text-dark mb-2">Condiciones del ambiente de trabajo</h6>
                  <ul class="list-unstyled text-sm">
                    <li><i class="fas fa-circle text-primary mr-1"></i> Iluminación</li>
                    <li><i class="fas fa-circle text-primary mr-1"></i> Ruido</li>
                    <li><i class="fas fa-circle text-primary mr-1"></i> Temperatura</li>
                    <li><i class="fas fa-circle text-primary mr-1"></i> Ventilación</li>
                  </ul>
                </div>
                <div class="col-md-4">
                  <h6 class="font-weight-bold text-dark mb-2">Factores de la tarea</h6>
                  <ul class="list-unstyled text-sm">
                    <li><i class="fas fa-circle text-primary mr-1"></i> Carga mental</li>
                    <li><i class="fas fa-circle text-primary mr-1"></i> Control</li>
                    <li><i class="fas fa-circle text-primary mr-1"></i> Variedad</li>
                    <li><i class="fas fa-circle text-primary mr-1"></i> Significado</li>
                  </ul>
                </div>
                <div class="col-md-4">
                  <h6 class="font-weight-bold text-dark mb-2">Organización del tiempo de trabajo</h6>
                  <ul class="list-unstyled text-sm">
                    <li><i class="fas fa-circle text-primary mr-1"></i> Jornada</li>
                    <li><i class="fas fa-circle text-primary mr-1"></i> Turnos</li>
                    <li><i class="fas fa-circle text-primary mr-1"></i> Descansos</li>
                    <li><i class="fas fa-circle text-primary mr-1"></i> Horas extra</li>
                  </ul>
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-md-4">
                  <h6 class="font-weight-bold text-dark mb-2">Liderazgo y relaciones</h6>
                  <ul class="list-unstyled text-sm">
                    <li><i class="fas fa-circle text-primary mr-1"></i> Supervisión</li>
                    <li><i class="fas fa-circle text-primary mr-1"></i> Apoyo social</li>
                    <li><i class="fas fa-circle text-primary mr-1"></i> Violencia</li>
                    <li><i class="fas fa-circle text-primary mr-1"></i> Acoso</li>
                  </ul>
                </div>
                <div class="col-md-4">
                  <h6 class="font-weight-bold text-dark mb-2">Ambiente organizacional</h6>
                  <ul class="list-unstyled text-sm">
                    <li><i class="fas fa-circle text-primary mr-1"></i> Comunicación</li>
                    <li><i class="fas fa-circle text-primary mr-1"></i> Participación</li>
                    <li><i class="fas fa-circle text-primary mr-1"></i> Justicia</li>
                    <li><i class="fas fa-circle text-primary mr-1"></i> Recompensa</li>
                  </ul>
                </div>
                <div class="col-md-4">
                  <h6 class="font-weight-bold text-dark mb-2">Vida trabajo - vida personal</h6>
                  <ul class="list-unstyled text-sm">
                    <li><i class="fas fa-circle text-primary mr-1"></i> Interferencia</li>
                    <li><i class="fas fa-circle text-primary mr-1"></i> Flexibilidad</li>
                    <li><i class="fas fa-circle text-primary mr-1"></i> Conciliación</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <div v-if="factoresSeleccionados.length > 0" class="mt-4 p-3 bg-light border-left border-primary rounded" style="border-left: 4px solid #007bff !important;">
            <h5 class="font-weight-bold text-primary mb-3">
              <i class="fas fa-file-alt mr-2"></i> RESUMEN GUÍA III
            </h5>
            <div class="row">
              <div class="col-md-4">
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Factores identificados:</span>
                  <span class="font-weight-bold">{{ factoresSeleccionados.length }}</span>
                </div>
              </div>
              <div class="col-md-4">
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Severidad máxima:</span>
                  <span class="font-weight-bold" :class="getSeveridadMaximaClass()">{{ severidadMaxima }}</span>
                </div>
              </div>
              <div class="col-md-4">
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Puntuación riesgo:</span>
                  <span class="font-weight-bold">{{ form.puntuacion_riesgo_texto || '—' }}</span>
                </div>
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
  name: 'GuiaIIIFactoresPsicologia',
  props: {
    modelValue: { type: Object, required: true },
    factores: { type: Array, default: () => [] }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      factoresDisponibles: [],
      factoresIds: [],
      factoresSeveridad: {}
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },
    factoresSeleccionados() {
      return this.factoresDisponibles.filter(f => this.factoresIds.includes(f.id))
    },
    severidadMaxima() {
      if (this.factoresSeleccionados.length === 0) return '—'
      const severidades = this.factoresSeleccionados.map(f => this.getSeveridad(f.id))
      if (severidades.includes('severo')) return 'Severo'
      if (severidades.includes('moderado')) return 'Moderado'
      return 'Leve'
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
    getSeveridadMaximaClass() {
      if (this.severidadMaxima === 'Severo') return 'text-danger'
      if (this.severidadMaxima === 'Moderado') return 'text-warning'
      return 'text-success'
    },
    syncFactoresConForm() {
      this.updateField('factores_ids', [...this.factoresIds])
      this.updateField('factores_severidad', this.factoresIds.map(id => this.factoresSeveridad[id]))
    },
    cargarFactores() {
      this.factoresDisponibles = this.factores
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
.seccion-guia-iii-factores-psicologia { background: #f8f9fa; }
.card { border: 1px solid #dee2e6; box-shadow: 0 2px 4px rgba(0,0,0,0.08); }
.card-header { background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%) !important; color: white !important; }
.form-control { border-radius: 4px; border: 1px solid #ced4da; font-size: 14px; padding: 10px 12px; }
.form-control:focus { border-color: #6f42c1; box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25); }
.form-control-sm { font-size: 0.8125rem; padding: 4px 8px; }
.form-check-input { width: 1.1rem; height: 1.1rem; margin-top: 0.15rem; }
.form-check-label { font-size: 0.9rem; cursor: pointer; }
.badge { font-size: 0.75rem; padding: 0.375rem 0.75rem; border-radius: 2rem; }
.badge-primary { background-color: #007bff; color: white; }
.text-muted { color: #6c757d !important; }
.text-primary { color: #007bff !important; }
.text-danger { color: #dc3545 !important; }
.text-warning { color: #ffc107 !important; }
.text-success { color: #28a745 !important; }
.font-weight-bold { font-weight: 600 !important; }
.bg-light { background-color: #f8f9fa !important; }
.bg-white { background-color: #fff !important; }
.border { border: 1px solid #dee2e6 !important; }
.border-top { border-top: 1px solid #dee2e6 !important; }
.border-left { border-left: 4px solid !important; }
.border-primary { border-color: #007bff !important; }
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
.text-center { text-align: center !important; }
.w-100 { width: 100% !important; }
.text-sm { font-size: 0.875rem !important; }
.small { font-size: 0.875rem !important; }
.list-unstyled { padding-left: 0; list-style: none; }
.transition { transition: all 0.2s ease; }
.cursor-pointer { cursor: pointer; }
.gap-2 { gap: 10px; }
.ml-2 { margin-left: 0.5rem; }
.mr-1 { margin-right: 0.25rem; }
.mr-2 { margin-right: 0.5rem; }
</style>