<template>
  <div class="registro-paciente-container">
    <!-- Header Banner -->
    <div class="registro-header">
      <div class="header-content">
        <div class="header-icon">
          <i class="fas fa-user-plus"></i>
        </div>
        <div class="header-text">
          <h1>Registro de Nuevo Paciente</h1>
          <p>Clínica del Trabajo - {{ modoInicial === 'alta' ? 'Alta de Paciente' : 'Edición de Paciente' }}</p>
        </div>
        <div class="header-badge">
          <span :class="['badge', modoInicial === 'alta' ? 'badge-nuevo' : 'badge-edicion']">
            {{ modoInicial === 'alta' ? 'NUEVO' : 'EDICIÓN' }}
          </span>
        </div>
      </div>
    </div>

    <!-- Nav Tabs Rediseñados -->
    <div class="tabs-container">
      <ul class="nav nav-tabs custom-tabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="datos-personales-tab" data-bs-toggle="tab"
            data-bs-target="#datos-personales" type="button"
            aria-controls="datos-personales" aria-selected="true">
            <span class="tab-icon"><i class="fas fa-user"></i></span>
            <span class="tab-number">01</span>
            <span class="tab-label">Personal</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="contacto-emergencia-tab" data-bs-toggle="tab"
            data-bs-target="#contacto-emergencia" type="button"
            aria-controls="contacto-emergencia" aria-selected="false">
            <span class="tab-icon"><i class="fas fa-phone"></i></span>
            <span class="tab-number">02</span>
            <span class="tab-label">Contacto</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="datos-medicos-tab" data-bs-toggle="tab"
            data-bs-target="#datos-medicos" type="button"
            aria-controls="datos-medicos" aria-selected="false">
            <span class="tab-icon"><i class="fas fa-stethoscope"></i></span>
            <span class="tab-number">03</span>
            <span class="tab-label">Médico</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="datos-laborales-tab" data-bs-toggle="tab"
            data-bs-target="#datos-laborales" type="button"
            aria-controls="datos-laborales" aria-selected="false">
            <span class="tab-icon"><i class="fas fa-briefcase"></i></span>
            <span class="tab-number">04</span>
            <span class="tab-label">Laboral</span>
          </button>
        </li>
      </ul>
    </div>

    <!-- Tab content -->
    <div class="tab-content custom-tab-content">
      <div class="tab-pane fade show active" id="datos-personales" role="tabpanel">
        <TabDatosPersonales ref="tabDatosPersonales" :initialData="datosPaciente" />
      </div>
      <div class="tab-pane fade" id="contacto-emergencia" role="tabpanel">
        <TabContactoEmergencia ref="tabContactoEmergencia" :initialData="datosPaciente" />
      </div>
      <div class="tab-pane fade" id="datos-medicos" role="tabpanel">
        <TabDatosMedicos ref="tabDatosMedicos" :initialData="datosPaciente" />
      </div>
      <div class="tab-pane fade" id="datos-laborales" role="tabpanel">
        <TabDatosLaborales
          ref="tabDatosLaborales"
          :pacienteId="pacienteId"
          :empresas="empresas"
          :asignaciones="asignaciones"
          :modoInicial="modoInicial"
          @asignacion-creada="onAsignacionCreada"
          @asignacion-eliminada="onAsignacionEliminada" />
      </div>
    </div>

    <!-- Botón Guardar Flotante -->
    <div class="floating-save-btn">
      <button @click="handleSubmit" class="btn-save">
        <i class="fas fa-save"></i>
        <span>Guardar Paciente</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import apiClient from '@/services/ApiServiceClinicaTrabajo'
import Swal from 'sweetalert2'
import TabDatosPersonales from './TabDatosPersonales.vue'
import TabContactoEmergencia from './TabContactoEmergencia.vue'
import TabDatosMedicos from './TabDatosMedicos.vue'
import TabDatosLaborales from './TabDatosLaborales.vue'

const props = defineProps({
  pacienteId: { type: [Number, String], default: 0 }
})

const pacienteId = ref(Number(props.pacienteId) || 0)
const modoInicial = ref(pacienteId.value > 0 ? 'edicion' : 'alta')
const datosPaciente = ref({})
const empresas = ref([])
const asignaciones = ref([])

// ✅ CORREGIDO: Refs a los componentes hijos para extraer sus datos
const tabDatosPersonales = ref(null)
const tabContactoEmergencia = ref(null)
const tabDatosMedicos = ref(null)
const tabDatosLaborales = ref(null)

onMounted(async () => {
  await cargarEmpresas()
  if (pacienteId.value > 0) {
    await cargarPaciente()
    await cargarAsignaciones()
  }
})

const cargarPaciente = async () => {
  try {
    const respuesta = await apiClient.get('/clinica-trabajo-pacientes/' + pacienteId.value)
    datosPaciente.value = respuesta.data.data || respuesta.data || {}
  } catch (error) {
    console.error('Error cargando paciente:', error)
  }
}

const cargarEmpresas = async () => {
  try {
    const respuesta = await apiClient.get('/empresa-cliente', {
      params: { select: 1 }
    })
    empresas.value = respuesta.data.data || respuesta.data || []
  } catch (error) {
    console.error('Error cargando empresas:', error)
  }
}

const cargarAsignaciones = async () => {
  try {
    const respuesta = await apiClient.get('/clinica-trabajo-DatosLaborales', {
      params: { paciente_id: pacienteId.value }
    })
    asignaciones.value = respuesta.data.data || respuesta.data || []
  } catch (error) {
    console.error('Error cargando asignaciones:', error)
  }
}

watch(pacienteId, (id) => {
  if (id > 0) {
    cargarAsignaciones()
  }
})

const onAsignacionCreada = (nuevaAsignacion) => {
  asignaciones.value.push(nuevaAsignacion)
}

const onAsignacionEliminada = (id) => {
  asignaciones.value = asignaciones.value.filter(a => a.id !== id)
}

const handleSubmit = async (e) => {
  e.preventDefault()

  // ✅ CORREGIDO: Recolectar datos REALES de los hijos usando getFormData()
  // La foto (File) no viaja en el JSON; se resuelve al corregir TabDatosPersonales.
  const { foto, ...datosPersonales } = tabDatosPersonales.value.getFormData()
  const payload = {
    ...datosPersonales,
    ...tabContactoEmergencia.value.getFormData(),
    ...tabDatosMedicos.value.getFormData(),
  }

  // ✅ CORREGIDO: Si es alta, incluir asignación laboral si el usuario la llenó
  if (modoInicial.value === 'alta') {
    const laborData = tabDatosLaborales.value.getFormData()
    if (laborData.empresa_cliente_id) {
      payload.asignacion = laborData
    }
  }

  try {
    if (modoInicial.value === 'alta') {
      const respuesta = await apiClient.post('/clinica-trabajo-pacientes', payload)
      pacienteId.value = respuesta.data.data?.id || pacienteId.value
      modoInicial.value = 'edicion'

      Swal.fire({
        title: '¡Éxito!',
        text: 'Paciente creado correctamente',
        icon: 'success',
        confirmButtonText: 'Aceptar'
      })
    } else {
      await apiClient.put(`/clinica-trabajo-pacientes/${pacienteId.value}`, payload)
      Swal.fire({
        title: '¡Éxito!',
        text: 'Paciente actualizado correctamente',
        icon: 'success',
        confirmButtonText: 'Aceptar'
      })
    }
  } catch (error) {
    if (error.response && error.response.status === 422) {
      Swal.fire({
        title: 'Error de validación',
        text: error.response.data.message || 'Verifica los campos resaltados',
        icon: 'error',
        confirmButtonText: 'Aceptar'
      })
    } else if (error.response && error.response.status === 409) {
      Swal.fire({
        title: 'Error',
        text: error.response.data.message || 'No se pudo generar el folio, intenta de nuevo',
        icon: 'error',
        confirmButtonText: 'Aceptar'
      })
    } else {
      Swal.fire({
        title: 'Error',
        text: 'Error al guardar el paciente',
        icon: 'error',
        confirmButtonText: 'Aceptar'
      })
    }
  }
}

// ✅ ELIMINADAS: Las funciones fetchDatos... que devolvían valores vacíos
// Ya no son necesarias porque usamos getFormData() de los hijos

defineExpose({ handleSubmit })
</script>

<style scoped>
.registro-paciente-container {
  padding: 20px;
  background: #f8fafc;
  min-height: 100vh;
  position: relative;
}

/* Header Banner */
.registro-header {
  background: linear-gradient(135deg, #0061f2 0%, #00ba94 100%);
  border-radius: 16px;
  padding: 30px;
  margin-bottom: 30px;
  box-shadow: 0 10px 30px rgba(0, 97, 242, 0.2);
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  color: white;
}

.header-icon {
  width: 70px;
  height: 70px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  margin-right: 20px;
}

.header-text {
  flex: 1;
}

.header-text h1 {
  font-size: 28px;
  font-weight: 700;
  margin: 0 0 5px 0;
}

.header-text p {
  font-size: 16px;
  margin: 0;
  opacity: 0.9;
}

.header-badge .badge {
  padding: 10px 20px;
  border-radius: 25px;
  font-weight: 600;
  font-size: 14px;
}

.badge-nuevo {
  background: #10b981;
  color: white;
}

.badge-edicion {
  background: #f59e0b;
  color: white;
}

/* Tabs Container */
.tabs-container {
  background: white;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.custom-tabs {
  border: none;
  display: flex;
  justify-content: space-around;
}

.custom-tabs .nav-link {
  border: none;
  background: transparent;
  padding: 15px 25px;
  display: flex;
  align-items: center;
  gap: 12px;
  color: #64748b;
  font-weight: 500;
  transition: all 0.3s ease;
  border-radius: 10px;
}

.custom-tabs .nav-link:hover {
  background: #f1f5f9;
  color: #0061f2;
}

.custom-tabs .nav-link.active {
  background: linear-gradient(135deg, #0061f2 0%, #00ba94 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(0, 97, 242, 0.3);
}

.tab-icon {
  font-size: 20px;
}

.tab-number {
  font-weight: 700;
  font-size: 18px;
}

.tab-label {
  font-size: 15px;
}

/* Tab Content */
.custom-tab-content {
  background: white;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  min-height: 500px;
}

/* Floating Save Button */
.floating-save-btn {
  position: fixed;
  bottom: 30px;
  right: 30px;
  z-index: 1000;
}

.btn-save {
  background: linear-gradient(135deg, #0061f2 0%, #00ba94 100%);
  color: white;
  border: none;
  padding: 15px 30px;
  border-radius: 50px;
  font-size: 16px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 6px 20px rgba(0, 97, 242, 0.4);
  transition: all 0.3s ease;
  cursor: pointer;
}

.btn-save:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 97, 242, 0.5);
}

.btn-save i {
  font-size: 18px;
}
</style>