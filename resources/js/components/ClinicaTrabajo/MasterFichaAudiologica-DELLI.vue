<template>
  <div class="master-ficha-audiologica-delli">
    <!-- CHAT IA (ARRIBA) -->
    <div class="col-12 mb-4">
      <div class="card card-primary card-outline shadow-sm">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-microphone-alt text-danger mr-2"></i>
            Consulta en Tiempo Real (DELLI)
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
                ¡Hola! Estoy lista para asistirte con la evaluación audiológica. Puedes dictarme información o escribir tus observaciones.
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
      <div class="card-header bg-primary text-white">
        <h4 class="mb-0">
          <i class="fas fa-ear mr-2"></i>
          Historia Clínica Audiológica (DELLI 2026)
        </h4>
      </div>
      <div class="card-body p-0">
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#hoja1" role="tab">
              <i class="fas fa-user mr-1"></i> Hoja 1: Identificación
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#hoja2" role="tab">
              <i class="fas fa-briefcase mr-1"></i> Hoja 2: Riesgos & Ototóxicos
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#hoja3" role="tab">
              <i class="fas fa-stethoscope mr-1"></i> Hoja 3: Síntomas & Otoscopia
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#hoja4" role="tab">
              <i class="fas fa-volume-up mr-1"></i> Hoja 4: Audiometría
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- TABS CONTENT -->
    <div class="tab-content">
      <!-- HOJA 1 -->
      <div id="hoja1" class="tab-pane fade show active" role="tabpanel">
        <hoja1-datosbasicos-delli
          :modelValue="form.hoja1"
          @update:modelValue="form.hoja1 = $event"
        ></hoja1-datosbasicos-delli>
      </div>

      <!-- HOJA 2 -->
      <div id="hoja2" class="tab-pane fade" role="tabpanel">
        <hoja2-riesgosototoxicos-delli
          :modelValue="form.hoja2"
          @update:modelValue="form.hoja2 = $event"
        ></hoja2-riesgosototoxicos-delli>
      </div>

      <!-- HOJA 3 -->
      <div id="hoja3" class="tab-pane fade" role="tabpanel">
        <hoja3-antecedentessintomas-delli
          :modelValue="form.hoja3"
          @update:modelValue="form.hoja3 = $event"
        ></hoja3-antecedentessintomas-delli>
      </div>

      <!-- HOJA 4 -->
      <div id="hoja4" class="tab-pane fade" role="tabpanel">
        <hoja4-audiometria-delli
          :modelValue="form.hoja4"
          @update:modelValue="form.hoja4 = $event"
        ></hoja4-audiometria-delli>
      </div>
    </div>

    <!-- BOTONES ACCIÓN -->
    <div class="card mt-4">
      <div class="card-footer bg-light">
        <button class="btn btn-primary" @click="guardar">
          <i class="fas fa-save mr-2"></i> Guardar Evaluación
        </button>
        <button class="btn btn-secondary ml-2" @click="cancelar">
          <i class="fas fa-times mr-2"></i> Cancelar
        </button>
        <button class="btn btn-info ml-2" @click="imprimir">
          <i class="fas fa-print mr-2"></i> Imprimir
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import Hoja1DatosbasicosDelli from './secciones/audiologia/FichaAudiologicaDelli/DatosBasicos.vue'
import Hoja2RiesgosototoxicosDelli from './secciones/audiologia/FichaAudiologicaDelli/riesgosototoxicos.vue'
import Hoja3AntecedentessintomasDelli from './secciones/audiologia/FichaAudiologicaDelli/antecedentessintomas.vue'
import Hoja4AudiometriaDelli from './secciones/audiologia/FichaAudiologicaDelli/audiometria.vue'
import { clinicaTrabajo } from '../../services/ApiService'

export default {
  name: 'MasterFichaAudiologicaDelli',
  components: {
    Hoja1DatosbasicosDelli,
    Hoja2RiesgosototoxicosDelli,
    Hoja3AntecedentessintomasDelli,
    Hoja4AudiometriaDelli
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
          folio: '',
          nombre: '',
          fecha_nacimiento: '',
          edad: 0,
          genero: '',
          empresa_actual: '',
          departamento: '',
          numero_empleado: '',
          antiguedad_empresa_anos: 0,
          antiguedad_empresa_meses: 0,
          puesto: '',
          antiguedad_puesto_anos: 0,
          antiguedad_puesto_meses: 0,
          empresas_anteriores: [],
          otra_empresa_actual: []
        },
        hoja2: {
          riesgo_auditivo: '',
          duracion_exposicion: '',
          medidas_control: '',
          uso_proteccion: '',
          tipo_protector: '',
          disolventes: false,
          metales: false,
          gases: false,
          sales: false,
          tabaquismo: false,
          cigarrillos_dia: 0,
          agentes_ototoxicos: [],
          // Nuevos campos de Hoja 2 según Excel
          toxicomanias: false,
          antibioticos: false,
          diureticos: false,
          salicilatos: false,
          armas_fuego_caneria: false,
          niveles_altos_musica: false,
          lugares_ruidosos_discoteca: false,
          automovilismo_motociclismo: false,
          otros_pasatiempos_ruido: false
        },
        hoja3: {
          historia_familiar_perdida: false,
          parentesco: '',
          observaciones_familia: '',
          alergias: false,
          alergias_especificar: '',
          covid19: false,
          covid19_obs: '',
          resfriado: false,
          resfriado_obs: '',
          otitis: false,
          otitis_obs: '',
          descripcion_hipoacusia: '',
          tiempo_evolucion: '',
          otalgia_der: false,
          otalgia_izq: false,
          otorrea_der: false,
          otorrea_izq: false,
          prurito_der: false,
          prurito_izq: false,
          otodinia_der: false,
          otodinia_izq: false,
          otorragia_der: false,
          otorragia_izq: false,
          autofonia_der: false,
          autofonia_izq: false,
          acufenos: false,
          congestion: false,
          algiacusia: false,
          conducto_auditivo_der: 'Normal',
          conducto_auditivo_izq: 'Normal',
          conducto_hallazgos_der: '',
          conducto_hallazgos_izq: '',
          oido_medio_der: 'Normal',
          oido_medio_izq: 'Normal',
          oido_medio_hallazgos_der: '',
          oido_medio_hallazgos_izq: '',
          // Nuevos campos de Hoja 3 según Excel
          sarampion: false,
          hipertension: false,
          paperas: false,
          meningitis: false,
          diabetes: false,
          enfermedad1: false,
          enfermedad2: false,
          uso_aparat: false,
          ninguna_otitis: false,
          vertigo: false,
          paracusias_willis: false,
          paracusias_weber: false,
          paracusias_negado: false,
          paracusias_hipo: false,
          paracusias_hiper: false,
          fatiga: false,
          sensibilidad: false,
          examen_otoscopico: '',
          patron: ''
        },
        hoja4: {
          exposicion_14horas: false,
          audiometria_125_der: null,
          audiometria_250_der: null,
          audiometria_500_der: null,
          audiometria_1000_der: null,
          audiometria_2000_der: null,
          audiometria_3000_der: null,
          audiometria_4000_der: null,
          audiometria_6000_der: null,
          audiometria_8000_der: null,
          audiometria_125_izq: null,
          audiometria_250_izq: null,
          audiometria_500_izq: null,
          audiometria_1000_izq: null,
          audiometria_2000_izq: null,
          audiometria_3000_izq: null,
          audiometria_4000_izq: null,
          audiometria_6000_izq: null,
          audiometria_8000_izq: null,
          pta_der: 0,
          pta_izq: 0,
          pta_promedio: 0,
          clasificacion: '',
          interpretacion: '',
          aptitud: '',
          tipo_hipoacusia: '',
          restricciones: '',
          recomendaciones: '',
          // Nuevo campo de Hoja 4 según Excel
          valoracion: ''
        }
      },
      cargando: false
    }
  },

  computed: {
    folioDelli() {
      if (!this.form.hoja1.folio) {
        const ano = new Date().getFullYear()
        const random = String(Math.floor(Math.random() * 9000) + 1000)
        return `AUD-${ano}-${random}`
      }
      return this.form.hoja1.folio
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
      this.form.hoja1.empresa_actual = trabajador.empresa || ''
      this.form.hoja1.numero_empleado = trabajador.numero_empleado || ''
    },

    deseleccionarTrabajador() {
      this.trabajadorSeleccionado = null
      this.conversacion = []
      this.form = {
        hoja1: {
          folio: '',
          nombre: '',
          fecha_nacimiento: '',
          edad: 0,
          genero: '',
          empresa_actual: '',
          departamento: '',
          numero_empleado: '',
          antiguedad_empresa_anos: 0,
          antiguedad_empresa_meses: 0,
          puesto: '',
          antiguedad_puesto_anos: 0,
          antiguedad_puesto_meses: 0,
          empresas_anteriores: [],
          otra_empresa_actual: []
        },
        hoja2: {
          riesgo_auditivo: '',
          duracion_exposicion: '',
          medidas_control: '',
          uso_proteccion: '',
          tipo_protector: '',
          disolventes: false,
          metales: false,
          gases: false,
          sales: false,
          tabaquismo: false,
          cigarrillos_dia: 0,
          agentes_ototoxicos: [],
          // Nuevos campos de Hoja 2 según Excel
          toxicomanias: false,
          antibioticos: false,
          diureticos: false,
          salicilatos: false,
          armas_fuego_caneria: false,
          niveles_altos_musica: false,
          lugares_ruidosos_discoteca: false,
          automovilismo_motociclismo: false,
          otros_pasatiempos_ruido: false
        },
        hoja3: {
          historia_familiar_perdida: false,
          parentesco: '',
          observaciones_familia: '',
          alergias: false,
          alergias_especificar: '',
          covid19: false,
          covid19_obs: '',
          resfriado: false,
          resfriado_obs: '',
          otitis: false,
          otitis_obs: '',
          descripcion_hipoacusia: '',
          tiempo_evolucion: '',
          otalgia_der: false,
          otalgia_izq: false,
          otorrea_der: false,
          otorrea_izq: false,
          prurito_der: false,
          prurito_izq: false,
          otodinia_der: false,
          otodinia_izq: false,
          otorragia_der: false,
          otorragia_izq: false,
          autofonia_der: false,
          autofonia_izq: false,
          acufenos: false,
          congestion: false,
          algiacusia: false,
          conducto_auditivo_der: 'Normal',
          conducto_auditivo_izq: 'Normal',
          conducto_hallazgos_der: '',
          conducto_hallazgos_izq: '',
          oido_medio_der: 'Normal',
          oido_medio_izq: 'Normal',
          oido_medio_hallazgos_der: '',
          oido_medio_hallazgos_izq: '',
          // Nuevos campos de Hoja 3 según Excel
          sarampion: false,
          hipertension: false,
          paperas: false,
          meningitis: false,
          diabetes: false,
          enfermedad1: false,
          enfermedad2: false,
          uso_aparat: false,
          ninguna_otitis: false,
          vertigo: false,
          paracusias_willis: false,
          paracusias_weber: false,
          paracusias_negado: false,
          paracusias_hipo: false,
          paracusias_hiper: false,
          fatiga: false,
          sensibilidad: false,
          examen_otoscopico: '',
          patron: ''
        },
        hoja4: {
          exposicion_14horas: false,
          audiometria_125_der: null,
          audiometria_250_der: null,
          audiometria_500_der: null,
          audiometria_1000_der: null,
          audiometria_2000_der: null,
          audiometria_3000_der: null,
          audiometria_4000_der: null,
          audiometria_6000_der: null,
          audiometria_8000_der: null,
          audiometria_125_izq: null,
          audiometria_250_izq: null,
          audiometria_500_izq: null,
          audiometria_1000_izq: null,
          audiometria_2000_izq: null,
          audiometria_3000_izq: null,
          audiometria_4000_izq: null,
          audiometria_6000_izq: null,
          audiometria_8000_izq: null,
          pta_der: 0,
          pta_izq: 0,
          pta_promedio: 0,
          clasificacion: '',
          interpretacion: '',
          aptitud: '',
          tipo_hipoacusia: '',
          restricciones: '',
          recomendaciones: '',
          // Nuevo campo de Hoja 4 según Excel
          valoracion: ''
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

        if (!this.form.hoja4.aptitud) {
          alert('Por favor seleccione la APTITUD en la Hoja 4 (Requerida)')
          return
        }

        const payload = {
          ...this.form,
          folio: this.folioDelli,
          tipo_evaluacion: 'delli_2026'
        }

        const response = await clinicaTrabajo.guardarFichaAudiologicaDelli(payload)

        if (response.data.success) {
          alert('✅ Evaluación Audiológica guardada correctamente')
          this.form.hoja1.folio = response.data.folio
          // Redirigir o recargar
          window.location.href = `/clinica/audiologia/delli/${response.data.id}`
        }
      } catch (error) {
        console.error('Error guardando:', error)
        alert('❌ Error al guardar la evaluación: ' + error.message)
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

  mounted() {
    // Auto-generar folio si es nuevo
    if (!this.form.hoja1.folio) {
      this.form.hoja1.folio = this.folioDelli
    }

    // Inicializar valores por defecto para nuevos campos
    this.form.hoja2.toxicomanias = this.form.hoja2.toxicomanias || false
    this.form.hoja2.antibioticos = this.form.hoja2.antibioticos || false
    this.form.hoja2.diureticos = this.form.hoja2.diureticos || false
    this.form.hoja2.salicilatos = this.form.hoja2.salicilatos || false
    this.form.hoja2.armas_fuego_caneria = this.form.hoja2.armas_fuego_caneria || false
    this.form.hoja2.niveles_altos_musica = this.form.hoja2.niveles_altos_musica || false
    this.form.hoja2.lugares_ruidosos_discoteca = this.form.hoja2.lugares_ruidosos_discoteca || false
    this.form.hoja2.automovilismo_motociclismo = this.form.hoja2.automovilismo_motociclismo || false
    this.form.hoja2.otros_pasatiempos_ruido = this.form.hoja2.otros_pasatiempos_ruido || false

    this.form.hoja3.sarampion = this.form.hoja3.sarampion || false
    this.form.hoja3.hipertension = this.form.hoja3.hipertension || false
    this.form.hoja3.paperas = this.form.hoja3.paperas || false
    this.form.hoja3.meningitis = this.form.hoja3.meningitis || false
    this.form.hoja3.diabetes = this.form.hoja3.diabetes || false
    this.form.hoja3.enfermedad1 = this.form.hoja3.enfermedad1 || false
    this.form.hoja3.enfermedad2 = this.form.hoja3.enfermedad2 || false
    this.form.hoja3.uso_aparat = this.form.hoja3.uso_aparat || false
    this.form.hoja3.ninguna_otitis = this.form.hoja3.ninguna_otitis || false
    this.form.hoja3.vertigo = this.form.hoja3.vertigo || false
    this.form.hoja3.paracusias_willis = this.form.hoja3.paracusias_willis || false
    this.form.hoja3.paracusias_weber = this.form.hoja3.paracusias_weber || false
    this.form.hoja3.paracusias_negado = this.form.hoja3.paracusias_negado || false
    this.form.hoja3.paracusias_hipo = this.form.hoja3.paracusias_hipo || false
    this.form.hoja3.paracusias_hiper = this.form.hoja3.paracusias_hiper || false
    this.form.hoja3.fatiga = this.form.hoja3.fatiga || false
    this.form.hoja3.sensibilidad = this.form.hoja3.sensibilidad || false
    this.form.hoja3.examen_otoscopico = this.form.hoja3.examen_otoscopico || ''
    this.form.hoja3.patron = this.form.hoja3.patron || ''

    this.form.hoja4.valoracion = this.form.hoja4.valoracion || ''
  },

  watch: {
    // Reset conversation when switching tabs if needed
  }
}
</script>

<style scoped>
.master-ficha-audiologica-delli {
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
  color: #0c5460;
  border-bottom-color: #0c5460;
}

.nav-tabs .nav-link.active {
  color: #fff;
  background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%);
  border-bottom: 3px solid #007bff;
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