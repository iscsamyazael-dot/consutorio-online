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
              :value="form.folio"
              class="form-control"
              readonly
              placeholder="Auto-generado"
            />
            <small class="text-muted">Formato: ERG-YYYY-NNNN</small>
          </div>
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold d-block mb-1">Tipo de Evaluación *</label>
            <select :value="form.tipo_evaluacion" @change="updateField('tipo_evaluacion', $event.target.value)" class="form-control" required>
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
              :value="form.fecha_valoracion"
              @input="updateField('fecha_valoracion', $event.target.value)"
              class="form-control"
              :max="fechaHoy"
              required
            />
          </div>
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold d-block mb-1">Hora Valoración *</label>
            <input
              type="time"
              :value="form.hora_valoracion"
              @input="updateField('hora_valoracion', $event.target.value)"
              class="form-control"
              required
            />
          </div>
        </div>
        
        <!-- Fila 2: Paciente, Empresa, Puesto de trabajo, Ergónomo -->
        <div class="row mb-4">
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold d-block mb-1">Paciente *</label>
            <input 
              type="text" 
              :value="form.nombre_paciente" 
              class="form-control" 
            />
          </div>
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold d-block mb-1">Empresa *</label>
            <input 
              type="text" 
              :value="form.nombre_empresa" 
              class="form-control" 
            />
          </div>
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold d-block mb-1">Puesto de Trabajo *</label>
            <input 
              type="text" 
              :value="form.nombre_puesto" 
              class="form-control" 
            />
          </div>
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold d-block mb-1">Ergónomo *</label>
            <select :value="form.ergonomo_id" @change="updateField('ergonomo_id', $event.target.value)" class="form-control" required>
              <option value="">Seleccionar ergónomo...</option>
              <option v-for="m in medicos" :key="m.id" :value="m.id">
                {{ m.nombre }} {{ m.apellido_paterno }}
              </option>
            </select>
          </div>
        </div>

        <!-- Info de respaldo (IDs) -->
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
      medicos: [],
      fechaHoy: new Date().toISOString().split('T')[0]
    }
  },
  computed: {
    form() {
      return this.modelValue
    }
  },
  methods: {
    updateField(key, value) {
      this.$emit('update:modelValue', { ...this.form, [key]: value })
    },
    async cargarMedicos() {
      try {
        const response = await clinicaTrabajo.get('medicos');
        this.medicos = response.data || response
      } catch (error) {
        console.error('Error cargando médicos:', error)
      }
    }
  },
  mounted() {
    this.cargarMedicos()
    
    // Generar folio de forma reactiva si no existe
    if (!this.form.folio) {
      const año = new Date().getFullYear()
      this.updateField('folio', `ERG-${año}-****`)
    }
  },
  // watch: {
  //   'form.paciente_id'(newVal) {
  //     if (newVal && this.pacientes.length > 0) {
  //       const paciente = this.pacientes.find(p => p.id === newVal)
  //       if (paciente) {
  //         this.$emit('update:modelValue', {
  //           ...this.form,
  //           nombre_paciente: paciente.nombre || '',
  //           nombre_empresa: paciente.empresa || '',
  //           nombre_puesto: paciente.puesto || ''
  //         })
  //       }
  //     }
  //   }
  // }
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
  background-color: #fff;
}
.form-control[readonly] {
  background-color: #e9ecef;
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