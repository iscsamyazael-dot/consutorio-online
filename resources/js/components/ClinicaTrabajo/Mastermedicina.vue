<template>
  <div class="master-medicina">
    <!-- Renderiza MasterFichaOcupacional (no ValoracionInteligente) -->
    <master-ficha-ocupacional></master-ficha-ocupacional>
  </div>
</template>

<script>
import MasterFichaOcupacional from './MasterFichaOcupacional.vue'

export default {
  name: 'MasterMedicina',
  components: {
    MasterFichaOcupacional
  },
  data() {
    return {}
  },
  mounted() {
    // Medicina usa su propio Master con chat IA
  }
}
</script>

<style scoped>
.master-medicina {
  width: 100%;
  height: 100%;
}
</style>