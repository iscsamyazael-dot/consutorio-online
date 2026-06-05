<template>
    <!-- ========================================= -->
    <!-- ACCCIONES DEL MÉNU DE LOS MEDICAMENTOS -->
    <!-- ========================================= -->
     <acciones_botones 
         @cambiarVista="vista = $event"
         @actualizar-inventario="refrescarInventario">
    </acciones_botones>
    <!-- ========================================= -->
    <!-- KPI CARDS -->
    <!-- ========================================= -->
    <template v-if="vista === 'inventario'">
        <kpicards></kpicards>
        <alertasResumen></alertasResumen>
        <inventario ref="tablaInventario"></inventario>
    </template>

    <template v-if="vista === 'alertas'">
        <alerta_farmacia></alerta_farmacia>
    </template>
    
</template>

<script>
    import kpicards from './KPI_CARDS.vue'
    import alertasResumen from './alertasResumen.vue'
    import inventario from './TablaMedicamentos.vue'
    import acciones_botones from './accionesMedicamento.vue'
    import alerta_farmacia from './alertasMedicamentos.vue'
    
    export default {

        components: {
            kpicards,
            alertasResumen,
            inventario,
            acciones_botones,
            alerta_farmacia
        },
        data(){ 
            return{
                vista:'inventario'
            }
        },
        methods: {
            cambiarVista(vistaNueva){
                this.vista = vistaNueva
            },
            refrescarInventario(){
                this.$refs.tablaInventario.obtenerMedicamentos()
            }
        }
}

</script>