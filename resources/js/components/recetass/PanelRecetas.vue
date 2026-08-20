<template>

<HeaderRecetas></HeaderRecetas>
<CartasRecetas></CartasRecetas>
<TablaRecetas></TablaRecetas>
</template>


<script>
import CartasRecetas from './CartasRecetas.vue';
import HeaderRecetas from './HeaderRecetas.vue';
import TablaRecetas from './TablaRecetas.vue';



export default {
    components: {
        CartasRecetas,
        HeaderRecetas,
        TablaRecetas
        
    },
    data(){
        return{}
    }, 
    methods:{}

}


</script>