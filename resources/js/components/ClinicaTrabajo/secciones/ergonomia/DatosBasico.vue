<template>
  <div class="seccion-datos-basicos-ergonomia">
    <div class="card mb-4">
      <div class="card-header" style="background: linear-gradient(135deg, #2E8B57 0%, #228B22 100%); color: white;">
        <h3 class="mb-0">
          <i class="fas fa-user mr-2"></i>
          Datos Básicos y Puesto de Trabajo
        </h3>
      </div>
      <div class="card-body">
        <!-- Fila 1: Folio, Tipo de evaluación, Fecha valoración, Hora valoración -->
        <div class="row mb-4">
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold d-block mb-1">Folio *</label>
            <input
              type="text"
              v-model="form.folio"
              class="form-control"
              readonly
              placeholder="Auto-generado al guardar"
            />
            <small class="text-muted">Formato: ERG-YYYY-NNNN</small>
          </div>
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold d-block mb-1">Tipo de Evaluación *</label>
            <select v-model="form.tipo_evaluacion" class="form-control" required>
              <option value="">Seleccionar...</option>
              <option value="ingreso">Ingreso</option>
              <option value="periodica">Periódica</option>
              <option value="seguimiento">Seguimiento</option>
              <option value="extraordinaria">Extraordinaria</option>
              <option value="reingreso">Reingreso</option>
            </select>
          </div>
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold d-block mb-1">Fecha Valoración *</label>
            <input
              type="date"
              v-model="form.fecha_valoracion"
              class="form-control"
              :max="fechaHoy"
              required
            />
          </div>
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold d-block mb-1">Hora Valoración *</label>
            <input
              type="time"
              v-model="form.hora_valoracion"
              class="form-control"
              required
            />
          </div>
        </div>
        <!-- Fila 2: Paciente, Empresa, Puesto de trabajo, Ergónomo -->
        <div class="row mb-4">
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold d-block mb-1">Paciente *</label>
            <select v-model="form.paciente_id" class="form-control" required>
              <option value="">Seleccionar paciente...</option>
              <option v-for="p in pacientes" :key="p.id" :value="p.id">
                {{ p.nombre }} {{ p.apellido_paterno }} {{ p.apellido_materno }}
              </option>
            </select>
          </div>
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold d-block mb-1">Empresa *</label>
            <select v-model="form.empresa_cliente_id" class="form-control" required @change="onEmpresaChange">
              <option value="">Seleccionar empresa...</option>
              <option v-for="e in empresas" :key="e.id" :value="e.id">
                {{ e.nombre || e.razon_social }}
              </option>
            </select>
          </div>
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold d-block mb-1">Puesto de Trabajo *</label>
            <select v-model="form.puesto_trabajo_id" class="form-control" required>
              <option value="">Seleccionar puesto...</option>
              <option v-for="p in puestos" :key="p.id" :value="p.id">
                {{ p.nombre }}
              </option>
            </select>
          </div>
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold d-block mb-1">Ergónomo *</label>
            <select v-model="form.ergonomo_id" class="form-control" required>
              <option value="">Seleccionar ergónomo...</option>
              <option v-for="m in medicos" :key="m.id" :value="m.id">
                {{ m.nombre }} {{ m.apellido_paterno }}
              </option>
            </select>
          </div>
        </div>
        <!-- Info paciente seleccionado -->
        <div v-if="form.paciente_id" class="mt-4 p-3" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;">
          <div class="row">
            <div class="col-md-4">
              <span class="font-weight-bold">Edad: </span>
              {{ calcularEdad(pacienteSeleccionado?.fecha_nacimiento) }} años
            </div>
            <div class="col-md-4">
              <span class="font-weight-bold">Puesto: </span>
              {{ puestoSeleccionado?.nombre }}
            </div>
            <div class="col-md-4">
              <span class="font-weight-bold">Empresa: </span>
              {{ empresaSeleccionada?.nombre || empresaSeleccionada?.razon_social }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { clinicaTrabajo } from '../../../../services/ApiService'

export default {
  name: 'DatosBasicoErgonomia',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      pacientes: [],
      empresas: [],
      puestos: [],
      medicos: [],
      fechaHoy: new Date().toISOString().split('T')[0]
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },
    pacienteSeleccionado() {
      return this.pacientes.find(p => p.id === this.form.paciente_id)
    },
    empresaSeleccionada() {
      return this.empresas.find(e => e.id === this.form.empresa_cliente_id)
    },
    puestoSeleccionado() {
      return this.puestos.find(p => p.id === this.form.puesto_trabajo_id)
    }
  },
  methods: {
    updateField(key, value) {
      this.$emit('update:modelValue', { ...this.form, [key]: value })
    },
    async cargarPacientes() {
      try {
        const response = await clinicaTrabajo.pacientes.lista()
        this.pacientes = response.data || response
      } catch (error) {
        console.error('Error cargando pacientes:', error)
      }
    },
    async cargarEmpresas() {
      try {
        const response = await clinicaTrabajo.empresas.lista()
        this.empresas = response.data || response
      } catch (error) {
        console.error('Error cargando empresas:', error)
      }
    },
    async cargarPuestos() {
      if (!this.form.empresa_cliente_id) {
        this.puestos = []
        return
      }
      try {
        const response = await clinicaTrabajo.puestos.lista()
        this.puestos = response.data || response
      } catch (error) {
        console.error('Error cargando puestos:', error)
      }
    },
    async cargarMedicos() {
      try {
        const response = await clinicaTrabajo.medicos.lista()
        this.medicos = response.data || response
      } catch (error) {
        console.error('Error cargando médicos:', error)
      }
    },
    onEmpresaChange() {
      this.updateField('puesto_trabajo_id', null)
      this.cargarPuestos()
    },
    calcularEdad(fechaNacimiento) {
      if (!fechaNacimiento) return 0
      const hoy = new Date()
      const nacimiento = new Date(fechaNacimiento)
      let edad = hoy.getFullYear() - nacimiento.getFullYear()
      const mes = hoy.getMonth() - nacimiento.getMonth()
      if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) edad--
      return edad
    }
  },
  mounted() {
    this.cargarPacientes()
    this.cargarEmpresas()
    this.cargarMedicos()
    if (!this.form.folio) {
      const año = new Date().getFullYear()
      this.form.folio = `ERG-${año}-****`
    }
  }
}
</script>

<style scoped>
.seccion-datos-basicos-ergonomia {
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

.font-weight-bold {
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  color: #2c3e50;
}

.text-muted {
  color: #6c757d !important;
}
</style>