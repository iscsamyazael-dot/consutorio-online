<template>
    <nuevaconsulta></nuevaconsulta>
    <stracartas></stracartas>
    <registrarconsulta></registrarconsulta>


</template>
<script>
import Nuevaconsulta from './nuevaconsulta.vue';
import Stracartas from './stracartas.vue';  
import Registrarconsulta from './registrarconsulta.vue';

    export default {
        components: {
            Nuevaconsulta,
            Stracartas,
            Registrarconsulta
        },
        data() {
            return {
                // Aquí puedes agregar datos específicos para MasterPrograma si es necesario
            }
        },
        methods: {
            // Aquí puedes agregar métodos específicos para MasterPrograma si es necesario
        }
    }
</script>