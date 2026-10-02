<template>
  <div class="hoja3-exploracionfisica-tr">
    <!-- SECTION: SIGNOS VITALES -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-heartbeat mr-2"></i> SIGNOS VITALES
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- PESO -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">PESO (Kg)</label>
            <input 
              type="number" 
              class="form-control" 
              v-model.number="localData.peso_kg"
              @change="emitChange"
              placeholder="0.0"
              step="0.1"
              style="height: 38px;"
            >
          </div>

          <!-- ESTATURA -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">ESTATURA (m)</label>
            <input 
              type="number" 
              class="form-control" 
              v-model.number="localData.estatura_m"
              @change="calcularIMC; emitChange()"
              placeholder="0.00"
              step="0.01"
              style="height: 38px;"
            >
            <small class="text-muted">Ej: 1.75</small>
          </div>

          <!-- IMC (Auto-calculado) -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">IMC (Auto)</label>
            <input 
              type="text" 
              class="form-control" 
              :value="formatNumber(imc)"
              disabled
              style="height: 38px;"
            >
            <small class="text-muted">{{ imcClasificacion }}</small>
          </div>

          <!-- PRESIÓN SISTÓLICA -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">P. SISTÓLICA (mmHg)</label>
            <input 
              type="number" 
              class="form-control" 
              v-model.number="localData.presion_sistolica"
              @change="emitChange"
              placeholder="120"
              style="height: 38px;"
            >
          </div>

          <!-- PRESIÓN DIASTÓLICA -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">P. DIASTÓLICA (mmHg)</label>
            <input 
              type="number" 
              class="form-control" 
              v-model.number="localData.presion_diastolica"
              @change="emitChange"
              placeholder="80"
              style="height: 38px;"
            >
          </div>

          <!-- FRECUENCIA CARDÍACA -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">F.C. (lat/min)</label>
            <input 
              type="number" 
              class="form-control" 
              v-model.number="localData.frecuencia_cardiaca"
              @change="emitChange"
              placeholder="72"
              style="height: 38px;"
            >
          </div>

          <!-- FRECUENCIA RESPIRATORIA -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">F.R. (resp/min)</label>
            <input 
              type="number" 
              class="form-control" 
              v-model.number="localData.frecuencia_respiratoria"
              @change="emitChange"
              placeholder="16"
              style="height: 38px;"
            >
          </div>

          <!-- SCORE -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">SCORE</label>
            <input 
              type="text" 
              class="form-control" 
              v-model="localData.score"
              @change="emitChange"
              placeholder="Ej: NEWS, MEWS, etc."
              style="height: 38px;"
            >
          </div>
        </div>

        <!-- REFERENCIA -->
        <div class="alert alert-secondary mb-0" style="font-size: 11px;">
          <strong>Referencia:</strong> PA Normal &lt;120/80 | Sobrepeso IMC 25-29.9 | Obesidad ≥30 | FC Normal 60-100 lpm | FR Normal 12-20 rpm
        </div>
      </div>
    </div>

    <!-- SECTION: EXAMEN POR SISTEMAS -->
    <div class="card">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-procedures mr-2"></i> EXAMEN POR SISTEMAS (13 áreas)
        </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm table-bordered">
            <thead style="background: #ecf0f1;">
              <tr>
                <th style="width: 20%;">ÓRGANO / SISTEMA</th>
                <th style="width: 15%;">NORMAL</th>
                <th style="width: 15%;">ANORMAL</th>
                <th style="width: 15%;">DIFERIDO</th>
                <th style="width: 35%;">OBSERVACIONES</th>
              </tr>
            </thead>
            <tbody>
              <!-- Fila por cada sistema -->
              <tr v-for="sistema in sistemas" :key="sistema.id">
                <td class="font-weight-bold">
                  <i :class="sistema.icono" class="mr-2"></i>
                  {{ sistema.nombre }}
                </td>
                <td>
                  <div class="custom-control custom-radio">
                    <input 
                      type="radio" 
                      :id="'radio_' + sistema.id + '_normal'"
                      class="custom-control-input"
                      :value="'Normal'"
                      v-model="localData[sistema.id].estado"
                      @change="emitChange"
                    >
                    <label class="custom-control-label" :for="'radio_' + sistema.id + '_normal'"></label>
                  </div>
                </td>
                <td>
                  <div class="custom-control custom-radio">
                    <input 
                      type="radio" 
                      :id="'radio_' + sistema.id + '_anormal'"
                      class="custom-control-input"
                      :value="'Anormal'"
                      v-model="localData[sistema.id].estado"
                      @change="emitChange"
                    >
                    <label class="custom-control-label" :for="'radio_' + sistema.id + '_anormal'"></label>
                  </div>
                </td>
                <td>
                  <div class="custom-control custom-radio">
                    <input 
                      type="radio" 
                      :id="'radio_' + sistema.id + '_diferido'"
                      class="custom-control-input"
                      :value="'Diferido'"
                      v-model="localData[sistema.id].estado"
                      @change="emitChange"
                    >
                    <label class="custom-control-label" :for="'radio_' + sistema.id + '_diferido'"></label>
                  </div>
                </td>
                <td>
                  <textarea 
                    v-if="localData[sistema.id].estado === 'Anormal' || localData[sistema.id].estado === 'Diferido'"
                    class="form-control form-control-sm" 
                    v-model="localData[sistema.id].observaciones"
                    @change="emitChange"
                    placeholder="Describa los hallazgos o motivo del diferimiento"
                    rows="1"
                    style="font-size: 11px; padding: 4px 6px; min-height: 30px;"
                  ></textarea>
                  <small v-else class="text-muted">--</small>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- RESUMEN -->
        <div class="mt-3">
          <div class="alert alert-info">
            <strong>📊 Resumen:</strong>
            <span class="badge badge-success mr-2">Normal: {{ countByEstado('Normal') }}</span>
            <span class="badge badge-warning mr-2">Anormal: {{ countByEstado('Anormal') }}</span>
            <span class="badge badge-secondary">Diferido: {{ countByEstado('Diferido') }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Hoja3ExploracionfisicaTr',
  props: {
    modelValue: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      localData: {
        peso_kg: 0,
        estatura_m: 0,
        presion_sistolica: 0,
        presion_diastolica: 0,
        frecuencia_cardiaca: 0,
        frecuencia_respiratoria: 0,
        score: '',
        cabeza: { estado: 'Normal', observaciones: '' },
        ojos: { estado: 'Normal', observaciones: '' },
        oidos: { estado: 'Normal', observaciones: '' },
        nariz: { estado: 'Normal', observaciones: '' },
        boca: { estado: 'Normal', observaciones: '' },
        cuello: { estado: 'Normal', observaciones: '' },
        cardiovascular: { estado: 'Normal', observaciones: '' },
        pulmonar: { estado: 'Normal', observaciones: '' },
        abdomen: { estado: 'Normal', observaciones: '' },
        genitourinario: { estado: 'Normal', observaciones: '' },
        osteomuscular: { estado: 'Normal', observaciones: '' },
        neurologico: { estado: 'Normal', observaciones: '' },
        tegumentos: { estado: 'Normal', observaciones: '' }
      },
      sistemas: [
        { id: 'cabeza', nombre: 'CABEZA', icono: 'fas fa-head-side-virus' },
        { id: 'ojos', nombre: 'OJOS', icono: 'fas fa-eye' },
        { id: 'oidos', nombre: 'OIDOS', icono: 'fas fa-ear' },
        { id: 'nariz', nombre: 'NARIZ', icono: 'fas fa-mask' },
        { id: 'boca', nombre: 'BOCA', icono: 'fas fa-tooth' },
        { id: 'cuello', nombre: 'CUELLO', icono: 'fas fa-ring' },
        { id: 'cardiovascular', nombre: 'CARDIOVASCULAR', icono: 'fas fa-heart' },
        { id: 'pulmonar', nombre: 'PULMONAR', icono: 'fas fa-lungs' },
        { id: 'abdomen', nombre: 'ABDOMEN', icono: 'fas fa-stomach' },
        { id: 'genitourinario', nombre: 'GENITOURINARIO', icono: 'fas fa-kidney' },
        { id: 'osteomuscular', nombre: 'OSTEOMUSCULAR', icono: 'fas fa-bone' },
        { id: 'neurologico', nombre: 'NEUROLOGICO', icono: 'fas fa-brain' },
        { id: 'tegumentos', nombre: 'TEGUMENTOS', icono: 'fas fa-hand-paper' }
      ]
    }
  },
  computed: {
    imc() {
      if (!this.localData.peso_kg || !this.localData.estatura_m) return 0
      return this.localData.peso_kg / (this.localData.estatura_m * this.localData.estatura_m)
    },
    imcClasificacion() {
      const imc = this.imc
      if (imc < 18.5) return 'Bajo peso'
      if (imc < 25) return 'Normal'
      if (imc < 30) return 'Sobrepeso'
      return 'Obesidad'
    }
  },
  methods: {
    formatNumber(value) {
      if (!value) return '0.0'
      return parseFloat(value).toFixed(1)
    },
    calcularIMC() {
      // Solo para trigger, el computed ya lo calcula
    },
    emitChange() {
      this.$emit('update:modelValue', JSON.parse(JSON.stringify(this.localData)))
    },
    countByEstado(estado) {
      return this.sistemas.filter(s => this.localData[s.id].estado === estado).length
    }
  },
  watch: {
    modelValue: {
      handler(newVal) {
        if (newVal && Object.keys(newVal).length > 0) {
          this.localData = { ...this.localData, ...newVal }
        }
      },
      deep: true
    }
  },
  mounted() {
    if (this.modelValue && Object.keys(this.modelValue).length > 0) {
      this.localData = { ...this.localData, ...newVal }
    }
  }
}
</script>

<style scoped>
.hoja3-exploracionfisica-tr {
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
  height: 38px;
}

.form-control:focus {
  border-color: #0c5460;
  box-shadow: 0 0 0 0.2rem rgba(12, 84, 96, 0.25);
}

.form-control-sm {
  font-size: 12px;
  border-radius: 3px;
}

.table {
  margin-bottom: 0;
  font-size: 12px;
}

.table thead th {
  font-weight: 600;
  color: #2c3e50;
  border-bottom: 2px solid #dee2e6;
  padding: 10px 6px;
}

.table tbody td {
  padding: 8px 6px;
  vertical-align: middle;
}

.custom-control {
  position: relative;
  display: block;
  min-height: 1.25rem;
  padding-left: 0;
}

.custom-control-input {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  z-index: -1;
}

.custom-control-label {
  position: relative;
  margin-bottom: 0;
  vertical-align: top;
  cursor: pointer;
}

label {
  font-size: 13px;
  margin-bottom: 6px;
  color: #2c3e50;
}

.alert {
  font-size: 12px;
  margin-bottom: 0;
  border-radius: 4px;
}

.badge {
  font-size: 11px;
  padding: 4px 8px;
  border-radius: 3px;
}

textarea.form-control-sm {
  resize: vertical;
  min-height: 32px;
}

i {
  color: #0c5460;
}
</style>