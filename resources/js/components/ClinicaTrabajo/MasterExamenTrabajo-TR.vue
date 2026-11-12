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
    <div v-if="iaAdvertencias.length" class="alert alert-info small mb-3">
      <i class="fas fa-info-circle mr-1"></i> {{ iaAdvertencias.join(' · ') }}
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
          :ia-campos="iaCampos"
          :ia-diags-propuestos="iaDiagsPropuestos"
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
import  clinicaTrabajo from '@/services/ApiServiceClinicaTrabajo'
import Swal from 'sweetalert2'

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
      fichaId: null,
      iaAdvertencias: [],
      iaCargando: false,
      iaCampos: {},
      iaDiagsPropuestos: [],
      editadosPorMedico: {},
      iaTimer: null,
      ultimaFirmaIA: null,
      snapshotHoja4: null,
      recomendacionPendiente: false,
  
      pacienteId: null,               
      infoPacientes: null,            
      datosLaboralesActuales: null,   

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
          folio:'',
          nombre: '',
          puesto: '',
          fecha_nacimiento: '',
          edad: 0,
          genero: '',
          empresa: '',
          numero_empleado: '',
          departamento: '',
          
          antiguedad_empresa_anos: 0,
          antiguedad_empresa_meses: 0,
          antiguedad_puesto_anos: 0,
          antiguedad_puesto_meses: 0,

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
          cabeza: { estado: 'Normal', observaciones: '' },
          ojos: { estado: 'Normal', observaciones: '' },
          oidos: { estado: 'Normal', observaciones: '' },
          nariz: { estado: 'Normal', observaciones: '' },
          boca: { estado: 'Normal', observaciones: '' },
          cuello: { estado: 'Normal', observaciones: '' },
          cardiovascular: { estado: 'Normal', observaciones: '' },
          pulmonar: { estado: 'Normal', observaciones: '' },
          abdomen: { estado: 'Normal', observaciones: '' },
          genitourinario: { estado: 'Normal', observaciones: '' },
          osteomuscular: { estado: 'Normal', observaciones: '' },
          neurologico: { estado: 'Normal', observaciones: '' },
          tegumentos: { estado: 'Normal', observaciones: '' }
        },
        hoja4: {
          ecg: { realizado: false, fecha: '', resultado: 'Normal', reporte: '' },
          rx_torax: { realizado: false, fecha: '', resultado: 'Normal', reporte: '' },
          evaluacion_visual: { realizado: false, fecha: '', resultado: 'Normal', od: '', oi: '', reporte: '' },
          audiometria: { 
            realizado: false, 
            fecha: '', 
            resultado: 'Normal', 
            frecuencias: {
              derecho: { 125: null, 250: null, 500: null, 1000: null, 2000: null, 3000: null, 4000: null, 6000: null, 8000: null },
              izquierdo: { 125: null, 250: null, 500: null, 1000: null, 2000: null, 3000: null, 4000: null, 6000: null, 8000: null }
            },
            reporte: '' 
          },
          espirometria: { realizado: false, fecha: '', resultado: 'Normal', reporte: '' },
          diagnosticos: ['', '', '', '', '','', '', '', '', ''],
          enfermedad_profesional: false,
          enfermedad_profesional_detalle: '',
          diagnostico_principal: '',
          aptitud: '',
          observaciones: '',
          recomendaciones: []
        }
      },
      cargando: false
    }
  },
  computed: {
    folioTR() {
      if (!this.form.hoja1.folio) {
        const ano = new Date().getFullYear()
        const random = String(Math.floor(Math.random() * 9000) + 1000)
        return `TR-${ano}-${random}`
      }
      return this.form.hoja1.folio
    },
    firmaContextoIA() {
      return JSON.stringify(this.construirContextoIA())
    }
  },
  methods: {
   async obtenerPacientes() {
      if (!this.pacienteId) {
        console.warn('No hay pacienteId para cargar el expediente')
        return
      }

      try {
        this.cargando = true
        const response = await clinicaTrabajo.get(`/PacientesDetalleClinicaTrabajo/${this.pacienteId}`)
        const data = response.data.data
        this.infoPacientes = data

        // Datos laborales (último registro)
        const datosLaborales = data.datos_laborales || []
        const laboral = datosLaborales.length > 0 ? datosLaborales[datosLaborales.length - 1] : null
        this.datosLaboralesActuales = laboral

        // Helper para calcular edad exacta
        const calcularEdad = (fechaStr) => {
          if (!fechaStr) return 0
          const hoy = new Date()
          const nacimiento = new Date(fechaStr)
          let edad = hoy.getFullYear() - nacimiento.getFullYear()
          const mes = hoy.getMonth() - nacimiento.getMonth()
          if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
            edad--
          }
          return edad
        }

        // ✅ NUEVO: Helper para calcular antigüedad en años y meses
        const calcularAntiguedad = (fechaIngresoStr) => {
          if (!fechaIngresoStr) return { anos: 0, meses: 0 }
          
          const ingreso = new Date(fechaIngresoStr)
          const hoy = new Date()
          
          let anos = hoy.getFullYear() - ingreso.getFullYear()
          let meses = hoy.getMonth() - ingreso.getMonth()
          
          // Ajustar si el día del mes aún no ha llegado
          if (meses < 0 || (meses === 0 && hoy.getDate() < ingreso.getDate())) {
            anos--
            meses += 12
          }
          
          return { anos, meses }
        }

        // Calcular antigüedad si existe fecha de ingreso
        const antiguedad = calcularAntiguedad(laboral?.fecha_ingreso_empresa)

        // ==========================================
        // MAPEO: HOJA 1 - Identificación y Empleo
        // ==========================================
        this.form.hoja1 = {
          ...this.form.hoja1,
          nombre: data.nombre || '',
          fecha_nacimiento: data.fecha_nacimiento ? data.fecha_nacimiento.split('T')[0] : '',
          edad: calcularEdad(data.fecha_nacimiento),
          genero: data.genero === 'Hombre' ? 'Masculino' : (data.genero === 'Mujer' ? 'Femenino' : (data.genero || '')),
          empresa: laboral?.empresa_cliente?.razon_social || '',
          numero_empleado: laboral?.numero_empleado || '',
          departamento: laboral?.puesto_trabajo?.departamento || '',
          puesto: laboral?.puesto_trabajo?.nombre || '',
          
          // ✅ NUEVO: Asignar antigüedades calculadas
          antiguedad_empresa_anos: antiguedad.anos,
          antiguedad_empresa_meses: antiguedad.meses,
          antiguedad_puesto_anos: antiguedad.anos, // Si no hay fecha específica del puesto, usamos la de empresa
          antiguedad_puesto_meses: antiguedad.meses
        }

        // NOTA: Las hojas 2, 3 y 4 se mantienen con sus valores por defecto
        // (Si tu backend ya devuelve estos datos guardados en este mismo endpoint, avísame y los mapeamos).

        // Sincronizar trabajador seleccionado para el Chat IA
        this.trabajadorSeleccionado = {
          id: this.pacienteId,
          nombre: data.nombre,
          empresa: this.form.hoja1.empresa,
          numero_empleado: this.form.hoja1.numero_empleado,
          puesto: this.form.hoja1.puesto
        }

        console.log('✅ Expediente base cargado correctamente:', this.infoPacientes)
        console.log(' Antigüedad calculada:', antiguedad)

      } catch (error) {
        console.error('❌ Error al obtener paciente:', error)
        alert('Error al cargar el expediente: ' + (error.response?.data?.message || error.message))
      } finally {
        this.cargando = false
      }
    },

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
          antiguedad_empresa_anos: 0,
          antiguedad_empresa_meses: 0,
          antiguedad_puesto_anos: 0,
          antiguedad_puesto_meses: 0,
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
          // PATOLOGÍA CORONARIA
          marcapaso: false,
          valvulopatias: false,
          protesis_valvulares: false,
          cardiopatia_congenita: false,
          taquicardia: false,
          cardiopatia_isquemica: false,
          bradicardia: false,
          arritmias: false,
          insuficiencia_cardiaca: false,
          // PATOLOGÍA NEUROLÓGICA
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
          // PATOLOGÍA VISUAL
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
          // PATOLOGÍA GENITOURINARIA
          enf_renal_cronica: false,
          pielonefritis: false,
          litiasis_renal: false,
          incontinencia_severa: false,
          // PATOLOGÍA DIGESTIVA
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
          frecuencia_respiratoria: 0, // ✅ AGREGADO
          score: '',                  // ✅ AGREGADO
          cabeza: { estado: 'Normal', observaciones: '' },          // ✅ CORREGIDO: hallazgos -> observaciones
          ojos: { estado: 'Normal', observaciones: '' },            // ✅ CORREGIDO
          oidos: { estado: 'Normal', observaciones: '' },           // ✅ CORREGIDO
          nariz: { estado: 'Normal', observaciones: '' },           // ✅ CORREGIDO
          boca: { estado: 'Normal', observaciones: '' },            // ✅ CORREGIDO
          cuello: { estado: 'Normal', observaciones: '' },          // ✅ CORREGIDO
          cardiovascular: { estado: 'Normal', observaciones: '' },  // ✅ CORREGIDO
          pulmonar: { estado: 'Normal', observaciones: '' },        // ✅ CORREGIDO
          abdomen: { estado: 'Normal', observaciones: '' },         // ✅ CORREGIDO
          genitourinario: { estado: 'Normal', observaciones: '' },  // ✅ CORREGIDO
          osteomuscular: { estado: 'Normal', observaciones: '' },   // ✅ CORREGIDO
          neurologico: { estado: 'Normal', observaciones: '' },     // ✅ CORREGIDO
          tegumentos: { estado: 'Normal', observaciones: '' }       // ✅ CORREGIDO
        },
        hoja4: {
          ecg: { realizado: false, fecha: '', resultado: 'Normal', reporte: '' },
          rx_torax: { realizado: false, fecha: '', resultado: 'Normal', reporte: '' },
          evaluacion_visual: { realizado: false, fecha: '', resultado: 'Normal', od: '', oi: '', reporte: '' },
          audiometria: { 
            realizado: false, 
            fecha: '', 
            resultado: 'Normal', 
            // ✅ CORREGIDO: Estructura anidada correcta para las frecuencias (evita el error undefined)
            frecuencias: {
              derecho: { 125: null, 250: null, 500: null, 1000: null, 2000: null, 3000: null, 4000: null, 6000: null, 8000: null },
              izquierdo: { 125: null, 250: null, 500: null, 1000: null, 2000: null, 3000: null, 4000: null, 6000: null, 8000: null }
            }, 
            reporte: '' 
          },
          espirometria: { realizado: false, fecha: '', resultado: 'Normal', reporte: '' },
          // ✅ CORREGIDO: 10 espacios para diagnósticos (coincide con el componente hijo)
          diagnosticos: ['', '', '', '', '', '', '', '', '', ''],
          enfermedad_profesional: false,
          enfermedad_profesional_detalle: '',
          diagnostico_principal: '',
          aptitud: '',
          observaciones: '',
          recomendaciones: []
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
      this.iaCargando = true

      try {
        console.log(' CONTEXTO ENVIADO A IA:', this.construirContextoIA())
        const response = await clinicaTrabajo.post('/AudiologiaTrabajoAltoRiesgo-consultarIA', {
          modo: 'chat',
          mensaje: mensaje,
          paciente_id: this.pacienteId ? Number(this.pacienteId) : null,
          contexto: this.construirContextoIA(),
          historial: this.conversacion.slice(-10)
        })
        
        console.log('✅ RESPUESTA DE LA IA:', response.data)
        console.log('📋 CAMPOS COMPLETADOS:', response.data.campos_completados)
        
        this.conversacion.push({
          sender: 'ia',
          text: response.data.respuesta || 'No pude procesar tu solicitud.',
          timestamp: new Date()
        })

        if (response.data.campos_completados) {
          Object.entries(response.data.campos_completados).forEach(([hoja, campos]) => {
            console.log(`🔄 Actualizando ${hoja}:`, campos)
            if (this.form[hoja]) {
              this.form[hoja] = { ...this.form[hoja], ...campos }
            }
          })
        }

        this.iaAdvertencias = response.data.advertencias || []

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
      } finally {
        this.iaCargando = false
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
      if (!this.form.hoja1.nombre) {
        return Swal.fire({ icon: 'warning', title: 'Falta el nombre', text: 'Ingresa el nombre en la Hoja 1.' })
      }
      if (!this.form.hoja4.aptitud) {
        return Swal.fire({ icon: 'warning', title: 'Falta la aptitud', text: 'Selecciona la aptitud en la Hoja 4.' })
      }

      this.guardando = true
      try {
        const payload = {
          ...this.form,
          folio: this.folioTR,
          tipo_evaluacion: 'examen_tr_2026',
          paciente_id: Number(this.pacienteId),
          empresa_cliente_id: this.datosLaboralesActuales?.empresa_cliente_id ?? null,
          puesto_trabajo_id: this.datosLaboralesActuales?.puesto_trabajo_id ?? null,
        }

        const response = this.fichaId
          ? await clinicaTrabajo.put(`/AudiologiaTrabajoAltoRiesgo/${this.fichaId}`, payload)
          : await clinicaTrabajo.post('/AudiologiaTrabajoAltoRiesgo', payload)

        const idGuardado = this.fichaId || response.data.id
        if (!this.fichaId) {
          this.form.hoja1.folio = response.data.folio
          this.fichaId = idGuardado
        }

        const decision = await Swal.fire({
          icon: 'success',
          title: this.fichaId ? 'Examen actualizado' : 'Examen guardado',
          text: '¿Deseas imprimir el reporte?',
          showCancelButton: true,
          confirmButtonText: 'Imprimir reporte',
          cancelButtonText: 'Continuar',
          confirmButtonColor: '#1E88E5',
        })

        if (decision.isConfirmed) {
          window.open(`/AudiologiaTrabajoAltoRiesgo-imprimir/${idGuardado}`, '_blank')
        }

        if (!this.fichaId) {
          window.location.href = `/AudiologiaTrabajoAltoRiesgo/${this.pacienteId}?ficha=${idGuardado}`
        }
      } catch (error) {
        console.error('Error guardando:', error)
        Swal.fire({ icon: 'error', title: 'Error', text: error.response?.data?.message || error.message })
      } finally {
        this.guardando = false
      }
    },
    cancelar() {
      if (confirm('¿Está seguro de que desea cancelar? Se perderán los cambios.')) {
        window.location.href = '/clinica/audiologia'
      }
    },
    imprimir() {
      if (!this.fichaId) {
        return Swal.fire({ icon: 'warning', title: 'Guarda primero', text: 'El reporte se genera a partir de un examen guardado.' })
      }
      window.open(`/AudiologiaTrabajoAltoRiesgo-imprimir/${this.fichaId}`, '_blank')
    },
    // AGREGAR ESTOS MÉTODOS:

    construirContextoIA() {
      const { hoja1, hoja2, hoja3, hoja4 } = this.form
      
      const patologiasActivas = []
      Object.entries(hoja2).forEach(([key, val]) => {
        if (typeof val === 'boolean' && val) patologiasActivas.push(key)
        else if (val && typeof val === 'object' && val.activo) patologiasActivas.push(key)
      })

      const sistemasAnormales = Object.entries(hoja3)
        .filter(([k, v]) => ['cabeza','ojos','oidos','nariz','boca','cuello','cardiovascular','pulmonar','abdomen','genitourinario','osteomuscular','neurologico','tegumentos'].includes(k) && v.estado !== 'Normal')
        .map(([k, v]) => `${k}: ${v.estado}${v.observaciones ? ' - ' + v.observaciones : ''}`)

      const riesgosSeleccionados = Object.entries(hoja1.tipo_trabajo_riesgo || {})
        .filter(([_, v]) => v)
        .map(([k]) => k)

      return {
        hoja1: {
          edad: hoja1.edad,
          genero: hoja1.genero,
          puesto: hoja1.puesto,
          empresa: hoja1.empresa,
          antiguedad_empresa_anos: hoja1.antiguedad_empresa_anos,
          riesgos_trabajo: riesgosSeleccionados
        },
        hoja2: {
          patologias_activas: patologiasActivas
        },
        hoja3: {
          peso_kg: hoja3.peso_kg,
          estatura_m: hoja3.estatura_m,
          imc: hoja3.imc,
          presion_arterial: `${hoja3.presion_sistolica}/${hoja3.presion_diastolica}`,
          frecuencia_cardiaca: hoja3.frecuencia_cardiaca,
          frecuencia_respiratoria: hoja3.frecuencia_respiratoria,
          sistemas_anormales: sistemasAnormales
        },
        hoja4: {
          ecg: hoja4.ecg,
          rx_torax: hoja4.rx_torax,
          evaluacion_visual: hoja4.evaluacion_visual,
          audiometria: hoja4.audiometria,
          espirometria: hoja4.espirometria,
          diagnosticos: hoja4.diagnosticos.filter(d => d && d.trim()),
          enfermedad_profesional: hoja4.enfermedad_profesional
        }
      }
    },

    programarRecomendacionIA() {
      clearTimeout(this.iaTimer)
      // Validar que haya datos mínimos antes de disparar la IA
      if (!this.tieneDatosSuficientes()) {
        console.log('⏳ IA en espera: datos insuficientes para recomendar')
        return
      }
      this.iaTimer = setTimeout(() => this.recomendarConIA(), 2000)
    },

    tieneDatosSuficientes() {
      const h1 = this.form.hoja1
      const h2 = this.form.hoja2
      const h3 = this.form.hoja3
      const h4 = this.form.hoja4
      
      // Hoja 1: debe tener al menos nombre y empresa
      const hoja1Lista = h1.nombre && h1.nombre.trim().length > 0
                      && h1.empresa && h1.empresa.trim().length > 0
      
      // Hoja 2: debe tener al menos 1 patología marcada
      const patologiasActivas = Object.values(h2).filter(v => {
        if (typeof v === 'boolean') return v
        if (typeof v === 'object' && v !== null) return v.activo === true
        return false
      }).length
      const hoja2Lista = patologiasActivas > 0
      
      // Hoja 3: debe tener peso Y estatura (para IMC) o al menos presión arterial
      const hoja3Lista = (h3.peso_kg > 0 && h3.estatura_m > 0)
                      || (h3.presion_sistolica > 0 && h3.presion_diastolica > 0)
      
      // Hoja 4: debe tener al menos 1 paraclínico realizado o 1 diagnóstico
      const paraclinicosRealizados = [h4.ecg, h4.rx_torax, h4.evaluacion_visual, h4.audiometria, h4.espirometria]
        .some(p => p.realizado === true || p.realizado === 'true')
      const diagnosticosLlenos = h4.diagnosticos.some(d => d && d.trim().length > 0)
      const hoja4Lista = paraclinicosRealizados || diagnosticosLlenos
      
      // Regla: al menos Hoja 1 + Hoja 3 (signos vitales) para recomendar
      const tieneMinimo = hoja1Lista && hoja3Lista
      
      return tieneMinimo
    },

    async recomendarConIA() {
      if (this.iaCargando) {
        this.recomendacionPendiente = true
        return
      }

      const firma = this.firmaContextoIA
      if (firma === this.ultimaFirmaIA) return

      this.iaCargando = true
      try {
        const response = await clinicaTrabajo.post('/AudiologiaTrabajoAltoRiesgo-consultarIA', {
          modo: 'recomendar',
          paciente_id: this.pacienteId ? Number(this.pacienteId) : null,
          contexto: this.construirContextoIA()
        })

        const propuestos = response.data.campos_completados?.hoja4 || {}
        const nuevoHoja4 = { ...this.form.hoja4 }

        Object.entries(propuestos).forEach(([campo, valor]) => {
          if (this.editadosPorMedico[campo]) return
          const vacio = v => v === '' || v === null || v === undefined || (Array.isArray(v) && v.length === 0)
          const estaLlenado = !vacio(nuevoHoja4[campo])
          if (estaLlenado && !this.iaCampos[campo]) return

          // Si es el campo 'diagnosticos', inyectar en el array
          if (campo === 'diagnosticos' && Array.isArray(valor)) {
            valor.forEach((diag, idx) => {
              if (idx < nuevoHoja4.diagnosticos.length) {
                nuevoHoja4.diagnosticos[idx] = diag
                this.iaDiagsPropuestos.push(idx)
              }
            })
            this.iaCampos['diagnosticos'] = true
          } else {
            nuevoHoja4[campo] = valor
            this.iaCampos[campo] = true
          }
        })

        this.form.hoja4 = nuevoHoja4
        this.snapshotHoja4 = JSON.parse(JSON.stringify(nuevoHoja4))
        this.ultimaFirmaIA = firma
        this.iaAdvertencias = response.data.advertencias || []
      } catch (error) {
        console.error('Error en recomendación IA:', error)
      } finally {
        this.iaCargando = false
        if (this.recomendacionPendiente) {
          this.recomendacionPendiente = false
          this.programarRecomendacionIA()
        }
      }
    },

    async cargarFicha(id) {
      this.cargando = true
      try {
        const response = await clinicaTrabajo.get(`/AudiologiaTrabajoAltoRiesgo/${id}`)
        const registro = response.data.data
        const datos = registro.datos || {}

        this.fichaId = registro.id
        this.pacienteId = registro.paciente_id
        this.form.hoja1 = { ...this.form.hoja1, ...(datos.hoja1 || {}) }
        this.form.hoja2 = { ...this.form.hoja2, ...(datos.hoja2 || {}) }
        this.form.hoja3 = { ...this.form.hoja3, ...(datos.hoja3 || {}) }
        this.form.hoja4 = { ...this.form.hoja4, ...(datos.hoja4 || {}) }
        this.form.hoja1.folio = registro.folio

        this.snapshotHoja4 = JSON.parse(JSON.stringify(this.form.hoja4))
        this.$nextTick(() => {
          this.ultimaFirmaIA = this.firmaContextoIA
        })
      } catch (error) {
        console.error('Error al cargar la ficha:', error)
        Swal.fire({ icon: 'error', title: 'Error', text: error.response?.data?.message || error.message })
        window.location.href = '/AudiologiaTrabajoAltoRiesgo'
      } finally {
        this.cargando = false
      }
    },
  },

 async mounted() {
    // 1. OBTENER EL ID DE LA URL AUTOMÁTICAMENTE
    // Intenta tomarlo de Vue Router, si no, lo extrae de la URL del navegador
    this.pacienteId = this.$route?.params?.id || window.location.pathname.split('/').pop();

    // 2. SI HAY UN ID VÁLIDO, CARGAMOS LOS DATOS DEL PACIENTE
    // La condición !isNaN asegura que no intente cargar si la URL termina en texto (ej. '/tr')
    if (this.pacienteId && !isNaN(this.pacienteId) && this.pacienteId !== 'tr') {
      this.obtenerPacientes();
    }

    const fichaParam = new URLSearchParams(window.location.search).get('ficha')
    if (fichaParam) {
      await this.cargarFicha(fichaParam)
    }

    if (!this.form.hoja1.folio) {
      this.form.hoja1.folio = this.folioTR
    }

    this.snapshotHoja4 = JSON.parse(JSON.stringify(this.form.hoja4))
  },

  // AGREGAR ESTOS WATCHERS:
  watch: {
    firmaContextoIA(nueva) {
      if (nueva !== this.ultimaFirmaIA) this.programarRecomendacionIA()
    },
    'form.hoja4': {
      deep: true,
      handler(nuevo) {
        const anterior = this.snapshotHoja4 || {}
        const cambiados = Object.keys(nuevo).filter(
          k => JSON.stringify(nuevo[k]) !== JSON.stringify(anterior[k])
        )
        
        cambiados.forEach(k => {
          // Agregamos 'diagnosticos' a la lista de campos vigilados
          if (['aptitud', 'diagnostico_principal', 'observaciones', 'recomendaciones', 'diagnosticos'].includes(k)) {
            this.editadosPorMedico[k] = true
            delete this.iaCampos[k]
            
            // Si el campo modificado es el array de diagnósticos, limpiamos los índices propuestos
            if (k === 'diagnosticos') {
              this.iaDiagsPropuestos = []
            }
          }
        })
        
        this.snapshotHoja4 = JSON.parse(JSON.stringify(nuevo))
      }
    }
  },
  beforeUnmount() {
    clearTimeout(this.iaTimer)
  },

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