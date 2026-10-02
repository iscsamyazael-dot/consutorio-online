<template>
  <div class="seccion-antecedentes-medicos">
    <!-- SECTION: ANTECEDENTES MÉDICOS PERSONALES PATOLÓGICOS -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-heartbeat mr-2"></i> ANTECEDENTES MÉDICOS PERSONALES PATOLÓGICOS
        </h5>
      </div>
      <div class="card-body">
        <!-- ENFERMEDADES CRÓNICAS -->
        <h6 class="font-weight-bold text-dark mb-3">Enfermedades crónicas</h6>
        <div class="row mb-4">
          <div v-for="antecedente in enfermedadesCronicas"
               :key="antecedente.key"
               class="col-md-3 mb-3">
            <div class="border rounded p-2 hover:bg-light transition">
              <label class="font-weight-bold d-block mb-1">
                {{ antecedente.label }}
              </label>
              <div class="btn-group-toggle" data-toggle="buttons">
                <label v-for="opcion in opcionesSI_NO"
                       :key="opcion.value"
                       class="btn btn-outline-secondary btn-sm m-1 rounded-pill"
                       :class="[form[antecedente.key] === opcion.value ? 'active' : '']">
                  <input type="radio"
                         :name="antecedente.key"
                         :value="opcion.value"
                         :checked="form[antecedente.key] === opcion.value"
                         @change="updateField(antecedente.key, opcion.value)"
                         class="position-static">
                  {{ opcion.label }}
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- OTROS SISTEMAS -->
        <h6 class="font-weight-bold text-dark mb-3 mt-4">Otros Sistemas</h6>
        <div class="row mb-4">
          <div v-for="antecedente in otrosSistemas"
               :key="antecedente.key"
               class="col-md-4 mb-3">
            <div class="border rounded p-2 hover:bg-light transition">
              <label class="font-weight-bold d-block mb-1">
                {{ antecedente.label }}
              </label>
              <div class="btn-group-toggle" data-toggle="buttons">
                <label v-for="opcion in opcionesSI_NO"
                       :key="opcion.value"
                       class="btn btn-outline-secondary btn-sm m-1 rounded-pill"
                       :class="[form[antecedente.key] === opcion.value ? 'active' : '']">
                  <input type="radio"
                         :name="antecedente.key"
                         :value="opcion.value"
                         :checked="form[antecedente.key] === opcion.value"
                         @change="updateField(antecedente.key, opcion.value)"
                         class="position-static">
                  {{ opcion.label }}
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- GINECO-OBSTÉTRICOS -->
        <div v-if="esMujer" class="mt-4 pt-3 border-top border-top">
          <div class="card-header pb-0 pt-0">
            <h5 class="mb-0">
              <i class="fas fa-venus mr-2"></i> ANTECEDENTES GINECO-OBSTÉTRICOS
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <!-- MENARCA -->
              <div class="col-md-3 mb-3">
                <label class="font-weight-bold">Menarca (años)</label>
                <input
                  type="number"
                  class="form-control"
                  :value="form.menarca_anos"
                  @input="updateField('menarca_anos', $event.target.value)"
                  min="8"
                  max="20"
                  placeholder="Ej: 12"
                  style="height: 38px;"
                >
              </div>

              <!-- CICLOS -->
              <div class="col-md-3 mb-3">
                <label class="font-weight-bold">Ciclos</label>
                <select
                  class="form-control"
                  :value="form.ciclos"
                  @change="updateField('ciclos', $event.target.value)"
                  style="height: 38px;"
                >
                  <option value="">Seleccionar...</option>
                  <option value="regular">Regular</option>
                  <option value="irregular">Irregular</option>
                  <option value="amenorrea">Amenorrea</option>
                </select>
              </div>

              <!-- F.U.M. -->
              <div class="col-md-3 mb-3">
                <label class="font-weight-bold">F.U.M. (Fecha Última Menstruación)</label>
                <input
                  type="date"
                  class="form-control"
                  :value="form.fum"
                  @input="updateField('fum', $event.target.value)"
                  style="height: 38px;"
                />
              </div>

              <!-- MÉTODO DE PLANIFICACIÓN FAMILIAR -->
              <div class="col-md-3 mb-3">
                <label class="font-weight-bold">Método de Planificación Familiar</label>
                <input
                  type="text"
                  class="form-control"
                  :value="form.metodo_planificacion"
                  @input="updateField('metodo_planificacion', $event.target.value)"
                  placeholder="Ej: DIU, pastillas, condón..."
                  style="height: 38px;"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- RESUMEN -->
        <div v-if="antecedentesPositivos.length > 0" class="mt-4 p-3 bg-light border border-left border-danger">
          <h5 class="font-weight-bold text-danger mb-3 flex items-center gap-2">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Antecedentes Médicos Positivos ({{ antecedentesPositivos.length }})
          </h5>
          <div class="d-flex flex-wrap gap-2">
            <span v-for="a in antecedentesPositivos"
                  :key="a.key"
                  class="badge badge-pill badge-danger p-2">
              {{ a.label }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AntecedentesMedicos',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      enfermedadesCronicas: [
        { key: 'am_obesidad', label: 'Obesidad' },
        { key: 'am_diabetes', label: 'Diabetes' },
        { key: 'am_hipertension', label: 'Hipertensión' },
        { key: 'am_dislipidemias', label: 'Dislipidemias' }
      ],
      otrosSistemas: [
        { key: 'am_cardiovasculares', label: 'Cardio-vasculares' },
        { key: 'am_renales', label: 'Renales' },
        { key: 'am_hepaticos', label: 'Hepáticos' },
        { key: 'am_musculo_esqueleticos', label: 'Músculo-esqueléticos' },
        { key: 'am_urologicos', label: 'Urológicos' },
        { key: 'am_infecciosos', label: 'Infecciosos' }
      ],
      opcionesSI_NO: [
        { value: 'si', label: 'SÍ' },
        { value: 'no', label: 'NO' }
      ]
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },
    esMujer() {
      const genero = this.form.paciente?.genero
      return genero === 'F' || genero === 'Femenino'
    },
    antecedentesPositivos() {
      const todos = [...this.enfermedadesCronicas, ...this.otrosSistemas]
      return todos.filter(a => this.form[a.key] === 'si')
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
.seccion-antecedentes-medicos {
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
  background-color: #5F6E7E;
  border-color: #5F6E7E;
  color: white;
}

.badge-danger {
  background-color: #e74c3c;
  color: white;
  font-size: 0.875rem;
  padding: 0.5rem 1rem;
  border-radius: 2rem;
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