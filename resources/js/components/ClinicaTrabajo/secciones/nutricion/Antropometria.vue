<template>
  <div class="seccion-antropometria-nutricion">
    <!-- SECTION: ANTROPOMETRÍA Y COMPOSICIÓN CORPORAL -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-ruler mr-2"></i> ANTROPOMETRÍA Y COMPOSICIÓN CORPORAL
        </h5>
      </div>
      <div class="card-body">
        <!-- MEDIDAS PRINCIPALES CON AUTO-CÁLCULO IMC -->
        <div class="row mb-4">
          <!-- ESTATURA -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Estatura (m) <span class="text-danger">*</span></label>
            <input
              type="number"
              class="form-control"
              v-model.number="form.estatura_m"
              @input="updateField('estatura_m', $event.target.value)"
              step="0.01"
              min="0.5"
              max="2.5"
              placeholder="Ej: 1.70"
              style="height: 38px;"
            >
          </div>

          <!-- PESO -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Peso (kg) <span class="text-danger">*</span></label>
            <input
              type="number"
              class="form-control"
              v-model.number="form.peso_kg"
              @input="updateField('peso_kg', $event.target.value)"
              step="0.1"
              min="20"
              max="300"
              placeholder="Ej: 75.5"
              style="height: 38px;"
            >
          </div>

          <!-- PESO IDEAL -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Peso Ideal (kg)</label>
            <input
              type="number"
              class="form-control"
              v-model.number="form.peso_ideal_kg"
              @input="updateField('peso_ideal_kg', $event.target.value)"
              step="0.1"
              min="20"
              max="200"
              placeholder="Opcional"
              style="height: 38px;"
            >
          </div>

          <!-- IMC CALCULADO -->
          <div class="col-md-3 mb-3 d-flex align-items-end">
            <label class="font-weight-bold w-100 mb-1">IMC Calculado</label>
            <div v-if="imcCalculado" class="w-100 bg-light text-dark font-weight-bold text-lg p-2 border rounded text-center" style="height: 38px; display: flex; align-items: center; justify-content: center;">
              {{ imcCalculado }} ({{ imcClasificacionAuto }})
            </div>
            <div v-else class="w-100 bg-light text-muted p-2 border rounded text-center" style="height: 38px; display: flex; align-items: center; justify-content: center;">
              Complete estatura y peso
            </div>
          </div>
        </div>

        <!-- CIRCUNFERENCIAS E ÍNDICES -->
        <div class="row mb-4">
          <!-- CINTURA -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Cintura (cm)</label>
            <input
              type="number"
              class="form-control"
              v-model.number="form.circunferencia_cintura_cm"
              @input="updateField('circunferencia_cintura_cm', $event.target.value)"
              step="0.1"
              min="40"
              max="200"
              placeholder="Ej: 85"
              style="height: 38px;"
            >
          </div>

          <!-- CADERA -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Cadera (cm)</label>
            <input
              type="number"
              class="form-control"
              v-model.number="form.circunferencia_cadera_cm"
              @input="updateField('circunferencia_cadera_cm', $event.target.value)"
              step="0.1"
              min="40"
              max="200"
              placeholder="Ej: 95"
              style="height: 38px;"
            >
          </div>

          <!-- ICC (CINTURA/CADERA) -->
          <div class="col-md-3 mb-3 d-flex align-items-end">
            <label class="font-weight-bold w-100 mb-1">ICC (Cintura/Cadera)</label>
            <div v-if="iccCalculado" class="w-100 bg-light text-dark font-weight-bold text-lg p-2 border rounded text-center" style="height: 38px; display: flex; align-items: center; justify-content: center;">
              {{ iccCalculado }}
            </div>
            <div v-else class="w-100 bg-light text-muted p-2 border rounded text-center" style="height: 38px; display: flex; align-items: center; justify-content: center;">
              —
            </div>
          </div>

          <!-- BRAZO -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Brazo (cm)</label>
            <input
              type="number"
              class="form-control"
              v-model.number="form.circunferencia_brazo_cm"
              @input="updateField('circunferencia_brazo_cm', $event.target.value)"
              step="0.1"
              min="15"
              max="100"
              placeholder="Ej: 28"
              style="height: 38px;"
            >
          </div>
        </div>

        <!-- COMPOSICIÓN CORPORAL (BIOIMPEDANCIA) -->
        <div class="mt-4 pt-3 border-top border-top">
          <div class="card-header pb-0 pt-0">
            <h5 class="mb-0">
              <i class="fas fa-chart-pie mr-2"></i> COMPOSICIÓN CORPORAL (BIOIMPEDANCIA)
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <!-- GRASA CORPORAL -->
              <div class="col-md-4 mb-3">
                <label class="font-weight-bold">Grasa Corporal (%)</label>
                <input
                  type="number"
                  class="form-control"
                  v-model.number="form.grasa_corporal_pct"
                  @input="updateField('grasa_corporal_pct', $event.target.value)"
                  step="0.1"
                  min="0"
                  max="100"
                  placeholder="Ej: 25"
                  style="height: 38px;"
                >
              </div>

              <!-- GRASA VISCERAL -->
              <div class="col-md-4 mb-3">
                <label class="font-weight-bold">Grasa Visceral (%)</label>
                <input
                  type="number"
                  class="form-control"
                  v-model.number="form.grasa_visceral_pct"
                  @input="updateField('grasa_visceral_pct', $event.target.value)"
                  step="0.1"
                  min="0"
                  max="100"
                  placeholder="Ej: 8"
                  style="height: 38px;"
                >
              </div>

              <!-- MÚSCULO -->
              <div class="col-md-4 mb-3">
                <label class="font-weight-bold">Músculo (%)</label>
                <input
                  type="number"
                  class="form-control"
                  v-model.number="form.musculo_pct"
                  @input="updateField('musculo_pct', $event.target.value)"
                  step="0.1"
                  min="0"
                  max="100"
                  placeholder="Ej: 40"
                  style="height: 38px;"
                >
              </div>
            </div>
          </div>
        </div>

        <!-- RESUMEN CLASIFICACIÓN -->
        <div v-if="imcCalculado" class="mt-4 p-3 bg-light border border-left border-info">
          <h5 class="font-weight-bold text-info mb-3 flex items-center gap-2">
            <i class="fas fa-file-alt mr-2"></i> RESUMEN ANTROPOMÉTRICO
          </h5>
          <div class="row">
            <div class="col-md-4">
              <div class="d-flex justify-content-between">
                <span class="text-muted">IMC:</span>
                <span class="font-weight-bold">{{ imcCalculado }}</span>
              </div>
              <div class="d-flex justify-content-between mt-2">
                <span class="text-muted">Clasificación:</span>
                <span :class="getImcBadgeClass()" class="badge badge-pill">{{ imcClasificacionAuto }}</span>
              </div>
            </div>
            <div class="col-md-4">
              <div class="d-flex justify-content-between">
                <span class="text-muted">ICC:</span>
                <span class="font-weight-bold">{{ iccCalculado || '—' }}</span>
              </div>
              <div v-if="iccCalculado" class="d-flex justify-content-between mt-2">
                <span class="text-muted">Riesgo ICC:</span>
                <span class="font-weight-bold" :class="getIccRiskClass()">{{ iccRiesgo }}</span>
              </div>
            </div>
            <div class="col-md-4">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Riesgo Cardiometabólico:</span>
                <span class="font-weight-bold" :class="getRiesgoClass()">{{ riesgoCardiometabolico }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- TABLAS DE REFERENCIA -->
        <div class="mt-4 pt-3 border-top border-top">
          <div class="card-header pb-0 pt-0">
            <h5 class="mb-0">
              <i class="fas fa-table mr-2"></i> REFERENCIAS OMS / ATP III
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <!-- CLASIFICACIÓN IMC (OMS) -->
              <div class="col-md-6">
                <h6 class="font-weight-bold text-dark mb-2">Clasificación IMC (OMS)</h6>
                <ul class="list-unstyled text-sm">
                  <li><i class="fas fa-circle text-primary mr-1"></i> < 18.5: Bajo peso</li>
                  <li><i class="fas fa-circle text-success mr-1"></i> 18.5 - 24.9: Normal</li>
                  <li><i class="fas fa-circle text-warning mr-1"></i> 25.0 - 29.9: Sobrepeso</li>
                  <li><i class="fas fa-circle text-danger mr-1"></i> 30.0 - 34.9: Obesidad I</li>
                  <li><i class="fas fa-circle text-danger mr-1"></i> 35.0 - 39.9: Obesidad II</li>
                  <li><i class="fas fa-circle text-danger mr-1"></i> ≥ 40.0: Obesidad III</li>
                </ul>
              </div>

              <!-- ICC - RIESGO CARDIOVASCULAR (ATP III) -->
              <div class="col-md-6">
                <h6 class="font-weight-bold text-dark mb-2">ICC - Riesgo Cardiovascular (ATP III)</h6>
                <ul class="list-unstyled text-sm">
                  <li><i class="fas fa-circle text-info mr-1"></i> Hombres: > 0.90 = Riesgo ↑</li>
                  <li><i class="fas fa-circle text-info mr-1"></i> Mujeres: > 0.85 = Riesgo ↑</li>
                  <li><i class="fas fa-circle text-info mr-1"></i> Cintura H: > 102 cm = Riesgo ↑</li>
                  <li><i class="fas fa-circle text-info mr-1"></i> Cintura M: > 88 cm = Riesgo ↑</li>
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
  name: 'AntropometriaNutricion',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },
    imcCalculado() {
      if (this.form.peso_kg && this.form.estatura_m && this.form.estatura_m > 0) {
        return (this.form.peso_kg / (this.form.estatura_m ** 2)).toFixed(2)
      }
      return null
    },
    imcClasificacionAuto() {
      if (!this.imcCalculado) return ''
      const imc = parseFloat(this.imcCalculado)
      if (imc < 18.5) return 'Bajo peso'
      if (imc < 25) return 'Normal'
      if (imc < 30) return 'Sobrepeso'
      if (imc < 35) return 'Obesidad I'
      if (imc < 40) return 'Obesidad II'
      return 'Obesidad III'
    },
    getImcBadgeClass() {
      const imc = parseFloat(this.imcCalculado || 0)
      if (imc < 18.5) return 'badge-primary'      // Bajo peso
      if (imc < 25) return 'badge-success'       // Normal
      if (imc < 30) return 'badge-warning'       // Sobrepeso
      if (imc < 35) return 'badge-danger'        // Obesidad I
      if (imc < 40) return 'badge-danger'        // Obesidad II
      return 'badge-danger'                      // Obesidad III
    },
    iccCalculado() {
      if (this.form.circunferencia_cintura_cm && this.form.circunferencia_cadera_cm && this.form.circunferencia_cadera_cm > 0) {
        return (this.form.circunferencia_cintura_cm / this.form.circunferencia_cadera_cm).toFixed(2)
      }
      return null
    },
    iccRiesgo() {
      if (!this.iccCalculado) return ''
      const icc = parseFloat(this.iccCalculado)
      const genero = this.form.paciente?.genero || 'M'
      if (genero === 'F' || genero === 'Femenino') {
        return icc > 0.85 ? '⚠️ Riesgo ↑' : '✅ Normal'
      }
      return icc > 0.90 ? '⚠️ Riesgo ↑' : '✅ Normal'
    },
    getIccRiskClass() {
      const icc = parseFloat(this.iccCalculado || 0)
      const genero = this.form.paciente?.genero || 'M'
      if (genero === 'F' || genero === 'Femenino') {
        return icc > 0.85 ? 'text-danger' : 'text-success'
      }
      return icc > 0.90 ? 'text-danger' : 'text-success'
    },
    riesgoCardiometabolico() {
      const riesgos = []
      const imc = parseFloat(this.imcCalculado || 0)
      const cintura = this.form.circunferencia_cintura_cm
      const genero = this.form.paciente?.genero || 'M'

      if (imc >= 30) riesgos.push('Obesidad')
      if (cintura && ((genero === 'F' || genero === 'Femenino') ? cintura > 88 : cintura > 102)) {
        riesgos.push('Cintura ↑')
      }
      if (this.iccCalculado && ((genero === 'F' || genero === 'Femenino') ? parseFloat(this.iccCalculado) > 0.85 : parseFloat(this.iccCalculado) > 0.90)) {
        riesgos.push('ICC ↑')
      }

      return riesgos.length > 0 ? riesgos.join(', ') : 'Bajo'
    },
    getRiesgoClass() {
      const riesgos = this.riesgoCardiometabolico
      return riesgos === 'Bajo' ? 'text-success' : 'text-danger'
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
.seccion-antropometria-nutricion {
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

.badge {
  font-size: 0.875rem;
  padding: 0.5rem 1rem;
  border-radius: 2rem;
}

.badge-primary {
  background-color: #5F6E7E;
  color: white;
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
</style>