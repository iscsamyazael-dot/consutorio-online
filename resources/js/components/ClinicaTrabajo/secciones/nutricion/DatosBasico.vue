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
          <!-- PACIENTE (READONLY) -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Paciente *</label>
            <input 
              type="text" 
              :value="form.nombre_paciente" 
              class="form-control" 
            />
          </div>
          
          <!-- EMPRESA (READONLY) -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Empresa *</label>
            <input 
              type="text" 
              :value="form.nombre_empresa" 
              class="form-control" 
            />
          </div>
          
          <!-- PUESTO DE TRABAJO (READONLY) -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Puesto de Trabajo</label>
            <input 
              type="text" 
              :value="form.nombre_puesto" 
              class="form-control" 
            />
          </div>
          
          <!-- NUTRIÓLOGO (SELECT) -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">Nutriólogo *</label>
            <select 
              class="form-control"
              :value="form.nutriologo_id"
              @change="updateField('nutriologo_id', $event.target.value)"
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

        <!-- INFO DE RESPALDO (IDs) -->
        <!-- <div v-if="form.paciente_id" class="mt-4 p-3" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;">
          <div class="row text-muted" style="font-size: 0.85rem;">
            <div class="col-md-4">
              <span class="font-weight-bold">ID Paciente:</span> {{ form.paciente_id }}
            </div>
            <div class="col-md-4">
              <span class="font-weight-bold">ID Empresa:</span> {{ form.empresa_cliente_id }}
            </div>
            <div class="col-md-4">
              <span class="font-weight-bold">ID Puesto:</span> {{ form.puesto_trabajo_id }}
            </div>
          </div>
        </div> -->
      </div>
    </div>
  </div>
</template>

<script>
import clinicaTrabajo from '@/services/ApiServiceClinicaTrabajo'

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
      medicos: [],
      fechaHoy: new Date().toISOString().split('T')[0]
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    }
  },
  methods: {
    updateField(key, value) {
      this.$emit('update:modelValue', { ...this.form, [key]: value })
    },
    async cargarMedicos() {
      try {
        // Ajusta la ruta si tu endpoint de médicos es diferente
        const response = await clinicaTrabajo.get('medicos')
        this.medicos = response.data?.data || response.data || []
      } catch (error) {
        console.error('Error cargando médicos:', error)
      }
    }
  },
  mounted() {
    this.cargarMedicos()
    
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

.form-control[readonly] {
  background-color: #e9ecef !important;
  cursor: not-allowed;
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