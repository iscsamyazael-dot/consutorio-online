<template>
    <div>
        <Plataformamedica
        ref="dashboard"
          :paciente-id="pacienteId">
        </Plataformamedica>
        <Panelatencion
          :paciente-id="pacienteId"
          @triage-guardado="onTriageGuardado">
        </Panelatencion>
    </div>
</template>
<script>
import Plataformamedica from './plataformamedica.vue';
import Panelatencion from './panelatencion.vue';

export default {
    components: {
        Plataformamedica,
        Panelatencion
    },

    data() {
        return {
            pacienteId: ''
        }
    },

    methods:{
        //Funcion que recibe el ID del paciente a traves de la ruta paramaetrizada //
        obtenerPacienteId() {
            this.pacienteId = window.location.pathname.split('/').pop();
            console.log('Paciente ID:',this.pacienteId
            );
        },
        onTriageGuardado() {
            this.$refs.dashboard?.cargarDatos()
        }
    },
    mounted() {
        this.obtenerPacienteId()
    }
}
</script>