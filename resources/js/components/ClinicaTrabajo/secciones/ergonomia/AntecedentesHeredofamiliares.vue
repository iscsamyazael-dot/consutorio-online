<template>
  <div class="seccion-antecedentes-heredofamiliares-ergonomia">
    <!-- SECTION: ANTECEDENTES HEREDOFAMILIARES -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #2E8B57 0%, #228B22 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-user-md mr-2"></i> ANTECEDENTES HEREDOFAMILIARES
        </h5>
      </div>
      <div class="card-body">
        <p class="text-muted mb-4">
          Indique si algún familiar directo (padres, abuelos, hermanos) presenta las siguientes condiciones.
        </p>

        <!-- Fila 1: Condiciones principales -->
        <div class="row mb-4">
          <div v-for="(antecedente, index) in antecedentesHeredofamiliares.slice(0, 3)"
               :key="antecedente.key"
               class="col-md-4">
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

        <!-- Fila 2: Condiciones restantes -->
        <div class="row mb-4">
          <div v-for="(antecedente, index) in antecedentesHeredofamiliares.slice(3, 6)"
               :key="antecedente.key"
               class="col-md-4">
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
          <div v-if="antecedentesHeredofamiliares.length > 6" :key="'otros'" class="col-md-4">
            <div class="border rounded p-3 hover:bg-light transition">
              <label class="font-weight-bold d-block mb-2">
                Otros
              </label>
              <div class="btn-group-toggle" data-toggle="buttons">
                <label v-for="opcion in opcionesSI_NO"
                       :key="opcion.value"
                       class="btn btn-outline-secondary btn-sm m-1 rounded-pill"
                       :class="[form.hf_otros === opcion.value ? 'active' : '']">
                  <input type="radio"
                         name="hf_otros"
                         :value="opcion.value"
                         :checked="form.hf_otros === opcion.value"
                         @change="updateField('hf_otros', opcion.value)"
                         class="position-static">
                  {{ opcion.label }}
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- RESUMEN DE ANTECEDENTES POSITIVOS -->
        <div v-if="antecedentesPositivos.length > 0" class="mt-4 p-3 bg-light border border-left border-success">
          <h5 class="font-weight-bold text-success mb-3 flex items-center gap-2">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Antecedentes Heredofamiliares Positivos ({{ antecedentesPositivos.length }})
          </h5>
          <div class="d-flex flex-wrap gap-2">
            <span v-for="a in antecedentesPositivos"
                  :key="a.key"
                  class="badge badge-pill badge-success p-2">
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
  name: 'AntecedentesHeredofamiliaresErgonomia',
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
.seccion-antecedentes-heredofamiliares-ergonomia {
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

.badge-success {
  background-color: #2E8B57;
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