<template>
  <div class="seccion-datos-generales-espirometria">
    <!-- SECTION: DATOS GENERALES Y ANTECEDENTES RESPIRATORIOS -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #2C7BE5 0%, #1A56C1 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-user-injured mr-2"></i> DATOS GENERALES Y ANTECEDENTES RESPIRATORIOS
        </h5>
      </div>
      <div class="card-body">
        <!-- FILA 1: DATOS DEL PACIENTE -->
        <div class="row mb-4">
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Nombre del Paciente <span class="text-danger">*</span></label>
            <input type="text" v-model="form.nombre" class="form-control" placeholder="Apellido, Nombre" style="height: 38px;" required />
          </div>
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold">ID / Número de Empleado</label>
            <input type="text" v-model="form.id_empleado" class="form-control" placeholder="Ej: EMP-001" style="height: 38px;" />
          </div>
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Empresa</label>
            <input type="text" v-model="form.empresa" class="form-control" placeholder="Nombre de la empresa" style="height: 38px;" />
          </div>
        </div>

        <!-- FILA 2: DATOS DEMOGRÁFICOS CON EDAD AUTO-CALCULADA -->
        <div class="row mb-4">
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Fecha de Nacimiento <span class="text-danger">*</span></label>
            <input type="date" v-model="form.fecha_nacimiento" @change="calcularEdad" class="form-control" :max="fechaHoy" style="height: 38px;" required />
          </div>
          <div class="col-md-3 mb-3 d-flex align-items-end">
            <label class="font-weight-bold w-100 mb-1">Edad (años)</label>
            <div v-if="form.edad !== null && form.edad !== undefined" class="w-100 bg-light text-dark font-weight-bold text-lg p-2 border rounded text-center" style="height: 38px; display: flex; align-items: center; justify-content: center;">
              {{ form.edad }}
            </div>
            <div v-else class="w-100 bg-light text-muted p-2 border rounded text-center" style="height: 38px; display: flex; align-items: center; justify-content: center;">
              Complete fecha de nacimiento
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Sexo <span class="text-danger">*</span></label>
            <select v-model="form.sexo" class="form-control" style="height: 38px;" required>
              <option value="">Seleccionar...</option>
              <option value="Masculino">Masculino</option>
              <option value="Femenino">Femenino</option>
            </select>
          </div>
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Origen Étnico</label>
            <select v-model="form.origen_etnico" class="form-control" style="height: 38px;">
              <option value="Hispano">Hispano</option>
              <option value="Caucásico">Caucásico</option>
              <option value="Afrodescendiente">Afrodescendiente</option>
              <option value="Otro">Otro</option>
            </select>
          </div>
        </div>

        <!-- FILA 3: ANTROPOMETRÍA CON IMC AUTO-CALCULADO (ESTILO NUTRICIÓN) -->
        <div class="row mb-4">
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Altura (cm) <span class="text-danger">*</span></label>
            <input type="number" v-model.number="form.altura_cm" @input="calcularIMC" class="form-control" step="0.1" min="50" max="250" placeholder="Ej: 171" style="height: 38px;" required />
          </div>
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Peso (kg) <span class="text-danger">*</span></label>
            <input type="number" v-model.number="form.peso_kg" @input="calcularIMC" class="form-control" step="0.1" min="20" max="300" placeholder="Ej: 81" style="height: 38px;" required />
          </div>
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Peso Ideal (kg)</label>
            <input type="number" v-model.number="form.peso_ideal_kg" class="form-control" step="0.1" min="20" max="200" placeholder="Opcional (Devine/Robinson)" style="height: 38px;" />
          </div>
          <div class="col-md-3 mb-3 d-flex align-items-end">
            <label class="font-weight-bold w-100 mb-1">IMC Calculado</label>
            <div v-if="imcCalculado" class="w-100 bg-light text-dark font-weight-bold text-lg p-2 border rounded text-center" style="height: 38px; display: flex; align-items: center; justify-content: center;">
              {{ imcCalculado }} <span class="ml-2" :class="getImcBadgeClass()">{{ imcClasificacionAuto }}</span>
            </div>
            <div v-else class="w-100 bg-light text-muted p-2 border rounded text-center" style="height: 38px; display: flex; align-items: center; justify-content: center;">
              Complete altura y peso
            </div>
          </div>
        </div>

        <!-- RESUMEN ANTROPOMÉTRICO (ESTILO NUTRICIÓN) -->
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
                <span class="text-muted">Clasificación OMS:</span>
                <span :class="getImcBadgeClass()" class="badge badge-pill">{{ imcClasificacionAuto }}</span>
              </div>
            </div>
            <div class="col-md-4">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Peso Ideal:</span>
                <span class="font-weight-bold">{{ form.peso_ideal_kg || '—' }} kg</span>
              </div>
              <div v-if="form.peso_ideal_kg && form.peso_kg" class="d-flex justify-content-between mt-2">
                <span class="text-muted">% Peso Ideal:</span>
                <span class="font-weight-bold" :class="getPorcentajePesoIdealClass()">{{ porcentajePesoIdeal }}%</span>
              </div>
            </div>
            <div class="col-md-4">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Superficie Corporal:</span>
                <span class="font-weight-bold">{{ superficieCorporal || '—' }} m²</span>
              </div>
            </div>
          </div>
        </div>

        <!-- FILA 4: ANTECEDENTES RESPIRATORIOS Y HÁBITOS -->
        <div class="border-top pt-4 mt-4">
          <h5 class="font-weight-bold text-dark mb-3">
            <i class="fas fa-lungs mr-2 text-primary"></i> ANTECEDENTES RESPIRATORIOS Y HÁBITOS TOXICOLÓGICOS
          </h5>
          <div class="row mb-4">
            <!-- TABAQUISMO -->
            <div class="col-md-6 mb-3">
              <div class="border rounded p-3 bg-light">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" v-model="form.fumador" id="fumador" @change="onFumadorChange" />
                  <label class="form-check-label font-weight-bold text-dark" for="fumador">
                    <i class="fas fa-smoking mr-2 text-warning"></i> Tabaquismo
                  </label>
                </div>
                <div v-if="form.fumador" class="mt-3 row">
                  <div class="col-md-6 mb-2">
                    <label class="font-weight-bold small">Cigarrillos/día</label>
                    <input type="number" v-model.number="form.cigarrillos_dia" class="form-control form-control-sm" min="0" max="100" placeholder="Ej: 20" style="height: 34px;" />
                  </div>
                  <div class="col-md-6 mb-2">
                    <label class="font-weight-bold small">Años de hábito</label>
                    <input type="number" v-model.number="form.anos_fumador" class="form-control form-control-sm" min="0" max="80" placeholder="Ej: 15" style="height: 34px;" />
                  </div>
                  <div class="col-md-12" v-if="packYears !== null">
                    <label class="font-weight-bold small">Pack-Years Calculado</label>
                    <div class="bg-white border p-2 text-center font-weight-bold text-primary" style="height: 34px; display: flex; align-items: center; justify-content: center;">
                      {{ packYears.toFixed(1) }} {{ packYears >= 20 ? '<span class="text-danger ml-2 small">(⚠️ Alto riesgo)</span>' : '<span class="text-success ml-2 small">(Bajo riesgo)</span>' }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- OTROS ANTECEDENTES -->
            <div class="col-md-6 mb-3">
              <div class="border rounded p-3 bg-light">
                <div class="row">
                  <div class="col-md-6 mb-2">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" v-model="form.asma" id="asma" />
                      <label class="form-check-label font-weight-bold" for="asma">
                        <i class="fas fa-wind mr-1 text-info"></i> Asma
                      </label>
                    </div>
                  </div>
                  <div class="col-md-6 mb-2">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" v-model="form.epoc" id="epoc" />
                      <label class="form-check-label font-weight-bold" for="epoc">
                        <i class="fas fa-lungs-virus mr-1 text-danger"></i> EPOC
                      </label>
                    </div>
                  </div>
                  <div class="col-md-6 mb-2">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" v-model="form.antecedente_covid" id="covid" />
                      <label class="form-check-label font-weight-bold" for="covid">
                        <i class="fas fa-virus mr-1 text-purple"></i> COVID-19
                      </label>
                    </div>
                  </div>
                  <div class="col-md-6 mb-2">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" v-model="form.rinitis_alergica" id="rinitis" />
                      <label class="form-check-label font-weight-bold" for="rinitis">
                        <i class="fas fa-allergies mr-1 text-success"></i> Rinitis Alérgica
                      </label>
                    </div>
                  </div>
                  <div class="col-md-6 mb-2">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" v-model="form.tuberculosis" id="tb" />
                      <label class="form-check-label font-weight-bold" for="tb">
                        <i class="fas fa-bacteria mr-1 text-secondary"></i> Tuberculosis
                      </label>
                    </div>
                  </div>
                  <div class="col-md-6 mb-2">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" v-model="form.neumonia_previa" id="neumonia" />
                      <label class="form-check-label font-weight-bold" for="neumonia">
                        <i class="fas fa-hospital mr-1 text-primary"></i> Neumonía Previa
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- RESUMEN DE ANTECEDENTES POSITIVOS -->
          <div v-if="antecedentesPositivos.length > 0" class="mt-3 p-3 bg-light border border-left border-danger">
            <h6 class="font-weight-bold text-danger mb-2 flex items-center gap-2">
              <i class="fas fa-exclamation-triangle mr-2"></i> Antecedentes Respiratorios Positivos ({{ antecedentesPositivos.length }})
            </h6>
            <div class="d-flex flex-wrap gap-2">
              <span v-for="a in antecedentesPositivos" :key="a.key" class="badge badge-pill badge-danger p-2">
                {{ a.label }}
              </span>
            </div>
          </div>

          <!-- COMENTARIOS -->
          <div class="mt-4">
            <label class="font-weight-bold">Comentarios / Observaciones Clínicas</label>
            <textarea v-model="form.comentarios" rows="3" class="form-control" placeholder="Ej: Niega antecedente de COVID. Refiere tos ocasional matutina. Exposición laboral a polvos orgánicos..." style="height: 96px;"></textarea>
          </div>
        </div>

        <!-- TABLAS DE REFERENCIA IMC (ESTILO NUTRICIÓN) -->
        <div class="mt-4 pt-3 border-top">
          <div class="card-header pb-0 pt-0">
            <h5 class="mb-0">
              <i class="fas fa-table mr-2"></i> REFERENCIAS IMC (OMS) Y SUPERFICIE CORPORAL (MOSTELLER)
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
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
              <div class="col-md-6">
                <h6 class="font-weight-bold text-dark mb-2">Fórmulas de Peso Ideal</h6>
                <ul class="list-unstyled text-sm">
                  <li><i class="fas fa-circle text-info mr-1"></i> <strong>Devine (Hombres):</strong> 50 + 2.3 × (pulgadas - 60)</li>
                  <li><i class="fas fa-circle text-info mr-1"></i> <strong>Devine (Mujeres):</strong> 45.5 + 2.3 × (pulgadas - 60)</li>
                  <li><i class="fas fa-circle text-info mr-1"></i> <strong>Robinson (Hombres):</strong> 52 + 1.9 × (pulgadas - 60)</li>
                  <li><i class="fas fa-circle text-info mr-1"></i> <strong>Robinson (Mujeres):</strong> 49 + 1.7 × (pulgadas - 60)</li>
                  <li><i class="fas fa-circle text-info mr-1"></i> <strong>Superficie Corporal (Mosteller):</strong> √(altura_cm × peso_kg / 3600)</li>
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
  name: 'DatosGeneralesEspirometria',
  props: {
    modelValue: { type: Object, required: true }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      fechaHoy: new Date().toISOString().split('T')[0]
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },
    imcCalculado() {
      if (this.form.peso_kg && this.form.altura_cm && this.form.altura_cm > 0) {
        const alturaM = this.form.altura_cm / 100
        return (this.form.peso_kg / (alturaM * alturaM)).toFixed(2)
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
      if (imc < 18.5) return 'badge-primary'
      if (imc < 25) return 'badge-success'
      if (imc < 30) return 'badge-warning'
      if (imc < 35) return 'badge-danger'
      if (imc < 40) return 'badge-danger'
      return 'badge-danger'
    },
    packYears() {
      if (this.form.fumador && this.form.cigarrillos_dia && this.form.anos_fumador) {
        return (this.form.cigarrillos_dia / 20) * this.form.anos_fumador
      }
      return null
    },
    porcentajePesoIdeal() {
      if (this.form.peso_kg && this.form.peso_ideal_kg && this.form.peso_ideal_kg > 0) {
        return ((this.form.peso_kg / this.form.peso_ideal_kg) * 100).toFixed(1)
      }
      return null
    },
    getPorcentajePesoIdealClass() {
      const pct = parseFloat(this.porcentajePesoIdeal || 0)
      if (pct < 90) return 'text-danger'
      if (pct <= 110) return 'text-success'
      if (pct <= 120) return 'text-warning'
      return 'text-danger'
    },
    superficieCorporal() {
      if (this.form.altura_cm && this.form.peso_kg) {
        return Math.sqrt((this.form.altura_cm * this.form.peso_kg) / 3600).toFixed(2)
      }
      return null
    },
    antecedentesPositivos() {
      const checks = [
        { key: 'asma', label: 'Asma' },
        { key: 'epoc', label: 'EPOC' },
        { key: 'antecedente_covid', label: 'COVID-19' },
        { key: 'rinitis_alergica', label: 'Rinitis Alérgica' },
        { key: 'tuberculosis', label: 'Tuberculosis' },
        { key: 'neumonia_previa', label: 'Neumonía Previa' },
        { key: 'fumador', label: 'Tabaquismo' }
      ]
      return checks.filter(a => this.form[a.key])
    }
  },
  methods: {
    calcularEdad() {
      if (!this.form.fecha_nacimiento) return
      const hoy = new Date()
      const nacimiento = new Date(this.form.fecha_nacimiento)
      let edad = hoy.getFullYear() - nacimiento.getFullYear()
      const m = hoy.getMonth() - nacimiento.getMonth()
      if (m < 0 || (m === 0 && hoy.getDate() < nacimiento.getDate())) edad--
      this.$emit('update:modelValue', { ...this.form, edad })
    },
    calcularIMC() {
      // El computed imcCalculado se actualiza reactivamente
      // Solo emitimos si hay cambios en altura/peso para trigger reactividad
      this.$emit('update:modelValue', { ...this.form })
    },
    onFumadorChange() {
      if (!this.form.fumador) {
        this.$emit('update:modelValue', { ...this.form, cigarrillos_dia: 0, anos_fumador: 0 })
      }
    }
  }
}
</script>

<style scoped>
.seccion-datos-generales-espirometria {
  background: #f8f9fa;
}

.card {
  border: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.card-header {
  background: linear-gradient(135deg, #2C7BE5 0%, #1A56C1 100%) !important;
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
  border-color: #2C7BE5;
  box-shadow: 0 0 0 0.2rem rgba(44, 123, 229, 0.25);
}

.form-control-sm {
  font-size: 0.875rem;
  padding: 6px 10px;
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

.badge {
  font-size: 0.875rem;
  padding: 0.5rem 1rem;
  border-radius: 2rem;
}

.badge-primary {
  background-color: #2C7BE5;
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

.text-primary { color: #2C7BE5 !important; }
.text-info { color: #17a2b8 !important; }
.text-success { color: #28a745 !important; }
.text-warning { color: #ffc107 !important; }
.text-danger { color: #dc3545 !important; }
.text-purple { color: #6f42c1 !important; }
.text-secondary { color: #6c757d !important; }

.text-muted { color: #6c757d !important; }
.font-weight-bold { font-weight: 600 !important; }
.bg-light { background-color: #f8f9fa !important; }
.bg-white { background-color: #fff !important; }
.bg-success { background-color: #28a745 !important; }
.bg-warning { background-color: #ffc107 !important; }
.bg-danger { background-color: #dc3545 !important; }
.bg-info { background-color: #17a2b8 !important; }

.border { border: 1px solid #dee2e6 !important; }
.border-top { border-top: 1px solid #dee2e6 !important; }
.border-left { border-left: 4px solid !important; }
.border-info { border-color: #17a2b8 !important; }
.border-success { border-color: #28a745 !important; }
.border-danger { border-color: #dc3545 !important; }
.border-warning { border-color: #ffc107 !important; }
.border-secondary { border-color: #6c757d !important; }

.rounded { border-radius: 0.25rem !important; }
.p-2 { padding: 0.5rem !important; }
.p-3 { padding: 1rem !important; }
.mt-2 { margin-top: 0.5rem !important; }
.mt-3 { margin-top: 1rem !important; }
.mt-4 { margin-top: 1.5rem !important; }
.mb-2 { margin-bottom: 0.5rem !important; }
.mb-3 { margin-bottom: 1rem !important; }
.mb-4 { margin-bottom: 1.5rem !important; }
.ml-2 { margin-left: 0.5rem !important; }

.d-flex { display: flex !important; }
.flex-wrap { flex-wrap: wrap !important; }
.justify-content-between { justify-content: space-between !important; }
.align-items-center { align-items: center !important; }
.align-items-end { align-items: flex-end !important; }
.text-center { text-align: center !important; }
.w-100 { width: 100% !important; }

.text-lg { font-size: 1.125rem !important; }
.text-sm { font-size: 0.875rem !important; }
.small { font-size: 0.875rem !important; }

.position-static { position: static !important; }

.shadow-sm { box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075) !important; }

.transition { transition: all 0.2s ease-in-out !important; }
.transition-all { transition: all 0.2s !important; }

.hover\:bg-light:hover { background-color: #f8f9fa !important; }
.cursor-pointer { cursor: pointer !important; }

.rounded-pill { border-radius: 50rem !important; }

.list-unstyled { padding-left: 0; list-style: none; }
</style>