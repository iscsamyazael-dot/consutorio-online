
<template>
    <agenda-medica></agenda-medica>
    <dradmin></dradmin>

    <div style="display: flex; gap: 16px; align-items: flex-start;">
        <div style="flex: 2;">
            <calendario></calendario>
        </div>
        <div style="flex: 1;">
            <resumen-medico></resumen-medico>
        </div>
    </div>
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