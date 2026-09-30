<template>
  <div class="container-fluid py-4">
    <!-- BÚSQUEDA DE PACIENTES -->
    <div v-if="!trabajadorSeleccionado" class="row">
      <div class="col-lg-8 mx-auto">
        <div class="card card-primary card-outline shadow-sm">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-id-card mr-2 text-primary"></i>
              Nueva Ficha Médica Ocupacional (WORLDSTRIDE)
            </h3>
          </div>
          <div class="card-body">
            <p class="text-muted">Busca y selecciona al trabajador para iniciar la evaluación ocupacional completa.</p>
            <div class="form-group position-relative">
              <label class="font-weight-bold">Nombre del Trabajador:</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
                <input
                  type="text"
                  class="form-control"
                  placeholder="Buscar por nombre o apellido..."
                  v-model="busqueda"
                  @input="buscarPacientes"
                />
              </div>
              
              <div v-if="buscando" class="mt-2 text-muted">
                <i class="fas fa-spinner fa-spin mr-1"></i> Buscando...
              </div>
              
              <ul v-else-if="resultados.length" class="list-group mt-2">
                <li
                  v-for="p in resultados"
                  :key="p.id"
                  class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                  style="cursor: pointer;"
                  @click="seleccionarTrabajador(p)"
                >
                  <div>
                    <strong>{{ p.nombre }} {{ p.apellido_paterno }} {{ p.apellido_materno }}</strong>
                    <span class="text-muted small d-block">{{ p.edad_formateada || 'Edad no registrada' }}</span>
                  </div>
                  <span class="badge badge-primary">Seleccionar</span>
                </li>
              </ul>
              
              <div v-else-if="busqueda.length >= 2 && !buscando" class="mt-2 text-muted">
                <i class="fas fa-exclamation-circle mr-1"></i> No se encontraron registros.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- EVALUACIÓN ACTIVA -->
    <div v-else class="row">
      
      <!-- CHAT IA (ARRIBA) -->
      <div class="col-12 order-1 mb-4">
        <div class="card card-primary card-outline shadow-sm">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-microphone-alt text-danger mr-2"></i>
              Consulta en Tiempo Real (WORLDSTRIDE)
            </h3>
            <div class="card-tools">
              <span v-if="escuchando" class="badge badge-success mr-2">
                🤖 IA escuchando
              </span>
            </div>
          </div>

          <div class="card-body p-0">
            <div
              ref="chatContainer"
              class="direct-chat-messages p-3 bg-light"
              style="height: 380px; overflow-y: auto;"
            >
              <div class="direct-chat-msg mb-3">
                <div class="direct-chat-infos clearfix">
                  <span class="direct-chat-name float-left text-primary">🤖 Asistente IA</span>
                </div>
                <div class="direct-chat-text bg-white border">
                  ¡Hola! Estoy lista para asistirte. Cuéntame sobre el trabajador, puesto, antecedentes, examen físico, o adjunta documentos (RX, análisis).
                </div>
              </div>

              <div v-for="(msg, index) in conversacion" :key="index" class="mb-3">
                <div v-if="msg.sender === 'user'" class="direct-chat-msg">
                  <div class="direct-chat-infos clearfix">
                    <span class="direct-chat-name float-left">👨‍⚕️ Médico</span>
                  </div>
                  <div class="direct-chat-text bg-primary text-white">
                    {{ msg.text }}
                  </div>
                </div>

                <div v-else class="direct-chat-msg right">
                  <div class="direct-chat-infos clearfix">
                    <span class="direct-chat-name float-right text-primary">🤖 IA</span>
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
                <button class="btn btn-outline-secondary" type="button" @click="$refs.inputArchivo.click()">
                  <i class="fas fa-paperclip"></i>
                </button>
                <input ref="inputArchivo" type="file" hidden @change="seleccionarArchivo" />
              </div>

              <input
                type="text"
                class="form-control"
                placeholder="Escribe o adjunta documentos..."
                v-model="mensajeActual"
                @keyup.enter="enviarMensaje"
              />

              <div class="input-group-append">
                <button class="btn btn-primary" type="button" @click="enviarMensaje">
                  <i class="fas fa-paper-plane"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- CONTENIDO DE HOJAS -->
      <div class="col-12 order-2">
        <div class="card card-primary card-outline card-tabs shadow-sm">
          
          <div class="card-header p-3 bg-light border-bottom">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="mb-0">
                <i class="fas fa-file-alt mr-2 text-info"></i>
                <strong>Hoja {{ hojaActual }} de 3:</strong> 
                <span v-if="hojaActual === 1" class="text-success">Datos Laborales</span>
                <span v-else-if="hojaActual === 2" class="text-warning">Antecedentes Médicos</span>
                <span v-else-if="hojaActual === 3" class="text-info">Examen Físico</span>
              </h5>
              <div class="progress" style="width: 150px; height: 5px;">
                <div class="progress-bar" :style="{ width: (hojaActual / 3 * 100) + '%' }"></div>
              </div>
            </div>
          </div>

          <div class="card-body">
            
            <!-- HOJA 1: DATOS LABORALES -->
            <div v-if="hojaActual === 1">
              <DatosPuesto v-model="form.datos_puesto" />
              <hr class="my-4">
              <ExposiciónRiesgos v-model="form.exposicion_riesgos" />
            </div>

            <!-- HOJA 2: ANTECEDENTES MÉDICOS -->
            <div v-if="hojaActual === 2">
              <Antecedentes v-model="form.antecedentes" />
            </div>

            <!-- HOJA 3: EXAMEN FÍSICO -->
            <div v-if="hojaActual === 3">
              <ClinícoExamen v-model="form.clinico_examen" />
            </div>

          </div>

          <!-- BOTONES NAVEGACIÓN -->
          <div class="card-footer bg-light d-flex justify-content-between align-items-center">
            <button v-if="hojaActual > 1" class="btn btn-secondary" @click="hojaActual--">
              <i class="fas fa-arrow-left mr-1"></i> Anterior
            </button>
            <div v-else></div>
            
            <small class="text-muted">Progreso: {{ hojaActual }} de 3</small>
            
            <div v-if="hojaActual < 3">
              <button class="btn btn-primary" @click="hojaActual++">
                Siguiente <i class="fas fa-arrow-right ml-1"></i>
              </button>
            </div>
            <div v-else>
              <button class="btn btn-success" @click="guardarFichaCompleta" :disabled="guardando">
                <i v-if="!guardando" class="fas fa-save mr-1"></i>
                <i v-if="guardando" class="fas fa-spinner fa-spin mr-1"></i>
                {{ guardando ? 'Guardando...' : 'Guardar Ficha Completa' }}
              </button>
            </div>
          </div>

        </div>
      </div>

    </div>

  </div>
</template>

<script>
import DatosPuesto from '../secciones/medicina-enfermeria/datospuesto.vue'
import ExposiciónRiesgos from '../secciones/medicina-enfermeria/exposicionriesgos.vue'
import ClinícoExamen from '../secciones/medicina-enfermeria/clinicoexamen.vue'
import Antecedentes from '../secciones/medicina-enfermeria/antecedentes.vue'
import { clinicaTrabajo } from '../../../../services/ApiService'

export default {
  name: 'MasterFichaOcupacional',
  components: {
    DatosPuesto,
    ExposiciónRiesgos,
    ClinícoExamen,
    Antecedentes
  },
  data() {
    return {
      hojaActual: 1,
      trabajadorSeleccionado: null,
      busqueda: '',
      resultados: [],
      buscando: false,
      escuchando: false,
      guardando: false,
      conversacion: [],
      mensajeActual: '',
      archivoSeleccionado: null,

      form: {
        datos_puesto: {},
        exposicion_riesgos: {},
        antecedentes: {},
        clinico_examen: {},
        aptitud: ''
      }
    }
  },
  methods: {
    async buscarPacientes() {
      if (this.busqueda.length < 2) {
        this.resultados = []
        return
      }
      
      try {
        this.buscando = true
        const response = await clinicaTrabajo.buscarPacientes({ q: this.busqueda })
        this.resultados = response.data || []
      } catch (error) {
        console.error('Error buscando pacientes:', error)
        this.resultados = []
      } finally {
        this.buscando = false
      }
    },

    seleccionarTrabajador(trabajador) {
      this.trabajadorSeleccionado = trabajador
      this.busqueda = ''
      this.resultados = []
      this.hojaActual = 1
      this.conversacion = []
      this.form = {
        datos_puesto: { paciente_id: trabajador.id },
        exposicion_riesgos: {},
        antecedentes: {},
        clinico_examen: {},
        aptitud: ''
      }
    },

    deseleccionarTrabajador() {
      this.trabajadorSeleccionado = null
      this.form = {
        datos_puesto: {},
        exposicion_riesgos: {},
        antecedentes: {},
        clinico_examen: {},
        aptitud: ''
      }
      this.conversacion = []
    },

    async enviarMensaje() {
      if (!this.mensajeActual.trim()) return

      this.conversacion.push({
        sender: 'user',
        text: this.mensajeActual,
        timestamp: new Date()
      })

      const mensaje = this.mensajeActual
      this.mensajeActual = ''

      try {
        const response = await clinicaTrabajo.consultarIA({
          mensaje: mensaje,
          paciente_id: this.trabajadorSeleccionado.id,
          contexto: this.form
        })

        this.conversacion.push({
          sender: 'ia',
          text: response.data.respuesta || 'No pude procesar tu solicitud.',
          timestamp: new Date()
        })

        if (response.data.campos_completados) {
          this.form = { ...this.form, ...response.data.campos_completados }
        }

        this.$nextTick(() => {
          this.$refs.chatContainer.scrollTop = this.$refs.chatContainer.scrollHeight
        })
      } catch (error) {
        console.error('Error enviando mensaje:', error)
        this.conversacion.push({
          sender: 'ia',
          text: 'Disculpa, hubo un error al procesar tu solicitud.',
          timestamp: new Date()
        })
      }
    },

    seleccionarArchivo(event) {
      this.archivoSeleccionado = event.target.files[0]
    },

    quitarArchivo() {
      this.archivoSeleccionado = null
      this.$refs.inputArchivo.value = ''
    },

    async guardarFichaCompleta() {
      try {
        this.guardando = true

        if (!this.trabajadorSeleccionado || !this.form.datos_puesto.folio) {
          alert('Por favor complete los datos requeridos')
          return
        }

        const datosGuardar = {
          paciente_id: this.trabajadorSeleccionado.id,
          ...this.form,
          conversacion: this.conversacion
        }

        const response = await clinicaTrabajo.guardarFichaCompleta(datosGuardar)

        alert('Ficha médica guardada correctamente')
        this.deseleccionarTrabajador()

      } catch (error) {
        console.error('Error guardando ficha:', error)
        alert('Error al guardar la ficha: ' + (error.response?.data?.message || error.message))
      } finally {
        this.guardando = false
      }
    }
  },

  watch: {
    hojaActual() {
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  }
}
</script>

<style scoped>
.progress {
  background-color: #e9ecef;
}

.progress-bar {
  background-color: #007bff;
  transition: width 0.3s ease;
}
</style>
