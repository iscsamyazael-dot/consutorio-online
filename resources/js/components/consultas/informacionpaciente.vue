<template>
  <div class="col-lg-8">
    <div class="form-scroll">
      <form @submit.prevent="guardarConsulta">
        <!-- CARD PACIENTE -->
        <div class="card main-card border-0 mb-4 animated-section">
          <div class="card-body p-5">
            <div class="mb-5">
              <h4 class="section-title">
                <i class="fas fa-user text-primary"></i>
                Información del Paciente
              </h4>
            </div>
            <div class="row g-4">
              <div class="col-md-6">
                <label class="form-label">Paciente</label>
                <div class="input-icon-box">
                  <i class="fas fa-user"></i>
                  <input
                    type="text"
                    class="form-control custom-input with-icon"
                    placeholder="Nombre del paciente"
                    v-model="infoPacientes.nombre_completo"
                  >
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Doctor</label>
                <div class="input-icon-box">
                  <i class="fas fa-user-md"></i>
                  <input
                    type="text"
                    class="form-control custom-input with-icon"
                    placeholder="Doctor responsable"
                    v-model="infoPacientes.doctor"
                  >
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Fecha</label>
                <div class="input-icon-box">
                  <i class="fas fa-calendar-alt"></i>
                  <input
                    type="date"
                    class="form-control custom-input with-icon"
                    :class="{ 'is-invalid': errores.fecha }"
                    v-model="form.fecha"
                  >
                </div>
                <div class="invalid-feedback" v-if="errores.fecha">
                  {{ errores.fecha }}
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Hora</label>
                <div class="input-icon-box">
                  <i class="fas fa-clock"></i>
                  <input
                    type="time"
                    class="form-control custom-input with-icon"
                    :class="{ 'is-invalid': errores.hora }"
                    v-model="form.hora"
                  >
                </div>
                <div class="invalid-feedback" v-if="errores.hora">
                  {{ errores.hora }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- CARD DATOS DE LA CONSULTA -->
        <div class="card main-card border-0 mb-4 animated-section delay-1">
          <div class="card-body p-5">
            <div class="mb-5">
              <h4 class="section-title">
                <i class="fas fa-clipboard-list text-primary"></i>
                Datos de la Consulta
              </h4>
            </div>
            <div class="row g-4">
              <div class="col-md-6">
                <label class="form-label">Motivo de consulta</label>
                <div class="input-icon-box">
                  <i class="fas fa-comment-medical"></i>
                  <input
                    type="text"
                    class="form-control custom-input with-icon"
                    :class="{ 'is-invalid': errores.motivo }"
                    placeholder="Ingrese motivo"
                    v-model="form.motivo"
                  >
                </div>
                <div class="invalid-feedback" v-if="errores.motivo">
                  {{ errores.motivo }}
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Diagnóstico</label>
                <div class="input-icon-box">
                  <i class="fas fa-stethoscope"></i>
                  <input
                    type="text"
                    class="form-control custom-input with-icon"
                    :class="{ 'is-invalid': errores.diagnostico }"
                    placeholder="Ingrese diagnóstico"
                    v-model="form.diagnostico"
                  >
                </div>
                <div class="invalid-feedback" v-if="errores.diagnostico">
                  {{ errores.diagnostico }}
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Peso (kg)</label>
                <div class="input-icon-box">
                  <i class="fas fa-weight"></i>
                  <input
                    type="number"
                    step="0.1"
                    min="0"
                    class="form-control custom-input with-icon"
                    :class="{ 'is-invalid': errores.peso }"
                    placeholder="Ej. 70"
                    v-model="form.peso"
                  >
                </div>
                <div class="invalid-feedback" v-if="errores.peso">
                  {{ errores.peso }}
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Presión arterial</label>
                <div class="input-icon-box">
                  <i class="fas fa-heartbeat"></i>
                  <input
                    type="text"
                    class="form-control custom-input with-icon"
                    :class="{ 'is-invalid': errores.presion }"
                    placeholder="120/80"
                    v-model="form.presion"
                  >
                </div>
                <div class="invalid-feedback" v-if="errores.presion">
                  {{ errores.presion }}
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Saturación</label>
                <div class="input-icon-box">
                  <i class="fas fa-lungs"></i>
                  <input
                    type="text"
                    class="form-control custom-input with-icon"
                    :class="{ 'is-invalid': errores.saturacion }"
                    placeholder="98%"
                    v-model="form.saturacion"
                  >
                </div>
                <div class="invalid-feedback" v-if="errores.saturacion">
                  {{ errores.saturacion }}
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Temperatura</label>
                <div class="input-icon-box">
                  <i class="fas fa-thermometer-half"></i>
                  <input
                    type="text"
                    class="form-control custom-input with-icon"
                    :class="{ 'is-invalid': errores.temperatura }"
                    placeholder="36.5 °C"
                    v-model="form.temperatura"
                  >
                </div>
                <div class="invalid-feedback" v-if="errores.temperatura">
                  {{ errores.temperatura }}
                </div>
              </div>

              <div class="col-12">
                <label class="form-label">Observaciones</label>
                <div class="textarea-icon-box">
                  <i class="fas fa-edit"></i>
                  <textarea
                    class="form-control custom-textarea with-icon"
                    rows="4"
                    placeholder="Observaciones médicas..."
                    v-model="form.observaciones"
                  ></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- CARD EVALUACION CLINICA -->
        <div class="card main-card border-0 animated-section delay-2">
          <div class="card-body p-5">
            <div class="mb-5">
              <h4 class="section-title">
                <i class="fas fa-file-medical text-danger"></i>
                Evaluación Clínica
              </h4>
            </div>

            <!-- SINTOMAS -->
            <div class="mb-4">
              <label class="form-label">Síntomas</label>
              <div class="textarea-icon-box">
                <i class="fas fa-notes-medical"></i>
                <textarea
                  class="form-control custom-textarea with-icon"
                  :class="{ 'is-invalid': errores.sintomas }"
                  rows="4"
                  placeholder="Ingrese síntomas"
                  v-model="form.sintomas"
                ></textarea>
              </div>
              <div class="invalid-feedback" v-if="errores.sintomas">
                {{ errores.sintomas }}
              </div>
            </div>

            <!-- BOTONES -->
            <div class="d-flex justify-content-end gap-3 mt-5 animated-section delay-3">
              <button
                type="button"
                class="btn btn-cancel btn-lg rounded-pill px-4"
                @click="cancelarConsulta"
                :disabled="guardando"
              >
                Cancelar
              </button>
              <button
                type="submit"
                class="btn btn-primary btn-lg rounded-pill px-5 save-btn"
                :disabled="guardando"
              >
                <i class="fas fa-file-medical me-1"></i>
                {{ guardando ? 'Generando expediente...' : 'Generar expediente' }}
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import ApiService from '../../services/ApiService.js'
import { jsPDF } from 'jspdf'
import Swal from 'sweetalert2'

export default {
  name: 'ConsultaForm',
    props: {
        pacienteId: {
            type: [Number, String],
            required: true
        },
        doctorNombre: {
            type: String,
            default: ''
        },
        doctorCedula: {
            type: String,
            default: ''
        }
    },
  data() {
    return {
      infoPacientes: {},
      guardando: false,
      form: {
        fecha: '',
        hora: '',
        motivo: '',
        diagnostico: '',
        peso: '',
        presion: '',
        saturacion: '',
        temperatura: '',
        observaciones: '',
        sintomas: ''
      },
      errores: {
        fecha: '',
        hora: '',
        motivo: '',
        diagnostico: '',
        peso: '',
        presion: '',
        saturacion: '',
        temperatura: '',
        sintomas: ''
      }
    }
  },
  mounted() {
    console.log('PROP PacienteId:', this.pacienteId)
  },
  methods: {

   


    async obtenerPacientes() {
      try {
        const response = await ApiService.get('/ExpedienteDetalle/' + this.pacienteId)
        this.infoPacientes = response.data
        console.log('Pacientes cargados:', this.infoPacientes)
        this.precargarDatosPaciente()
      } catch (error) {
        console.error('Error al obtener pacientes:', error)
      }
    },

    precargarDatosPaciente() {
      const p = this.infoPacientes
      if (!p) return

      p.nombre_completo = [p.nombre, p.apellido_paterno, p.apellido_materno]
        .filter(Boolean)
        .join(' ')

      const ultimoTriage = (p.triages && p.triages.length)
        ? p.triages[p.triages.length - 1]
        : null

      const ahora = new Date()
      const fechaHoy = ahora.toISOString().split('T')[0]
      const horaAhora = ahora.toTimeString().slice(0, 5)

      this.form = {
        fecha: fechaHoy,
        hora: horaAhora,
        motivo: ultimoTriage?.motivo_consulta || '',
        diagnostico: '',
        peso: ultimoTriage?.peso || '',
        presion: ultimoTriage?.presion || '',
        saturacion: ultimoTriage?.saturacion || '',
        temperatura: ultimoTriage?.temperatura || '',
        observaciones: '',
        sintomas: ultimoTriage?.sintomas || ''
      }
    },

    validarFormulario() {
      this.errores = {
        fecha: '',
        hora: '',
        motivo: '',
        diagnostico: '',
        peso: '',
        presion: '',
        saturacion: '',
        temperatura: '',
        sintomas: ''
      }
      let valido = true

      if (!this.form.fecha) {
        this.errores.fecha = 'La fecha es obligatoria.'
        valido = false
      }
      if (!this.form.hora) {
        this.errores.hora = 'La hora es obligatoria.'
        valido = false
      }
      if (!this.form.motivo.trim()) {
        this.errores.motivo = 'El motivo de consulta es obligatorio.'
        valido = false
      }
      if (!this.form.diagnostico.trim()) {
        this.errores.diagnostico = 'El diagnóstico es obligatorio.'
        valido = false
      }
      if (this.form.peso && Number(this.form.peso) <= 0) {
        this.errores.peso = 'El peso debe ser mayor a 0.'
        valido = false
      }
      if (this.form.presion && !/^\d{2,3}\/\d{2,3}$/.test(this.form.presion.trim())) {
        this.errores.presion = 'Formato esperado: 120/80.'
        valido = false
      }
      if (!this.form.sintomas.trim()) {
        this.errores.sintomas = 'Describa los síntomas del paciente.'
        valido = false
      }

      return valido
    },

    limpiarFormulario() {
      this.form = {
        fecha: '',
        hora: '',
        motivo: '',
        diagnostico: '',
        peso: '',
        presion: '',
        saturacion: '',
        temperatura: '',
        observaciones: '',
        sintomas: ''
      }
    },

    async guardarConsulta() {
        if (!this.validarFormulario()) return

        this.guardando = true
        try {
            // 1. Obtener el nombre completo del paciente
            const nombreCompleto = this.infoPacientes.nombre_completo || 
            [this.infoPacientes.nombre, this.infoPacientes.apellido_paterno, this.infoPacientes.apellido_materno]
                .filter(Boolean)
                .join(' ')

            // 2. Extraer el teléfono con fallback por si no viniera definido
            const telefonoPaciente = this.infoPacientes.telefono || this.infoPacientes.celular || '0000000000'

            // 3. Estructurar el payload exactamente como Laravel lo solicita
            const payload = {
            // Requeridos por las reglas de validación en el backend:
            nombre_paciente: nombreCompleto,
            telefono: telefonoPaciente,
            paciente_id: this.pacienteId,

            // Datos específicos de la consulta:
            fecha: this.form.fecha,
            hora: this.form.hora,
            motivo: this.form.motivo,
            diagnostico: this.form.diagnostico,
            sintomas: this.form.sintomas,
            peso: this.form.peso || null,
            presion: this.form.presion || null,
            saturacion: this.form.saturacion || null,
            temperatura: this.form.temperatura || null,
            observaciones: this.form.observaciones || null
            }

            const response = await ApiService.post('/consultas', payload)
            console.log('Consulta guardada:', response.data)

            // Generar PDF y mostrar notificación
            await this.generarPDF()

            Swal.fire({
            icon: 'success',
            title: 'Expediente generado',
            text: 'La consulta fue guardada y el PDF se descargó exitosamente.',
            confirmButtonText: 'Aceptar'
            })

            this.limpiarFormulario()
        } catch (error) {
            console.error('Error al guardar consulta:', error)
            
            if (error.response && error.response.status === 422) {
            console.log('Campos rechazados por Laravel:', error.response.data.errors)
            }

            Swal.fire({
            icon: 'error',
            title: 'Error de validación',
            text: 'Verifica los campos obligatorios del formulario.',
            confirmButtonText: 'Aceptar'
            })
        } finally {
            this.guardando = false
        }
    },

    cancelarConsulta() {
      this.limpiarFormulario()
      this.errores = {
        fecha: '',
        hora: '',
        motivo: '',
        diagnostico: '',
        peso: '',
        presion: '',
        saturacion: '',
        temperatura: '',
        sintomas: ''
      }
      this.$emit('cancelar')
    },

    // Función auxiliar para cargar imagen de manera síncrona/promesificada
    cargarImagen(url) {
      return new Promise((resolve, reject) => {
        const img = new Image()
        img.onload = () => resolve(img)
        img.onerror = (err) => reject(err)
        img.src = url
      })
    },

    async generarPDF() {
        try {
            const doc = new jsPDF({ unit: 'mm', format: 'a4' })
            const margenX = 15
            const anchoUtil = 180
            let y = 15

            // ============ LOGO ============
            try {
            const logoImg = await this.cargarImagen('/vendor/adminlte/dist/img/logo.png')
            doc.addImage(logoImg, 'PNG', margenX, y, 25, 25)
            } catch (error) {
            console.error('No se pudo cargar el logo:', error)
            doc.setDrawColor(200, 200, 200)
            doc.rect(margenX, y, 25, 25)
            doc.setFontSize(6)
            doc.setTextColor(180, 180, 180)
            doc.text('LOGO ERROR', margenX + 12.5, y + 13, { align: 'center' })
            doc.setTextColor(20, 20, 20)
            }

            // ============ DATOS DOCTOR ============
            const centroX = margenX + 30
            
            // Nombre del doctor (prop o backend)
            const nombreDoctor = this.doctorNombre || this.infoPacientes.doctor || 'Dr(a). Nombre del Doctor'
            // Cédula del doctor
            const cedulaDoctor = this.doctorCedula || this.infoPacientes.cedula || 'Pendiente'

            doc.setFontSize(14)
            doc.setFont('helvetica', 'bold')
            doc.text(nombreDoctor, centroX, y + 6)

            doc.setFontSize(9)
            doc.setFont('helvetica', 'normal')
            doc.setTextColor(90, 90, 90)
            doc.text('Medicina General', centroX, y + 12)
            doc.text(`Cédula Profesional: ${cedulaDoctor}`, centroX, y + 17)
            doc.text('Institución: Ultra tech', centroX, y + 22)
            doc.setTextColor(20, 20, 20)

            // Fecha / Hora
            doc.setFontSize(10)
            doc.setFont('helvetica', 'bold')
            doc.text(`Fecha: ${this.form.fecha || '—'}`, margenX + anchoUtil, y + 4, { align: 'right' })
            doc.setFont('helvetica', 'normal')
            doc.text(`Hora: ${this.form.hora || '—'}`, margenX + anchoUtil, y + 9, { align: 'right' })

            y += 30
            doc.setDrawColor(30, 30, 30)
            doc.setLineWidth(0.4)
            doc.line(margenX, y, margenX + anchoUtil, y)
            y += 8

            // ============ SIGNOS VITALES (Derecha) ============
            const cajaAncho = 45
            const cajaX = margenX + anchoUtil - cajaAncho
            const cajaYInicio = y
            doc.setFontSize(9)
            doc.setFont('helvetica', 'bold')
            doc.text('Signos Vitales', cajaX, y)
            doc.setFont('helvetica', 'normal')
            doc.setFontSize(8.5)
            let yCaja = y + 5
            const signos = [
            ['Presión arterial', this.form.presion],
            ['Peso (kg)', this.form.peso],
            ['Saturación (%)', this.form.saturacion],
            ['Temperatura (°C)', this.form.temperatura]
            ]
            signos.forEach(([etiqueta, valor]) => {
            const texto = `${etiqueta}: ${valor && String(valor).trim() ? valor : '—'}`
            doc.text(texto, cajaX, yCaja)
            yCaja += 5
            })
            doc.setDrawColor(210, 210, 210)
            doc.rect(cajaX - 4, cajaYInicio - 5, cajaAncho + 4, (yCaja - cajaYInicio) + 3)

            // ============ PACIENTE / DIAGNÓSTICO ============
            const anchoIzquierda = anchoUtil - cajaAncho - 8
            doc.setFontSize(10)
            doc.setFont('helvetica', 'bold')
            doc.text('Paciente:', margenX, y)
            doc.setFont('helvetica', 'normal')
            doc.text(this.infoPacientes.nombre_completo || '—', margenX + 20, y)

            y += 7
            doc.setFont('helvetica', 'bold')
            doc.text('Diagnóstico:', margenX, y)
            doc.setFont('helvetica', 'normal')
            const diagLineas = doc.splitTextToSize(this.form.diagnostico || '—', anchoIzquierda - 24)
            doc.text(diagLineas, margenX + 24, y)

            y = Math.max(y + diagLineas.length * 5, yCaja) + 8
            doc.setDrawColor(220, 220, 220)
            doc.line(margenX, y, margenX + anchoUtil, y)
            y += 8

            // ============ MOTIVO DE CONSULTA ============
            doc.setFontSize(10)
            doc.setFont('helvetica', 'bold')
            doc.text('Motivo de consulta', margenX, y)
            y += 5
            doc.setFont('helvetica', 'normal')
            doc.setFontSize(9.5)
            const motivoLineas = doc.splitTextToSize(this.form.motivo || '—', anchoUtil)
            doc.text(motivoLineas, margenX, y)
            y += motivoLineas.length * 5 + 6

            // ============ SÍNTOMAS ============
            doc.setFontSize(10)
            doc.setFont('helvetica', 'bold')
            doc.text('Síntomas', margenX, y)
            y += 6
            doc.setFont('helvetica', 'normal')
            doc.setFontSize(9.5)
            const listaSintomas = (this.form.sintomas || '—')
            .split('\n')
            .map(s => s.trim())
            .filter(Boolean)
            listaSintomas.forEach((linea, i) => {
            const envuelto = doc.splitTextToSize(`${i + 1}. ${linea}`, anchoUtil - 4)
            doc.text(envuelto, margenX, y)
            y += envuelto.length * 5 + 2
            })
            y += 4

            // ============ OBSERVACIONES ============
            doc.setFontSize(10)
            doc.setFont('helvetica', 'bold')
            doc.text('Indicaciones adicionales', margenX, y)
            y += 6
            doc.setFont('helvetica', 'normal')
            doc.setFontSize(9.5)
            const obsLineas = doc.splitTextToSize(this.form.observaciones || '—', anchoUtil)
            doc.text(obsLineas, margenX, y)
            y += obsLineas.length * 5 + 15

            // ============ FIRMA ============
            const firmaX = margenX + anchoUtil - 60
            doc.setDrawColor(30, 30, 30)
            doc.line(firmaX, y, firmaX + 60, y)
            doc.setFontSize(8.5)
            doc.setFont('helvetica', 'normal')
            doc.text('Firma', firmaX + 30, y + 4, { align: 'center' })

            // ============ PIE DE PÁGINA ============
            doc.setDrawColor(220, 220, 220)
            doc.line(margenX, 280, margenX + anchoUtil, 280)
            doc.setFontSize(7.5)
            doc.setTextColor(140, 140, 140)
            doc.text('Generado el ' + new Date().toLocaleString('es-MX'), margenX, 285)

            const nombrePaciente = (this.infoPacientes.nombre || 'paciente')
            .trim()
            .replace(/\s+/g, '_')
            .toLowerCase()
            doc.save(`consulta_${nombrePaciente}.pdf`)
        } catch (error) {
            console.error('Error al generar el PDF:', error)
            throw error
        }
    },
  },
  watch: {
    pacienteId: {
      immediate: true,
      handler(id) {
        if (id) {
          this.obtenerPacientes()
        }
      }
    }
  }
}
</script>

<style scoped>
.form-scroll {
  height: calc(100vh - 120px);
  overflow-y: auto;
  padding-right: 10px;
}

.main-card {
  border-radius: 30px;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.section-title {
  font-weight: 800;
  color: #1f2937;
}

.form-label {
  font-weight: 700;
  color: #374151;
  margin-bottom: 8px;
}

.input-icon-box,
.textarea-icon-box {
  position: relative;
}

.input-icon-box i {
  position: absolute;
  top: 50%;
  left: 18px;
  transform: translateY(-50%);
  color: #0d6efd;
}

.textarea-icon-box i {
  position: absolute;
  top: 20px;
  left: 18px;
  color: #0d6efd;
}

.custom-input {
  height: 46px;
  border: none;
  border-radius: 14px;
  padding: 10px 16px;
  background: #f8fafc;
  box-shadow: inset 0 0 0 1px #e5e7eb;
}

.custom-input.with-icon {
  padding-left: 50px;
}

.custom-input.is-invalid,
.custom-textarea.is-invalid {
  box-shadow: inset 0 0 0 1px #dc2626;
}

.custom-textarea {
  border: none;
  border-radius: 16px;
  padding: 16px;
  background: #f8fafc;
  resize: none;
  box-shadow: inset 0 0 0 1px #e5e7eb;
}

.custom-textarea.with-icon {
  padding-left: 50px;
}

.btn-cancel {
  background: #fee2e2;
  color: #dc2626;
  border: none;
  font-weight: 700;
}

.btn-cancel:disabled,
.save-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.save-btn {
  background: linear-gradient(135deg, #0d6efd, #00c6ff);
  border: none;
}

/* Animaciones */
.animated-section {
  animation: fadeInUp 0.5s ease-out forwards;
}

.delay-1 {
  animation-delay: 0.15s;
}

.delay-2 {
  animation-delay: 0.3s;
}

.delay-3 {
  animation-delay: 0.45s;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>