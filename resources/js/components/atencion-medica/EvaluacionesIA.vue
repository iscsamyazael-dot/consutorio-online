<template>
  <EvaluacionesInteligentes></EvaluacionesInteligentes>

  <FiltrosEvaluacionIA
    :especialidades="especialidades"
    :medicos="medicos"
    @filtrar="onFiltrar"
  ></FiltrosEvaluacionIA>

  <EvaluacionIA
    v-if="vistaActual === 'tabla'"
    :filtros="filtros"
    @ver-evaluacion="abrirInforme"
  />

  <InformeCompletoIA
    v-else-if="vistaActual === 'detalle'"
    :folio="folioSeleccionado"
    @volver="volverATabla"
  />
</template> 

<script>
import EvaluacionesInteligentes from './EvaluacionesInteligentes.vue';
import FiltrosEvaluacionIA from './FiltrosEvaluacionIA.vue';
import EvaluacionIA from './EvaluacionIA.vue';
import InformeCompletoIA from './InformeCompletoIA.vue';

export default {
  name: 'IndexEvaluacionesIA',

  components: {
    EvaluacionesInteligentes,
    FiltrosEvaluacionIA,
    EvaluacionIA,
    InformeCompletoIA,
  },

  props: {
    especialidadesJson: {
      type: [Array, String],
      default: () => [],
    },
    medicosJson: {
      type: [Array, String],
      default: () => [],
    },
  },

  data() {
    return {
      vistaActual: 'tabla',
      folioSeleccionado: null,
      filtros: {},
    };
  },

  computed: {
    // Vue convierte automáticamente los atributos JSON del blade en arrays/objetos,
    // pero por si llegan como string (según cómo esté registrado el elemento raíz),
    // los parseamos de forma segura aquí.
    especialidades() {
      return typeof this.especialidadesJson === 'string'
        ? JSON.parse(this.especialidadesJson || '[]')
        : this.especialidadesJson;
    },
    medicos() {
      return typeof this.medicosJson === 'string'
        ? JSON.parse(this.medicosJson || '[]')
        : this.medicosJson;
    },
  },

  methods: {
    onFiltrar(filtros) {
      this.filtros = filtros;
    },
    abrirInforme(folio) {
      this.folioSeleccionado = folio;
      this.vistaActual = 'detalle';
    },
    volverATabla() {
      this.vistaActual = 'tabla';
      this.folioSeleccionado = null;
    },
  },
};
</script>