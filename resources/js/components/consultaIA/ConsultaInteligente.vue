<template>
    
    <div class="col-lg-3">
        <HistorialClinico :consulta-id="consultaId" :ia-data="iaData"></HistorialClinico>
        <AlertasClinicas :ia-data="iaData"></AlertasClinicas>
        <ArchivosClinicos></ArchivosClinicos>
    </div>

    <div class="col-lg-6">
        <TranscripcionLive
            @actualizarSintomas="actualizarSintomas"
            @actualizarIaData="actualizarIaData"
            @marcarErrorIa="marcarErrorIa"
            @actualizarConsultaId="actualizarConsultaId"
        ></TranscripcionLive>
        <PanelIA :ia-data="iaData" :has-error="iaError"></PanelIA>
    </div>
    
    <div class="col-lg-3">
        <RecetaInteligente :sintomas="sintomasDetectados"></RecetaInteligente>
        <DerivacionClinica :sintomas="sintomasDetectados"></DerivacionClinica>
        <SubirArchivos></SubirArchivos>
    </div>
    
</template>

<script>

import TranscripcionLive from './TranscripcionLive.vue'
import PanelIA from './PanelIA.vue'
import HistorialClinico from './HistorialClinico.vue'
import AlertasClinicas from './AlertasClinicas.vue'
import ArchivosClinicos from './ArchivosClinicos.vue'
import DerivacionClinica from './DerivacionClinica.vue'
import SubirArchivos from './UploadArchivos.vue'
import RecetaInteligente from './RecetaInteligente.vue'

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
            sintomasDetectados: [],
            iaData: null,
            iaError: false,
            consultaId: null
        }
    },
    methods: {
        actualizarSintomas(sintomas){
            this.sintomasDetectados = sintomas
        },
        actualizarIaData(iaData){
            this.iaData = iaData
            this.iaError = false
        },
        marcarErrorIa(){
            this.iaError = true
        },
        actualizarConsultaId(consultaId){
            this.consultaId = consultaId
        }
    }
}

</script>