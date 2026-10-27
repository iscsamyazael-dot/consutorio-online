<template>
  <div class="container-fluid p-0">
    <!-- ✅ StatsCards ahora es autocontenido, no necesita props -->
    <StatsCards />

    <!-- Tarjeta de la tabla principal -->
    <div class="card border-0 shadow-sm rounded-4 bg-white">
      <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <PatientSearch 
            v-model="buscar" 
            v-model:empresaFilter="empresaSeleccionada"
          />
        </div>

        <PatientTable 
          :pacientes="pacientesFiltrados" 
          @view="abrirModalVer"
          @delete="abrirModalEliminar"
        />
      </div>
    </div>

    <!-- Modales existentes -->
    <PatientViewModal :show="modalVer" :paciente="detallePaciente" @close="modalVer = false" />
    <PatientDeleteModal :show="modalEliminar" @close="modalEliminar = false" @confirm="confirmarEliminacion" />
    
    <!-- ✅ Modal de Importación -->
    <ImportarExcelModal 
      :show="mostrarModalImportar" 
      tipo="paciente" 
      @close="mostrarModalImportar = false"
      @success="onImportacionExitosa"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import ApiServiceClinicaTrabajo from '@/services/ApiServiceClinicaTrabajo'

import StatsCards from './StatsCards.vue'
import PatientSearch from './PacienteSearch.vue'
import PatientTable from './PacienteTabla.vue'
import PatientViewModal from './PacienteVerModal.vue'
import PatientDeleteModal from './PacienteEliminarModal.vue'
import ImportarExcelModal from './ImportarExcelModal.vue'

// Estado de la tabla
const pacientes = ref([])
const buscar = ref('')
const empresaSeleccionada = ref('') // ✅ Filtro por empresa
const detallePaciente = ref({})
const pacienteAEliminar = ref(null)
const modalVer = ref(false)
const modalEliminar = ref(false)
const mostrarModalImportar = ref(false)
const cargando = ref(false)

// ✅ Ahora el filtrado es más simple porque el backend ya nos devuelve los datos filtrados
const pacientesFiltrados = computed(() => {
  return pacientes.value
})

// ✅ FUNCIÓN OPTIMIZADA: Envía los filtros al backend
const obtenerPacientes = async () => {
  cargando.value = true
  try {
    const params = {}
    if (buscar.value.trim()) {
      params.search = buscar.value.trim()
    }
    if (empresaSeleccionada.value) {
      params.empresa_id = empresaSeleccionada.value // ✅ Enviamos el ID de la empresa
    }

    const response = await ApiServiceClinicaTrabajo.get('/clinica-trabajo-pacientes', { params })
    pacientes.value = response.data.data || response.data || []
  } catch (error) { 
    console.error('Error al obtener pacientes:', error) 
  } finally {
    cargando.value = false
  }
}

// ✅ Observamos los cambios en los filtros para recargar automáticamente
watch([buscar, empresaSeleccionada], () => {
  obtenerPacientes()
})

const abrirModalVer = async (paciente) => {
  try {
    const response = await ApiServiceClinicaTrabajo.get('/clinica-trabajo-pacientes/' + paciente.id)
    detallePaciente.value = response.data.data || response.data || {}
    modalVer.value = true
  } catch (error) { 
    console.error('Error al obtener detalle:', error) 
  }
}

const abrirModalEliminar = (paciente) => {
  pacienteAEliminar.value = paciente
  modalEliminar.value = true
}

const confirmarEliminacion = async () => {
  if (!pacienteAEliminar.value) return
  try {
    await ApiServiceClinicaTrabajo.delete('/clinica-trabajo-pacientes/' + pacienteAEliminar.value.id)
    pacientes.value = pacientes.value.filter(p => p.id !== pacienteAEliminar.value.id)
    modalEliminar.value = false
    
    if (typeof Swal !== 'undefined') {
      Swal.fire({ icon: 'success', title: 'Paciente eliminado', text: 'El paciente fue eliminado correctamente.', confirmButtonText: 'Aceptar' })
    }
  } catch (error) {
    console.error(error)
    if (typeof Swal !== 'undefined') {
      Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrió un error al eliminar el paciente.', confirmButtonText: 'Aceptar' })
    }
  }
}

const onImportacionExitosa = () => {
  obtenerPacientes() // Recarga la tabla tras importar
}

const manejarAperturaModal = () => {
  mostrarModalImportar.value = true
}

onMounted(() => { 
  obtenerPacientes()
  window.addEventListener('abrir-modal-importar', manejarAperturaModal)
})

onUnmounted(() => {
  window.removeEventListener('abrir-modal-importar', manejarAperturaModal)
})
</script>

<style scoped>
/* Tus estilos aquí */
</style>