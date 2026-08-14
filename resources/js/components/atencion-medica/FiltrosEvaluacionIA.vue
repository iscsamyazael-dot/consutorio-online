<template>
  <div class="card card-outline card-primary shadow-sm">
    <!-- Card Header (Mantiene el botón de collapse) -->
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filtros de búsqueda</h3>
      <div class="card-tools">
        <button type="button" class="btn btn-tool" data-card-widget="collapse">
          <i class="fas fa-minus"></i>
        </button>
      </div>
    </div>

    <!-- Card Body (Este bloque es el que se oculta/desliza con la animación) -->
    <div class="card-body">
      <form @submit.prevent="aplicarFiltros">
        <!-- Primera Fila: Paciente, Especialidad, Médico, Fecha -->
        <div class="row">
          <div class="col-md-4 col-lg-3 form-group">
            <label for="filtroPaciente"><i class="fas fa-user-injured text-muted mr-1"></i> Paciente</label>
            <input
              type="text"
              id="filtroPaciente"
              class="form-control"
              placeholder="Nombre del paciente..."
              v-model="filtros.paciente"
            >
          </div>

          <div class="col-md-4 col-lg-3 form-group">
            <label for="filtroEspecialidad"><i class="fas fa-stethoscope text-muted mr-1"></i> Especialidad</label>
            <select id="filtroEspecialidad" class="form-control custom-select" v-model="filtros.especialidad">
              <option value="">Todas las especialidades</option>
              <option v-for="esp in especialidades" :key="esp.value" :value="esp.value">
                {{ esp.label }}
              </option>
            </select>
          </div>

          <div class="col-md-4 col-lg-3 form-group">
            <label for="filtroMedico"><i class="fas fa-user-md text-muted mr-1"></i> Médico</label>
            <select id="filtroMedico" class="form-control custom-select" v-model="filtros.medico">
              <option value="">Todos los médicos</option>
              <option v-for="med in medicos" :key="med.value" :value="med.value">
                {{ med.label }}
              </option>
            </select>
          </div>

          <div class="col-md-4 col-lg-3 form-group">
            <label for="filtroFecha"><i class="far fa-calendar-alt text-muted mr-1"></i> Fecha</label>
            <input
              type="date"
              id="filtroFecha"
              class="form-control"
              v-model="filtros.fecha"
            >
          </div>
        </div>

        <!-- Segunda Fila: Riesgo, Estado, Confianza IA -->
        <div class="row align-items-center">
          <div class="col-md-4 col-lg-3 form-group">
            <label for="filtroRiesgo"><i class="fas fa-exclamation-triangle text-muted mr-1"></i> Riesgo</label>
            <select id="filtroRiesgo" class="form-control custom-select" v-model="filtros.riesgo">
              <option value="">Todos los niveles</option>
              <option value="ALTO">Alto</option>
              <option value="MEDIO">Medio</option>
              <option value="BAJO">Bajo</option>
            </select>
          </div>

          <div class="col-md-4 col-lg-3 form-group">
            <label for="filtroEstado"><i class="fas fa-tasks text-muted mr-1"></i> Estado</label>
            <select id="filtroEstado" class="form-control custom-select" v-model="filtros.estado">
              <option value="">Todos los estados</option>
              <option value="pendiente">Pendiente revisión</option>
              <option value="revisada">Revisada</option>
              <option value="reanalisis">Reanálisis pendiente</option>
            </select>
          </div>

          <div class="col-md-8 col-lg-6 form-group mb-0">
            <div class="d-flex justify-content-between">
              <label for="filtroConfianza"><i class="fas fa-robot text-muted mr-1"></i> Confianza IA mínima</label>
              <span class="badge badge-info">{{ filtros.confianzaMin }}%</span>
            </div>
            <input
              type="range"
              class="custom-range mt-2"
              id="filtroConfianza"
              min="0"
              max="100"
              step="5"
              v-model.number="filtros.confianzaMin"
            >
          </div>
        </div>

        <!-- Botones dentro del body para que se oculten junto con los filtros -->
        <hr class="my-3 border-light">
        <div class="d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-default mr-2" @click="limpiarFiltros">
            <i class="fas fa-undo mr-1"></i> Limpiar
          </button>
          <button type="submit" class="btn btn-primary px-4">
            <i class="fas fa-search mr-1"></i> Buscar
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import ApiService from '../../services/ApiService.js';
export default {

    name: 'FiltrosEvaluaciones',

    props: {

        especialidades: {
            type: Array,
            default: () => []
        },

        medicos: {
            type: Array,
            default: () => []
        }

    },

    emits: ['filtrar'],

    data(){

        return{

            filtros:{

                paciente:'',
                especialidad:'',
                fecha:'',
                riesgo:'',
                estado:'',
                medico:'',
                confianzaMin:0

            }

        }

    },

    methods:{

        aplicarFiltros(){

            this.$emit('filtrar',{...this.filtros});

        },

        limpiarFiltros(){

            this.filtros={

                paciente:'',

                especialidad:'',

                riesgo:'',

                estado:'',

                medico:'',

                confianzaMin:0

            };

            this.$emit('filtrar',{...this.filtros});

        }

    }

}
</script>