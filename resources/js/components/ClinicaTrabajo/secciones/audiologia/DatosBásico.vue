<template>
  <div class="seccion-datos-basicos-audiologia">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-user text-indigo-600"></i>
        Datos Básicos y Puesto de Trabajo
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
          <p class="text-xs text-gray-500 mt-1">Formato: AUD-YYYY-NNNN</p>
        </div>

        <!-- Tipo de evaluación -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Tipo de Evaluación *</label>
          <select v-model="form.tipo_evaluacion" class="input-field w-full" required>
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
          <label class="block text-sm font-semibold text-gray-700 mb-1">Puesto de Trabajo *</label>
          <select v-model="form.puesto_trabajo_id" class="input-field w-full" required>
            <option value="">Seleccionar puesto...</option>
            <option v-for="p in puestos" :key="p.id" :value="p.id">
              {{ p.nombre }}
            </option>
          </select>
        </div>

        <!-- Audiólogo -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Audiólogo *</label>
          <select v-model="form.audiologo_id" class="input-field w-full" required>
            <option value="">Seleccionar audiólogo...</option>
            <option v-for="m in medicos" :key="m.id" :value="m.id">
              {{ m.nombre }} {{ m.apellido_paterno }}
            </option>
          </select>
        </div>
      </div>

      <!-- Exposición a ruido actual -->
      <div class="mt-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-volume text-indigo-600"></i>
          Exposición a Ruido Laboral Actual
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nivel de Exposición a Ruido *</label>
            <select v-model="form.exposicion_ruido_actual" class="input-field w-full" required>
              <option value="">Seleccionar...</option>
              <option value="<85">< 85 dB(A)</option>
              <option value="85-90">85-90 dB(A)</option>
              <option value="90-95">90-95 dB(A)</option>
              <option value="95-100">95-100 dB(A)</option>
              <option value=">100">> 100 dB(A)</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Años de Exposición</label>
            <input
              type="number"
              v-model.number="form.anos_exposicion_ruido"
              @input="updateField('anos_exposicion_ruido', $event.target.value)"
              min="0"
              max="50"
              class="input-field w-full"
              placeholder="Años"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Uso de Protector Auditivo</label>
            <select v-model="form.uso_protector_auditivo" class="input-field w-full">
              <option value="">Seleccionar...</option>
              <option value="siempre">Siempre</option>
              <option value="frecuentemente">Frecuentemente</option>
              <option value="ocasionalmente">Ocasionalmente</option>
              <option value="nunca">Nunca</option>
              <option value="no_aplica">No aplica</option>
            </select>
          </div>
        </div>

        <!-- Exposiciones químicas -->
        <div class="mt-4 pt-4 border-t border-gray-200">
          <h4 class="font-semibold text-gray-700 mb-2">Exposiciones Químicas Adicionales</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
            <label
              v-for="exp in exposicionesQuimicas"
              :key="exp.key"
              class="flex items-center gap-2 cursor-pointer"
            >
              <input
                type="checkbox"
                :checked="form[exp.key]"
                @change="updateField(exp.key, $event.target.checked)"
                class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
              />
              <span class="text-sm text-gray-700">{{ exp.label }}</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Info paciente seleccionado -->
      <div v-if="form.paciente_id" class="mt-4 p-3 bg-indigo-50 border border-indigo-200 rounded">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-sm">
          <div>
            <span class="font-semibold text-gray-600">Edad: </span>
            {{ calcularEdad(pacienteSeleccionado?.fecha_nacimiento) }} años
          </div>
          <div>
            <span class="font-semibold text-gray-600">Puesto: </span>
            {{ puestoSeleccionado?.nombre }}
          </div>
          <div>
            <span class="font-semibold text-gray-600">Ruido: </span>
            {{ form.exposicion_ruido_actual || '—' }} | Protector: {{ form.uso_protector_auditivo || '—' }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ApiService from '../../../services/ApiService.js'

export default {
  name: 'DatosBásicoAudiologia',
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
      fechaHoy: new Date().toISOString().split('T')[0],
      exposicionesQuimicas: [
        { key: 'disolventes_exposicion', label: 'Disolventes' },
        { key: 'metales_exposicion', label: 'Metales pesados' },
        { key: 'gases_exposicion', label: 'Gases' },
        { key: 'sales_exposicion', label: 'Sales' },
        { key: 'tabaco_exposicion', label: 'Tabaco' }
      ]
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
    if (!this.form.folio) {
      const año = new Date().getFullYear()
      this.form.folio = `AUD-${año}-****`
    }
  }
}
</script>

<style scoped>
/* Estilos heredados */
</style>