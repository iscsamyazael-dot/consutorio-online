
<template>
    <div class="container">
        <div class="d-flex justify-content-start mb-3">
    <button
        type="button"
        class="btn btn-outline-primary rounded-pill px-4"
        @click="regresarExpediente"
    >
        <i class="fas fa-arrow-left me-2"></i>
        Regresar al expediente
    </button>
</div>

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
                <!-- CARGANDO -->
                <div
                    v-if="cargando"
                    class="text-center text-muted py-4"
                >
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Cargando consulta...
                </div>
                <!-- SIN CONSULTA -->
                <div
                    v-else-if="!consulta"
                    class="alert alert-warning"
                >
                    No se encontró información de esta consulta.
                </div>
                <!-- CARD CONSULTA -->
                <div
                    v-else
                    class="record-card"
                >
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                            <h6 class="fw-bold mb-0">
                                {{ formatearFecha(consulta.created_at) }}
                            </h6>
                            <span
                                class="badge rounded-pill px-3 py-2"
                                :class="colorBadge(estadoMostrado)"
                            >
                                {{ estadoMostrado }}
                            </span>
                        </div>
                        <p class="mb-1">
                            <strong>Motivo:</strong>
                            {{ consulta.motivo_consulta || 'Sin motivo registrado' }}
                        </p>
                        <p
                            class="text-muted mb-2"
                            v-if="consulta.diagnostico"
                        >
                            <strong>Diagnóstico:</strong>
                            {{ consulta.diagnostico }}
                        </p>
                        <small
                            class="text-muted"
                            v-if="consulta.medico"
                        >
                            Médico:
                            {{ consulta.medico.nombre || consulta.medico.name }}
                        </small>
                    </div>
                    <!-- ÚNICA ACCIÓN: VER -->
                    <div class="d-flex gap-2 flex-wrap">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-info rounded-pill px-3"
                            data-bs-toggle="modal"
                            data-bs-target="#detalleConsultaModal"
                            title="Ver detalle de la consulta"
                        >
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ===================================================== -->
    <!-- MODAL DETALLE DE LA CONSULTA -->
    <!-- ===================================================== -->
    <div
        class="modal fade"
        id="detalleConsultaModal"
        tabindex="-1"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 rounded-4">
                <!-- HEADER DEL MODAL -->
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
                <!-- BODY DEL MODAL -->
                <div
                    class="modal-body p-4"
                    v-if="consulta"
                >
                    <!-- ===================================== -->
                    <!-- DATOS GENERALES -->
                    <!-- ===================================== -->
                    <div class="mb-4">
                        <h5 class="fw-bold text-primary mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Datos de la consulta
                        </h5>
                        <div class="row g-4">
                            <!-- FECHA -->
                            <div class="col-md-6">
                                <label class="fw-bold">
                                    Fecha
                                </label>
                                <p class="text-muted mb-0">
                                    {{ formatearFecha(consulta.created_at) }}
                                </p>
                            </div>
                            <!-- MÉDICO -->
                            <div class="col-md-6">
                                <label class="fw-bold">
                                    Médico
                                </label>
                                <p class="text-muted mb-0">

                                    {{
                                        consulta.medico
                                            ? (
                                                consulta.medico.nombre ||
                                                consulta.medico.name
                                            )
                                            : 'Sin asignar'
                                    }}
                                </p>
                            </div>
                            <!-- PACIENTE -->
                            <div class="col-md-6">
                                <label class="fw-bold">
                                    Paciente
                                </label>
                                <p class="text-muted mb-0">
                                    {{
                                        consulta.paciente
                                            ? consulta.paciente.nombre
                                            : 'Sin paciente registrado'
                                    }}
                                </p>
                            </div>
                            <!-- FOLIO -->
                            <div class="col-md-6">
                                <label class="fw-bold">
                                    Folio
                                </label>
                                <p class="text-muted mb-0">
                                    {{
                                        consulta.folio ||
                                        'Sin folio'
                                    }}
                                </p>
                            </div>
                            <!-- MOTIVO -->
                            <div class="col-md-6">
                                <label class="fw-bold">
                                    Motivo de consulta
                                </label>
                                <p class="text-muted mb-0">
                                    {{
                                        consulta.motivo_consulta ||
                                        'Sin motivo registrado'
                                    }}
                                </p>
                            </div>
                            <!-- DIAGNÓSTICO -->
                            <div class="col-md-6">
                                <label class="fw-bold">
                                    Diagnóstico
                                </label>
                                <p class="text-danger fw-semibold mb-0">
                                    {{
                                        consulta.diagnostico ||
                                        'Sin diagnóstico registrado'
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <!-- ===================================== -->
                    <!-- NOTA PSOAPP -->
                    <!-- ===================================== -->
                    <div class="mb-4">
                        <h5 class="fw-bold text-primary mb-3">
                            <i class="fas fa-file-medical me-2"></i>
                            Nota PSOAPP
                        </h5>

                        <div v-if="consulta.nota_psoapp">

                            <!-- PRESENTACIÓN -->
                            <div class="mb-3">
                                <h6 class="fw-bold text-dark">
                                    Presentación
                                </h6>
                                <p class="text-muted mb-0">
                                    {{ consulta.nota_psoapp.presentacion || 'Sin información registrada.' }}
                                </p>
                            </div>

                            <!-- SUBJETIVO -->
                            <div class="mb-3">
                                <h6 class="fw-bold text-dark">
                                    Subjetivo
                                </h6>
                                <p class="text-muted mb-0">
                                    {{ consulta.nota_psoapp.subjetivo || 'Sin información registrada.' }}
                                </p>
                            </div>

                            <!-- OBJETIVO -->
                            <div class="mb-3">
                                <h6 class="fw-bold text-dark">
                                    Objetivo
                                </h6>
                                <p class="text-muted mb-0">
                                    {{ consulta.nota_psoapp.objetivo || 'Sin información registrada.' }}
                                </p>
                            </div>

                            <!-- ANÁLISIS -->
                            <div class="mb-3">
                                <h6 class="fw-bold text-dark">
                                    Análisis
                                </h6>
                                <p class="text-muted mb-0">
                                    {{ consulta.nota_psoapp.analisis || 'Sin información registrada.' }}
                                </p>
                            </div>

                            <!-- PLAN -->
                            <div class="mb-3">
                                <h6 class="fw-bold text-dark">
                                    Plan
                                </h6>
                                <p class="text-muted mb-0">
                                    {{ consulta.nota_psoapp.plan || 'Sin información registrada.' }}
                                </p>
                            </div>

                            <!-- PRONÓSTICO -->
                            <div class="mb-3">
                                <h6 class="fw-bold text-dark">
                                    Pronóstico
                                </h6>
                                <p class="text-muted mb-0">
                                    {{ consulta.nota_psoapp.pronostico || 'Sin información registrada.' }}
                                </p>
                            </div>

                        </div>

                        <!-- SIN NOTA -->
                        <div
                            v-else
                            class="alert alert-light border text-muted mb-0"
                        >
                            <i class="fas fa-info-circle me-2"></i>
                            No se encontró una Nota PSOAPP registrada
                            para esta consulta.
                        </div>
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

    const estado = (
        this.consulta.estado_consulta ||
        this.consulta.estado ||
        ''
    ).toLowerCase()

    // Si la consulta ya está marcada como finalizada
    if (
        estado === 'finalizada' ||
        estado === 'completada'
    ) {
        return 'Finalizada'
    }

    // Si está cancelada
    if (estado === 'cancelada') {
        return 'Cancelada'
    }

    // Si está en proceso pero ya tiene diagnóstico,
    // consideramos que la consulta terminó
    if (
        estado === 'en_proceso' &&
        this.consulta.diagnostico
    ) {
        return 'Finalizada'
    }

    // Si no tiene diagnóstico, continúa en proceso
    return 'En proceso'
},
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
        regresarExpediente() {
    if (this.consulta && this.consulta.paciente) {
        window.location.href = '/ExpedientePacientes/' + this.consulta.paciente.id
    } else {
        console.warn('No se encontró el paciente asociado a la consulta.')
    }
},
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
                    if (evaluacion.nota_psoap) {
                        this.consulta.nota_psoap = evaluacion.nota_psoap
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