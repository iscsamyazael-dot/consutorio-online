<template>
    <div class="container-fluid p-0">
        
        <!-- ✅ StatsCards ahora es autocontenido, no necesita props -->
        <StatsCards />

        <!-- Tarjeta de la tabla principal -->
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <PatientSearch v-model="buscar" />
                </div>

                <PatientTable 
                    :pacientes="pacientesFiltrados" 
                    @view="abrirModalVer"
                    @delete="abrirModalEliminar"
                />
            </div>
        </div>

        <!-- Modales -->
        <PatientViewModal :show="modalVer" :paciente="detallePaciente" @close="modalVer = false" />
        <PatientDeleteModal :show="modalEliminar" @close="modalEliminar = false" @confirm="confirmarEliminacion" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import ApiServiceClinicaTrabajo from '@/services/ApiServiceClinicaTrabajo'

import StatsCards from './StatsCards.vue'
import PatientSearch from './PacienteSearch.vue'
import PatientTable from './PacienteTabla.vue'
import PatientViewModal from './PacienteVerModal.vue'
import PatientDeleteModal from './PacienteEliminarModal.vue'

// Estado de la tabla
const pacientes = ref([])
const buscar = ref('')
const detallePaciente = ref({})
const pacienteAEliminar = ref(null)
const modalVer = ref(false)
const modalEliminar = ref(false)

const pacientesFiltrados = computed(() => {
    if (!buscar.value.trim()) return pacientes.value
    const q = buscar.value.toLowerCase()
    return pacientes.value.filter(p =>
        (p.nombre || '').toLowerCase().includes(q) ||
        (p.apellido_paterno || '').toLowerCase().includes(q) ||
        (p.apellido_materno || '').toLowerCase().includes(q) ||
        (p.telefono || '').toLowerCase().includes(q) ||
        String(p.folio || p.paciente_id || '').toLowerCase().includes(q)
    )
})

const obtenerPacientes = async () => {
    try {
        const response = await ApiServiceClinicaTrabajo.get('/clinica-trabajo-pacientes')
        pacientes.value = response.data.data || response.data || []
    } catch (error) { 
        console.error('Error al obtener pacientes:', error) 
    }
}

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

onMounted(() => { 
    obtenerPacientes() 
})
</script>

<style scoped>
/* Tus estilos aquí */
</style>