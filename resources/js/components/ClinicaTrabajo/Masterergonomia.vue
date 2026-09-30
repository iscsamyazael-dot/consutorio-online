<template>
  <div class="master-ergonomia">
    <!-- Renderiza ValoracionInteligente si estás en /nueva o /{id} -->
    <valoracion-inteligente 
      v-if="esNuevo || esEdicion" 
      submódulo="ergonomia"
    ></valoracion-inteligente>
    
    <!-- Renderiza ListadoValoraciones si estás en el listado principal -->
    <listado-valoraciones 
      v-else 
      submódulo="ergonomia"
    ></listado-valoraciones>
  </div>
</template>

<script>
import ListadoValoraciones from './ListadoValoraciones.vue'
import ValoracionInteligente from './ValoracionInteligente.vue'

export default {
  name: 'MasterErgonomia',
  components: {
    ListadoValoraciones,
    ValoracionInteligente
  },
  data() {
    return {
      esNuevo: false,
      esEdicion: false
    }
  },
  mounted() {
    this.detectarRuta()
  },
  methods: {
    detectarRuta() {
      const pathname = window.location.pathname
      
      if (pathname.includes('/nueva')) {
        this.esNuevo = true
        this.esEdicion = false
      }
      else if (/\/ergonomia\/\d+/.test(pathname)) {
        this.esNuevo = false
        this.esEdicion = true
      }
      else {
        this.esNuevo = false
        this.esEdicion = false
      }
    }
  }
}
</script>

<style scoped>
.master-ergonomia {
  width: 100%;
  height: 100%;
}
</style>