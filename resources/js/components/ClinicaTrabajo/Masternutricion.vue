<template>
  <div class="master-historia-clinica-nutricional">
    <!-- LISTADO (Si no es nueva ni edición) -->
    <!-- <listado-valoraciones v-if="!esNuevo && !esEdicion" submodulo="nutricion"></listado-valoraciones> -->
    <!-- FORMULARIO DE EVALUACIÓN (Si es nueva o edición) -->
    <div  class="container-fluid p-0">
      <!-- 1. CHAT IA / TRANSCRIPCIÓN EN VIVO (ARRIBA) -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card card-primary card-outline shadow-sm">
            <div class="card-header bg-gradient-primary text-white">
              <h3 class="card-title">
                <i class="fas fa-microphone-alt mr-2"></i>
                Asistente IA y Transcripción en Vivo (DELLI)
              </h3>
              <div class="card-tools">
                <span v-if="escuchando" class="badge badge-light text-danger mr-2 animate-pulse">
                  🎙️ IA Escuchando...
                </span>
              </div>
            </div>
            <div class="card-body p-0">
              <div ref="chatContainer" class="direct-chat-messages p-3 bg-light"
                style="height: 200px; overflow-y: auto;">
                <!-- Mensaje de bienvenida -->
                <div class="direct-chat-msg mb-3" v-if="conversacion.length === 0">
                  <div class="direct-chat-infos clearfix">
                    <span class="direct-chat-name float-left text-primary">🤖 Asistente Nutricional IA</span>
                  </div>
                  <div class="direct-chat-text bg-white border">
                    ¡Hola! Estoy lista para asistirte con la Historia Clínica Nutricional. Puedes dictarme los datos del
                    paciente, síntomas o hallazgos, y yo llenaré los campos por ti.
                  </div>
                </div>
                <!-- Historial de conversación -->
                <div v-for="(msg, index) in conversacion" :key="index" class="mb-3">
                  <div v-if="msg.sender === 'user'" class="direct-chat-msg">
                    <div class="direct-chat-infos clearfix">
                      <span class="direct-chat-name float-left">👨‍⚕️ Nutriólogo(a)</span>
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
                  <button class="btn btn-outline-secondary" type="button" @click="$refs.inputArchivo.click()"
                    title="Adjuntar archivo">
                    <i class="fas fa-paperclip"></i>
                  </button>
                  <input ref="inputArchivo" type="file" hidden @change="seleccionarArchivo" />
                  <button class="btn" :class="escuchando ? 'btn-danger' : 'btn-outline-secondary'" type="button"
                    @click="alternarEscucha" title="Iniciar/Detener transcripción por voz">
                    <i :class="escuchando ? 'fas fa-stop' : 'fas fa-microphone'"></i>
                  </button>
                </div>
                <input type="text" class="form-control"
                  placeholder="Escribe, dicta o adjunta estudios de laboratorio..." v-model="mensajeActual"
                  @keyup.enter="enviarMensaje" />
                <div class="input-group-append">
                  <button class="btn btn-primary" type="button" @click="enviarMensaje" :disabled="guardando">
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
        <div class="card-header bg-success text-white">
          <h4 class="mb-0">
            <i class="fas fa-apple-alt mr-2"></i>
            Historia Clínica Nutricional Massvital
            <span class="badge badge-light text-success ml-2">{{ folioNutricion }}</span>
          </h4>
        </div>
        <div class="card-body p-0">
          <ul class="nav nav-tabs nav-fill" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" data-toggle="tab" href="#hoja1" role="tab">
                <i class="fas fa-user mr-1"></i> 1. Identificación
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#hoja2" role="tab">
                <i class="fas fa-notes-medical mr-1"></i> 2. Antecedentes
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#hoja3" role="tab">
                <i class="fas fa-briefcase mr-1"></i> 3. Hábitos y Riesgo Laboral
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#hoja4" role="tab">
                <i class="fas fa-ruler-combined mr-1"></i> 4. Antropometría y Bioquímica
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#hoja5" role="tab">
                <i class="fas fa-clipboard-check mr-1"></i> 5. Diagnóstico y Aptitud
              </a>
            </li>
          </ul>
        </div>
      </div>
      <!-- 3. TABS CONTENT -->
      <div class="tab-content bg-white p-4 rounded-bottom shadow-sm mb-4">
        <!-- HOJA 1: Datos Básicos -->
        <div id="hoja1" class="tab-pane fade show active" role="tabpanel">
          <datos-basico-nutricion :modelValue="form.datosBasicos"
            @update:modelValue="form.datosBasicos = $event"></datos-basico-nutricion>
        </div>
        <!-- HOJA 2: Antecedentes -->
        <div id="hoja2" class="tab-pane fade" role="tabpanel">
          <antecedentes-heredofamiliares :modelValue="form.antecedentes"
            @update:modelValue="form.antecedentes = $event"></antecedentes-heredofamiliares>
          <antecedentes-medicos :modelValue="form.antecedentes"
            @update:modelValue="form.antecedentes = $event"></antecedentes-medicos>
          <cirugias-medicamentos :modelValue="form.antecedentes"
            @update:modelValue="form.antecedentes = $event"></cirugias-medicamentos>
        </div>
        <!-- HOJA 3: Hábitos y Factores Laborales -->
        <div id="hoja3" class="tab-pane fade" role="tabpanel">
          <habitos-consumo-nutricion :modelValue="form.habitos"
            @update:modelValue="form.habitos = $event"></habitos-consumo-nutricion>
          <factores-laborales-nutricion :modelValue="form.factoresLaborales"
            @update:modelValue="form.factoresLaborales = $event"></factores-laborales-nutricion>
        </div>
        <!-- HOJA 4: Antropometría y Bioquímica -->
        <div id="hoja4" class="tab-pane fade" role="tabpanel">
          <antropometria-nutricion :modelValue="form.evaluacionFisica"
            @update:modelValue="form.evaluacionFisica = $event"></antropometria-nutricion>
          <bioquimica-nutricion :modelValue="form.evaluacionFisica"
            @update:modelValue="form.evaluacionFisica = $event"></bioquimica-nutricion>
        </div>
        <!-- HOJA 5: Diagnóstico y Aptitud -->
        <div id="hoja5" class="tab-pane fade" role="tabpanel">
          <diagnostico-nutricion :modelValue="form.dictamen"
            @update:modelValue="form.dictamen = $event"></diagnostico-nutricion>
          <aptitud-dictamen-nutricion :modelValue="form.dictamen"
            @update:modelValue="form.dictamen = $event"></aptitud-dictamen-nutricion>
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
// Importar componentes del Listado
//import ListadoValoraciones from './ListadoValoraciones.vue'

// Importar componentes de las Secciones de Nutrición
// AJUSTA las rutas según tu estructura de carpetas real
import DatosBasicoNutricion from './secciones/nutricion/DatosBasico.vue'
import AntecedentesHeredofamiliares from './secciones/nutricion/AntecedentesHeredofamiliares.vue'
import AntecedentesMedicos from './secciones/nutricion/AntecedentesMedicos.vue'
import CirugiasMedicamentos from './secciones/nutricion/CirugiasMedicamentos.vue'
import HabitosConsumoNutricion from './secciones/nutricion/HabitosConsumoNutricion.vue'
import FactoresLaboralesNutricion from './secciones/nutricion/FactoresLaborales.vue'
import AntropometriaNutricion from './secciones/nutricion/Antropometria.vue'
import BioquimicaNutricion from './secciones/nutricion/Bioquimica.vue'
import DiagnosticoNutricion from './secciones/nutricion/Diagnostico.vue'
import AptitudDictamenNutricion from './secciones/nutricion/AptitudDictamen.vue'

// Servicio API (Ajusta el nombre según tu proyecto)
import clinicaTrabajo from '@/services/ApiServiceClinicaTrabajo'

export default {
  name: 'MasterNutricion',
  components: {
    // ListadoValoraciones,
    DatosBasicoNutricion,
    AntecedentesHeredofamiliares,
    AntecedentesMedicos,
    CirugiasMedicamentos,
    HabitosConsumoNutricion,
    FactoresLaboralesNutricion,
    AntropometriaNutricion,
    BioquimicaNutricion,
    DiagnosticoNutricion,
    AptitudDictamenNutricion
  },
  data() {
    return {
      esNuevo: false,
      esEdicion: false,
      guardando: false,
      cargando: false, // <-- AGREGAR ESTA LÍNEA
      escuchando: false, // Para la transcripción en vivo
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
          nombre_paciente: '', // <-- AGREGAR ESTO
          empresa_cliente_id: null,
          nombre_empresa: '', // <-- AGREGAR ESTO
          puesto_trabajo_id: null,
          nombre_puesto: '', // <-- AGREGAR ESTO
          nutriologo_id: null,
          paciente: null
        },
        antecedentes: {
          hf_obesidad: 'no', hf_diabetes: 'no', hf_hipertension: 'no', hf_cancer: 'no', hf_nefropatia: 'no', hf_otros: 'no',
          am_obesidad: 'no', am_diabetes: 'no', am_hipertension: 'no', am_dislipidemias: 'no',
          am_cardiovasculares: 'no', am_renales: 'no', am_hepaticos: 'no', am_musculo_esqueleticos: 'no', am_urologicos: 'no', am_infecciosos: 'no',
          menarca_anos: null, ciclos: '', fum: '', metodo_planificacion: '',
          cirugias: 'no', cirugias_especifique: '', consumo_medicamentos: '', alergias: ''
        },
        habitos: {
          tabaquismo: false, alcoholismo: false, varia_consumo_estres: false, varia_consumo_tristeza: false,
          escala_fagerstrm: 0, escala_audit: 0, frecuencia_consumo_alcohol: '',
          refiere_diarrea: false, refiere_estrenimiento: false, refiere_gastritis: false, refiere_ulcera: false,
          refiere_nauseas: false, refiere_reflujo: false, refiere_vomitos: false, refiere_colitis: false
        },
        factoresLaborales: {
          tension_emocional: 'no', alta_responsabilidad: 'no', carga_excesiva_trabajo: 'no',
          turno_rotativo: 'no', turno_nocturno: 'no', trabajo_repetitivo: 'no',
          condiciones_entorno: 'no', carga_trabajo: 'no', falta_control_trabajo: 'no',
          jornadas_trabajo: 'no', interferencia_trabajo_familia: 'no', relaciones_interpersonales: 'no',
          violencia_laboral: 'no', acontecimientos_traumaticos: 'no'
        },
        evaluacionFisica: {
          estatura_m: null, peso_kg: null, peso_ideal_kg: null,
          circunferencia_cintura_cm: null, circunferencia_cadera_cm: null, circunferencia_brazo_cm: null,
          grasa_corporal_pct: null, grasa_visceral_pct: null, musculo_pct: null,
          glucosa_mg_dl: null, trigliceridos_mg_dl: null, colesterol_total_mg_dl: null,
          hdl_mg_dl: null, ldl_mg_dl: null, acido_urico_mg_dl: null, hemoglobina_g_dl: null,
          paciente: null // <-- AGREGAR ESTO PARA QUE BIOQUIMICA LEA EL GÉNERO
        },
        dictamen: {
          diagnostico_problema: '',
          imc_clasificacion: '',
          aptitud: '',
          restricciones: '',
          recomendaciones: '',
          seguimiento_requerido: false,
          plazo_proximo_seguimiento: '',
          alimentos_ids: [],
          alimentos_frecuencias: []
        }
      }
    }
  },

  computed: {
    folioNutricion() {
      if (!this.form.datosBasicos.folio || this.form.datosBasicos.folio.includes('****')) {
        const ano = new Date().getFullYear()
        const random = String(Math.floor(Math.random() * 9000) + 1000)
        return `NUT-${ano}-${random}`
      }
      return this.form.datosBasicos.folio
    }
  },

  methods: {
    detectarRuta() {
       const pathname = window.location.pathname
      // Detecta si es nueva evaluación (incluye "nueva" o "ficha")
      if (pathname.includes('/nueva') || pathname.includes('/ficha')) {
        this.esNuevo = true
        this.esEdicion = false
      } 
      // Detecta edición (ruta con ID numérico al final pero NO es ficha)
      else if (/\/nutricion\/\d+$/.test(pathname) && !pathname.includes('/ficha')) {
        this.esNuevo = false
        this.esEdicion = true
        this.cargarDatosExistentes()
      } 
      else {
        this.esNuevo = false
        this.esEdicion = false
      }
    },

    alternarEscucha() {
      this.escuchando = !this.escuchando
      if (this.escuchando) {
        // TODO: Aquí integrarías tu lógica de Web Speech API o servicio de transcripción en vivo 
        this.conversacion.push({ sender: 'ia', text: '🎙️ Escuchando... Habla ahora.', timestamp: new Date() })
        this.scrollearChat()
      } else {
        this.conversacion.push({ sender: 'ia', text: '⏹️ Transcripción detenida.', timestamp: new Date() })
        this.scrollearChat()
      }
    },

    async enviarMensaje() {
      if (!this.mensajeActual.trim() && !this.archivoSeleccionado) return

      const textoEnviar = this.mensajeActual
      this.conversacion.push({
        sender: 'user',
        text: this.archivoSeleccionado ? `[Archivo adjunto: ${this.archivoSeleccionado.name}] ${textoEnviar}` : textoEnviar,
        timestamp: new Date()
      })
      this.mensajeActual = ''
      this.scrollearChat()

      try {
        // Simulación de llamada a IA. Ajusta el endpoint a tu backend real
        const response = await clinicaTrabajo.get('/clinica/nutricion/ia-consultar', {
          params: {
            mensaje: textoEnviar,
            archivo: this.archivoSeleccionado,
            paciente_id: this.form.datosBasicos.paciente_id,
            contexto_actual: this.form
          }
        })

        this.conversacion.push({
          sender: 'ia',
          text: response.data.respuesta || 'Procesando información...',
          timestamp: new Date()
        })

        // Si la IA devuelve campos para actualizar automáticamente
        if (response.data.campos_completados) {
          this.fusionarDatosIA(response.data.campos_completados)
        }

        this.quitarArchivo()
        this.scrollearChat()
      } catch (error) {
        console.error('Error enviando mensaje a IA:', error)
        this.conversacion.push({
          sender: 'ia',
          text: '❌ Disculpa, hubo un error al procesar tu solicitud.',
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
      try {
        // Validaciones críticas
        if (!this.form.datosBasicos.paciente_id) {
          alert('⚠️ Por favor selecciona un Paciente en la Hoja 1.')
          return
        }
        if (!this.form.dictamen.aptitud) {
          alert('⚠️ Es obligatorio seleccionar la APTITUD en la Hoja 5 para poder guardar.')
          return
        }

        this.guardando = true

        const payload = {
          ...this.form,
          folio: this.folioNutricion,
          tipo_evaluacion: this.form.datosBasicos.tipo_evaluacion || 'nutricion'
        }

        const response = await clinicaTrabajo.post('/clinica/nutricion', payload)

        if (response.data.success) {
          alert('✅ Historia Clínica Nutricional guardada correctamente.')
          window.location.href = `/clinica/nutricion/${response.data.id}`
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
        window.location.href = '/clinica/nutricion'
      }
    },

    imprimir() {
      alert('🖨️ Generando vista previa para impresión...')
      // TODO: window.print() o integración con html2pdf / jsPDF
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

        // ✅ Cargar valoración existente
        const response = await clinicaTrabajo.get(`/clinica/nutricion/${valoracionId}`);
        const data = response.data.data || response.data;

        // Mapear datos al formulario
        this.form = {
          ...this.form,
          datosBasicos: {
            ...this.form.datosBasicos,
            folio: data.folio || this.folioNutricion,
            tipo_evaluacion: data.tipo_evaluacion || '',
            fecha_valoracion: data.fecha_valoracion || new Date().toISOString().split('T')[0],
            hora_valoracion: data.hora_valoracion || '',
            paciente_id: data.paciente_id,
            nombre_paciente: data.paciente?.nombre || '',
            empresa_cliente_id: data.empresa_cliente_id,
            nombre_empresa: data.empresa_cliente?.razon_social || '',
            puesto_trabajo_id: data.puesto_trabajo_id,
            nombre_puesto: data.puesto_trabajo?.nombre || '',
            nutriologo_id: data.nutriologo_id || data.medico_id,
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
            alergias: data.alergias || ''
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
          evaluacionFisica: {
            ...this.form.evaluacionFisica,
            estatura_m: data.estatura_m || null,
            peso_kg: data.peso_kg || null,
            peso_ideal_kg: data.peso_ideal_kg || null,
            circunferencia_cintura_cm: data.circunferencia_cintura_cm || null,
            circunferencia_cadera_cm: data.circunferencia_cadera_cm || null,
            circunferencia_brazo_cm: data.circunferencia_brazo_cm || null,
            grasa_corporal_pct: data.grasa_corporal_pct || null,
            grasa_visceral_pct: data.grasa_visceral_pct || null,
            musculo_pct: data.musculo_pct || null,
            glucosa_mg_dl: data.glucosa_mg_dl || null,
            trigliceridos_mg_dl: data.trigliceridos_mg_dl || null,
            colesterol_total_mg_dl: data.colesterol_total_mg_dl || null,
            hdl_mg_dl: data.hdl_mg_dl || null,
            ldl_mg_dl: data.ldl_mg_dl || null,
            acido_urico_mg_dl: data.acido_urico_mg_dl || null,
            hemoglobina_g_dl: data.hemoglobina_g_dl || null,
            paciente: data.paciente || null
          },
          dictamen: {
            ...this.form.dictamen,
            diagnostico_problema: data.diagnostico_problema || '',
            imc_clasificacion: data.imc_clasificacion || '',
            aptitud: data.aptitud || '',
            restricciones: data.restricciones || '',
            recomendaciones: data.recomendaciones || '',
            seguimiento_requerido: data.seguimiento_requerido || false,
            plazo_proximo_seguimiento: data.plazo_proximo_seguimiento || '',
            alimentos_ids: data.alimentos_ids || [],
            alimentos_frecuencias: data.alimentos_frecuencias || []
          }
        };

        console.log('✅ Valoración nutricional cargada correctamente:', this.form);

      } catch (error) {
        console.error('❌ Error al cargar valoración nutricional:', error);
        alert('Error al cargar la valoración: ' + (error.response?.data?.message || error.message));
        window.location.href = '/clinica/nutricion';
      } finally {
        this.cargando = false;
      }
    },

    async cargarDatosPaciente(pacienteId) {
      console.log('🚀 INICIANDO carga de datos para el paciente ID:', pacienteId);
      
      if (!pacienteId) {
        console.warn('⚠️ No se proporcionó un pacienteId válido');
        return;
      }

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
          folio: this.folioNutricion
        };

        // Prellenar antecedentes si existen
        if (data.alergias) {
          this.form.antecedentes.alergias = data.alergias;
        }
        if (data.antecedentes_medicos) {
          this.form.antecedentes.am_otros = data.antecedentes_medicos;
        }

        console.log('✅ Datos del paciente prellenados correctamente:', this.form.datosBasicos);

      } catch (error) {
        console.error('❌ Error al cargar datos del paciente:', error);
        alert('No se pudieron cargar los datos del paciente.');
      } finally {
        this.cargando = false;
      }
    },

  },
  mounted() {
      this.detectarRuta();

      // Inicializar folio si es nuevo
      if (this.esNuevo && !this.form.datosBasicos.folio) {
        this.form.datosBasicos.folio = this.folioNutricion;
      }

      // ✅ EXTRAER ID DIRECTAMENTE DE LA URL
      const pathname = window.location.pathname;
      const partes = pathname.split('/');
      const ultimoSegmento = partes[partes.length - 1];
      const pacienteId = !isNaN(ultimoSegmento) ? parseInt(ultimoSegmento) : null;

      // ✅ Cargar datos del paciente si hay ID y es modo nuevo/ficha
      if (pacienteId && (this.esNuevo || pathname.includes('/ficha'))) {
        console.log('🔍 ID detectado en URL:', pacienteId);
        console.log('📍 Ruta actual:', pathname);
        this.cargarDatosPaciente(pacienteId);
      } else {
        console.log('️ No se detectó ID válido o no es modo ficha/nueva');
        console.log(' Ruta:', pathname);
        console.log('🔢 ID extraído:', pacienteId);
        console.log('🆕 esNuevo:', this.esNuevo);
      }
    }
}
</script>
<style scoped>
.master-historia-clinica-nutricional {
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

.direct-chat-text.bg-primary {
  background-color: #007bff !important;
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
  0% {
    opacity: 1;
  }

  50% {
    opacity: 0.5;
  }

  100% {
    opacity: 1;
  }
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
  from {
    opacity: 0;
    transform: translateY(5px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
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