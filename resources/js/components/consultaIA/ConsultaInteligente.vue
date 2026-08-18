<!-- ConsultaInteligente.vue -->
<template>
    <!-- Sin paciente seleccionado: mostramos buscador -->
    <div v-if="!hasPaciente" class="col-lg-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="mb-3">
                    <i class="fas fa-user-md mr-2"></i>
                    Selecciona un paciente para iniciar la consulta
                </h5>

                <div class="form-group">
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Buscar paciente por nombre..."
                        v-model="busqueda"
                        @input="buscarPacientes">
                </div>

                <div v-if="buscando" class="text-muted">
                    <i class="fas fa-spinner fa-spin"></i> Buscando...
                </div>

                <ul v-else-if="resultados.length" class="list-group">
                    <li
                        v-for="p in resultados"
                        :key="p.id"
                        class="list-group-item list-group-item-action"
                        style="cursor:pointer"
                        @click="seleccionarPaciente(p)">
                        <strong>{{ p.nombre }}</strong>
                        <span class="text-muted" v-if="p.edad || p.sexo">
                            — {{ p.edad }} años | {{ p.sexo }}
                        </span>
                    </li>
                </ul>

                <div v-else-if="busqueda.length >= 2 && !buscando" class="text-muted">
                    No se encontraron pacientes con ese nombre.
                </div>
            </div>
        </div>
    </div>
    <!-- Con paciente seleccionado: consulta normal -->
    <template v-else>
        <!-- BARRA SUPERIOR: salida explícita de la consulta -->
        <div class="row mb-2">
            <div class="col-12 d-flex justify-content-end">
                <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm"
                    @click="salirConsulta">
                    <i class="fas fa-sign-out-alt mr-1"></i> Salir de la consulta
                </button>
            </div>
        </div>

        <!-- SIGNOS VITALES DEL TRIAGE -->
        <div class="row">
            <div class="col-12">
                <!--
                    @triage-agregado: SignosVitales.vue emite este evento
                    justo después de guardar un triage nuevo (POST
                    /triage/guardar). Volvemos a pedir el paciente completo
                    para que `paciente.triages` refleje el dato real que
                    regresó el backend (el componente hijo ya lo muestra de
                    inmediato de forma optimista, esto solo lo sincroniza).
                -->
                <SignosVitales
                    :paciente="paciente"
                    @triage-agregado="obtenerPaciente"
                />
            </div>
        </div>
        <!-- FILA PRINCIPAL -->
        <div class="row">
            <div class="col-lg-3">
                <HistorialClinico
                    :paciente-id="pacienteId"
                    :ia-data="iaData"
                />
                <AlertasClinicas
                    :ia-data="iaData"
                />
                <ArchivosClinicos
                    ref="archivosClinicos"
                    :consulta-id="consultaId"
                />
            </div>
            <div class="col-lg-6">
                <TranscripcionLive
                    :paciente-id="pacienteId"
                    @actualizarSintomas="actualizarSintomas"
                    @actualizarIaData="actualizarIaData"
                    @marcarErrorIa="marcarErrorIa"
                    @actualizarConsultaId="actualizarConsultaId"
                    @archivoSubido="refrescarArchivos"
                    @conversacionFinalizada="manejarConversacionFinalizada"
                />
                <PanelIA
                    :ia-data="iaData"
                    :has-error="iaError"
                />
            </div>
            <div class="col-lg-3">
                <RecetaInteligente
                    :sintomas="sintomasDetectados"
                    :consulta-id="consultaId"
                />
                <DerivacionClinica
                    :sintomas="sintomasDetectados"
                    :consulta-id="consultaId"
                />
            </div>
        </div>
        <!-- NOTA PSOAPP A TODO EL ANCHO -->
        <div class="row psoapp-row">
            <div class="col-12 psoapp-col">
                <NotaPSOAPP
                    ref="notaPsoapp"
                    :consulta-id="consultaId"
                    :nota-psoapp="iaData ? iaData.nota_psoapp : null"
                />
            </div>
        </div>
    </template>
</template>
<script>
import ApiService from '../../services/ApiService.js'
import axios from 'axios'
import eventBus from '../../utils/eventBus.js'
import TranscripcionLive from './TranscripcionLive.vue'
import PanelIA from './PanelIA.vue'
import HistorialClinico from './HistorialClinico.vue'
import AlertasClinicas from './AlertasClinicas.vue'
import ArchivosClinicos from './ArchivosClinicos.vue'
import DerivacionClinica from './DerivacionClinica.vue'
import RecetaInteligente from './RecetaInteligente.vue'
import NotaPSOAPP from './NotaPSOAPP.vue'
import SignosVitales from './SignosVitales.vue'
export default {
    components: {
        TranscripcionLive,
        PanelIA,
        HistorialClinico,
        AlertasClinicas,
        ArchivosClinicos,
        DerivacionClinica,
        RecetaInteligente,
        NotaPSOAPP,
        SignosVitales
    },
    props: {
        pacienteId: {
            type: [String, Number],
            required: false,
            default: ''
        },
        // NUEVO: URL real de la lista de consultas, resuelta desde Blade
        // con route('ListaConsultas'). Es el destino fijo al confirmar
        // salida (botón "Salir de la consulta" o link del sidebar
        // interceptado), sin importar desde dónde se disparó.
        rutaListaConsultas: {
            type: String,
            default: '/ListaConsultas'
        }
    },
    data() {
        return {
            sintomasDetectados: [],
            iaData: null,
            iaError: false,
            consultaId: null,
            paciente: {},
            busqueda: '',
            resultados: [],
            buscando: false,
            debounceTimer: null,
            todosPacientes: []
        }
    },
    computed: {
        hasPaciente() {
            return this.pacienteId !== null &&
                   this.pacienteId !== undefined &&
                   this.pacienteId !== '';
        }
    },
    mounted() {
        console.log(
            "Paciente recibido:",
            this.pacienteId
        );
        if (this.hasPaciente) {
            this.obtenerPaciente();
        } else {
            this.cargarListaPacientes();
        }

        // Protección de salida: si hay receta/diagnóstico sin descargar,
        // avisamos antes de dejar salir al usuario de esta vista.
        window.addEventListener('beforeunload', this.confirmarSalidaNativa)
        document.addEventListener('click', this.interceptarNavegacion, true)
    },
    beforeDestroy() {
        window.removeEventListener('beforeunload', this.confirmarSalidaNativa)
        document.removeEventListener('click', this.interceptarNavegacion, true)
    },
    methods: {
        async obtenerPaciente() {
            try {
                const response = await ApiService.get(
                    '/ExpedienteDetalle/' + this.pacienteId
                );
                this.paciente = response.data;
                console.log(
                    'Datos paciente:',
                    this.paciente
                );
                // Actualiza el encabezado del Blade
                const nombre = document.getElementById('nombrePaciente');
                const datos = document.getElementById('datosPaciente');
                if (nombre) {
                    nombre.innerHTML = this.paciente.nombre;
                }
                if (datos) {
                    datos.innerHTML =
                        this.paciente.edad +
                        ' años | ' +
                        this.paciente.sexo;
                }
            } catch (error) {
                console.error(
                    'Error al cargar paciente:',
                    error
                );
            }
        },
        async cargarListaPacientes() {
            this.buscando = true;
            try {
                const response = await ApiService.get('/pacientes');
                this.todosPacientes = Array.isArray(response.data)
                    ? response.data
                    : (response.data.data || []);
            } catch (error) {
                console.error(
                    'Error al cargar lista de pacientes:',
                    error
                );
                this.todosPacientes = [];
            } finally {
                this.buscando = false;
            }
        },
        buscarPacientes() {
            clearTimeout(this.debounceTimer);
            if (this.busqueda.length < 2) {
                this.resultados = [];
                return;
            }
            this.debounceTimer = setTimeout(() => {
                const texto = this.busqueda.toLowerCase();
                this.resultados = this.todosPacientes.filter(p =>
                    (p.nombre || '')
                        .toLowerCase()
                        .includes(texto)
                );
            }, 200);
        },
        seleccionarPaciente(paciente) {
            window.location.href =
                '/ConsultaInteligente/' + paciente.id;
        },
        actualizarSintomas(sintomas) {
            this.sintomasDetectados = sintomas;
            console.log(
                'Síntomas detectados por IA:',
                sintomas
            );
        },
        actualizarIaData(iaData) {
            this.iaData = iaData;
            this.iaError = false;
            console.log(
                'Datos recibidos de la IA:',
                iaData
            );
            if (iaData && iaData.debug_usage) {
                console.log(
                    '%c [IA] Consumo de Tokens:',
                    'background: #222; color: #bada55; padding: 2px 5px; border-radius: 3px;',
                    iaData.debug_usage
                );
            }
        },
        marcarErrorIa() {
            this.iaError = true;
            console.error(
                'Se produjo un error en el procesamiento de IA.'
            );
        },
        actualizarConsultaId(consultaId) {
            this.consultaId = consultaId;
            console.log(
                'Consulta ID actualizado:',
                consultaId
            );
        },
        refrescarArchivos() {
            if (this.$refs.archivosClinicos) {
                this.$refs.archivosClinicos.cargarArchivos();
            }
        },

        obtenerFechaHoyISO() {
            const hoy = new Date()
            const y = hoy.getFullYear()
            const m = String(hoy.getMonth() + 1).padStart(2, '0')
            const d = String(hoy.getDate()).padStart(2, '0')
            return `${y}-${m}-${d}`
        },

        // NUEVO: navegación real fuera de la vista. Quita el guard nativo
        // justo antes de salir, para no disparar el diálogo feo del
        // navegador encima de una confirmación que el usuario ya dio en
        // nuestro propio SweetAlert (validarSalida). Sin esto, el
        // beforeunload volvía a interceptar la salida ya aprobada y en
        // vez de navegar, terminaba recargando la página actual.
        navegarFuera(url) {
            window.removeEventListener('beforeunload', this.confirmarSalidaNativa)
            window.location.href = url
        },

        // NUEVO: salida explícita y predecible de la consulta actual.
        // Valida pendientes (receta/diagnóstico) y, si el usuario
        // confirma, navega a la lista de consultas.
        salirConsulta() {
            if (this.$refs.notaPsoapp) {
                this.$refs.notaPsoapp.validarSalida(() => {
                    this.navegarFuera(this.rutaListaConsultas)
                })
            } else {
                this.navegarFuera(this.rutaListaConsultas)
            }
        },

        async manejarConversacionFinalizada() {
            if (this.$refs.notaPsoapp) {
                this.$refs.notaPsoapp.validarSalida(() => this._avanzarSiguientePaciente());
            } else {
                await this._avanzarSiguientePaciente();
            }
        },

        async _avanzarSiguientePaciente() {
            try {
                const hoy = this.obtenerFechaHoyISO()

                const respCitas = await axios.get('/api/citas')
                const citasHoy = (respCitas.data || []).filter(c => String(c.fecha).slice(0, 10) === hoy)

                const citaActual = citasHoy.find(
                    c => c.paciente && c.paciente.id == this.pacienteId
                )

                if (citaActual) {
                    await axios.patch(`/api/citas/${citaActual.id}/estado`, {
                        estado: 'Finalizada'
                    })
                    eventBus.emit('consulta-finalizada')
                } else {
                    console.warn('No se encontró una cita de hoy para este paciente; no se pudo marcar como Finalizada.')
                }

                const pendientes = citasHoy
                    .filter(c => c.id !== citaActual?.id)
                    .filter(c => !['Finalizada', 'Cancelada'].includes(c.estado))
                    .sort((a, b) => (a.hora || '').localeCompare(b.hora || ''))

                const siguiente = pendientes[0]

                if (siguiente && siguiente.paciente) {
                    // CAMBIO: antes era window.location.href directo.
                    // Ahora pasa por navegarFuera() para quitar el
                    // beforeunload antes de salir de verdad.
                    this.navegarFuera('/ConsultaInteligente/' + siguiente.paciente.id)
                } else if (window.Swal) {
                    window.Swal.fire({
                        icon: 'info',
                        title: 'No hay más pacientes en espera',
                        text: 'Ya atendiste a todos los pacientes agendados para hoy.'
                    })
                } else {
                    alert('No hay más pacientes en espera para hoy.')
                }
            } catch (error) {
                console.error('Error al avanzar al siguiente paciente:', error)
                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'error',
                        title: 'No se pudo avanzar al siguiente paciente',
                        text: 'Intenta de nuevo o vuelve a la lista.'
                    })
                }
            }
        },

        confirmarSalidaNativa(e) {
            if (this.hasPaciente && this.$refs.notaPsoapp && this.$refs.notaPsoapp.tienePendientes()) {
                e.preventDefault()
                e.returnValue = ''
            }
        },

        interceptarNavegacion(e) {
            if (!this.hasPaciente || !this.$refs.notaPsoapp) return
            if (!this.$refs.notaPsoapp.tienePendientes()) return

            const link = e.target.closest('.main-sidebar a[href]')
            if (!link) return

            e.preventDefault()
            e.stopPropagation()
            this.$refs.notaPsoapp.validarSalida(() => {
                // CAMBIO: antes navegaba a link.href (el link que el
                // usuario tocó). Ahora siempre manda a la lista de
                // consultas al confirmar, sin importar cuál link del
                // sidebar se haya clickeado.
                this.navegarFuera(this.rutaListaConsultas)
            })
        }
    }
}
</script>