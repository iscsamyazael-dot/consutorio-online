<template>
  <div class="master-espirometria">
    <!-- LISTADO (Si no es nueva ni edición) -->
    <listado-valoraciones 
      v-if="!esNuevo && !esEdicion" 
      submodulo="espirometria"
    ></listado-valoraciones>

    <!-- FORMULARIO DE EVALUACIÓN (Si es nueva o edición) -->
    <div v-else class="container-fluid p-0">
      
      <!-- 1. CHAT IA / TRANSCRIPCIÓN EN VIVO -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card card-primary card-outline shadow-sm">
            <div class="card-header bg-info text-white">
              <h3 class="card-title">
                <i class="fas fa-microphone-alt mr-2"></i>
                Asistente IA y Transcripción (Espirometría)
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
                    <span class="direct-chat-name float-left text-info">🤖 Asistente de Neumología IA</span>
                  </div>
                  <div class="direct-chat-text bg-white border">
                    ¡Hola! Puedes dictarme los valores de la espirometría (ej: "FEV1 de 4.12 litros, FVC de 4.75"), subir el PDF del equipo ndd, o indicar antecedentes como asma o tabaquismo.
                  </div>
                </div>

                <div v-for="(msg, index) in conversacion" :key="index" class="mb-3">
                  <div v-if="msg.sender === 'user'" class="direct-chat-msg">
                    <div class="direct-chat-infos clearfix">
                      <span class="direct-chat-name float-left">👨‍⚕️ Médico</span>
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
                  placeholder="Dicta valores, sube el PDF del equipo ndd o escribe..."
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
            <i class="fas fa-lungs mr-2"></i> Reporte de Espirometría - MASSVITAL
            <span class="badge badge-light text-primary ml-2">{{ folioEspirometria }}</span>
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
                <i class="fas fa-table mr-1"></i> 2. Parámetros FVL
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#hoja3" role="tab">
                <i class="fas fa-clipboard-check mr-1"></i> 3. Interpretación y Dictamen
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- 3. TABS CONTENT -->
      <div class="tab-content bg-white p-4 rounded-bottom shadow-sm mb-4">
        
        <!-- HOJA 1: Datos Generales -->
        <div id="hoja1" class="tab-pane fade show active" role="tabpanel">
          <datos-generales-espirometria 
            :modelValue="form.datosGenerales" 
            @update:modelValue="form.datosGenerales = $event"
          ></datos-generales-espirometria>
        </div>

        <!-- HOJA 2: Parámetros FVL -->
        <div id="hoja2" class="tab-pane fade" role="tabpanel">
          <parametros-fvl-espirometria 
            :modelValue="form.parametrosFVL" 
            @update:modelValue="form.parametrosFVL = $event"
          ></parametros-fvl-espirometria>
        </div>

        <!-- HOJA 3: Interpretación -->
        <div id="hoja3" class="tab-pane fade" role="tabpanel">
          <interpretacion-espirometria 
            :modelValue="form.interpretacion" 
            @update:modelValue="form.interpretacion = $event"
          ></interpretacion-espirometria>
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

// 2. Componentes de Sección (carpeta secciones/espirometria)
import DatosGeneralesEspirometria from './secciones/espirometria/DatosGeneralesEspirometria.vue'
import ParametrosFvlEspirometria from './secciones/espirometria/ParametrosFvlEspirometria.vue'
import InterpretacionEspirometria from './secciones/espirometria/InterpretacionEspirometria.vue'

// 3. Servicio API (ajusta la ruta si tu ApiService está en otro lugar, ej: '@/services/ApiService')
import { clinicaTrabajo } from '../../services/ApiService'

export default {
  name: 'MasterEspirometria',
  components: {
    ListadoValoraciones,
    DatosGeneralesEspirometria,
    ParametrosFvlEspirometria,
    InterpretacionEspirometria
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
      
      // Estado centralizado del formulario dividido por secciones lógicas
      form: {
        datosGenerales: {
          folio: '',
          fecha_valoracion: new Date().toISOString().split('T')[0],
          hora_valoracion: '',
          nombre: '',
          id_empleado: '',
          edad: 0,
          sexo: '',
          altura_cm: null,
          peso_kg: null,
          imc: 0,
          origen_etnico: 'Hispano',
          empresa: '',
          remitido_por: '',
          prescrito_por: '',
          fumador: false,
          cigarrillos_dia: 0,
          anos_fumador: 0,
          antecedente_covid: false,
          asma: false,
          epoc: false,
          comentarios: ''
        },
        parametrosFVL: {
          referencia: 'NHANES III',
          interpretacion_predicha: 'GOLD(2008)/Hardie',
          fvc_l: null, fev1_l: null, fev1_fvc_ratio: null, fef25_75: null, pef_l_s: null,
          fet_s: null, fivc_l: null, pif_l_s: null, eotv_l: null, bev_l: null,
          fev1_var_l: null, fvc_var_l: null,
          fev1_porcentaje_predicho: 0, fvc_porcentaje_predicho: 0
        },
        interpretacion: {
          edad_pulmonar: 0,
          calidad_sesion: '', 
          interpretacion_sistema: 'Espirometría Normal',
          diagnostico_medico: '',
          aptitud: '',
          recomendaciones: ''
        }
      }
    }
  },

  computed: {
    folioEspirometria() {
      if (!this.form.datosGenerales.folio || this.form.datosGenerales.folio.includes('****')) {
        const ano = new Date().getFullYear()
        const random = String(Math.floor(Math.random() * 9000) + 1000)
        return `ESP-${ano}-${random}`
      }
      return this.form.datosGenerales.folio
    }
  },

  methods: {
    detectarRuta() {
        const pathname = window.location.pathname
        
        // Detecta /ficha-espirometria/nueva o /ficha-espirometria (sin ID)
        if (pathname.includes('/ficha-espirometria/nueva') || 
            pathname === '/ficha-espirometria' ||
            pathname.endsWith('/ficha-espirometria')) {
        this.esNuevo = true
        this.esEdicion = false
        } 
        // Detecta /ficha-espirometria/{id} para edición
        else if (/\/ficha-espirometria\/\d+/.test(pathname)) {
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
        this.conversacion.push({ sender: 'ia', text: '🎙️ Escuchando valores espirométricos... (Prueba dictando: "FEV1 4.12, FVC 4.75")' })
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
        const response = await clinicaTrabajo.consultarIAEspirometria({
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
      if (!this.form.datosGenerales.nombre) {
        return alert('⚠️ Por favor ingresa el nombre del paciente en la Hoja 1.')
      }
      if (!this.form.interpretacion.aptitud) {
        return alert('⚠️ Es obligatorio seleccionar la APTITUD en la Hoja 3 para poder guardar.')
      }

      this.guardando = true
      try {
        const payload = {
          ...this.form,
          folio: this.folioEspirometria,
          tipo_evaluacion: 'espirometria'
        }

        const response = await clinicaTrabajo.espirometria.crear(payload)

        if (response.data.success) {
          alert('✅ Reporte de Espirometría guardado correctamente.')
          window.location.href = `/clinica/espirometria/${response.data.id}`
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
        window.location.href = '/clinica/espirometria'
      }
    },

    imprimir() {
      alert('🖨️ Generando vista previa para impresión en PDF...')
      // TODO: Integrar html2pdf.js o window.print() con estilos específicos
    },

    async cargarDatosExistentes() {
      // TODO: Lógica para cargar los datos si `esEdicion` es true
      // const id = window.location.pathname.split('/').pop()
      // const response = await clinicaTrabajo.obtenerEspirometria(id)
      // this.form = response.data
    }
  },

  mounted() {
    this.detectarRuta()
    
    // Inicializar folio si es nuevo
    if (this.esNuevo && !this.form.datosGenerales.folio) {
      this.form.datosGenerales.folio = this.folioEspirometria
    }
  }
}
</script>

<style scoped>
.master-espirometria {
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