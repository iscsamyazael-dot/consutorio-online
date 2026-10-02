<template>
  <div class="seccion-antecedentes-heredofamiliares">
    <!-- SECTION: ANTECEDENTES HEREDOFAMILIARES -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-user-md mr-2"></i> ANTECEDENTES HEREDOFAMILIARES
        </h5>
      </div>
      <div class="card-body">
        <p class="text-muted mb-4">
          Indique si algún familiar directo (padres, abuelos, hermanos) presenta las siguientes condiciones.
        </p>

        <div class="row">
          <!-- ITERAR SOBRE LOS ANTECEDENTES -->
          <div v-for="antecedente in antecedentesHeredofamiliares"
               :key="antecedente.key"
               class="col-md-4 mb-3">
            <div class="border rounded p-3 hover:bg-light transition">
              <label class="font-weight-bold d-block mb-2">
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

        <!-- RESUMEN DE ANTECEDENTES POSITIVOS -->
        <div v-if="antecedentesPositivos.length > 0" class="mt-4 p-3 bg-light border border-left border-primary">
          <h5 class="font-weight-bold text-primary mb-3 flex items-center gap-2">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Antecedentes Heredofamiliares Positivos ({{ antecedentesPositivos.length }})
          </h5>
          <div class="d-flex flex-wrap gap-2">
            <span v-for="a in antecedentesPositivos"
                  :key="a.key"
                  class="badge badge-pill badge-primary p-2">
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
  name: 'AntecedentesHeredofamiliares',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      antecedentesHeredofamiliares: [
        { key: 'hf_obesidad', label: 'Obesidad' },
        { key: 'hf_diabetes', label: 'Diabetes' },
        { key: 'hf_hipertension', label: 'Hipertensión' },
        { key: 'hf_cancer', label: 'Cáncer' },
        { key: 'hf_nefropatia', label: 'Nefropatía' },
        { key: 'hf_otros', label: 'Otros' }
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
    antecedentesPositivos() {
      return this.antecedentesHeredofamiliares.filter(a => this.form[a.key] === 'si')
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
.seccion-antecedentes-heredofamiliares {
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

.badge-primary {
  background-color: #5F6E7E;
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