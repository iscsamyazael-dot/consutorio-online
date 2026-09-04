<template>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history"></i>
                Historial Clínico
            </h3>
        </div>

        <div class="card-body p-2" style="max-height: 500px; overflow-y:auto;">

            <div v-if="cargando" class="text-center text-muted py-3">
                <i class="fas fa-spinner fa-spin"></i> Cargando historial...
            </div>

            <div v-else-if="error" class="alert alert-danger m-3 py-2 px-3">
                ⚠️ No se pudo cargar el historial clínico.
            </div>

            <div v-else-if="consultas.length === 0" class="text-muted text-center py-3">
                Este paciente aún no tiene consultas registradas.
            </div>

            <div v-else>
                <div
                    v-for="consulta in consultasRecientes"
                    :key="consulta.id"
                    class="hc-card"
                >
                    <div class="hc-dot" :class="colorPunto(consulta.estado_consulta)"></div>

                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start flex-wrap">
                            <div>
                                <h6 class="fw-bold mb-0" style="font-size:13px;">
                                    <i class="fas fa-calendar-alt text-primary mr-1"></i>
                                    {{ formatearFecha(consulta.fecha || consulta.created_at) }}
                                </h6>
                                <span class="text-muted" style="font-size:12px;">
                                    <i class="far fa-clock mr-1"></i>
                                    {{ formatearHora(consulta.fecha || consulta.created_at) }}
                                </span>
                            </div>
                            <span
                                class="badge rounded-pill px-2 py-1"
                                :class="colorBadge(consulta.estado_consulta)"
                                style="font-size:11px;"
                            >
                                {{ formatearEstado(consulta.estado_consulta) }}
                            </span>
                        </div>

                        <p class="mb-1 mt-2" style="font-size:13px;" v-if="consulta.motivo_consulta">
                            <strong>Motivo:</strong> {{ consulta.motivo_consulta }}
                        </p>
                        <p class="text-muted mb-2" style="font-size:12px;" v-if="diagnosticoDe(consulta)">
                            Diagnóstico: {{ diagnosticoDe(consulta) }}
                        </p>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary rounded-pill px-3"
                            style="font-size:12px;"
                            @click="verDetalleConsulta(consulta)"
                        >
                            Ver consulta completa
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal: Detalle de la Consulta -->
        <div class="modal fade" id="modalDetalleConsulta" tabindex="-1" ref="modalDetalleConsulta">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header" style="background:#0d6efd; color:#fff;">
                        <h5 class="modal-title">
                            <i class="fas fa-file-medical-alt mr-2"></i>
                            Detalle de la Consulta
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <div v-if="cargandoDetalle" class="text-center text-muted py-4">
                            <i class="fas fa-spinner fa-spin"></i> Cargando detalle...
                        </div>

                        <div v-else-if="errorDetalle" class="alert alert-danger py-2 px-3">
                            ⚠️ No se pudo cargar el detalle de la consulta.
                        </div>

                        <div v-else-if="detalleConsulta">

                            <h6 class="fw-bold text-primary">
                                <i class="fas fa-info-circle mr-1"></i> Datos de la consulta
                            </h6>
                            <div class="row mb-3" style="font-size:14px;">
                                <div class="col-6 mb-2">
                                    <strong>Fecha</strong>
                                    <div class="text-muted">{{ formatearFecha(detalleConsulta.created_at) }}</div>
                                </div>
                                <div class="col-6 mb-2">
                                    <strong>Médico</strong>
                                    <div class="text-muted">
                                        {{ detalleConsulta.medico ? (detalleConsulta.medico.nombre || detalleConsulta.medico.name) : 'Sin asignar' }}
                                    </div>
                                </div>
                                <div class="col-6 mb-2">
                                    <strong>Paciente</strong>
                                    <div class="text-muted">
                                        {{ detalleConsulta.paciente ? detalleConsulta.paciente.nombre : 'Sin paciente registrado' }}
                                    </div>
                                </div>
                                <div class="col-6 mb-2">
                                    <strong>Folio</strong>
                                    <div class="text-muted">{{ detalleConsulta.folio || 'Sin folio' }}</div>
                                </div>
                                <div class="col-6 mb-2">
                                    <strong>Motivo de consulta</strong>
                                    <div class="text-muted">{{ detalleConsulta.motivo_consulta || 'Sin motivo registrado' }}</div>
                                </div>
                                <div class="col-6 mb-2">
                                    <strong>Diagnóstico</strong>
                                    <div class="text-danger">{{ detalleConsulta.diagnostico || 'Sin diagnóstico registrado' }}</div>
                                </div>
                            </div>

                            <hr>

                            <h6 class="fw-bold text-primary">
                                <i class="fas fa-clipboard-list mr-1"></i> Nota PSOAPP
                            </h6>

                            <div v-if="detalleConsulta.nota_psoapp" style="font-size:14px;">
                                <div class="mb-2" v-if="detalleConsulta.nota_psoapp.presentacion">
                                    <strong>Presentación</strong>
                                    <p class="text-muted mb-0">{{ detalleConsulta.nota_psoapp.presentacion }}</p>
                                </div>
                                <div class="mb-2" v-if="detalleConsulta.nota_psoapp.subjetivo">
                                    <strong>Subjetivo</strong>
                                    <p class="text-muted mb-0">{{ detalleConsulta.nota_psoapp.subjetivo }}</p>
                                </div>
                                <div class="mb-2" v-if="detalleConsulta.nota_psoapp.objetivo">
                                    <strong>Objetivo</strong>
                                    <p class="text-muted mb-0">{{ detalleConsulta.nota_psoapp.objetivo }}</p>
                                </div>
                                <div class="mb-2" v-if="detalleConsulta.nota_psoapp.analisis">
                                    <strong>Análisis</strong>
                                    <p class="text-muted mb-0">{{ detalleConsulta.nota_psoapp.analisis }}</p>
                                </div>
                                <div class="mb-2" v-if="detalleConsulta.nota_psoapp.plan">
                                    <strong>Plan</strong>
                                    <p class="text-muted mb-0">{{ detalleConsulta.nota_psoapp.plan }}</p>
                                </div>
                                <div class="mb-2" v-if="detalleConsulta.nota_psoapp.pronostico">
                                    <strong>Pronóstico</strong>
                                    <p class="text-muted mb-0">{{ detalleConsulta.nota_psoapp.pronostico }}</p>
                                </div>
                            </div>
                            <div v-else class="text-muted small">
                                No se encontró una Nota PSOAPP registrada para esta consulta.
                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- fin modal -->

    </div>
</template>

<style scoped>
.hc-card {
    display: flex;
    gap: 12px;
    background: #fff;
    border: 1px solid #edf0f4;
    border-radius: 14px;
    padding: 12px;
    margin-bottom: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
}

.hc-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-top: 4px;
    flex-shrink: 0;
}
</style>

<script>
import axios from 'axios'

var route = document.querySelector("[name=route]").value
var urlHistorialClinico = route + '/historialClinico'

export default {
    props: {
        pacienteId: {
            type: [Number, String],
            default: null
        },
        iaData: {
            type: Object,
            default: null
        },
        // Cuántas consultas finalizadas mostrar en el historial.
        maxConsultas: {
            type: Number,
            default: 5
        }
    },
    data() {
        return {
            consultas: [],
            cargando: false,
            error: false,

            // Modal de detalle de consulta
            detalleConsulta: null,
            cargandoDetalle: false,
            errorDetalle: false
        }
    },
    computed: {
        // El Historial Clínico solo debe mostrar consultas ya cerradas
        // (finalizada/completada). La consulta "en_proceso" es la actual,
        // que se está viendo en el panel de "Consulta en Tiempo Real", así
        // que no debe listarse aquí como si fuera historial.
        // Además, solo se muestran las 2 más recientes. Se asume que el
        // backend ya devuelve `consultas` ordenadas de más reciente a más
        // antigua.
        // El Historial Clínico solo debe mostrar consultas ya cerradas
        // (finalizada/completada) y que además tengan diagnóstico. Una
        // consulta finalizada sin diagnóstico se considera "vacía" (sin
        // nota PSOAPP ni evaluación real), así que tampoco se muestra.
        // Además, solo se muestran las N más recientes (maxConsultas). Se
        // asume que el backend ya devuelve `consultas` ordenadas de más
        // reciente a más antigua.
        consultasRecientes() {
            return this.consultas
                .filter(c => ['finalizada', 'completada'].includes((c.estado_consulta || '').toLowerCase()))
                .filter(c => !!this.diagnosticoDe(c))
                .slice(0, this.maxConsultas)
        }
    },
    watch: {
        // Refresca cuando la IA responde algo nuevo en la consulta activa,
        // así la consulta en curso aparece/actualiza dentro del historial
        iaData() {
            if (this.pacienteId) {
                this.cargarHistorial(this.pacienteId, { mantenerAbiertos: true })
            }
        },
        pacienteId: {
            handler(nuevoId) {
                if (nuevoId) this.cargarHistorial(nuevoId)
            },
            immediate: true
        }
    },
    methods: {
        async cargarHistorial(pacienteId, opts = {}) {
            this.cargando = !opts.mantenerAbiertos
            this.error = false

            try {
                const response = await axios.get(urlHistorialClinico, {
                    params: { paciente_id: pacienteId }
                })

                this.consultas = response.data.success ? response.data.consultas : []

            } catch (err) {
                console.error('Error al cargar historial clínico:', err)
                this.error = true
            } finally {
                this.cargando = false
            }
        },

        // Extrae el diagnóstico probable de la consulta. Se intenta primero
        // el campo directo de la consulta y, si no existe, se busca en la
        // primera evaluación asociada, probando distintos nombres de campo
        // por si el backend no usa exactamente "diagnostico_probable".
        diagnosticoDe(consulta) {
            if (consulta.diagnostico) return consulta.diagnostico

            const evaluacion = (consulta.evaluaciones && consulta.evaluaciones[0]) || null
            if (!evaluacion) return null

            return evaluacion.diagnostico_probable
                || evaluacion.diagnostico
                || evaluacion.diagnostico_ia
                || null
        },

        // ── Modal: Detalle de la Consulta ───────────────────────────
        // Usa el mismo endpoint que ya carga HistorialConsulta.vue
        // (ConsultaController@show, ruta GET /consultas/{id}), que
        // devuelve la consulta con sus relaciones paciente, medico y
        // notaPsoapp (serializada como "nota_psoapp").
        async verDetalleConsulta(consulta) {
            this.detalleConsulta = null
            this.errorDetalle = false
            this.cargandoDetalle = true

            $('#modalDetalleConsulta').modal('show')

            try {
                const response = await axios.get(`${route}/consultas/${consulta.id}`)
                const datos = response.data

                if (datos && datos.id) {
                    this.detalleConsulta = datos

                    // Las consultas generadas por Consulta Inteligente no
                    // traen diagnostico en la tabla consultas (queda null);
                    // el diagnóstico real vive en evaluaciones_ia, así que
                    // lo completamos igual que hace HistorialConsulta.vue.
                    if (!this.detalleConsulta.diagnostico && this.detalleConsulta.paciente_id) {
                        await this.completarDiagnosticoIA(this.detalleConsulta)
                    }
                } else {
                    this.errorDetalle = true
                }
            } catch (err) {
                console.error('Error al cargar el detalle de la consulta:', err)
                this.errorDetalle = true
            } finally {
                this.cargandoDetalle = false
            }
        },

        // Busca la evaluación IA de esta consulta específica dentro del
        // historial clínico del paciente y, si trae diagnostico_probable,
        // lo usa para completar el detalle mostrado en el modal.
        async completarDiagnosticoIA(consultaDetalle) {
            try {
                const response = await axios.get(urlHistorialClinico, {
                    params: { paciente_id: consultaDetalle.paciente_id }
                })

                const consultas = (response.data && response.data.consultas) || []
                const encontrada = consultas.find(c => String(c.id) === String(consultaDetalle.id))
                const evaluacion = encontrada && encontrada.evaluaciones && encontrada.evaluaciones[0]

                if (evaluacion && evaluacion.diagnostico_probable) {
                    consultaDetalle.diagnostico = evaluacion.diagnostico_probable
                }
            } catch (err) {
                console.error('No se pudo completar el diagnóstico IA en el detalle:', err)
            }
        },

        formatearFecha(fecha) {
            if (!fecha) return ''
            const d = new Date(fecha)
            return d.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' })
        },
        formatearHora(fecha) {
            if (!fecha) return ''
            const d = new Date(fecha)
            return d.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit', hour12: true })
        },

        // Mismos helpers de estado que usa ExpedienteTabs.vue, para que
        // el badge se vea y comporte igual en ambos lugares.
        formatearEstado(estado) {
            if (!estado) return 'En proceso'
            switch (estado.toLowerCase()) {
                case 'en_proceso': return 'En proceso'
                case 'finalizada':
                case 'completada': return 'Finalizada'
                case 'cancelada': return 'Cancelada'
                default: return 'En proceso'
            }
        },
        colorPunto(estado) {
            switch ((estado || '').toLowerCase()) {
                case 'finalizada':
                case 'completada': return 'bg-success'
                case 'en_proceso': return 'bg-warning'
                case 'urgencia': return 'bg-danger'
                default: return 'bg-secondary'
            }
        },
        colorBadge(estado) {
            switch ((estado || '').toLowerCase()) {
                case 'finalizada':
                case 'completada': return 'bg-success'
                case 'en_proceso': return 'bg-warning text-dark'
                case 'urgencia': return 'bg-danger'
                default: return 'bg-secondary'
            }
        }
    }
}
</script>