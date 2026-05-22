<template>
    <!-- ========================================= -->
    <!-- KPI CARDS -->
    <!-- ========================================= -->
     <kpicards></kpicards>
    <!-- ========================================= -->
    <!-- ALERTAS -->
    <!-- ========================================= -->
     <alertas></alertas>

    <!-- INVENTARIO MEDICAMENTOS -->
    <!-- ========================================= -->
     <inventario></inventario>
     <h1>hola mundo</h1>
</template>
<script>
    import kpicards
    from './KPI_CARDS.vue'
    import alertas
    from './alertas.vue'
    import inventario
    from './TablaMedicamentos.vue'
    
    export default {

        components: {
            kpicards,
            alertas,
            inventario
        },
        data(){ 
            return{}
        },
        methods: {
        }
}

</script>