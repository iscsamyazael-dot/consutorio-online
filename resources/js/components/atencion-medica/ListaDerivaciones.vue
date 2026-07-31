<template>
  <div class="card border-0 shadow-sm rounded-4 p-4 my-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h5 class="fw-bold mb-0 text-dark">
        <i class="fas fa-share-square text-primary me-2"></i>
        Derivaciones Médicas
      </h5>
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
                <i class="fas fa-stethoscope me-1 p-1"></i>{{ item.especialidad }}
              </span>
            </td>

            <td class="text-secondary">{{ item.hospital || 'N/A' }}</td>

            <!-- Motivo limpio sin la frase "triage" -->
            <td style="max-width:320px;">
                <div class="motivo-preview">
                    {{ item.motivo }}
                </div>

                <button
                    v-if="item.motivo"
                    class="btn btn-link btn-sm p-0 mt-1"
                    @click="verMotivo(item)">
                    <i class="fas fa-eye me-1"></i> Ver más
                </button>

                <div
                    v-else
                    class="alert alert-danger d-inline-flex align-items-center py-1 px-2 mb-0 small"
                    role="alert">

                    <i class="fas fa-exclamation-triangle me-2 p-1"></i>
                    SIN MOTIVO

                </div>
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

  <!--MODAL VER MOTIVO-->
    <div
        class="modal fade"
        id="modalMotivo"
        tabindex="-1"
        aria-labelledby="modalMotivoLabel"
        aria-hidden="true">

        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalMotivoLabel">
                        <i class="fas fa-file-medical me-2"></i>
                        Motivo de la Derivación
                    </h5>

                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <strong>Paciente: </strong>
                        <span>{{ motivoSeleccionado.paciente }}</span>
                    </div>

                    <div class="mb-3">
                        <strong>Especialidad: </strong>
                        <span>{{ motivoSeleccionado.especialidad }}</span>
                    </div>

                    <div class="mb-3">
                        <strong>Prioridad: </strong>

                        <span
                            :class="['badge rounded-pill px-3 py-2', getPrioridadBadge(motivoSeleccionado.prioridad)]">

                            {{ motivoSeleccionado.prioridad?.toUpperCase() }}

                        </span>
                    </div>

                    <hr>

                    <label class="fw-bold mb-2">
                        Motivo
                    </label>

                    <div
                        class="border rounded-3 p-3 bg-light"
                        style="white-space: pre-line; line-height:1.7">

                        {{ motivoSeleccionado.motivo }}

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Cerrar

                    </button>

                </div>

            </div>
        </div>
    </div>
</template>

<script>
import * as bootstrap from 'bootstrap'
import ApiService from '../../services/ApiService.js'

export default {
  props: {
    consultaId: {
      type: [Number, String],
      default: 516
    }
  },

  data() {
    return {
      derivaciones: [],
      loading: true,
      motivoSeleccionado: {}
    }
  },

  mounted() {
    this.obtenerDerivaciones()
  },

  methods: {
    async obtenerDerivaciones() {
      this.loading = true

      try {
        const response = await ApiService.get('/derivaciones')
        this.derivaciones = response.data
      } catch (error) {
        console.error('Error al cargar derivaciones:', error)
      } finally {
        this.loading = false
      }
    },

    getPrioridadBadge(prioridad) {
      switch (prioridad) {
        case 'baja':
          return 'bg-success-subtle text-success border border-success-subtle'
        case 'media':
          return 'bg-warning-subtle text-warning border border-warning-subtle'
        case 'alta':
          return 'bg-orange-subtle text-orange border border-orange-subtle' // O 'bg-warning text-dark'
        case 'critica':
          return 'bg-danger text-white font-weight-bold shadow-sm'
        default:
          return 'bg-secondary-subtle text-secondary'
      }
    },

    getPrioridadIcon(prioridad) {
      switch (prioridad) {
        case 'baja':
          return 'fas fa-arrow-down'
        case 'media':
          return 'fas fa-minus'
        case 'alta':
          return 'fas fa-exclamation-triangle' // Advertencia/Riesgo alto
        case 'critica':
          return 'fas fa-radiation' // O 'fas fa-fire' / 'fas fa-skull-crossbones' / 'fas fa-ban'
        default:
          return 'fas fa-circle'
      }
    },

    getEstadoBadge(estado) {
      switch (estado) {
        case 'pendiente':
          return 'bg-warning text-dark'
        case 'enviado':
          return 'bg-primary text-white'
        case 'atendido':
          return 'bg-success text-white'
        default:
          return 'bg-secondary text-white'
      }
    },

    verMotivo(item) {

        this.motivoSeleccionado = item

        const modal = new bootstrap.Modal(
            document.getElementById('modalMotivo')
        )

        modal.show()

    },
  }
}
</script>

<style>
.motivo-preview{
display:-webkit-box;
-webkit-line-clamp:2;
-webkit-box-orient:vertical;
overflow:hidden;
text-overflow:ellipsis;
}
</style>