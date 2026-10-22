<template>
  <div class="master-ergonomia">
    <!-- LISTADO (Si no es nueva ni edición) -->
    <listado-valoraciones
      v-if="!esNuevo && !esEdicion"
      submodulo="ergonomia"
    ></listado-valoraciones>

    <!-- FORMULARIO DE EVALUACIÓN (Si es nueva o edición) -->
    <div v-else class="container-fluid p-0">

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
      if (pathname.includes('/ergonomia/nueva') ||
          pathname === '/ergonomia' ||
          pathname.endsWith('/ergonomia')) {
        this.esNuevo = true
        this.esEdicion = false
      }
      // Detecta /ergonomia/{id} para edición
      else if (/\/ergonomia\/\d+/.test(pathname)) {
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
        const response = await clinicaTrabajo.consultarIAErgonomia({
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
          text: ' Disculpa, hubo un error al procesar tu solicitud o el archivo.',
          timestamp: new Date()
        })
      }
    },

    fusionarDatosIA(nuevosDatos) {
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
      if (!this.form.datosBasicos.paciente_id) {
        return alert('⚠️ Por favor selecciona un paciente en la Hoja 1.')
      }
      if (!this.form.datosBasicos.empresa_cliente_id) {
        return alert('⚠️ Por favor selecciona una empresa en la Hoja 1.')
      }
      if (!this.form.aptitud) {
        return alert('⚠️ Es obligatorio seleccionar la APTITUD en la Hoja 5 para poder guardar.')
      }

      this.guardando = true
      try {
        const payload = {
          ...this.form,
          folio: this.folioErgonomia,
          tipo_evaluacion: this.form.datosBasicos.tipo_evaluacion || 'ergonomia'
        }

        const response = await clinicaTrabajo.ergonomia.crear(payload)

        if (response.data.success) {
          alert('✅ Valoración Ergonómica guardada correctamente.')
          window.location.href = `/clinica/ergonomia/${response.data.id}`
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
        window.location.href = '/clinica/ergonomia'
      }
    },

    imprimir() {
      alert('🖨️ Generando vista previa para impresión en PDF...')
    },

    async cargarDatosExistentes() {
      const pathname = window.location.pathname;
      const valoracionId = pathname.split('/').pop();

      if (!valoracionId || isNaN(valoracionId)) {
        console.warn('No se encontró un ID válido en la URL');
        return;
      }

      try {
        this.cargando = true;

        // ✅ CORREGIDO: Usar endpoint de valoraciones ergonómicas
        const response = await clinicaTrabajo.get(`/clinica/ergonomia/${valoracionId}`);
        const data = response.data.data || response.data;

        // Mapear los datos de la valoración existente al formulario
        this.form = {
          ...this.form,
          datosBasicos: {
            folio: data.folio || this.folioErgonomia,
            tipo_evaluacion: data.tipo_evaluacion || '',
            fecha_valoracion: data.fecha_valoracion || new Date().toISOString().split('T')[0],
            hora_valoracion: data.hora_valoracion || '',
            paciente_id: data.paciente_id,
            nombre_paciente: data.paciente?.nombre || '',
            empresa_cliente_id: data.empresa_cliente_id,
            nombre_empresa: data.empresa_cliente?.razon_social || '',
            puesto_trabajo_id: data.puesto_trabajo_id,
            nombre_puesto: data.puesto_trabajo?.nombre || '',
            ergonomo_id: data.ergonomo_id || data.medico_id,
            paciente: data.paciente || null
          },
          antecedentes: {
            ...this.form.antecedentes,
            hf_obesidad: data.hf_obesidad || 'no',
            hf_diabetes: data.hf_diabetes || 'no',
            hf_hipertension: data.hf_hipertension || 'no',
            hf_cancer: data.hf_cancer || 'no',
            hf_nefropatia: data.hf_nefropatia || 'no',
            hf_otros: data.hf_otros || 'no',
            am_obesidad: data.am_obesidad || 'no',
            am_diabetes: data.am_diabetes || 'no',
            am_hipertension: data.am_hipertension || 'no',
            am_dislipidemias: data.am_dislipidemias || 'no',
            am_cardiovasculares: data.am_cardiovasculares || 'no',
            am_renales: data.am_renales || 'no',
            am_hepaticos: data.am_hepaticos || 'no',
            am_musculo_esqueleticos: data.am_musculo_esqueleticos || 'no',
            am_urologicos: data.am_urologicos || 'no',
            am_infecciosos: data.am_infecciosos || 'no',
            menarca_anos: data.menarca_anos || null,
            ciclos: data.ciclos || '',
            fum: data.fum || '',
            metodo_planificacion: data.metodo_planificacion || '',
            cirugias: data.cirugias || 'no',
            cirugias_especifique: data.cirugias_especifique || '',
            consumo_medicamentos: data.consumo_medicamentos || '',
            alergias: data.alergias || '',
            am_otros: data.am_otros || ''
          },
          habitos: {
            ...this.form.habitos,
            tabaquismo: data.tabaquismo || false,
            alcoholismo: data.alcoholismo || false,
            varia_consumo_estres: data.varia_consumo_estres || false,
            varia_consumo_tristeza: data.varia_consumo_tristeza || false,
            escala_fagerstrm: data.escala_fagerstrm || 0,
            escala_audit: data.escala_audit || 0,
            frecuencia_consumo_alcohol: data.frecuencia_consumo_alcohol || '',
            refiere_diarrea: data.refiere_diarrea || false,
            refiere_estrenimiento: data.refiere_estrenimiento || false,
            refiere_gastritis: data.refiere_gastritis || false,
            refiere_ulcera: data.refiere_ulcera || false,
            refiere_nauseas: data.refiere_nauseas || false,
            refiere_reflujo: data.refiere_reflujo || false,
            refiere_vomitos: data.refiere_vomitos || false,
            refiere_colitis: data.refiere_colitis || false
          },
          factoresLaborales: {
            ...this.form.factoresLaborales,
            tension_emocional: data.tension_emocional || 'no',
            alta_responsabilidad: data.alta_responsabilidad || 'no',
            carga_excesiva_trabajo: data.carga_excesiva_trabajo || 'no',
            turno_rotativo: data.turno_rotativo || 'no',
            turno_nocturno: data.turno_nocturno || 'no',
            trabajo_repetitivo: data.trabajo_repetitivo || 'no',
            condiciones_entorno: data.condiciones_entorno || 'no',
            carga_trabajo: data.carga_trabajo || 'no',
            falta_control_trabajo: data.falta_control_trabajo || 'no',
            jornadas_trabajo: data.jornadas_trabajo || 'no',
            interferencia_trabajo_familia: data.interferencia_trabajo_familia || 'no',
            relaciones_interpersonales: data.relaciones_interpersonales || 'no',
            violencia_laboral: data.violencia_laboral || 'no',
            acontecimientos_traumaticos: data.acontecimientos_traumaticos || 'no'
          },
          examenMedico: {
            ...this.form.examenMedico,
            examen_medico_realizado: data.examen_medico_realizado || false,
            seguimiento_requerido: data.seguimiento_requerido || false,
            plazo_proximo_seguimiento: data.plazo_proximo_seguimiento || null,
            diagnostico_ergonomico: data.diagnostico_ergonomico || '',
            observaciones_examen: data.observaciones_examen || '',
            recomendaciones_examen: data.recomendaciones_examen || '',
            restricciones_laborales: data.restricciones_laborales || '',
            derivacion_especialista: data.derivacion_especialista || '',
            derivacion_especialista_otro: data.derivacion_especialista_otro || '',
            hallazgos: data.hallazgos || {}
          },
          hallazgos: {
            ...this.form.hallazgos,
            rula_puntuacion: data.rula_puntuacion || null,
            rula_nivel_riesgo: data.rula_nivel_riesgo || '',
            reba_puntuacion: data.reba_puntuacion || null,
            reba_nivel_accion: data.reba_nivel_accion || '',
            niosh_indice_levantamiento: data.niosh_indice_levantamiento || null,
            niosh_es_seguro: data.niosh_es_seguro || false,
            posturas_forzadas: data.posturas_forzadas || false,
            movimientos_repetitivos: data.movimientos_repetitivos || false,
            manipulacion_cargas: data.manipulacion_cargas || false,
            carga_maxima_manipulada_kg: data.carga_maxima_manipulada_kg || null
          },
          instrumento_utilizado: data.instrumento_utilizado || '',
          otro_descripcion: data.otro_descripcion || '',
          otro_puntuacion: data.otro_puntuacion || null,
          aptitud: data.aptitud || '',
          restricciones: data.restricciones || '',
          recomendaciones: data.recomendaciones || '',
          seguimiento_requerido: data.seguimiento_requerido || false,
          plazo_proximo_seguimiento: data.plazo_proximo_seguimiento || null
        };

        console.log('✅ Valoración ergonómica cargada correctamente:', this.form);

      } catch (error) {
        console.error('❌ Error al cargar valoración ergonómica:', error);
        alert('Error al cargar la valoración: ' + (error.response?.data?.message || error.message));
        window.location.href = '/clinica/ergonomia';
      } finally {
        this.cargando = false;
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
          this.$set(this.form.antecedentes, 'am_otros', data.antecedentes_medicos);
        }

        console.log('✅ Datos del paciente prellenados correctamente:', this.form.datosBasicos);

      } catch (error) {
        console.error('❌ Error al cargar datos del paciente:', error);
        alert('No se pudieron cargar los datos del paciente.');
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