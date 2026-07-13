<template>
    <div class="container-fluid">
        <div class="row g-4">
            <Consultageneral 
                 :paciente-id="pacienteId">
            </Consultageneral>
            <Informacionpaciente 
                :paciente-id="pacienteId">
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
    data (){
        return{
            pacienteId: ''
        }
    },
    methods:{
        //Funcion que recibe el ID del paciente a traves de la ruta paramaetrizada //
        obtenerPacienteId() {
            this.pacienteId = window.location.pathname.split('/').pop();
            console.log('Paciente ID:',this.pacienteId
            );
        }
    },
    mounted() {
        this.obtenerPacienteId()
    }


 }

</script>