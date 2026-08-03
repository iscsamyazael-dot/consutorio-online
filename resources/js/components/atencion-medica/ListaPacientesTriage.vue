
<template>

<!-- Lista -->

<div class="card card-outline card-primary shadow-lg">

    <div class="card-header">

        <h3 class="card-title font-weight-bold">
            Lista de Pacientes TRIAGE
        </h3>

    </div>

    <div class="card-body table-responsive">

        <table class="table table-hover table-bordered">

            <thead class="bg-light">

                <tr>

                    <th>Prioridad</th>
                    <th>Paciente</th>
                    <th>Síntomas</th>
                    <th>Presión</th>
                    <th>Saturación</th>
                    <th>Temperatura</th>
                    <th>Estado</th>
                    <th>Tiempo</th>
                    <th>Acciones</th>

                </tr>

             </thead>

                <tbody>
                    <tr v-for="paciente in triage" :key="paciente.id">

                        <!-- PRIORIDAD (viene de la IA) -->
                        <td>
                            <template v-if="analisisIA[paciente.id]?.loading">
                                <span class="badge bg-secondary">
                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                    Analizando IA...
                                </span>
                            </template>
                            <template v-else>
                                <span :class="obtenerPrioridad(analisisIA[paciente.id]?.prioridad).clase">
                                    {{ obtenerPrioridad(analisisIA[paciente.id]?.prioridad).texto }}
                                </span>
                                <!-- Icono de fallback si la IA usó BD -->
                                <i v-if="analisisIA[paciente.id]?.error"
                                class="fas fa-exclamation-circle text-warning ms-1"
                                title="IA no disponible, usando datos de BD">
                                </i>
                            </template>
                        </td>

                        <!-- PACIENTE -->
                        <td class="fw-bold">{{ paciente.nombre }}</td>

                        <!-- SÍNTOMAS -->
                        <td>{{ paciente.triages[0]?.sintomas }}</td>

                        <!-- PRESIÓN -->
                        <td>{{ paciente.triages[0]?.presion }}</td>

                        <!-- SATURACIÓN -->
                        <td>{{ paciente.triages[0]?.saturacion }}</td>

                        <!-- TEMPERATURA -->
                        <td>{{ paciente.triages[0]?.temperatura }}</td>

                        <!-- ESTADO (IA) -->
                        <td>
                            <template v-if="analisisIA[paciente.id]?.loading">
                                <span class="badge bg-secondary">...</span>
                            </template>
                            <template v-else>
                                <span v-if="analisisIA[paciente.id]?.estado === 'grave'"
                                    class="badge bg-danger">GRAVE</span>
                                <span v-else-if="analisisIA[paciente.id]?.estado === 'moderado'"
                                    class="badge bg-warning text-dark">MODERADO</span>
                                <span v-else
                                    class="badge bg-success">LEVE</span>
                                <!-- Justificación IA en tooltip -->
                                <br>
                                <small class="text-muted fst-italic" style="font-size:11px;">
                                    {{ analisisIA[paciente.id]?.justificacion }}
                                </small>
                            </template>
                        </td>

                        <!-- COUNTDOWN (reactivo cada 30s) -->
                        <td>
                            <template v-if="analisisIA[paciente.id]?.loading">
                                <span class="text-muted">—</span>
                            </template>
                            <span v-else
                                :class="obtenerContadorTiempoIa(
                                    analisisIA[paciente.id]?.prioridad,
                                    paciente.triages?.[0]?.created_at
                                ).claseCss">
                                {{ obtenerContadorTiempoIa(
                                    analisisIA[paciente.id]?.prioridad,
                                    paciente.triages?.[0]?.created_at
                                ).texto }}
                            </span>
                        </td>

                        <!-- ACCIONES -->
                        <td>
                            <button
                                class="btn btn-info btn-sm"
                                data-toggle="modal"
                                data-target="#modalVerTriage"
                                @click="obtenerPacientesIndividual(paciente.id)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
        </table>
    </div>
</div>


<!-- =========================================
MODAL VER TRIAGE PREMIUM
========================================= -->
<div
    class="modal fade"
    id="modalVerTriage"
    tabindex="-1"
    aria-labelledby="modalVerTriageLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 overflow-hidden rounded-5 shadow-lg">

            <div class="p-4 text-white" style="background: linear-gradient(135deg, #1e3a8a, #3b82f6);">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex justify-content-center align-items-center me-4" 
                             style="width:70px; height:70px; background:rgba(255,255,255,.2); border: 2px solid rgba(255,255,255,0.4);">
                            <i class="fas fa-robot text-warning" style="font-size:32px;"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold mb-1" id="modalVerTriageLabel">Monitoreo Clínico Inteligente</h2>
                            <p class="mb-0 opacity-75">Análisis de constantes vitales en tiempo real por IA</p>
                        </div>
                    </div>
                    <button type="button" class="close text-white opacity-100" data-dismiss="modal" aria-label="Close" style="font-size: 30px; background: none; border: none;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>

            <div class="modal-body p-5" style="background:#f8fafc;">
                
                <div v-if="!detalletriage || !detalletriage.id" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
                    <h5 class="text-muted mt-3 fw-bold">Procesando diagnóstico de IA...</h5>
                </div>

                <div v-else class="row">
                    
                    <div class="col-md-6 mb-4">
                        <div class="bg-white rounded-4 shadow-sm p-4 h-100 border-start border-primary border-4">
                            <small class="text-uppercase fw-bold text-muted d-block mb-1">
                                <i class="fas fa-id-card text-primary me-2"></i> Paciente
                            </small>
                            <h4 class="fw-bold text-dark mt-2 mb-0">{{ detalletriage.nombre }}</h4>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="bg-white rounded-4 shadow-sm p-4 h-100 border-start border-danger border-4">
                            <small class="text-uppercase fw-bold text-muted d-block mb-1">
                                <i class="fas fa-brain text-danger me-2"></i> Prioridad Sugerida por IA
                            </small>
                            <h4 class="fw-bold mt-2 mb-0">
                                {{ obtenerPrioridad(detalletriage._ia?.prioridad ?? 
                                    mapearEstadoAPrioridad(detalletriage.triages?.[0]?.estado)).texto }}
                            </h4>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="bg-white rounded-4 shadow-sm p-4 h-100 text-center">
                            <div class="mb-2">
                                <i class="fas fa-heartbeat text-danger" style="font-size: 28px;"></i>
                            </div>
                            <small class="text-uppercase fw-bold text-muted d-block mb-2">Estado General</small>
                            <span v-if="detalletriage.triages?.[0]?.estado === 'grave'" class="badge bg-danger px-3 py-2 text-white fw-bold fs-6">GRAVE</span>
                            <span v-else-if="detalletriage.triages?.[0]?.estado === 'moderado'" class="badge bg-warning px-3 py-2 text-dark fw-bold fs-6">MODERADO</span>
                            <span v-else class="badge bg-success px-3 py-2 text-white fw-bold fs-6">LEVE</span>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="bg-white rounded-4 shadow-sm p-4 h-100 text-center">
                            <div class="mb-2">
                                <i class="fas fa-stethoscope text-info" style="font-size: 28px;"></i>
                            </div>
                            <small class="text-uppercase fw-bold text-muted d-block mb-1">Presión Arterial</small>
                            <h4 class="fw-bold text-dark mt-2 mb-0">{{ detalletriage.triages?.[0]?.presion || 'N/R' }}</h4>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="bg-white rounded-4 shadow-sm p-4 h-100 text-center">
                            <div class="mb-2">
                                <i class="fas fa-lungs text-teal" style="font-size: 28px; color: #0d9488;"></i>
                            </div>
                            <small class="text-uppercase fw-bold text-muted d-block mb-1">Saturación O₂</small>
                            <h4 class="fw-bold text-dark mt-2 mb-0">
                                {{ detalletriage.triages?.[0]?.saturacion ? detalletriage.triages[0].saturacion + '%' : 'N/R' }}
                            </h4>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="bg-white rounded-4 shadow-sm p-4 h-100 border-start border-warning border-4">
                            <div class="d-flex align-items-center">
                                <div class="p-3 bg-warning-light rounded-circle me-3" style="background: #fef9c3;">
                                    <i class="fas fa-thermometer-high text-warning" style="font-size: 24px;"></i>
                                </div>
                                <div>
                                    <small class="text-uppercase fw-bold text-muted d-block">Temperatura Corporal</small>
                                    <h4 class="fw-bold text-dark mb-0 mt-1">
                                        {{ detalletriage.triages?.[0]?.temperatura ? detalletriage.triages[0].temperatura + ' °C' : 'N/R' }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="bg-white rounded-4 shadow-sm p-4 h-100 border-start border-info border-4">
                            <div class="d-flex align-items-center">
                                <div class="p-3 bg-info-light rounded-circle me-3" style="background: #e0f2fe;">
                                    <i class="fas fa-clock text-info" style="font-size: 24px;"></i>
                                </div>
                                <div>
                                    <small class="text-uppercase fw-bold text-muted d-block">Tiempo Límite Restante</small>
                                    <h5 :class="obtenerLimiteYEstadoEspera(detalletriage.triages?.[0]?.estado, detalletriage.triages?.[0]?.created_at).claseCss" class="mb-0 mt-1">
                                        {{ obtenerLimiteYEstadoEspera(detalletriage.triages?.[0]?.estado, detalletriage.triages?.[0]?.created_at).texto }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mb-5">
                        <div class="bg-white rounded-4 shadow-sm p-4 border-top border-primary border-3">
                            <small class="text-uppercase fw-bold text-muted d-block mb-2">
                                <i class="fas fa-comment-medical text-primary me-2"></i> Motivo de Consulta y Síntomas
                            </small>
                            <p class="mt-3 mb-0 text-secondary p-3 bg-light rounded-3" style="line-height:1.8; font-size:16px; border-left: 4px solid #cbd5e1;">
                                {{ detalletriage.triages?.[0]?.sintomas || 'No se registraron síntomas descriptivos.' }}
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <AlertasClinicasIA :iaData="{ alertas: detalletriage.alertas || [] }" />
                    </div>

                    <div class="col-md-6 mb-4">
                        <RecomendacionesIA :iaData="{ recomendaciones: detalletriage.recomendaciones || [] }" />
                    </div>
                    </div> </div>

            <div class="modal-footer bg-light border-0 px-5 py-3">
                <button type="button" class="btn btn-secondary rounded-pill px-4 fw-bold" data-dismiss="modal">
                    <i class="fas fa-times me-2"></i> Cerrar Ventana
                </button>
            </div>
        </div>
    </div> 

    </div> 
 
</template>


<script>
import ApiService from '../../services/ApiService.js'

export default {

    data() {
        return {
            triage: [],
            detalletriage: [],
            analisisIA: {},    // { [paciente.id]: { prioridad, estado, loading, error } }
            tiempoActual: new Date(), // Ticker reactivo para los countdowns
        }
    },

    mounted() {
        this.obtenerPacientes()

        // ⏱️ Actualiza el ticker cada 30 segundos → Vue re-renderiza los countdowns
        this.intervalTiempo = setInterval(() => {
            this.tiempoActual = new Date()
        }, 30000)
    },

    beforeUnmount() {
        clearInterval(this.intervalTiempo)
    },

    methods: {

        // ─────────────────────────────────────────────
        // 🤖 ANÁLISIS DE IA POR PACIENTE (DeepSeek)
        // ─────────────────────────────────────────────
        async analizarConIA(paciente) {
            const triage = paciente.triages?.[0]
            if (!triage) return

            // ✅ Vue detecta el cambio porque reemplazamos el objeto completo
            this.analisisIA = {
                ...this.analisisIA,
                [paciente.id]: { loading: true }
            }

            try {
                const response = await ApiService.get(`/triage/${paciente.id}/analizar-ia`)
                const data = response.data

                this.analisisIA = {
                    ...this.analisisIA,
                    [paciente.id]: {
                        loading:       false,
                        prioridad:     data.prioridad,
                        estado:        data.estado,
                        justificacion: data.justificacion,
                        error:         data.fuente === 'fallback',
                        fechaTriage:   triage.created_at,
                    }
                }

            } catch (error) {
                console.error(`❌ Error IA paciente ${paciente.id}:`, error)

                this.analisisIA = {
                    ...this.analisisIA,
                    [paciente.id]: {
                        loading:       false,
                        prioridad:     this.mapearEstadoAPrioridad(triage.estado),
                        estado:        triage.estado || 'leve',
                        justificacion: 'IA no disponible',
                        error:         true,
                        fechaTriage:   triage.created_at,
                    }
                }
            }
        },

        // Convierte 'grave/moderado/leve' (BD) → 'rojo/amarillo/verde' (IA)
        mapearEstadoAPrioridad(estado) {
            const mapa = { grave: 'rojo', moderado: 'amarillo', leve: 'verde' }
            return mapa[estado?.toLowerCase()] || 'verde'
        },

        // ─────────────────────────────────────────────
        // 📋 CARGAR PACIENTES Y LANZAR IA
        // ─────────────────────────────────────────────
        async obtenerPacientes() {
            try {
                const response = await ApiService.get('/triage')
                this.triage = response.data
                console.log('triages cargados:', this.triage)

                // 🚀 Analizar cada paciente con IA en paralelo
                const promesas = this.triage.map(p => this.analizarConIA(p))
                await Promise.allSettled(promesas)

            } catch (error) {
                console.error('Error al cargar triage:', error)
            }
        },

        // ─────────────────────────────────────────────
        // 🏷️ BADGE DE PRIORIDAD (ahora con IA)
        // ─────────────────────────────────────────────
        obtenerPrioridad(prioridadIA) {
            if (!prioridadIA) return {
                texto: 'Analizando...',
                clase: 'badge bg-secondary',
                claseCss: 'text-secondary'
            }

            const mapa = {
                rojo:     { texto: '🔴 ROJA — Inmediata',   clase: 'badge bg-danger',   claseCss: 'text-danger fw-bold' },
                naranja:  { texto: '🟠 NARANJA — Muy Urgente', clase: 'badge bg-orange text-dark', claseCss: 'text-warning fw-bold' },
                amarillo: { texto: '🟡 AMARILLA — Urgente', clase: 'badge bg-warning text-dark', claseCss: 'text-warning fw-bold' },
                verde:    { texto: '🟢 VERDE — No Urgente', clase: 'badge bg-success',   claseCss: 'text-success fw-bold' },
            }

            return mapa[prioridadIA.toLowerCase()] || {
                texto: 'Sin clasificar',
                clase: 'badge bg-secondary',
                claseCss: 'text-secondary'
            }
        },

        // ─────────────────────────────────────────────
        // ⏱️ COUNTDOWN REACTIVO (usa this.tiempoActual)
        // ─────────────────────────────────────────────
        obtenerContadorTiempoIa(prioridadIA, fechaRegistro) {
            if (!fechaRegistro || !prioridadIA) return { texto: '...', claseCss: 'text-muted' }

            const inicio = new Date(fechaRegistro)
            const ahora = this.tiempoActual // ← reactivo al setInterval
            const minutosTranscurridos = Math.floor((ahora - inicio) / 60000)

            const limites = { rojo: 10, naranja: 30, amarillo: 60, verde: 120 }
            const limite = limites[prioridadIA.toLowerCase()] ?? 120
            const restante = limite - minutosTranscurridos

            if (restante <= 0) {
                return {
                    texto: `⚠️ Excedido ${Math.abs(restante)} min`,
                    claseCss: 'badge bg-danger text-white fw-bold alert-blink'
                }
            }

            const urgente = restante <= Math.floor(limite * 0.25) // último 25% del tiempo
            return {
                texto: `⏱ ${restante} min restantes`,
                claseCss: urgente
                    ? 'badge bg-warning text-dark fw-bold'
                    : 'badge bg-success text-white fw-bold'
            }
        },

        obtenerLimiteYEstadoEspera(prioridadIA, fechaRegistro) {
            // Mismo cálculo, reutiliza la lógica de arriba
            return this.obtenerContadorTiempoIa(prioridadIA, fechaRegistro)
        },

        // ─────────────────────────────────────────────
        // 👁️ VER DETALLE INDIVIDUAL
        // ─────────────────────────────────────────────
        async obtenerPacientesIndividual(id) {
            try {
                const response = await ApiService.get('/triage/' + id)
                this.detalletriage = {
                    ...response.data,
                    // Inyectar análisis IA en el detalle
                    _ia: this.analisisIA[id] || null
                }
                console.log('detalle triage:', this.detalletriage)
            } catch (error) {
                console.error('Error al cargar detalle:', error)
            }
        },

        guardarEdicion() {
            console.log(this.triageEditar)
        }
    }
}
</script>

