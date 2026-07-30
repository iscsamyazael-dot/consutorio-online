<template>
  <div class="card border-0 shadow-sm rounded-4 p-4 my-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h5 class="fw-bold mb-0 text-dark">
        <i class="fas fa-share-square text-primary me-2"></i>
        Derivaciones Médicas
      </h5>
      <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
        Consulta ID: {{ consultaId }}
      </span>
    </div>

    <!-- Carga -->
    <div v-if="loading" class="text-center my-4 py-3">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="text-muted small mt-2 mb-0">Cargando datos...</p>
    </div>

    <!-- Sin datos -->
    <div v-else-if="derivaciones.length === 0" class="text-center my-4 py-4 border rounded-4 bg-light">
      <i class="fas fa-folder-open text-muted fa-2x mb-2"></i>
      <p class="text-muted mb-0">No se encontraron derivaciones.</p>
    </div>

    <!-- Tabla Dinámica -->
    <div v-else class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th scope="col" class="py-3 ps-3">Paciente</th>
            <th scope="col" class="py-3">Especialidad</th>
            <th scope="col" class="py-3">Hospital</th>
            <th scope="col" class="py-3">Motivo</th>
            <th scope="col" class="py-3 text-center">Prioridad</th>
            <th scope="col" class="py-3 text-center">Estado</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in derivaciones" :key="item.id">
            <td class="ps-3 fw-semibold text-dark">{{ item.paciente }}</td>
            
            <td>
              <span class="badge bg-info-subtle text-info px-2 py-1 rounded-3">
                <i class="fas fa-stethoscope me-1"></i>{{ item.especialidad }}
              </span>
            </td>

            <td class="text-secondary">{{ item.hospital || 'N/A' }}</td>

            <!-- Motivo limpio sin la frase "triage" -->
            <td class="text-wrap" style="max-width: 250px;">
              {{ item.motivo }}
            </td>

            <!-- Prioridad detectada -->
            <td class="text-center">
              <span :class="['badge rounded-pill px-3 py-2', getPrioridadBadge(item.prioridad)]">
                <i :class="['me-1', getPrioridadIcon(item.prioridad)]"></i>
                {{ item.prioridad ? item.prioridad.toUpperCase() : 'MEDIA' }}
              </span>
            </td>

            <!-- Estado -->
            <td class="text-center">
              <span :class="['badge rounded-pill px-3 py-2', getEstadoBadge(item.estado)]">
                {{ item.estado }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import ApiService from '../../services/ApiService.js' 

const props = defineProps({
  consultaId: { type: [Number, String], default: 516 }
})

const derivaciones = ref([])
const loading = ref(true)

const obtenerDerivaciones = async () => {
  loading.value = true
  try {
    // Petición usando tu ApiService
    const response = await ApiService.get(`derivaciones/consulta/${props.consultaId}`)
    
    // Si ApiService retorna la respuesta en .data o directa:
    derivaciones.value = response.data || response
  } catch (error) {
    console.error('Error al cargar derivaciones:', error)
  } finally {
    loading.value = false
  }
}

// Estilos de Prioridad
const getPrioridadBadge = (prioridad) => {
  switch (prioridad) {
    case 'baja': return 'bg-success-subtle text-success border border-success-subtle'
    case 'media': return 'bg-warning-subtle text-warning border border-warning-subtle'
    case 'alta': return 'bg-danger-subtle text-danger border border-danger-subtle'
    default: return 'bg-secondary-subtle text-secondary'
  }
}

const getPrioridadIcon = (prioridad) => {
  switch (prioridad) {
    case 'baja': return 'fas fa-arrow-down'
    case 'media': return 'fas fa-minus'
    case 'alta': return 'fas fa-exclamation-triangle'
    default: return 'fas fa-circle'
  }
}

// Estilos de Estado
const getEstadoBadge = (estado) => {
  switch (estado) {
    case 'pendiente': return 'bg-warning text-dark'
    case 'enviado': return 'bg-primary text-white'
    case 'atendido': return 'bg-success text-white'
    default: return 'bg-secondary text-white'
  }
}

onMounted(() => {
  obtenerDerivaciones()
})
</script>