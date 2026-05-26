<template>
    <!-- ========================================= -->
    <!-- ACCCIONES DEL MÉNU DE LOS MEDICAMENTOS -->
    <!-- ========================================= -->
     <acciones_botones></acciones_botones>
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
     
</template>
<script>
    import kpicards
    from './KPI_CARDS.vue'
    import alertas
    from './alertas.vue'
    import inventario
    from './TablaMedicamentos.vue'
    import acciones_botones from './accionesMedicamento.vue'
    
    export default {

        components: {
            kpicards,
            alertas,
            inventario,
            acciones_botones
        },
        data(){ 
            return{}
        },
        methods: {
        }
}

</script>