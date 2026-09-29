<template>
  <div class="master-medicina">
    <!-- Renderiza MasterFichaOcupacional directamente -->
    <master-ficha-ocupacional></master-ficha-ocupacional>
  </div>
</template>

<script>
import MasterFichaOcupacional from '../../FichaOcupacional/MasterFichaOcupacional.vue'

export default {
  name: 'MasterMedicina',
  components: {
    MasterFichaOcupacional
  },
  data() {
    return {}
  },
  mounted() {
    // Lógica específica de medicina si es necesaria
  }
}
</script>

<style scoped>
.master-medicina {
  width: 100%;
  height: 100%;
}
</style>