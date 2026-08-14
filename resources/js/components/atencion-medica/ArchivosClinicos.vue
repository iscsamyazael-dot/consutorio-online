<template>
<EstudiosMedicos></EstudiosMedicos>

<div class="row">
<IAimagenes></IAimagenes>

<Recomendaciones></Recomendaciones>
</div>
</template>



<script>
import EstudiosMedicos from './EstudiosMedicos.vue';
import IAimagenes from './IAimagenes.vue';
import Recomendaciones from './Recomendaciones.vue';


export default {
    components: {
        EstudiosMedicos,
        IAimagenes,
        Recomendaciones
    },
    data(){
        return{}
    }, 
    methods:{}

}

</script>