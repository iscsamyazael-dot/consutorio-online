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
        <kpicards :medicamentos="medicamentos" :cargando="cargando"></kpicards>
        <alertasResumen :medicamentos="medicamentos"></alertasResumen>
        <inventario
            ref="tablaInventario"
            :medicamentos="medicamentos"
            @actualizar-inventario="refrescarInventario">
        </inventario>
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
    import ApiService from '../../services/ApiService.js'
    
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
                vista:'inventario',
                medicamentos: [],
                cargando: true
            }
        },
        mounted(){
            this.obtenerMedicamentos()
        },
        methods: {
            cambiarVista(vistaNueva){
                this.vista = vistaNueva
            },
            // Única fuente de la verdad: se llama al montar la vista y cada
            // vez que se crea/edita un medicamento o se registra un movimiento
            async obtenerMedicamentos(){
                this.cargando = true
                try {
                    const response = await ApiService.get('medicamentos')
                    this.medicamentos = response.data
                } catch (error) {
                    console.error('Error al obtener medicamentos:', error)
                } finally {
                    this.cargando = false
                }
            },
            refrescarInventario(){
                this.obtenerMedicamentos()
            }
        }
}
</script>