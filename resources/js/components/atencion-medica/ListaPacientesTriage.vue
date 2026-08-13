<template>

    <div>

        <!-- =======================================================
             VISTA A: LISTADO PRINCIPAL
        ======================================================== -->
        <div v-if="!pacienteSeleccionado">

            <!-- ==========================================
                 BANNER DE ALERTAS
            =========================================== -->
            <div
                v-if="pacientesVencidos.length"
                class="alert alert-danger d-flex align-items-center shadow-sm mb-3 alert-blink"
                role="alert"
            >

                <i
                    class="fas fa-exclamation-triangle me-3"
                    style="font-size: 22px;"
                ></i>

                <div>

                    <strong>
                        ¡Atención doctor!
                        {{ pacientesVencidos.length }}
                        paciente(s) con tiempo de espera excedido:
                    </strong>

                    <span
                        v-for="(v, idx) in pacientesVencidos"
                        :key="v.paciente.id"
                    >

                        {{ v.paciente.nombre }}

                        <span
                            v-if="
                                idx <
                                pacientesVencidos.length - 1
                            "
                        >
                            ,
                        </span>

                    </span>

                </div>

            </div>


            <!-- ==========================================
                 TABLA
            =========================================== -->
            <div class="card card-outline card-primary shadow-lg">

                <div class="card-header">

                    <h3 class="card-title font-weight-bold">

                        <i class="fas fa-notes-medical me-2"></i>

                        Lista de Pacientes TRIAGE

                    </h3>

                </div>


                <div class="card-body table-responsive">

                    <table
                        class="table table-hover table-bordered align-middle"
                    >

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

                            
                            <tr
                                v-for="paciente in pacientesFiltrados"
                                :key="paciente.id"
                                :class="{
                                    'table-danger':
                                        !analisisIA[paciente.id]?.sinDatos &&
                                        obtenerEspera(
                                            paciente.triages?.[0]?.estado,
                                            paciente.triages?.[0]?.created_at,
                                            paciente.estado_consulta
                                        ).vencido
                                }"
                            >

                                <!-- ==================================
                                     PRIORIDAD
                                =================================== -->
                                <!-- Columna PRIORIDAD -->
                                <td>
                                    <template v-if="analisisIA[paciente.id]?.sinDatos">
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-minus-circle me-1"></i>
                                            Sin triage
                                        </span>
                                    </template>

                                    <template v-else-if="analisisIA[paciente.id]?.loading">
                                        <span class="badge bg-secondary">
                                            <span class="spinner-border spinner-border-sm me-1"></span>
                                            Analizando IA...
                                        </span>
                                    </template>

                                    <template v-else>
                                        <span :class="obtenerPrioridad(analisisIA[paciente.id]?.prioridad || mapearEstadoAPrioridad(paciente.triages?.[0]?.estado)).clase">
                                            {{ obtenerPrioridad(analisisIA[paciente.id]?.prioridad || mapearEstadoAPrioridad(paciente.triages?.[0]?.estado)).texto }}
                                        </span>
                                        <i v-if="analisisIA[paciente.id]?.error" class="fas fa-exclamation-circle text-warning ms-1" title="IA no disponible, usando reglas de respaldo"></i>
                                    </template>
                                </td>


                                <!-- ==================================
                                     PACIENTE
                                =================================== -->
                                <td class="fw-bold">

                                    {{ paciente.nombre }}

                                </td>


                                <!-- ==================================
                                     MOTIVO
                                =================================== -->
                               <td
                                    class="motivo-columna"
                                    :title="paciente.triages?.[0]?.sintomas || 'Sin motivo'"
                                >
                                    {{ paciente.triages?.[0]?.sintomas ? recortarMotivo(paciente.triages[0].sintomas) : 'Sin motivo' }}
                                </td>


                                <!-- ==================================
                                     SIGNOS VITALES
                                =================================== -->
                                <td style="font-size: 12.5px; white-space: nowrap;">
    
                                    <!-- Evaluamos si el paciente no tiene datos de triage cargados -->
                                    <template v-if="!paciente.triages || paciente.triages.length === 0">
                                        <span class="badge bg-secondary">Sin triage</span>
                                    </template>

                                    <template v-else>
                                        <!-- Presión -->
                                        <div>
                                            <i class="fas fa-stethoscope text-info me-1"></i>
                                            {{ paciente.triages[0].presion || 'Sin triage' }}
                                        </div>

                                        <!-- Saturación -->
                                        <div>
                                            <i class="fas fa-lungs me-1" style="color: #0d9488;"></i>
                                            {{
                                                paciente.triages[0].saturacion !== null && paciente.triages[0].saturacion !== undefined && paciente.triages[0].saturacion !== ''
                                                    ? paciente.triages[0].saturacion + '%'
                                                    : 'Sin triage'
                                            }}
                                        </div>

                                        <div>
                                            <i class="fas fa-thermometer-half text-warning me-1"></i>
                                            {{
                                                paciente.triages[0].temperatura !== null && paciente.triages[0].temperatura !== undefined && paciente.triages[0].temperatura !== ''
                                                    ? paciente.triages[0].temperatura + ' °C'
                                                    : 'Sin triage'
                                            }}
                                        </div>
                                    </template>
                                </td>


                                <!-- ==================================
                                     ESTADO
                                =================================== -->
                                <td>

                                    <template
                                        v-if="
                                            analisisIA[paciente.id]?.loading
                                        "
                                    >

                                        <span class="badge bg-secondary">
                                            ...
                                        </span>

                                    </template>


                                    <template v-else>
                                        <span v-if="obtenerEstadoFinal(paciente) === 'sin_datos'" class="badge bg-secondary">SIN TRIAGE</span>
                                        <span v-else-if="obtenerEstadoFinal(paciente) === 'grave'" class="badge bg-danger">GRAVE</span>
                                        <span v-else-if="obtenerEstadoFinal(paciente) === 'moderado'" class="badge bg-warning text-dark">MODERADO</span>
                                        <span v-else class="badge bg-info text-dark">LEVE</span>

                                        <br>

                                        <small class="text-muted fst-italic" style="font-size: 11px;">
                                            {{ analisisIA[paciente.id]?.justificacion || 'Evaluado por Triage base' }}
                                        </small>
                                    </template>

                                </td>


                                <!-- ==================================
                                     ESPERA
                                =================================== -->
                                <!-- Columna ESPERA -->
                                <td>
                                    <span v-if="analisisIA[paciente.id]?.sinDatos" class="badge bg-secondary">
                                        — Sin evaluar
                                    </span>

                                    <span v-else :class="obtenerEspera(paciente.triages?.[0]?.estado, paciente.triages?.[0]?.created_at, paciente.estado_consulta).claseCss">
                                        {{ obtenerEspera(paciente.triages?.[0]?.estado, paciente.triages?.[0]?.created_at, paciente.estado_consulta).texto }}
                                    </span>
                                </td>


                                <!-- ==================================
                                     ACCIÓN
                                =================================== -->
                                <td>

                                    <button
                                        class="btn btn-sm btn-info text-white"
                                        @click="
                                            verDetallePaciente(paciente.id)
                                        "
                                    >

                                        <i
                                            class="fas fa-id-card me-1"
                                        ></i>

                                        Ver Detalle

                                    </button>

                                </td>

                            </tr>


                            <!-- ==================================
                                 SIN RESULTADOS
                            =================================== -->
                            <tr
                                v-if="
                                    !loading &&
                                    pacientesFiltrados.length === 0
                                "
                            >

                                <td
                                    colspan="7"
                                    class="text-center py-5"
                                >

                                    <i
                                        class="fas fa-filter fa-2x text-muted mb-3"
                                    ></i>

                                    <p class="text-muted mb-0">

                                        No hay pacientes que coincidan
                                        con este filtro.

                                    </p>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        <!-- =======================================================
             VISTA B: DETALLE
        ======================================================== -->
        <div
            v-else
            class="card card-outline card-info shadow-lg"
        >

            <div class="card-header">

                <h3 class="card-title font-weight-bold m-0">

                    <i class="fas fa-user-md me-2"></i>

                    Expediente Triage:
                    {{ detalletriage.nombre || 'Cargando...' }}

                </h3>


                <div class="card-tools">

                    <button
                        class="btn btn-secondary btn-sm rounded-pill px-3"
                        style="
                            border-radius: 50px !important;
                            font-weight: 600;
                        "
                        @click="cerrarDetalle"
                    >

                        <i class="fas fa-arrow-left me-1"></i>

                        Volver a la Lista

                    </button>

                </div>

            </div>


            <div class="card-body">

                <div
                    v-if="cargandoDetalle"
                    class="text-center py-5"
                >

                    <div
                        class="spinner-border text-info"
                        role="status"
                    >

                        <span class="sr-only">
                            Cargando expediente...
                        </span>

                    </div>

                    <p class="mt-2 text-muted">

                        Obteniendo expediente del paciente...

                    </p>

                </div>


                <ModalTriage
                    v-else
                    :detalletriage="detalletriage"
                    :obtenerPrioridad="obtenerPrioridad"
                    :mapearEstadoAPrioridad="mapearEstadoAPrioridad"
                    :obtenerEspera="obtenerEspera"
                    @cerrar="cerrarDetalle"
                />

            </div>

        </div>

    </div>

</template>


<script>

import ApiService from '../../services/ApiService.js'
import ModalTriage from './ModalTriage.vue'

export default {

    name: 'ListaPacientesTriage',

    components: {
        ModalTriage
    },


    props: {

        triages: {
            type: Array,
            default: () => []
        },

        loading: {
            type: Boolean,
            default: false
        },

        filtroActivo: {
            type: String,
            default: null
        }

    },


    emits: [
        'actualizar-conteos'
    ],


    data() {

        return {

            triage: [],

            pacienteSeleccionado: null,

            detalletriage: {},

            analisisIA: {},

            tiempoActual: new Date(),

            alertasEnviadas: new Set(),

            cargandoDetalle: false,

            intervalTiempo: null,

            LIMITES_MINUTOS_ESTADO: {

                grave: 5,

                urgente: 15,

                leve: 60,

                estable: 120

            }

        }

    },


    computed: {

        /**
         * ==========================================
         * PACIENTES FILTRADOS
         * ==========================================
         */
         pacientesFiltrados() {

        // ==========================================
        // SIN FILTRO
        // ==========================================
        if (
            !this.filtroActivo ||
            this.filtroActivo === 'todos'
        ) {
            return this.triage;
        }


        const filtro =
            String(this.filtroActivo)
                .toLowerCase()
                .trim();


        return this.triage.filter(paciente => {

            // ======================================
            // ESTADO DEL TRIAGE
            // ======================================
            const estadoTriage =
                this.obtenerEstadoFinal(paciente);


            // ======================================
            // ESTADO DE CONSULTA
            // ======================================
            const estadoConsulta =
                String(
                    paciente.estado_consulta || ''
                )
                    .toLowerCase()
                    .trim();


            // ======================================
            // 🔴 CRÍTICOS
            // GRAVE
            //
            // IMPORTANTE:
            // NO importa si está finalizado.
            // Un paciente puede ser GRAVE + FINALIZADO.
            // ======================================
            if (filtro === 'critico') {

                return estadoTriage === 'grave';

            }


            // ======================================
            // 🟠 URGENTES
            // URGENTE
            // ======================================
            if (filtro === 'urgente') {

                return estadoTriage === 'urgente';

            }


            // ======================================
            // 🔵 MODERADOS
            // LEVE
            // ======================================
            if (filtro === 'moderado') {

                return estadoTriage === 'leve';

            }


            // ======================================
            // 🟢 ATENDIDOS
            // FINALIZADO
            // ======================================
            if (filtro === 'finalizado') {

                return (
                    estadoConsulta === 'finalizado' ||
                    estadoConsulta === 'finalizada' ||
                    estadoConsulta === 'atendido'
                );

            }


            return false;

        });

    },


    // ==========================================
    // PACIENTES VENCIDOS
    // ==========================================
    pacientesVencidos() {

        return this.triage
            .filter(paciente => {

                // ⛔ Sin datos suficientes: no genera alerta de vencido
                if (this.analisisIA[paciente.id]?.sinDatos) {
                    return false;
                }

                if (!paciente.estado_consulta || !paciente.triages?.[0]) {
                    return false;
                }

                const estadoConsulta = String(paciente.estado_consulta).toLowerCase().trim();

                if (['finalizada', 'finalizado', 'atendido'].includes(estadoConsulta)) {
                    return false;
                }

                const triage0 = paciente.triages[0];

                return this.obtenerEspera(triage0.estado, triage0.created_at, paciente.estado_consulta).vencido;

            })
            .map(paciente => ({ paciente }));

    },

    },


    watch: {

        triages: {

            handler(nuevaLista) {

                this.sincronizarPacientes(
                    nuevaLista
                );

            },

            immediate: true

        },


        triage: {

            handler() {

                this.emitirConteos();

            },

            deep: true

        },


        analisisIA: {

            handler() {

                this.emitirConteos();

            },

            deep: true

        }

    },


    mounted() {

        if (
            window.Notification &&
            Notification.permission === 'default'
        ) {

            Notification.requestPermission();

        }


        this.intervalTiempo = setInterval(() => {

            this.tiempoActual = new Date();

            this.verificarVencidosYAlertar();

        }, 15000);

    },


    beforeUnmount() {

        if (this.intervalTiempo) {

            clearInterval(
                this.intervalTiempo
            );

        }

    },


    methods: {

        recortarMotivo(motivo) {
            if (!motivo) return 'Sin motivo';
            const limite = 30; // o los caracteres que uses
            return motivo.length > limite ? motivo.substring(0, limite) + '...' : motivo;
        },

        /**
         * ==========================================
         * ESTADO FINAL
         * ==========================================
         */
        obtenerEstadoFinal(paciente) {

            if (this.analisisIA[paciente.id]?.sinDatos) {
                return 'sin_datos';
            }

            const estadoIA = String(this.analisisIA[paciente.id]?.estado || '').toLowerCase().trim();
            if (estadoIA) return estadoIA;

            const estadoBD = String(paciente.triages?.[0]?.estado || '').toLowerCase().trim();
            if (estadoBD) return estadoBD;

            return 'leve';
        },


        /**
         * ==========================================
         * CONTEOS DE TARJETAS
         * ==========================================
         */
        emitirConteos() {

            let critico = 0;
            let urgente = 0;
            let leve = 0;
            let finalizado = 0;
            
            this.triage.forEach(paciente => {
                // ======================================
                // ESTADO DEL TRIAGE
                // ======================================
                const estado =
                    this.obtenerEstadoFinal(paciente);

                // ======================================
                // ESTADO DE CONSULTA
                // ======================================
                const estadoConsulta =
                    String(
                        paciente.estado_consulta || ''
                    )
                        .toLowerCase()
                        .trim();
                // ======================================
                // 🔴 CRÍTICO = GRAVE
                // ======================================
                if (estado === 'grave') {
                    critico++;
                }

                // ======================================
                // 🟠 URGENTE = URGENTE
                // ======================================
                if (estado === 'urgente') {
                    urgente++;
                }


                // ======================================
                // 🔵 MODERADO = LEVE
                // ======================================
                if (estado === 'leve') {
                    leve++;
                }

                // ======================================
                // 🟢 ATENDIDO = FINALIZADO
                // ======================================
                if (
                    estadoConsulta === 'finalizado' ||
                    estadoConsulta === 'finalizada' ||
                    estadoConsulta === 'atendido'
                ) {

                    finalizado++;
                }
            });

            this.$emit(
                'actualizar-conteos',
                {
                    todos: this.triage.length,
                    critico,
                    urgente,
                    leve,
                    finalizado
                }
            );

        },


        /**
         * ==========================================
         * VER DETALLE
         * ==========================================
         */
        async verDetallePaciente(id) {

            this.pacienteSeleccionado = id;
            this.cargandoDetalle = true;

            try {

                const pacienteLocal = this.triage.find(p => p.id === id);

                const response = await ApiService.get('/triage/' + id);

                const triagesRespuesta = response.data.triages?.length
                    ? [...response.data.triages]
                    : [];

                // Fallback: si el triage[0] de la respuesta no trae síntomas,
                // usamos los del paciente local (los mismos que ya se ven en la tabla)
                if (triagesRespuesta[0] && !triagesRespuesta[0].sintomas) {
                    triagesRespuesta[0] = {
                        ...triagesRespuesta[0],
                        sintomas: pacienteLocal?.triages?.[0]?.sintomas || null
                    };
                }

                this.detalletriage = {

                    ...response.data,

                    triages: triagesRespuesta.length
                        ? triagesRespuesta
                        : pacienteLocal?.triages,

                    estado_consulta:
                        response.data.estado_consulta ||
                        pacienteLocal?.estado_consulta,

                    _ia: this.analisisIA[id] || null,

                    estado: this.obtenerEstadoFinal(pacienteLocal || response.data)

                };

            } catch (error) {

                console.error('Error al cargar el detalle del paciente:', error);

            } finally {

                this.cargandoDetalle = false;

            }

        },


        /**
         * ==========================================
         * CERRAR DETALLE
         * ==========================================
         */
        cerrarDetalle() {

            this.pacienteSeleccionado = null;

            this.detalletriage = {};

        },


        /**
         * ==========================================
         * ANALIZAR CON IA
         * ==========================================
         */
        async analizarConIA(paciente) {

            const triage = paciente.triages?.[0];

            // ⛔ Sin vitales reales: no se llama a la IA, no se gasta contador/token
            if (!this.tieneDatosSuficientes(triage)) {

                this.analisisIA = {
                    ...this.analisisIA,
                    [paciente.id]: {
                        loading: false,
                        sinDatos: true,
                        prioridad: null,
                        estado: 'sin_datos',
                        justificacion: 'Sin triage registrado o evaluación no completada.',
                    }
                };

                return; // 👈 nunca llega al ApiService.get(...) que llama a DeepSeek
            }

            this.analisisIA = {
                ...this.analisisIA,
                [paciente.id]: { loading: true }
            };

            try {
                const response = await ApiService.get(`/triage/${paciente.id}/analizar-ia`);


                const data =
                    response.data;


                let prioridad =
                    data.prioridad;


                const prioridadPorVitales =
                    this.evaluarVitalesCriticos(
                        triage
                    );


                if (
                    prioridadPorVitales &&
                    this.rango(
                        prioridadPorVitales
                    ) <
                    this.rango(
                        prioridad
                    )
                ) {

                    prioridad =
                        prioridadPorVitales;

                }


                this.analisisIA = {

                    ...this.analisisIA,

                    [paciente.id]: {

                        loading: false,

                        prioridad,

                        estado:
                            data.estado,

                        justificacion:
                            data.justificacion,

                        error:
                            data.fuente ===
                            'fallback',

                        fechaTriage:
                            triage.created_at

                    }

                };

            } catch (error) {

                let prioridad =
                    this.mapearEstadoAPrioridad(
                        triage.estado
                    );


                const prioridadPorVitales =
                    this.evaluarVitalesCriticos(
                        triage
                    );


                if (
                    prioridadPorVitales &&
                    this.rango(
                        prioridadPorVitales
                    ) <
                    this.rango(
                        prioridad
                    )
                ) {

                    prioridad =
                        prioridadPorVitales;

                }


                this.analisisIA = {

                    ...this.analisisIA,

                    [paciente.id]: {

                        loading: false,

                        prioridad,

                        estado:
                            triage.estado ||
                            'leve',

                        justificacion:
                            'IA no disponible, se usaron reglas de respaldo',

                        error: true,

                        fechaTriage:
                            triage.created_at

                    }

                };

            }

        },


        /**
         * ==========================================
         * RANGO DE PRIORIDAD
         * ==========================================
         */
        rango(prioridad) {

            const orden = {

                rojo: 1,

                naranja: 2,

                amarillo: 3,

                verde: 4,

                azul: 5

            };


            return (
                orden[
                    prioridad?.toLowerCase()
                ] ?? 5
            );

        },


        /**
         * ==========================================
         * SIGNOS VITALES CRÍTICOS
         * ==========================================
         */
        evaluarVitalesCriticos(triage) {

            const sat =
                parseFloat(
                    triage?.saturacion
                );


            const temp =
                parseFloat(
                    triage?.temperatura
                );


            if (
                !isNaN(sat) &&
                sat < 90
            ) {

                return 'rojo';

            }


            if (
                !isNaN(temp) &&
                temp >= 40
            ) {

                return 'rojo';

            }


            if (
                !isNaN(sat) &&
                sat < 94
            ) {

                return 'naranja';

            }


            if (
                !isNaN(temp) &&
                temp >= 39
            ) {

                return 'naranja';

            }


            return null;

        },


        /**
         * ==========================================
         * MAPEAR ESTADO A PRIORIDAD
         * ==========================================
         */
        mapearEstadoAPrioridad(estado) {

            const mapa = {

                grave: 'rojo',

                urgente: 'naranja',

                leve: 'verde'

            };


            return (
                mapa[
                    estado?.toLowerCase()
                ] || 'verde'
            );

        },


        /**
         * ==========================================
         * SINCRONIZAR PACIENTES
         * ==========================================
         */
        sincronizarPacientes(lista) {

            this.triage =
                lista || [];


            const pendientes =
                this.triage.filter(
                    p =>
                        !this.analisisIA[p.id]
                );


            const promesas =
                pendientes.map(
                    p =>
                        this.analizarConIA(p)
                );


            Promise.allSettled(
                promesas
            );

        },


        /**
         * ==========================================
         * PRIORIDAD VISUAL
         * ==========================================
         */
        obtenerPrioridad(prioridadIA) {

            if (!prioridadIA) {

                return {

                    texto: 'Analizando...',

                    clase:
                        'badge bg-secondary'

                };

            }


            const mapa = {

                rojo: {
                    texto:
                        '🔴 ROJO — Inmediata (Nivel 1)',
                    clase:
                        'badge bg-danger'
                },

                naranja: {
                    texto:
                        '🟠 NARANJA — Emergencia (Nivel 2)',
                    clase:
                        'badge bg-orange text-dark'
                },

                amarillo: {
                    texto:
                        '🟡 AMARILLO — Urgente (Nivel 3)',
                    clase:
                        'badge bg-warning text-dark'
                },

                verde: {
                    texto:
                        '🟢 VERDE — Urgencia menor (Nivel 4)',
                    clase:
                        'badge bg-success'
                },

                azul: {
                    texto:
                        '🔵 AZUL — No urgente (Nivel 5)',
                    clase:
                        'badge bg-primary'
                }

            };


            return (
                mapa[
                    prioridadIA.toLowerCase()
                ] || {

                    texto:
                        'Sin clasificar',

                    clase:
                        'badge bg-secondary'

                }
            );

        },


        /**
         * ==========================================
         * TIEMPO DE ESPERA
         * ==========================================
         */
        obtenerEspera(
            estadoTriage,
            fechaRegistro,
            estadoConsulta = 'en_proceso'
        ) {

            const estadoConsultaNormalizado =
                String(
                    estadoConsulta || ''
                )
                    .toLowerCase()
                    .trim();


            if (
                estadoConsultaNormalizado ===
                    'finalizada' ||
                estadoConsultaNormalizado ===
                    'finalizado' ||
                estadoConsultaNormalizado ===
                    'atendido'
            ) {

                return {

                    texto:
                        '✓ Finalizada',

                    claseCss:
                        'badge bg-success text-white fw-bold',

                    vencido: false,

                    finalizada: true,

                    restante: null

                };

            }


            if (
                estadoConsultaNormalizado ===
                'excedido'
            ) {

                return {

                    texto:
                        '⚠️ Excedido',

                    claseCss:
                        'badge bg-danger text-white fw-bold alert-blink',

                    vencido: true,

                    finalizada: false,

                    restante: 0

                };

            }


            if (
                !fechaRegistro ||
                !estadoTriage
            ) {

                return {

                    texto: '...',

                    claseCss:
                        'text-muted',

                    vencido: false,

                    finalizada: false,

                    restante: null

                };

            }


            const inicio =
                new Date(
                    fechaRegistro
                );


            const ahora =
                this.tiempoActual;


            const minutosTranscurridos =
                Math.floor(
                    (
                        ahora - inicio
                    ) / 60000
                );


            const estadoNormalizado =
                String(
                    estadoTriage
                )
                    .toLowerCase()
                    .trim();


            const limite =
                this.LIMITES_MINUTOS_ESTADO[
                    estadoNormalizado
                ] ?? 120;


            const restante =
                limite -
                minutosTranscurridos;


            if (
                restante <= 0
            ) {

                return {

                    texto:
                        `⚠️ Excedido ${Math.abs(restante)} min`,

                    claseCss:
                        'badge bg-danger text-white fw-bold alert-blink',

                    vencido: true,

                    finalizada: false,

                    restante

                };

            }


            const urgente =
                restante <=
                Math.max(
                    1,
                    Math.floor(
                        limite * 0.25
                    )
                );


            return {

                texto:
                    `⏱ ${restante} min`,

                claseCss:
                    urgente
                        ? 'badge bg-warning text-dark fw-bold'
                        : 'badge bg-success text-white fw-bold',

                vencido: false,

                finalizada: false,

                restante

            };

        },

        tieneDatosSuficientes(triage) {
            if (!triage) return false;

            return (
                (triage.presion && String(triage.presion).trim() !== '') ||
                (triage.saturacion !== null && triage.saturacion !== undefined && triage.saturacion !== '') ||
                (triage.temperatura !== null && triage.temperatura !== undefined && triage.temperatura !== '')
            );
        },


        /**
         * ==========================================
         * VERIFICAR VENCIDOS
         * ==========================================
         */
        verificarVencidosYAlertar() {

            this.triage.forEach(paciente => {

                // ⛔ Sin datos suficientes: no participa en el ciclo de alertas
                if (this.analisisIA[paciente.id]?.sinDatos) {
                    return;
                }

                const triage0 = paciente.triages?.[0];
                if (!triage0) return;

                // ...resto igual que ya tenías
            });

        },

        /**
         * ==========================================
         * NOTIFICACIÓN
         * ==========================================
         */
        notificarDoctor(
            paciente,
            triage0
        ) {

            this.reproducirSonidoAlerta();


            if (
                window.Notification &&
                Notification.permission ===
                    'granted'
            ) {

                new Notification(
                    '⚠️ Tiempo de espera excedido',
                    {

                        body:
                            `${paciente.nombre} — Estado ${triage0.estado?.toUpperCase()} — revisar de inmediato`

                    }
                );

            }

        },


        /**
         * ==========================================
         * SONIDO
         * ==========================================
         */
        reproducirSonidoAlerta() {

            try {

                const ctx =
                    new (
                        window.AudioContext ||
                        window.webkitAudioContext
                    )();


                const osc =
                    ctx.createOscillator();


                const gain =
                    ctx.createGain();


                osc.type =
                    'sine';


                osc.frequency.value =
                    880;


                gain.gain.value =
                    0.15;


                osc.connect(gain);

                gain.connect(
                    ctx.destination
                );


                osc.start();


                setTimeout(() => {

                    osc.stop();

                    ctx.close();

                }, 400);


            } catch (e) {

                console.warn(
                    'No se pudo reproducir la alerta sonora',
                    e
                );

            }

        }

    }

};

</script>


<style scoped>

.alert-blink {

    animation:
        blinker 1.5s linear infinite;

}


@keyframes blinker {

    50% {

        opacity: 0.6;

    }

}

</style>