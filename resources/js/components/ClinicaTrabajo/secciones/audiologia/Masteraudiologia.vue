<template>
  <div class="master-audiologia">
    <!-- Renderiza ListadoValoraciones con submódulo audiologia -->
    <listado-valoraciones submódulo="audiologia"></listado-valoraciones>
  </div>
</template>

<script>
import ListadoValoraciones from '../../ListadoValoraciones.vue'

export default {
  name: 'MasterAudiologia',
  components: {
    ListadoValoraciones
  },
  data() {
    return {}
  },
  mounted() {
    // Lógica específica de audiología si es necesaria
  }
}
</script>

<style scoped>
.master-audiologia {
  width: 100%;
  height: 100%;
}
</style>