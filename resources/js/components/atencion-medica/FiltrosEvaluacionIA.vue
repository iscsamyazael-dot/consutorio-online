<template>
  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filtros</h3>
      <div class="card-tools">
        <button type="button" class="btn btn-tool" data-card-widget="collapse">
          <i class="fas fa-minus"></i>
        </button>
      </div>
    </div>

    <div class="card-body">
      <form @submit.prevent="aplicarFiltros">
        <div class="row">
          <div class="col-md-3 form-group">
            <label for="filtroPaciente">Buscar paciente</label>
            <input
              type="text"
              id="filtroPaciente"
              class="form-control"
              placeholder="Nombre del paciente..."
              v-model="filtros.paciente"
            >
          </div>

          <div class="col-md-2 form-group">
            <label for="filtroEspecialidad">Especialidad</label>
            <select id="filtroEspecialidad" class="form-control" v-model="filtros.especialidad">
              <option value="">Todas</option>
              <option v-for="esp in especialidades" :key="esp.value" :value="esp.value">
                {{ esp.label }}
              </option>
            </select>
          </div>

          <div class="col-md-2 form-group">
            <label for="filtroRiesgo">Riesgo</label>
            <select id="filtroRiesgo" class="form-control" v-model="filtros.riesgo">
              <option value="">Todos</option>
              <option value="ALTO">Alto</option>
              <option value="MEDIO">Medio</option>
              <option value="BAJO">Bajo</option>
            </select>
          </div>

          <div class="col-md-2 form-group">
            <label for="filtroEstado">Estado</label>
            <select id="filtroEstado" class="form-control" v-model="filtros.estado">
              <option value="">Todos</option>
              <option value="pendiente">Pendiente revisión</option>
              <option value="revisada">Revisada</option>
              <option value="reanalisis">Reanálisis pendiente</option>
            </select>
          </div>

          <div class="col-md-2 form-group">
            <label for="filtroMedico">Médico</label>
            <select id="filtroMedico" class="form-control" v-model="filtros.medico">
              <option value="">Todos</option>
              <option v-for="med in medicos" :key="med.value" :value="med.value">
                {{ med.label }}
              </option>
            </select>
          </div>

          <div class="col-md-1 form-group d-flex align-items-end">
            <div class="btn-group w-100">
              <button type="submit" class="btn btn-primary" title="Buscar">
                <i class="fas fa-search"></i>
              </button>
              <button type="button" class="btn btn-secondary" title="Limpiar filtros" @click="limpiarFiltros">
                <i class="fas fa-undo"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4 form-group">
            <label for="filtroConfianza">
              Confianza IA mínima: <span>{{ filtros.confianzaMin }}%</span>
            </label>
            <input
              type="range"
              class="custom-range"
              id="filtroConfianza"
              min="0"
              max="100"
              step="5"
              v-model.number="filtros.confianzaMin"
            >
          </div>
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