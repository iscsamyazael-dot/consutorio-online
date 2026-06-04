
<template>
    <agenda-medica></agenda-medica>
    <dradmin></dradmin>
    <calendario></calendario>
    <resumen-medico></resumen-medico>


</template>
<script> 
import AgendaMedica from './agendamedica.vue';
import DrAdmin from './dradmin.vue';
import Calendario from './calendario.vue';  
import ResumenMedico from './Resumenmedico.vue';

    export default {
        components: {
            AgendaMedica,
            DrAdmin,
            Calendario,
            ResumenMedico
        },
        data() {
            return {
                // Aquí puedes agregar datos específicos para MasterCitas si es necesario
            }
        },
        methods: {
            // Aquí puedes agregar métodos específicos para MasterCitas si es necesario
        }
    }
</script>