<template>
    <div class="container-fluid">
        <div class="row g-4">
            <Consultageneral
            :doctor="doctor">
            
            </Consultageneral>
            <Informacionpaciente 
                :paciente-id="pacienteId"
                :doctor="doctor">
            </Informacionpaciente>
        </div>
    </div>
</template>
<script>
import Consultageneral from './consultageneral.vue';
import Informacionpaciente from './informacionpaciente.vue';

export default{
    components:{
        Consultageneral,
        Informacionpaciente
    },
    props: {
        pacienteId: {
            type: [Number, String],
            default: ''
        },
        doctor: {
            type: Object,
            default: () => ({})
        }
    },
    mounted() {
        console.log('Paciente ID recibido de Blade:', this.pacienteId)
        console.log('Doctor recibido de Blade:', this.doctor)
    }
}
</script>