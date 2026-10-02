<template>
  <div class="hoja1-identificacionempleo-tr">
    <!-- SECTION: IDENTIFICACIÓN PERSONAL -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-user-circle mr-2"></i> IDENTIFICACIÓN PERSONAL
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- NOMBRE COMPLETO -->
          <div class="col-md-6 mb-3">
            <label class="font-weight-bold">NOMBRE COMPLETO <span class="text-danger">*</span></label>
            <input
              type="text"
              class="form-control"
              v-model="localData.nombre"
              @change="emitChange"
              placeholder="Nombre completo"
              style="height: 38px;"
            >
          </div>

          <!-- PUESTO -->
          <div class="col-md-6 mb-3">
            <label class="font-weight-bold">PUESTO <span class="text-danger">*</span></label>
            <input
              type="text"
              class="form-control"
              v-model="localData.puesto"
              @change="emitChange"
              placeholder="Ej: Médico General, Enfermera, Admin."
              style="height: 38px;"
            >
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

          <!-- FECHA NACIMIENTO -->
          <div class="col-md-4 mb-3">
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
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold">EDAD (años)</label>
            <input type="number" class="form-control" :value="localData.edad" disabled style="height: 38px;">
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: DATOS DE EMPLEO ACTUAL -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-building mr-2"></i> INFORMACIÓN DEL EMPLEO ACTUAL
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- EMPRESA -->
          <div class="col-md-6 mb-3">
            <label class="font-weight-bold">EMPRESA <span class="text-danger">*</span></label>
            <input 
              type="text" 
              class="form-control" 
              v-model="localData.empresa"
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
              placeholder="Ej: Operaciones"
              style="height: 38px;"
            >
          </div>

          <!-- ANTIGÜEDAD EN EMPRESA -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">ANTIGÜEDAD EN EMPRESA</label>
            <input 
              type="text" 
              class="form-control" 
              v-model="localData.antiguedad_empresa"
              @change="emitChange"
              placeholder="Ej: 2 años 3 meses"
              style="height: 38px;"
            >
          </div>

          <!-- ANTIGÜEDAD EN PUESTO -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">ANTIGÜEDAD EN PUESTO</label>
            <input 
              type="text" 
              class="form-control" 
              v-model="localData.antiguedad_puesto"
              @change="emitChange"
              placeholder="Ej: 1 año 6 meses"
              style="height: 38px;"
            >
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: ANTECEDENTES LABORALES -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-history mr-2"></i> ANTECEDENTES LABORALES
        </h5>
      </div>
      <div class="card-body">
        <div class="alert alert-info" style="font-size: 12px;">
          <i class="fas fa-info-circle mr-1"></i> Registre empleos anteriores o actuales adicionales
        </div>
        
        <div v-for="(empleo, idx) in localData.antecedentes_laborales" :key="'empleo-' + idx" class="border p-3 mb-3 rounded">
          <div class="row">
            <div class="col-md-6 mb-2">
              <label class="font-weight-bold">EMPRESA</label>
              <input 
                type="text" 
                class="form-control form-control-sm" 
                v-model="empleo.empresa"
                @change="emitChange"
                placeholder="Nombre de la empresa"
              >
            </div>
            <div class="col-md-3 mb-2">
              <label class="font-weight-bold">PUESTO</label>
              <input 
                type="text" 
                class="form-control form-control-sm" 
                v-model="empleo.puesto"
                @change="emitChange"
                placeholder="Puesto desempeñado"
              >
            </div>
            <div class="col-md-3 mb-2">
              <label class="font-weight-bold">TIEMPO LABORADO</label>
              <input 
                type="text" 
                class="form-control form-control-sm" 
                v-model="empleo.tiempo"
                @change="emitChange"
                placeholder="Ej: 2 años"
              >
            </div>
            <div class="col-md-3 mb-2">
              <label class="font-weight-bold">EXPOSICIÓN A RUIDO</label>
              <select class="form-control form-control-sm" v-model="empleo.exposicion_ruido" @change="emitChange">
                <option value="">-- Seleccionar --</option>
                <option value="Bajo">Bajo</option>
                <option value="Medio">Medio</option>
                <option value="Muy alto">Muy alto</option>
                <option value="Crítico">Crítico</option>
              </select>
            </div>
            <div class="col-md-3 mb-2">
              <label class="font-weight-bold">AGENTES OTOTÓXICOS</label>
              <select class="form-control form-control-sm" v-model="empleo.agentes_ototoxicos" @change="emitChange">
                <option value="">-- Seleccionar --</option>
                <option value="Disolventes">Disolventes</option>
                <option value="Gases">Gases</option>
                <option value="Metales Sales">Metales Sales</option>
                <option value="Varios">Varios</option>
                <option value="Ninguno">Ninguno</option>
              </select>
            </div>
            <div class="col-md-3 mb-2">
              <label class="font-weight-bold">USO EPP AUDITIVO</label>
              <select class="form-control form-control-sm" v-model="empleo.uso_epp" @change="emitChange">
                <option value="">-- Seleccionar --</option>
                <option value="Siempre">Siempre</option>
                <option value="Usualmente">Usualmente</option>
                <option value="Ocasionalmente">Ocasionalmente</option>
                <option value="Nunca">Nunca</option>
              </select>
            </div>
          </div>
          <button 
            v-if="localData.antecedentes_laborales.length > 1"
            type="button" 
            class="btn btn-sm btn-outline-danger mt-2"
            @click="eliminarEmpleo(idx)"
          >
            <i class="fas fa-trash mr-1"></i> Eliminar empleo
          </button>
        </div>
        
        <button type="button" class="btn btn-sm btn-outline-primary" @click="agregarEmpleo">
          <i class="fas fa-plus mr-1"></i> Agregar otro empleo
        </button>
      </div>
    </div>

    <!-- SECTION: TIPO DE TRABAJO DE ALTO RIESGO -->
    <div class="card">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-warning mr-2"></i> CLASIFICACIÓN: TIPO DE TRABAJO DE ALTO RIESGO <span class="text-danger">*</span>
        </h5>
      </div>
      <div class="card-body">
        <div class="alert alert-warning">
          <small><i class="fas fa-info-circle mr-1"></i> Seleccione todas las categorías de riesgo que aplican al puesto.</small>
        </div>

        <div class="row">
          <!-- Trabajo de Altura -->
          <div class="col-md-6 mb-3">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData.tipo_trabajo_riesgo.altura" @change="emitChange">
              <span class="custom-control-label">
                <i class="fas fa-mountain mr-1"></i> Trabajo de Altura (≥1.8m)
              </span>
            </label>
            <small class="text-muted d-block ml-4">Andamios, estructuras elevadas, techos</small>
          </div>

          <!-- Espacio Confinado -->
          <div class="col-md-6 mb-3">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData.tipo_trabajo_riesgo.espacio_confinado" @change="emitChange">
              <span class="custom-control-label">
                <i class="fas fa-door-open mr-1"></i> Espacio Confinado
              </span>
            </label>
            <small class="text-muted d-block ml-4">Tanques, tuberías, alcantarillas, sótanos</small>
          </div>

          <!-- Trabajo en Caliente -->
          <div class="col-md-6 mb-3">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData.tipo_trabajo_riesgo.trabajo_caliente" @change="emitChange">
              <span class="custom-control-label">
                <i class="fas fa-fire mr-1"></i> Trabajo en Caliente
              </span>
            </label>
            <small class="text-muted d-block ml-4">Soldadura, corte con oxicorte, hornos</small>
          </div>

          <!-- Trabajo Eléctrico -->
          <div class="col-md-6 mb-3">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData.tipo_trabajo_riesgo.trabajo_electrico" @change="emitChange">
              <span class="custom-control-label">
                <i class="fas fa-bolt mr-1"></i> Trabajo Eléctrico
              </span>
            </label>
            <small class="text-muted d-block ml-4">Alta/baja tensión, instalaciones eléctricas</small>
          </div>

          <!-- Excavación y Zanjas -->
          <div class="col-md-6 mb-3">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData.tipo_trabajo_riesgo.excavacion_zanjas" @change="emitChange">
              <span class="custom-control-label">
                <i class="fas fa-shovel mr-1"></i> Excavación y Zanjas
              </span>
            </label>
            <small class="text-muted d-block ml-4">Excavadoras, motoniveladoras, derrumbes</small>
          </div>

          <!-- Maquinaria Pesada -->
          <div class="col-md-6 mb-3">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData.tipo_trabajo_riesgo.maquinaria_pesada" @change="emitChange">
              <span class="custom-control-label">
                <i class="fas fa-industry mr-1"></i> Manejo Maquinaria Pesada
              </span>
            </label>
            <small class="text-muted d-block ml-4">Grúas, retroexcavadoras, tractores</small>
          </div>

          <!-- Materiales Peligrosos -->
          <div class="col-md-6 mb-3">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData.tipo_trabajo_riesgo.materiales_peligrosos" @change="emitChange">
              <span class="custom-control-label">
                <i class="fas fa-flask mr-1"></i> Manejo Materiales Peligrosos
              </span>
            </label>
            <small class="text-muted d-block ml-4">Químicos, radiactivos, explosivos, tóxicos</small>
          </div>

          <!-- Izaje de Carga -->
          <div class="col-md-6 mb-3">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData.tipo_trabajo_riesgo.izaje_carga" @change="emitChange">
              <span class="custom-control-label">
                <i class="fas fa-cube mr-1"></i> Izaje de Carga
              </span>
            </label>
            <small class="text-muted d-block ml-4">Más de 20 kg, movimientos repetitivos</small>
          </div>
        </div>

        <!-- RESUMEN -->
        <div class="alert alert-info mt-3">
          <strong>✓ Riesgos Seleccionados:</strong>
          <span v-if="riesgosSeleccionados.length === 0" class="text-muted"> Ninguno (revisar si aplica)</span>
          <span v-else class="badge badge-info mr-2" v-for="riesgo in riesgosSeleccionados" :key="riesgo">{{ riesgo }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Hoja1IdentificacionempleoTr',
  props: {
    modelValue: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      localData: {
        nombre: '',
        fecha_nacimiento: '',
        edad: 0,
        genero: '',
        empresa: '',
        numero_empleado: '',
        departamento: '',
        antiguedad_empresa: '',
        antiguedad_puesto: '',
        puesto: '',
        antecedentes_laborales: [
          {
            empresa: '',
            puesto: '',
            tiempo: '',
            exposicion_ruido: '',
            agentes_ototoxicos: '',
            uso_epp: ''
          }
        ],
        tipo_trabajo_riesgo: {
          altura: false,
          espacio_confinado: false,
          trabajo_caliente: false,
          trabajo_electrico: false,
          excavacion_zanjas: false,
          maquinaria_pesada: false,
          materiales_peligrosos: false,
          izaje_carga: false
        }
      }
    }
  },
  computed: {
    riesgosSeleccionados() {
      const riesgos = {
        altura: 'Trabajo de Altura',
        espacio_confinado: 'Espacio Confinado',
        trabajo_caliente: 'Trabajo en Caliente',
        trabajo_electrico: 'Trabajo Eléctrico',
        excavacion_zanjas: 'Excavación y Zanjas',
        maquinaria_pesada: 'Maquinaria Pesada',
        materiales_peligrosos: 'Materiales Peligrosos',
        izaje_carga: 'Izaje de Carga'
      }

      return Object.keys(riesgos)
        .filter(key => this.localData.tipo_trabajo_riesgo[key])
        .map(key => riesgos[key])
    }
  },
  watch: {
    modelValue: {
      handler(newVal) {
        if (newVal && Object.keys(newVal).length > 0) {
          this.localData = JSON.parse(JSON.stringify(newVal))
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
    agregarEmpleo() {
      this.localData.antecedentes_laborales.push({
        empresa: '',
        puesto: '',
        tiempo: '',
        exposicion_ruido: '',
        agentes_ototoxicos: '',
        uso_epp: ''
      })
      this.emitChange()
    },
    eliminarEmpleo(index) {
      this.localData.antecedentes_laborales.splice(index, 1)
      this.emitChange()
    },
    emitChange() {
      this.$emit('update:modelValue', JSON.parse(JSON.stringify(this.localData)))
    }
  },
  mounted() {
    if (this.modelValue && Object.keys(this.modelValue).length > 0) {
      this.localData = JSON.parse(JSON.stringify(this.modelValue))
    }
  }
}
</script>

<style scoped>
.hoja1-identificacionempleo-tr {
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
  border-color: #0c5460;
  box-shadow: 0 0 0 0.2rem rgba(12, 84, 96, 0.25);
}

.custom-control {
  position: relative;
  display: block;
  min-height: 1.5rem;
  padding-left: 1.5rem;
  margin-bottom: 0.75rem;
}

.custom-control-label {
  cursor: pointer;
  font-weight: 500;
  color: #2c3e50;
}

label {
  font-size: 13px;
  margin-bottom: 6px;
  color: #2c3e50;
}

.alert {
  font-size: 12px;
  margin-bottom: 15px;
}

.badge-info {
  background: #17a2b8;
  padding: 4px 8px;
  border-radius: 3px;
  font-size: 11px;
  margin-bottom: 4px;
}

small {
  font-size: 12px;
}
</style>