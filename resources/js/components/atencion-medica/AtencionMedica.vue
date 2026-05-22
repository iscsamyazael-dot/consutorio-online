
<template>
    
    <!-- ========================================= -->
    <!-- PANEL DE ARRIBA -->
    <!-- ========================================= -->
    <TriageClinico></TriageClinico>
   

    <!-- ========================================= -->
    <!-- PANEL CENTRAL -->
    <!-- ========================================= -->
     <ListaPacientesTriage></ListaPacientesTriage>
    
    <!-- ========================================= -->
    <!-- ALERTAS IA -->
    <!-- ========================================= -->
     <div class="row">

  
    <AlertasClinicasIA></AlertasClinicasIA>

    <!-- ========================================= -->
    <!-- RECOMENDACIONES IA -->
    <!-- ========================================= -->
    <RecomendacionesIA></RecomendacionesIA>
   </div>
    </template>

<script>
import TriageClinico from './TriageClinico.vue';
import ListaPacientesTriage from './ListaPacientesTriage.vue';
import AlertasClinicasIA from './AlertasClinicasIA.vue';
import RecomendacionesIA from './RecomendacionesIA.vue';


export default {
    components: {
        TriageClinico,
        ListaPacientesTriage,
        AlertasClinicasIA,
        RecomendacionesIA
    },
    data(){
        return{}
    }, 
    methods:{}

}

</script>
