<template>
  <div class="tab-section">
    <!-- MODO ALTA -->
    <div v-if="modo === 'alta'">
      <div class="section-header">
        <div class="section-icon bg-primary">
          <i class="fas fa-briefcase"></i>
        </div>
        <div class="section-title">
          <h3>Asignación Laboral</h3>
          <p>Datos de la empresa y puesto de trabajo</p>
        </div>
      </div>

      <div v-if="empresas.length === 0" class="alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
          <strong>No hay empresas registradas</strong>
          <p>Primero debe registrar empresas cliente para poder asignar pacientes.</p>
        </div>
      </div>

      <div v-else>
        <div class="form-grid">
          <div class="form-group full-width">
            <label class="form-label">
              <i class="fas fa-building"></i>
              Empresa cliente <span class="required">*</span>
            </label>
            <div class="input-wrapper">
              <select v-model="form.empresa_cliente_id" class="form-input" @change="cargarPuestos">
                <option value="">Seleccionar empresa...</option>
                <option v-for="empresa in empresas" :key="empresa.id" :value="empresa.id">
                  {{ empresa.razon_social }}
                </option>
              </select>
            </div>
          </div>

          <div class="form-group full-width">
            <label class="form-label">
              <i class="fas fa-user-tie"></i>
              Puesto de trabajo
            </label>
            <div class="input-wrapper">
              <select v-model="form.puesto_trabajo_id" class="form-input" :disabled="!form.empresa_cliente_id || puestos.length === 0">
                <option value="">{{ !form.empresa_cliente_id ? 'Primero selecciona una empresa' : (puestos.length === 0 ? 'La empresa no tiene puestos registrados' : 'Seleccionar puesto...') }}</option>
                <option v-for="puesto in puestos" :key="puesto.id" :value="puesto.id">
                  {{ puesto.nombre }}
                </option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-id-card"></i>
              Número de empleado
            </label>
            <div class="input-wrapper">
              <input type="text" v-model="form.numero_empleado" maxlength="50" class="form-input" placeholder="Opcional">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-calendar-alt"></i>
              Fecha ingreso empresa
            </label>
            <div class="input-wrapper">
              <input type="date" v-model="form.fecha_ingreso_empresa" class="form-input">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-file-contract"></i>
              Tipo de contrato
            </label>
            <div class="input-wrapper">
              <select v-model="form.tipo_contrato" class="form-input">
                <option value="">Seleccionar...</option>
                <option value="Indefinido">Indefinido</option>
                <option value="Por tiempo determinado">Por tiempo determinado</option>
                <option value="Por proyecto">Por proyecto</option>
                <option value="Pasantía">Pasantía</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-clock"></i>
              Turno
            </label>
            <div class="input-wrapper">
              <select v-model="form.turno" class="form-input">
                <option value="">Seleccionar...</option>
                <option value="matutino">Matutino</option>
                <option value="vespertino">Vespertino</option>
                <option value="nocturno">Nocturno</option>
                <option value="mixto">Mixto</option>
                <option value="rotativo">Rotativo</option>
              </select>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- MODO EDICIÓN -->
    <div v-else>
      <div class="section-header">
        <div class="section-icon bg-success">
          <i class="fas fa-list-alt"></i>
        </div>
        <div class="section-title">
          <h3>Asignaciones Actuales</h3>
          <p>Historial de asignaciones laborales del paciente</p>
        </div>
      </div>

      <div v-if="asignaciones.length === 0" class="alert-info">
        <i class="fas fa-info-circle"></i>
        <span>No tiene asignaciones laborales registradas.</span>
      </div>

      <div v-else class="table-container">
        <table class="custom-table">
          <thead>
            <tr>
              <th><i class="fas fa-building"></i> Empresa</th>
              <th><i class="fas fa-user-tie"></i> Puesto</th>
              <th><i class="fas fa-id-card"></i> Empleado</th>
              <th><i class="fas fa-calendar"></i> Fecha ingreso</th>
              <th><i class="fas fa-file-contract"></i> Contrato</th>
              <th><i class="fas fa-clock"></i> Turno</th>
              <th class="text-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="asignacion in asignaciones" :key="asignacion.id">
              <td>{{ asignacion.empresaCliente?.razon_social || 'N/A' }}</td>
              <td>{{ asignacion.puestoTrabajo?.nombre || 'N/A' }}</td>
              <td>{{ asignacion.numero_empleado || 'N/A' }}</td>
              <td>{{ asignacion.fecha_ingreso_empresa || 'N/A' }}</td>
              <td>{{ asignacion.tipo_contrato || 'N/A' }}</td>
              <td>{{ asignacion.turno || 'N/A' }}</td>
              <td class="text-right">
                <button @click="editarAsignacion(asignacion.id)" class="btn-action btn-edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button @click="eliminarAsignacion(asignacion.id)" class="btn-action btn-delete">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import apiClient from '@/services/ApiServiceClinicaTrabajo'
import Swal from 'sweetalert2'

const props = defineProps({
  pacienteId: { type: Number, required: true },
  empresas: { type: Array, default: () => [] },
  asignaciones: { type: Array, default: () => [] },
  modoInicial: { type: String, default: 'alta' }
})

// ✅ CORREGIDO: Definir los eventos que este componente emite al padre
const emit = defineEmits(['asignacion-creada', 'asignacion-eliminada'])

const modo = ref('alta')
const puestos = ref([])

const form = ref({
  empresa_cliente_id: '',
  puesto_trabajo_id: '',
  numero_empleado: '',
  fecha_ingreso_empresa: '',
  tipo_contrato: '',
  turno: ''
})

watch(() => props.modoInicial, (val) => {
  modo.value = val
})

watch(() => form.value.empresa_cliente_id, async (empresaId) => {
  if (empresaId && parseInt(empresaId) > 0) {
    try {
      const respuesta = await apiClient.get('/empresa-cliente/' + empresaId)
      const empresa = respuesta.data.data || respuesta.data
      puestos.value = empresa?.puestosTrabajo || empresa?.puestos_trabajo || []
    } catch (error) {
      console.error('Error cargando puestos:', error)
      puestos.value = []
    }
  } else {
    puestos.value = []
  }
})

const cargarPuestos = async () => {
  // Los puestos ya se cargan vía el watch
}

const asignarEmpresa = async () => {
  try {
    const respuesta = await apiClient.post('/clinica-trabajo-DatosLaborales', {
      paciente_id: props.pacienteId,
      ...form.value
    })

    Swal.fire({
      title: '¡Éxito!',
      text: 'Asignación creada correctamente',
      icon: 'success',
      confirmButtonText: 'Aceptar'
    })

    form.value = {
      empresa_cliente_id: '',
      puesto_trabajo_id: '',
      numero_empleado: '',
      fecha_ingreso_empresa: '',
      tipo_contrato: '',
      turno: ''
    }
    puestos.value = []
    
    // ✅ CORREGIDO: Notificar al padre para que actualice su lista de asignaciones
    emit('asignacion-creada', respuesta.data.data || respuesta.data)
  } catch (error) {
    const msg = error.response?.data?.message || 'Error al crear la asignación'
    const errors = error.response?.data?.errors
    Swal.fire({
      title: 'Error',
      text: errors ? Object.values(errors).flat().join(', ') : msg,
      icon: 'error',
      confirmButtonText: 'Aceptar'
    })
  }
}

const editarAsignacion = (id) => {
  console.log('Editar asignación', id)
}

const eliminarAsignacion = async (id) => {
  const result = await Swal.fire({
    title: '¿Eliminar asignación?',
    text: 'Esta acción no se puede deshacer',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  })

  if (result.isConfirmed) {
    try {
      await apiClient.delete(`/clinica-trabajo-DatosLaborales/${id}`)
      Swal.fire('Eliminada', 'La asignación fue eliminada', 'success')
      
      // ✅ CORREGIDO: Notificar al padre para que elimine el registro de su lista
      emit('asignacion-eliminada', id)
    } catch (error) {
      const msg = error.response?.data?.message || 'No se pudo eliminar la asignación'
      Swal.fire('Atención', msg, 'warning')
    }
  }
}

defineExpose({ getFormData: () => form.value })
</script>

<style scoped>
.tab-section {
  padding: 10px;
}

.section-header {
  display: flex;
  align-items: center;
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 2px solid #e2e8f0;
}

.section-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
  margin-right: 15px;
}

.bg-primary { background: linear-gradient(135deg, #0061f2 0%, #00ba94 100%); }
.bg-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }

.section-title h3 {
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 5px 0;
}

.section-title p {
  font-size: 14px;
  color: #64748b;
  margin: 0;
}

/* Alerts */
.alert-warning, .alert-info {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 20px;
  border-radius: 10px;
  margin-bottom: 20px;
}

.alert-warning {
  background: #fef3c7;
  border-left: 4px solid #f59e0b;
  color: #92400e;
}

.alert-warning i {
  font-size: 24px;
  color: #f59e0b;
}

.alert-info {
  background: #dbeafe;
  border-left: 4px solid #3b82f6;
  color: #1e40af;
}

.alert-info i {
  font-size: 24px;
  color: #3b82f6;
}

/* Form Grid */
.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
  margin-bottom: 30px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
  color: #475569;
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-label i {
  color: #0061f2;
  font-size: 16px;
}

.required {
  color: #ef4444;
}

.input-wrapper {
  position: relative;
}

.form-input {
  width: 100%;
  padding: 12px 16px;
  font-size: 15px;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  background: #f8fafc;
  transition: all 0.3s ease;
  color: #334155;
}

.form-input:focus {
  outline: none;
  border-color: #0061f2;
  background: white;
  box-shadow: 0 0 0 4px rgba(0, 97, 242, 0.1);
}

/* Button */
.btn-container {
  display: flex;
  justify-content: center;
  margin-top: 30px;
}

.btn-primary {
  background: linear-gradient(135deg, #0061f2 0%, #00ba94 100%);
  color: white;
  border: none;
  padding: 15px 40px;
  border-radius: 50px;
  font-size: 16px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(0, 97, 242, 0.3);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 97, 242, 0.4);
}

/* Table */
.table-container {
  overflow-x: auto;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.custom-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
}

.custom-table thead {
  background: linear-gradient(135deg, #0061f2 0%, #00ba94 100%);
  color: white;
}

.custom-table th {
  padding: 15px;
  text-align: left;
  font-weight: 600;
  font-size: 14px;
}

.custom-table th i {
  margin-right: 8px;
}

.custom-table td {
  padding: 15px;
  border-bottom: 1px solid #e2e8f0;
  font-size: 14px;
  color: #475569;
}

.custom-table tbody tr:hover {
  background: #f8fafc;
}

.text-right {
  text-align: right;
}

/* Action Buttons */
.btn-action {
  border: none;
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  margin: 0 4px;
}

.btn-edit {
  background: #3b82f6;
  color: white;
}

.btn-edit:hover {
  background: #2563eb;
  transform: translateY(-1px);
}

.btn-delete {
  background: #ef4444;
  color: white;
}

.btn-delete:hover {
  background: #dc2626;
  transform: translateY(-1px);
}
</style>