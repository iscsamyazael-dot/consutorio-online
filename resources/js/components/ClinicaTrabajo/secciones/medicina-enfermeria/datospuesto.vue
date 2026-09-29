<template>
  <div class="datos-puesto-container">
    <!-- HEADER -->
    <div class="section-header">
      <i class="ti ti-user-circle" aria-hidden="true"></i>
      <h2>Datos del paciente y puesto de trabajo</h2>
    </div>

    <!-- CARD: PACIENTE -->
    <div class="card-section">
      <div class="card-header">
        <h3>Información del paciente</h3>
      </div>
      
      <div class="card-body">
        <div class="form-grid-2x2">
          <div class="form-group">
            <label for="nombre">Nombre completo</label>
            <input
              id="nombre"
              v-model="form.paciente_nombre"
              type="text"
              placeholder="Juan Pérez García"
              readonly
              class="input-field"
            />
            <span class="helper-text">Precargado del registro</span>
          </div>

          <div class="form-group">
            <label for="cedula">Cédula / DNI</label>
            <input
              id="cedula"
              v-model="form.paciente_cedula"
              type="text"
              placeholder="12.345.678-0"
              readonly
              class="input-field"
            />
          </div>

          <div class="form-group">
            <label for="edad">Edad</label>
            <input
              id="edad"
              :value="calcularEdad()"
              type="number"
              readonly
              class="input-field"
            />
            <span class="helper-text">Calculada automáticamente</span>
          </div>

          <div class="form-group">
            <label for="sexo">Sexo</label>
            <select v-model="form.paciente_sexo" id="sexo" class="input-field" disabled>
              <option value="">Seleccionar</option>
              <option value="M">Masculino</option>
              <option value="F">Femenino</option>
              <option value="O">Otro</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- CARD: EMPRESA & PUESTO -->
    <div class="card-section">
      <div class="card-header">
        <h3>Empresa y puesto de trabajo</h3>
      </div>

      <div class="card-body">
        <div class="form-grid-2x2">
          <div class="form-group">
            <label for="empresa">Empresa cliente</label>
            <select v-model="form.empresa_cliente_id" id="empresa" class="input-field" @change="onEmpresaChange">
              <option value="">Seleccionar empresa</option>
              <option v-for="emp in empresas" :key="emp.id" :value="emp.id">
                {{ emp.nombre }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label for="puesto">Puesto de trabajo</label>
            <select v-model="form.puesto_trabajo_id" id="puesto" class="input-field">
              <option value="">Seleccionar puesto</option>
              <option v-for="p in puestos" :key="p.id" :value="p.id">
                {{ p.nombre }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label for="area">Área / Departamento</label>
            <input
              id="area"
              v-model="form.area_trabajo"
              type="text"
              placeholder="Producción, Administrativo, etc."
              class="input-field"
            />
          </div>

          <div class="form-group">
            <label for="antiguedad">Antigüedad en puesto</label>
            <div class="input-with-unit">
              <input
                id="antiguedad"
                v-model="form.antiguedad_puesto"
                type="number"
                min="0"
                step="0.1"
                placeholder="12"
                class="input-field"
              />
              <span class="unit">meses</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CARD: TIPO EVALUACIÓN -->
    <div class="card-section">
      <div class="card-header">
        <h3>Tipo y contexto de la evaluación</h3>
      </div>

      <div class="card-body">
        <div class="form-grid-2x2">
          <div class="form-group">
            <label for="tipo">Tipo de evaluación</label>
            <select v-model="form.tipo_evaluacion" id="tipo" class="input-field" required>
              <option value="">Seleccionar tipo</option>
              <option value="ingreso">Ingreso</option>
              <option value="periodica">Periódica</option>
              <option value="seguimiento">Seguimiento</option>
              <option value="extraordinaria">Extraordinaria</option>
            </select>
          </div>

          <div class="form-group">
            <label for="fecha">Fecha de evaluación</label>
            <input
              id="fecha"
              v-model="form.fecha_valoracion"
              type="date"
              :max="hoy"
              class="input-field"
              required
            />
            <span class="helper-text">No puede ser futura</span>
          </div>

          <div class="form-group">
            <label for="medico">Médico responsable</label>
            <select v-model="form.medico_id" id="medico" class="input-field" required>
              <option value="">Seleccionar médico</option>
              <option v-for="m in medicosOcupacionales" :key="m.id" :value="m.id">
                Dr(a). {{ m.nombre }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label for="motivo">Motivo de evaluación</label>
            <input
              id="motivo"
              v-model="form.motivo_evaluacion"
              type="text"
              placeholder="Ej: Incidente, cambio de puesto, rutina"
              class="input-field"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- FOLIO AUTO-GENERADO -->
    <div class="info-box">
      <i class="ti ti-info-circle" aria-hidden="true"></i>
      <div>
        <p class="info-label">Folio generado:</p>
        <p class="info-value">{{ form.folio || 'Se generará al guardar' }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import ApiService from '../../../services/ApiService.js'

export default {
  name: 'DatosPuesto',
  props: {
    modelValue: {
      type: Object,
      required: true
    },
    pacienteData: {
      type: Object,
      default: () => ({})
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      form: this.modelValue,
      empresas: [],
      puestos: [],
      medicosOcupacionales: [],
      hoy: new Date().toISOString().split('T')[0]
    }
  },
  watch: {
    modelValue(newVal) {
      this.form = newVal
    },
    form: {
      handler(newVal) {
        this.$emit('update:modelValue', newVal)
      },
      deep: true
    }
  },
  methods: {
    calcularEdad() {
      if (!this.pacienteData.fecha_nacimiento) return ''
      const hoy = new Date()
      const nacimiento = new Date(this.pacienteData.fecha_nacimiento)
      let edad = hoy.getFullYear() - nacimiento.getFullYear()
      const mes = hoy.getMonth() - nacimiento.getMonth()
      if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--
      }
      return edad
    },
    async cargarDatos() {
      try {
        this.empresas = await ApiService.empresas.lista()
        this.puestos = await ApiService.puestos.lista()
        this.medicosOcupacionales = await ApiService.medicos.lista('ocupacional')
      } catch (error) {
        console.error('Error cargando datos:', error)
      }
    },
    onEmpresaChange() {
      // Opcional: filtrar puestos por empresa si es necesario
    }
  },
  mounted() {
    if (this.pacienteData.nombre) {
      this.form.paciente_nombre = this.pacienteData.nombre
      this.form.paciente_cedula = this.pacienteData.cedula
      this.form.paciente_sexo = this.pacienteData.sexo
    }
    this.cargarDatos()
  }
}
</script>

<style scoped>
.datos-puesto-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 0.5rem;
}

.section-header i {
  font-size: 24px;
  color: #0F6E9F;
}

.section-header h2 {
  font-size: 18px;
  font-weight: 500;
  color: #1F2937;
  margin: 0;
  letter-spacing: 0;
}

.card-section {
  background: #FFFFFF;
  border: 0.5px solid #E5E7EB;
  border-radius: 6px;
  border-left: 3px solid #0F6E9F;
  overflow: hidden;
}

.card-header {
  background: linear-gradient(135deg, #0F6E9F 0%, #0A5A84 100%);
  padding: 12px 20px;
  border-bottom: 1px solid #E5E7EB;
}

.card-header h3 {
  font-size: 14px;
  font-weight: 500;
  color: #FFFFFF;
  margin: 0;
  text-transform: none;
  letter-spacing: 0;
}

.card-body {
  padding: 20px;
}

.form-grid-2x2 {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px 12px;
}

@media (max-width: 768px) {
  .form-grid-2x2 {
    grid-template-columns: 1fr;
    gap: 16px;
  }
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.form-group label {
  font-size: 13px;
  font-weight: 500;
  color: #1F2937;
  margin: 0;
}

.input-field {
  padding: 8px 12px;
  border: 0.5px solid #D1D5DB;
  border-radius: 6px;
  font-size: 14px;
  font-family: inherit;
  color: #1F2937;
  background: #FFFFFF;
  transition: all 200ms ease;
}

.input-field:focus {
  outline: none;
  border-color: #0F6E9F;
  box-shadow: 0 0 0 2px rgba(15, 110, 159, 0.1);
}

.input-field:disabled,
.input-field[readonly] {
  background: #F5F7FA;
  color: #6B7280;
  cursor: not-allowed;
}

.input-with-unit {
  display: flex;
  align-items: center;
  gap: 6px;
}

.input-with-unit .input-field {
  flex: 1;
}

.unit {
  font-size: 13px;
  color: #9CA3AF;
  font-weight: 500;
}

.helper-text {
  font-size: 12px;
  color: #9CA3AF;
  margin: 0;
}

.info-box {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  background: #F0F9FF;
  border: 0.5px solid #BAE6FD;
  border-radius: 6px;
  padding: 12px 16px;
}

.info-box i {
  font-size: 16px;
  color: #0F6E9F;
  margin-top: 2px;
  flex-shrink: 0;
}

.info-label {
  font-size: 12px;
  color: #5F6E7E;
  margin: 0;
  font-weight: 500;
}

.info-value {
  font-size: 14px;
  color: #0F6E9F;
  margin: 4px 0 0 0;
  font-weight: 500;
  font-family: 'Courier New', monospace;
}
</style>