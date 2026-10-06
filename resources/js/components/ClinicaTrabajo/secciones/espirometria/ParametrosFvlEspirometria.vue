<template>
  <div class="seccion-parametros-fvl-espirometria">
    <!-- SECTION: PARÁMETROS FVL -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #43A047 0%, #2E7D32 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-chart-line mr-2"></i> PARÁMETROS FVL (Forced Vital Capacity)
        </h5>
      </div>
      <div class="card-body">
        <!-- CONFIGURACIÓN DE REFERENCIA -->
        <div class="row mb-4">
          <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Ecuación de Referencia <span class="text-danger">*</span></label>
            <select v-model="form.referencia" class="form-control" style="height: 38px;">
              <option value="NHANES III">NHANES III (EE.UU.)</option>
              <option value="GLI-2012">GLI-2012 (Global)</option>
              <option value="Hankinson">Hankinson (EE.UU.)</option>
              <option value="Lloveras">Lloveras (Latinoamérica)</option>
              <option value="Pellegrino">Pellegrino (Europa)</option>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Interpretación Predicha *</label>
            <select v-model="form.interpretacion_predicha" class="form-control" style="height: 38px;">
              <option value="GOLD(2008)/Hardie">GOLD (2008) / Hardie</option>
              <option value="ATS/ERS 2021">ATS/ERS 2021</option>
              <option value="GOLD-2023">GOLD 2023 (Actualizada)</option>
            </select>
          </div>
        </div>

        <!-- TABLA DE MEJORES VALORES CON % PREVISTO (ESTILO DASHBOARD) -->
        <div class="table-responsive mb-6">
          <table class="table table-sm table-bordered table-hover align-middle">
            <thead class="bg-gray-100 text-gray-700">
              <tr>
                <th class="text-center">Parámetro</th>
                <th class="text-center">Unidad</th>
                <th class="text-center">Predicho</th>
                <th class="text-center">LLN</th>
                <th class="text-center bg-blue-50">Mejor Valor</th>
                <th class="text-center">% Predicho</th>
                <th class="text-center">Interpretación</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="param in parametrosPrincipales" :key="param.key">
                <td class="font-weight-bold">{{ param.label }}</td>
                <td class="text-center text-muted small">{{ param.unidad || '—' }}</td>
                <td class="text-center">
                  <input type="number" step="0.01" v-model.number="form[param.key + '_pred']"
                    class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center">
                  <input type="number" step="0.01" v-model.number="form[param.key + '_lln']"
                    class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center bg-blue-50">
                  <input type="number" step="0.01" v-model.number="form[param.key]"
                    class="form-control form-control-sm font-weight-bold text-blue-700 text-center"
                    style="height: 34px; background-color: #eff6ff; border-color: #bfdbfe;"
                    @input="calcularPorcentajes" />
                </td>
                <td class="text-center font-weight-bold" :class="getColorPorcentaje(param.key)">
                  {{ form[param.key + '_porcentaje_predicho'] || '—' }}%
                </td>
                <td class="text-center">
                  <span :class="getBadgeClass(param.key)" class="badge">{{ getInterpretacion(param.key) }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- CRITERIOS DE CALIDAD Y REPETIBILIDAD (ATS/ERS) -->
        <div class="border-top pt-4">
          <h5 class="font-weight-bold text-dark mb-3">
            <i class="fas fa-clipboard-check mr-2 text-primary"></i> CRITERIOS DE CALIDAD Y REPETIBILIDAD (ATS/ERS)
          </h5>
          <div class="row mb-4">
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold">Varianza FEV1 (L)</label>
              <input type="number" step="0.01" v-model.number="form.fev1_var_l"
                class="form-control" style="height: 38px;" placeholder="Ej: 0.03" />
              <div class="mt-1">
                <span class="badge" :class="form.fev1_var_l <= 0.150 ? 'badge-success' : 'badge-danger'">
                  {{ form.fev1_var_l <= 0.150 ? '✓ Cumple' : '✗ Excede límite' }}
                </span>
                <small class="text-muted d-block mt-1">Debe ser ≤ 0.150 L para Grado A</small>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold">Varianza FVC (L)</label>
              <input type="number" step="0.01" v-model.number="form.fvc_var_l"
                class="form-control" style="height: 38px;" placeholder="Ej: 0.01" />
              <div class="mt-1">
                <span class="badge" :class="form.fvc_var_l <= 0.150 ? 'badge-success' : 'badge-danger'">
                  {{ form.fvc_var_l <= 0.150 ? '✓ Cumple' : '✗ Excede límite' }}
                </span>
                <small class="text-muted d-block mt-1">Debe ser ≤ 0.150 L para Grado A</small>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold">Tiempo Espiratorio (FET) [s]</label>
              <input type="number" step="0.1" v-model.number="form.fet_s"
                class="form-control" style="height: 38px;" placeholder="Ej: 4.4" />
              <div class="mt-1">
                <span class="badge" :class="form.fet_s >= 6 ? 'badge-success' : 'badge-warning'">
                  {{ form.fet_s >= 6 ? '✓ Cumple' : '⚠ Insuficiente' }}
                </span>
                <small class="text-muted d-block mt-1">Debe ser ≥ 6 segundos en adultos</small>
              </div>
            </div>
          </div>

          <!-- PUNTOS ADICIONALES DE CALIDAD -->
          <div class="row mb-4">
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold">Varianza PEF (L/s)</label>
              <input type="number" step="0.1" v-model.number="form.pef_var_l_s"
                class="form-control" style="height: 38px;" placeholder="Ej: 0.25" />
              <small class="text-muted d-block mt-1">Debe ser ≤ 0.100 L/s para repetición aceptable</small>
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold">Varianza FEF25-75 (L/s)</label>
              <input type="number" step="0.1" v-model.number="form.fef2575_var_l_s"
                class="form-control" style="height: 38px;" placeholder="Ej: 0.15" />
              <small class="text-muted d-block mt-1">Menor precisión que FEV1/FVC, se acepta ±0.200</small>
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold">Número de Maniobras</label>
              <input type="number" v-model.number="form.num_maniobras"
                class="form-control" style="height: 38px;" placeholder="Ej: 3-8" />
              <small class="text-muted d-block mt-1">ATS/ERS recomienda ≥3 maniobras aceptables</small>
            </div>
          </div>

          <!-- BOTÓN DE VALIDACIÓN AUTOMÁTICA -->
          <div class="d-flex justify-content-end">
            <button type="button" @click="validarCalidadSesion"
              class="btn text-white font-weight-bold px-4 py-2"
              style="background: linear-gradient(135deg, #43A047 0%, #2E7D32 100%); border: none; border-radius: 0.375rem;">
              <i class="fas fa-check-circle mr-2"></i> VALIDAR CALIDAD DE SESIÓN
            </button>
          </div>

          <!-- RESULTADO DE VALIDACIÓN -->
          <div v-if="validacionCalidad" class="mt-4 p-3 border rounded"
            :class="validacionCalidad.ok ? 'bg-success bg-opacity-10 border-success' : 'bg-danger bg-opacity-10 border-danger'">
            <h6 class="font-weight-bold mb-2 flex items-center gap-2"
              :class="validacionCalidad.ok ? 'text-success' : 'text-danger'">
              <i :class="validacionCalidad.ok ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i>
              {{ validacionCalidad.ok ? '✓ SESIÓN VÁLIDA' : '✗ SESIÓN REQUIERE REVISIÓN' }}
            </h6>
            <ul class="text-sm mb-0">
              <li v-for="item in validacionCalidad.detalles" :key="item" :class="validacionCalidad.ok ? 'text-success' : 'text-danger'">
                {{ item }}
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ParametrosFvlEspirometria',
  props: {
    modelValue: { type: Object, required: true }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      parametrosPrincipales: [
        { key: 'fvc_l', label: 'FVC', unidad: 'L' },
        { key: 'fev1_l', label: 'FEV1', unidad: 'L' },
        { key: 'fev1_fvc_ratio', label: 'FEV1/FVC', unidad: '' },
        { key: 'fef25_75', label: 'FEF25-75%', unidad: 'L/s' },
        { key: 'pef_l_s', label: 'PEF', unidad: 'L/s' }
      ],
      validacionCalidad: null
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    }
  },
  methods: {
    calcularPorcentajes() {
      this.parametrosPrincipales.forEach(p => {
        const pred = this.form[p.key + '_pred']
        const val = this.form[p.key]
        if (pred > 0 && val > 0) {
          this.form[p.key + '_porcentaje_predicho'] = Math.round((val / pred) * 100)
        } else {
          this.form[p.key + '_porcentaje_predicho'] = null
        }
      })

      // Actualizar específicamente FEV1 % predicho para la hoja de interpretación
      if (this.form.fev1_l && this.form.fev1_pred) {
        this.form.fev1_porcentaje_predicho = Math.round((this.form.fev1_l / this.form.fev1_pred) * 100)
      }

      // Actualizar específicamente FVC % predicho
      if (this.form.fvc_l && this.form.fvc_pred) {
        this.form.fvc_porcentaje_predicho = Math.round((this.form.fvc_l / this.form.fvc_pred) * 100)
      }

      this.$emit('update:modelValue', { ...this.form })
    },
    getColorPorcentaje(key) {
      const pct = this.form[key + '_porcentaje_predicho']
      if (!pct) return 'text-muted'
      if (pct >= 80) return 'text-success'
      if (pct >= 70) return 'text-warning'
      return 'text-danger'
    },
    getBadgeClass(key) {
      const pct = this.form[key + '_porcentaje_predicho']
      if (!pct) return 'bg-light text-muted'
      if (pct >= 80) return 'badge-success'
      if (pct >= 70) return 'badge-warning'
      return 'badge-danger'
    },
    getInterpretacion(key) {
      const pct = this.form[key + '_porcentaje_predicho']
      if (!pct) return '—'
      if (pct >= 80) return 'Normal'
      if (pct >= 70) return 'Límite bajo'
      return 'Anormal'
    },
    validarCalidadSesion() {
      const detalles = []
      let ok = true

      // Validar varianzas
      if (this.form.fev1_var_l !== undefined && this.form.fev1_var_l !== null) {
        if (this.form.fev1_var_l <= 0.150) {
          detalles.push('✓ Varianza FEV1 ≤ 0.150 L')
        } else {
          detalles.push('✗ Varianza FEV1 > 0.150 L')
          ok = false
        }
      }

      if (this.form.fvc_var_l !== undefined && this.form.fvc_var_l !== null) {
        if (this.form.fvc_var_l <= 0.150) {
          detalles.push('✓ Varianza FVC ≤ 0.150 L')
        } else {
          detalles.push('✗ Varianza FVC > 0.150 L')
          ok = false
        }
      }

      // Validar tiempo espiratorio
      if (this.form.fet_s !== undefined && this.form.fet_s !== null) {
        if (this.form.fet_s >= 6) {
          detalles.push('✓ Tiempo espiratorio ≥ 6 seg')
        } else {
          detalles.push('✗ Tiempo espiratorio < 6 seg')
          ok = false
        }
      }

      // Validar número de maniobras
      if (this.form.num_maniobras !== undefined && this.form.num_maniobras !== null) {
        if (this.form.num_maniobras >= 3) {
          detalles.push('✓ ≥ 3 maniobras realizadas')
        } else {
          detalles.push('✗ Menos de 3 maniobras')
          ok = false
        }
      }

      this.validacionCalidad = { ok, detalles }
    }
  }
}
</script>

<style scoped>
.seccion-parametros-fvl-espirometria {
  background: #f8f9fa;
}

.card {
  border: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.card-header {
  background: linear-gradient(135deg, #43A047 0%, #2E7D32 100%) !important;
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
  border-color: #43A047;
  box-shadow: 0 0 0 0.2rem rgba(67, 160, 71, 0.25);
}

.form-control-sm {
  font-size: 0.8125rem;
  padding: 4px 8px;
}

.badge {
  font-size: 0.75rem;
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

.badge-danger {
  background-color: #dc3545;
  color: white;
}

.text-success { color: #28a745 !important; }
.text-warning { color: #ffc107 !important; }
.text-danger { color: #dc3545 !important; }
.text-muted { color: #6c757d !important; }
.text-blue-700 { color: #1e40af !important; }

.font-weight-bold { font-weight: 600 !important; }
.bg-light { background-color: #f8f9fa !important; }
.bg-white { background-color: #fff !important; }
.bg-blue-50 { background-color: #eff6ff !important; }

.border { border: 1px solid #dee2e6 !important; }
.border-top { border-top: 1px solid #dee2e6 !important; }
.border-success { border-color: #28a745 !important; }
.border-danger { border-color: #dc3545 !important; }

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
.mb-6 { margin-bottom: 3rem !important; }
.px-4 { padding-left: 1.5rem !important; padding-right: 1.5rem !important; }
.py-2 { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; }

.d-flex { display: flex !important; }
.flex-wrap { flex-wrap: wrap !important; }
.justify-content-between { justify-content: space-between !important; }
.justify-content-end { justify-content: flex-end !important; }
.align-middle { align-items: center !important; }
.text-center { text-align: center !important; }
.w-100 { width: 100% !important; }

.text-sm { font-size: 0.875rem !important; }
.small { font-size: 0.8125rem !important; }

.position-static { position: static !important; }
.cursor-pointer { cursor: pointer !important; }

.table {
  width: 100%;
  margin-bottom: 1rem;
  color: #212529;
  background-color: #fff;
  border-collapse: collapse;
}

.table th, .table td {
  padding: 0.5rem;
  vertical-align: middle;
  border-top: 1px solid #dee2e6;
}

.table thead th {
  vertical-align: bottom;
  border-bottom: 2px solid #dee2e6;
  background-color: #f8f9fa;
}

.table-hover tbody tr:hover {
  background-color: rgba(0, 0, 0, 0.03);
}

.table-bordered {
  border: 1px solid #dee2e6;
}

.table-bordered th, .table-bordered td {
  border: 1px solid #dee2e6;
}

.align-middle { vertical-align: middle !important; }

.bg-opacity-10 { background-color: rgba(40, 167, 69, 0.1) !important; }

@media (max-width: 768px) {
  .col-md-4 { flex: 0 0 100%; max-width: 100%; }
}
</style>
