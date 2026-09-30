<template>
  <div class="seccion-datos-basicos-nutricion">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-user text-green-600"></i>
        Datos Básicos y Empresa
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Folio -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Folio *</label>
          <input
            type="text"
            :value="form.folio"
            class="input-field w-full bg-gray-50"
            readonly
            placeholder="Auto-generado al guardar"
          />
          <p class="text-xs text-gray-500 mt-1">Formato: NUT-YYYY-NNNN</p>
        </div>

        <!-- Tipo de evaluación -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Tipo de Evaluación *</label>
          <select
            :value="form.tipo_evaluacion"
            @change="updateField('tipo_evaluacion', $event.target.value)"
            class="input-field w-full"
            required
          >
            <option value="">Seleccionar...</option>
            <option value="ingreso">Ingreso</option>
            <option value="periodica">Periódica</option>
            <option value="seguimiento">Seguimiento</option>
            <option value="extraordinaria">Extraordinaria</option>
          </select>
        </div>

        <!-- Fecha valoración -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha Valoración *</label>
          <input
            type="date"
            :value="form.fecha_valoracion"
            @input="updateField('fecha_valoracion', $event.target.value)"
            class="input-field w-full"
            :max="fechaHoy"
            required
          />
        </div>

        <!-- Hora valoración -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Hora</label>
          <input
            type="time"
            :value="form.hora_valoracion"
            @input="updateField('hora_valoracion', $event.target.value)"
            class="input-field w-full"
          />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
        <!-- Paciente -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Paciente *</label>
          <select :value="form.paciente_id" @change="onPacienteChange($event.target.value)" class="input-field w-full" required>
            <option value="">Seleccionar paciente...</option>
            <option v-for="p in pacientes" :key="p.id" :value="p.id">
              {{ p.nombre }} {{ p.apellido_paterno }} {{ p.apellido_materno }}
            </option>
          </select>
        </div>

        <!-- Empresa -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Empresa *</label>
          <select :value="form.empresa_cliente_id" @change="onEmpresaChange($event.target.value)" class="input-field w-full" required>
            <option value="">Seleccionar empresa...</option>
            <option v-for="e in empresas" :key="e.id" :value="e.id">
              {{ e.nombre || e.razon_social }}
            </option>
          </select>
        </div>

        <!-- Puesto de trabajo -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Puesto de Trabajo</label>
          <select :value="form.puesto_trabajo_id" @change="onSelectId('puesto_trabajo_id', puestos, $event.target.value)" class="input-field w-full">
            <option value="">Seleccionar puesto...</option>
            <option v-for="p in puestos" :key="p.id" :value="p.id">
              {{ p.nombre }}
            </option>
          </select>
        </div>

        <!-- Nutriólogo -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Nutriólogo *</label>
          <select :value="form.nutriologo_id" @change="onSelectId('nutriologo_id', medicos, $event.target.value)" class="input-field w-full" required>
            <option value="">Seleccionar nutriólogo...</option>
            <option v-for="m in medicos" :key="m.id" :value="m.id">
              {{ m.nombre }} {{ m.apellido_paterno }}
            </option>
          </select>
        </div>
      </div>

      <!-- Info paciente seleccionado -->
      <div v-if="form.paciente_id" class="mt-4 p-3 bg-green-50 border border-green-200 rounded">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-sm">
          <div>
            <span class="font-semibold text-gray-600">Edad: </span>
            {{ calcularEdad(pacienteSeleccionado?.fecha_nacimiento) }} años
          </div>
          <div>
            <span class="font-semibold text-gray-600">Género: </span>
            {{ pacienteSeleccionado?.genero }}
          </div>
          <div>
            <span class="font-semibold text-gray-600">Empresa: </span>
            {{ empresaSeleccionada?.nombre || empresaSeleccionada?.razon_social }}
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
/* Estilos heredados de ValoracionInteligente */
</style>