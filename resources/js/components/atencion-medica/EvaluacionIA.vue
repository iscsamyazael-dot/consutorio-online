<template>
  <div class="card card-outline card-info">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-list mr-1"></i> Evaluaciones IA</h3>
    </div>

    <div class="card-body table-responsive p-0">
      <table class="table table-hover text-nowrap">
        <thead>
          <tr>
            <th>Folio</th>
            <th>Paciente</th>
            <th>Consulta</th>
            <th>Fecha</th>
            <th>Riesgo</th>
            <th>Confianza IA</th>
            <th>Estado</th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <!-- Spinner de carga -->
          <tr v-if="loading">
            <td colspan="8" class="text-center py-4">
              <i class="fas fa-spinner fa-spin fa-2x text-info"></i>
              <p class="mb-0 mt-2 text-muted">Cargando datos...</p>
            </td>
          </tr>

          <!-- Sin datos -->
          <tr v-else-if="!evaluaciones.length">
            <td colspan="8" class="text-center text-muted py-4">
              No hay evaluaciones registradas.
            </td>
          </tr>

          <!-- Filas -->
          <tr v-else v-for="eva in evaluaciones" :key="eva.folio">
            <td><span class="font-weight-bold">{{ eva.folio }}</span></td>
            <td>{{ eva.paciente }}</td>
            <td>{{ eva.consulta }}</td>
            <td>{{ eva.fecha }}</td>
            <td>
              <span class="badge" :class="`badge-${riesgoColor(eva.riesgo)}`">
                {{ eva.riesgo }}
              </span>
            </td>
            <td style="min-width: 140px;">
              <div class="d-flex align-items-center">
                <div class="progress progress-sm flex-grow-1 mr-2">
                  <div class="progress-bar bg-primary" :style="{ width: eva.confianza + '%' }"></div>
                </div>
                <span class="small">{{ eva.confianza }}%</span>
              </div>
            </td>
            <td>
              <span class="badge" :class="`badge-${estadoColor(eva.estado)}`">
                {{ eva.estado }}
              </span>
            </td>
            <td class="text-center">
              <button
                    type="button"
                    class="btn btn-sm btn-outline-primary"
                    @click="verEvaluacion(eva.consulta_folio)"
                    >
                    <i class="fas fa-eye"></i> Ver
                </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <div class="card-footer clearfix" v-if="evaluaciones.length && totalPaginas > 1">
      <ul class="pagination pagination-sm m-0 float-right">
        <li class="page-item" :class="{ disabled: paginaActual === 1 }">
          <a class="page-link" href="#" @click.prevent="cargarDatos(paginaActual - 1)">«</a>
        </li>
        <li 
          v-for="p in totalPaginas" 
          :key="p" 
          class="page-item" 
          :class="{ active: paginaActual === p }"
        >
          <a class="page-link" href="#" @click.prevent="cargarDatos(p)">{{ p }}</a>
        </li>
        <li class="page-item" :class="{ disabled: paginaActual === totalPaginas }">
          <a class="page-link" href="#" @click.prevent="cargarDatos(paginaActual + 1)">»</a>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import ApiService from '../../services/ApiService.js';

export default {
  name: 'TablaEvaluacionesIA',

  props: {
    filtros: {
      type: Object,
      default: () => ({}),
    }
  },

  emits: ['ver-evaluacion'],

  data() {
    return {
      evaluaciones: [],
      paginaActual: 1,
      totalPaginas: 1,
      loading: false,
    };
  },

  watch: {
    filtros: {
      deep: true,
      handler() {
        this.cargarDatos(1);
      }
    }
  },

  mounted() {
    this.cargarDatos();
  },

  methods: {
    async cargarDatos(page = 1) {
      this.loading = true;
      try {
        const response = await ApiService.get('/api/evaluaciones-ia', {
          params: { page, ...this.filtros }
        });
        
        // Maneja respuestas tanto de axios como de wrappers personalizados
        const resData = response.data || response;
        
        this.evaluaciones = resData.data || [];
        this.paginaActual = resData.current_page || 1;
        this.totalPaginas = resData.last_page || 1;
      } catch (error) {
        console.error("Error al cargar evaluaciones desde ApiService:", error);
      } finally {
        this.loading = false;
      }
    },

    riesgoColor(riesgo) {
      const colores = { alto: 'danger', medio: 'warning', bajo: 'success' };
      return colores[riesgo] || 'secondary';
    },

    estadoColor(estado) {
      const colores = {
        'pendiente': 'warning',
        'Revisada': 'success',
      };
      return colores[estado] || 'secondary';
    },

    verEvaluacion(folio) {
      this.$emit('ver-evaluacion', folio);
    },
  },
};
</script>

<style scoped>
.progress-sm {
  height: 6px;
}
</style>