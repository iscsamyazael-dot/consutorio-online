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

                        <div
                            class="direct-chat-text"
                            :class="msg.error ? 'bg-danger text-white' : 'bg-light'"
                        >

                            {{ msg.texto }}

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- FOOTER -->
        <div class="card-footer">

            <!-- AVISO: consulta no inicializada -->
            <div
                v-if="!consultaId"
                class="alert alert-warning py-1 px-2 mb-2"
                style="font-size:13px;"
            >

                ⚠️ No se pudo inicializar la consulta. Los mensajes no se enviarán hasta reconectar.

                <button
                    class="btn btn-sm btn-link p-0 ml-1"
                    @click="iniciarConsulta"
                >
                    Reintentar
                </button>

            </div>

            <div class="row">

                <div class="col-md-8">

                    <input
                        type="text"
                        class="form-control"
                        placeholder="Simular mensaje..."
                        v-model="nuevoMensaje"
                        :disabled="enviando || !consultaId"
                        @keyup.enter="enviarMensaje"
                    >

                </div>

                <div class="col-md-4">

                    <button
                        class="btn btn-primary btn-block"
                        :disabled="enviando || !consultaId"
                        @click="enviarMensaje"
                    >

                        <span v-if="enviando">
                            <i class="fas fa-spinner fa-spin"></i> Enviando...
                        </span>
                        <span v-else>
                            Enviar mensaje
                        </span>

                    </button>

                </div>

            </div>

            <!-- SÍNTOMAS -->
            <div class="mt-4">

                <h6 class="font-weight-bold text-primary">

                    🤖 Síntomas detectados

                </h6>

                <span
                    v-if="sintomas.length === 0"
                    class="text-muted"
                    style="font-size:13px;"
                >
                    Aún no se detectaron síntomas.
                </span>

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
            mensajes: [],
            enviando: false

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
                this.$emit('actualizarConsultaId', this.consultaId)
                console.log('Consulta ID:', this.consultaId)
                console.log(response.data)
            }catch(error){
                console.error('Error al iniciar consulta:', error)
                this.consultaId = null
            }
        },

        /**
         * Combina los síntomas ya acumulados en la consulta con los
         * detectados en el mensaje más reciente, evitando duplicados
         * (comparación insensible a mayúsculas/espacios). Se conserva
         * la redacción del primer síntoma detectado en cada caso.
         */
        combinarSintomas(actuales, nuevos) {
            const combinados = [...actuales]
            const normalizados = new Set(
                actuales.map(s => s.trim().toLowerCase())
            )

            nuevos.forEach(sintoma => {
                const clave = sintoma.trim().toLowerCase()
                if (!normalizados.has(clave)) {
                    normalizados.add(clave)
                    combinados.push(sintoma)
                }
            })

            return combinados
        },

        async enviarMensaje() {

            if(this.nuevoMensaje === '' || !this.consultaId || this.enviando) return

            this.enviando = true

            // GUARDAR MENSAJE
            const mensajePaciente = this.nuevoMensaje

            // MENSAJE PACIENTE
            this.mensajes.push({

                tipo: 'paciente',
                texto: mensajePaciente

            })

            // LIMPIAR INPUT
            this.nuevoMensaje = ''
            this.scrollBottom()

            // ESPERAR RESPUESTA REAL DE LA IA
            setTimeout(async() => {

                // Guardamos el índice del mensaje "analizando..." para poder reemplazarlo
                const idxAnalizando = this.mensajes.push({

                    tipo: 'ia',
                    texto: 'IA analizando síntomas clínicos...'

                }) - 1

                this.scrollBottom()

                try{
                    const response = await axios.post(
                        urlConsultaIA,
                        {
                            consulta_id: this.consultaId,
                            transcripcion: mensajePaciente,
                            sintomas: this.sintomas
                        }
                    )

                    if(response.data.success) {

                        // SÍNTOMAS DETECTADOS EN ESTE MENSAJE
                        const sintomasNuevos = response.data.ia_data.sintomas || []

                        // ACUMULAMOS con los ya detectados en mensajes anteriores
                        // (antes esto se sobrescribía y se perdía contexto clave,
                        // ej: "accidente"/"dolor de pecho" del primer mensaje se
                        // perdían al llegar "dificultad para respirar" en el segundo)
                        this.sintomas = this.combinarSintomas(this.sintomas, sintomasNuevos)

                        // ENVIAR AL PADRE EL RESULTADO COMPLETO DE LA IA
                        this.$emit('actualizarIaData', response.data.ia_data)
                        this.$emit('actualizarSintomas', this.sintomas)

                        // REEMPLAZAR MENSAJE "analizando..." POR EL DIAGNÓSTICO REAL
                        this.mensajes[idxAnalizando].texto = response.data.ia_data.diagnostico_probable
                            ? `Diagnóstico probable: ${response.data.ia_data.diagnostico_probable}`
                            : 'Análisis completado.'

                    } else {

                        console.error('Backend reportó error:', response.data.error)

                        this.mensajes[idxAnalizando].texto = '⚠️ No se pudo completar el análisis. Intentá de nuevo.'
                        this.mensajes[idxAnalizando].error = true

                        // AVISAR AL PADRE QUE HUBO ERROR
                        this.$emit('marcarErrorIa')

                    }

                }catch(error){

                    console.error('Error al consultar IA:', error)

                    this.mensajes[idxAnalizando].texto = '⚠️ Error al conectar con la IA. Reintentá en unos segundos.'
                    this.mensajes[idxAnalizando].error = true

                    // AVISAR AL PADRE QUE HUBO ERROR
                    this.$emit('marcarErrorIa')

                }finally{
                    this.enviando = false
                }

                this.scrollBottom()

            },1000)
        },

        scrollBottom() {

            this.$nextTick(() => {

                const container = this.$refs.chatContainer

                if (container) {
                    container.scrollTop = container.scrollHeight
                }

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