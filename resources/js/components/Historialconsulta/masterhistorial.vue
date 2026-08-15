<template>
    <div>
        <Historialconsulta
          :paciente-id="pacienteId">
        </Historialconsulta>
    </div>
</template>
    <script>
    import Historialconsulta from './historialconsulta.vue';
    export default {
        components: {
            Historialconsulta
        },
        data() {
            return {
                Historialconsulta: '',
            }
        },
    } 

</script>