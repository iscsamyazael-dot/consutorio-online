<template>
  <div class="seccion-cirugias-medicamentos-ergonomia">
    <!-- SECTION: CIRUGÍAS Y MEDICAMENTOS -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #2E8B57 0%, #228B22 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-pills mr-2"></i> CIRUGÍAS Y MEDICAMENTOS
        </h5>
      </div>
      <div class="card-body">
        <p class="text-muted mb-4">
          Indique si el paciente ha tenido cirugías o consume medicamentos de forma regular.
        </p>

        <!-- Fila 1: Cirugías y Medicamentos -->
        <div class="row mb-4">
          <div class="col-md-6">
            <label class="font-weight-bold d-block mb-2">¿Ha tenido cirugías?</label>
            <div class="btn-group-toggle" data-toggle="buttons">
              <label class="btn btn-outline-secondary btn-sm m-1 rounded-pill"
                     :class="[form.cirugias === opcion.value ? 'active' : '']"
                     v-for="opcion in opcionesSI_NO"
                     :key="opcion.value">
                <input type="radio"
                       name="cirugias"
                       :value="opcion.value"
                       :checked="form.cirugias === opcion.value"
                       @change="updateField('cirugias', opcion.value)"
                       class="position-static">
                {{ opcion.label }}
              </label>
            </div>
          </div>
          <div class="col-md-6">
            <label class="font-weight-bold d-block mb-2">¿Consume medicamentos regularmente?</label>
            <div class="btn-group-toggle" data-toggle="buttons">
              <label class="btn btn-outline-secondary btn-sm m-1 rounded-pill"
                     :class="[form.consumo_medicamentos === opcion.value ? 'active' : '']"
                     v-for="opcion in opcionesSI_NO"
                     :key="opcion.value">
                <input type="radio"
                       name="consumo_medicamentos"
                       :value="opcion.value"
                       :checked="form.consumo_medicamentos === opcion.value"
                       @change="updateField('consumo_medicamentos', opcion.value)"
                       class="position-static">
                {{ opcion.label }}
              </label>
            </div>
          </div>
        </div>

        <!-- Fila 2: Detalles Cirugías y Medicamentos -->
        <div class="row mb-4">
          <!-- DETALLE CIRUGÍAS -->
          <div v-if="form.cirugias === 'si'" class="col-md-6">
            <label class="font-weight-bold d-block mb-2">Especifique cirugías:</label>
            <input type="text"
                   class="form-control"
                   v-model="form.cirugias_especifique"
                   placeholder="Ej: Apendicectomía, Cesárea, etc."
            />
          </div>
          <div v-if="form.cirugias === 'si'" class="col-md-6">
            <!-- Espacio vacío para alineación -->
            <div v-if="!form.cirugias_especifique" class="text-muted small mt-2">
              Por favor especifique las cirugías
            </div>
          </div>
          <!-- DETALLE MEDICAMENTOS -->
          <div v-if="form.consumo_medicamentos === 'si'" class="col-md-6">
            <label class="font-weight-bold d-block mb-2">Especifique medicamentos:</label>
            <input type="text"
                   class="form-control"
                   v-model="form.medicamentos_especifique"
                   placeholder="Ej: Antiinflamatorios, Antihipertensivos, etc."
            />
          </div>
          <div v-if="form.consumo_medicamentos === 'si'" class="col-md-6">
            <!-- Espacio vacío para alineación -->
            <div v-if="!form.medicamentos_especifique" class="text-muted small mt-2">
              Por favor especifique los medicamentos
            </div>
          </div>
        </div>

        <!-- Fila 3: Alergias y Detalle Alergias -->
        <div class="row mb-4">
          <div class="col-md-6">
            <label class="font-weight-bold d-block mb-2">¿Tiene alergias?</label>
            <div class="btn-group-toggle" data-toggle="buttons">
              <label class="btn btn-outline-secondary btn-sm m-1 rounded-pill"
                     :class="[form.alergias === opcion.value ? 'active' : '']"
                     v-for="opcion in opcionesSI_NO"
                     :key="opcion.value">
                <input type="radio"
                       name="alergias"
                       :value="opcion.value"
                       :checked="form.alergias === opcion.value"
                       @change="updateField('alergias', opcion.value)"
                       class="position-static">
                {{ opcion.label }}
              </label>
            </div>
          </div>
          <div class="col-md-6">
            <!-- Espacio para alineación -->
          </div>
        </div>

        <div class="row mb-4">
          <!-- DETALLE ALERGIAS -->
          <div v-if="form.alergias === 'si'" class="col-md-6">
            <label class="font-weight-bold d-block mb-2">Especifique alergias:</label>
            <input type="text"
                   class="form-control"
                   v-model="form.alergias_especifique"
                   placeholder="Ej: Penicilina, Látex, Polen, etc."
            />
          </div>
          <div v-if="form.alergias === 'si'" class="col-md-6">
            <!-- Espacio vacío para alineación -->
            <div v-if="!form.alergias_especifique" class="text-muted small mt-2">
              Por favor especifique las alergias
            </div>
          </div>
        </div>

        <!-- RESUMEN -->
        <div v-if="[form.cirugias, form.consumo_medicamentos, form.alergias].includes('si')" class="mt-4 p-3 bg-light border border-left border-success">
          <h5 class="font-weight-bold text-success mb-3 flex items-center gap-2">
            <i class="fas fa-info-circle mr-2"></i>
            Información Relevante
          </h5>
          <div class="d-flex flex-wrap gap-2">
            <span v-if="form.cirugias === 'si'" class="badge badge-pill badge-success p-2">
              Cirugías: {{ form.cirugias_especifique || 'No especificada' }}
            </span>
            <span v-if="form.consumo_medicamentos === 'si'" class="badge badge-pill badge-success p-2">
              Medicamentos: {{ form.medicamentos_especifique || 'No especificado' }}
            </span>
            <span v-if="form.alergias === 'si'" class="badge badge-pill badge-success p-2">
              Alergias: {{ form.alergias_especifique || 'No especificada' }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CirugiasMedicamentosErgonomia',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
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
.seccion-cirugias-medicamentos-ergonomia {
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

.form-control {
  border-radius: 4px;
  border: 1px solid #ced4da;
  font-size: 14px;
  padding: 10px 12px;
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