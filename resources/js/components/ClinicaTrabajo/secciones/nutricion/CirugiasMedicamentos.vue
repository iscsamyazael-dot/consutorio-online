<template>
  <div class="seccion-cirugias-medicamentos">
    <!-- SECTION: CIRUGÍAS Y CONSUMO DE MEDICAMENTOS -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-scissors mr-2"></i> CIRUGÍAS Y CONSUMO DE MEDICAMENTOS
        </h5>
      </div>
      <div class="card-body">
        <!-- CIRUGÍAS -->
        <div class="mb-4">
          <label class="font-weight-bold">¿Ha sido sometido a cirugías? <span class="text-danger">*</span></label>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label v-for="opcion in opcionesSI_NO"
                     :key="opcion.value"
                     class="cursor-pointer d-block border rounded p-3 text-center hover:bg-light transition"
                     :class="form.cirugias === opcion.value ? 'border-success bg-success text-white' : 'border-secondary'">
                <input type="radio"
                       name="cirugias"
                       :value="opcion.value"
                       :checked="form.cirugias === opcion.value"
                       @change="updateField('cirugias', opcion.value)"
                       class="position-static">
                <div class="mt-2 font-weight-bold">{{ opcion.label }}</div>
              </label>
            </div>
          </div>

          <div v-if="form.cirugias === 'si'" class="mt-3">
            <label class="font-weight-bold">Especifique cirugías (tipo, fecha, complicaciones)</label>
            <textarea
              class="form-control"
              rows="3"
              v-model="form.cirugias_especifique"
              @input="updateField('cirugias_especifique', $event.target.value)"
              placeholder="Ej: Apendicectomía en 2015, sin complicaciones..."
              style="height: 96px;"
            ></textarea>
          </div>
        </div>

        <!-- CONSUMO DE MEDICAMENTOS -->
        <div class="mb-4" v-if="form.cirugias === 'si'">
          <label class="font-weight-bold">Consumo de Medicamentos</label>
          <p class="text-sm text-muted mb-2">Indique tipo de medicamento, dosis, tiempo de consumo, etc.</p>
          <textarea
            class="form-control"
            rows="4"
            v-model="form.consumo_medicamentos"
            @input="updateField('consumo_medicamentos', $event.target.value)"
            placeholder="Ej: Metformina 850mg cada 12hrs desde hace 2 años, Losartán 50mg diario..."
            style="height: 128px;"
          ></textarea>
        </div>

        <!-- ALERGIAS -->
        <div class="mb-4" v-if="form.cirugias === 'si'">
          <label class="font-weight-bold">Alergias Conocidas</label>
          <textarea
            class="form-control"
            rows="2"
            v-model="form.alergias"
            @input="updateField('alergias', $event.target.value)"
            placeholder="Ej: Penicilina, mariscos, látex..."
            style="height: 64px;"
          ></textarea>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CirugiasMedicamentos',
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
.seccion-cirugias-medicamentos {
  background: #f8f9fa;
}

.card {
  border: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.form-control {
  border-radius: 4px;
  border: 1px solid #ced4da;
  font-size: 14px;
  padding: 10px 12px;
}

.form-control:focus {
  border-color: #5F6E7E;
  box-shadow: 0 0 0 0.2rem rgba(95, 110, 126, 0.25);
}

.label {
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  color: #2c3e50;
}

.text-muted {
  color: #6c757d !important;
}

.cursor-pointer {
  cursor: pointer;
}

.border-success {
  background-color: rgba(40, 167, 69, 0.05);
}

.border-secondary {
  background-color: #f8f9fa;
}
</style>