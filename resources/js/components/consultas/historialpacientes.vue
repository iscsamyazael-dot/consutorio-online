<template>
<div class="container">
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        <!-- HEADER -->
        <div class="card-header bg-white border-0 py-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="fas fa-notes-medical text-primary me-2"></i>
                        Consultas Médicas
                    </h4>
                    <small class="text-muted">
                        Historial clínico del paciente
                    </small>
                </div>
                <a
                    href="/consultas/create"
                    class="btn btn-primary rounded-pill px-4"
                >
                    <i class="fas fa-plus me-1"></i>
                    Nueva Consulta
                </a>
            </div>
        </div>
        <!-- BODY -->
        <div class="card-body p-4">

            <div v-if="cargando" class="text-center text-muted py-4">
                <span class="spinner-border spinner-border-sm me-2"></span>
                Cargando consulta...
            </div>

            <div v-else-if="!consulta" class="alert alert-warning">
                No se encontró información de esta consulta.
            </div>

            <!-- CARD CONSULTA -->
            <div v-else class="record-card">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                        <h6 class="fw-bold mb-0">
                            {{ formatearFecha(consulta.created_at) }}
                        </h6>
                        <span class="badge rounded-pill px-3 py-2" :class="colorBadge(estadoMostrado)">
                            {{ estadoMostrado }}
                        </span>
                    </div>
                    <p class="mb-1">
                        <strong>Motivo:</strong>
                        {{ consulta.motivo_consulta }}
                    </p>
                    <p class="text-muted mb-2" v-if="consulta.diagnostico">
                        Diagnóstico: {{ consulta.diagnostico }}
                    </p>
                    <small class="text-muted" v-if="consulta.medico">
                        Médico: {{ consulta.medico.nombre || consulta.medico.name }}
                    </small>
                </div>
                <!-- ACCIONES -->
                <div class="d-flex gap-2 flex-wrap">
                    <button
                        class="btn btn-sm btn-outline-info rounded-pill px-3"
                        data-bs-toggle="modal"
                        data-bs-target="#detalleConsultaModal"
                    >
                        <i class="fas fa-eye"></i>
                    </button>
                    <button
                        class="btn btn-sm btn-outline-warning rounded-pill px-3"
                        data-bs-toggle="modal"
                        data-bs-target="#recetaModal"
                    >
                        <i class="fas fa-prescription"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- MODAL DETALLE -->
<div class="modal fade" id="detalleConsultaModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title">
                    <i class="fas fa-notes-medical me-2"></i>
                    Detalle de la Consulta
                </h5>
                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                ></button>
            </div>
            <div class="modal-body p-4" v-if="consulta">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="fw-bold">Fecha</label>
                        <p class="text-muted">{{ formatearFecha(consulta.created_at) }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Médico</label>
                        <p class="text-muted">
                            {{ consulta.medico ? (consulta.medico.nombre || consulta.medico.name) : 'Sin asignar' }}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Paciente</label>
                        <p class="text-muted">
                            {{ consulta.paciente ? consulta.paciente.nombre : '' }}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Folio</label>
                        <p class="text-muted">
                            {{ consulta.folio }}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Motivo</label>
                        <p class="text-muted">
                            {{ consulta.motivo_consulta }}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Diagnóstico</label>
                        <p class="text-danger fw-semibold">
                            {{ consulta.diagnostico || 'Sin diagnóstico registrado' }}
                        </p>
                    </div>
                </div>
                <hr>
                <div>
                    <label class="fw-bold">
                        Recomendaciones médicas (IA)
                    </label>
                    <p class="text-muted mb-0">
                        {{ consulta.recomendacion_ia || consulta.observaciones || 'Sin recomendaciones registradas.' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- MODAL RECETA -->
<div class="modal fade" id="recetaModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title">
                    <i class="fas fa-prescription me-2"></i>
                    Receta Médica
                </h5>
                <button
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                ></button>
            </div>
            <div class="modal-body p-4">

                <!-- DATOS DE LA SUCURSAL Y DEL MÉDICO -->
                <div class="section-title mb-3">
                    <h5>Datos de la receta</h5>
                    <small>
                        Se imprimen en el encabezado del PDF
                    </small>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="fw-bold small mb-1">Sucursal</label>
                        <select class="form-select" v-model="ubicacionSeleccionada">
                            <option :value="null">Selecciona una sucursal</option>
                            <option
                                v-for="ubicacion in ubicaciones"
                                :key="ubicacion.id"
                                :value="ubicacion.id"
                            >
                                {{ ubicacion.nombre }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold small mb-1">Teléfono de contacto</label>
                        <input type="text" class="form-control" v-model="telefonoContacto" placeholder="Ej. 999 743 8532">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold small mb-1">Cédula profesional</label>
                        <input type="text" class="form-control" v-model="cedulaProfesional" placeholder="Ej. 3908953">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold small mb-1">Universidad</label>
                        <input type="text" class="form-control" v-model="universidad" placeholder="Ej. Universidad Autónoma de Campeche">
                    </div>
                </div>

                <!-- SIGNOS VITALES -->
                <div class="section-title mb-3">
                    <h5>Signos vitales</h5>
                    <small>
                        Captura manual al momento de la receta
                    </small>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-2">
                        <label class="fw-bold small mb-1">PA (mmHg)</label>
                        <input type="text" class="form-control" v-model="signosVitales.pa" placeholder="110/80">
                    </div>
                    <div class="col-md-2">
                        <label class="fw-bold small mb-1">FC (lpm)</label>
                        <input type="text" class="form-control" v-model="signosVitales.fc" placeholder="78">
                    </div>
                    <div class="col-md-2">
                        <label class="fw-bold small mb-1">FR (rpm)</label>
                        <input type="text" class="form-control" v-model="signosVitales.fr" placeholder="17">
                    </div>
                    <div class="col-md-2">
                        <label class="fw-bold small mb-1">Glucosa (mg/dl)</label>
                        <input type="text" class="form-control" v-model="signosVitales.glucosa" placeholder="194">
                    </div>
                    <div class="col-md-2">
                        <label class="fw-bold small mb-1">SpO2 (%)</label>
                        <input type="text" class="form-control" v-model="signosVitales.spo2" placeholder="95">
                    </div>
                    <div class="col-md-2">
                        <label class="fw-bold small mb-1">Temp. (°C)</label>
                        <input type="text" class="form-control" v-model="signosVitales.temperatura" placeholder="39">
                    </div>
                </div>

                <hr>

                <div class="section-title mb-4">
                    <h5>Medicamentos</h5>
                    <small>
                        Agrega los medicamentos del tratamiento
                    </small>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Medicamento</th>
                                <th>Dosis</th>
                                <th>Frecuencia</th>
                                <th>Duración</th>
                                <th width="120">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(med, index) in medicamentos"
                                :key="index"
                            >
                                <td>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="med.nombre"
                                    >
                                </td>
                                <td>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="med.dosis"
                                    >
                                </td>
                                <td>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="med.frecuencia"
                                    >
                                </td>
                                <td>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="med.duracion"
                                    >
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button
                                            type="button"
                                            class="btn btn-danger btn-sm"
                                            @click="eliminarMedicamento(index)"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-success btn-sm"
                                            @click="guardarReceta"
                                        >
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button
                    type="button"
                    class="btn btn-outline-primary rounded-pill px-4 mb-4"
                    @click="agregarMedicamento"
                >
                    <i class="fas fa-plus me-1"></i>
                    Agregar medicamento
                </button>

                <hr>

                <div class="mb-4">
                    <label class="fw-bold mb-2">Indicaciones adicionales</label>
                    <textarea
                        class="form-control"
                        rows="5"
                        v-model="indicacionesAdicionales"
                        placeholder="Ej. Evitar alimentos irritantes, medidas de higiene, datos de alarma..."
                    ></textarea>
                </div>

                <div class="d-flex justify-content-end">
                    <button
                        type="button"
                        class="btn btn-primary rounded-pill px-4"
                        @click="generarPDF"
                    >
                        <i class="fas fa-file-pdf me-1"></i>
                        Generar PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</template>
<style scoped>
.rounded-4 {
    border-radius: 24px !important;
}
.section-title {
    margin-bottom: 20px;
}
.section-title h5 {
    font-weight: 800;
    margin-bottom: 2px;
}
.section-title small {
    color: #6c757d;
}
.record-card {
    background: #fff;
    border: 1px solid #edf0f4;
    border-radius: 18px;
    padding: 20px;
    margin-bottom: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.04);
}
@media (max-width: 768px) {
    .record-card {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
<script>
import ApiService from '../../services/ApiService.js'
import jsPDF from 'jspdf'

export default {
    props: {
        // Si en el futuro Blade pasa el id como prop (:consulta-id="{{ $id }}"),
        // este prop tiene prioridad. Si no, se toma de la URL en mounted().
        consultaId: {
            type: [Number, String],
            default: null
        }
    },
    data() {
        return {
            consulta: null,
            cargando: false,
            medicamentos: [
                {
                    nombre: '',
                    dosis: '',
                    frecuencia: '',
                    duracion: ''
                }
            ],
            indicacionesAdicionales: '',

            // Datos que no viven en ninguna tabla relacionada a la consulta
            // todavía, así que se capturan a mano al generar la receta.
            ubicaciones: [],
            ubicacionSeleccionada: null,
            telefonoContacto: '',
            cedulaProfesional: '',
            universidad: '',
            signosVitales: {
                pa: '',
                fc: '',
                fr: '',
                glucosa: '',
                spo2: '',
                temperatura: ''
            }
        }
    },
    computed: {
        // La tabla `consultas` tiene tanto `estado` como `estado_consulta`
        // (según ConsultaController::store). Mostramos estado_consulta si
        // existe (ej. 'finalizada'), si no, caemos a estado (ej. 'en_proceso').
        estadoMostrado() {
            if (!this.consulta) return ''
            return this.consulta.estado_consulta || this.consulta.estado || ''
        }
    },
    mounted() {
        const id = this.consultaId || this.obtenerIdDesdeUrl()
        if (id) {
            this.cargarConsulta(id)
        } else {
            console.warn('No se encontró el id de la consulta ni por prop ni por URL.')
        }
        this.cargarUbicaciones()
    },
    methods: {
        // Lee el último segmento de la URL, ej: /HistorialConsulta/285 -> "285"
        obtenerIdDesdeUrl() {
            const partes = window.location.pathname.split('/').filter(Boolean)
            const ultimo = partes[partes.length - 1]
            return /^\d+$/.test(ultimo) ? ultimo : null
        },
        async cargarConsulta(id) {
            this.cargando = true
            try {
                // Endpoint que ya existe: ConsultaController@show
                const response = await ApiService.get('/consultas/' + id)
                this.consulta = response.data || null

                // El motivo_consulta y diagnostico de las consultas generadas
                // por Consulta Inteligente no traen datos reales en esas
                // columnas (motivo_consulta queda fijo como "Consulta
                // Inteligente" y diagnostico queda null). El diagnóstico real
                // vive en evaluaciones_ia, que ya trae historialClinico().
                // Reutilizamos ese endpoint filtrando la consulta actual,
                // así no hay que tocar el backend.
                if (this.consulta && this.consulta.paciente_id) {
                    await this.cargarEvaluacionReal(this.consulta.paciente_id, id)
                }
            } catch (error) {
                console.error('Error al cargar la consulta:', error)
                this.consulta = null
            } finally {
                this.cargando = false
            }
        },
        async cargarEvaluacionReal(pacienteId, consultaId) {
            try {
                const response = await ApiService.get('/historialClinico?paciente_id=' + pacienteId)
                const consultas = response.data.consultas || []
                const consultaEncontrada = consultas.find(c => String(c.id) === String(consultaId))

                if (consultaEncontrada && consultaEncontrada.evaluaciones && consultaEncontrada.evaluaciones.length > 0) {
                    const evaluacion = consultaEncontrada.evaluaciones[0]
                    // Solo sobreescribimos si el dato real de la IA existe;
                    // si la columna original ya traía algo, la dejamos.
                    if (!this.consulta.diagnostico && evaluacion.diagnostico_probable) {
                        this.consulta.diagnostico = evaluacion.diagnostico_probable
                    }
                    if (evaluacion.sintomas_detectados) {
                        this.consulta.sintomas_detectados = evaluacion.sintomas_detectados
                    }
                    if (evaluacion.recomendacion) {
                        this.consulta.recomendacion_ia = evaluacion.recomendacion
                    }
                }
            } catch (error) {
                console.error('Error al cargar la evaluación de IA:', error)
            }
        },
        // Trae el catálogo de sucursales para el <select> del modal de
        // receta. Ajusta la ruta si tu endpoint real es distinto a
        // '/ubicaciones' (por ejemplo '/ubicaciones/activas').
        async cargarUbicaciones() {
            try {
                const response = await ApiService.get('/ubicaciones')
                this.ubicaciones = response.data.ubicaciones || response.data || []
            } catch (error) {
                console.error('Error al cargar ubicaciones:', error)
                this.ubicaciones = []
            }
        },
        formatearFecha(fecha) {
            if (!fecha) return ''
            const f = new Date(fecha)
            return f.toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' })
        },
        colorBadge(estado) {
            switch ((estado || '').toLowerCase()) {
                case 'finalizada':
                case 'completada':
                    return 'bg-success'
                case 'en_proceso':
                case 'activa':
                    return 'bg-warning text-dark'
                case 'cancelada':
                    return 'bg-danger'
                default:
                    return 'bg-secondary'
            }
        },
        agregarMedicamento() {
            this.medicamentos.push({
                nombre: '',
                dosis: '',
                frecuencia: '',
                duracion: ''
            })
        },
        eliminarMedicamento(index) {
            this.medicamentos.splice(index, 1)
        },
        guardarReceta() {
            console.log(this.medicamentos)
            alert('Receta guardada correctamente')
        },
        // Genera el PDF de la receta con jsPDF, en un formato de dos
        // columnas similar a una receta física: encabezado con sucursal
        // y médico, datos del paciente, motivo, diagnóstico, medicamentos
        // numerados a la izquierda, un recuadro de "Signos Vitales" a la
        // derecha, recomendaciones de la IA, indicaciones adicionales y
        // firma.
        generarPDF() {
            const doc = new jsPDF({ unit: 'mm', format: 'letter' })
            const margenIzq = 15
            const anchoColumnaIzq = 130
            const xColumnaDer = margenIzq + anchoColumnaIzq + 8
            const anchoColumnaDer = 35
            let y = 20

            const nombreMedico = this.consulta && this.consulta.medico
                ? (this.consulta.medico.nombre || this.consulta.medico.name)
                : 'Médico no asignado'

            const nombrePaciente = this.consulta && this.consulta.paciente
                ? this.consulta.paciente.nombre
                : ''

            const motivo = (this.consulta && this.consulta.motivo_consulta) || ''
            const diagnostico = (this.consulta && this.consulta.diagnostico) || 'Sin diagnóstico registrado'
            const recomendacionIA = (this.consulta && (this.consulta.recomendacion_ia || this.consulta.observaciones)) || ''
            const folio = (this.consulta && this.consulta.folio) || ''
            const fecha = this.consulta
                ? this.formatearFecha(this.consulta.created_at)
                : new Date().toLocaleDateString('es-MX')

            const ubicacion = this.ubicaciones.find(u => u.id === this.ubicacionSeleccionada) || null
            const nombreSucursal = ubicacion ? ubicacion.nombre : ''
            const direccionSucursal = ubicacion ? ubicacion.direccion : ''

            const verificarSaltoPagina = (limite = 270) => {
                if (y > limite) {
                    doc.addPage()
                    y = 20
                }
            }

            // --- Encabezado: sucursal + médico ---
            if (nombreSucursal) {
                doc.setFont('helvetica', 'bold')
                doc.setFontSize(12)
                doc.text(nombreSucursal, margenIzq, y)
                y += 5
            }
            if (direccionSucursal) {
                doc.setFont('helvetica', 'normal')
                doc.setFontSize(9)
                doc.text(direccionSucursal, margenIzq, y)
                y += 6
            }

            doc.setFont('helvetica', 'bold')
            doc.setFontSize(14)
            doc.text(nombreMedico, margenIzq, y)
            y += 6

            doc.setFont('helvetica', 'normal')
            doc.setFontSize(10)
            doc.text('Medicina General', margenIzq, y)
            y += 5

            if (this.cedulaProfesional) {
                doc.text('Cédula Profesional ' + this.cedulaProfesional, margenIzq, y)
                y += 5
            }
            if (this.universidad) {
                doc.text(this.universidad, margenIzq, y)
                y += 5
            }

            y += 3
            doc.setFontSize(10)
            doc.text('Folio: ' + folio, margenIzq, y)
            doc.text('Fecha: ' + fecha, margenIzq + 90, y)
            y += 8

            doc.text('Paciente: ' + nombrePaciente, margenIzq, y)
            y += 8

            if (motivo) {
                doc.setFont('helvetica', 'bold')
                doc.text('Motivo: ', margenIzq, y)
                doc.setFont('helvetica', 'normal')
                doc.text(motivo, margenIzq + 18, y)
                y += 8
            }

            doc.setFont('helvetica', 'bold')
            const diagnosticoLineas = doc.splitTextToSize('Diagnóstico: ' + diagnostico, anchoColumnaIzq)
            doc.text(diagnosticoLineas, margenIzq, y)
            y += diagnosticoLineas.length * 5 + 4

            doc.setDrawColor(180)
            doc.line(margenIzq, y, margenIzq + anchoColumnaIzq + 8 + anchoColumnaDer, y)
            y += 8

            // A partir de aquí, medicamentos (columna izquierda) y signos
            // vitales (columna derecha) arrancan a la misma altura.
            const yInicioColumnas = y

            // --- Columna derecha: Signos Vitales ---
            const hayVitales = Object.values(this.signosVitales).some(v => v)
            if (hayVitales) {
                let yDer = yInicioColumnas
                doc.setFont('helvetica', 'bold')
                doc.setFontSize(11)
                doc.text('Signos Vitales', xColumnaDer, yDer)
                yDer += 6

                doc.setFontSize(9)
                const filasVitales = [
                    this.signosVitales.pa ? ['PA', this.signosVitales.pa + ' mmHg'] : null,
                    this.signosVitales.fc ? ['FC', this.signosVitales.fc + ' lpm'] : null,
                    this.signosVitales.fr ? ['FR', this.signosVitales.fr + ' rpm'] : null,
                    this.signosVitales.glucosa ? ['Glucosa', this.signosVitales.glucosa + ' mg/dl'] : null,
                    this.signosVitales.spo2 ? ['SpO2', this.signosVitales.spo2 + ' %'] : null,
                    this.signosVitales.temperatura ? ['T', this.signosVitales.temperatura + ' °C'] : null,
                ].filter(Boolean)

                filasVitales.forEach(([etiqueta, valor]) => {
                    doc.setFont('helvetica', 'bold')
                    doc.text(etiqueta, xColumnaDer, yDer)
                    doc.setFont('helvetica', 'normal')
                    doc.text(valor, xColumnaDer, yDer + 4)
                    yDer += 9
                })
            }

            // --- Columna izquierda: Medicamentos ---
            doc.setFont('helvetica', 'normal')
            doc.setFontSize(11)

            this.medicamentos.forEach((med, index) => {
                if (!med.nombre) return

                verificarSaltoPagina(260)

                doc.setFont('helvetica', 'bold')
                doc.text((index + 1) + '. ' + med.nombre, margenIzq, y)
                y += 5

                const detalle = [med.dosis, med.frecuencia, med.duracion]
                    .filter(Boolean)
                    .join(' — ')

                if (detalle) {
                    doc.setFont('helvetica', 'normal')
                    const detalleLineas = doc.splitTextToSize(detalle, anchoColumnaIzq - 6)
                    doc.text(detalleLineas, margenIzq + 6, y)
                    y += detalleLineas.length * 5
                }

                y += 3
            })

            // --- Recomendaciones médicas (IA) ---
            if (recomendacionIA) {
                y += 5
                verificarSaltoPagina(260)

                doc.setFont('helvetica', 'bold')
                doc.setFontSize(11)
                doc.text('Recomendaciones médicas (IA)', margenIzq, y)
                y += 6

                doc.setFont('helvetica', 'normal')
                doc.setFontSize(10)
                const recomendacionLineas = doc.splitTextToSize(recomendacionIA, anchoColumnaIzq + 8 + anchoColumnaDer)
                recomendacionLineas.forEach(linea => {
                    verificarSaltoPagina(270)
                    doc.text(linea, margenIzq, y)
                    y += 5
                })
            }

            // --- Indicaciones adicionales ---
            if (this.indicacionesAdicionales) {
                y += 5
                verificarSaltoPagina(260)

                doc.setFont('helvetica', 'bold')
                doc.setFontSize(11)
                doc.text('Indicaciones adicionales', margenIzq, y)
                y += 6

                doc.setFont('helvetica', 'normal')
                doc.setFontSize(10)
                const indicacionesLineas = doc.splitTextToSize(
                    this.indicacionesAdicionales,
                    anchoColumnaIzq + 8 + anchoColumnaDer
                )
                indicacionesLineas.forEach(linea => {
                    verificarSaltoPagina(270)
                    doc.text(linea, margenIzq, y)
                    y += 5
                })
            }

            // --- Pie de página: teléfono de contacto ---
            if (this.telefonoContacto) {
                verificarSaltoPagina(260)
                y += 8
                doc.setDrawColor(180)
                doc.line(margenIzq, y, margenIzq + anchoColumnaIzq + 8 + anchoColumnaDer, y)
                y += 6
                doc.setFont('helvetica', 'normal')
                doc.setFontSize(9)
                doc.text('En caso de dudas llamar al ' + this.telefonoContacto, margenIzq, y)
                y += 6
            }

            // --- Firma ---
            verificarSaltoPagina(250)
            y += 20
            doc.line(margenIzq + 110, y, margenIzq + 180, y)
            doc.setFontSize(9)
            doc.text('Firma', margenIzq + 135, y + 5)

            const nombreArchivo = 'Receta_' + (folio || 'consulta') + '.pdf'
            doc.save(nombreArchivo)
        }
    }
}
</script>