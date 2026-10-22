<template>
    <div class="row mb-4">
        <!-- Tarjeta 1: Total Pacientes -->
        <div class="col-md-6">
            <div class="small-box bg-primary shadow border-0 rounded-4">
                <div class="inner">
                    <h3 class="h4">{{ totalPacientes }}</h3>
                    <p class="mb-0">Total Pacientes</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>

        <!-- Tarjeta 2: Pacientes Pendientes -->
        <div class="col-md-6">
            <div 
                class="small-box bg-danger shadow border-0 rounded-4 tarjeta-pendientes"
                :class="{ 'cursor-pointer': totalPendientes > 0 }"
                @click="togglePendientes"
            >
                <div class="inner">
                    <h3 class="h4">{{ totalPendientes }}</h3>
                    <p class="mb-0">Pacientes con datos incompletos</p>
                    <small v-if="totalPendientes > 0" class="d-block mt-1">
                        <i class="fas fa-hand-pointer"></i> Haz clic para ver la lista
                    </small>
                </div>
            </div>
        </div>

        <!-- Lista Desplegable de Pendientes -->
        <div class="col-12">
            <transition name="fade-lista">
                <div v-if="mostrarPendientes && totalPendientes > 0" class="mb-4 mt-2">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Pacientes que deben completar sus datos</h6>
                            <ul class="list-group list-group-flush">
                                <li v-for="p in pacientesPendientes" :key="p.id" class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold">{{ p.nombre }}</span>
                                        <small class="text-muted ms-2">({{ p.folio }})</small>
                                    </div>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-outline-primary rounded-pill"
                                        @click="completarDatos(p)"
                                    >
                                        Completar datos
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import ApiServiceClinicaTrabajo from '@/services/ApiServiceClinicaTrabajo'

// Estado reactivo
const totalPacientes = ref(0)
const totalPendientes = ref(0)
const pacientesPendientes = ref([])
const mostrarPendientes = ref(false)

// ✅ Llamar a la API al montar el componente
const obtenerEstadisticas = async () => {
    try {
        const response = await ApiServiceClinicaTrabajo.get('/clinica-trabajo-TotalPacientesyPendientes')
        const data = response.data.data
        
        totalPacientes.value = data.totalPacientes
        totalPendientes.value = data.totalPendientes
        pacientesPendientes.value = data.pacientesPendientes
    } catch (error) {
        console.error('Error al obtener estadísticas:', error)
    }
}

const togglePendientes = () => {
    if (totalPendientes.value > 0) {
        mostrarPendientes.value = !mostrarPendientes.value
        if (mostrarPendientes.value) {
            setTimeout(() => {
                const el = document.querySelector('.fade-lista-enter-active')?.parentElement
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
            }, 100)
        }
    }
}

const completarDatos = (paciente) => {
    localStorage.setItem('pacientePrecargar', JSON.stringify(paciente))
    window.location.href = '/PacienteNuevo' // Ajusta esta ruta si es diferente
}

// Ejecutar al montar
onMounted(() => {
    obtenerEstadisticas()
})
</script>

<style scoped>
.small-box { border-radius: 22px !important; height: 120px !important; display: flex; align-items: center; position: relative; overflow: hidden; }
.small-box .inner { width: 100%; z-index: 2; }
.small-box .icon { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); font-size: 65px; opacity: 0.18; }
.small-box:hover { transform: translateY(-5px); transition: 0.3s; }
.tarjeta-pendientes { cursor: pointer; transition: all 0.30s ease; }
.tarjeta-pendientes:hover { transform: translateY(-5px); box-shadow: 0 12px 35px rgba(0,0,0,0.20); }
.tarjeta-pendientes:hover .icon { transform: scale(1.1); }
.fade-lista-enter-active, .fade-lista-leave-active { transition: all 0.35s ease; }
.fade-lista-enter-from, .fade-lista-leave-to { opacity: 0; transform: translateY(-10px); }
</style>