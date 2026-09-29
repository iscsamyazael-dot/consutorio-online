<template>
  <div class="master-ergonomia">
    <!-- Renderiza ListadoValoraciones con submódulo ergonomia -->
    <listado-valoraciones submódulo="ergonomia"></listado-valoraciones>
  </div>
</template>

<script>
import ListadoValoraciones from '../../ListadoValoraciones.vue'

export default {
  name: 'MasterErgonomia',
  components: {
    ListadoValoraciones
  },
  data() {
    return {}
  },
  mounted() {
    // Lógica específica de ergonomía si es necesaria
  }
}
</script>

<style scoped>
.master-ergonomia {
  width: 100%;
  height: 100%;
}
</style>