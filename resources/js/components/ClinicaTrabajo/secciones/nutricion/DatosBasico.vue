<template>
  <div class="seccion-datos-basicos-nutricion">
    <!-- SECTION: DATOS BÁSICOS Y EMPRESA -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
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
            <small class="text-muted">Formato: NUT-YYYY-NNNN</small>
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
              <option value="seguimiento">Seguimiento</option>
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

        <!-- PACIENTE, EMPRESA, PUESTO, NUTRIÓLOGO -->
        <div class="row mb-4">
          <!-- PACIENTE -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Paciente *</label>
            <select class="form-control"
                    :value="form.paciente_id"
                    @change="onPacienteChange($event.target.value)"
                    style="height: 38px;"
                    required
            >
              <option value="">Seleccionar paciente...</option>
              <option v-for="p in pacientes" :key="p.id" :value="p.id">
                {{ p.nombre }} {{ p.apellido_paterno }} {{ p.apellido_materno }}
              </option>
            </select>
          </div>

          <!-- EMPRESA -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Empresa *</label>
            <select class="form-control"
                    :value="form.empresa_cliente_id"
                    @change="onEmpresaChange($event.target.value)"
                    style="height: 38px;"
                    required
            >
              <option value="">Seleccionar empresa...</option>
              <option v-for="e in empresas" :key="e.id" :value="e.id">
                {{ e.nombre || e.razon_social }}
              </option>
            </select>
          </div>

          <!-- PUESTO DE TRABAJO -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Puesto de Trabajo</label>
            <select class="form-control"
                    :value="form.puesto_trabajo_id"
                    @change="onSelectId('puesto_trabajo_id', puestos, $event.target.value)"
                    style="height: 38px;"
            >
              <option value="">Seleccionar puesto...</option>
              <option v-for="p in puestos" :key="p.id" :value="p.id">
                {{ p.nombre }}
              </option>
            </select>
          </div>

          <!-- NUTRIÓLOGO -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Nutriólogo *</label>
            <select class="form-control"
                    :value="form.nutriologo_id"
                    @change="onSelectId('nutriologo_id', medicos, $event.target.value)"
                    style="height: 38px;"
                    required
            >
              <option value="">Seleccionar nutriólogo...</option>
              <option v-for="m in medicos" :key="m.id" :value="m.id">
                {{ m.nombre }} {{ m.apellido_paterno }}
              </option>
            </select>
          </div>
        </div>

        <!-- INFO PACIENTE SELECCIONADO -->
        <div v-if="form.paciente_id" class="mt-4 p-3 bg-light border border-left border-success">
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
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ApiService from '../../../../services/ApiService'

// AJUSTA AQUÍ las rutas según `php artisan route:list`.
// /empresas dio 404, así que probablemente cuelga de /clinica o /api.
const RUTAS = {
  pacientes: '/pacientes',
  empresas: '/empresas',
  medicos: '/medicos',
  puestos: (empresaId) => `/empresas/${empresaId}/puestos`
}

export default {
  name: 'DatosBasicoNutricion',
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
    // Emite un solo cambio con uno o varios campos (evita perder cambios por objetos desactualizados)
    updateFields(cambios) {
      this.$emit('update:modelValue', { ...this.form, ...cambios })
    },
    updateField(key, value) {
      this.updateFields({ [key]: value })
    },

    // Laravel puede devolver [], { data: [] }, { lista: [] } o paginado { data: { data: [] } }
    extraerLista(res) {
      const body = res?.data ?? res
      if (Array.isArray(body)) return body
      if (Array.isArray(body?.data)) return body.data
      if (Array.isArray(body?.lista)) return body.lista
      return []
    },

    // Los <select> devuelven string; recupera el id original (número o texto) desde la lista
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
        // Bioquímica lee form.paciente.genero para los rangos por sexo
        paciente: p ? { id: p.id, genero: p.genero } : null
      })
    },
    onEmpresaChange(value) {
      const id = this.idDesdeLista(this.empresas, value)
      this.updateFields({ empresa_cliente_id: id, puesto_trabajo_id: null })
      this.cargarPuestos(id)
    },

    async cargarPacientes() {
      try {
        this.pacientes = this.extraerLista(await ApiService.get(RUTAS.pacientes))
      } catch (error) {
        console.error('Error cargando pacientes:', error)
      }
    },
    async cargarEmpresas() {
      try {
        this.empresas = this.extraerLista(await ApiService.get(RUTAS.empresas))
      } catch (error) {
        console.error('Error cargando empresas:', error)
      }
    },
    async cargarPuestos(empresaId = this.form.empresa_cliente_id) {
      if (!empresaId) {
        this.puestos = []
        return
      }
      try {
        this.puestos = this.extraerLista(await ApiService.get(RUTAS.puestos(empresaId)))
      } catch (error) {
        console.error('Error cargando puestos:', error)
      }
    },
    async cargarMedicos() {
      try {
        this.medicos = this.extraerLista(await ApiService.get(RUTAS.medicos))
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
    this.cargarPacientes()
    this.cargarEmpresas()
    this.cargarMedicos()
    if (this.form.empresa_cliente_id) this.cargarPuestos()
    // Folio temporal si es una valoración nueva
    if (!this.form.folio) {
      this.updateField('folio', `NUT-${new Date().getFullYear()}-****`)
    }
  }
}
</script>

<style scoped>
.seccion-datos-basicos-nutricion {
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