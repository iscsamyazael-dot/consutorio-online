<template>
    
    <!-- ========================================= -->
    <!-- PANEL IZQUIERDO -->
    <!-- ========================================= -->
    
    <div class="col-lg-3">
        <HistorialClinico></HistorialClinico>
        <AlertasClinicas :sintomas="sintomasDetectados"></AlertasClinicas>
        <ArchivosClinicos></ArchivosClinicos>

    </div>
      
    <!-- ========================================= -->
    <!-- PANEL CENTRAL -->
    <!-- ========================================= -->

      <!-- TRANSCRIPCIÓN -->
    <div class="col-lg-6">
        <TranscripcionLive @actualizarSintomas="actualizarSintomas"></TranscripcionLive>
        <PanelIA :sintomas="sintomasDetectados"></PanelIA>
    </div>
    
    <!-- ========================================= -->
    <!-- PANEL DERECHO -->
    <!-- ========================================= -->
    
    <div class="col-lg-3">
        <RecetaInteligente :sintomas="sintomasDetectados"></RecetaInteligente>
        <DerivacionClinica></DerivacionClinica>
        <SubirArchivos></SubirArchivos>
    </div>
    
</template>

<script>

import TranscripcionLive
from './TranscripcionLive.vue'

import PanelIA
from './PanelIA.vue'

import HistorialClinico
from './HistorialClinico.vue'

import AlertasClinicas
from './AlertasClinicas.vue'

import ArchivosClinicos
from './ArchivosClinicos.vue'

import DerivacionClinica
from './DerivacionClinica.vue'

import SubirArchivos
from './UploadArchivos.vue'

import RecetaInteligente
from './RecetaInteligente.vue'

export default {

    components: {

        TranscripcionLive,
        PanelIA,
        HistorialClinico,
        AlertasClinicas,
        ArchivosClinicos,
        DerivacionClinica,
        SubirArchivos,
        RecetaInteligente

    },
    data(){
        return{
            sintomasDetectados:[]
        }
    },
    methods: {
        actualizarSintomas(sintomas){
            this.sintomasDetectados = sintomas
        }
    }

}

</script>