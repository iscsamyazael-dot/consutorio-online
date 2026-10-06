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

          <!-- PESTAÑAS DE NAVEGACIÓN -->
          <div class="card-header p-0 bg-light border-bottom">
            <ul class="nav nav-tabs card-header-tabs w-100" role="tablist">
              <li class="nav-item" role="presentation">
                <button
                  class="nav-link"
                  :class="{ 'active': hojaActual === 1 }"
                  @click="hojaActual = 1"
                  role="tab"
                  aria-selected="true"
                >
                  <i class="fas fa-briefcase mr-1"></i> Hoja 1: Datos Laborales
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button
                  class="nav-link"
                  :class="{ 'active': hojaActual === 2 }"
                  @click="hojaActual = 2"
                  role="tab"
                  aria-selected="false"
                >
                  <i class="fas fa-notes-medical mr-1"></i> Hoja 2: Antecedentes Médicos
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button
                  class="nav-link"
                  :class="{ 'active': hojaActual === 3 }"
                  @click="hojaActual = 3"
                  role="tab"
                  aria-selected="false"
                >
                  <i class="fas fa-stethoscope mr-1"></i> Hoja 3: Examen Físico
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button
                  class="nav-link"
                  :class="{ 'active': hojaActual === 4 }"
                  @click="hojaActual = 4"
                  role="tab"
                  aria-selected="false"
                >
                  <i class="fas fa-clipboard-check mr-1"></i> Hoja 4: Aptitud & Dictamen
                </button>
              </li>
            </ul>
            <!-- Barra de progreso -->
            <div class="progress m-3" style="height: 4px;">
              <div class="progress-bar bg-success" :style="{ width: (hojaActual / 4 * 100) + '%' }" role="progressbar"></div>
            </div>
          </div>

          <div class="card-body">

            <!-- HOJA 1: DATOS LABORALES -->
            <div v-show="hojaActual === 1">
              <DatosPuesto :modelValue="form.datos_puesto" @update:modelValue="form.datos_puesto = $event" />
              <hr class="my-4">
              <ExposicionRiesgos :modelValue="form.exposicion_riesgos" @update:modelValue="form.exposicion_riesgos = $event" />
            </div>

            <!-- HOJA 2: ANTECEDENTES MÉDICOS -->
            <div v-show="hojaActual === 2">
              <Antecedentes :modelValue="form.antecedentes" @update:modelValue="form.antecedentes = $event" />
            </div>

            <!-- HOJA 3: EXAMEN FÍSICO -->
            <div v-show="hojaActual === 3">
              <ClinicoExamen :modelValue="form.clinico_examen" @update:modelValue="form.clinico_examen = $event" />
            </div>

            <!-- HOJA 4: APTITUD & DICTAMEN -->
            <div v-show="hojaActual === 4">
              <AptitudDictamen :modelValue="form.aptitud" @update:modelValue="form.aptitud = $event" />
            </div>

          </div>

          <!-- BOTÓN GUARDAR FIJA EN EL FOOTER -->
          <div class="card-footer bg-light d-flex justify-content-between align-items-center">
            <div>
              <small class="text-muted">Hoja {{ hojaActual }} de 4 completada</small>
            </div>

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
</template>

<script>
import DatosPuesto from './secciones/medicina-enfermeria/datospuesto.vue'
import ExposicionRiesgos from './secciones/medicina-enfermeria/exposicionriesgos.vue'
import Antecedentes from './secciones/medicina-enfermeria/antecedentes.vue'
import ClinicoExamen from './secciones/medicina-enfermeria/clinicoexamen.vue'
import AptitudDictamen from './secciones/medicina-enfermeria/aptituddictamen.vue'
import { clinicaTrabajo } from '../../services/ApiService'

export default {
  name: 'MasterFichaOcupacional',
  components: {
    DatosPuesto,
    ExposicionRiesgos,
    Antecedentes,
    ClinicoExamen,
    AptitudDictamen
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
        datos_puesto: {
          folio: this.generarFolio(),
          tipo_evaluacion: '',
          fecha_evaluacion: new Date().toISOString().split('T')[0],
          hora_evaluacion: '',
          medico_evaluador: '',
          apellido_paterno: '',
          apellido_materno: '',
          nombre: '',
          edad: '',
          fecha_nacimiento: '',
          estado_civil: '',
          escolaridad: '',
          edad_inicio_vida_laboral: '',
          cedula: '',
          genero: '',
          lugar_nacimiento: '',
          tipo_sanguineo: '',
          telefono: '',
          celular: '',
          email: '',
          direccion: '',
          emergencia_nombre: '',
          emergencia_telefono: '',
          emergencia_relacion: '',
          ultima_consulta_fecha: '',
          ultima_consulta_motivo: '',
          numero_empleado: '',
          empresa: '',
          departamento: '',
          puesto: '',
          antiguedad: '',
          antiguedad_laboral: '',
          antiguedad_empresa: '',
          tipo_contrato: '',
          jornada: '',
          modalidad_trabajo: '',
          categoria_laboral: '',
          descripcion_puesto: '',
          riesgos_identificados: '',
          signos_vitales: {
            frecuencia_cardiaca: '',
            frecuencia_respiratoria: '',
            presion_arterial: '',
            temperatura: '',
            saturacion_oxigeno: '',
            glucosa: ''
          }
        },

        exposicion_riesgos: {
          primer_apellido: '',
          segundo_apellido: '',
          nombres: '',
          tipo_sanguineo: '',
          lugar_nacimiento: '',
          telefono: '',
          celular: '',
          emergencia_nombre: '',
          emergencia_telefono: '',
          emergencia_relacion: '',
          empresas_anteriores: [
            { nombre: '', puesto: '', antiguedad: '' },
            { nombre: '', puesto: '', antiguedad: '' }
          ],
          agentes: {
            polvo_mineral: false,
            asbesto: false,
            silice: false,
            berilio: false,
            cadmio: false,
            plomo: false,
            mercurio: false,
            cromo: false,
            arsenico: false,
            niquel: false,
            dioxinas: false,
            vibraciones: false,
            radiacion_ionizante: false,
            radiacion_no_ionizante: false,
            ruido: false,
            temperatura_extrema: false,
            estres_termico: false,
            exposicion_solar: false,
            otros: ''
          },
          condiciones_riesgo: {
            trabajo_altura_2m: false,
            espacio_confinado: false,
            maquinaria_pesada: false,
            cargas_25kg: false,
            sustancias_quimicas: false,
            agentes_biologicos: false,
            radiaciones_ionizantes: false,
            radiaciones_no_ionizantes: false,
            turnos_rotativos_nocturnos: false,
            estres_ocupacional: false,
            trabajo_repetitivo: false,
            posturas_forzadas: false,
            vibraciones_2: false,
            temperatura_extrema_2: false,
            exposicion_solar_prolongada: false
          },
          otras_condiciones: {
            realiza_generalmente: '',
            carga_mental: false,
            decisiones_criticas: false,
            responsabilidad_personas: false,
            comunicacion_constante: false,
            precision_extrema: false,
            ritmo_acelerado: false,
            monotonia: false,
            falta_autonomia: false,
            conflictos: false,
            acoso: false,
            discriminacion: false
          }
        },

        antecedentes: {
          accidentes: [{ fecha: '', empresa: '', tipo: '', parte_cuerpo: '', dias_incapacidad: '', secuelas: '' }],
          enfermedades_laborales: [
            { diagnostico: '', fecha: '' },
            { diagnostico: '', fecha: '' },
            { diagnostico: '', fecha: '' }
          ],
          incapacidades_3m: {
            tiene: false,
            items: [{ motivo: '', dias: '' }, { motivo: '', dias: '' }]
          },
          vacunas: {
            covid19: { presente: false, fecha: '' },
            td: { presente: false, fecha: '' },
            tdpa: { presente: false, fecha: '' },
            influenza: { presente: false, fecha: '' },
            hepatitis_ab: { presente: false, fecha: '' },
            sr: { presente: false, fecha: '' }
          },
          cirugias_accidentes_no_laborales: [{ descripcion: '' }],
          alergias: [{ sustancia: '', reaccion: '', hospitalizacion: false }],
          transfusiones: [{ motivo: '', fecha: '' }],
          heredo_familiares: {
            cancer: { presente: false, parentesco: '' },
            diabetes: { presente: false, parentesco: '' },
            hipertension: { presente: false, parentesco: '' },
            cardiopatias: { presente: false, parentesco: '' },
            infarto: { presente: false, parentesco: '' },
            acv: { presente: false, parentesco: '' },
            pulmonar: { presente: false, parentesco: '' },
            tuberculosis: { presente: false, parentesco: '' },
            hepatica: { presente: false, parentesco: '' },
            renal: { presente: false, parentesco: '' },
            artritis: { presente: false, parentesco: '' },
            osteoporosis: { presente: false, parentesco: '' },
            mental: { presente: false, parentesco: '' },
            adiccion: { presente: false, parentesco: '' },
            otros: ''
          },
          no_patologicos: {
            tabaquismo: '',
            tabaquismo_detalles: '',
            alcoholismo: '',
            alcoholismo_detalles: '',
            drogas_no: true,
            drogas_si: false,
            drogas_detalles: '',
            actividad_fisica: '',
            estres: '',
            estres_detalles: ''
          },
          genero: '',
          gineco: {
            menarquia: '',
            ciclo_dias: '',
            duracion_dias: '',
            desorden_menstrual: false,
            dismenorrea: false,
            gestaciones: '',
            partos: '',
            abortos: '',
            anticonceptivos: false
          },
          urologo: {
            disfuncion_erectil: false,
            infertilidad: false,
            problemas_prostaticos: false,
            infecciones_urinarias: false,
            otros: ''
          },
          patologicos: {
            hipertension: { presente: false, observaciones: '' },
            cardiopatia: { presente: false, observaciones: '' },
            infarto: { presente: false, observaciones: '' },
            arritmia: { presente: false, observaciones: '' },
            asma: { presente: false, observaciones: '' },
            epoc: { presente: false, observaciones: '' },
            tuberculosis: { presente: false, observaciones: '' },
            neumonia: { presente: false, observaciones: '' },
            silicosis: { presente: false, observaciones: '' },
            ulcera: { presente: false, observaciones: '' },
            gastritis: { presente: false, observaciones: '' },
            hepatitis: { presente: false, observaciones: '' },
            cirrosis: { presente: false, observaciones: '' },
            diabetes: { presente: false, observaciones: '' },
            hipertiroidismo: { presente: false, observaciones: '' },
            hipotiroidismo: { presente: false, observaciones: '' },
            nefritis: { presente: false, observaciones: '' },
            litiasis: { presente: false, observaciones: '' },
            artrosis: { presente: false, observaciones: '' },
            artritis: { presente: false, observaciones: '' },
            osteoporosis: { presente: false, observaciones: '' },
            hernia_discal: { presente: false, observaciones: '' },
            sindrome_tunel_carpal: { presente: false, observaciones: '' },
            epilepsia: { presente: false, observaciones: '' },
            migraña: { presente: false, observaciones: '' },
            depresion: { presente: false, observaciones: '' },
            ansiedad: { presente: false, observaciones: '' },
            otros: ''
          }
        },

        clinico_examen: {
          signos_vitales: {
            fc: '',
            fr: '',
            ta: '',
            temperatura: '',
            saturacion: '',
            glucosa: ''
          },
          antropometria: {
            peso: '',
            talla: '',
            imc: '',
            perimetro_cintura: '',
            perimetro_cadera: '',
            complexion_fisica: '',
            estado_nutricional: ''
          },
          comorbilidades: {
            diabetes: false,
            hipertension: false,
            asma: false,
            cardiopatia: false,
            artritis: false,
            depresion: false,
            ansiedad: false,
            hiperlipidemia: false,
            tiroidea: false,
            otras_presente: false,
            otras: ''
          },
          sistemas: {
            cabeza: { interrogatorio: '', exploracion: '', notas: '' },
            cuello: {
              interrogatorio: '',
              exploracion: '',
              notas: '',
              tiroides: '',
              traquea: '',
              adenopatias: '',
              neoplasias: ''
            },
            ojos: {
              interrogatorio: '',
              exploracion: '',
              notas: '',
              agudeza_visual_od: '',
              agudeza_visual_oi: '',
              ishihara: '',
              presion_intraocular: ''
            },
            oidos: {
              interrogatorio: '',
              exploracion: '',
              notas: '',
              acufenos: false,
              mareos: false,
              audiometria: ''
            },
            nariz: {
              interrogatorio: '',
              exploracion: '',
              notas: '',
              epistaxis: false,
              obstruccion: false,
              desviacion_septal: ''
            },
            cardiovascular: {
              interrogatorio: '',
              exploracion: '',
              notas: '',
              auscultacion: '',
              pulsos_perifericos: '',
              edemas: '',
              varices: ''
            },
            pulmonar: {
              interrogatorio: '',
              exploracion: '',
              notas: '',
              auscultacion: '',
              tos_esputo: '',
              espirometria: ''
            },
            gastrointestinal: {
              interrogatorio: '',
              exploracion: '',
              notas: '',
              palpacion: '',
              ruidos_intestinales: '',
              tamano_higado: '',
              bazo: ''
            },
            abdomen: { interrogatorio: '', exploracion: '', notas: '' },
            miembros_superiores: { interrogatorio: '', exploracion: '', notas: '' },
            miembros_inferiores: { interrogatorio: '', exploracion: '', notas: '' },
            genitourinario: {
              interrogatorio: '',
              exploracion: '',
              notas: '',
              hallazgos_genitales: '',
              puño_percision: ''
            },
            piel: {
              interrogatorio: '',
              exploracion: '',
              notas: '',
              dermatitis: false,
              verrugas: false,
              nevos: false,
              nodulos: false,
              acne: false
            },
            inmunologico: {
              interrogatorio: '',
              exploracion: '',
              notas: '',
              lupus: false,
              vih: false,
              artritis_reumatoide: false
            }
          },
          medicamentos: '',
          alergias: '',
          pruebas_complementarias: [
            { tipo: '', fecha: '', resultado: '' }
          ]
        },

        aptitud: ''
      }
    }
  },

  methods: {
    generarFolio() {
      const fecha = new Date()
      const ano = fecha.getFullYear()
      const mes = String(fecha.getMonth() + 1).padStart(2, '0')
      const dia = String(fecha.getDate()).padStart(2, '0')
      const numero = String(Math.floor(Math.random() * 10000)).padStart(4, '0')
      return `MED-${ano}${mes}${dia}-${numero}`
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
      
      // Actualiza datos_puesto con info del trabajador
      this.form.datos_puesto.nombre = trabajador.nombre || ''
      this.form.datos_puesto.apellido_paterno = trabajador.apellido_paterno || ''
      this.form.datos_puesto.apellido_materno = trabajador.apellido_materno || ''
      this.form.datos_puesto.edad = trabajador.edad || ''
      this.form.datos_puesto.cedula = trabajador.cedula || ''
    },

    deseleccionarTrabajador() {
      this.trabajadorSeleccionado = null
      this.conversacion = []
      this.form = {
        datos_puesto: { /* estructura completa */ },
        exposicion_riesgos: { /* estructura completa */ },
        antecedentes: { /* estructura completa */ },
        clinico_examen: { /* estructura completa */ },
        aptitud: ''
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
          paciente_id: this.trabajadorSeleccionado.id,
          contexto: this.form
        })

        this.conversacion.push({
          sender: 'ia',
          text: response.data.respuesta || 'No pude procesar tu solicitud.',
          timestamp: new Date()
        })

        if (response.data.campos_completados) {
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

  watch: {}
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