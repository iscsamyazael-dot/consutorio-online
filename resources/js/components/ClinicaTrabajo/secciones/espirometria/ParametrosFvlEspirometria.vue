<template>
  <div class="seccion-parametros-fvl-espirometria">
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #43A047 0%, #2E7D32 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-chart-line mr-2"></i> PARÁMETROS FVL - MÚLTIPLES MANIOBRAS
        </h5>
      </div>
      <div class="card-body">
        <!-- GUARD: No renderizar hasta que maniobras esté listo -->
        <div v-if="!maniobrasListas" class="alert alert-info text-center">
          <i class="fas fa-spinner fa-spin mr-2"></i> Inicializando componente...
        </div>

        <template v-else>
          <!-- CONFIGURACIÓN DE REFERENCIA -->
          <div class="row mb-4">
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold">Ecuación de Referencia <span class="text-danger">*</span></label>
              <select v-model="localForm.referencia" @change="emitChange" class="form-control" style="height: 38px;">
                <option value="NHANES III">NHANES III (EE.UU.)</option>
                <option value="GLI-2012">GLI-2012 (Global)</option>
                <option value="Hankinson">Hankinson (EE.UU.)</option>
                <option value="Lloveras">Lloveras (Latinoamérica)</option>
                <option value="Pellegrino">Pellegrino (Europa)</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold">Interpretación Predicha *</label>
              <select v-model="localForm.interpretacion_predicha" @change="emitChange" class="form-control" style="height: 38px;">
                <option value="GOLD(2008)/Hardie">GOLD (2008) / Hardie</option>
                <option value="ATS/ERS 2021">ATS/ERS 2021</option>
                <option value="GOLD-2023">GOLD 2023 (Actualizada)</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold">Número de Maniobras</label>
              <input type="number" :value="maniobras.length" class="form-control" style="height: 38px;" readonly />
              <small class="text-muted d-block mt-1">ATS/ERS recomienda ≥3 maniobras aceptables</small>
            </div>
          </div>

          <!-- BOTONES DE GESTIÓN DE MANIOBRAS -->
          <div class="d-flex justify-content-between mb-3">
            <button type="button" @click="agregarManiobra" class="btn btn-success btn-sm" :disabled="maniobras.length >= 8">
              <i class="fas fa-plus mr-1"></i> Agregar Maniobra
            </button>
            <button type="button" @click="eliminarUltimaManiobra" class="btn btn-danger btn-sm" :disabled="maniobras.length <= 1">
              <i class="fas fa-minus mr-1"></i> Eliminar Última
            </button>
          </div>

          <!-- TABLA COMPARATIVA DE MANIOBRAS -->
          <div class="table-responsive mb-4">
            <table class="table table-sm table-bordered table-hover align-middle">
              <thead class="bg-gray-100">
                <tr>
                  <th class="text-center" rowspan="2" style="vertical-align: middle;">Parámetro</th>
                  <th class="text-center" rowspan="2" style="vertical-align: middle;">Unidad</th>
                  <th class="text-center" rowspan="2" style="vertical-align: middle;">Predicho</th>
                  <th class="text-center" rowspan="2" style="vertical-align: middle;">LLN</th>
                  <th class="text-center" :colspan="maniobras.length">Maniobras</th>
                  <th class="text-center bg-blue-50" rowspan="2" style="vertical-align: middle;">Mejor Valor</th>
                  <th class="text-center" rowspan="2" style="vertical-align: middle;">% Pred</th>
                  <th class="text-center" rowspan="2" style="vertical-align: middle;">Interpretación</th>
                </tr>
                <tr>
                  <th class="text-center" v-for="(maniobra, index) in maniobras" :key="'header-'+index">
                    Prueba {{ index + 1 }}
                    <button type="button" @click="eliminarManiobra(index)" class="btn btn-xs btn-danger ml-1" 
                      v-if="maniobras.length > 1" style="padding: 2px 6px; font-size: 10px;">
                      ✕
                    </button>
                  </th>
                </tr>
              </thead>
              <tbody>
                <!-- FVC -->
                <tr>
                  <td class="font-weight-bold">FVC</td>
                  <td class="text-center text-muted small">L</td>
                  <td class="text-center">
                    <input type="number" step="0.01" v-model.number="localForm.fvc_pred" @input="recalcularTodo" class="form-control form-control-sm text-center" style="height: 34px;" />
                  </td>
                  <td class="text-center">
                    <input type="number" step="0.01" v-model.number="localForm.fvc_lln" class="form-control form-control-sm text-center" style="height: 34px;" />
                  </td>
                  <td class="text-center" v-for="(maniobra, index) in maniobras" :key="'fvc-'+index">
                    <input type="number" step="0.01" v-model.number="maniobra.fvc_l" @input="recalcularTodo" class="form-control form-control-sm text-center" style="height: 34px;" />
                  </td>
                  <td class="text-center bg-blue-50 font-weight-bold text-blue-700">{{ localForm.mejor_fvc || '—' }}</td>
                  <td class="text-center font-weight-bold" :class="getColorPorcentaje('fvc')">{{ localForm.fvc_porcentaje_predicho || '—' }}%</td>
                  <td class="text-center"><span :class="getBadgeClass('fvc')" class="badge">{{ getInterpretacion('fvc') }}</span></td>
                </tr>

                <!-- FEV1 -->
                <tr>
                  <td class="font-weight-bold">FEV1</td>
                  <td class="text-center text-muted small">L</td>
                  <td class="text-center">
                    <input type="number" step="0.01" v-model.number="localForm.fev1_pred" @input="recalcularTodo" class="form-control form-control-sm text-center" style="height: 34px;" />
                  </td>
                  <td class="text-center">
                    <input type="number" step="0.01" v-model.number="localForm.fev1_lln" class="form-control form-control-sm text-center" style="height: 34px;" />
                  </td>
                  <td class="text-center" v-for="(maniobra, index) in maniobras" :key="'fev1-'+index">
                    <input type="number" step="0.01" v-model.number="maniobra.fev1_l" @input="recalcularTodo" class="form-control form-control-sm text-center" style="height: 34px;" />
                  </td>
                  <td class="text-center bg-blue-50 font-weight-bold text-blue-700">{{ localForm.mejor_fev1 || '—' }}</td>
                  <td class="text-center font-weight-bold" :class="getColorPorcentaje('fev1')">{{ localForm.fev1_porcentaje_predicho || '—' }}%</td>
                  <td class="text-center"><span :class="getBadgeClass('fev1')" class="badge">{{ getInterpretacion('fev1') }}</span></td>
                </tr>

                <!-- FEV1/FVC -->
                <tr>
                  <td class="font-weight-bold">FEV1/FVC</td>
                  <td class="text-center text-muted small">—</td>
                  <td class="text-center">
                    <input type="number" step="0.001" v-model.number="localForm.fev1_fvc_pred" class="form-control form-control-sm text-center" style="height: 34px;" />
                  </td>
                  <td class="text-center">
                    <input type="number" step="0.001" v-model.number="localForm.fev1_fvc_lln" class="form-control form-control-sm text-center" style="height: 34px;" />
                  </td>
                  <td class="text-center" v-for="(maniobra, index) in maniobras" :key="'ratio-'+index">
                    <input type="number" step="0.001" v-model.number="maniobra.fev1_fvc_ratio" class="form-control form-control-sm text-center" style="height: 34px;" />
                  </td>
                  <td class="text-center bg-blue-50 font-weight-bold text-blue-700">{{ localForm.mejor_fev1_fvc || '—' }}</td>
                  <td class="text-center font-weight-bold" :class="getColorPorcentaje('fev1_fvc')">{{ localForm.fev1_fvc_porcentaje_predicho || '—' }}%</td>
                  <td class="text-center"><span :class="getBadgeClass('fev1_fvc')" class="badge">{{ getInterpretacion('fev1_fvc') }}</span></td>
                </tr>

                <!-- FEF25-75% -->
                <tr>
                  <td class="font-weight-bold">FEF25-75%</td>
                  <td class="text-center text-muted small">L/s</td>
                  <td class="text-center">
                    <input type="number" step="0.01" v-model.number="localForm.fef25_75_pred" class="form-control form-control-sm text-center" style="height: 34px;" />
                  </td>
                  <td class="text-center">
                    <input type="number" step="0.01" v-model.number="localForm.fef25_75_lln" class="form-control form-control-sm text-center" style="height: 34px;" />
                  </td>
                  <td class="text-center" v-for="(maniobra, index) in maniobras" :key="'fef-'+index">
                    <input type="number" step="0.01" v-model.number="maniobra.fef25_75" class="form-control form-control-sm text-center" style="height: 34px;" />
                  </td>
                  <td class="text-center bg-blue-50 font-weight-bold text-blue-700">{{ localForm.mejor_fef25_75 || '—' }}</td>
                  <td class="text-center font-weight-bold" :class="getColorPorcentaje('fef25_75')">{{ localForm.fef25_75_porcentaje_predicho || '—' }}%</td>
                  <td class="text-center"><span :class="getBadgeClass('fef25_75')" class="badge">{{ getInterpretacion('fef25_75') }}</span></td>
                </tr>

                <!-- PEF -->
                <tr>
                  <td class="font-weight-bold">PEF</td>
                  <td class="text-center text-muted small">L/s</td>
                  <td class="text-center">
                    <input type="number" step="0.01" v-model.number="localForm.pef_pred" class="form-control form-control-sm text-center" style="height: 34px;" />
                  </td>
                  <td class="text-center">
                    <input type="number" step="0.01" v-model.number="localForm.pef_lln" class="form-control form-control-sm text-center" style="height: 34px;" />
                  </td>
                  <td class="text-center" v-for="(maniobra, index) in maniobras" :key="'pef-'+index">
                    <input type="number" step="0.01" v-model.number="maniobra.pef_l_s" class="form-control form-control-sm text-center" style="height: 34px;" />
                  </td>
                  <td class="text-center bg-blue-50 font-weight-bold text-blue-700">{{ localForm.mejor_pef || '—' }}</td>
                  <td class="text-center font-weight-bold" :class="getColorPorcentaje('pef')">{{ localForm.pef_porcentaje_predicho || '—' }}%</td>
                  <td class="text-center"><span :class="getBadgeClass('pef')" class="badge">{{ getInterpretacion('pef') }}</span></td>
                </tr>

                <!-- FET -->
                <tr>
                  <td class="font-weight-bold">FET</td>
                  <td class="text-center text-muted small">s</td>
                  <td class="text-center">—</td>
                  <td class="text-center">—</td>
                  <td class="text-center" v-for="(maniobra, index) in maniobras" :key="'fet-'+index">
                    <input type="number" step="0.1" v-model.number="maniobra.fet_s" class="form-control form-control-sm text-center" style="height: 34px;" />
                    <small :class="maniobra.fet_s >= 6 ? 'text-success' : 'text-warning'">
                      {{ maniobra.fet_s >= 6 ? '✓' : '⚠' }}
                    </small>
                  </td>
                  <td class="text-center bg-blue-50">—</td>
                  <td class="text-center text-muted" colspan="2">
                    <small class="text-muted">≥ 6 seg en adultos</small>
                  </td>
                </tr>

                <!-- FIVC -->
                <tr>
                  <td class="font-weight-bold">FIVC</td>
                  <td class="text-center text-muted small">L</td>
                  <td class="text-center">—</td>
                  <td class="text-center">—</td>
                  <td class="text-center" v-for="(maniobra, index) in maniobras" :key="'fivc-'+index">
                    <input type="number" step="0.01" v-model.number="maniobra.fivc_l" class="form-control form-control-sm text-center" style="height: 34px;" />
                  </td>
                  <td class="text-center bg-blue-50">—</td>
                  <td class="text-center text-muted" colspan="2">Info</td>
                </tr>

                <!-- PIF -->
                <tr>
                  <td class="font-weight-bold">PIF</td>
                  <td class="text-center text-muted small">L/s</td>
                  <td class="text-center">—</td>
                  <td class="text-center">—</td>
                  <td class="text-center" v-for="(maniobra, index) in maniobras" :key="'pif-'+index">
                    <input type="number" step="0.01" v-model.number="maniobra.pif_l_s" class="form-control form-control-sm text-center" style="height: 34px;" />
                  </td>
                  <td class="text-center bg-blue-50">—</td>
                  <td class="text-center text-muted" colspan="2">Info</td>
                </tr>

                <!-- BEV -->
                <tr>
                  <td class="font-weight-bold">BEV</td>
                  <td class="text-center text-muted small">L</td>
                  <td class="text-center">—</td>
                  <td class="text-center">—</td>
                  <td class="text-center" v-for="(maniobra, index) in maniobras" :key="'bev-'+index">
                    <input type="number" step="0.01" v-model.number="maniobra.bev_l" class="form-control form-control-sm text-center" style="height: 34px;" />
                  </td>
                  <td class="text-center bg-blue-50">—</td>
                  <td class="text-center text-muted" colspan="2">Info</td>
                </tr>

                <!-- EOTV -->
                <tr>
                  <td class="font-weight-bold">EOTV</td>
                  <td class="text-center text-muted small">L</td>
                  <td class="text-center">—</td>
                  <td class="text-center">—</td>
                  <td class="text-center" v-for="(maniobra, index) in maniobras" :key="'eotv-'+index">
                    <input type="number" step="0.01" v-model.number="maniobra.eotv_l" class="form-control form-control-sm text-center" style="height: 34px;" />
                  </td>
                  <td class="text-center bg-blue-50">—</td>
                  <td class="text-center text-muted" colspan="2">Info</td>
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
                <input type="number" step="0.001" :value="localForm.fev1_var_l" class="form-control" 
                  style="height: 38px;" readonly />
                <div class="mt-1">
                  <span class="badge" :class="(localForm.fev1_var_l || 999) <= 0.150 ? 'badge-success' : 'badge-danger'">
                    {{ (localForm.fev1_var_l || 999) <= 0.150 ? '✓ Cumple' : ' Excede límite' }}
                  </span>
                  <small class="text-muted d-block mt-1">Debe ser ≤ 0.150 L para Grado A</small>
                </div>
              </div>
              <div class="col-md-4 mb-3">
                <label class="font-weight-bold">Varianza FVC (L)</label>
                <input type="number" step="0.001" :value="localForm.fvc_var_l" class="form-control" 
                  style="height: 38px;" readonly />
                <div class="mt-1">
                  <span class="badge" :class="(localForm.fvc_var_l || 999) <= 0.150 ? 'badge-success' : 'badge-danger'">
                    {{ (localForm.fvc_var_l || 999) <= 0.150 ? '✓ Cumple' : '✗ Excede límite' }}
                  </span>
                  <small class="text-muted d-block mt-1">Debe ser ≤ 0.150 L para Grado A</small>
                </div>
              </div>
              <div class="col-md-4 mb-3">
                <label class="font-weight-bold">Calidad de Sesión</label>
                <div class="form-control" style="height: 38px; background: #f8f9fa; display: flex; align-items: center;">
                  <span :class="getCalidadClass()" class="font-weight-bold">
                    {{ calidadSesion || '—' }}
                  </span>
                </div>
                <small class="text-muted d-block mt-1">Grado A, B, C, D o F según ATS/ERS</small>
              </div>
            </div>

            <div class="d-flex justify-content-end">
              <button type="button" @click="validarCalidadSesion" class="btn text-white font-weight-bold px-4 py-2"
                style="background: linear-gradient(135deg, #43A047 0%, #2E7D32 100%); border: none; border-radius: 0.375rem;">
                <i class="fas fa-check-circle mr-2"></i> VALIDAR CALIDAD DE SESIÓN
              </button>
            </div>

            <div v-if="validacionCalidad" class="mt-4 p-3 border rounded"
              :class="validacionCalidad.ok ? 'bg-success bg-opacity-10 border-success' : 'bg-danger bg-opacity-10 border-danger'">
              <h6 class="font-weight-bold mb-2 d-flex align-items-center"
                :class="validacionCalidad.ok ? 'text-success' : 'text-danger'">
                <i :class="validacionCalidad.ok ? 'fas fa-check-circle mr-2' : 'fas fa-times-circle mr-2'"></i>
                {{ validacionCalidad.ok ? '✓ SESIÓN VÁLIDA' : '✗ SESIÓN REQUIERE REVISIÓN' }}
              </h6>
              <ul class="text-sm mb-0">
                <li v-for="(item, idx) in validacionCalidad.detalles" :key="idx" :class="validacionCalidad.ok ? 'text-success' : 'text-danger'">
                  {{ item }}
                </li>
              </ul>
            </div>
          </div>
        </template>
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
      validacionCalidad: null,
      localForm: {
        referencia: 'NHANES III',
        interpretacion_predicha: 'GOLD(2008)/Hardie',
        fvc_pred: null,
        fvc_lln: null,
        fev1_pred: null,
        fev1_lln: null,
        fev1_fvc_pred: null,
        fev1_fvc_lln: null,
        fef25_75_pred: null,
        fef25_75_lln: null,
        pef_pred: null,
        pef_lln: null,
        mejor_fvc: null,
        mejor_fev1: null,
        mejor_fev1_fvc: null,
        mejor_fef25_75: null,
        mejor_pef: null,
        fvc_porcentaje_predicho: null,
        fev1_porcentaje_predicho: null,
        fev1_fvc_porcentaje_predicho: null,
        fef25_75_porcentaje_predicho: null,
        pef_porcentaje_predicho: null,
        fev1_var_l: null,
        fvc_var_l: null,
        maniobras: []
      },
      plantillaManiobra: {
        fvc_l: null,
        fev1_l: null,
        fev1_fvc_ratio: null,
        fef25_75: null,
        pef_l_s: null,
        fet_s: null,
        fivc_l: null,
        pif_l_s: null,
        bev_l: null,
        eotv_l: null
      }
    }
  },
  computed: {
    maniobras() {
      return this.localForm.maniobras || []
    },
    maniobrasListas() {
      return Array.isArray(this.localForm.maniobras) && this.localForm.maniobras.length > 0
    },
    calidadSesion() {
      const fev1Var = this.localForm.fev1_var_l
      const fvcVar = this.localForm.fvc_var_l
      
      if (fev1Var === null || fev1Var === undefined || fvcVar === null || fvcVar === undefined) return null
      
      if (fev1Var <= 0.150 && fvcVar <= 0.150) return 'A'
      if (fev1Var <= 0.200 && fvcVar <= 0.200) return 'B'
      if (fev1Var <= 0.250 && fvcVar <= 0.250) return 'C'
      return 'D'
    }
  },
  created() {
    // CRÍTICO: Inicializar en created() ANTES del primer render
    this.inicializarDesdeProps()
  },
  watch: {
    modelValue: {
      handler(newVal) {
        if (newVal && typeof newVal === 'object') {
          // Solo actualizar si hay cambios reales para evitar loops
          Object.keys(newVal).forEach(key => {
            if (key !== 'maniobras' && this.localForm[key] !== newVal[key]) {
              this.localForm[key] = newVal[key]
            }
          })
          // Sincronizar maniobras si vienen del padre
          if (newVal.maniobras && Array.isArray(newVal.maniobras)) {
            this.localForm.maniobras = JSON.parse(JSON.stringify(newVal.maniobras))
          }
          this.recalcularTodo()
        }
      },
      deep: true,
      immediate: true
    }
  },
  methods: {
    inicializarDesdeProps() {
      // Copiar datos del padre si existen
      if (this.modelValue && typeof this.modelValue === 'object') {
        Object.keys(this.modelValue).forEach(key => {
          if (key in this.localForm) {
            this.localForm[key] = this.modelValue[key]
          }
        })
      }
      
      // Garantizar que maniobras sea un array con al menos 3 elementos
      if (!Array.isArray(this.localForm.maniobras) || this.localForm.maniobras.length === 0) {
        this.localForm.maniobras = [
          { ...this.plantillaManiobra },
          { ...this.plantillaManiobra },
          { ...this.plantillaManiobra }
        ]
      }
      
      this.$nextTick(() => {
        this.recalcularTodo()
        this.emitChange()
      })
    },
    emitChange() {
      this.$emit('update:modelValue', { ...this.localForm })
    },
    agregarManiobra() {
      if (this.localForm.maniobras.length < 8) {
        this.localForm.maniobras.push({ ...this.plantillaManiobra })
        this.recalcularTodo()
        this.emitChange()
      }
    },
    eliminarManiobra(index) {
      if (this.localForm.maniobras.length > 1) {
        this.localForm.maniobras.splice(index, 1)
        this.recalcularTodo()
        this.emitChange()
      }
    },
    eliminarUltimaManiobra() {
      if (this.localForm.maniobras.length > 1) {
        this.localForm.maniobras.pop()
        this.recalcularTodo()
        this.emitChange()
      }
    },
    recalcularTodo() {
      this.calcularMejoresValores()
      this.calcularPorcentajes()
      this.calcularVarianzas()
    },
    calcularMejoresValores() {
      const maniobras = this.localForm.maniobras || []
      
      const fvcValues = maniobras.map(m => m.fvc_l).filter(v => v !== null && v !== undefined && !isNaN(v))
      this.localForm.mejor_fvc = fvcValues.length > 0 ? Math.max(...fvcValues) : null
      
      const fev1Values = maniobras.map(m => m.fev1_l).filter(v => v !== null && v !== undefined && !isNaN(v))
      this.localForm.mejor_fev1 = fev1Values.length > 0 ? Math.max(...fev1Values) : null
      
      if (this.localForm.mejor_fev1 && this.localForm.mejor_fvc && this.localForm.mejor_fvc > 0) {
        this.localForm.mejor_fev1_fvc = parseFloat((this.localForm.mejor_fev1 / this.localForm.mejor_fvc).toFixed(3))
      } else {
        this.localForm.mejor_fev1_fvc = null
      }
      
      const fefValues = maniobras.map(m => m.fef25_75).filter(v => v !== null && v !== undefined && !isNaN(v))
      this.localForm.mejor_fef25_75 = fefValues.length > 0 ? Math.max(...fefValues) : null
      
      const pefValues = maniobras.map(m => m.pef_l_s).filter(v => v !== null && v !== undefined && !isNaN(v))
      this.localForm.mejor_pef = pefValues.length > 0 ? Math.max(...pefValues) : null
    },
    calcularPorcentajes() {
      const f = this.localForm
      if (f.fvc_pred > 0 && f.mejor_fvc) {
        f.fvc_porcentaje_predicho = Math.round((f.mejor_fvc / f.fvc_pred) * 100)
      } else {
        f.fvc_porcentaje_predicho = null
      }
      if (f.fev1_pred > 0 && f.mejor_fev1) {
        f.fev1_porcentaje_predicho = Math.round((f.mejor_fev1 / f.fev1_pred) * 100)
      } else {
        f.fev1_porcentaje_predicho = null
      }
      if (f.fev1_fvc_pred > 0 && f.mejor_fev1_fvc) {
        f.fev1_fvc_porcentaje_predicho = Math.round((f.mejor_fev1_fvc / f.fev1_fvc_pred) * 100)
      } else {
        f.fev1_fvc_porcentaje_predicho = null
      }
      if (f.fef25_75_pred > 0 && f.mejor_fef25_75) {
        f.fef25_75_porcentaje_predicho = Math.round((f.mejor_fef25_75 / f.fef25_75_pred) * 100)
      } else {
        f.fef25_75_porcentaje_predicho = null
      }
      if (f.pef_pred > 0 && f.mejor_pef) {
        f.pef_porcentaje_predicho = Math.round((f.mejor_pef / f.pef_pred) * 100)
      } else {
        f.pef_porcentaje_predicho = null
      }
    },
    calcularVarianzas() {
      const maniobras = this.localForm.maniobras || []
      const fev1Values = maniobras.map(m => m.fev1_l).filter(v => v !== null && v !== undefined && !isNaN(v)).sort((a, b) => b - a)
      const fvcValues = maniobras.map(m => m.fvc_l).filter(v => v !== null && v !== undefined && !isNaN(v)).sort((a, b) => b - a)
      
      if (fev1Values.length >= 2) {
        this.localForm.fev1_var_l = parseFloat(Math.abs(fev1Values[0] - fev1Values[1]).toFixed(3))
      } else {
        this.localForm.fev1_var_l = null
      }
      if (fvcValues.length >= 2) {
        this.localForm.fvc_var_l = parseFloat(Math.abs(fvcValues[0] - fvcValues[1]).toFixed(3))
      } else {
        this.localForm.fvc_var_l = null
      }
    },
    getColorPorcentaje(key) {
      const pct = this.localForm[key + '_porcentaje_predicho']
      if (!pct) return 'text-muted'
      if (pct >= 80) return 'text-success'
      if (pct >= 70) return 'text-warning'
      return 'text-danger'
    },
    getBadgeClass(key) {
      const pct = this.localForm[key + '_porcentaje_predicho']
      if (!pct) return 'bg-light text-muted'
      if (pct >= 80) return 'badge-success'
      if (pct >= 70) return 'badge-warning'
      return 'badge-danger'
    },
    getInterpretacion(key) {
      const pct = this.localForm[key + '_porcentaje_predicho']
      if (!pct) return '—'
      if (pct >= 80) return 'Normal'
      if (pct >= 70) return 'Límite bajo'
      return 'Anormal'
    },
    getCalidadClass() {
      const c = this.calidadSesion
      if (c === 'A') return 'text-success'
      if (c === 'B' || c === 'C') return 'text-warning'
      if (c === 'D') return 'text-danger'
      return 'text-muted'
    },
    validarCalidadSesion() {
      const detalles = []
      let ok = true
      const f = this.localForm

      if (f.fev1_var_l !== null && f.fev1_var_l !== undefined) {
        if (f.fev1_var_l <= 0.150) {
          detalles.push(`✓ Varianza FEV1 = ${f.fev1_var_l.toFixed(3)} L (≤ 0.150 L)`)
        } else {
          detalles.push(`✗ Varianza FEV1 = ${f.fev1_var_l.toFixed(3)} L (> 0.150 L)`)
          ok = false
        }
      } else {
        detalles.push('⚠ Varianza FEV1 no calculada (se necesitan ≥2 maniobras)')
        ok = false
      }

      if (f.fvc_var_l !== null && f.fvc_var_l !== undefined) {
        if (f.fvc_var_l <= 0.150) {
          detalles.push(`✓ Varianza FVC = ${f.fvc_var_l.toFixed(3)} L (≤ 0.150 L)`)
        } else {
          detalles.push(`✗ Varianza FVC = ${f.fvc_var_l.toFixed(3)} L (> 0.150 L)`)
          ok = false
        }
      } else {
        detalles.push('⚠ Varianza FVC no calculada (se necesitan ≥2 maniobras)')
        ok = false
      }

      const numManiobras = this.localForm.maniobras.length
      if (numManiobras >= 3) {
        detalles.push(`✓ ${numManiobras} maniobras realizadas (≥ 3 requeridas)`)
      } else {
        detalles.push(`✗ Solo ${numManiobras} maniobras (< 3 requeridas)`)
        ok = false
      }

      const maniobrasConFETValido = this.localForm.maniobras.filter(m => m.fet_s >= 6).length
      if (maniobrasConFETValido >= 2) {
        detalles.push(`✓ ${maniobrasConFETValido} maniobras con FET ≥ 6 seg`)
      } else {
        detalles.push(` Solo ${maniobrasConFETValido} maniobras con FET ≥ 6 seg`)
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

.badge-success { background-color: #28a745; color: white; }
.badge-warning { background-color: #ffc107; color: #212529; }
.badge-danger { background-color: #dc3545; color: white; }

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
.p-3 { padding: 1rem !important; }
.mt-1 { margin-top: 0.25rem !important; }
.mt-2 { margin-top: 0.5rem !important; }
.mt-3 { margin-top: 1rem !important; }
.mt-4 { margin-top: 1.5rem !important; }
.mb-2 { margin-bottom: 0.5rem !important; }
.mb-3 { margin-bottom: 1rem !important; }
.mb-4 { margin-bottom: 1.5rem !important; }
.px-4 { padding-left: 1.5rem !important; padding-right: 1.5rem !important; }
.py-2 { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; }

.d-flex { display: flex !important; }
.justify-content-between { justify-content: space-between !important; }
.justify-content-end { justify-content: flex-end !important; }
.align-items-center { align-items: center !important; }
.text-center { text-align: center !important; }

.text-sm { font-size: 0.875rem !important; }
.small { font-size: 0.8125rem !important; }

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

.table-bordered { border: 1px solid #dee2e6; }
.table-bordered th, .table-bordered td { border: 1px solid #dee2e6; }

.align-middle { vertical-align: middle !important; }

.bg-opacity-10 { background-color: rgba(40, 167, 69, 0.1) !important; }

.btn-xs {
  padding: 2px 6px;
  font-size: 10px;
  line-height: 1.5;
  border-radius: 0.2rem;
}

@media (max-width: 768px) {
  .col-md-4 { flex: 0 0 100%; max-width: 100%; }
  .table-responsive { overflow-x: auto; }
}
</style>