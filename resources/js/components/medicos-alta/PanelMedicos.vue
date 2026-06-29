<template>
  <div>
    <!-- 1. Evento para cambiar de vista y ocultar cuando se cambie a la vista registro -->
    <HeaderMedicos v-if="vistaActual === 'tabla'"@ir-a-registro="vistaActual = 'registro'"></HeaderMedicos>
    
    <!-- Si la vista actual es 'tabla', mostramos la lista -->
    <TablaMedicos v-if="vistaActual === 'tabla'"></TablaMedicos>

    <!-- Si la vista actual es 'registro', mostramos el formulario -->
    <!-- Le pasamos un evento @volver por si pones un botón de "Cancelar" o "Regresar" en tu formulario -->
    <RegistroMedicos v-else-if="vistaActual === 'registro'" @volver="vistaActual = 'tabla'"></RegistroMedicos>
  </div>
</template>

<script>
import HeaderMedicos from './HeaderMedicos.vue';
import TablaMedicos from './TablaMedicos.vue';
import RegistroMedicos from './RegistroMedicos.vue';


export default {
    components: {
        HeaderMedicos,
        TablaMedicos,
        RegistroMedicos
    },
    data() {
        return {
            // Definimos que por defecto inicie mostrando la tabla
            vistaActual: 'tabla' 
        }
    },
     methods:{}

}
</script>

