<template>
  <div class="master-ergonomia">
    <!-- FORMULARIO DE EVALUACIÓN (Si es nueva o edición) -->
    <div class="container-fluid p-0">

      <!-- 1. CHAT IA / TRANSCRIPCIÓN EN VIVO -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card card-primary card-outline shadow-sm">
            <div class="card-header bg-success text-white">
              <h3 class="card-title">
                <i class="fas fa-microphone-alt mr-2"></i>
                Asistente IA y Transcripción (Ergonomía)
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
                    <span class="direct-chat-name float-left text-success">🤖 Asistente de Ergonomía IA</span>
                  </div>
                  <div class="direct-chat-text bg-white border">
                    ¡Hola! Puedes dictarme los datos de la evaluación ergonómica (ej: "Trabaja levantando cargas de 20 kg", "Sufre de dolor lumbar"), subir estudios o indicar antecedentes.
                  </div>
                </div>

                <div v-for="(msg, index) in conversacion" :key="index" class="mb-3">
                  <div v-if="msg.sender === 'user'" class="direct-chat-msg">
                    <div class="direct-chat-infos clearfix">
                      <span class="direct-chat-name float-left">👨⚕️ Ergonómico</span>
                    </div>
                    <div class="direct-chat-text bg-success text-white">
                      {{ msg.text }}
                    </div>
                  </div>

                  <div v-else class="direct-chat-msg right">
                    <div class="direct-chat-infos clearfix">
                      <span class="direct-chat-name float-right text-success">🤖 IA</span>
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
                  placeholder="Dicta datos ergonómicos, sube estudios o escribe..."
                  v-model="mensajeActual"
                  @keyup.enter="enviarMensaje"
                />

                <div class="input-group-append">
                  <button class="btn btn-success text-white" type="button" @click="enviarMensaje" :disabled="guardando">
                    <i class="fas fa-paper-plane"></i> Enviar
                  </button>
                </div>
              </div>
              <div v-if="archivoSeleccionado" class="mt-2 text-sm text-gray-600">
                 Archivo: {{ archivoSeleccionado.name }}
                <button class="btn btn-sm btn-link text-danger p-0 ml-2" @click="quitarArchivo">Quitar</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. TABS NAVIGATION -->
      <div class="card mb-3 shadow-sm">
        <div class="card-header bg-success text-white">
          <h4 class="mb-0">
            <i class="fas fa-chair mr-2"></i>
            Valoración Ergonómica Ocupacional
            <span class="badge badge-light text-success ml-2">{{ folioErgonomia }}</span>
            <small v-if="esNuevo && folioErgonomia" class="ml-2 text-white-50">
              Folio sugerido, se asignará al guardar
            </small>
          </h4>
        </div>
        <div class="card-body p-0">
          <ul class="nav nav-tabs nav-fill" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" data-toggle="tab" href="#hoja1" role="tab">
                <i class="fas fa-user mr-1"></i> 1. Datos Básicos y Puesto
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#hoja2" role="tab">
                <i class="fas fa-notes-medical mr-1"></i> 2. Antecedentes
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#hoja3" role="tab">
                <i class="fas fa-briefcase mr-1"></i> 3. Hábitos y Factores Laborales
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#hoja4" role="tab">
                <i class="fas fa-stethoscope mr-1"></i> 4. Examen Médico y Hallazgos
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#hoja5" role="tab">
                <i class="fas fa-tools mr-1"></i> 5. Instrumentos y Dictamen
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- 3. TABS CONTENT -->
      <div class="tab-content bg-white p-4 rounded-bottom shadow-sm mb-4">

        <!-- HOJA 1: DATOS BÁSICOS Y PUESTO DE TRABAJO -->
        <div id="hoja1" class="tab-pane fade show active" role="tabpanel">
          <datos-basico-ergonomia
            :modelValue="form.datosBasicos"
            @update:modelValue="form.datosBasicos = $event"
          ></datos-basico-ergonomia>
        </div>

        <!-- HOJA 2: ANTECEDENTES -->
        <div id="hoja2" class="tab-pane fade" role="tabpanel">
          <antecedentes-heredofamiliares
            :modelValue="form.antecedentes"
            @update:modelValue="form.antecedentes = $event"
          ></antecedentes-heredofamiliares>

          <antecedentes-medicos
            :modelValue="form.antecedentes"
            @update:modelValue="form.antecedentes = $event"
          ></antecedentes-medicos>

          <cirugias-medicamentos
            :modelValue="form.antecedentes"
            @update:modelValue="form.antecedentes = $event"
          ></cirugias-medicamentos>
        </div>

        <!-- HOJA 3: HÁBITOS Y FACTORES LABORALES -->
        <div id="hoja3" class="tab-pane fade" role="tabpanel">
          <habitos-consumo-ergonomia
            :modelValue="form.habitos"
            @update:modelValue="form.habitos = $event"
          ></habitos-consumo-ergonomia>

          <factores-laborales-ergonomia
            :modelValue="form.factoresLaborales"
            @update:modelValue="form.factoresLaborales = $event"
          ></factores-laborales-ergonomia>
        </div>

        <!-- HOJA 4: EXAMEN MÉDICO Y HALLAZGOS -->
        <div id="hoja4" class="tab-pane fade" role="tabpanel">
          <examen-medico-ergonomia
            :modelValue="form.examenMedico"
            @update:modelValue="form.examenMedico = $event"
          ></examen-medico-ergonomia>

          <hallazgos-ergonomia
            :modelValue="form.hallazgos"
            @update:modelValue="form.hallazgos = $event"
          ></hallazgos-ergonomia>
        </div>

        <!-- HOJA 5: INSTRUMENTOS Y DICTAMEN -->
        <div id="hoja5" class="tab-pane fade" role="tabpanel">
          <selector-instrumento-ergonomia
            :modelValue="form"
            @update:modelValue="form = $event"
          ></selector-instrumento-ergonomia>

          <instrumento-especifico-ergonomia
            :modelValue="form"
            @update:modelValue="form = $event"
          ></instrumento-especifico-ergonomia>

          <aptitud-dictamen-ergonomia
            :modelValue="form"
            @update:modelValue="form = $event"
          ></aptitud-dictamen-ergonomia>
        </div>
      </div>

      <!-- 4. BOTONES DE ACCIÓN -->
      <div class="card shadow-sm">
        <div class="card-footer bg-light d-flex justify-content-end gap-2">
          <button class="btn btn-secondary" @click="cancelar" :disabled="guardando">
            <i class="fas fa-times mr-2"></i> Cancelar
          </button>
          <button class="btn btn-info text-white" @click="imprimir" :disabled="guardando">
            <i class="fas fa-print mr-2"></i> Vista Previa / Imprimir
          </button>
          <button class="btn btn-success" @click="guardar" :disabled="guardando">
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

// 2. Componentes de Sección (carpeta secciones/ergonomia)
import DatosBasicoErgonomia from './secciones/ergonomia/DatosBasico.vue'
import AntecedentesHeredofamiliares from './secciones/ergonomia/AntecedentesHeredofamiliares.vue'
import AntecedentesMedicos from './secciones/ergonomia/AntecedentesMedicos.vue'
import CirugiasMedicamentos from './secciones/ergonomia/CirugiasMedicamentos.vue'
import HabitosConsumoErgonomia from './secciones/ergonomia/HabitosConsumo.vue'
import FactoresLaboralesErgonomia from './secciones/ergonomia/FactoresLaborales.vue'
import ExamenMedicoErgonomia from './secciones/ergonomia/ExamenMedico.vue'
import HallazgosErgonomia from './secciones/ergonomia/Hallazgos.vue'
import SelectorInstrumentoErgonomia from './secciones/ergonomia/SelectorInstrumento.vue'
import InstrumentoEspecificoErgonomia from './secciones/ergonomia/InstrumentoEspecifico.vue'
import AptitudDictamenErgonomia from './secciones/ergonomia/AptitudDictamen.vue'

// 3. Servicio API
import clinicaTrabajo from '@/services/ApiServiceClinicaTrabajo'
import Swal from 'sweetalert2'

// Escapa el texto para que el HTML del mensaje no se interprete
const escapar = (texto) => String(texto ?? '')
  .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')

// Aviso simple con un solo botón. Devuelve una promesa: await espera a que el usuario lo cierre
const notificar = (icon, title, texto = '') => Swal.fire({
  icon,
  title,
  html: escapar(texto).replace(/\n/g, '<br>'),
  confirmButtonText: 'Entendido',
  confirmButtonColor: '#1E88E5'
})

// Pregunta de sí/no. Devuelve true solo si el usuario confirma
const confirmarAccion = async ({ title, texto, confirmText, cancelText }) => {
  const r = await Swal.fire({
    icon: 'warning',
    title,
    html: escapar(texto),
    showCancelButton: true,
    confirmButtonText: confirmText,
    cancelButtonText: cancelText,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d'
  })
  return r.isConfirmed
}

const CAMPOS_INSTRUMENTO = [
  'rula_puntuacion', 'rula_nivel_riesgo', 'rula_areas_evaluadas',
  'reba_puntuacion', 'reba_nivel_accion', 'reba_areas_evaluadas',
  'niosh_indice_levantamiento', 'niosh_es_seguro', 'niosh_peso_real_kg',
  'niosh_peso_recomendado_kg', 'niosh_variables_evaluadas', 'justificacion_instrumento'
]

const BOOLEANOS_HALLAZGOS = ['posturas_forzadas', 'movimientos_repetitivos', 'manipulacion_cargas']

const VACIO_A_NULL = (valor) => (valor === '' || valor === undefined ? null : valor)

export default {
  name: 'MasterErgonomia',
  components: {
    ListadoValoraciones,
    DatosBasicoErgonomia,
    AntecedentesHeredofamiliares,
    AntecedentesMedicos,
    CirugiasMedicamentos,
    HabitosConsumoErgonomia,
    FactoresLaboralesErgonomia,
    ExamenMedicoErgonomia,
    HallazgosErgonomia,
    SelectorInstrumentoErgonomia,
    InstrumentoEspecificoErgonomia,
    AptitudDictamenErgonomia
  },
  data() {
    return {
      esNuevo: false,
      esEdicion: false,
      guardando: false,
      cargando: false, 
      escuchando: false,
      mensajeActual: '',
      archivoSeleccionado: null,
      conversacion: [],
      valoracionId: null,
      sessionUuid: null,
      usaIA: false,
      confirmaDictamenIA: false,

      // Estado centralizado del formulario dividido por secciones lógicas
      form: {
        datosBasicos: {
          folio: '',
          tipo_evaluacion: '',
          fecha_valoracion: new Date().toISOString().split('T')[0],
          hora_valoracion: '',
          paciente_id: null,
          nombre_paciente: '',
          empresa_cliente_id: null,
          nombre_empresa: '',
          puesto_trabajo_id: null,
          nombre_puesto: '',
          ergonomo_id: null,
          paciente: null
        },
        antecedentes: {
          hf_obesidad: 'no',
          hf_diabetes: 'no',
          hf_hipertension: 'no',
          hf_cancer: 'no',
          hf_nefropatia: 'no',
          hf_otros: 'no',
          am_obesidad: 'no',
          am_diabetes: 'no',
          am_hipertension: 'no',
          am_dislipidemias: 'no',
          am_cardiovasculares: 'no',
          am_renales: 'no',
          am_hepaticos: 'no',
          am_musculo_esqueleticos: 'no',
          am_urologicos: 'no',
          am_infecciosos: 'no',
          menarca_anos: null,
          ciclos: '',
          fum: '',
          metodo_planificacion: '',
          cirugias: 'no',
          cirugias_especifique: '',
          consumo_medicamentos: '',
          alergias: '',
          // ✅ CORREGIDO: Agregar am_otros para antecedentes médicos
          am_otros: ''
        },
        habitos: {
          tabaquismo: false,
          alcoholismo: false,
          varia_consumo_estres: false,
          varia_consumo_tristeza: false,
          escala_fagerstrm: 0,
          escala_audit: 0,
          frecuencia_consumo_alcohol: '',
          refiere_diarrea: false,
          refiere_estrenimiento: false,
          refiere_gastritis: false,
          refiere_ulcera: false,
          refiere_nauseas: false,
          refiere_reflujo: false,
          refiere_vomitos: false,
          refiere_colitis: false
        },
        factoresLaborales: {
          tension_emocional: 'no',
          alta_responsabilidad: 'no',
          carga_excesiva_trabajo: 'no',
          turno_rotativo: 'no',
          turno_nocturno: 'no',
          trabajo_repetitivo: 'no',
          condiciones_entorno: 'no',
          carga_trabajo: 'no',
          falta_control_trabajo: 'no',
          jornadas_trabajo: 'no',
          interferencia_trabajo_familia: 'no',
          relaciones_interpersonales: 'no',
          violencia_laboral: 'no',
          acontecimientos_traumaticos: 'no'
        },
        examenMedico: {
          examen_medico_realizado: false,
          seguimiento_requerido: false,
          plazo_proximo_seguimiento: null,
          diagnostico_ergonomico: '',
          observaciones_examen: '',
          recomendaciones_examen: '',
          restricciones_laborales: '',
          derivacion_especialista: '',
          derivacion_especialista_otro: '',
          hallazgos: {} // ← CRÍTICO: inicializar vacío para los checkboxes
        },
        hallazgos: {
          rula_puntuacion: null,
          rula_nivel_riesgo: '',
          reba_puntuacion: null,
          reba_nivel_accion: '',
          niosh_indice_levantamiento: null,
          niosh_es_seguro: false,
          posturas_forzadas: false,
          movimientos_repetitivos: false,
          manipulacion_cargas: false,
          carga_maxima_manipulada_kg: null
        },
        // Los campos específicos del instrumento se añadirán dinámicamente por SelectorInstrumento
        instrumento_utilizado: '',
        otro_descripcion: '',
        otro_puntuacion: null,
        // Campos de aptitud y dictamen
        aptitud: '',
        restricciones: '',
        recomendaciones: '',
        seguimiento_requerido: false,
        plazo_proximo_seguimiento: null
      }
    }
  },

  computed: {
    folioErgonomia() {
      if (!this.form.datosBasicos.folio || this.form.datosBasicos.folio.includes('****')) {
        const ano = new Date().getFullYear()
        const random = String(Math.floor(Math.random() * 9000) + 1000)
        return `ERG-${ano}-${random}`
      }
      return this.form.datosBasicos.folio
    }
  },

  methods: {
    detectarRuta() {
      const pathname = window.location.pathname

      // Detecta /ergonomia/nueva o /ergonomia (sin ID)
      if (pathname.includes('/ficha-ergonomia') ||
          pathname === '/ergonomia' ||
          pathname.endsWith('/ergonomia')) {
        this.esNuevo = true
        this.esEdicion = false
      }
      // Detecta /ergonomia/{id} para edición
      else if (/\/ficha-ergonomia\/\d+/.test(pathname)) {
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
        this.conversacion.push({
          sender: 'ia',
          text: '🎙️ Escuchando datos ergonómicos... (Prueba dictando: "Trabaja en postura forzada levantando cargas")'
        })
      } else {
        this.conversacion.push({
          sender: 'ia',
          text: '⏹️ Transcripción detenida.'
        })
      }
      this.scrollearChat()
    },

    async enviarMensaje() {
      const textoEnviar = this.mensajeActual.trim()
      if (!textoEnviar) return

      this.conversacion.push({
        sender: 'user',
        text: textoEnviar,
        timestamp: new Date()
      })
      this.mensajeActual = ''
      this.scrollearChat()

      try {
        const { data } = await clinicaTrabajo.post('/ergonomia-consultarIA', {
        modo: 'chat',
        mensaje: textoEnviar,
        contexto: this.contextoIA(),
        historial: this.conversacion.slice(0, -1).map(m => ({ sender: m.sender, text: m.text })),
        session_uuid: this.sessionUuid,
        valoracion_id: this.valoracionId
      })

        this.conversacion.push({
          sender: 'ia',
          text: data.respuesta || 'Sin respuesta.',
          timestamp: new Date()
        })

        if (data.campos_completados) {
          this.fusionarDatosIA(data.campos_completados)
        }

        if (data.advertencias?.length) {
          console.warn('Advertencias de la IA:', data.advertencias)
        }

        this.scrollearChat()
      } catch (error) {
        console.error('Error enviando mensaje a IA:', error)
        this.conversacion.push({
          sender: 'ia',
          text: 'Disculpa, hubo un error al procesar tu solicitud.',
          timestamp: new Date()
        })
        this.scrollearChat()
      }
    },

    fusionarDatosIA(campos) {
      const fusionar = (destino, origen) => {
        for (const clave in origen) {
          const valor = origen[clave];
          if (valor === null || valor === undefined || valor === '') continue;
          if (typeof valor === 'object' && !Array.isArray(valor)) {
            destino[clave] = { ...(destino[clave] || {}) };
            fusionar(destino[clave], valor);
          } else {
            destino[clave] = valor;
          }
        }
      };
      fusionar(this.form, campos);
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

    // Campos planos que valida store()/update(); los nombres son las columnas de la tabla
    construirPayload() {
      const b = this.form.datosBasicos
      const ex = this.form.examenMedico
      const h = this.form.hallazgos

      const instrumento = {}
      CAMPOS_INSTRUMENTO.forEach(c => { instrumento[c] = VACIO_A_NULL(this.form[c] ?? h[c]) })

      const booleanos = {}
      BOOLEANOS_HALLAZGOS.forEach(c => { booleanos[c] = !!h[c] })

      return {
        ...instrumento,
        ...booleanos,
        paciente_id: b.paciente_id,
        empresa_cliente_id: b.empresa_cliente_id,
        puesto_trabajo_id: b.puesto_trabajo_id,
        tipo_evaluacion: VACIO_A_NULL(b.tipo_evaluacion),
        fecha_valoracion: VACIO_A_NULL(b.fecha_valoracion),
        hora_valoracion: VACIO_A_NULL(b.hora_valoracion),
        instrumento_utilizado: VACIO_A_NULL(this.form.instrumento_utilizado),
        aptitud: VACIO_A_NULL(this.form.aptitud),
        restricciones_descripcion: VACIO_A_NULL(this.form.restricciones),
        recomendaciones_puesto: VACIO_A_NULL(this.form.recomendaciones),
        seguimiento_requerido: !!this.form.seguimiento_requerido,
        plazo_proximo_seguimiento: VACIO_A_NULL(this.form.plazo_proximo_seguimiento),
        examen_medico_realizado: !!ex.examen_medico_realizado,
        exploracion_osteomuscular_hallazgos: VACIO_A_NULL(ex.observaciones_examen),
        diagnostico_relacionado: VACIO_A_NULL(ex.diagnostico_ergonomico),
        acciones_recomendadas: VACIO_A_NULL(ex.recomendaciones_examen),
        restricciones_laborales: VACIO_A_NULL(ex.restricciones_laborales),
        derivacion_especialista: VACIO_A_NULL(ex.derivacion_especialista)
      }
    },

    // Contexto para la IA: campos planos (criterios) más secciones anidadas (hallazgos por región)
    contextoIA() {
      return { ...this.form, ...this.construirPayload() }
    },

    async guardar() {
      const b = this.form.datosBasicos
      if (!b.paciente_id) return notificar('warning', 'Falta el paciente', 'Selecciona un paciente en la Hoja 1.')
      if (!b.empresa_cliente_id) return notificar('warning', 'Sin empresa', 'Selecciona una empresa en la Hoja 1.')
      if (!b.tipo_evaluacion) return notificar('warning', 'Falta el tipo de evaluación', 'Selecciona el tipo de evaluación en la Hoja 1.')
      if (!this.form.instrumento_utilizado) return notificar('warning', 'Falta el instrumento', 'Selecciona el instrumento en la Hoja 5.')
      if (!this.form.aptitud) return notificar('warning', 'Falta la aptitud', 'Selecciona la aptitud en la Hoja 5 para poder guardar.')

      this.guardando = true
      try {
        const payload = {
          ...this.form,
          ...this.construirPayload(),
          usa_ia: this.usaIA,
          confirma_dictamen_ia: this.confirmaDictamenIA,
          session_uuid: this.sessionUuid
        }

        const { data } = this.valoracionId
          ? await clinicaTrabajo.put(`/ergonomia/${this.valoracionId}`, payload)
          : await clinicaTrabajo.post('/ergonomia', payload)

        if (data.success) {
          const idGuardado = this.valoracionId || data.id

          const decision = await Swal.fire({
            icon: 'success',
            title: this.valoracionId ? 'Valoración actualizada' : 'Valoración guardada',
            text: 'La valoración se guardó correctamente. ¿Deseas imprimir el reporte?',
            showCancelButton: true,
            confirmButtonText: 'Imprimir reporte',
            cancelButtonText: 'Continuar',
            confirmButtonColor: '#1E88E5',
            cancelButtonColor: '#6c757d'
          })

          // El clic en el modal cuenta como acción del usuario, así que el navegador no bloquea la pestaña
          if (decision.isConfirmed) {
            window.open(`/ergonomia-imprimir/${idGuardado}`, '_blank')
          }

          // Igual que DELLI: solo redirige al crear; al editar se queda en la misma pantalla
          if (!this.valoracionId) {
            window.location.href = `/ficha-ergonomia/${idGuardado}`
          }
        }
      } catch (error) {
        console.error('Error guardando:', error)
        const errores = error.response?.data?.errors
          ? Object.values(error.response.data.errors).flat().join('\n')
          : (error.response?.data?.message || error.message)
        await notificar('error', 'Error al guardar', errores)
      } finally {
        this.guardando = false
      }
    },
    imprimir() {
      if (!this.valoracionId) {
        return notificar('warning', 'Guarda primero', 'El reporte se genera a partir de una valoración guardada.')
      }
      window.open(`/ergonomia-imprimir/${this.valoracionId}`, '_blank')
    },

    async cancelar() {
      const ok = await confirmarAccion({
        title: '¿Cancelar la valoración?',
        texto: 'Se perderán los datos no guardados.',
        confirmText: 'Sí, cancelar',
        cancelText: 'Seguir editando'
      })
      if (ok) window.location.href = '/clinica/ergonomia'
    },

    async cargarDatosExistentes() {
      const id = window.location.pathname.match(/\/ficha-ergonomia\/(\d+)\/?$/)?.[1]
      if (!id) {
        console.warn('No se encontró un ID válido en la URL')
        return
      }

      this.cargando = true
      try {
        const response = await clinicaTrabajo.get(`/ergonomia/${id}`)
        const registro = response.data.data

        this.valoracionId = registro.id
        this.form = {
          ...this.form,
          ...(registro.datos || {}),
          datosBasicos: {
            ...this.form.datosBasicos,
            ...(registro.datos?.datosBasicos || {}),
            folio: registro.folio,
            paciente_id: registro.paciente_id,
            empresa_cliente_id: registro.empresa_cliente_id,
            puesto_trabajo_id: registro.puesto_trabajo_id,
            tipo_evaluacion: registro.tipo_evaluacion,
            fecha_valoracion: registro.fecha_valoracion ? registro.fecha_valoracion.slice(0, 10) : '',
            hora_valoracion: registro.hora_valoracion ? registro.hora_valoracion.slice(0, 5) : '',
            paciente: registro.paciente || null,
            nombre_paciente: registro.paciente?.nombre || '',
            nombre_empresa: registro.empresa?.razon_social || '',
            nombre_puesto: registro.puesto?.nombre || ''
          }
        }
      } catch (error) {
        console.error('Error al cargar la valoración ergonómica:', error)
        await notificar('error', 'Error al cargar la valoración', error.response?.data?.message || error.message)
        window.location.href = '/clinica/ergonomia'
      } finally {
        this.cargando = false
      }
    },

    async cargarDatosPaciente(pacienteId) {
      if (!pacienteId) return;

      try {
        this.cargando = true;
        
        const response = await clinicaTrabajo.get(`/PacientesDetalleClinicaTrabajo/${pacienteId}`);
        const data = response.data.data;
        
        const datosLaborales = data.datos_laborales || [];
        const laboral = datosLaborales.length > 0 ? datosLaborales[datosLaborales.length - 1] : {};

        this.form.datosBasicos = {
          ...this.form.datosBasicos,
          paciente_id: pacienteId,
          nombre_paciente: data.nombre || '',
          empresa_cliente_id: laboral.empresa_cliente_id || null,
          nombre_empresa: laboral.empresa_cliente?.razon_social || '',
          puesto_trabajo_id: laboral.puesto_trabajo_id || null,
          nombre_puesto: laboral.puesto_trabajo?.nombre || '',
          folio: this.folioErgonomia
        };

        // ✅ CORREGIDO: Usar propiedades que sí existen
        if (data.alergias) {
          this.form.antecedentes.alergias = data.alergias;
        }
        
        // ✅ CORREGIDO: Usar am_otros en lugar de patologicos.otros
        if (data.antecedentes_medicos && data.antecedentes_medicos !== 'Ninguno') {
            this.form.antecedentes.am_otros = data.antecedentes_medicos;
        }

        console.log('✅ Datos del paciente prellenados correctamente:', this.form.datosBasicos);

      } catch (error) {
        console.error('❌ Error al cargar datos del paciente:', error);
        await notificar('error', 'Error al cargar el paciente', 'No se pudieron cargar los datos del paciente.')
      } finally {
        this.cargando = false;
      }
    }
  },

  mounted() {
    this.detectarRuta();

    // Inicializar folio si es nuevo
    if (this.esNuevo && !this.form.datosBasicos.folio) {
      this.form.datosBasicos.folio = this.folioErgonomia;
    }

    // ✅ EXTRAER ID DIRECTAMENTE DE LA URL
    const pathname = window.location.pathname;
    // La URL es: /clinica/ergonomia/nueva/4
    // Queremos extraer el "4" del final
    const partes = pathname.split('/');
    const ultimoSegmento = partes[partes.length - 1];
    const pacienteId = !isNaN(ultimoSegmento) ? parseInt(ultimoSegmento) : null;

    if (pacienteId && this.esNuevo) {
      console.log('🔍 ID detectado en URL:', pacienteId);
      this.cargarDatosPaciente(pacienteId);
    } else {
      console.log('ℹ️ No se detectó ID válido en la URL o no es modo nuevo');
    }
  }
}
</script>

<style scoped>
.master-ergonomia {
  background: #f4f6f9;
  min-height: 100vh;
  padding: 15px;
}

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

.direct-chat-text.bg-success {
  background-color: #28a745 !important;
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
  color: #155724;
  border-bottom-color: #28a745;
  background-color: #f8f9fa;
}

.nav-tabs .nav-link.active {
  color: #155724;
  background: #fff;
  border-bottom: 3px solid #28a745;
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