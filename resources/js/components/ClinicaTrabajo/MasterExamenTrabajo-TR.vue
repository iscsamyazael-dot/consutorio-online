<template>
  <div class="master-examen-trabajo-tr">
    <!-- CHAT IA (ARRIBA) -->
    <div class="col-12 mb-4">
      <div class="card card-primary card-outline shadow-sm">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-microphone-alt text-danger mr-2"></i>
            Consulta en Tiempo Real (EXAMEN_TR)
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
            style="height: 200px; overflow-y: auto;"
          >
            <div class="direct-chat-msg mb-3">
              <div class="direct-chat-infos clearfix">
                <span class="direct-chat-name float-left text-primary">🤖 Asistente IA</span>
              </div>
              <div class="direct-chat-text bg-white border">
                ¡Hola! Estoy lista para asistirte con el examen TR. Puedes dictarme información o escribir tus observaciones.
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

    <!-- TABS NAVIGATION -->
    <div class="card mb-3">
      <div class="card-header bg-info text-white">
        <h4 class="mb-0">
          <i class="fas fa-heartbeat mr-2"></i>
          Examen Médico - Trabajo de Alto Riesgo (EXAMEN_TR)
        </h4>
      </div>
      <div class="card-body p-0">
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#hoja1" role="tab">
              <i class="fas fa-user-tie mr-1"></i> Hoja 1: Identificación & Empleo
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#hoja2" role="tab">
              <i class="fas fa-stethoscope mr-1"></i> Hoja 2: Antecedentes Salud
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#hoja3" role="tab">
              <i class="fas fa-procedures mr-1"></i> Hoja 3: Exploración Física
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#hoja4" role="tab">
              <i class="fas fa-flask mr-1"></i> Hoja 4: Paraclínicos & Diagnóstico
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- TABS CONTENT -->
    <div class="tab-content">
      <!-- HOJA 1 -->
      <div id="hoja1" class="tab-pane fade show active" role="tabpanel">
        <hoja1-identificacionempleo-tr
          :modelValue="form.hoja1"
          @update:modelValue="form.hoja1 = $event"
        ></hoja1-identificacionempleo-tr>
      </div>

      <!-- HOJA 2 -->
      <div id="hoja2" class="tab-pane fade" role="tabpanel">
        <hoja2-antecedentessalud-tr
          :modelValue="form.hoja2"
          @update:modelValue="form.hoja2 = $event"
        ></hoja2-antecedentessalud-tr>
      </div>

      <!-- HOJA 3 -->
      <div id="hoja3" class="tab-pane fade" role="tabpanel">
        <hoja3-exploracionfisica-tr
          :modelValue="form.hoja3"
          @update:modelValue="form.hoja3 = $event"
        ></hoja3-exploracionfisica-tr>
      </div>

      <!-- HOJA 4 -->
      <div id="hoja4" class="tab-pane fade" role="tabpanel">
        <hoja4-paraclinicodiagnosticos-tr
          :modelValue="form.hoja4"
          @update:modelValue="form.hoja4 = $event"
        ></hoja4-paraclinicodiagnosticos-tr>
      </div>
    </div>

    <!-- BOTONES ACCIÓN -->
    <div class="card mt-4">
      <div class="card-footer bg-light">
        <button class="btn btn-info" @click="guardar">
          <i class="fas fa-save mr-2"></i> Guardar Examen
        </button>
        <button class="btn btn-secondary ml-2" @click="cancelar">
          <i class="fas fa-times mr-2"></i> Cancelar
        </button>
        <button class="btn btn-warning ml-2" @click="imprimir">
          <i class="fas fa-print mr-2"></i> Imprimir
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import Hoja1IdentificacionempleoTr from './secciones/audiologia/ExamenTrabajoTR/identificacionempleo.vue'
import Hoja2AntecedentessaludTr from './secciones/audiologia/ExamenTrabajoTR/antecedentessalud.vue'
import Hoja3ExploracionfisicaTr from './secciones/audiologia/ExamenTrabajoTR/exploracionfisica.vue'
import Hoja4ParaclinicodiagnosticosTr from './secciones/audiologia/ExamenTrabajoTR/paraclinicodiagnosticos.vue'
import { clinicaTrabajo } from '../../services/ApiService'

export default {
  name: 'MasterExamenTrabajoTr',
  components: {
    Hoja1IdentificacionempleoTr,
    Hoja2AntecedentessaludTr,
    Hoja3ExploracionfisicaTr,
    Hoja4ParaclinicodiagnosticosTr
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
        hoja1: {
          nombre: '',
          puesto: '',
          fecha_nacimiento: '',
          edad: 0,
          genero: '',
          empresa: '',
          numero_empleado: '',
          departamento: '',
          tipo_trabajo_riesgo: {
            altura: false,
            espacio_confinado: false,
            trabajo_caliente: false,
            trabajo_electrico: false,
            excavacion_zanjas: false,
            maquinaria_pesada: false,
            materiales_peligrosos: false,
            izaje_carga: false
          }
        },
        hoja2: {
          // PATOLOGÍA CORONARIA (9 campos)
          marcapaso: false,
          valvulopatias: false,
          protesis_valvulares: false,
          cardiopatia_congenita: false,
          taquicardia: false,
          cardiopatia_isquemica: false,
          bradicardia: false,
          arritmias: false,
          insuficiencia_cardiaca: false,
          // PATOLOGÍA NEUROLÓGICA (14 campos)
          insomnio: false,
          convulsion: false,
          evc: false,
          letargo: false,
          paralisis: false,
          tce: false,
          migraña: false,
          hipercinesia: false,
          epilepsia: false,
          hipoestesia: false,
          sincope: false,
          hiperestesia: false,
          paresia: false,
          parestesia: false,
          // PATOLOGÍA VISUAL (12 campos)
          catarata: false,
          discromatopsia: false,
          glaucoma: false,
          ptosis_palpebral: false,
          estrabismo: false,
          ceguera_nocturna: false,
          retinopatia: false,
          estereopsis_anormal: false,
          diplopia: false,
          campimetria_anormal: false,
          nistagmo: false,
          movimientos_oculares: false,
          // PATOLOGÍA GENITOURINARIA (4 campos)
          enf_renal_cronica: false,
          pielonefritis: false,
          litiasis_renal: false,
          incontinencia_severa: false,
          // PATOLOGÍA DIGESTIVA (6 campos)
          gastroparesia_severa: false,
          erge_sii_severo: false,
          ulcera_peptica: false,
          enf_crohn_activa: false,
          hepatopatia_grave: false,
          diverticulitis_aguda: false
        },
        hoja3: {
          peso_kg: 0,
          estatura_m: 0,
          presion_sistolica: 0,
          presion_diastolica: 0,
          frecuencia_cardiaca: 0,
          cabeza: { estado: 'Normal', hallazgos: '' },
          ojos: { estado: 'Normal', hallazgos: '' },
          oidos: { estado: 'Normal', hallazgos: '' },
          nariz: { estado: 'Normal', hallazgos: '' },
          boca: { estado: 'Normal', hallazgos: '' },
          cuello: { estado: 'Normal', hallazgos: '' },
          cardiovascular: { estado: 'Normal', hallazgos: '' },
          pulmonar: { estado: 'Normal', hallazgos: '' },
          abdomen: { estado: 'Normal', hallazgos: '' },
          genitourinario: { estado: 'Normal', hallazgos: '' },
          osteomuscular: { estado: 'Normal', hallazgs: '' },
          neurologico: { estado: 'Normal', hallazgos: '' },
          tegumentos: { estado: 'Normal', hallazgos: '' }
        },
        hoja4: {
          ecg: { realizado: false, fecha: '', resultado: 'Normal', reporte: '' },
          rx_torax: { realizado: false, fecha: '', resultado: 'Normal', reporte: '' },
          evaluacion_visual: { realizado: false, fecha: '', resultado: 'Normal', od: '', oi: '', reporte: '' },
          audiometria: { realizado: false, fecha: '', resultado: 'Normal', frecuencias: [], reporte: '' },
          espirometria: { realizado: false, fecha: '', resultado: 'Normal', reporte: '' },
          diagnosticos: ['', '', '', '', ''],
          enfermedad_profesional: false,
          enfermedad_profesional_detalle: ''
        }
      },
      cargando: false
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

      // Actualiza datos básicos con info del trabajador
      this.form.hoja1.nombre = trabajador.nombre || ''
      this.form.hoja1.fecha_nacimiento = trabajador.fecha_nacimiento || ''
      this.form.hoja1.edad = trabajador.edad || ''
      this.form.hoja1.genero = trabajador.genero || ''
      this.form.hoja1.empresa = trabajador.empresa || ''
      this.form.hoja1.numero_empleado = trabajador.numero_empleado || ''
      this.form.hoja1.puesto = trabajador.puesto || ''
    },

    deseleccionarTrabajador() {
      this.trabajadorSeleccionado = null
      this.conversacion = []
      this.form = {
        hoja1: {
          nombre: '',
          puesto: '',
          fecha_nacimiento: '',
          edad: 0,
          genero: '',
          empresa: '',
          numero_empleado: '',
          departamento: '',
          tipo_trabajo_riesgo: {
            altura: false,
            espacio_confinado: false,
            trabajo_caliente: false,
            trabajo_electrico: false,
            excavacion_zanjas: false,
            maquinaria_pesada: false,
            materiales_peligrosos: false,
            izaje_carga: false
          }
        },
        hoja2: {
          // Reset all boolean fields to false and strings to empty
          marcapaso: false,
          valvulopatias: false,
          protesis_valvulares: false,
          cardiopatia_congenita: false,
          taquicardia: false,
          cardiopatia_isquemica: false,
          bradicardia: false,
          arritmias: false,
          insuficiencia_cardiaca: false,
          insomnio: false,
          convulsion: false,
          evc: false,
          letargo: false,
          paralisis: false,
          tce: false,
          migraña: false,
          hipercinesia: false,
          epilepsia: false,
          hipoestesia: false,
          sincope: false,
          hiperestesia: false,
          paresia: false,
          parestesia: false,
          catarata: false,
          discromatopsia: false,
          glaucoma: false,
          ptosis_palpebral: false,
          estrabismo: false,
          ceguera_nocturna: false,
          retinopatia: false,
          estereopsis_anormal: false,
          diplopia: false,
          campimetria_anormal: false,
          nistagmo: false,
          movimientos_oculares: false,
          enf_renal_cronica: false,
          pielonefritis: false,
          litiasis_renal: false,
          incontinencia_severa: false,
          gastroparesia_severa: false,
          erge_sii_severo: false,
          ulcera_peptica: false,
          enf_crohn_activa: false,
          hepatopatia_grave: false,
          diverticulitis_aguda: false
        },
        hoja3: {
          peso_kg: 0,
          estatura_m: 0,
          presion_sistolica: 0,
          presion_diastolica: 0,
          frecuencia_cardiaca: 0,
          cabeza: { estado: 'Normal', hallazgs: '' },
          ojos: { estado: 'Normal', hallazgs: '' },
          oidos: { estado: 'Normal', hallazgs: '' },
          nariz: { estado: 'Normal', hallazgs: '' },
          boca: { estado: 'Normal', hallazgs: '' },
          cuello: { estado: 'Normal', hallazgs: '' },
          cardiovascular: { estado: 'Normal', hallazgs: '' },
          pulmonar: { estado: 'Normal', hallazgs: '' },
          abdomen: { estado: 'Normal', hallazgs: '' },
          genitourinario: { estado: 'Normal', hallazgs: '' },
          osteomuscular: { estado: 'Normal', hallazgs: '' },
          neurologico: { estado: 'Normal', hallazgs: '' },
          tegumentos: { estado: 'Normal', hallazgs: '' }
        },
        hoja4: {
          ecg: { realizado: false, fecha: '', resultado: 'Normal', reporte: '' },
          rx_torax: { realizado: false, fecha: '', resultado: 'Normal', reporte: '' },
          evaluacion_visual: { realizado: false, fecha: '', resultado: 'Normal', od: '', oi: '', reporte: '' },
          audiometria: { realizado: false, fecha: '', resultado: 'Normal', frecuencias: [], reporte: '' },
          espirometria: { realizado: false, fecha: '', resultado: 'Normal', reporte: '' },
          diagnosticos: ['', '', '', '', ''],
          enfermedad_profesional: false,
          enfermedad_profesional_detalle: ''
        }
      }
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
          paciente_id: this.trabajadorSeleccionado?.id,
          contexto: this.form
        })

        this.conversacion.push({
          sender: 'ia',
          text: response.data.respuesta || 'No pude procesar tu solicitud.',
          timestamp: new Date()
        })

        if (response.data.campos_completados) {
          // Update form fields based on IA response
          Object.assign(this.form, response.data.campos_completados)
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

    async guardar() {
      try {
        this.cargando = true

        // Validar datos críticos
        if (!this.form.hoja1.nombre) {
          alert('Por favor ingrese el nombre del paciente')
          return
        }

        if (!this.form.hoja1.empresa) {
          alert('Por favor ingrese la empresa')
          return
        }

        const payload = {
          ...this.form,
          tipo_evaluacion: 'examen_tr'
        }

        const response = await clinicaTrabajo.guardarExamenTrabajoTr(payload)

        if (response.data.success) {
          alert('✅ Examen Médico guardado correctamente')
          // Redirigir o recargar
          window.location.href = `/clinica/audiologia/tr/${response.data.id}`
        }
      } catch (error) {
        console.error('Error guardando:', error)
        alert('❌ Error al guardar el examen: ' + error.message)
      } finally {
        this.cargando = false
      }
    },
    cancelar() {
      if (confirm('¿Está seguro de que desea cancelar? Se perderán los cambios.')) {
        window.location.href = '/clinica/audiologia'
      }
    },
    imprimir() {
      alert('🖨️ Funcionalidad de impresión en desarrollo')
      // TODO: Implementar impresión con HTML2PDF
    }
  },

  watch: {
    // Reset conversation when switching tabs if needed
  }
}
</script>

<style scoped>
.master-examen-trabajo-tr {
  background: #f8f9fa;
  padding: 15px;
  border-radius: 8px;
}

/* Chat styling */
.direct-chat-msg {
  margin-right: 0;
  margin-left: 0;
}

.direct-chat-infos {
  display: block;
  margin-bottom: 5px;
}

.direct-chat-name {
  font-weight: 600;
}

.direct-chat-text {
  display: block;
  padding: 5px 10px;
  border-radius: 5px;
  position: relative;
}

.direct-chat-text.bg-primary {
  background-color: #007bff !important;
  color: white !important;
}

.direct-chat-text.bg-white {
  background-color: white !important;
  border: 1px solid #ddd !important;
  color: #333 !important;
}

.right .direct-chat-text {
  margin-left: 20px;
  background-color: #f8f9fa !important;
  border-color: #eee !important;
}

.progress {
  background-color: #e9ecef;
}

.progress-bar {
  background-color: #007bff;
  transition: width 0.3s ease;
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
  transition: all 0.3s ease;
}

.nav-tabs .nav-link:hover {
  color: #017a8b;
  border-bottom-color: #017a8b;
}

.nav-tabs .nav-link.active {
  color: #fff;
  background: linear-gradient(135deg, #0c5460 0%, #063c48 100%);
  border-bottom: 3px solid #17a2b8;
  border-radius: 4px 4px 0 0;
}

.tab-content {
  background: #fff;
  border-radius: 0 0 8px 8px;
  padding: 20px;
  margin-bottom: 20px;
  min-height: 400px;
}

.tab-pane {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.card-footer {
  border-top: 1px solid #dee2e6;
  display: flex;
  gap: 10px;
  padding: 15px;
}

.btn {
  min-width: 120px;
}
</style>