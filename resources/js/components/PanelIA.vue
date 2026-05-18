<template>
     <!-- PANEL IA -->
            <div class="card bg-dark">
                <div class="card-header border-0">
                    <h3 class="card-title">
                        <i class="fas fa-robot"></i>
                        Asistente Clínico IA
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h5>{{ diagnosticoPrincipal }}</h5>
                                    <p>{{ compatibilidad }}</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-brain"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h5>{{ alertaPrincipal }}</h5>
                                    <p>{{ descripcionAlerta }}</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <h6 class="text-light">
                            Recomendaciones IA
                        </h6>
                        <ul class="list-group">
                            <li class="list-group-item" v-for="(rec,index) in recomendaciones" :key="index">
                                {{ rec }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

</template>

<script>

export default {
    props:{
        sintomas:{
            type: Array,
            default: () => []
        }
    },
    computed: {
        diagnosticoPrincipal(){
            if(
                this.sintomas.includes('Dolor') &&
                this.sintomas.includes('Náuseas')
            ){
                return 'Gastritis Aguda'
            }
            if(this.sintomas.includes('Fiebre')){
                return 'Posible infección'
            }
            return 'Sin Diagnostico'
        },
        compatibilidad(){
            if(this.sintomas.length > 0){
                return '92% compatibilidad'
            }
            return 'Esperando datos clínicos'
        },
        alertaPrincipal() {
            if(this.sintomas.includes('Fiebre')) {
                return 'Riesgo infeccioso'
            }
            return 'Sin alertas'
        },
        descripcionAlerta() {
            if(this.sintomas.includes('Fiebre')) {
                return 'Posible proceso infeccioso'
            }
            return 'Paciente estable'
        },
        recomendaciones() {
            let recomendaciones = []
            if(this.sintomas.includes('Dolor')) {
                recomendaciones.push(
                    'Solicitar evaluación abdominal'
                )
            }
            if(this.sintomas.includes('Fiebre')) {
                recomendaciones.push(
                    'Solicitar BH completa'
                )
            }
            if(this.sintomas.includes('Náuseas')) {
                recomendaciones.push(
                    'Evaluar dieta irritante'
                )
            }
            if(recomendaciones.length === 0) {
                recomendaciones.push(
                    'Esperando síntomas clínicos'
                )
            }
            return recomendaciones
        }
    }
}
</script>