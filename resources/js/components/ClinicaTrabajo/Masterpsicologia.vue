<template>
  <div class="master-psicologia">
    <!-- LISTADO (Si no es nueva ni edición) -->
    <listado-valoraciones
      v-if="!esNuevo && !esEdicion"
      submodulo="psicologia"
    ></listado-valoraciones>

    <!-- FORMULARIO DE EVALUACIÓN (Si es nueva o edición) -->
    <div v-else class="container-fluid p-0">

      <!-- 1. CHAT IA / TRANSCRIPCIÓN EN VIVO (Igual que espirometria) -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card card-primary card-outline shadow-sm">
            <div class="card-header bg-info text-white">
              <h3 class="card-title">
                <i class="fas fa-microphone-alt mr-2"></i>
                Asistente IA y Transcripción (Psicología)
              </h3>
              <div class="card-tools">
                <span v-if="escuchando" class="badge badge-light text-danger mr-2 animate-pulse">
                  🎙️ IA Escuchando...
                </span>
              </div>
            </div>

            <div class="card-body p-0">
              <div
                ref="chatContainer"
                class="direct-chat-messages p-3 bg-light"
                style="height: 200px; overflow-y: auto;"
              >
                <div class="direct-chat-msg mb-3" v-if="conversacion.length === 0">
                  <div class="direct-chat-infos clearfix">
                    <span class="direct-chat-name float-left text-info">🤖 Asistente de Psicología IA</span>
                  </div>
                  <div class="direct-chat-text bg-white border">
                    ¡Hola! Puedes dictarme los eventos traumáticos (ej: "Presenció accidente laboral con lesión grave"), describir factores psicosociales, o indicar síntomas como ansiedad, depresión, burnout.
                  </div>
                </div>

                <div v-for="(msg, index) in conversacion" :key="index" class="mb-3">
                  <div v-if="msg.sender === 'user'" class="direct-chat-msg">
                    <div class="direct-chat-infos clearfix">
                      <span class="direct-chat-name float-left">👨‍⚕️ Psicólogo</span>
                    </div>
                    <div class="direct-chat-text bg-info text-white">
                      {{ msg.text }}
                    </div>
                  </div>

                  <div v-else class="direct-chat-msg right">
                    <div class="direct-chat-infos clearfix">
                      <span class="direct-chat-name float-right text-info">🤖 IA</span>
                    </div>
                    <div class="direct-chat-text bg-white border">
                      {{ msg.text }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="card-footer bg-white border-top">
              <div class="input-group">
                <div class="input-group-prepend">
                  <button class="btn btn-outline-secondary" type="button" @click="$refs.inputArchivo.click()" title="Adjuntar archivo">
                    <i class="fas fa-paperclip"></i>
                  </button>
                  <input ref="inputArchivo" type="file" hidden @change="seleccionarArchivo" accept=".pdf,.xlsx,.csv,.jpg,.png" />

                  <button
                    class="btn"
                    :class="escuchando ? 'btn-danger' : 'btn-outline-secondary'"
                    type="button"
                    @click="alternarEscucha"
                    title="Iniciar/Detener transcripción por voz"
                  >
                    <i :class="escuchando ? 'fas fa-stop' : 'fas fa-microphone'"></i>
                  </button>
                </div>

                <input
                  type="text"
                  class="form-control"
                  placeholder="Dicta eventos, factores psicosociales, síntomas o escribe..."
                  v-model="mensajeActual"
                  @keyup.enter="enviarMensaje"
                />

                <div class="input-group-append">
                  <button class="btn btn-info text-white" type="button" @click="enviarMensaje" :disabled="guardando">
                    <i class="fas fa-paper-plane"></i> Enviar
                  </button>
                </div>
              </div>
              <div v-if="archivoSeleccionado" class="mt-2 text-sm text-gray-600">
                📎 Archivo: {{ archivoSeleccionado.name }}
                <button class="btn btn-sm btn-link text-danger p-0 ml-2" @click="quitarArchivo">Quitar</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. TABS NAVIGATION -->
      <div class="card mb-3 shadow-sm">
        <div class="card-header bg-primary text-white">
          <h4 class="mb-0">
            <i class="fas fa-brain mr-2"></i> Valoración Psicológica - CONSULTORIO ONLINE
            <span class="badge badge-light text-primary ml-2">{{ folioPsicologia }}</span>
          </h4>
        </div>
        <div class="card-body p-0">
          <ul class="nav nav-tabs nav-fill" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" data-toggle="tab" href="#hoja1" role="tab">
                <i class="fas fa-user mr-1"></i> 1. Datos y Antecedentes
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#hoja2" role="tab">
                <i class="fas fa-exclamation-triangle mr-1"></i> 2. Guía I: Evento Traumático
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#hoja3" role="tab">
                <i class="fas fa-chart-bar mr-1"></i> 3. Guía III: Factores Psicosociales
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#hoja4" role="tab">
                <i class="fas fa-stethoscope mr-1"></i> 4. Diagnóstico Clínico
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#hoja5" role="tab">
                <i class="fas fa-check-circle mr-1"></i> 5. Aptitud y Dictamen
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- 3. TABS CONTENT -->
      <div class="tab-content bg-white p-4 rounded-bottom shadow-sm mb-4">

        <!-- HOJA 1: Datos Generales -->
        <div id="hoja1" class="tab-pane fade show active" role="tabpanel">
          <datos-basico-psicologia
            :modelValue="form.datosBasicos"
            @update:modelValue="form.datosBasicos = $event"
          ></datos-basico-psicologia>
        </div>

        <!-- HOJA 2: Guía I Evento Traumático -->
        <div id="hoja2" class="tab-pane fade" role="tabpanel">
          <guia-i-evento-psicologia
            :modelValue="form.guiaIEvento"
            @update:modelValue="form.guiaIEvento = $event"
          ></guia-i-evento-psicologia>
        </div>

        <!-- HOJA 3: Guía III Factores Psicosociales -->
        <div id="hoja3" class="tab-pane fade" role="tabpanel">
          <GuiaIIIFactoresPsicologia
            :modelValue="form.guiaIIIFactores"
            :factores="factoresDisponibles"
            @update:modelValue="form.guiaIIIFactores = $event"
          ></GuiaIIIFactoresPsicologia>
        </div>

        

        <!-- HOJA 4: Diagnóstico -->
        <div id="hoja4" class="tab-pane fade" role="tabpanel">
          <diagnostico-psicologia
            :modelValue="form.diagnostico"
            @update:modelValue="form.diagnostico = $event"
          ></diagnostico-psicologia>
        </div>

        <!-- HOJA 5: Aptitud -->
        <div id="hoja5" class="tab-pane fade" role="tabpanel">
          <aptitud-dictamen-psicologia
            :modelValue="form.aptitudDictamen"
            @update:modelValue="form.aptitudDictamen = $event"
          ></aptitud-dictamen-psicologia>
        </div>
      </div>

      <!-- 4. BOTONES DE ACCIÓN -->
      <div class="card shadow-sm">
        <div class="card-footer bg-light d-flex justify-content-end gap-2">
          <button class="btn btn-secondary" @click="cancelar" :disabled="guardando">
            <i class="fas fa-times mr-2"></i> Cancelar
          </button>
          <button class="btn btn-info text-white" @click="imprimir" :disabled="guardando">
            <i class="fas fa-print mr-2"></i> Imprimir Reporte
          </button>
          <button class="btn btn-primary" @click="guardar" :disabled="guardando">
            <i class="fas fa-save mr-2"></i> {{ guardando ? 'Guardando...' : 'Guardar Evaluación' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
// 1. Componente de Listado (misma carpeta)
import ListadoValoraciones from './ListadoValoraciones.vue'

// 2. Componentes de Sección (carpeta secciones/psicologia)
import DatosBasicoPsicologia from './secciones/psicologia/DatosBasico.vue'
import GuiaIEventoPsicologia from './secciones/psicologia/GuiaIEvento.vue'
import GuiaIIIFactoresPsicologia from './secciones/psicologia/FactoresPsicosociales.vue'
import DiagnosticoPsicologia from './secciones/psicologia/Diagnostico.vue'
import AptitudDictamenPsicologia from './secciones/psicologia/AptitudDictamen.vue'

// 3. Servicio API (ajusta la ruta si tu ApiService está en otro lugar, ej: '@/services/ApiService')
import { clinicaTrabajo } from '../../services/ApiService.js'

export default {
  name: 'MasterPsicologia',
  components: {
    ListadoValoraciones,
    DatosBasicoPsicologia,
    GuiaIEventoPsicologia,
    GuiaIIIFactoresPsicologia, 
    DiagnosticoPsicologia,
    AptitudDictamenPsicologia
  },
  data() {
    return {
      esNuevo: false,
      esEdicion: false,
      guardando: false,
      escuchando: false,
      mensajeActual: '',
      archivoSeleccionado: null,
      conversacion: [],
      factoresDisponibles: [],

      // Estado centralizado del formulario dividido por secciones lógicas
      form: {
        datosBasicos: {
          folio: '',
          fecha_valoracion: new Date().toISOString().split('T')[0],
          hora_valoracion: '',
          paciente_id: null,
          empresa_cliente_id: null,
          puesto_trabajo_id: null,
          psicologo_id: null
        },
        guiaIEvento: {
          guia_ref_i_aplicada: false,
          ha_presenciado_evento_traumatico: false,
          descripcion_evento_traumatico: '',
          fecha_evento_traumatico: '',
          requiere_canalizacion_imss: false
        },
        guiaIIIFactores: {
          guia_ref_iii_aplicada: false,
          ambiente_laboral_descripcion: '',
          puntuacion_riesgo_texto: '',
          factores_ids: [],
          factores_severidad: []
        },
        diagnostico: {
          diagnostico_clinico: '',
          codigo_cie11: '',
          relacionado_con_trabajo: false
        },
        aptitudDictamen: {
          aptitud: '',
          restricciones: '',
          recomendaciones: '',
          requiere_seguimiento: false,
          plazo_proximo_seguimiento: null,
          canalizado_a: '',
          lugar_canalizacion: ''
        }
      }
    }
  },
  computed: {
    folioPsicologia() {
      if (!this.form.datosBasicos.folio || this.form.datosBasicos.folio.includes('****')) {
        const ano = new Date().getFullYear()
        const random = String(Math.floor(Math.random() * 9000) + 1000)
        return `PSI-${ano}-${random}`
      }
      return this.form.datosBasicos.folio
    }
  },
  methods: {
    detectarRuta() {
        const pathname = window.location.pathname

        // Detecta /psicologia/nueva o /psicologia (sin ID)
        if (pathname.includes('/psicologia/nueva') ||
            pathname === '/psicologia' ||
            pathname.endsWith('/psicologia')) {
        this.esNuevo = true
        this.esEdicion = false
        }
        // Detecta /psicologia/{id} para edición
        else if (/\/psicologia\/\d+/.test(pathname)) {
        this.esNuevo = false
        this.esEdicion = true
        this.cargarDatosExistentes()
        }
        // Cualquier otra ruta muestra el listado
        else {
        this.esNuevo = false
        this.esEdicion = false
        }
    },

    alternarEscucha() {
      this.escuchando = !this.escuchando
      if (this.escuchando) {
        this.conversacion.push({ sender: 'ia', text: '🎙️ Escuchando datos psicológicos... (Prueba dictando: "Presenció robo a mano armada en el trabajo")' })
      } else {
        this.conversacion.push({ sender: 'ia', text: '⏹️ Transcripción detenida.' })
      }
      this.scrollearChat()
    },

    async enviarMensaje() {
      if (!this.mensajeActual.trim() && !this.archivoSeleccionado) return

      const textoEnviar = this.mensajeActual
      this.conversacion.push({
        sender: 'user',
        text: this.archivoSeleccionado ? `[Archivo: ${this.archivoSeleccionado.name}] ${textoEnviar}` : textoEnviar,
        timestamp: new Date()
      })
      this.mensajeActual = ''
      this.scrollearChat()

      try {
        // Llamada al backend para procesar el mensaje o el archivo con IA
        const response = await clinicaTrabajo.consultarIAPsicologia({
          mensaje: textoEnviar,
          archivo: this.archivoSeleccionado,
          contexto_actual: this.form
        })

        this.conversacion.push({
          sender: 'ia',
          text: response.data.respuesta || 'Procesando información...',
          timestamp: new Date()
        })

        if (response.data.campos_completados) {
          this.fusionarDatosIA(response.data.campos_completados)
        }

        this.quitarArchivo()
        this.scrollearChat()
      } catch (error) {
        console.error('Error enviando mensaje a IA:', error)
        this.conversacion.push({
          sender: 'ia',
          text: '❌ Disculpa, hubo un error al procesar tu solicitud o el archivo.',
          timestamp: new Date()
        })
      }
    },

    fusionarDatosIA(nuevosDatos) {
      // Fusiona recursivamente los datos que la IA extrae en el objeto form
      for (const seccion in nuevosDatos) {
        if (this.form[seccion]) {
          this.form[seccion] = { ...this.form[seccion], ...nuevosDatos[seccion] }
        }
      }
    },

    seleccionarArchivo(event) {
      this.archivoSeleccionado = event.target.files[0]
    },

    quitarArchivo() {
      this.archivoSeleccionado = null
      if (this.$refs.inputArchivo) this.$refs.inputArchivo.value = ''
    },

    scrollearChat() {
      this.$nextTick(() => {
        if (this.$refs.chatContainer) {
          this.$refs.chatContainer.scrollTop = this.$refs.chatContainer.scrollHeight
        }
      })
    },

    async guardar() {
      // Validar campos requeridos
      if (!this.form.datosBasicos.paciente_id) {
        return alert('⚠️ Por favor selecciona un paciente en la Hoja 1.')
      }
      if (!this.form.datosBasicos.empresa_cliente_id) {
        return alert('⚠️ Por favor selecciona una empresa en la Hoja 1.')
      }
      if (!this.form.aptitudDictamen.aptitud) {
        return alert('⚠️ Es obligatorio seleccionar la APTITUD en la Hoja 5 para poder guardar.')
      }

      this.guardando = true
      try {
        const payload = {
          ...this.form,
          folio: this.folioPsicologia,
          tipo_evaluacion: 'psicologia'
        }

        const response = await clinicaTrabajo.guardarPsicologia(payload)

        if (response.data.success) {
          alert('✅ Valoración Psicológica guardada correctamente.')
          window.location.href = `/clinica/psicologia/${response.data.id}`
        }
      } catch (error) {
        console.error('Error guardando:', error)
        alert('❌ Error al guardar: ' + (error.response?.data?.message || error.message))
      } finally {
        this.guardando = false
      }
    },

    cancelar() {
      if (confirm('¿Está seguro de que desea cancelar? Se perderán los datos no guardados.')) {
        window.location.href = '/clinica/psicologia'
      }
    },

    imprimir() {
      alert('🖨️ Generando vista previa para impresión en PDF...')
      // TODO: Integrar html2pdf.js o window.print() con estilos específicos
    },

    async cargarDatosExistentes() {
      // TODO: Lógica para cargar los datos si `esEdicion` es true
      // const id = window.location.pathname.split('/').pop()
      // const response = await clinicaTrabajo.obtenerPsicologia(id)
      // this.form = response.data
    },

    async cargarFactores() {
      try {
        const response = await clinicaTrabajo.catalogo.factoresPsicosociales()
        this.factoresDisponibles = response.data || response
      } catch (error) {
        console.error('Error cargando factores psicosociales:', error)
        // Usa datos mock si hay error
        this.factoresDisponibles = [
          { id: 1, nombre: 'Carga mental', descripcion: 'Demanda cognitiva' },
          { id: 2, nombre: 'Control', descripcion: 'Falta de autonomía' },
          { id: 3, nombre: 'Apoyo social', descripcion: 'Falta de apoyo' }
        ]
      }
    }
  },

  mounted() {
    this.detectarRuta()
    this.cargarFactores()

    // Inicializar folio si es nuevo
    if (this.esNuevo && !this.form.datosBasicos.folio) {
      this.form.datosBasicos.folio = this.folioPsicologia
    }
  }
}
</script>

<style scoped>
.master-psicologia {
  background: #f4f6f9;
  min-height: 100vh;
  padding: 15px;
}

/* Chat styling (AdminLTE / Bootstrap compatible) */
.direct-chat-msg {
  margin-right: 0;
  margin-left: 0;
  display: flex;
  flex-direction: column;
}

.direct-chat-infos {
  display: block;
  margin-bottom: 5px;
  font-size: 0.85rem;
}

.direct-chat-name {
  font-weight: 600;
}

.direct-chat-text {
  display: inline-block;
  padding: 8px 12px;
  border-radius: 8px;
  position: relative;
  max-width: 85%;
  word-wrap: break-word;
}

.direct-chat-text.bg-info {
  background-color: #17a2b8 !important;
  color: white !important;
  border-bottom-left-radius: 2px;
}

.direct-chat-text.bg-white {
  background-color: white !important;
  border: 1px solid #dee2e6 !important;
  color: #333 !important;
  border-bottom-right-radius: 2px;
}

.right .direct-chat-msg {
  align-items: flex-end;
}

.right .direct-chat-infos {
  text-align: right;
}

.animate-pulse {
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

/* Tabs styling */
.nav-tabs {
  border-bottom: 2px solid #dee2e6;
}

.nav-tabs .nav-link {
  color: #495057;
  border: none;
  border-bottom: 3px solid transparent;
  font-weight: 500;
  transition: all 0.2s ease;
  padding: 12px 15px;
}

.nav-tabs .nav-link:hover {
  color: #004085;
  border-bottom-color: #007bff;
  background-color: #f8f9fa;
}

.nav-tabs .nav-link.active {
  color: #004085;
  background: #fff;
  border-bottom: 3px solid #007bff;
  font-weight: 600;
}

.tab-content {
  min-height: 400px;
}

.tab-pane {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(5px); }
  to { opacity: 1; transform: translateY(0); }
}

.card-footer {
  border-top: 1px solid #dee2e6;
  padding: 15px;
}

.gap-2 {
  gap: 10px;
}

.btn {
  min-width: 130px;
}
</style>