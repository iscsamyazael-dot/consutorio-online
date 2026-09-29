<template>
  <div class="seccion-datos-basicos">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-user text-blue-600"></i>
        Datos Básicos de la Valoración
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Folio -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Folio *</label>
          <input
            type="text"
            v-model="form.folio"
            class="input-field w-full bg-gray-50"
            readonly
            placeholder="Auto-generado al guardar"
          />
          <p class="text-xs text-gray-500 mt-1">Formato: PSI-YYYY-NNNN</p>
        </div>

        <!-- Tipo de evaluación -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Tipo de Evaluación *</label>
          <select v-model="form.tipo_evaluacion" class="input-field w-full" required>
            <option value="">Seleccionar...</option>
            <option value="ingreso">Ingreso</option>
            <option value="periodica">Periódica</option>
            <option value="seguimiento_trauma">Seguimiento Trauma</option>
            <option value="extraordinaria">Extraordinaria</option>
          </select>
        </div>

        <!-- Fecha valoración -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha Valoración *</label>
          <input
            type="date"
            v-model="form.fecha_valoracion"
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
            v-model="form.hora_valoracion"
            class="input-field w-full"
          />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
        <!-- Paciente -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Paciente *</label>
          <select v-model="form.paciente_id" class="input-field w-full" required>
            <option value="">Seleccionar paciente...</option>
            <option v-for="p in pacientes" :key="p.id" :value="p.id">
              {{ p.nombre }} {{ p.apellido_paterno }} {{ p.apellido_materno }}
            </option>
          </select>
        </div>

        <!-- Empresa -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Empresa *</label>
          <select v-model="form.empresa_cliente_id" class="input-field w-full" required @change="onEmpresaChange">
            <option value="">Seleccionar empresa...</option>
            <option v-for="e in empresas" :key="e.id" :value="e.id">
              {{ e.nombre || e.razon_social }}
            </option>
          </select>
        </div>

        <!-- Puesto de trabajo -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Puesto de Trabajo</label>
          <select v-model="form.puesto_trabajo_id" class="input-field w-full">
            <option value="">Seleccionar puesto...</option>
            <option v-for="p in puestos" :key="p.id" :value="p.id">
              {{ p.nombre }}
            </option>
          </select>
        </div>

        <!-- Psicólogo -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Psicólogo *</label>
          <select v-model="form.psicologo_id" class="input-field w-full" required>
            <option value="">Seleccionar psicólogo...</option>
            <option v-for="m in medicos" :key="m.id" :value="m.id">
              {{ m.nombre }} {{ m.apellido_paterno }}
            </option>
          </select>
        </div>
      </div>

      <!-- Info paciente seleccionado -->
      <div v-if="form.paciente_id" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
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
import ApiService from '../../../services/ApiService.js'

export default {
  name: 'DatosBásicoPsicologia',
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
    updateField(key, value) {
      this.$emit('update:modelValue', { ...this.form, [key]: value })
    },
    async cargarPacientes() {
      try {
        const response = await ApiService.pacientes.lista()
        this.pacientes = response.data || response
      } catch (error) {
        console.error('Error cargando pacientes:', error)
      }
    },
    async cargarEmpresas() {
      try {
        const response = await ApiService.empresas.lista()
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
        const response = await ApiService.puestos.lista(this.form.empresa_cliente_id)
        this.puestos = response.data || response
      } catch (error) {
        console.error('Error cargando puestos:', error)
      }
    },
    async cargarMedicos() {
      try {
        const response = await ApiService.medicos.lista()
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
    // Generar folio temporal si es nueva
    if (!this.form.folio) {
      const año = new Date().getFullYear()
      this.form.folio = `PSI-${año}-****`
    }
  }
}
</script>

<style scoped>
/* Estilos heredados de ValoracionInteligente */
</style>