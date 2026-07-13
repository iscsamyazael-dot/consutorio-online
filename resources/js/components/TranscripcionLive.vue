<template>

    <div class="card card-primary card-outline shadow-sm">

        <!-- HEADER -->
        <div class="card-header">

            <h3 class="card-title">

                <i class="fas fa-microphone-alt text-danger"></i>

                Consulta en Tiempo Real

            </h3>

            <div class="card-tools">

                <span class="badge badge-success">

                    🤖 IA escuchando

                </span>

            </div>

        </div>

        <!-- BODY -->
        <div class="card-body p-0">

            <div
                ref="chatContainer"
                class="direct-chat-messages p-3"
                style="height:450px; overflow-y:auto;"
            >

                <!-- MENSAJES -->
                <div
                    v-for="(msg,index) in mensajes"
                    :key="index"
                    class="mb-3"
                >

                    <!-- MÉDICO -->
                    <div
                        v-if="msg.tipo === 'medico'"
                        class="direct-chat-msg"
                    >

                        <div class="direct-chat-infos clearfix">

                            <span class="direct-chat-name float-left">

                                👨‍⚕️ Médico

                            </span>

                        </div>

                        <div class="direct-chat-text bg-primary">

                            {{ msg.texto }}

                        </div>

                    </div>

                    <!-- PACIENTE -->
                    <div
                        v-if="msg.tipo === 'paciente'"
                        class="direct-chat-msg right"
                    >

                        <div class="direct-chat-infos clearfix">

                            <span class="direct-chat-name float-right">

                                🧑 Paciente

                            </span>

                        </div>

                        <div class="direct-chat-text">

                            {{ msg.texto }}

                        </div>

                    </div>

                    <!-- IA -->
                    <div
                        v-if="msg.tipo === 'ia'"
                        class="direct-chat-msg"
                    >

                        <div class="direct-chat-infos clearfix">

                            <span
                                class="direct-chat-name float-left text-primary"
                            >

                                🤖 IA Clínica

                            </span>

                        </div>

                        <div class="direct-chat-text bg-light">

                            {{ msg.texto }}

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- FOOTER -->
        <div class="card-footer">

            <div class="row">

                <div class="col-md-8">

                    <input
                        type="text"
                        class="form-control"
                        placeholder="Simular mensaje..."
                        v-model="nuevoMensaje"
                        @keyup.enter="enviarMensaje"
                    >

                </div>

                <div class="col-md-4">

                    <button
                        class="btn btn-primary btn-block"
                        @click="enviarMensaje"
                    >

                        Enviar mensaje

                    </button>

                </div>

            </div>

            <!-- SÍNTOMAS -->
            <div class="mt-4">

                <h6 class="font-weight-bold text-primary">

                    🤖 Síntomas detectados

                </h6>

                <span
                    v-for="(sintoma,index) in sintomas"
                    :key="index"
                    class="badge badge-warning mr-2 mb-2"
                >

                    {{ sintoma }}

                </span>

            </div>

        </div>

    </div>

</template>

<script>
import axios from 'axios'
var route = document.querySelector("[name=route]").value //Esta linea sirve para las rutas parametrizadas //

var urlConsultaIA = route + '/consultaIA'; //Se consume la ruta de la API que se encuentra en el archivo web//
export default {
    data() {

        return {
            
            consultaId: null,
            nuevoMensaje: '',
            sintomas: [],
            mensajes: [

                {
                    tipo: 'medico',
                    texto: '¿Dónde siente el dolor?'
                },

                {
                    tipo: 'paciente',
                    texto: 'Tengo dolor abdominal.'
                },

                {
                    tipo: 'ia',
                    texto: 'IA detectó posible dolor abdominal.'
                }

            ]

        }

    },
     mounted() {
        this.iniciarConsulta()
    },

    methods: {

        async iniciarConsulta(){
            try{
                const response = await axios.post(
                    urlConsultaIA,
                    {
                        iniciar_consulta:true
                    }
                )
                this.consultaId = response.data.consulta_id
                console.log('Consulta ID:', this.consultaId)
                console.log(response.data);
            }catch(error){
                console.error(error)
            }
        },

       async enviarMensaje() {

            if(this.nuevoMensaje === '') return
            
            //  GUARDAR MENSAJE
            const mensajePaciente = this.nuevoMensaje

            // MENSAJE PACIENTE
            this.mensajes.push({

                tipo: 'paciente',
                texto: mensajePaciente

            })

            // LIMPIAR IMPUTS
            this.nuevoMensaje = ''
            this.scrollBottom()

            // IA SIMULADA
            setTimeout(async() => {

                this.mensajes.push({

                    tipo: 'ia',
                    texto: 'IA analizando síntomas clínicos...'

                })



                // DETECTAR SINTOMAS
                this.detectarSintomas(mensajePaciente)

                //ENVIAR AL PADRE(CONSULTAINTELIGENTE)
                this.$emit(
                    'actualizarSintomas',
                    this.sintomas
                )
                
                try{
                    console.log(this.consultaId)
                    const response = await axios.post(
                        urlConsultaIA,
                        {
                            consulta_id: this.consultaId,
                            transcripcion:mensajePaciente,
                            sintomas:this.sintomas
                        }
                    )
                    console.log(response.data)
                }catch(error){
                    console.error(error)
                }

                this.scrollBottom()

            },1000)
        },

        detectarSintomas(texto) {

            // NORMALIZAR TEXTO
            texto = texto
                .toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")

            // DOLOR
            if(texto.includes('dolor')) {

                if(!this.sintomas.includes('Dolor')) {

                    this.sintomas.push('Dolor')

                }

            }

            // NAUSEAS
            if(
                texto.includes('nausea') ||
                texto.includes('nauseas')
            ) {

            if(!this.sintomas.includes('Náuseas')) {

                this.sintomas.push('Náuseas')

            }

            }

            // FIEBRE
            if(texto.includes('fiebre')) {

            if(!this.sintomas.includes('Fiebre')) {

                this.sintomas.push('Fiebre')

            }

            }

        },

        scrollBottom() {

            this.$nextTick(() => {

                const container = this.$refs.chatContainer

                container.scrollTop = container.scrollHeight

            })
        }
    }

}

</script>

<style scoped>

.direct-chat-messages{
    background: #f4f6f9;
}

.direct-chat-text{
    border-radius: 10px;
}

.badge{
    font-size: 13px;
}

</style>