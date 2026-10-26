<template>
  <div class="row mb-4 g-3">
    <!-- Tarjeta 1: Total Pacientes -->
    <div class="col-md-6">
      <div class="stat-card stat-card-primary shadow-sm">
        <div class="stat-icon-wrapper">
          <i class="fas fa-user"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-number">{{ totalPacientes }}</h3>
          <p class="stat-label mb-0">Total Pacientes</p>
        </div>
      </div>
    </div>

    <!-- Tarjeta 2: Pacientes Pendientes -->
    <div class="col-md-6">
      <div
        class="stat-card stat-card-danger shadow-sm"
        :class="{ 'cursor-pointer': totalPendientes > 0 }"
        @click="togglePendientes"
      >
        <div class="stat-icon-wrapper">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-number">{{ totalPendientes }}</h3>
          <p class="stat-label mb-0">Pacientes con datos incompletos</p>
          <small v-if="totalPendientes > 0" class="stat-hint d-block mt-1">
            <i class="fas fa-hand-pointer me-1"></i>Haz clic para ver la lista
          </small>
        </div>
      </div>
    </div>

    <!-- Lista Desplegable de Pendientes -->
    <div class="col-12">
      <transition name="fade-lista">
        <div v-if="mostrarPendientes && totalPendientes > 0" class="mt-3">
          <div class="card border-0 shadow-sm rounded-4 pendientes-card">
            <div class="card-header bg-white border-0 pt-3 px-4">
              <h6 class="fw-bold mb-0 text-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                Pacientes que deben completar sus datos
              </h6>
            </div>
            <div class="card-body px-4 pb-3">
              <ul class="list-group list-group-flush">
                <li
                  v-for="p in pacientesPendientes"
                  :key="p.id"
                  class="list-group-item d-flex justify-content-between align-items-center border-0 px-0"
                >
                  <div class="d-flex align-items-center gap-3">
                    <div class="avatar-sm" :style="{ background: obtenerColor(p.nombre) }">
                      {{ (p.nombre || 'P').substring(0, 2).toUpperCase() }}
                    </div>
                    <div>
                      <span class="fw-bold">{{ p.nombre }}</span>
                      <small class="text-muted d-block">{{ p.folio }}</small>
                    </div>
                  </div>
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-primary rounded-pill px-3"
                    @click="completarDatos(p)"
                  >
                    <i class="fas fa-edit me-1"></i>Completar datos
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

const obtenerColor = (nombre) => {
  const colores = ["#1976d2", "#43a047", "#7b1fa2", "#ef6c00", "#00897b", "#c2185b", "#5d4037", "#546e7a"]
  if (!nombre) return "#6c757d"
  let suma = 0
  for (let i = 0; i < String(nombre).length; i++) suma += String(nombre).charCodeAt(i)
  return colores[suma % colores.length]
}

// Ejecutar al montar
onMounted(() => {
  obtenerEstadisticas()
})
</script>

<style scoped>
/* Tarjetas de estadísticas rediseñadas */
.stat-card {
  border-radius: 16px !important;
  display: flex;
  align-items: center;
  padding: 24px 28px;
  position: relative;
  overflow: hidden;
  min-height: 110px;
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12) !important;
}

/* Gradientes con colores originales (azul y rojo) */
.stat-card-primary {
  background: linear-gradient(135deg, #4dabff 0%, #0d6efd 100%);
  color: white;
}

.stat-card-danger {
  background: linear-gradient(135deg, #ff6b7a 0%, #dc3545 100%);
  color: white;
}

.stat-icon-wrapper {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 20px;
  flex-shrink: 0;
}

.stat-icon-wrapper i {
  font-size: 32px;
  color: white;
}

.stat-content {
  flex: 1;
}

.stat-number {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0;
  line-height: 1;
}

.stat-label {
  font-size: 0.95rem;
  opacity: 0.95;
  margin-top: 4px;
}

.stat-hint {
  font-size: 0.8rem;
  opacity: 0.85;
}

.cursor-pointer {
  cursor: pointer;
}

/* Avatar pequeño para lista de pendientes */
.avatar-sm {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 14px;
  font-weight: bold;
  flex-shrink: 0;
}

/* Card de pendientes */
.pendientes-card {
  border: 1px solid #f0f0f0;
}

/* Animación de lista */
.fade-lista-enter-active,
.fade-lista-leave-active {
  transition: all 0.35s ease;
}

.fade-lista-enter-from,
.fade-lista-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>