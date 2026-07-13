<template>

<HeaderPerfil></HeaderPerfil>
  
        
<div class="row">
<PerfilUsuario></PerfilUsuario>

<InfoUsuario></InfoUsuario>
    </div>

</template>



<script>
import HeaderPerfil from './HeaderPerfil.vue';
import PerfilUsuario from './PerfilUsuario.vue';
import InfoUsuario from './InfoUsuario.vue';


export default {
    components: {
        HeaderPerfil,
        PerfilUsuario,
        InfoUsuario

    },
    data(){
        return{}
    }, 
    methods:{}

}

</script>