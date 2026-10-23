<template>
  <div class="seccion-datos-basicos-psicologia">
    <!-- SECTION: DATOS BÁSICOS Y EMPRESA -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-user mr-2"></i> DATOS BÁSICOS Y EMPRESA
        </h5>
      </div>
      <div class="card-body">
        <div class="row mb-4">
          <!-- FOLIO -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Folio *</label>
            <input
              type="text"
              class="form-control"
              :value="form.folio"
              readonly
              placeholder="Auto-generado al guardar"
              style="height: 38px; background-color: #e9ecef;"
            >
            <small class="text-muted">Formato: PSI-YYYY-NNNN</small>
          </div>

          <!-- TIPO DE EVALUACIÓN -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Tipo de Evaluación *</label>
            <select
              class="form-control"
              :value="form.tipo_evaluacion"
              @change="updateField('tipo_evaluacion', $event.target.value)"
              style="height: 38px;"
              required
            >
              <option value="">Seleccionar...</option>
              <option value="ingreso">Ingreso</option>
              <option value="periodica">Periódica</option>
              <option value="seguimiento_trauma">Seguimiento Trauma</option>
              <option value="extraordinaria">Extraordinaria</option>
            </select>
          </div>

          <!-- FECHA VALORACIÓN -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Fecha Valoración *</label>
            <input
              type="date"
              class="form-control"
              :value="form.fecha_valoracion"
              @input="updateField('fecha_valoracion', $event.target.value)"
              :max="fechaHoy"
              style="height: 38px;"
              required
            >
          </div>

          <!-- HORA VALORACIÓN -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Hora</label>
            <input
              type="time"
              class="form-control"
              :value="form.hora_valoracion"
              @input="updateField('hora_valoracion', $event.target.value)"
              style="height: 38px;"
            >
          </div>
        </div>

        <!-- PACIENTE, EMPRESA, PUESTO, PSICÓLOGO -->
        <div class="row mb-4">
          <!-- PACIENTE -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Paciente *</label>
            <input 
              type="text" 
              :value="form.nombre_paciente" 
              class="form-control" 
            />
          </div>

          <!-- EMPRESA -->
          <div class="col-md-3 mb-3">
              <label class="font-weight-bold">Empresa *</label>
              <input 
                type="text" 
                :value="form.nombre_empresa" 
                class="form-control" 
              />
          </div>

          <!-- PUESTO DE TRABAJO -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Puesto de Trabajo</label>
            <input 
              type="text" 
              :value="form.nombre_puesto" 
              class="form-control" 
            />
          </div>

          <!-- PSICÓLOGO -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Psicólogo *</label>
            <select class="form-control"
                    :value="form.psicologo_id"
                    @change="onSelectId('psicologo_id', medicos, $event.target.value)"
                    style="height: 38px;"
                    required
            >
              <option value="">Seleccionar psicólogo...</option>
              <option v-for="m in medicos" :key="m.id" :value="m.id">
                {{ m.nombre }} {{ m.apellido_paterno }}
              </option>
            </select>
          </div>
        </div>

        <!-- INFO PACIENTE SELECCIONADO -->
        <!-- <div v-if="form.paciente_id" class="mt-4 p-3 bg-light border border-left border-success">
          <div class="row">
            <div class="col-md-4">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Edad:</span>
                <span class="font-weight-bold">{{ calcularEdad(pacienteSeleccionado?.fecha_nacimiento) }} años</span>
              </div>
            </div>
            <div class="col-md-4">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Género:</span>
                <span class="font-weight-bold">{{ pacienteSeleccionado?.genero }}</span>
              </div>
            </div>
            <div class="col-md-4">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Empresa:</span>
                <span class="font-weight-bold">{{ empresaSeleccionada?.nombre || empresaSeleccionada?.razon_social }}</span>
              </div>
            </div>
          </div>
        </div> -->
      </div>
    </div>
  </div>
</template>

<script>
import clinicaTrabajo from '@/services/ApiServiceClinicaTrabajo'

const RUTAS = {
  pacientes: '/pacientes',
  empresas: '/empresas-cliente',
  medicos: '/medicos',
  puestos: (empresaId) => `/empresas-cliente/${empresaId}/puestos`
}

export default {
  name: 'DatosBasicoPsicologia',
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
    }
  },
  methods: {
    updateFields(cambios) {
      this.$emit('update:modelValue', { ...this.form, ...cambios })
    },
    updateField(key, value) {
      this.updateFields({ [key]: value })
    },

    extraerLista(res) {
      const body = res?.data ?? res
      if (Array.isArray(body)) return body
      if (Array.isArray(body?.data)) return body.data
      if (Array.isArray(body?.lista)) return body.lista
      return []
    },

    idDesdeLista(lista, value) {
      if (value === '' || value == null) return null
      const item = lista.find(x => String(x.id) === String(value))
      return item ? item.id : null
    },
    onSelectId(key, lista, value) {
      this.updateField(key, this.idDesdeLista(lista, value))
    },

    onPacienteChange(value) {
      const id = this.idDesdeLista(this.pacientes, value)
      const p = this.pacientes.find(x => x.id === id)
      this.updateFields({
        paciente_id: id,
        paciente: p ? { id: p.id, genero: p.genero, fecha_nacimiento: p.fecha_nacimiento } : null
      })
    },
    onEmpresaChange(value) {
      const id = this.idDesdeLista(this.empresas, value)
      this.updateFields({ empresa_cliente_id: id, puesto_trabajo_id: null })
      this.cargarPuestos(id)
    },
    async cargarMedicos() {
      try {
        // Ajusta la ruta si tu endpoint de médicos es diferente
        const response = await clinicaTrabajo.get('medicos')
        this.medicos = response.data?.data || response.data || []
      } catch (error) {
        console.error('Error cargando médicos:', error)
      }
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
    this.cargarMedicos()
    if (this.form.empresa_cliente_id) this.cargarPuestos()
    // Folio temporal si es una valoración nueva
    if (!this.form.folio) {
      this.updateField('folio', `PSI-${new Date().getFullYear()}-****`)
    }
  }
}
</script>

<style scoped>
.seccion-datos-basicos-psicologia {
  background: #f8f9fa;
}

.card {
  border: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.card-header {
  background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%) !important;
  color: white !important;
}

.form-control {
  border-radius: 4px;
  border: 1px solid #ced4da;
  font-size: 14px;
  padding: 10px 12px;
}

.form-control:focus {
  border-color: #6f42c1;
  box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
}

.label {
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  color: #2c3e50;
}

.text-muted {
  color: #6c757d !important;
}

.border-left {
  border-left: 4px solid !important;
}

.border-success {
  border-color: #28a745 !important;
}

.border-left.border-success {
  border-left-color: #28a745 !important;
}

.bg-light {
  background-color: #f8f9fa !important;
}
</style>