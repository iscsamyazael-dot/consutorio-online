<template>
  <div class="container-fluid py-4">
    <!-- EVALUACIÓN ACTIVA -->
    <!-- <div v-else class="row"> -->
    <div class="row">
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
              <ExposicionRiesgos :modelValue="form.exposicion_riesgos" :datos-puesto="form.datos_puesto" @update:modelValue="form.exposicion_riesgos = $event" />
            </div>

            <!-- HOJA 2: ANTECEDENTES MÉDICOS -->
            <div v-show="hojaActual === 2">
              <Antecedentes :modelValue="form.antecedentes" :datos-puesto="form.datos_puesto" @update:modelValue="form.antecedentes = $event" />
            </div>

            <!-- HOJA 3: EXAMEN FÍSICO -->
            <div v-show="hojaActual === 3">
              <ClinicoExamen :modelValue="form.clinico_examen" :datos-puesto="form.datos_puesto" @update:modelValue="form.clinico_examen = $event" />
            </div>

            <!-- HOJA 4: APTITUD & DICTAMEN -->
            <div v-show="hojaActual === 4">
              <AptitudDictamen :modelValue="form.aptitud" :sugeridos="sugeridosIA" @update:modelValue="form.aptitud = $event" />
            </div>

            <!-- NAVEGACIÓN ENTRE HOJAS -->
            <div class="d-flex justify-content-between border-top pt-3 mt-4">
              <button v-if="hojaActual > 1" type="button" class="btn btn-outline-secondary" @click="irAHoja(hojaActual - 1)">
                <i class="fas fa-arrow-left mr-1"></i> Anterior
              </button>
              <span v-else></span>
              <button v-if="hojaActual < 4" type="button" class="btn btn-primary" @click="irAHoja(hojaActual + 1)">
                Siguiente <i class="fas fa-arrow-right ml-1"></i>
              </button>
            </div>

          </div>

          <!-- BOTÓN GUARDAR FIJA EN EL FOOTER -->
                    <!-- BOTONES DE ACCIÓN -->
          <div class="card-footer bg-light d-flex justify-content-end align-items-center">
            <small class="text-muted mr-auto">Hoja {{ hojaActual }} de 4</small>
            <button class="btn btn-secondary ml-2" @click="cancelar" :disabled="guardando">
              <i class="fas fa-times mr-2"></i> Cancelar
            </button>
            <button v-if="fichaId" class="btn btn-info text-white ml-2" @click="imprimir" :disabled="guardando">
              <i class="fas fa-print mr-2"></i> Imprimir
            </button>
            <button class="btn btn-success ml-2" @click="guardarFichaCompleta" :disabled="guardando">
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
import clinicaTrabajo from '@/services/ApiServiceClinicaTrabajo'
import Swal from 'sweetalert2'

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
      pacienteId: null,
      infoPacientes: null,
      datosLaboralesActuales: null,
      cargando: false,
      hojaActual: 1,
      trabajadorSeleccionado: null,
      escuchando: false,
      guardando: false,
      conversacion: [],
      mensajeActual: '',
      archivoSeleccionado: null,
      cargandoIA: false,
      advertenciasIA: [],
      fichaId: null,
      propuestaHecha: false,
      iaCampos: {},            // campos de la hoja 4 llenados por la IA y no editados aún
      editadosPorMedico: {},   // campos que el médico editó: la IA ya no los sobrescribe
      snapshotAptitud: {},     // copia para detectar qué cambió el médico
      ultimaFirmaIA: '',       // datos con los que se hizo la última propuesta
      recalcularIA: false,     // hubo cambios de hojas 1 a 3 mientras la IA respondía
      esEdicion: false,
      form: this.formularioVacio()
    }
  },

  methods: {
    /** Estructura completa del formulario. Se usa al iniciar y al cerrar una ficha. */
    formularioVacio() {
      return {
        datos_puesto: {
          folio:'',
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
          alergias: [{ sustancia: '', tipo_reaccion: '', hospitalizacion: '' }],
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
            drogas_no: false,
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
              puño_percusion: ''
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

        aptitud: {}
      }
    },
    
    irAHoja(numero) {
      if (numero < 1 || numero > 4) return
      this.hojaActual = numero
      this.$nextTick(() => {
        const encabezado = this.$el.querySelector('.card-tabs')
        if (encabezado) encabezado.scrollIntoView({ behavior: 'smooth', block: 'start' })
      })
    },

    async cancelar() {
      const { isConfirmed } = await Swal.fire({
        icon: 'warning',
        title: '¿Cancelar la ficha?',
        text: 'Se perderán los datos no guardados.',
        showCancelButton: true,
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'Seguir editando'
      })
      if (isConfirmed) window.location.href = '/buscador-pacientes-clinicaTrabajo'
    },

    imprimir() {
      if (!this.fichaId) {
        Swal.fire({ icon: 'warning', title: 'Guarda primero', text: 'El reporte se genera a partir de una ficha guardada.' })
        return
      }
      window.open(`/FichaMedicinaOcupacional-imprimir/${this.fichaId}`, '_blank')
    },

    async obtenerFolioSugerido() {
      try {
        const { data } = await clinicaTrabajo.get('/FichaMedicinaOcupacional/create')
        this.form.datos_puesto.folio = data.folio
        this.form.datos_puesto.medico_evaluador = data.medico || ''
      } catch (error) {
        console.error('No se pudo obtener el folio sugerido:', error)
      }
    },

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

        const datosLaborales = data.datos_laborales || []
        const laboral = datosLaborales.length > 0 ? datosLaborales[datosLaborales.length - 1] : null
        this.datosLaboralesActuales = laboral
        
        // Helper para calcular antigüedad en texto
        const partesFecha = (fechaStr) => {
          if (!fechaStr) return null
          const [a, m, d] = String(fechaStr).split('T')[0].split('-').map(Number)
          return { a, m, d }
        }

        // Helper para calcular edad
        const calcularEdad = (fechaStr) => {
          const f = partesFecha(fechaStr)
          if (!f) return ''
          const hoy = new Date()
          let edad = hoy.getFullYear() - f.a
          const mesHoy = hoy.getMonth() + 1
          if (mesHoy < f.m || (mesHoy === f.m && hoy.getDate() < f.d)) edad--
          return edad
        }

        

        const calcularAntiguedadTexto = (fechaIngresoStr) => {
          const f = partesFecha(fechaIngresoStr)
          if (!f) return ''
          const hoy = new Date()
          let anos = hoy.getFullYear() - f.a
          let meses = (hoy.getMonth() + 1) - f.m
          if (hoy.getDate() < f.d) meses -= 1
          if (meses < 0) { anos -= 1; meses += 12 }
          if (anos < 0) return ''
          if (anos > 0 && meses > 0) return `${anos} años, ${meses} meses`
          if (anos > 0) return `${anos} años`
          return `${meses} meses`
        }

        const anosDesde = (fechaStr) => {
          const f = partesFecha(fechaStr)
          if (!f) return ''
          const hoy = new Date()
          let anos = hoy.getFullYear() - f.a
          const mesHoy = hoy.getMonth() + 1
          if (mesHoy < f.m || (mesHoy === f.m && hoy.getDate() < f.d)) anos -= 1
          return anos < 0 ? '' : anos
        }

        const antiguedadTexto = calcularAntiguedadTexto(laboral?.fecha_ingreso_empresa)

        // Puesto actual: el registro del historial que no tiene fecha_fin
        const puestoActual = (data.historial_puestos || []).find(h => !h.fecha_fin)
        const antiguedadPuestoAnios = puestoActual ? anosDesde(puestoActual.fecha_inicio) : ''

        // ==========================================
        // HELPERS DE MAPEO PARA COINCIDIR CON LOS <option value=""> DEL HIJO
        // ==========================================
        const mapGenero = (g) => g === 'Hombre' ? 'M' : (g === 'Mujer' ? 'F' : (g === 'Otro' ? 'Otro' : ''))

        const mapEstadoCivil = (ec) => {
          const val = (ec || '').toLowerCase().trim()
          if (val.includes('casad')) return 'casado'
          if (val.includes('solter')) return 'soltero'
          if (val.includes('divorci')) return 'divorciado'
          if (val.includes('viud')) return 'viudo'
          if (val.includes('union') || val.includes('unión')) return 'union_libre'
          return ''
        }

        const mapEscolaridad = (esc) => {
          const val = (esc || '').toLowerCase().trim()
          if (val.includes('universidad') || val.includes('licenciatura') || val.includes('profesional')) return 'profesional'
          if (val.includes('tecnico') || val.includes('técnico')) return 'tecnico'
          if (val.includes('secundaria')) return 'secundaria'
          if (val.includes('primaria')) return 'primaria'
          if (val.includes('posgrado') || val.includes('maestria') || val.includes('doctorado')) return 'posgrado'
          if (val.includes('ninguna')) return 'ninguna'
          return ''
        }

        const mapTipoContrato = (tc) => {
          const val = (tc || '').toLowerCase().trim()
          if (val.includes('indefinido') || val.includes('indeterminado') || val.includes('planta')) return 'indefinido'
          if (val.includes('temporal') || val.includes('fijo') || val.includes('determinado')) return 'temporal'
          if (val.includes('proyecto') || val.includes('obra')) return 'proyecto'
          if (val.includes('practicante') || val.includes('pasantia') || val.includes('becario')) return 'practicante'
          return ''
        }

        const mapJornada = (jor) => {
          const valores = ['matutino', 'vespertino', 'nocturno', 'mixto', 'rotativo']
          return valores.includes(jor) ? jor : ''
        }

        const mapModalidad = (mod) => {
          const val = (mod || '').toLowerCase().trim()
          if (val.includes('presencial') || val.includes('oficina')) return 'presencial'
          if (val.includes('remoto') || val.includes('home') || val.includes('casa')) return 'remoto'
          if (val.includes('hibrido') || val.includes('híbrido')) return 'hibrido'
          if (val.includes('turno') || val.includes('turnos')) return 'turnos'
          return ''
        }

        const mapCategoria = (cat) => {
          const val = (cat || '').toLowerCase().trim()
          if (val.includes('operativo') || val.includes('operario') || val.includes('obrero')) return 'operativo'
          if (val.includes('tecnico') || val.includes('técnico') || val.includes('tecnólogo')) return 'tecnico'
          if (val.includes('profesional') || val.includes('ingeniero') || val.includes('licenciado')) return 'profesional'
          if (val.includes('admin') || val.includes('oficina') || val.includes('staff')) return 'administrativo'
          if (val.includes('directivo') || val.includes('gerente') || val.includes('director') || val.includes('jefe')) return 'directivo'
          return ''
        }

        // ==========================================
        // MAPEO: HOJA 1 - Datos Puesto
        // ==========================================
        this.form.datos_puesto = {
          ...this.form.datos_puesto,
          folio: this.form.datos_puesto.folio,
          nombre: data.nombre || '',
          fecha_nacimiento: data.fecha_nacimiento ? data.fecha_nacimiento.split('T')[0] : '',
          edad: calcularEdad(data.fecha_nacimiento),

          // MAPEO PARA QUE COINCIDA CON LOS <option value=""> DEL HIJO
          genero: mapGenero(data.genero),
          estado_civil: mapEstadoCivil(data.estado_civil),
          escolaridad: mapEscolaridad(data.escolaridad),
          edad_inicio_vida_laboral: data.edad_inicio_vida_laboral ?? '',
          lugar_nacimiento: data.lugar_nacimiento || '',
          cedula: data.cedula_profesional || '',
          tipo_sanguineo: (data.tipo_sangre && data.rh) ? `${data.tipo_sangre}${data.rh}` : '',
          telefono: data.telefono || '',
          celular: data.celular || '',
          email: data.email || '',
          direccion: data.direccion || '',
          emergencia_nombre: data.contacto_emergencia || '',
          emergencia_telefono: data.telefono_emergencia || '',
          emergencia_relacion: data.relacion_contacto_emergencia || '',
          ultima_consulta_fecha: data.fecha_ultima_consulta_medica ? data.fecha_ultima_consulta_medica.split('T')[0] : '',
          ultima_consulta_motivo: data.motivo_ultima_consulta_medica || '',

          // Datos laborales con mapeo correcto
          numero_empleado: laboral?.numero_empleado || '',
          empresa: laboral?.empresa_cliente?.razon_social || '',
          departamento: laboral?.puesto_trabajo?.departamento || '',
          puesto: laboral?.puesto_trabajo?.nombre || '',
          antiguedad_empresa: antiguedadTexto,
          antiguedad: antiguedadPuestoAnios,
          tipo_contrato: mapTipoContrato(laboral?.tipo_contrato),
          jornada: mapJornada(laboral?.turno),
          modalidad_trabajo: mapModalidad(laboral?.modalidad_trabajo),
          categoria_laboral: mapCategoria(laboral?.categoria_laboral),
          descripcion_puesto: laboral?.puesto_trabajo?.descripcion_actividades || '',
          riesgos_identificados: laboral?.puesto_trabajo?.riesgos_adicionales || ''
        }

        // ==========================================
        // MAPEO: HOJA 2 - Antecedentes (Transformación de Strings a Estructura)
        // ==========================================

        // 1. ALERGIAS (Convertir strings separados por coma a array de objetos)
        const alergiasArray = []
        if (data.alergias && data.alergias.trim() !== 'Ninguno' && data.alergias.trim() !== '') {
          data.alergias.split(',').forEach(item => {
            if (item.trim()) {
              alergiasArray.push({ sustancia: item.trim(), tipo_reaccion: '', hospitalizacion: '' })
            }
          })
        }
        if (data.alergia_medicamentos && data.alergia_medicamentos.trim() !== 'Ninguno' && data.alergia_medicamentos.trim() !== '') {
          data.alergia_medicamentos.split(',').forEach(item => {
            if (item.trim()) {
              alergiasArray.push({ sustancia: `${item.trim()} (medicamento)`, tipo_reaccion: '', hospitalizacion: '' })
            }
          })
        }
        // Asignamos el array o dejamos uno vacío por defecto si no hay alergias
        this.form.antecedentes.alergias = alergiasArray.length > 0
          ? alergiasArray
          : [{ sustancia: '', tipo_reaccion: '', hospitalizacion: '' }]

        // 2. CIRUGÍAS / ACCIDENTES NO LABORALES
        if (data.antecedentes_quirurgicos && data.antecedentes_quirurgicos.trim() !== 'Ninguno' && data.antecedentes_quirurgicos.trim() !== '') {
          this.form.antecedentes.cirugias_accidentes_no_laborales = [{ descripcion: data.antecedentes_quirurgicos.trim() }]
        }

        // 3. ANTECEDENTES PATOLÓGICOS / ENFERMEDADES CRÓNICAS (Otros)
        const otrosPatologicos = []
        if (data.antecedentes_medicos && data.antecedentes_medicos.trim() !== 'Ninguno' && data.antecedentes_medicos.trim() !== '') {
          otrosPatologicos.push(data.antecedentes_medicos.trim())
        }
        if (data.enfermedades_cronicas && data.enfermedades_cronicas.trim() !== 'Ninguna' && data.enfermedades_cronicas.trim() !== '') {
          otrosPatologicos.push(`Enfermedades crónicas: ${data.enfermedades_cronicas.trim()}`)
        }

        if (otrosPatologicos.length > 0) {
          this.form.antecedentes.patologicos.otros = otrosPatologicos.join('. ')
        }

        this.trabajadorSeleccionado = {
          id: this.pacienteId,
          nombre: data.nombre,
          empresa: this.form.datos_puesto.empresa,
          numero_empleado: this.form.datos_puesto.numero_empleado,
          puesto: this.form.datos_puesto.puesto
        }

      } catch (error) {
        console.error('Error al obtener paciente:', error)
        Swal.fire({
          icon: 'error',
          title: 'Error al cargar el expediente',
          text: error.response?.data?.message || error.message
        })
      } finally {
        this.cargando = false
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
        const response = await clinicaTrabajo.post('/FichaMedicinaOcupacional-consultarIA', {
          mensaje: mensaje,
          paciente_id: this.trabajadorSeleccionado.id,
          contexto: this.form
        })

        this.conversacion.push({
          sender: 'ia',
          text: response.data.data.respuesta || 'No pude procesar tu solicitud.',
          timestamp: new Date()
        })

        if (response.data.data.campos_completados) {
          this.form.aptitud = { ...(this.form.aptitud || {}), ...response.data.data.campos_completados }
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
      
      if (this.sugeridosIA.length && !this.form.aptitud?.dictamen_confirmado) {
        Swal.fire({
          icon: 'warning',
          title: 'Dictamen sin confirmar',
          text: 'Revisa las sugerencias de la IA y marca "Confirmo que revisé el dictamen" antes de guardar.'
        })
        return
      }

      if (!this.trabajadorSeleccionado || !this.form.datos_puesto.folio) {
        Swal.fire({
          icon: 'warning',
          title: 'Datos incompletos',
          text: 'Selecciona un paciente antes de guardar la ficha.'
        })
        return
      }

      this.guardando = true
      try {
        const datosGuardar = {
          ...this.form,
          paciente_id: this.trabajadorSeleccionado.id,
          aptitud: this.form.aptitud?.aptitud || null,
          aptitud_hoja: this.form.aptitud,
          conversacion: this.conversacion
        }
        console.log('aptitud_hoja enviado:', datosGuardar.aptitud_hoja)
        console.log('datos_puesto.antiguedad_empresa:', datosGuardar.datos_puesto.antiguedad_empresa)
        const esActualizacion = !!this.fichaId

        if (esActualizacion) {
          await clinicaTrabajo.put(`/FichaMedicinaOcupacional/${this.fichaId}`, datosGuardar)
        } else {
          const { data } = await clinicaTrabajo.post('/FichaMedicinaOcupacional', datosGuardar)
          this.fichaId = data?.data?.id ?? null
        }

        const decision = await Swal.fire({
          icon: 'success',
          title: esActualizacion ? 'Ficha actualizada' : 'Ficha guardada',
          text: 'La ficha se guardó correctamente. ¿Deseas imprimir el reporte?',
          showCancelButton: true,
          confirmButtonText: 'Imprimir reporte',
          cancelButtonText: 'Continuar',
          confirmButtonColor: '#1E88E5',
          cancelButtonColor: '#6c757d'
        })

        // El clic en el modal cuenta como acción del usuario, así que el navegador no bloquea la pestaña
        if (decision.isConfirmed) {
          window.open(`/FichaMedicinaOcupacional-imprimir/${this.fichaId}`, '_blank')
        }

        window.location.href = esActualizacion
          ? `/ExpedientePacientesClinicaTrabajo/${this.pacienteId}`
          : '/buscador-pacientes-clinicaTrabajo'
      } catch (error) {
        console.error('Error guardando ficha:', error)
        const errores = error.response?.data?.errors
          ? Object.values(error.response.data.errors).flat().join('\n')
          : (error.response?.data?.message || error.message)

        await Swal.fire({
          icon: 'error',
          title: 'No se pudo guardar la ficha',
          text: errores
        })
      } finally {
        this.guardando = false
      }
    },

    firmaContextoIA() {
      return JSON.stringify({
        dp: this.form.datos_puesto,
        er: this.form.exposicion_riesgos,
        an: this.form.antecedentes,
        ce: this.form.clinico_examen,
      })
    },

    async proponerAutomatico() {
      if (!this.trabajadorSeleccionado) return
      if (this.cargandoIA) {
        this.recalcularIA = true
        return
      }

      const firma = this.firmaContextoIA()
      this.cargandoIA = true
      try {
        const { data } = await clinicaTrabajo.post('/FichaMedicinaOcupacional-proponer', { ...this.form })
        const propuesta = data.data.campos_completados || {}
        const nuevo = { ...(this.form.aptitud || {}) }

        Object.keys(propuesta).forEach(k => {
          if (this.editadosPorMedico[k] || !this.estaVacio(nuevo[k])) return   // el médico ya lo editó: no se toca
            nuevo[k] = propuesta[k]
            this.iaCampos[k] = true
        })

        // Cualquier propuesta nueva requiere confirmar otra vez
        nuevo.dictamen_confirmado = false

        // Lo que escribe la IA no cuenta como edición del médico
        this.form.aptitud = nuevo
        this.snapshotAptitud = JSON.parse(JSON.stringify(nuevo))
        this.ultimaFirmaIA = firma
        this.advertenciasIA = data.data.advertencias || []
      } catch (error) {
        console.error('Error en propuesta automática:', error)
        // Conserve aquí el Swal.fire del catch que ya tiene
      } finally {
        this.cargandoIA = false
        if (this.recalcularIA) {
          this.recalcularIA = false
          if (this.firmaContextoIA() !== this.ultimaFirmaIA) this.proponerAutomatico()
        }
      }
    },

    estaVacio(valor) {
      return valor === null || valor === undefined || valor === ''
    },

    async cargarValoracion(id) {
      this.cargando = true
      try {
        const { data } = await clinicaTrabajo.get(`/FichaMedicinaOcupacional/${id}/edit`)
        const registro = data.data

        if (registro.esta_firmada) {
          await Swal.fire({ icon: 'warning', title: 'Ficha firmada', text: 'Esta ficha ya no se puede modificar.' })
          window.location.href = '/buscador-pacientes-clinicaTrabajo'
          return
        }
        if (!registro.form) {
          throw new Error('El backend no devolvió la estructura del formulario. Falta completar edit().')
        }
        this.form = registro.form
        this.fichaId = id
        this.pacienteId = registro.paciente_id
        this.trabajadorSeleccionado = { id: registro.paciente_id, nombre: registro.paciente?.nombre || '' }
        this.snapshotAptitud = {}
        this.iaCampos = {}
        this.editadosPorMedico = {}
      } catch (error) {
        console.error('Error al cargar la ficha:', error)
        Swal.fire({ icon: 'error', title: 'Error al cargar la ficha', text: error.response?.data?.message || error.message })
      } finally {
        this.cargando = false
      }
    }
  },

  computed: {
    sugeridosIA() {
      return Object.keys(this.iaCampos).filter(k => this.iaCampos[k])
    },
  },

  mounted() {
    
    // EDICIÓN: si la URL trae ?valoracion={id}, carga la ficha guardada y termina aquí
    const valoracionParam = new URLSearchParams(window.location.search).get('valoracion')
    if (valoracionParam) {
      this.esEdicion = true
      this.cargarValoracion(Number(valoracionParam))
      return
    }

    this.obtenerFolioSugerido()
    // 1. OBTENER EL ID DE LA URL AUTOMÁTICAMENTE
    this.pacienteId = this.$route?.params?.id || window.location.pathname.split('/').pop()


    // 2. SI HAY UN ID VÁLIDO, CARGAMOS LOS DATOS DEL PACIENTE
    // La condición !isNaN asegura que no intente cargar si la URL termina en texto
    
    if (this.pacienteId && !isNaN(this.pacienteId) && this.pacienteId !== 'ocupacional') {
      this.obtenerPacientes()
    }
  },

  watch: {
    'form.aptitud': {
      deep: true,
      handler(nuevo) {
        const anterior = this.snapshotAptitud || {}

        // Primera sincronización (al montar el componente): no es edición del médico
        if (Object.keys(anterior).length === 0) {
          this.snapshotAptitud = JSON.parse(JSON.stringify(nuevo || {}))
          return
        }

        Object.keys(nuevo || {}).forEach(k => {
          if (JSON.stringify(nuevo[k]) === JSON.stringify(anterior[k])) return
          // Cualquier cambio del médico protege el campo de futuras propuestas
          this.editadosPorMedico[k] = true
          this.iaCampos[k] = false
          // Si el médico elige un código CIE-11, el término de la IA ya no es sugerencia
          if (k === 'cie11_codigo' || k === 'cie11_titulo') {
            this.editadosPorMedico.cie11_termino = true
            this.iaCampos.cie11_termino = false
          }
        })
        this.snapshotAptitud = JSON.parse(JSON.stringify(nuevo || {}))
      }
    },
    // Cuando el usuario entra a la hoja 4 (aptitud y dictamen), la IA propone sola
    hojaActual(nuevo) {
      if (nuevo === 4 && this.firmaContextoIA() !== this.ultimaFirmaIA) {
        this.proponerAutomatico()
      }
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

.card-tabs {
  scroll-margin-top: 70px; /* ajuste a la altura de su barra fija */
}
</style>