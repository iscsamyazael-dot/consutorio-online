<template>
  <div class="seccion-factores-laborales-ergonomia">
    <!-- SECTION: FACTORES LABORALES -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #2E8B57 0%, #228B22 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-industry mr-2"></i> FACTORES LABORALES
        </h5>
      </div>
      <div class="card-body">
        <p class="text-muted mb-4">
          Indique si el paciente está expuesto a los siguientes factores de riesgo en su entorno laboral.
        </p>

        <!-- Fila 1: Factores principales -->
        <div class="row mb-4">
          <div v-for="(factor, index) in factoresLaborales.slice(0, 4)"
               :key="factor.key"
               class="col-md-3">
            <div class="border rounded p-3 hover:bg-light transition">
              <label class="font-weight-bold d-block mb-2">
                {{ factor.label }}
              </label>
              <div class="btn-group-toggle" data-toggle="buttons">
                <label v-for="opcion in opcionesSI_NO"
                       :key="opcion.value"
                       class="btn btn-outline-secondary btn-sm m-1 rounded-pill"
                       :class="[form[factor.key] === opcion.value ? 'active' : '']">
                  <input type="radio"
                         :name="factor.key"
                         :value="opcion.value"
                         :checked="form[factor.key] === opcion.value"
                         @change="updateField(factor.key, opcion.value)"
                         class="position-static">
                  {{ opcion.label }}
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Fila 2: Factores restantes -->
        <div class="row mb-4">
          <div v-for="(factor, index) in factoresLaborales.slice(4, 8)"
               :key="factor.key"
               class="col-md-3">
            <div class="border rounded p-3 hover:bg-light transition">
              <label class="font-weight-bold d-block mb-2">
                {{ factor.label }}
              </label>
              <div class="btn-group-toggle" data-toggle="buttons">
                <label v-for="opcion in opcionesSI_NO"
                       :key="opcion.value"
                       class="btn btn-outline-secondary btn-sm m-1 rounded-pill"
                       :class="[form[factor.key] === opcion.value ? 'active' : '']">
                  <input type="radio"
                         :name="factor.key"
                         :value="opcion.value"
                         :checked="form[factor.key] === opcion.value"
                         @change="updateField(factor.key, opcion.value)"
                         class="position-static">
                  {{ opcion.label }}
                </label>
              </div>
            </div>
          </div>
          <div v-if="factoresLaborales.length > 8" :key="'otros'" class="col-md-3">
            <div class="border rounded p-3 hover:bg-light transition">
              <label class="font-weight-bold d-block mb-2">
                Otros
              </label>
              <div class="btn-group-toggle" data-toggle="buttons">
                <label v-for="opcion in opcionesSI_NO"
                       :key="opcion.value"
                       class="btn btn-outline-secondary btn-sm m-1 rounded-pill"
                       :class="[form.ocurrencias_traumaticos === opcion.value ? 'active' : '']">
                  <input type="radio"
                         name="ocurrencias_traumaticos"
                         :value="opcion.value"
                         :checked="form.ocurrencias_traumaticos === opcion.value"
                         @change="updateField('ocurrencias_traumaticos', opcion.value)"
                         class="position-static">
                  {{ opcion.label }}
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- CONDICIONES DE ENTORNO -->
        <div v-if="form.condiciones_entorno === 'si'" class="mt-4 pt-3 border-top border-top">
          <div class="card-header pb-0 pt-0">
            <h5 class="mb-0">
              <i class="fas fa-cloud-sun-rain mr-2"></i> CONDICIONES DE ENTORNO TRABAJO
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <!-- Fila de condiciones de entorno -->
              <div v-for="(condicion, index) in condicionesEntorno.slice(0, 3)"
                   :key="condicion.key"
                   class="col-md-4 mb-3">
                <div class="border rounded p-2">
                  <label class="font-weight-bold d-block mb-1">
                    {{ condicion.label }}
                  </label>
                  <div class="form-check">
                    <input type="checkbox"
                           class="form-check-input"
                           :id="condicion.key"
                           v-model="form[condicion.key]"
                    >
                    <label class="form-check-label" :for="condicion.key">
                      {{ condicion.label }}
                    </label>
                  </div>
                </div>
              </div>
              <div v-for="(condicion, index) in condicionesEntorno.slice(3, 6)"
                   :key="condicion.key"
                   class="col-md-4 mb-3">
                <div class="border rounded p-2">
                  <label class="font-weight-bold d-block mb-1">
                    {{ condicion.label }}
                  </label>
                  <div class="form-check">
                    <input type="checkbox"
                           class="form-check-input"
                           :id="condicion.key"
                           v-model="form[condicion.key]"
                    >
                    <label class="form-check-label" :for="condicion.key">
                      {{ condicion.label }}
                    </label>
                  </div>
                </div>
              </div>
              <div v-for="(condicion, index) in condicionesEntorno.slice(6, 9)"
                   :key="condicion.key"
                   class="col-md-4 mb-3">
                <div class="border rounded p-2">
                  <label class="font-weight-bold d-block mb-1">
                    {{ condicion.label }}
                  </label>
                  <div class="form-check">
                    <input type="checkbox"
                           class="form-check-input"
                           :id="condicion.key"
                           v-model="form[condicion.key]"
                    >
                    <label class="form-check-label" :for="condicion.key">
                      {{ condicion.label }}
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- RESUMEN DE FACTORES DE RIESGO -->
        <div v-if="[form.tension_emocional, form.alta_responsabilidad, form.carga_excesiva_trabajo, form.turno_rotativo, form.turno_nocturno, form.trabajo_repetitivo, form.condiciones_entorno, form.carga_trabajo, form.falta_control_trabajo, form.jornadas_trabajo, form.interferencia_trabajo_familia, form.relaciones_interpersonales, form.violencia_laboral, form.ocurrencias_traumaticos].includes('si')" class="mt-4 p-3 bg-light border border-left border-success">
          <h5 class="font-weight-bold text-success mb-3 flex items-center gap-2">
            <i class="fas fa-info-circle mr-2"></i>
            Factores de Riesgo Laboral Identificados
          </h5>
          <div class="d-flex flex-wrap gap-2">
            <span v-if="form.tension_emocional === 'si'" class="badge badge-pill badge-success p-2">Tensión emocional</span>
            <span v-if="form.alta_responsabilidad === 'si'" class="badge badge-pill badge-success p-2">Alta responsabilidad</span>
            <span v-if="form.carga_excesiva_trabajo === 'si'" class="badge badge-pill badge-success p-2">Carga excesiva de trabajo</span>
            <span v-if="form.turno_rotativo === 'si'" class="badge badge-pill badge-success p-2">Turno rotativo</span>
            <span v-if="form.turno_nocturno === 'si'" class="badge badge-pill badge-success p-2">Turno nocturno</span>
            <span v-if="form.trabajo_repetitivo === 'si'" class="badge badge-pill badge-success p-2">Trabajo repetitivo</span>
            <span v-if="form.condiciones_entorno === 'si'" class="badge badge-pill badge-success p-2">
              Condiciones de entorno: {{ condicionesSeleccionadas.length > 0 ? condicionesSeleccionadas.map(c => c.label).join(', ') : 'No especificada' }}
            </span>
            <span v-if="form.carga_trabajo === 'si'" class="badge badge-pill badge-success p-2">Carga de trabajo</span>
            <span v-if="form.falta_control_trabajo === 'si'" class="badge badge-pill badge-success p-2">Falta de control laboral</span>
            <span v-if="form.jornadas_trabajo === 'si'" class="badge badge-pill badge-success p-2">Jornadas laborales</span>
            <span v-if="form.interferencia_trabajo_familia === 'si'" class="badge badge-pill badge-success p-2">Interferencia trabajo-familia</span>
            <span v-if="form.relaciones_interpersonales === 'si'" class="badge badge-pill badge-success p-2">Relaciones interpersonales</span>
            <span v-if="form.violencia_laboral === 'si'" class="badge badge-pill badge-success p-2">Violencia laboral</span>
            <span v-if="form.ocurrencias_traumaticos === 'si'" class="badge badge-pill badge-success p-2">Acontecimientos traumáticos</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FactoresLaboralesErgonomia',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      factoresLaborales: [
        { key: 'tension_emocional', label: 'Tensión emocional' },
        { key: 'alta_responsabilidad', label: 'Alta responsabilidad' },
        { key: 'carga_excesiva_trabajo', label: 'Carga excesiva de trabajo' },
        { key: 'turno_rotativo', label: 'Turno rotativo' },
        { key: 'turno_nocturno', label: 'Turno nocturno' },
        { key: 'trabajo_repetitivo', label: 'Trabajo repetitivo' },
        { key: 'condiciones_entorno', label: 'Condiciones de entorno adversas' },
        { key: 'carga_trabajo', label: 'Carga de trabajo' },
        { key: 'falta_control_trabajo', label: 'Falta de control en el trabajo' },
        { key: 'jornadas_trabajo', label: 'Jornadas laborales extensas' },
        { key: 'interferencia_trabajo_familia', label: 'Interferencia trabajo-familia' },
        { key: 'relaciones_interpersonales', label: 'Problemas de relaciones interpersonales' },
        { key: 'violencia_laboral', label: 'Violencia laboral' },
        { key: 'ocurrencias_traumaticos', label: 'Acontecimientos traumáticos laborales' }
      ],
      opcionesSI_NO: [
        { value: 'si', label: 'SÍ' },
        { value: 'no', label: 'NO' }
      ],
      condicionesEntorno: [
        { key: 'temperatura_extrema', label: 'Temperatura extrema (calor/frío)' },
        { key: 'ruido_elevo', label: 'Ruido elevado (>85 dB)' },
        { key: 'vibraciones', label: 'Exposición a vibraciones' },
        { key: 'iluminacion_inadecuada', label: 'Iluminación inadecuada' },
        { key: 'humedad_extrema', label: 'Humedad relativa extrema' },
        { key: 'presion_atmosferica', label: 'Presión atmosférica anormal' },
        { key: 'sustancias_toxicas', label: 'Exposición a sustancias tóxicas' },
        { key: 'radiacion', label: 'Exposición a radiación' },
        { key: 'biologicos', label: 'Agentes biológicos' },
        { key: 'electricidad', label: 'Riesgo eléctrico' }
      ]
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },
    condicionesSeleccionadas() {
      return this.condicionesEntorno.filter(c => this.form[c.key])
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
.seccion-factores-laborales-ergonomia {
  background: #f8f9fa;
}

.card {
  border: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.btn-outline-secondary {
  border-color: #ced4da;
  color: #495057;
  border-radius: 2rem;
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.btn-outline-secondary:hover {
  border-color: #adb5bd;
}

.btn-outline-secondary.active {
  background-color: #2E8B57;
  border-color: #2E8B57;
  color: white;
}

.form-control {
  border-radius: 4px;
  border: 1px solid #ced4da;
  font-size: 14px;
  padding: 10px 12px;
}

.form-check-input {
  margin-top: 0.25rem;
  margin-left: -1.25rem;
}

.label {
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  color: #2c3e50;
}

.text-muted {
  color: #6c757d !important;
}
</style>