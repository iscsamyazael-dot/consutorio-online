<template>
  <div class="master-psicologia">
    <!-- Renderiza ListadoValoraciones con submódulo psicologia -->
    <listado-valoraciones submódulo="psicologia"></listado-valoraciones>
  </div>
</template>

<script>
import ListadoValoraciones from '../../ListadoValoraciones.vue'

export default {
  name: 'MasterPsicologia',
  components: {
    ListadoValoraciones
  },
  data() {
    return {}
  },
  mounted() {
    // Lógica específica de psicología si es necesaria
  }
}
</script>

<style scoped>
.master-psicologia {
  width: 100%;
  height: 100%;
}
</style>