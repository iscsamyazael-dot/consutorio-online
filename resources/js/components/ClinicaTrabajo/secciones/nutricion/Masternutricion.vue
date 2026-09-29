<template>
  <div class="master-nutricion">
    <!-- Renderiza ListadoValoraciones con submódulo nutricion -->
    <listado-valoraciones submódulo="nutricion"></listado-valoraciones>
  </div>
</template>

<script>
import ListadoValoraciones from '../../ListadoValoraciones.vue'

export default {
  name: 'MasterNutricion',
  components: {
    ListadoValoraciones
  },
  data() {
    return {}
  },
  mounted() {
    // Lógica específica de nutrición si es necesaria
  }
}
</script>

<style scoped>
.master-nutricion {
  width: 100%;
  height: 100%;
}
</style>