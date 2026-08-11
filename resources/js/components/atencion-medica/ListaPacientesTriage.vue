<template>

<!-- ============================================
     🚨 BANNER DE ALERTAS — Pacientes con tiempo excedido
     Siempre visible, no depende de permisos del navegador
============================================= -->
<div v-if="pacientesVencidos.length" class="alert alert-danger d-flex align-items-center shadow-sm mb-3 alert-blink" role="alert">
    <i class="fas fa-exclamation-triangle me-3" style="font-size:22px;"></i>
    <div>
        <strong>¡Atención doctor! {{ pacientesVencidos.length }} paciente(s) con tiempo de espera excedido:</strong>
        <span v-for="(v, idx) in pacientesVencidos" :key="v.paciente.id">
            {{ v.paciente.nombre }}<span v-if="idx < pacientesVencidos.length - 1">, </span>
        </span>
    </div>
</div>

<!-- Lista -->
<div class="card card-outline card-primary shadow-lg">

    <div class="card-header">
        <h3 class="card-title font-weight-bold">
            Lista de Pacientes TRIAGE
        </h3>
    </div>

    <div class="card-body table-responsive">

        <table class="table table-hover table-bordered align-middle">

            <thead class="bg-light">
                <tr>
                    <th>Prioridad</th>
                    <th>Paciente</th>
                    <th>Motivo / Síntoma</th>
                    <th>Signos Vitales</th>
                    <th>Estado</th>
                    <th>Espera</th>
                    <th>Acción</th>
                </tr>
             </thead>

            <tbody>
                <tr v-for="paciente in triage" :key="paciente.id"
                    :class="{'table-danger': obtenerEspera(analisisIA[paciente.id]?.prioridad,paciente.triages?.[0]?.created_at,paciente.estado_consulta).vencido}">

                    <!-- PRIORIDAD (viene de la IA, con fallback a reglas) -->
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
                            <i v-if="analisisIA[paciente.id]?.error"
                               class="fas fa-exclamation-circle text-warning ms-1"
                               title="IA no disponible, usando reglas de respaldo (BD / signos vitales)">
                            </i>
                        </template>
                    </td>

                    <!-- PACIENTE -->
                    <td class="fw-bold">{{ paciente.nombre }}</td>

                    <!-- MOTIVO / SÍNTOMA -->
                    <td style="max-width:260px;">{{ paciente.triages?.[0]?.sintomas }}</td>

                    <!-- SIGNOS VITALES (agrupados) -->
                    <td style="font-size:12.5px; white-space:nowrap;">
                        <div><i class="fas fa-stethoscope text-info me-1"></i> {{ paciente.triages?.[0]?.presion || 'N/R' }}</div>
                        <div><i class="fas fa-lungs me-1" style="color:#0d9488;"></i> {{ paciente.triages?.[0]?.saturacion ? paciente.triages[0].saturacion + '%' : 'N/R' }}</div>
                        <div><i class="fas fa-thermometer-half text-warning me-1"></i> {{ paciente.triages?.[0]?.temperatura ? paciente.triages[0].temperatura + ' °C' : 'N/R' }}</div>
                    </td>

                    <!-- ESTADO (IA) -->
                    <td>
                        <template v-if="analisisIA[paciente.id]?.loading">
                            <span class="badge bg-secondary">...</span>
                        </template>
                        <template v-else>
                            <span v-if="analisisIA[paciente.id]?.estado === 'grave'" class="badge bg-danger">GRAVE</span>
                            <span v-else-if="analisisIA[paciente.id]?.estado === 'moderado'" class="badge bg-warning text-dark">MODERADO</span>
                            <span v-else class="badge bg-success">LEVE</span>
                            <br>
                            <small class="text-muted fst-italic" style="font-size:11px;">
                                {{ analisisIA[paciente.id]?.justificacion }}
                            </small>
                        </template>
                    </td>

                    <!-- ESPERA (countdown reactivo cada 15s) -->
                    <td>
                        <template v-if="analisisIA[paciente.id]?.loading">
                            <span class="text-muted">—</span>
                        </template>
                        <span
                            v-else
                            :class="obtenerEspera(
                                analisisIA[paciente.id]?.prioridad,
                                paciente.triages?.[0]?.created_at,
                                paciente.estado_consulta
                            ).claseCss"
                        >
                            {{
                                obtenerEspera(
                                    analisisIA[paciente.id]?.prioridad,
                                    paciente.triages?.[0]?.created_at,
                                    paciente.estado_consulta
                                ).texto
                            }}
                        </span>
                    </td>

                    <!-- ACCIÓN -->
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
                            <div class="mb-2"><i class="fas fa-heartbeat text-danger" style="font-size: 28px;"></i></div>
                            <small class="text-uppercase fw-bold text-muted d-block mb-2">Estado General</small>
                            <span v-if="detalletriage.triages?.[0]?.estado === 'grave'" class="badge bg-danger px-3 py-2 text-white fw-bold fs-6">GRAVE</span>
                            <span v-else-if="detalletriage.triages?.[0]?.estado === 'moderado'" class="badge bg-warning px-3 py-2 text-dark fw-bold fs-6">MODERADO</span>
                            <span v-else class="badge bg-success px-3 py-2 text-white fw-bold fs-6">LEVE</span>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="bg-white rounded-4 shadow-sm p-4 h-100 text-center">
                            <div class="mb-2"><i class="fas fa-stethoscope text-info" style="font-size: 28px;"></i></div>
                            <small class="text-uppercase fw-bold text-muted d-block mb-1">Presión Arterial</small>
                            <h4 class="fw-bold text-dark mt-2 mb-0">{{ detalletriage.triages?.[0]?.presion || 'N/R' }}</h4>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="bg-white rounded-4 shadow-sm p-4 h-100 text-center">
                            <div class="mb-2"><i class="fas fa-lungs text-teal" style="font-size: 28px; color: #0d9488;"></i></div>
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
                                    <h5 :class="obtenerEspera(detalletriage._ia?.prioridad ?? mapearEstadoAPrioridad(detalletriage.triages?.[0]?.estado), detalletriage.triages?.[0]?.created_at).claseCss" class="mb-0 mt-1">
                                        {{ obtenerEspera(detalletriage._ia?.prioridad ?? mapearEstadoAPrioridad(detalletriage.triages?.[0]?.estado), detalletriage.triages?.[0]?.created_at).texto }}
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
                </div>
            </div>

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
import AlertasClinicasIA from './AlertasClinicasIA.vue';
import RecomendacionesIA from './RecomendacionesIA.vue';

export default {
    name: 'ListaPacientesTriage',
    components: {
        AlertasClinicasIA,
        RecomendacionesIA
    },
    watch: {
        triages: {
            handler(nuevaLista) {
                this.sincronizarPacientes(nuevaLista)
            }
        }
    },
    props: {
        triages: {
            type: Array,
            default: () => []
        },
        loading: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            triage: [],
            detalletriage: [],
            analisisIA: {},        // { [paciente.id]: { prioridad, estado, loading, error } }
            tiempoActual: new Date(), // Ticker reactivo para los countdowns
            alertasEnviadas: new Set(), // evita repetir la alerta al doctor por el mismo paciente
            filtroFecha: '',

            // ⏱️ Límites de tiempo por nivel de prioridad (en minutos)
            LIMITES_MINUTOS: {
                rojo:     3,    // Nivel 1 — Atención inmediata / Reanimación
                naranja:  15,   // Nivel 2 — Emergencia
                amarillo: 60,   // Nivel 3 — Urgencia moderada
                verde:    120,  // Nivel 4 — Urgencia menor
                azul:     180,  // Nivel 5 — No urgente / derivación
            },
        }
    },

    computed: {
        // Lista reactiva de pacientes cuyo tiempo ya se venció
        // Solo considera consultas que NO estén finalizadas
        pacientesVencidos() {
            return this.triage
                .map(p => ({
                    paciente: p,
                    ia: this.analisisIA[p.id]
                }))
                .filter(({ ia, paciente }) => {

                    if (!ia || ia.loading) return false

                    // Blindaje: si no hay estado_consulta o no hay triage, nunca alertar
                    if (!paciente.estado_consulta || !paciente.triages?.[0]) {
                        return false
                    }

                    if (paciente.estado_consulta === 'finalizada') {
                        return false
                    }

                    const triage0 = paciente.triages?.[0]
                    if (!triage0) return false

                    return this.obtenerEspera(
                        ia.prioridad,
                        triage0.created_at,
                        paciente.estado_consulta
                    ).vencido
                })
        },
    },

    mounted() {
        this.sincronizarPacientes(this.triages)

        if (window.Notification && Notification.permission === 'default') {
            Notification.requestPermission()
        }

        this.intervalTiempo = setInterval(() => {
            this.tiempoActual = new Date()
            this.verificarVencidosYAlertar()
        }, 15000)
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

            this.analisisIA = {
                ...this.analisisIA,
                [paciente.id]: { loading: true }
            }

            try {
                const response = await ApiService.get(`/triage/${paciente.id}/analizar-ia`)
                const data = response.data

                let prioridad = data.prioridad

                // 🛡️ Red de seguridad clínica: si los signos vitales son críticos,
                // la IA NO puede bajar la prioridad por debajo de lo que indican los vitales.
                const prioridadPorVitales = this.evaluarVitalesCriticos(triage)
                if (prioridadPorVitales && this.rango(prioridadPorVitales) < this.rango(prioridad)) {
                    prioridad = prioridadPorVitales
                }

                this.analisisIA = {
                    ...this.analisisIA,
                    [paciente.id]: {
                        loading:       false,
                        prioridad:     prioridad,
                        estado:        data.estado,
                        justificacion: data.justificacion,
                        error:         data.fuente === 'fallback',
                        fechaTriage:   triage.created_at,
                    }
                }

            } catch (error) {
                console.error(`❌ Error IA paciente ${paciente.id}:`, error)

                // Fallback: reglas basadas en BD (estado) + chequeo de vitales críticos
                let prioridad = this.mapearEstadoAPrioridad(triage.estado)
                const prioridadPorVitales = this.evaluarVitalesCriticos(triage)
                if (prioridadPorVitales && this.rango(prioridadPorVitales) < this.rango(prioridad)) {
                    prioridad = prioridadPorVitales
                }

                this.analisisIA = {
                    ...this.analisisIA,
                    [paciente.id]: {
                        loading:       false,
                        prioridad:     prioridad,
                        estado:        triage.estado || 'leve',
                        justificacion: 'IA no disponible, se usaron reglas de respaldo',
                        error:         true,
                        fechaTriage:   triage.created_at,
                    }
                }
            }
        },

        // Orden de gravedad (menor número = más grave) para comparar prioridades
        rango(prioridad) {
            const orden = { rojo: 1, naranja: 2, amarillo: 3, verde: 4, azul: 5 }
            return orden[prioridad?.toLowerCase()] ?? 5
        },

        // 🛡️ Regla dura de seguridad: signos vitales fuera de rango fuerzan una prioridad mínima
        // Ajusta estos umbrales a tu protocolo clínico real.
        evaluarVitalesCriticos(triage) {
            const sat = parseFloat(triage?.saturacion)
            const temp = parseFloat(triage?.temperatura)

            if (!isNaN(sat) && sat < 90) return 'rojo'
            if (!isNaN(temp) && temp >= 40) return 'rojo'
            if (!isNaN(sat) && sat < 94) return 'naranja'
            if (!isNaN(temp) && temp >= 39) return 'naranja'

            return null // los vitales no obligan a subir la prioridad
        },

        // Convierte 'grave/moderado/leve' (BD) → color de prioridad (fallback sin IA)
        mapearEstadoAPrioridad(estado) {
            const mapa = { grave: 'rojo', moderado: 'amarillo', leve: 'verde' }
            return mapa[estado?.toLowerCase()] || 'verde'
        },

        // ─────────────────────────────────────────────
        // 📋 CARGAR PACIENTES Y LANZAR IA
        // ─────────────────────────────────────────────
       sincronizarPacientes(lista) {
            this.triage = lista || []

            // Analiza con IA solo los pacientes que aún no tengan resultado
            // (evita re-analizar y gastar tokens de más en cada cambio de filtro)
            const pendientes = this.triage.filter(p => !this.analisisIA[p.id])
            const promesas = pendientes.map(p => this.analizarConIA(p))

            Promise.allSettled(promesas)
        },
        // ─────────────────────────────────────────────
        // 🏷️ BADGE DE PRIORIDAD
        // ─────────────────────────────────────────────
        obtenerPrioridad(prioridadIA) {
            if (!prioridadIA) return {
                texto: 'Analizando...',
                clase: 'badge bg-secondary',
            }

            const mapa = {
                rojo:     { texto: '🔴 ROJO — Inmediata (Nivel 1)',    clase: 'badge bg-danger' },
                naranja:  { texto: '🟠 NARANJA — Emergencia (Nivel 2)', clase: 'badge bg-orange text-dark' },
                amarillo: { texto: '🟡 AMARILLO — Urgente (Nivel 3)',   clase: 'badge bg-warning text-dark' },
                verde:    { texto: '🟢 VERDE — Urgencia menor (Nivel 4)', clase: 'badge bg-success' },
                azul:     { texto: '🔵 AZUL — No urgente (Nivel 5)',    clase: 'badge bg-primary' },
            }

            return mapa[prioridadIA.toLowerCase()] || {
                texto: 'Sin clasificar',
                clase: 'badge bg-secondary',
            }
        },

        // ─────────────────────────────────────────────
        // ⏱️ ESPERA / COUNTDOWN REACTIVO (usa this.tiempoActual)
        // ─────────────────────────────────────────────
        obtenerEspera(prioridadIA, fechaRegistro, estadoConsulta = 'en_proceso') {

            // ==========================================
            // CONSULTA FINALIZADA
            // ==========================================
            // El estado viene directamente de la BD.
            // Si está finalizada:
            // - No está vencida
            // - No genera alerta
            // - El contador deja de correr
            // ==========================================

            if (estadoConsulta === 'finalizada') {
                return {
                    texto: '✓ Finalizada',
                    claseCss: 'badge bg-success text-white fw-bold',
                    vencido: false,
                    finalizada: true,
                    restante: null
                }
            }


            // ==========================================
            // CONSULTA EXCEDIDA
            // ==========================================
            // Si la BD ya tiene estado "excedido",
            // mostramos directamente el estado excedido.
            // No necesitamos esperar a que el contador
            // vuelva a calcularlo.
            // ==========================================

            if (estadoConsulta === 'excedido') {
                return {
                    texto: '⚠️ Excedido',
                    claseCss: 'badge bg-danger text-white fw-bold alert-blink',
                    vencido: true,
                    finalizada: false,
                    restante: 0
                }
            }


            // ==========================================
            // CONSULTA EN PROCESO
            // ==========================================

            if (!fechaRegistro || !prioridadIA) {
                return {
                    texto: '...',
                    claseCss: 'text-muted',
                    vencido: false,
                    finalizada: false,
                    restante: null
                }
            }


            const inicio = new Date(fechaRegistro)
            const ahora = this.tiempoActual

            const minutosTranscurridos =
                Math.floor((ahora - inicio) / 60000)


            const limite =
                this.LIMITES_MINUTOS[
                    prioridadIA.toLowerCase()
                ] ?? 180


            const restante =
                limite - minutosTranscurridos


            // ==========================================
            // EL TIEMPO SE TERMINÓ
            // ==========================================

            if (restante <= 0) {
                return {
                    texto: `⚠️ Excedido ${Math.abs(restante)} min`,
                    claseCss: 'badge bg-danger text-white fw-bold alert-blink',
                    vencido: true,
                    finalizada: false,
                    restante
                }
            }


            // ==========================================
            // ÚLTIMO 25% DEL TIEMPO
            // ==========================================

            const urgente =
                restante <= Math.max(
                    1,
                    Math.floor(limite * 0.25)
                )


            return {
                texto: `⏱ ${restante} min`,
                claseCss: urgente
                    ? 'badge bg-warning text-dark fw-bold'
                    : 'badge bg-success text-white fw-bold',

                vencido: false,
                finalizada: false,
                restante
            }
        },

        // ─────────────────────────────────────────────
        // 🚨 ALERTA AL DOCTOR CUANDO SE VENCE EL TIEMPO
        // ─────────────────────────────────────────────
        verificarVencidosYAlertar() {
            this.triage.forEach(p => {
                const ia = this.analisisIA[p.id]
                const triage0 = p.triages?.[0]
                if (!ia || ia.loading || !triage0) {
                    return
                }

                // ==========================================
                // SI YA FINALIZÓ, NO HACER NADA
                // ==========================================

                if (p.estado_consulta === 'finalizada') {

                    // Quitamos también la marca de alerta
                    // por si anteriormente había sido excedida.
                    this.alertasEnviadas.delete(p.id)
                    return
                }
                const espera = this.obtenerEspera(
                    ia.prioridad,
                    triage0.created_at,
                    p.estado_consulta
                )
                // ==========================================
                // CONSULTA EXCEDIDA
                // ==========================================

                if (
                    espera.vencido &&
                    !this.alertasEnviadas.has(p.id)
                ) {
                    this.alertasEnviadas.add(p.id)
                    this.notificarDoctor(p, ia)
                }
            })
        },

        notificarDoctor(paciente, ia) {
            this.reproducirSonidoAlerta()

            if (window.Notification && Notification.permission === 'granted') {
                new Notification('⚠️ Tiempo de espera excedido', {
                    body: `${paciente.nombre} — Prioridad ${ia.prioridad?.toUpperCase()} — revisar de inmediato`,
                })
            }

            console.warn(`🚨 ALERTA: ${paciente.nombre} excedió el tiempo límite de triage (${ia.prioridad}).`)
            // El banner rojo de arriba (pacientesVencidos) ya queda visible de forma
            // permanente en la vista, así que la alerta no depende solo de esto.
        },

        // Beep generado con Web Audio API, sin necesidad de un archivo de sonido externo
        reproducirSonidoAlerta() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)()
                const osc = ctx.createOscillator()
                const gain = ctx.createGain()
                osc.type = 'sine'
                osc.frequency.value = 880
                gain.gain.value = 0.15
                osc.connect(gain)
                gain.connect(ctx.destination)
                osc.start()
                setTimeout(() => { osc.stop(); ctx.close() }, 400)
            } catch (e) {
                console.warn('No se pudo reproducir la alerta sonora', e)
            }
        },

        // ─────────────────────────────────────────────
        // 👁️ VER DETALLE INDIVIDUAL
        // ─────────────────────────────────────────────
        async obtenerPacientesIndividual(id) {
            try {
                const response = await ApiService.get('/triage/' + id)
                this.detalletriage = {
                    ...response.data,
                    _ia: this.analisisIA[id] || null
                }
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

<style scoped>
.alert-blink {
    animation: parpadeo 1s infinite;
}
@keyframes parpadeo {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.55; }
}
</style>