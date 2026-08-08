<template>
  <div class="evaluaciones-inteligentes-header">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h1 class="m-0">Centro de Evaluaciones IA</h1>
        <p class="text-muted mb-0">
          Historial y auditoría de evaluaciones generadas automáticamente durante las consultas
        </p>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-3 col-6" v-for="(ind, index) in indicadores" :key="index">
        <div class="small-box" :class="`bg-${ind.color}`">
          <div class="inner">
            <h3>{{ cargando ? '…' : ind.valor }}</h3>
            <p>{{ ind.label }}</p>
          </div>
          <div class="icon">
            <i class="fas" :class="ind.icon"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

import ApiService from '../../services/ApiService.js';

export default {
  name: 'EvaluacionesInteligentes',

  data() {
    return {
      cargando: false,
      indicadores: [
        { label: 'Riesgo alto',           valor: 0,    icon: 'fa-exclamation-triangle', color: 'danger'  },
        { label: 'Evaluaciones del día',  valor: 0,    icon: 'fa-calendar-day',          color: 'info'    },
        { label: 'Pendientes de revisión',valor: 0,    icon: 'fa-sync-alt',              color: 'warning' },
        { label: 'Confianza promedio',    valor: '0%', icon: 'fa-brain',                 color: 'success' },
      ],
    };
  },

  mounted() {
    this.cargarIndicadores();
  },

  methods: {
    async cargarIndicadores() {
      this.cargando = true;
      try {
        // Mismo supuesto de prefijo "/api" que en los otros 2 componentes.
        const { data } = await ApiService.get('/api/evaluaciones-ia/indicadores');
        this.indicadores = data;
      } catch (err) {
        console.error('Error al cargar indicadores:', err);
      } finally {
        this.cargando = false;
      }
    },
  },
};
</script>