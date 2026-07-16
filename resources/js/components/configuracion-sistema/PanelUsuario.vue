<template>

    <RegistrarUsuario v-if="vistaActual === 'registro'"@volver="mostrarTabla"/>

    <TablaUsuarios v-if="vistaActual === 'tabla'" @volver="mostrarRegistro"/>

</template>


<script>
import RegistrarUsuario from './RegistrarUsuario.vue';
import TablaUsuarios from './TablaUsuarios.vue';

export default {

    components: {
        RegistrarUsuario,
        TablaUsuarios
    },

    data(){
        return{
            vistaActual: 'registro'
        }
    },

    methods:{

        mostrarTabla(){
            this.vistaActual = 'tabla'
        },

        mostrarRegistro(){
            this.vistaActual = 'registro'
        }

    }

}
</script>