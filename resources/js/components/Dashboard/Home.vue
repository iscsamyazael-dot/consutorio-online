<template>
    <div class="dashboard-home">

        <!-- ENCABEZADO -->
        <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2 fade-in-up">
            <div>
                <h2 class="fw-bold mb-1">Panel de administración Médico Online</h2>
                <p class="text-muted mb-0">Resumen de la actividad del consultorio</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <small class="text-muted ultima-actualizacion" v-if="ultimaActualizacionTexto">
                    <i class="fas fa-sync-alt me-1" :class="{ 'fa-spin': actualizando }"></i>
                    {{ ultimaActualizacionTexto }}
                </small>
                <div class="fecha-box">
                    <i class="far fa-calendar me-2"></i>{{ fechaHoy }}
                </div>
            </div>
        </div>

        <!-- TARJETAS DE ESTADISTICAS -->
        <div class="row g-3 mb-4">

            <div class="col-md-6 col-lg-3">
                <div class="stat-card fade-in-up delay-1">
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div class="stat-info">
                        <h6 class="mb-0">Pacientes registrados</h6>
                        <h3 class="fw-bold mb-0 counter-number">{{ resumen.pacientesRegistrados }}</h3>
                        <small class="text-muted">Total de pacientes</small>
                    </div>
                    <a href="/ListaPacientes" class="stat-link text-primary">
                        Ver pacientes <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="stat-card fade-in-up delay-2">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div class="stat-info">
                        <h6 class="mb-0">Triage de hoy</h6>
                        <h3 class="fw-bold mb-0 counter-number">{{ resumen.triageHoy }}</h3>
                        <small class="text-muted">Triages realizados</small>
                    </div>
                    <a href="/TRIAGES" class="stat-link text-success">
                        Ver triages <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="stat-card fade-in-up delay-3">
                    <div class="stat-icon bg-purple-subtle text-purple">
                        <i class="fas fa-notes-medical"></i>
                    </div>
                    <div class="stat-info">
                        <h6 class="mb-0">Consultas de hoy</h6>
                        <h3 class="fw-bold mb-0 counter-number">{{ resumen.consultasHoy }}</h3>
                        <small class="text-muted">Consultas agregadas hoy</small>
                    </div>
                    <a href="/ListaConsultas" class="stat-link text-purple">
                        Ver consultas <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="stat-card fade-in-up delay-4">
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-info">
                        <h6 class="mb-0">Pendientes de atención</h6>
                        <h3 class="fw-bold mb-0 counter-number">{{ resumen.pendientes }}</h3>
                        <small class="text-muted">Pacientes pendientes</small>
                    </div>
                    <a href="/ListaConsultas" class="stat-link text-warning">
                        Ver pendientes <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>

        </div>

        <!-- PROXIMAS CONSULTAS + ALERTAS CLINICAS -->
        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <div class="panel-card fade-in-up delay-2">
                    <div class="panel-header">
                        <h6 class="fw-bold mb-0">
                            <i class="far fa-calendar-alt me-2 text-primary"></i>
                            Próximas consultas de hoy
                        </h6>
                        <a href="/Agenda" class="small">Ver agenda completa</a>
                    </div>

                    <transition name="fade" mode="out-in">
                        <div v-if="cargandoConsultas" key="loading">
                            <div class="skeleton-row" v-for="n in 3" :key="n">
                                <div class="skeleton skeleton-hora"></div>
                                <div class="flex-grow-1">
                                    <div class="skeleton skeleton-line skeleton-line-title"></div>
                                    <div class="skeleton skeleton-line skeleton-line-sub"></div>
                                </div>
                                <div class="skeleton skeleton-badge"></div>
                            </div>
                        </div>

                        <div v-else-if="proximasConsultas.length === 0" key="empty" class="text-center text-muted py-4 empty-state">
                            <svg class="empty-illustration" width="92" height="92" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                                <rect x="20" y="28" width="80" height="72" rx="10" fill="#eaf3ff"/>
                                <rect x="20" y="28" width="80" height="20" rx="10" fill="#74c0fc"/>
                                <rect x="34" y="16" width="8" height="20" rx="4" fill="#1976d2"/>
                                <rect x="78" y="16" width="8" height="20" rx="4" fill="#1976d2"/>
                                <circle cx="60" cy="72" r="22" fill="#ffffff" stroke="#74c0fc" stroke-width="3"/>
                                <path d="M50 72 L57 79 L71 63" stroke="#1976d2" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <p class="mb-0 mt-2">No hay consultas programadas para hoy.</p>
                        </div>

                        <div v-else key="list" class="lista-scroll">
                            <div class="consulta-item stagger-item" v-for="(c, i) in proximasConsultas" :key="c.id" :style="{ '--i': i }">
                                <div class="hora">{{ c.hora }}</div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ c.paciente }}</h6>
                                    <small class="text-muted">{{ c.tipo }}</small>
                                </div>
                                <span :class="'badge estado-' + c.estadoClase">
                                    {{ c.estado }}
                                </span>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="panel-card fade-in-up delay-3">
                    <div class="panel-header">
                        <h6 class="fw-bold mb-0 d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                            Alertas clínicas
                            <transition name="fade">
                                <span v-if="notificacionNueva" class="badge-nueva ms-2">Nueva</span>
                            </transition>
                        </h6>
                        <a href="/TRIAGES" class="small">Ver todas</a>
                    </div>

                    <transition name="fade" mode="out-in">
                        <div v-if="cargandoAlertas" key="loading">
                            <div class="skeleton-alert" v-for="n in 2" :key="n">
                                <div class="skeleton skeleton-circle"></div>
                                <div class="flex-grow-1">
                                    <div class="skeleton skeleton-line skeleton-line-title"></div>
                                    <div class="skeleton skeleton-line skeleton-line-sub" style="width:75%"></div>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="alertasClinicas.length === 0" key="empty" class="text-center text-muted py-4 empty-state">
                            <svg class="empty-illustration" width="92" height="92" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                                <path d="M60 14 L96 26 V54 C96 80 80 98 60 106 C40 98 24 80 24 54 V26 Z" fill="#e6f9ef" stroke="#40c057" stroke-width="3"/>
                                <path d="M46 60 L56 70 L76 46" stroke="#2f9e44" stroke-width="5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <p class="mb-0 mt-2">Sin alertas por ahora.</p>
                        </div>

                        <div v-else key="list">
                            <div
                                class="alerta-item stagger-item"
                                v-for="(a, i) in alertasClinicasFiltradas"
                                :key="i"
                                :class="'alerta-' + a.nivel"
                                :style="{ '--i': i }"
                            >
                                <span class="alerta-icon-wrap">
                                    <i :class="a.icono"></i>
                                </span>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ a.titulo }}</h6>
                                    <small class="text-muted">{{ a.descripcion }}</small>
                                </div>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>
        </div>

        <!-- FLUJO DE ATENCION + ACTIVIDAD RECIENTE -->
        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <div class="panel-card fade-in-up delay-3">
                    <div class="panel-header">
                        <h6 class="fw-bold mb-0">
                            <i class="fas fa-chart-line me-2 text-primary"></i>
                            Flujo de atención del día
                        </h6>
                    </div>

                    <div class="flujo-row">
                        <div class="flujo-paso">
                            <div class="flujo-icon flujo-icon-registro" :class="{ 'flujo-activo': flujo.registrados > 0 }">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <small class="text-muted d-block mt-2">Registrados</small>
                            <h4 class="fw-bold mb-0">{{ flujo.registrados }}</h4>
                        </div>
                        <div class="flujo-linea"><span class="flujo-linea-progreso" :style="{ width: (flujo.registrados ? Math.min(100, (flujo.triageRealizado / flujo.registrados) * 100) : 0) + '%' }"></span></div>
                        <div class="flujo-paso">
                            <div class="flujo-icon flujo-icon-triage" :class="{ 'flujo-activo': flujo.triageRealizado > 0 }">
                                <i class="fas fa-heartbeat"></i>
                            </div>
                            <small class="text-muted d-block mt-2">Triage realizado</small>
                            <h4 class="fw-bold mb-0">{{ flujo.triageRealizado }}</h4>
                        </div>
                        <div class="flujo-linea"><span class="flujo-linea-progreso" :style="{ width: (flujo.triageRealizado ? Math.min(100, (flujo.enConsulta / flujo.triageRealizado) * 100) : 0) + '%' }"></span></div>
                        <div class="flujo-paso">
                            <div class="flujo-icon flujo-icon-consulta" :class="{ 'flujo-activo': flujo.enConsulta > 0 }">
                                <i class="fas fa-stethoscope"></i>
                            </div>
                            <small class="text-muted d-block mt-2">En consulta</small>
                            <h4 class="fw-bold mb-0">{{ flujo.enConsulta }}</h4>
                        </div>
                        <div class="flujo-linea"><span class="flujo-linea-progreso" :style="{ width: (flujo.enConsulta ? Math.min(100, (flujo.finalizados / flujo.enConsulta) * 100) : 0) + '%' }"></span></div>
                        <div class="flujo-paso">
                            <div class="flujo-icon flujo-icon-final" :class="{ 'flujo-activo': flujo.finalizados > 0 }">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <small class="text-muted d-block mt-2">Finalizados</small>
                            <h4 class="fw-bold mb-0">{{ flujo.finalizados }}</h4>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Progreso general del día</small>
                            <small class="fw-bold">{{ flujo.progreso }}%</small>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" :style="{ width: flujo.progreso + '%' }">
                                <span class="progress-shine"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="panel-card fade-in-up delay-4">
                    <div class="panel-header">
                        <h6 class="fw-bold mb-0">
                            <i class="far fa-clock me-2 text-primary"></i>
                            Actividad reciente
                        </h6>
                    </div>

                    <transition name="fade" mode="out-in">
                        <div v-if="cargandoActividad" key="loading">
                            <div class="skeleton-row" v-for="n in 4" :key="n">
                                <div class="skeleton skeleton-dot"></div>
                                <div class="flex-grow-1">
                                    <div class="skeleton skeleton-line skeleton-line-title"></div>
                                    <div class="skeleton skeleton-line skeleton-line-sub"></div>
                                </div>
                                <div class="skeleton skeleton-time"></div>
                            </div>
                        </div>

                        <div v-else-if="actividadReciente.length === 0" key="empty" class="text-center text-muted py-4">
                            <i class="far fa-clock d-block mb-2 empty-icon"></i>
                            Sin actividad registrada hoy.
                        </div>

                        <div v-else key="list">
                            <div class="timeline-item stagger-item" v-for="(act, i) in actividadReciente" :key="i" :style="{ '--i': i }">
                                <span class="timeline-dot" :class="'dot-' + act.tipo"></span>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ act.titulo }}</h6>
                                    <small class="text-muted">{{ act.detalle }}</small>
                                </div>
                                <small class="text-muted">{{ act.hora }}</small>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>
        </div>

        <!-- ACCESOS RAPIDOS -->
        <div class="panel-card fade-in-up delay-4">
            <div class="panel-header">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-bolt me-2 text-warning"></i>
                    Accesos rápidos
                </h6>
            </div>

            <div class="accesos-grid">
                <a v-for="(acc, i) in accesosRapidos"
                   :key="i"
                   :href="acc.url"
                   class="acceso-rapido stagger-item"
                   :class="'acceso-' + acc.color"
                   :style="{ '--i': i }">
                    <i :class="acc.icono"></i>
                    <div>
                        <h6 class="mb-0">{{ acc.titulo }}</h6>
                        <small class="text-muted">{{ acc.subtitulo }}</small>
                    </div>
                </a>
            </div>
        </div>

    </div>
</template>

<script>
import ApiService from '../../services/ApiService.js'
import axios from 'axios'
// Event bus para refrescar el dashboard al instante cuando otra vista
// (ej. ConsultaClinica.vue) finaliza o agrega una consulta, en vez de
// esperar hasta 60s al próximo setInterval.
import eventBus from '../../utils/eventBus.js'

export default {
    name: 'Home',

    data() {
        return {
            resumen: {
                pacientesRegistrados: 0,
                triageHoy: 0,
                consultasHoy: 0,
                pendientes: 0
            },
            proximasConsultas: [],
            cargandoConsultas: true,

            alertasClinicas: [],
            cargandoAlertas: true,
            alertaAltaPrevias: 0,
            primeraCargaAlertas: true,
            notificacionNueva: false,

            flujo: {
                registrados: 0,
                triageRealizado: 0,
                enConsulta: 0,
                finalizados: 0,
                progreso: 0
            },

            actividadReciente: [],
            cargandoActividad: true,

            accesosRapidos: [
                { titulo: 'Registrar paciente', subtitulo: 'Nuevo paciente', icono: 'fas fa-user-plus', url: '/PacienteNuevo', color: 'primary' },
                { titulo: 'Nuevo triage', subtitulo: 'Triage rápido', icono: 'fas fa-heartbeat', url: '/TRIAGES', color: 'success' },
                { titulo: 'Nueva consulta', subtitulo: 'Iniciar consulta', icono: 'fas fa-notes-medical', url: '/NuevaConsulta', color: 'purple' },
                { titulo: 'Ver agenda', subtitulo: 'Agenda completa', icono: 'far fa-calendar-alt', url: '/Agenda', color: 'warning' }
            ],

            intervaloRefresco: null,

            // Soporte para el indicador "Actualizado hace X"
            actualizando: false,
            ultimaActualizacion: null,
            tickerReloj: null,
            tiempoCreacionAlertas: null // timestamp de la última vez que se cargaron alertas, para evitar notificaciones repetidas al refrescar
        }
    },

    computed: {
        fechaHoy() {
            const hoy = new Date();
            return hoy.toLocaleDateString('es-MX', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
        },

        // Texto relativo ("hace 5s", "hace 2 min") recalculado cada vez
        // que cambia ultimaActualizacion o el tickerReloj (ver mounted).
        ultimaActualizacionTexto() {
            if (!this.ultimaActualizacion) return '';
            void this.tickerReloj;

            const segundos = Math.floor((Date.now() - this.ultimaActualizacion.getTime()) / 1000);
            if (segundos < 5) return 'Actualizado ahora';
            if (segundos < 60) return `Actualizado hace ${segundos}s`;
            const minutos = Math.floor(segundos / 60);
            return `Actualizado hace ${minutos} min`;
        },

        // Oculta únicamente la alerta de prioridad alta después de un
        // tiempo, para no dejarla parpadeando indefinidamente.
        alertasClinicasFiltradas() {
            void this.tickerReloj; // Mantiene el conteo reactivo cada segundo
            if (!this.tiempoCreacionAlertas) return this.alertasClinicas;

            const ahora = Date.now();
            const TIEMPO_EXPIRACION = 10 * 1000; // Ponlo en 10 segundos para probar rápido
            const expirado = (ahora - this.tiempoCreacionAlertas) >= TIEMPO_EXPIRACION;

            return this.alertasClinicas.filter(alerta => {
                const esPrioridadAlta =
                    (alerta.nivel && alerta.nivel.toLowerCase() === 'danger') ||
                    (alerta.titulo && alerta.titulo.toLowerCase().includes('prioridad alta'));

                if (esPrioridadAlta && expirado) {
                    return false;
                }

                return true;
            });
        }
    },

    mounted() {
        this.cargarResumen();

        this.intervaloRefresco = setInterval(() => {
            this.cargarResumen();
        }, 60000);

        // Refresco inmediato cuando cualquier vista avisa que finalizó (o
        // agregó) una consulta.
        eventBus.on('consulta-finalizada', this.cargarResumen);
        eventBus.on('consulta-actualizada', this.cargarResumen);

        // Ticker cada segundo solo para refrescar el texto de "Actualizado
        // hace X" sin volver a pedir datos al servidor.
        this.tickerReloj = 0;
        this._intervaloTicker = setInterval(() => {
            this.tickerReloj++;
        }, 1000);
    },

    beforeUnmount() {
        if (this.intervaloRefresco) clearInterval(this.intervaloRefresco);
        if (this._intervaloTicker) clearInterval(this._intervaloTicker);

        eventBus.off('consulta-finalizada', this.cargarResumen);
        eventBus.off('consulta-actualizada', this.cargarResumen);
    },

    methods: {

        async cargarResumen() {
            this.actualizando = true;

            const [pacientes, triage, citas, medicamentos, consultasResumen] = await Promise.all([
                this.obtenerPacientes(),
                this.obtenerTriage(),
                this.obtenerCitas(),
                this.obtenerMedicamentos(),
                this.obtenerResumenConsultas()
            ]);

            this.procesarTarjetasSuperiores(pacientes, triage, citas, consultasResumen);
            this.procesarProximasConsultas(citas);
            this.procesarAlertasClinicas(triage, citas, medicamentos);
            this.procesarFlujoAtencion(pacientes, triage, citas, consultasResumen);
            this.procesarActividadReciente(pacientes, triage, citas);

            this.ultimaActualizacion = new Date();
            this.actualizando = false;
        },

        // ─── OBTENCIÓN DE DATOS BASE (una sola vez por refresh) ───────────

        async obtenerPacientes() {
            try {
                const response = await ApiService.get('/pacientes');
                return response.data || [];
            } catch (error) {
                console.error('Error al cargar pacientes:', error);
                return [];
            }
        },

        async obtenerTriage() {
            try {
                const response = await ApiService.get('/triage');
                return response.data || [];
            } catch (error) {
                console.error('Error al cargar triage:', error);
                return [];
            }
        },

        async obtenerCitas() {
            try {
                const response = await axios.get('/api/citas');
                return response.data || [];
            } catch (error) {
                console.error('Error al cargar citas:', error);
                return [];
            }
        },

        async obtenerMedicamentos() {
            try {
                const response = await ApiService.get('medicamentos');
                return response.data || [];
            } catch (error) {
                console.error('Error al cargar medicamentos:', error);
                return [];
            }
        },

        // Cifras reales de la tabla `consultas` (no `citas`).
        // GET /api/dashboard/consultas-hoy -> { usadas_hoy, finalizadas_hoy, pendientes_hoy }
        // (ver DashboardController@consultasHoy). Las 3 tarjetas de arriba
        // relacionadas con consultas salen de aquí, así siempre cuadran
        // entre sí: usadas_hoy = finalizadas_hoy + pendientes_hoy.
        async obtenerResumenConsultas() {
            try {
                const response = await axios.get('/api/dashboard/consultas-hoy');
                return {
                    usadas_hoy: response.data?.usadas_hoy || 0,
                    finalizadas_hoy: response.data?.finalizadas_hoy || 0,
                    pendientes_hoy: response.data?.pendientes_hoy || 0
                };
            } catch (error) {
                console.error('Error al cargar resumen de consultas:', error);
                return { usadas_hoy: 0, finalizadas_hoy: 0, pendientes_hoy: 0 };
            }
        },

        // ─── UTILIDADES DE FECHA (hora LOCAL, no UTC) ──────────────────

        formatoFechaLocal(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },

        esHoy(fecha) {
            if (!fecha) return false;

            const soloFecha = /^\d{4}-\d{2}-\d{2}$/.test(String(fecha).trim());
            let fechaComparar;

            if (soloFecha) {
                fechaComparar = fecha;
            } else {
                const d = new Date(fecha);
                if (isNaN(d.getTime())) return false;
                fechaComparar = this.formatoFechaLocal(d);
            }

            return fechaComparar === this.formatoFechaLocal(new Date());
        },

        normalizarEstado(estado) {
            return (estado || '').toLowerCase().trim().replace(/\s+/g, '_');
        },

        ultimoTriage(p) {
            if (!p?.triages?.length) return null;
            return p.triages[0];
        },

        triageEsDeHoy(triageRecord) {
            if (!triageRecord) return false;
            return this.esHoy(triageRecord.created_at) || this.esHoy(triageRecord.updated_at);
        },

        nombrePacienteCita(c) {
            if (!c.paciente) return c.paciente_nombre || 'Paciente';
            if (c.paciente.nombre && (c.paciente.apellido_paterno || c.paciente.apellido_materno)) {
                return [c.paciente.nombre, c.paciente.apellido_paterno, c.paciente.apellido_materno]
                    .filter(Boolean)
                    .join(' ');
            }
            return c.paciente.nombre || 'Paciente';
        },

        idPacienteCita(c) {
            return c.paciente?.id ?? c.paciente_id ?? null;
        },

        // ─── TARJETAS SUPERIORES ────────────────────────────────────────

        procesarTarjetasSuperiores(pacientes, triage, citas, consultasResumen) {
            this.resumen.pacientesRegistrados = pacientes.length;

            this.resumen.triageHoy = triage.filter(p =>
                this.triageEsDeHoy(this.ultimoTriage(p))
            ).length;

            this.resumen.consultasHoy = consultasResumen.usadas_hoy;

            // CORREGIDO: "Pendientes" debe coincidir con la lista de
            // espera real que se ve en ConsultaClinica.vue
            // (pacientesFiltrados), la cual sale de la tabla `citas`, NO
            // de `consultas`. Son dos tablas distintas con datos propios;
            // usar consultasResumen.pendientes_hoy aquí hacía que la
            // tarjeta mostrara un número que no existía en ninguna lista
            // real que el usuario pudiera revisar.
            const citasHoy = citas.filter(c =>
                this.esHoy(c.fecha || c.created_at)
            );

            this.resumen.pendientes = citasHoy.filter(c => {
                const estado = this.normalizarEstado(c.estado);
                return estado !== 'finalizada' && estado !== 'completada' && estado !== 'cancelada';
            }).length;
        },

        // ─── PROXIMAS CONSULTAS (citas de hoy) ──────────────────────────

        procesarProximasConsultas(citas) {
            this.cargandoConsultas = false;

            const activasHoy = citas
                .filter(c => this.esHoy(c.fecha || c.created_at))
                .filter(c => {
                    const estado = this.normalizarEstado(c.estado);
                    return estado !== 'finalizada' && estado !== 'completada' && estado !== 'cancelada';
                })
                .sort((a, b) => (a.hora || '').localeCompare(b.hora || ''));

            this.proximasConsultas = activasHoy
                .slice(0, 10)
                .map((c, i) => ({
                    id: c.id,
                    hora: c.hora || '--:--',
                    paciente: this.nombrePacienteCita(c),
                    tipo: c.especialidad?.nombre || c.tipo || 'Consulta general',
                    estado: i === 0 ? 'En proceso' : 'Agendada',
                    estadoClase: i === 0 ? 'en-proceso' : 'agendada'
                }));
        },

        // ─── ALERTAS CLINICAS ───────────────────────────────────────────

        procesarAlertasClinicas(triage, citas, medicamentos) {
            this.cargandoAlertas = false;
            this.tiempoCreacionAlertas = Date.now();
            const alertas = [];

            const pacientesFinalizadosHoy = new Set(
                citas
                    .filter(c => this.esHoy(c.fecha || c.created_at))
                    .filter(c => {
                        const estado = this.normalizarEstado(c.estado);
                        return estado === 'finalizada' || estado === 'completada';
                    })
                    .map(c => c.paciente_id || c.id_paciente)
                    .filter(id => id !== null && id !== undefined)
                    .map(id => String(id))
            );

            const graves = triage.filter(p => {
                const t = this.ultimoTriage(p);
                const esGraveHoy = this.triageEsDeHoy(t) && (t?.estado || '').toLowerCase() === 'grave';
                return esGraveHoy && !pacientesFinalizadosHoy.has(String(p.id));
            });

            if (graves.length > 0) {
                alertas.push({
                    nivel: 'alta',
                    icono: 'fas fa-exclamation-triangle',
                    titulo: 'Prioridad alta',
                    descripcion: `${graves.length} paciente${graves.length > 1 ? 's' : ''} requiere${graves.length > 1 ? 'n' : ''} valoración médica prioritaria.`
                });
            }

            const DIAS_LIMITE_CADUCIDAD = 30;
            const hoyFecha = new Date();
            const limiteFecha = new Date();
            limiteFecha.setDate(limiteFecha.getDate() + DIAS_LIMITE_CADUCIDAD);

            let sinStock = 0, stockCritico = 0, porCaducar = 0, caducados = 0;

            medicamentos.forEach(med => {
                const inv = med.inventario;
                if (!inv) return;

                if (inv.stock_actual == 0) {
                    sinStock++;
                } else if (inv.stock_actual <= inv.stock_minimo) {
                    stockCritico++;
                }

                if (inv.fecha_caducidad) {
                    const fechaCad = new Date(inv.fecha_caducidad);
                    if (fechaCad >= hoyFecha && fechaCad <= limiteFecha) {
                        porCaducar++;
                    } else if (fechaCad < hoyFecha) {
                        caducados++;
                    }
                }
            });

            if (caducados > 0 || stockCritico > 0 || sinStock > 0) {
                const partes = [];
                if (caducados > 0) partes.push(`${caducados} caducado${caducados > 1 ? 's' : ''}`);
                if (stockCritico > 0) partes.push(`${stockCritico} con stock crítico`);
                if (sinStock > 0) partes.push(`${sinStock} sin existencia`);

                alertas.push({
                    nivel: 'alta',
                    icono: 'fas fa-pills',
                    titulo: 'Alertas de farmacia',
                    descripcion: `Medicamentos: ${partes.join(', ')}.`
                });
            }

            if (porCaducar > 0) {
                alertas.push({
                    nivel: 'media',
                    icono: 'fas fa-clock',
                    titulo: 'Medicamentos por caducar',
                    descripcion: `${porCaducar} medicamento${porCaducar > 1 ? 's' : ''} próximo${porCaducar > 1 ? 's' : ''} a caducar en los próximos ${DIAS_LIMITE_CADUCIDAD} días.`
                });
            }

            const finalizadasHoy = citas.filter(c => {
                const estado = this.normalizarEstado(c.estado);
                return this.esHoy(c.fecha || c.created_at) &&
                       (estado === 'finalizada' || estado === 'completada');
            });
            if (finalizadasHoy.length > 0) {
                alertas.push({
                    nivel: 'info',
                    icono: 'fas fa-info-circle',
                    titulo: 'Información',
                    descripcion: `${finalizadasHoy.length} consulta${finalizadasHoy.length > 1 ? 's' : ''} finalizada${finalizadasHoy.length > 1 ? 's' : ''} el día de hoy.`
                });
            }

            const altasActuales = alertas.filter(a => a.nivel === 'alta').length;
            if (!this.primeraCargaAlertas && altasActuales > this.alertaAltaPrevias) {
                this.dispararNotificacionAlerta();
            }
            this.alertaAltaPrevias = altasActuales;
            this.primeraCargaAlertas = false;

            this.alertasClinicas = alertas;
        },

        dispararNotificacionAlerta() {
            this.notificacionNueva = true;
            this.reproducirSonidoAlerta();
            setTimeout(() => { this.notificacionNueva = false; }, 4000);
        },

        reproducirSonidoAlerta() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(660, ctx.currentTime + 0.15);
                gain.gain.setValueAtTime(0.0001, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.12, ctx.currentTime + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.35);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.4);
            } catch (e) {
                // Audio no disponible; se ignora silenciosamente.
            }
        },

        // ─── FLUJO DE ATENCION DEL DIA ──────────────────────────────────

        procesarFlujoAtencion(pacientes, triage, citas, consultasResumen) {
            const registrados = pacientes.filter(p => this.esHoy(p.created_at)).length;
            const triageRealizado = triage.filter(p => this.triageEsDeHoy(this.ultimoTriage(p))).length;

            const citasHoy = citas.filter(c => this.esHoy(c.fecha || c.created_at));
            const enConsulta = citasHoy.filter(c =>
                this.normalizarEstado(c.estado) === 'en_proceso'
            ).length;

            // Antes se contaban las citas de hoy en estado
            // finalizada/completada, pero el número real de consultas
            // finalizadas hoy (tabla `consultas`) es distinto. Se usa el
            // mismo resumen de /api/dashboard/consultas-hoy que ya
            // alimenta la tarjeta "Consultas de hoy".
            const finalizados = consultasResumen.finalizadas_hoy;

            const totalPasos = Math.max(registrados, 1);
            const progreso = Math.min(100, Math.round((finalizados / totalPasos) * 100));

            this.flujo = { registrados, triageRealizado, enConsulta, finalizados, progreso };
        },

        // ─── ACTIVIDAD RECIENTE ─────────────────────────────────────────

        procesarActividadReciente(pacientes, triage, citas) {
            this.cargandoActividad = false;
            const eventos = [];

            pacientes
                .filter(p => this.esHoy(p.created_at))
                .forEach(p => eventos.push({
                    tipo: 'paciente',
                    titulo: 'Nuevo paciente registrado',
                    detalle: `${p.nombre || ''} ${p.apellido_paterno || ''}`.trim(),
                    fecha: p.created_at
                }));

            triage
                .filter(p => this.triageEsDeHoy(this.ultimoTriage(p)))
                .forEach(p => {
                    const t = this.ultimoTriage(p);
                    eventos.push({
                        tipo: 'triage',
                        titulo: 'Triage registrado/actualizado',
                        detalle: p.nombre || '',
                        fecha: t.updated_at || t.created_at
                    });
                });

            citas
                .filter(c => this.esHoy(c.fecha || c.created_at))
                .forEach(c => {
                    const estado = this.normalizarEstado(c.estado);
                    eventos.push({
                        tipo: (estado === 'finalizada' || estado === 'completada') ? 'consulta' : 'inicio',
                        titulo: (estado === 'finalizada' || estado === 'completada')
                            ? 'Consulta finalizada'
                            : 'Consulta iniciada',
                        detalle: this.nombrePacienteCita(c),
                        fecha: c.updated_at || c.created_at
                    });
                });

            this.actividadReciente = eventos
                .sort((a, b) => new Date(b.fecha) - new Date(a.fecha))
                .slice(0, 6)
                .map(e => ({
                    ...e,
                    hora: e.fecha
                        ? new Date(e.fecha).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })
                        : ''
                }));
        }
    }
}
</script>

<style scoped>

/* ─── ANIMACIONES DE ENTRADA ─────────────────────────────────────── */

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

.fade-in-up {
    animation: fadeInUp .55s cubic-bezier(.22,1,.36,1) both;
}

.delay-1 { animation-delay: .05s; }
.delay-2 { animation-delay: .12s; }
.delay-3 { animation-delay: .19s; }
.delay-4 { animation-delay: .26s; }

.stagger-item {
    animation: fadeInUp .4s cubic-bezier(.22,1,.36,1) both;
    animation-delay: calc(var(--i, 0) * .06s);
}

.fade-enter-active, .fade-leave-active {
    transition: opacity .25s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

.empty-icon {
    font-size: 28px;
    opacity: .35;
}

/* ─── SKELETON LOADERS ───────────────────────────────────────────── */

.skeleton {
    background: linear-gradient(90deg, #eef0f3 25%, #f7f8fa 37%, #eef0f3 63%);
    background-size: 400% 100%;
    animation: skeletonLoading 1.4s ease infinite;
    border-radius: 6px;
}

@keyframes skeletonLoading {
    0% { background-position: 100% 50%; }
    100% { background-position: 0 50%; }
}

.skeleton-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 10px 8px;
}

.skeleton-alert {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px;
}

.skeleton-hora { width: 42px; height: 14px; flex-shrink: 0; }
.skeleton-line { height: 12px; margin-bottom: 8px; }
.skeleton-line:last-child { margin-bottom: 0; }
.skeleton-line-title { width: 55%; height: 14px; }
.skeleton-line-sub { width: 38%; }
.skeleton-badge { width: 74px; height: 24px; border-radius: 20px; flex-shrink: 0; }
.skeleton-circle { width: 32px; height: 32px; border-radius: 10px; flex-shrink: 0; }
.skeleton-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.skeleton-time { width: 38px; height: 12px; flex-shrink: 0; }

/* ─── EMPTY STATES ILUSTRADOS ────────────────────────────────────── */

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.empty-illustration {
    animation: floatY 3s ease-in-out infinite;
}

@keyframes floatY {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-6px); }
}

/* ─── BADGE "NUEVA" (alertas) ────────────────────────────────────── */

.badge-nueva {
    background: #dc2626;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: .4px;
    animation: badgeNuevaPop .4s cubic-bezier(.34,1.56,.64,1);
}

@keyframes badgeNuevaPop {
    0% { transform: scale(0); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

/* ─── INDICADOR "ACTUALIZADO HACE X" ─────────────────────── */

.ultima-actualizacion {
    white-space: nowrap;
}

/* ─── TARJETAS DE ESTADISTICAS ───────────────────────────────────── */

.stat-card {
    background: white;
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(15,23,42,.05);
    height: 100%;
    display: flex;
    flex-direction: column;
    gap: 10px;
    transition: transform .28s cubic-bezier(.22,1,.36,1), box-shadow .28s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 14px 28px rgba(0,0,0,.1);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    transition: transform .35s cubic-bezier(.34,1.56,.64,1);
}

.stat-card:hover .stat-icon {
    transform: scale(1.12) rotate(-6deg);
}

.bg-purple-subtle { background: #f1e6fb; }
.text-purple { color: #7b1fa2; }

.counter-number {
    transition: color .2s ease;
}

.stat-link {
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    margin-top: auto;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: gap .2s ease;
}

.stat-link i {
    font-size: 11px;
    transition: transform .2s ease;
}

.stat-link:hover i {
    transform: translateX(3px);
}

/* ─── PANELES ─────────────────────────────────────────────────────── */

.panel-card {
    background: white;
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(15,23,42,.05);
    height: 100%;
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.lista-scroll {
    max-height: 232px;
    overflow-y: auto;
    padding-right: 4px;
    scrollbar-width: thin;
    scrollbar-color: #d1d5db transparent;
}

.lista-scroll::-webkit-scrollbar {
    width: 6px;
}

.lista-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.lista-scroll::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 10px;
}

.lista-scroll::-webkit-scrollbar-thumb:hover {
    background: #b3b8c1;
}

.consulta-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 10px 8px;
    border-top: 1px solid #f0f0f0;
    border-radius: 10px;
    transition: background .2s ease;
}

.consulta-item:hover {
    background: #f8fafc;
}

.consulta-item:first-of-type {
    border-top: none;
}

.hora {
    font-weight: 700;
    color: #1976d2;
    width: 55px;
}

.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 12px;
    transition: transform .2s ease;
}

.consulta-item:hover .badge {
    transform: scale(1.05);
}

.estado-finalizada {
    background: #d1fae5;
    color: #059669;
}

.estado-en-proceso {
    background: #fef3c7;
    color: #b45309;
    animation: badgePulse 2s ease-in-out infinite;
}

@keyframes badgePulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(180,83,9,.25); }
    50% { box-shadow: 0 0 0 5px rgba(180,83,9,0); }
}

.estado-agendada {
    background: #e7f0ff;
    color: #1d4ed8;
}

.estado-cancelada {
    background: #fee2e2;
    color: #dc2626;
}

.fecha-box {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 10px 16px;
    font-weight: 500;
    color: #495057;
    transition: box-shadow .2s ease;
}

.fecha-box:hover {
    box-shadow: 0 3px 10px rgba(0,0,0,.06);
}

/* ─── ALERTAS CLINICAS ────────────────────────────────────────────── */

.alerta-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px;
    border-radius: 14px;
    margin-bottom: 10px;
    transition: transform .2s ease;
    position: relative;
    overflow: hidden;
}

.alerta-item:hover {
    transform: translateX(3px);
}

.alerta-icon-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 10px;
    flex-shrink: 0;
    background: rgba(255,255,255,.55);
}

.alerta-alta {
    background: #fef2f2;
    color: #dc2626;
    animation: alertaLatido 1.8s ease-in-out infinite;
    box-shadow: 0 0 0 0 rgba(220,38,38,.35);
}

@keyframes alertaLatido {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(220,38,38,.35);
        transform: scale(1);
    }
    50% {
        box-shadow: 0 0 0 8px rgba(220,38,38,0);
        transform: scale(1.015);
    }
}

.alerta-alta .alerta-icon-wrap i {
    animation: iconoTemblor 1.8s ease-in-out infinite;
}

@keyframes iconoTemblor {
    0%, 100% { transform: rotate(0); }
    10% { transform: rotate(-10deg); }
    20% { transform: rotate(9deg); }
    30% { transform: rotate(-6deg); }
    40% { transform: rotate(4deg); }
    50%, 100% { transform: rotate(0); }
}

.alerta-media {
    background: #fffbeb;
    color: #b45309;
    animation: alertaResalte 2.6s ease-in-out infinite;
}

@keyframes alertaResalte {
    0%, 100% { box-shadow: 0 0 0 0 rgba(180,83,9,.25); }
    50% { box-shadow: 0 0 0 6px rgba(180,83,9,0); }
}

.alerta-info {
    background: #eff6ff;
    color: #1d4ed8;
}

/* ─── FLUJO DE ATENCION ──────────────────────────────────────────── */

.flujo-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    text-align: center;
}

.flujo-paso {
    flex: 1;
}

.flujo-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    margin: 0 auto;
    transition: transform .3s cubic-bezier(.34,1.56,.64,1), box-shadow .3s ease;
}

.flujo-icon-registro { background: linear-gradient(135deg, #74c0fc, #1971c2); color: #fff; }
.flujo-icon-triage { background: linear-gradient(135deg, #69db7c, #2f9e44); color: #fff; }
.flujo-icon-consulta { background: linear-gradient(135deg, #da77f2, #9c36b5); color: #fff; }
.flujo-icon-final { background: linear-gradient(135deg, #63e6be, #0ca678); color: #fff; }

.flujo-activo {
    box-shadow: 0 4px 14px rgba(0,0,0,.18);
    animation: flujoAparece .5s cubic-bezier(.34,1.56,.64,1);
}

@keyframes flujoAparece {
    0% { transform: scale(.7); }
    60% { transform: scale(1.12); }
    100% { transform: scale(1); }
}

.flujo-paso:hover .flujo-icon {
    transform: scale(1.08);
}

.flujo-linea {
    flex: 0 0 30px;
    border-top: 2px dashed #d1d5db;
    margin-top: -28px;
    position: relative;
    overflow: visible;
}

.flujo-linea-progreso {
    position: absolute;
    top: -1px;
    left: 0;
    height: 2px;
    background: #0d6efd;
    display: block;
    transition: width .6s ease;
}

.progress-track {
    background: #eef0f3;
    border-radius: 20px;
    height: 10px;
    overflow: hidden;
}

.progress-fill {
    background: linear-gradient(90deg, #0d6efd, #4dabf7);
    height: 100%;
    border-radius: 20px;
    transition: width .6s cubic-bezier(.22,1,.36,1);
    position: relative;
    overflow: hidden;
}

.progress-shine {
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, transparent 0%, rgba(255,255,255,.55) 50%, transparent 100%);
    transform: translateX(-100%);
    animation: shine 2.2s ease-in-out infinite;
}

@keyframes shine {
    0% { transform: translateX(-100%); }
    60%, 100% { transform: translateX(100%); }
}

/* ─── ACTIVIDAD RECIENTE ─────────────────────────────────────────── */

.timeline-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 8px;
    border-top: 1px solid #f0f0f0;
    border-radius: 10px;
    transition: background .2s ease;
}

.timeline-item:hover {
    background: #f8fafc;
}

.timeline-item:first-of-type {
    border-top: none;
}

.timeline-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
    position: relative;
}

.timeline-item:first-of-type .timeline-dot::after {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    background: inherit;
    opacity: .4;
    animation: dotPing 1.8s ease-out infinite;
}

@keyframes dotPing {
    0% { transform: scale(1); opacity: .45; }
    100% { transform: scale(2.2); opacity: 0; }
}

.dot-paciente { background: #f59e0b; }
.dot-triage { background: #10b981; }
.dot-consulta { background: #3b82f6; }
.dot-inicio { background: #10b981; }

/* ─── ACCESOS RAPIDOS ────────────────────────────────────────────── */

.accesos-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
}

.acceso-rapido {
    flex: 1 1 200px;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    border-radius: 16px;
    background: #f8f9fa;
    text-decoration: none;
    color: inherit;
    border-left: 3px solid transparent;
    transition: transform .25s cubic-bezier(.22,1,.36,1), box-shadow .25s ease, background .25s ease;
}

.acceso-rapido:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 10px 22px rgba(0,0,0,.1);
    background: white;
}

.acceso-rapido i {
    font-size: 20px;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: transform .35s cubic-bezier(.34,1.56,.64,1);
}

.acceso-rapido:hover i {
    transform: scale(1.15) rotate(8deg);
}

.acceso-primary { border-left-color: #1d4ed8; }
.acceso-primary i { background: #dbeafe; color: #1d4ed8; }

.acceso-success { border-left-color: #059669; }
.acceso-success i { background: #d1fae5; color: #059669; }

.acceso-purple { border-left-color: #7b1fa2; }
.acceso-purple i { background: #f1e6fb; color: #7b1fa2; }

.acceso-warning { border-left-color: #b45309; }
.acceso-warning i { background: #fef3c7; color: #b45309; }

.acceso-danger { border-left-color: #dc2626; }
.acceso-danger i { background: #fee2e2; color: #dc2626; }

@media (prefers-reduced-motion: reduce) {
    .fade-in-up, .stagger-item, .alerta-alta, .alerta-media,
    .estado-en-proceso, .flujo-activo, .progress-shine, .timeline-dot::after,
    .iconoTemblor {
        animation: none !important;
    }
}
</style>