<template>

<HeaderContraseña></HeaderContraseña>
  
        
<div class="row">
<InfoContraseña></InfoContraseña>
<ActualizarContaseña></ActualizarContaseña>

</div>

</template>



<script>
import HeaderContraseña from './HeaderContraseña.vue';
import InfoContraseña from './InfoContraseña.vue';
import ActualizarContaseña from './ActualizarContaseña.vue';



export default {
    components: {
        HeaderContraseña,
        ActualizarContaseña,
        InfoContraseña

    },
    data(){
        return{}
    }, 
    methods:{}

}

</script>