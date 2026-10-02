<template>
  <div class="hoja1-datosbasicos-delli">
    <!-- SECTION: FOLIO + IDENTIFICACIÓN PERSONAL -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-file-alt mr-2"></i> FOLIO E IDENTIFICACIÓN PERSONAL
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- FOLIO (Auto-generado, lectura) -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">FOLIO</label>
            <input type="text" class="form-control" :value="localData.folio" disabled style="height: 38px;">
            <small class="text-muted">Auto-generado: AUD-YYYY-NNNN</small>
          </div>

          <!-- NOMBRE(S) -->
          <div class="col-md-9 mb-3">
            <label class="font-weight-bold">NOMBRE(S) <span class="text-danger">*</span></label>
            <input 
              type="text" 
              class="form-control" 
              v-model="localData.nombre"
              @change="emitChange"
              placeholder="Apellidos y nombres completos"
              style="height: 38px;"
            >
          </div>

          <!-- FECHA NACIMIENTO -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">FECHA NACIMIENTO</label>
            <input 
              type="date" 
              class="form-control" 
              v-model="localData.fecha_nacimiento"
              @change="calcularEdad; emitChange()"
              style="height: 38px;"
            >
          </div>

          <!-- EDAD (Auto-calculada) -->
          <div class="col-md-2 mb-3">
            <label class="font-weight-bold">EDAD (años)</label>
            <input type="number" class="form-control" :value="localData.edad" disabled style="height: 38px;">
          </div>

          <!-- GÉNERO -->
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold">GÉNERO</label>
            <select class="form-control" v-model="localData.genero" @change="emitChange" style="height: 38px;">
              <option value="">-- Seleccionar --</option>
              <option value="Femenino">Femenino</option>
              <option value="Masculino">Masculino</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: EMPRESA ACTUAL -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-building mr-2"></i> EMPRESA ACTUAL
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- NOMBRE EMPRESA -->
          <div class="col-md-6 mb-3">
            <label class="font-weight-bold">NOMBRE EMPRESA <span class="text-danger">*</span></label>
            <input 
              type="text" 
              class="form-control" 
              v-model="localData.empresa_actual"
              @change="emitChange"
              placeholder="Razón social completa"
              style="height: 38px;"
            >
          </div>

          <!-- Nº EMPLEADO -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Nº EMPLEADO</label>
            <input 
              type="text" 
              class="form-control" 
              v-model="localData.numero_empleado"
              @change="emitChange"
              placeholder="ID empleado"
              style="height: 38px;"
            >
          </div>

          <!-- DEPARTAMENTO/ÁREA -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">DEPARTAMENTO / ÁREA</label>
            <input 
              type="text" 
              class="form-control" 
              v-model="localData.departamento"
              @change="emitChange"
              placeholder="Ej: Producción"
              style="height: 38px;"
            >
          </div>

          <!-- ANTIGÜEDAD EMPRESA -->
          <div class="col-md-6 mb-3">
            <label class="font-weight-bold">ANTIGÜEDAD EN EMPRESA</label>
            <div class="row">
              <div class="col-6">
                <input 
                  type="number" 
                  class="form-control" 
                  v-model.number="localData.antiguedad_empresa_anos"
                  @change="emitChange"
                  placeholder="Años"
                  style="height: 38px;"
                >
              </div>
              <div class="col-6">
                <input 
                  type="number" 
                  class="form-control" 
                  v-model.number="localData.antiguedad_empresa_meses"
                  @change="emitChange"
                  placeholder="Meses"
                  style="height: 38px;"
                >
              </div>
            </div>
            <small class="text-muted">Años y Meses</small>
          </div>

          <!-- PUESTO -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">PUESTO / CARGO</label>
            <input 
              type="text" 
              class="form-control" 
              v-model="localData.puesto"
              @change="emitChange"
              placeholder="Ej: Operador"
              style="height: 38px;"
            >
          </div>

          <!-- ANTIGÜEDAD PUESTO -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">ANTIGÜEDAD EN PUESTO</label>
            <div class="row">
              <div class="col-6">
                <input 
                  type="number" 
                  class="form-control" 
                  v-model.number="localData.antiguedad_puesto_anos"
                  @change="emitChange"
                  placeholder="Años"
                  style="height: 38px;"
                >
              </div>
              <div class="col-6">
                <input 
                  type="number" 
                  class="form-control" 
                  v-model.number="localData.antiguedad_puesto_meses"
                  @change="emitChange"
                  placeholder="Meses"
                  style="height: 38px;"
                >
              </div>
            </div>
            <small class="text-muted">Años y Meses</small>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: EMPRESAS ANTERIORES / OTRA EMPRESA ACTUAL -->
    <div class="card">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-history mr-2"></i> EMPRESAS ANTERIORES / OTRA EMPRESA ACTUAL
        </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm table-bordered">
            <thead style="background: #ecf0f1;">
              <tr>
                <th>Empresa</th>
                <th>Puesto</th>
                <th>Tiempo (Años)</th>
                <th>Exposición Ruido</th>
                <th>Exposición Ototóxicos</th>
                <th>EPP Auditivo</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(empresa, idx) in localData.empresas_anteriores" :key="'empresa-' + idx">
                <td>
                  <input
                    type="text"
                    class="form-control form-control-sm"
                    v-model="empresa.nombre"
                    @change="emitChange"
                    placeholder="Nombre empresa"
                  >
                </td>
                <td>
                  <input
                    type="text"
                    class="form-control form-control-sm"
                    v-model="empresa.puesto"
                    @change="emitChange"
                    placeholder="Puesto"
                  >
                </td>
                <td>
                  <input
                    type="number"
                    class="form-control form-control-sm"
                    v-model.number="empresa.tiempo_anos"
                    @change="emitChange"
                    placeholder="0"
                  >
                </td>
                <td>
                  <select class="form-control form-control-sm" v-model="empresa.exposicion_ruido" @change="emitChange">
                    <option value="">No</option>
                    <option value="Sí">Sí</option>
                  </select>
                </td>
                <td>
                  <select class="form-control form-control-sm" v-model="empresa.exposicion_ototoxicos" @change="emitChange">
                    <option value="">No</option>
                    <option value="Sí">Sí</option>
                  </select>
                </td>
                <td>
                  <select class="form-control form-control-sm" v-model="empresa.epp_auditivo" @change="emitChange">
                    <option value="">No</option>
                    <option value="Sí">Sí</option>
                  </select>
                </td>
                <td>
                  <button class="btn btn-sm btn-danger" @click="eliminarEmpresa(idx)">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <button class="btn btn-sm btn-success" @click="agregarEmpresa">
          <i class="fas fa-plus mr-1"></i> Agregar Empresa Anterior
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Hoja1DatosbasicosDelli',
  props: {
    modelValue: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      localData: {
        folio: '',
        nombre: '',
        fecha_nacimiento: '',
        edad: 0,
        genero: '',
        empresa_actual: '',
        numero_empleado: '',
        departamento: '',
        antiguedad_empresa_anos: 0,
        antiguedad_empresa_meses: 0,
        puesto: '',
        antiguedad_puesto_anos: 0,
        antiguedad_puesto_meses: 0,
        empresas_anteriores: []
      }
    }
  },
  watch: {
    modelValue: {
      handler(newVal) {
        if (newVal && Object.keys(newVal).length > 0) {
          this.localData = { ...newVal }
        }
      },
      deep: true
    }
  },
  methods: {
    calcularEdad() {
      if (!this.localData.fecha_nacimiento) return
      const hoy = new Date()
      const nacimiento = new Date(this.localData.fecha_nacimiento)
      let edad = hoy.getFullYear() - nacimiento.getFullYear()
      const mes = hoy.getMonth() - nacimiento.getMonth()
      if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--
      }
      this.localData.edad = edad
    },
    emitChange() {
      this.$emit('update:modelValue', { ...this.localData })
    },
    agregarEmpresa() {
      this.localData.empresas_anteriores.push({
        nombre: '',
        puesto: '',
        tiempo_anos: 0,
        exposicion_ruido: '',
        exposicion_ototoxicos: '',
        epp_auditivo: ''
      })
      this.emitChange()
    },
    eliminarEmpresa(idx) {
      this.localData.empresas_anteriores.splice(idx, 1)
      this.emitChange()
    }
  },
  mounted() {
    // Generar folio si es nuevo
    if (!this.localData.folio) {
      const ano = new Date().getFullYear()
      const random = String(Math.floor(Math.random() * 9000) + 1000)
      this.localData.folio = `AUD-${ano}-${random}`
      this.emitChange()
    }
    
    if (this.modelValue && Object.keys(this.modelValue).length > 0) {
      this.localData = { ...this.modelValue }
    }
  }
}
</script>

<style scoped>
.hoja1-datosbasicos-delli {
  background: #f8f9fa;
}

.card {
  border: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.form-control, .form-control-sm {
  border-radius: 4px;
  border: 1px solid #ced4da;
  font-size: 14px;
  padding: 10px 12px;
}

.form-control:focus {
  border-color: #5F6E7E;
  box-shadow: 0 0 0 0.2rem rgba(95, 110, 126, 0.25);
}

label {
  font-size: 13px;
  margin-bottom: 6px;
  color: #2c3e50;
}

.table {
  margin-bottom: 0;
  font-size: 13px;
}

.table thead th {
  font-weight: 600;
  color: #2c3e50;
  border-bottom: 2px solid #dee2e6;
}

.btn-success {
  background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
  border: none;
}

.btn-danger {
  background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
  border: none;
}
</style>