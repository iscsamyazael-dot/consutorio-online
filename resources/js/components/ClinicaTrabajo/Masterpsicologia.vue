<template>
  <div class="master-psicologia">
    <!-- Renderiza ValoracionInteligente si estás en /nueva o /{id} -->
    <valoracion-inteligente 
      v-if="esNuevo || esEdicion" 
      submódulo="psicologia"
    ></valoracion-inteligente>
    
    <!-- Renderiza ListadoValoraciones si estás en el listado principal -->
    <listado-valoraciones 
      v-else 
      submódulo="psicologia"
    ></listado-valoraciones>
  </div>
</template>

<script>
import ListadoValoraciones from './ListadoValoraciones.vue'
import ValoracionInteligente from './ValoracionInteligente.vue'

export default {
  name: 'MasterPsicologia',
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
      
      // Si contiene /nueva → es nueva valoración
      if (pathname.includes('/nueva')) {
        this.esNuevo = true
        this.esEdicion = false
      }
      // Si contiene /psicologia/ y un número al final → es edición
      else if (/\/psicologia\/\d+/.test(pathname)) {
        this.esNuevo = false
        this.esEdicion = true
      }
      // Si no, es el listado
      else {
        this.esNuevo = false
        this.esEdicion = false
      }
    }
  }
}
</script>

<style scoped>
.master-psicologia {
  width: 100%;
  height: 100%;
}
</style>