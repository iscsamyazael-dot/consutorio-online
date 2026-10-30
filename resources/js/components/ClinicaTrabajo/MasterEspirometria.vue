<template>
  <div class="master-espirometria">
    <!-- FORMULARIO (nueva o edición); el listado vive en su propia vista -->
    <div v-if="esNuevo || esEdicion" class="container-fluid p-0">

      <!-- 1. CHAT IA -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card card-primary card-outline shadow-sm">
            <div class="card-header bg-info text-white">
              <h3 class="card-title">
                <i class="fas fa-microphone-alt mr-2"></i>
                Asistente IA (Espirometría)
              </h3>
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
                    ¡Hola! Puedes dictarme los valores de la espirometría (ej: "FEV1 de 4.12 litros, FVC de 4.75"), subir el informe ndd en PDF o Excel, o indicar antecedentes como asma o tabaquismo.
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
                  <button class="btn btn-outline-secondary" type="button" @click="$refs.inputArchivo.click()" title="Adjuntar informe ndd">
                    <i class="fas fa-paperclip"></i>
                  </button>
                  <input ref="inputArchivo" type="file" hidden @change="seleccionarArchivo" accept=".pdf,.xls,.xlsx" />
                </div>

                <input
                  type="text"
                  class="form-control"
                  placeholder="Dicta valores, adjunta el informe ndd o escribe..."
                  v-model="mensajeActual"
                  @keyup.enter="enviarMensaje"
                />

                <div class="input-group-append">
                  <button class="btn btn-info text-white" type="button" @click="enviarMensaje" :disabled="enviandoIA || guardando">
                    <i class="fas fa-paper-plane"></i> {{ enviandoIA ? 'Procesando...' : 'Enviar' }}
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

      <!-- 2. ENCABEZADO Y TABS -->
      <div class="card mb-3 shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
          <h4 class="mb-0">
            <i class="fas fa-lungs mr-2"></i> Reporte de Espirometría
            <span class="badge badge-light text-primary ml-2">{{ folioMostrado }}</span>
          </h4>
          <select v-model="tipoEvaluacion" class="form-control form-control-sm" style="width: 200px;">
            <option value="ingreso">Ingreso</option>
            <option value="periodica">Periódica</option>
            <option value="seguimiento">Seguimiento</option>
            <option value="extraordinaria">Extraordinaria</option>
          </select>
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

      <!-- 3. CONTENIDO DE LAS PESTAÑAS -->
      <div class="tab-content bg-white p-4 rounded-bottom shadow-sm mb-4">

        <!-- HOJA 1: Datos generales -->
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
            @importar-interpretacion="alImportarInterpretacion($event)"
          ></parametros-fvl-espirometria>
        </div>

        <!-- HOJA 3: Interpretación -->
        <div id="hoja3" class="tab-pane fade" role="tabpanel">
          <interpretacion-espirometria
            :modelValue="form.interpretacion"
            :parametros="form.parametrosFVL"
            :sugeridos="camposSugeridos"
            :aptitudSugerida="aptitudSugerida"
            :pendiente="dictamenPendiente"
            :confirmado="dictamenConfirmado"
            @confirmar-dictamen="confirmarDictamen"
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
          <button v-if="valoracionId" class="btn btn-info text-white" @click="imprimir" :disabled="guardando">
            <i class="fas fa-print mr-2"></i> Imprimir Reporte
          </button>
          <button class="btn btn-outline-info" @click="proponerDictamen" :disabled="enviandoIA || guardando">
            <i class="fas fa-robot mr-2"></i> Proponer dictamen con IA
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
import apiClient from '@/services/ApiServiceClinicaTrabajo'
import Swal from 'sweetalert2'
import DatosGeneralesEspirometria from './secciones/espirometria/DatosGeneralesEspirometria.vue'
import ParametrosFvlEspirometria from './secciones/espirometria/ParametrosFvlEspirometria.vue'
import InterpretacionEspirometria from './secciones/espirometria/InterpretacionEspirometria.vue'

//Declaracion de las variables para SweetAlert//
const escapar = (texto) => String(texto ?? '')
  .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')

const notificar = (icon, title, texto = '') => Swal.fire({
  icon,
  title,
  html: escapar(texto).replace(/\n/g, '<br>'),
  confirmButtonText: 'Entendido',
  confirmButtonColor: '#1E88E5'
})

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

// ---------- Campos (nombres del formulario) ----------

const CAMPOS_DATOS = [
  'fecha_nacimiento','fecha_prueba','id_empleado', 'empresa', 'remitido_por', 'prescrito_por', 'edad', 'sexo',
  'origen_etnico', 'altura_cm', 'peso_kg', 'peso_ideal_kg', 'fumador', 'cigarrillos_dia',
  'anos_fumador', 'asma', 'epoc', 'antecedente_covid', 'rinitis_alergica', 'tuberculosis',
  'neumonia_previa', 'comentarios'
]

const CAMPOS_BOOLEANOS = [
  'fumador', 'asma', 'epoc', 'antecedente_covid', 'rinitis_alergica', 'tuberculosis', 'neumonia_previa'
]

const CAMPOS_INTERPRETACION = [
  'calidad_sesion', 'interpretacion_sistema', 'interpretacion_equipo', 'diagnostico_medico',
  'aptitud', 'recomendaciones', 'edad_pulmonar', 'seguimiento_requerido', 'plazo_proximo_seguimiento'
]

// Campos que tienen el mismo nombre en el formulario y en la tabla
const CAMPOS_PARAMETROS_DIRECTOS = [
  'fvc_pred', 'fvc_lln', 'fev1_pred', 'fev1_lln', 'fev1_fvc_pred', 'fev1_fvc_lln',
  'fef25_75_pred', 'fef25_75_lln', 'pef_pred', 'pef_lln', 'fivc_pred', 'fivc_lln',
  'pif_pred', 'pif_lln', 'eotv_pred', 'eotv_lln', 'bev_pred', 'bev_lln',
  'fev1_var_l', 'fvc_var_l', 'calidad_fev1', 'calidad_fvc',
  'fvc_porcentaje_predicho', 'fev1_porcentaje_predicho', 'fev1_fvc_porcentaje_predicho',
  'fef25_75_porcentaje_predicho', 'pef_porcentaje_predicho', 'fivc_porcentaje_predicho',
  'pif_porcentaje_predicho', 'fvc_z', 'fev1_z', 'fev1_fvc_z', 'fef25_75_z', 'pef_z', 'fivc_z'
]

// Mejor de cada parámetro: en el formulario "mejor_*", en la tabla la columna del valor
const MEJOR_A_COLUMNA = {
  mejor_fvc: 'fvc_l',
  mejor_fev1: 'fev1_l',
  mejor_fev1_fvc: 'fev1_fvc_ratio',
  mejor_fef25_75: 'fef25_75',
  mejor_pef: 'pef_l_s',
  mejor_fet: 'fet_s',
  mejor_fivc: 'fivc_l',
  mejor_pif: 'pif_l_s',
  mejor_eotv: 'eotv_l',
  mejor_bev: 'bev_l'
}

function calcularEdad(fechaStr) {
  if (!fechaStr) return null
  const hoy = new Date()
  const nacimiento = new Date(fechaStr)
  let edad = hoy.getFullYear() - nacimiento.getFullYear()
  const mes = hoy.getMonth() - nacimiento.getMonth()
  if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) edad--
  return edad
}

function formularioVacio() {
  const parametros = {
    referencia: 'NHANES III',
    interpretacion_predicha: 'GOLD(2008)/Hardie',
    maniobras: []
  }
  CAMPOS_PARAMETROS_DIRECTOS.forEach(c => { parametros[c] = null })
  Object.keys(MEJOR_A_COLUMNA).forEach(c => { parametros[c] = null })

  const datosGenerales = {
    nombre: '',
    empresa_cliente_id: null,
    origen_etnico: 'Hispano',
    sexo: '',
    fecha_prueba: '',
    fumador: false, asma: false, epoc: false, antecedente_covid: false,
    rinitis_alergica: false, tuberculosis: false, neumonia_previa: false,
    cigarrillos_dia: 0, anos_fumador: 0,
    remitido_por: '', prescrito_por: '', comentarios: '', id_empleado: '', empresa: '',
    fecha_nacimiento: '', edad: null, altura_cm: null, peso_kg: null, peso_ideal_kg: null
  }

  const interpretacion = {
    calidad_sesion: '', interpretacion_sistema: '', interpretacion_equipo: '',
    diagnostico_medico: '', aptitud: '', recomendaciones: '', edad_pulmonar: null,
    seguimiento_requerido: false, plazo_proximo_seguimiento: ''
  }

  return { datosGenerales, parametrosFVL: parametros, interpretacion }
}

export default {
  name: 'MasterEspirometria',
  components: {
    DatosGeneralesEspirometria,
    ParametrosFvlEspirometria,
    InterpretacionEspirometria
  },

  data() {
    return {
      esNuevo: false,
      esEdicion: false,
      guardando: false,
      enviandoIA: false,
      cargando: false,
      mensajeActual: '',
      archivoSeleccionado: null,
      conversacion: [],
      sessionUuid: null,

      valoracionId: null,
      pacienteId: null,
      folio: '',
      tipoEvaluacion: 'ingreso',

      // Campos del dictamen llenados por la IA y aún no editados por el médico
      camposSugeridos: [],
      valoresSugeridos: {},
      aptitudSugerida: '',
      dictamenPendiente: false,
      dictamenConfirmado: false,

      form: formularioVacio()
    }
  },

  computed: {
    folioMostrado() {
      if (this.folio) return this.folio
      return this.esNuevo ? 'Nuevo' : ''
    }
  },

  methods: {
    // ---------- Rutas ----------
    // /ficha-espirometria/{pacienteId}                 -> nueva valoración de ese paciente
    // /ficha-espirometria/{pacienteId}?valoracion={id} -> edición de esa valoración
    detectarRuta() {
      const ruta = window.location.pathname.match(/\/ficha-espirometria\/(\d+)\/?$/)
      if (!ruta) {
        this.esNuevo = false
        this.esEdicion = false
        return
      }

      const valoracion = new URLSearchParams(window.location.search).get('valoracion')

      if (valoracion) {
        this.esNuevo = false
        this.esEdicion = true
        this.valoracionId = Number(valoracion)
        this.cargarValoracion(this.valoracionId)
      } else {
        this.esNuevo = true
        this.esEdicion = false
        this.pacienteId = Number(ruta[1])
        this.cargarPaciente(this.pacienteId)
      }
    },

    // ---------- Carga de datos ----------
    async cargarPaciente(id) {
      this.cargando = true
      try {
        const { data } = await apiClient.get(`/PacientesDetalleClinicaTrabajo/${id}`)
        const paciente = data.data
        const laboral = (paciente.datos_laborales || []).slice(-1)[0] || null

        this.form.datosGenerales = {
          ...this.form.datosGenerales,
          nombre: paciente.nombre || '',
          fecha_nacimiento: paciente.fecha_nacimiento ? paciente.fecha_nacimiento.split('T')[0] : '',
          edad: calcularEdad(paciente.fecha_nacimiento),
          sexo: paciente.genero === 'Hombre' ? 'Masculino' : (paciente.genero === 'Mujer' ? 'Femenino' : ''),
          id_empleado: laboral?.numero_empleado || '',
          empresa: laboral?.empresa_cliente?.razon_social || '',
          empresa_cliente_id: laboral?.empresa_cliente_id ?? null,
          // Sugerido: la empresa del paciente. El médico puede cambiarlo.
          remitido_por: this.form.datosGenerales.remitido_por || laboral?.empresa_cliente?.razon_social || '',
          prescrito_por: this.form.datosGenerales.prescrito_por || ''
        }
      } catch (error) {
        console.error('Error al cargar el paciente:', error)
        await notificar('error', 'Error al cargar el expediente', error.response?.data?.message || error.message)
        window.location.href = '/ficha-espirometria'
      } finally {
        this.cargando = false
      }
    },

    async cargarValoracion(id) {
      this.cargando = true
      try {
        const { data } = await apiClient.get(`/espirometria/${id}`)
        const registro = data.data

        this.folio = registro.folio
        this.pacienteId = registro.paciente_id
        this.tipoEvaluacion = registro.tipo_evaluacion
        this.form = this.desdeRegistro(registro)
        this.camposSugeridos = []
        this.aptitudSugerida = ''
        this.dictamenPendiente = false
        this.dictamenConfirmado = false
      } catch (error) {
        console.error('Error al cargar la valoración:', error)
        await notificar('error', 'Error al cargar la valoración', error.response?.data?.message || error.message)
        window.location.href = '/ficha-espirometria'
      } finally {
        this.cargando = false
      }
    },

    // Registro de la BD -> estado del formulario
    desdeRegistro(r) {
      const f = formularioVacio()

      const datosGenerales = { ...f.datosGenerales, nombre: r.nombre || '', empresa_cliente_id: r.empresa_cliente_id }
      CAMPOS_DATOS.forEach(c => {
        datosGenerales[c] = r[c] ?? datosGenerales[c]
      })
      CAMPOS_BOOLEANOS.forEach(c => { datosGenerales[c] = !!r[c] })

      const parametros = { ...f.parametrosFVL, referencia: r.referencia || f.parametrosFVL.referencia,
        interpretacion_predicha: r.interpretacion_predicha || f.parametrosFVL.interpretacion_predicha,
        maniobras: r.maniobras ?? [] }
      CAMPOS_PARAMETROS_DIRECTOS.forEach(c => { parametros[c] = r[c] ?? null })
      Object.entries(MEJOR_A_COLUMNA).forEach(([mejor, columna]) => { parametros[mejor] = r[columna] ?? null })

      const interpretacion = { ...f.interpretacion }
      CAMPOS_INTERPRETACION.forEach(c => { interpretacion[c] = r[c] ?? interpretacion[c] })
      interpretacion.seguimiento_requerido = !!r.seguimiento_requerido

      return { datosGenerales, parametrosFVL: parametros, interpretacion }
    },

    // ---------- Payload (estado del formulario -> columnas de la tabla) ----------
    construirPayload() {
      const d = this.form.datosGenerales
      const p = this.form.parametrosFVL
      const i = this.form.interpretacion

      const payload = {
        paciente_id: this.pacienteId,
        empresa_cliente_id: d.empresa_cliente_id,
        tipo_evaluacion: this.tipoEvaluacion,
        fecha_valoracion: new Date().toLocaleDateString('en-CA'),
        nombre: d.nombre,
        referencia: p.referencia,
        interpretacion_predicha: p.interpretacion_predicha,
        maniobras: p.maniobras ?? []
      }

      CAMPOS_DATOS.forEach(c => { payload[c] = d[c] ?? null })
      payload.fecha_prueba = d.fecha_prueba || null
      CAMPOS_BOOLEANOS.forEach(c => { payload[c] = !!d[c] })
      CAMPOS_PARAMETROS_DIRECTOS.forEach(c => { payload[c] = p[c] ?? null })
      Object.entries(MEJOR_A_COLUMNA).forEach(([mejor, columna]) => { payload[columna] = p[mejor] ?? null })
      CAMPOS_INTERPRETACION.forEach(c => { payload[c] = i[c] ?? null })
      payload.seguimiento_requerido = !!i.seguimiento_requerido

      return payload
    },

    contexto() {
      return {
        datosGenerales: this.form.datosGenerales,
        parametrosFVL: this.form.parametrosFVL,
        interpretacion: this.form.interpretacion
      }
    },

    // ---------- Chat con la IA ----------
    async enviarMensaje() {
      const texto = this.mensajeActual.trim()
      if (!texto && !this.archivoSeleccionado) return

      this.conversacion.push({
        sender: 'user',
        text: this.archivoSeleccionado ? `[Archivo: ${this.archivoSeleccionado.name}] ${texto}` : texto
      })
      this.mensajeActual = ''
      this.scrollearChat()

      const formData = new FormData()
      formData.append('mensaje', texto || 'Adjunto el informe de espirometría.')
      formData.append('contexto_actual', JSON.stringify(this.contexto()))
      if (this.sessionUuid) formData.append('session_uuid', this.sessionUuid)
      if (this.pacienteId) formData.append('paciente_id', this.pacienteId)
      if (this.valoracionId) formData.append('valoracion_id', this.valoracionId)
      if (this.archivoSeleccionado) formData.append('archivo', this.archivoSeleccionado)

      this.enviandoIA = true
      try {
        const { data } = await apiClient.post('/espirometria-consultarIA', formData)

        this.sessionUuid = data.session_uuid
        this.conversacion.push({ sender: 'ia', text: data.respuesta || 'Listo.' })

        if (data.advertencias?.length) {
          this.conversacion.push({ sender: 'ia', text: '⚠️ ' + data.advertencias.join(' ') })
        }

        if (data.campos_completados) {
          this.fusionarDatosIA(data.campos_completados)
        }

        this.quitarArchivo()
      } catch (error) {
        console.error('Error en el chat con la IA:', error)
        const mensaje = error.response?.data?.message || 'Hubo un error al procesar tu solicitud.'
        this.conversacion.push({ sender: 'ia', text: '❌ ' + mensaje })
      } finally {
        this.enviandoIA = false
        this.scrollearChat()
      }
    },

    // Mezcla lo que propuso la IA o el importador con el formulario.
    // Las secciones de datos y parámetros se reemplazan; el dictamen solo se completa.
    fusionarDatosIA(nuevosDatos) {
      for (const seccion in nuevosDatos) {
        if (!this.form[seccion]) continue

        if (seccion === 'interpretacion') {
          this.completarDictamen(nuevosDatos.interpretacion)
        } else {
          this.form[seccion] = { ...this.form[seccion], ...nuevosDatos[seccion] }
        }
      }
    },

    // Solo llena campos vacíos del dictamen; lo que el médico ya escribió no se toca
    completarDictamen(propuestas = {}) {
      const { aptitud, ...resto } = propuestas
      const actual = this.form.interpretacion
      const nuevos = {}

      for (const campo in resto) {
        if (this.estaVacio(actual[campo])) {
          nuevos[campo] = resto[campo]
        }
      }

      // La aptitud nunca se preselecciona: solo se muestra como sugerencia
      if (aptitud && this.estaVacio(actual.aptitud)) {
        this.aptitudSugerida = aptitud
      }

      if (Object.keys(nuevos).length > 0) {
        this.valoresSugeridos = { ...this.valoresSugeridos, ...nuevos }
        this.camposSugeridos = [...new Set([...this.camposSugeridos, ...Object.keys(nuevos)])]
        this.form.interpretacion = { ...actual, ...nuevos }
      }

      if (Object.keys(nuevos).length > 0 || this.aptitudSugerida) {
        this.dictamenPendiente = true
        this.dictamenConfirmado = false
      }
    },

    // El médico confirma que revisó el dictamen: se quitan las marcas y se libera el guardado
    confirmarDictamen(valor) {
      this.dictamenConfirmado = valor
      this.dictamenPendiente = !valor
      if (valor) {
        this.camposSugeridos = []
        this.valoresSugeridos = {}
      }
    },

    estaVacio(valor) {
      return valor === null || valor === undefined || valor === ''
    },

    // Tras importar el informe, la IA propone el dictamen de forma automática
    alImportarInterpretacion(interpretacion) {
      this.fusionarDatosIA({ interpretacion })
      this.$nextTick(() => this.solicitarDictamen())
    },

    // Pide a la IA el borrador del dictamen con base en los parámetros y maniobras capturados
    proponerDictamen() {
      return this.solicitarDictamen()
    },

    async solicitarDictamen() {
      if (this.enviandoIA) return

      const formData = new FormData()
      formData.append('mensaje', 'Propuesta de dictamen')
      formData.append('modo', 'dictamen')
      formData.append('contexto_actual', JSON.stringify(this.contexto()))
      if (this.sessionUuid) formData.append('session_uuid', this.sessionUuid)
      if (this.pacienteId) formData.append('paciente_id', this.pacienteId)
      if (this.valoracionId) formData.append('valoracion_id', this.valoracionId)

      this.enviandoIA = true
      try {
        const { data } = await apiClient.post('/espirometria-consultarIA', formData)

        this.sessionUuid = data.session_uuid
        this.conversacion.push({ sender: 'ia', text: data.respuesta || 'Propuesta lista.' })
        // Solo llena campos vacíos del dictamen; lo que el médico ya escribió no se toca
        this.completarDictamen(data.campos_completados?.interpretacion || {})
      } catch (error) {
        console.error('Error al proponer el dictamen:', error)
        this.conversacion.push({ sender: 'ia', text: '❌ No se pudo generar la propuesta de dictamen.' })
      } finally {
        this.enviandoIA = false
        this.scrollearChat()
      }
    },

    seleccionarArchivo(event) {
      this.archivoSeleccionado = event.target.files[0] || null
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

    // ---------- Guardar ----------
    async guardar() {
      if (!this.pacienteId) {
        return notificar('warning', 'Falta el paciente', 'Abre la valoración desde el expediente del paciente.')
      }
      if (!this.form.datosGenerales.nombre) {
        return notificar('warning', 'Falta el nombre', 'Ingresa el nombre del paciente en la Hoja 1.')
      }
      if (!this.form.datosGenerales.empresa_cliente_id) {
        return notificar('warning', 'Sin empresa', 'El paciente no tiene empresa asignada en sus datos laborales.')
      }
      if (!this.form.interpretacion.aptitud) {
        return notificar('warning', 'Falta la aptitud', 'Selecciona la aptitud en la Hoja 3 para poder guardar.')
      }
      if (this.dictamenPendiente) {
        return notificar('warning', 'Dictamen sin confirmar', 'Revisa el dictamen sugerido por la IA y marca "Confirmo" en la Hoja 3 antes de guardar.')
      }

      this.guardando = true
      try {
        const payload = this.construirPayload()

        const respuesta = this.valoracionId
          ? await apiClient.put(`/espirometria/${this.valoracionId}`, payload)
          : await apiClient.post('/espirometria', { ...payload, session_uuid: this.sessionUuid })

        if (respuesta.data.success) {
          const idGuardado = respuesta.data.id

          // Después de guardar, el médico decide si imprime ahora
          const decision = await Swal.fire({
            icon: 'success',
            title: 'Reporte guardado',
            text: 'La valoración se guardó correctamente. ¿Deseas imprimir el reporte?',
            showCancelButton: true,
            confirmButtonText: 'Imprimir reporte',
            cancelButtonText: 'Continuar',
            confirmButtonColor: '#1E88E5',
            cancelButtonColor: '#6c757d'
          })

          // El clic en el modal cuenta como acción del usuario, así que el navegador no bloquea la pestaña
          if (decision.isConfirmed) {
            window.open(`/espirometria-imprimir/${idGuardado}`, '_blank')
          }

          window.location.href = `/ficha-espirometria/${this.pacienteId}?valoracion=${idGuardado}`
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

    async cancelar() {
      const ok = await confirmarAccion({
        title: '¿Cancelar la evaluación?',
        texto: 'Se perderán los datos no guardados.',
        confirmText: 'Sí, cancelar',
        cancelText: 'Seguir editando'
      })
      if (ok) window.location.href = '/ficha-espirometria'
    },

    imprimir() {
      if (!this.valoracionId) {
        return notificar('warning', 'Guarda primero', 'El reporte se genera a partir de una valoración guardada.')
      }
      window.open(`/espirometria-imprimir/${this.valoracionId}`, '_blank')
    },
  },

  watch: {
    // Quita la marca "Sugerido por IA" cuando el médico cambia el valor propuesto
    'form.interpretacion': {
      deep: true,
      handler(nuevo) {
        this.camposSugeridos = this.camposSugeridos.filter(
          campo => nuevo[campo] === this.valoresSugeridos[campo]
        )
      }
    }
  },

  mounted() {
    this.detectarRuta()
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