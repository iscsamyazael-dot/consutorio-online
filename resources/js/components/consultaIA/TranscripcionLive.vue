<template>

    <div class="card card-primary card-outline shadow-sm">

        <!-- HEADER -->
        <div class="card-header">

            <h3 class="card-title">

                <i class="fas fa-microphone-alt text-danger"></i>

                Consulta en Tiempo Real

            </h3>

            <div class="card-tools d-flex align-items-center">

                <!-- Antes siempre visible; ahora refleja el estado real del micrófono -->
                <span v-if="escuchando" class="badge badge-success mr-2">

                    🤖 IA escuchando

                </span>

                <!-- Estado final: reemplaza al botón una vez que la conversación se cortó -->
                <span v-if="consultaFinalizada" class="badge badge-secondary">
                    <i class="fas fa-lock mr-1"></i> Consulta finalizada
                </span>

                <!-- Cortar conversación: separado de "Enviar mensaje" a propósito,
                     para que no se confunda con una acción de envío más. -->
                <button
                    v-else
                    type="button"
                    class="btn btn-sm btn-outline-danger"
                    title="Finalizar y cortar esta conversación"
                    :disabled="!consultaId || finalizando"
                    @click="finalizarConversacion"
                >
                    <span v-if="finalizando">
                        <i class="fas fa-spinner fa-spin"></i> Finalizando...
                    </span>
                    <span v-else>
                        <i class="fas fa-stop-circle"></i> Cortar conversación
                    </span>
                </button>

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

                            <i v-if="msg.archivo" class="fas fa-paperclip mr-1"></i>
                            <i v-if="msg.voz" class="fas fa-microphone mr-1"></i>

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

                    <!-- SISTEMA (ej. aviso de conversación finalizada) -->
                    <div
                        v-if="msg.tipo === 'sistema'"
                        class="text-center"
                    >
                        <span class="badge badge-secondary" style="font-size:12px;">
                            {{ msg.texto }}
                        </span>
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

            <!-- AVISO: conversación finalizada -->
            <div
                v-if="consultaFinalizada"
                class="alert alert-secondary py-1 px-2 mb-2"
                style="font-size:13px;"
            >
                <i class="fas fa-lock mr-1"></i>
                Esta conversación ya fue finalizada. No se pueden enviar más mensajes.
            </div>

            <!-- PREVIEW DE ARCHIVO SELECCIONADO -->
            <div
                v-if="archivoSeleccionado"
                class="alert alert-light border py-1 px-2 mb-2 d-flex justify-content-between align-items-center"
                style="font-size:13px;"
            >
                <span>
                    <i class="fas fa-paperclip mr-1"></i>
                    {{ archivoSeleccionado.name }}
                    ({{ (archivoSeleccionado.size / 1024 / 1024).toFixed(2) }} MB)
                </span>

                <button
                    class="btn btn-sm btn-link text-danger p-0"
                    :disabled="subiendoArchivo"
                    @click="quitarArchivo"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="row">

                <div class="col-md-6">

                    <input
                        type="text"
                        class="form-control"
                        :placeholder="escuchando ? 'Escuchando...' : 'Simular mensaje...'"
                        v-model="nuevoMensaje"
                        :disabled="enviando || !consultaId || consultaFinalizada"
                        @keyup.enter="enviarMensaje"
                    >

                </div>

                <div class="col-md-1">

                    <button
                        class="btn btn-block"
                        :class="escuchando ? 'btn-danger' : 'btn-outline-danger'"
                        type="button"
                        :title="escuchando ? 'Detener escucha' : 'Escuchar'"
                        :disabled="enviando || subiendoArchivo || !consultaId || consultaFinalizada"
                        @click="toggleEscucha"
                    >
                        <i class="fas fa-microphone-alt"></i>
                    </button>

                </div>

                <div class="col-md-2">

                    <input
                        ref="inputArchivo"
                        type="file"
                        class="d-none"
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                        @change="seleccionarArchivo"
                    >

                    <button
                        class="btn btn-outline-secondary btn-block"
                        type="button"
                        title="Adjuntar PDF, Word o imagen"
                        :disabled="enviando || subiendoArchivo || !consultaId || consultaFinalizada"
                        @click="$refs.inputArchivo.click()"
                    >
                        <i class="fas fa-paperclip"></i>
                    </button>

                </div>

                <div class="col-md-3">

                    <button
                        class="btn btn-primary btn-block"
                        :disabled="enviando || subiendoArchivo || !consultaId || consultaFinalizada || (!nuevoMensaje && !archivoSeleccionado)"
                        @click="archivoSeleccionado ? subirArchivo() : enviarMensaje()"
                    >

                        <span v-if="enviando || subiendoArchivo">
                            <i class="fas fa-spinner fa-spin"></i>
                            {{ subiendoArchivo ? 'Subiendo...' : 'Enviando...' }}
                        </span>
                        <span v-else>
                            {{ archivoSeleccionado ? 'Enviar archivo' : 'Enviar mensaje' }}
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
var urlArchivoIA = route + '/consultaIA/archivo'; //Endpoint de subida de archivos - mismo prefijo que urlConsultaIA//

const FORMATOS_PERMITIDOS = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'image/jpeg',
    'image/png'
]

const TAMANIO_MAXIMO_MB = 15

export default {
    props: {
        // Recibido desde ConsultaInteligente.vue. El backend exige
        // paciente_id en /consultaIA, por eso es obligatorio aquí.
        pacienteId: {
            type: [String, Number],
            required: true
        }
    },
    data() {

        return {

            consultaId: null,
            nuevoMensaje: '',
            sintomas: [],
            mensajes: [],
            enviando: false,

            archivoSeleccionado: null,
            subiendoArchivo: false,

            // --- Reconocimiento de voz ---
            escuchando: false,
            recognition: null,
            bufferVoz: '',        // texto ya confirmado dictado por voz

            // --- Cortar conversación ---
            finalizando: false,
            consultaFinalizada: false

        }

    },
    mounted() {
        this.iniciarConsulta()
    },

    beforeDestroy() {
        this.detenerEscucha()
    },
    // Vue 3: si el proyecto corre en Vue 3 puro, este hook cubre la
    // limpieza (beforeDestroy queda como alias para Vue 2 / compat).
    beforeUnmount() {
        this.detenerEscucha()
    },

    methods: {

        async iniciarConsulta(){
            try{
                const response = await axios.post( 
                    urlConsultaIA,
                    {
                        iniciar_consulta: true,
                        paciente_id: this.pacienteId
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

            if(this.nuevoMensaje === '' || !this.consultaId || this.enviando || this.consultaFinalizada) return

            this.enviando = true

            // GUARDAR MENSAJE
            const mensajePaciente = this.nuevoMensaje
            const vinoDeVoz = this.bufferVoz.trim().length > 0
            this.bufferVoz = '' // listo para la próxima dictada, sin arrastrar texto ya enviado

            // MENSAJE PACIENTE
            this.mensajes.push({

                tipo: 'paciente',
                texto: mensajePaciente,
                voz: vinoDeVoz

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
                            paciente_id: this.pacienteId,
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

        /**
         * Valida y guarda el archivo elegido en el input oculto.
         * El envío real ocurre al presionar "Enviar archivo".
         */
        seleccionarArchivo(e) {

            const file = e.target.files[0]

            if (!file) return

            if (!FORMATOS_PERMITIDOS.includes(file.type)) {
                alert('Formato no permitido. Usa PDF, Word (doc/docx) o imagen (jpg/png).')
                e.target.value = ''
                return
            }

            if (file.size / 1024 / 1024 > TAMANIO_MAXIMO_MB) {
                alert(`El archivo supera el límite de ${TAMANIO_MAXIMO_MB}MB.`)
                e.target.value = ''
                return
            }

            this.archivoSeleccionado = file
        },

        quitarArchivo() {
            this.archivoSeleccionado = null
            this.$refs.inputArchivo.value = ''
        },

        /**
         * Sube el archivo seleccionado, lo muestra como mensaje del
         * paciente en el chat, y reemplaza el mensaje "analizando..."
         * con el diagnóstico igual que en enviarMensaje().
         */
        async subirArchivo() {

            if (!this.archivoSeleccionado || !this.consultaId || this.subiendoArchivo) return

            this.subiendoArchivo = true

            const archivo = this.archivoSeleccionado
            const nombreArchivo = archivo.name

            // MENSAJE PACIENTE (marcado como archivo para mostrar el ícono de clip)
            this.mensajes.push({
                tipo: 'paciente',
                texto: `Archivo adjunto: ${nombreArchivo}`,
                archivo: true
            })

            this.archivoSeleccionado = null
            this.$refs.inputArchivo.value = ''
            this.scrollBottom()

            const idxAnalizando = this.mensajes.push({
                tipo: 'ia',
                texto: 'IA leyendo el archivo...'
            }) - 1

            this.scrollBottom()

            const formData = new FormData()
            formData.append('consulta_id', this.consultaId)
            formData.append('paciente_id', this.pacienteId)
            formData.append('archivo', archivo)

            try {

                const response = await axios.post(urlArchivoIA, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })

                if (response.data.success) {

                    const sintomasNuevos = response.data.ia_data?.sintomas || []

                    this.sintomas = this.combinarSintomas(this.sintomas, sintomasNuevos)

                    this.$emit('actualizarIaData', response.data.ia_data)
                    this.$emit('actualizarSintomas', this.sintomas)

                // --- IMPRESIÓN DIRECTA EN LA CONSOLA DEL NAVEGADOR ---
                    if (response.data.debug_usage) {
                        console.log(
                            '%c [DeepSeek] Consumo de Tokens:', 
                            'background: #222; color: #bada55; padding: 2px 5px; border-radius: 3px;',
                            response.data.debug_usage
                        );
                    }

                    this.mensajes[idxAnalizando].texto = response.data.ia_data?.diagnostico_probable
                        ? `Diagnóstico probable (según ${nombreArchivo}): ${response.data.ia_data.diagnostico_probable}`
                        : `Archivo "${nombreArchivo}" analizado.`

                    // Avisamos al padre para que refresque ArchivosClinicos.vue
                    this.$emit('archivoSubido')

                } else {

                    console.error('Backend reportó error:', response.data.error)

                    this.mensajes[idxAnalizando].texto = `⚠️ ${response.data.error || 'No se pudo procesar el archivo.'}`
                    this.mensajes[idxAnalizando].error = true

                    this.$emit('marcarErrorIa')
                }

            } catch (error) {

                console.error('Error al subir archivo:', error)

                const mensajeError = error.response?.data?.error
                    || 'Error al conectar con la IA. Intentá de nuevo.'

                this.mensajes[idxAnalizando].texto = `⚠️ ${mensajeError}`
                this.mensajes[idxAnalizando].error = true

                this.$emit('marcarErrorIa')

            } finally {
                this.subiendoArchivo = false
                this.scrollBottom()
            }
        },

        /*
        |--------------------------------------------------------------------
        | CORTAR CONVERSACIÓN
        |--------------------------------------------------------------------
        | Pide confirmación (es una acción destructiva: ya no se puede
        | seguir escribiendo en esta consulta), detiene el micrófono si
        | estaba activo, bloquea el input/mic/adjuntar/enviar, y avisa
        | al padre por si necesita, por ejemplo, generar la nota PSOAPP
        | final o redirigir a otra vista.
        |
        | Si tu backend tiene un endpoint para cerrar la consulta
        | (ej. PATCH /consultaIA/{id}/finalizar), descomenta y ajusta el
        | bloque axios de abajo; por ahora el corte es a nivel de UI +
        | evento al padre, para no asumir una ruta que no existe todavía.
        */
        async finalizarConversacion() {

            if (!this.consultaId || this.finalizando || this.consultaFinalizada) return

            const confirmado = window.confirm(
                '¿Seguro que quieres cortar esta conversación? Ya no podrás enviar más mensajes.'
            )
            if (!confirmado) return

            this.finalizando = true

            try {

                // Ejemplo si existe un endpoint de cierre en el backend:
                // await axios.post(`${urlConsultaIA}/${this.consultaId}/finalizar`)

                this.detenerEscucha()
                this.consultaFinalizada = true

                this.mensajes.push({
                    tipo: 'sistema',
                    texto: 'La conversación fue finalizada por el médico.'
                })
                this.scrollBottom()

                this.$emit('conversacionFinalizada', this.consultaId)

            } catch (error) {

                console.error('Error al finalizar la conversación:', error)
                alert('No se pudo finalizar la conversación. Intentá de nuevo.')

            } finally {
                this.finalizando = false
            }
        },

        /*
        |--------------------------------------------------------------------
        | RECONOCIMIENTO DE VOZ
        |--------------------------------------------------------------------
        | No duplica nada del pipeline de IA: solo llena `nuevoMensaje` y
        | llama a `enviarMensaje()`, el mismo método que ya usa el input
        | de texto. Así el backend, el chat y los síntomas funcionan
        | exactamente igual sin importar si el mensaje vino escrito o hablado.
        */

        soportaReconocimiento() {
            return 'webkitSpeechRecognition' in window || 'SpeechRecognition' in window
        },

        crearReconocedor() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition
            const rec = new SpeechRecognition()

            rec.lang = 'es-MX'
            // continuous:true causaba que Chrome reiniciara el reconocimiento
            // tan rápido que competía consigo mismo y nunca alcanzaba a
            // capturar la voz (no-speech). En modo no-continuo, Chrome
            // captura una frase completa por sesión de forma confiable,
            // y nosotros reiniciamos manualmente para seguir "escuchando".
            rec.continuous = false
            rec.interimResults = true // muestra texto parcial mientras se habla

            rec.onresult = this.manejarResultadoVoz
            rec.onerror = this.manejarErrorVoz

            // Chrome cierra la sesión al terminar cada frase (o por
            // silencio). Si el médico sigue con el micrófono activo,
            // reiniciamos, pero con un pequeño respiro para no chocar
            // con el cierre anterior.
            rec.onend = () => {
                if (this.escuchando) {
                    setTimeout(() => {
                        if (this.escuchando) rec.start()
                    }, 300)
                }
            }

            return rec
        },

        manejarResultadoVoz(event) {
            let textoFinalNuevo = ''
            let textoInterino = ''

            for (let i = event.resultIndex; i < event.results.length; i++) {
                const transcript = event.results[i][0].transcript
                if (event.results[i].isFinal) {
                    textoFinalNuevo += transcript + ' '
                } else {
                    textoInterino += transcript
                }
            }

            if (textoFinalNuevo) {
                this.bufferVoz += textoFinalNuevo
            }

            // Solo llena el input. NO se envía solo: el médico revisa
            // el texto y presiona "Enviar mensaje" cuando esté listo.
            this.nuevoMensaje = (this.bufferVoz + textoInterino).trim()
        },

        manejarErrorVoz(event) {
            console.error('Error de reconocimiento de voz:', event.error)
            if (event.error === 'no-speech') return // silencio normal, no es un error real
            if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
                alert('No se pudo acceder al micrófono. Verifica los permisos del navegador.')
                this.detenerEscucha()
            }
        },

        toggleEscucha() {
            this.escuchando ? this.detenerEscucha() : this.iniciarEscucha()
        },

        iniciarEscucha() {
            if (!this.consultaId || this.consultaFinalizada) return

            if (!this.soportaReconocimiento()) {
                alert('Este navegador no soporta reconocimiento de voz. Usa Chrome o Edge.')
                return
            }

            if (!this.recognition) this.recognition = this.crearReconocedor()

            this.bufferVoz = ''
            this.escuchando = true
            this.recognition.start()
        },

        // Se detiene ÚNICAMENTE cuando el médico presiona el botón del
        // micrófono otra vez. No manda nada por sí sola: el texto
        // dictado se queda en el input hasta que se presione "Enviar
        // mensaje" manualmente.
        detenerEscucha() {
            if (!this.escuchando) return

            this.escuchando = false
            if (this.recognition) this.recognition.stop()
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