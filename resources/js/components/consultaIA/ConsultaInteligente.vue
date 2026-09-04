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

    <!--
        Con paciente seleccionado: consulta normal.

        consulta-contenedor: SignosVitales va como hijo DIRECTO de este
        div (no envuelto en su propio <div class="row"><div class="col-12">),
        porque ese row solo lo contenía a él y por lo tanto era tan alto
        como el panel mismo. El sticky solo tiene "espacio para moverse"
        si su padre directo es alto, así que al ser este div el padre
        directo tanto del panel como de todo el contenido alto de abajo
        (fila principal de 3 columnas + nota PSOAPP), el sticky sí tiene
        recorrido para quedarse pegado mientras se hace scroll por
        Historial/Transcripción/Receta. Mismo patrón que se usó para el
        header clínico en el blade (que también necesitó ser hijo
        directo del contenedor alto, no de un wrapper corto).
    -->
    <div v-else class="consulta-contenedor">
        <HeaderConsulta 
            :pacienteId="pacienteId" />
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
            :lista-espera-id="listaEsperaIdHoy"
            @triage-agregado="obtenerPaciente"
        />

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
    </div>
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
import HeaderConsulta from './HeaderConsulta.vue'

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
        SignosVitales,
        HeaderConsulta
    },
    props: {
        pacienteId: {
            type: [String, Number],
            required: false,
            default: ''
        },
        // URL real de la lista de consultas, resuelta desde Blade con
        // route('ListaConsultas'). Es el destino fijo al confirmar
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
            listaEsperaIdHoy: null,
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
        console.log('Paciente recibido:', this.pacienteId);

        if (this.hasPaciente) {
            this.obtenerPaciente();
            this.obtenerListaEsperaIdHoy();
        } else {
            this.cargarListaPacientes();
        }

        // Protección de salida: si hay receta/diagnóstico sin descargar,
        // avisamos antes de dejar salir al usuario de esta vista.
        window.addEventListener('beforeunload', this.confirmarSalidaNativa)
        document.addEventListener('click', this.interceptarNavegacion, true)

        // Altura real del navbar + header clínico (#headerClinico,
        // definido en consulta_inteligente.blade.php), para que
        // SignosVitales.vue se pegue justo debajo de ambos (sticky)
        // sin dejar un hueco ni encimarse, sin importar si el header
        // crece (nombre largo, badges que hacen wrap, etc) o si el
        // navbar cambia de tamaño en responsive.
        this.ajustarAlturaHeader();
        window.addEventListener('resize', this.ajustarAlturaHeader);
    },
    beforeDestroy() {
        window.removeEventListener('beforeunload', this.confirmarSalidaNativa)
        document.removeEventListener('click', this.interceptarNavegacion, true)
        window.removeEventListener('resize', this.ajustarAlturaHeader);
    },
    methods: {
        /**
         * Mide el navbar fijo de AdminLTE + #headerClinico y guarda la
         * suma (más un pequeño margen) en la variable CSS --header-height
         * en <html>, que SignosVitales.vue usa como `top` de su propio
         * `position: sticky`. Se pone en documentElement (no en un
         * elemento del árbol de Vue) para que la variable esté disponible
         * aunque el componente que la consume use scoped styles.
         *
         * También es la misma altura de navbar que usa el Blade para su
         * propia variable --navbar-height (ver consulta_inteligente.blade.php
         * sección de JS), así que si el navbar cambia de tamaño ambos
         * quedan sincronizados porque cada uno mide el navbar directamente
         * en el DOM.
         */
        ajustarAlturaHeader() {
            const header = document.getElementById('headerClinico');
            if (!header) return;

            const navbar = document.querySelector('.main-header.navbar') || document.querySelector('nav.main-header');
            const alturaNavbar = navbar ? navbar.offsetHeight : 0;

            // +16px de aire entre el header y el panel de signos vitales,
            // para que no queden pegados visualmente al hacer scroll.
            const altura = alturaNavbar + header.offsetHeight + 16;
            document.documentElement.style.setProperty('--header-height', altura + 'px');
        },

        async obtenerPaciente() {
            try {
                const response = await ApiService.get(
                    '/ExpedienteDetalle/' + this.pacienteId
                );
                this.paciente = response.data;
                console.log('Datos paciente:', this.paciente);

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

                // El nombre/edad recién insertados pueden cambiar la altura
                // del header (ej. un nombre largo que hace wrap a 2 líneas),
                // así que recalculamos --header-height ya con el DOM
                // actualizado.
                this.$nextTick(this.ajustarAlturaHeader);
            } catch (error) {
                console.error('Error al cargar paciente:', error);
            }
        },

        async cargarListaPacientes() {
            this.buscando = true;
            try {
                const response = await ApiService.get('/pacientes');
                // Por si la respuesta viene envuelta en { data: [...] }
                this.todosPacientes = Array.isArray(response.data)
                    ? response.data
                    : (response.data.data || []);
            } catch (error) {
                console.error('Error al cargar lista de pacientes:', error);
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
                    (p.nombre || '').toLowerCase().includes(texto)
                );
            }, 200);
        },

        seleccionarPaciente(paciente) {
            // Navega a la consulta inteligente con el paciente seleccionado
            window.location.href = '/ConsultaInteligente/' + paciente.id;
        },

        actualizarSintomas(sintomas) {
            this.sintomasDetectados = sintomas;
            console.log('Síntomas detectados por IA:', sintomas);
        },

        actualizarIaData(iaData) {
            // Guardamos los datos de IA.
            // NotaPSOAPP.vue recibe "iaData.nota_psoapp" por la prop
            // :nota-psoapp declarada en el template, y se reparte solo
            // gracias al watch interno del componente — ya no hace falta
            // llamar manualmente a actualizarDesdeIA() por cada campo aquí.
            this.iaData = iaData;
            this.iaError = false;
            console.log('Datos recibidos de la IA:', iaData);

            // --- IMPRESIÓN DE TOKENS EN LA CONSOLA DEL NAVEGADOR ---
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
            console.error('Se produjo un error en el procesamiento de IA.');
        },

        actualizarConsultaId(consultaId) {
            this.consultaId = consultaId;
            console.log('Consulta ID actualizado:', consultaId);
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

        // Navegación real fuera de la vista. Quita el guard nativo justo
        // antes de salir, para no disparar el diálogo feo del navegador
        // encima de una confirmación que el usuario ya dio en nuestro
        // propio SweetAlert (validarSalida). Sin esto, el beforeunload
        // volvía a interceptar la salida ya aprobada y en vez de navegar,
        // terminaba recargando la página actual.
        navegarFuera(url) {
            window.removeEventListener('beforeunload', this.confirmarSalidaNativa)
            window.location.href = url
        },

        // Salida explícita y predecible de la consulta actual. Valida
        // pendientes (receta/diagnóstico) y, si el usuario confirma,
        // navega a la lista de consultas.
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
            const opcionesModal = {
                titulo: '¿Avanzar al siguiente paciente?',
                confirmButtonText: 'Sí, continuar con el siguiente',
                cancelButtonText: 'Quedarme con este paciente',
                textos: {
                    ambos: 'No has descargado la receta ni el diagnóstico de esta consulta. Si avanzas ahora, podrías perderlos.',
                    receta: 'No has descargado la receta médica de esta consulta. Si avanzas ahora, podrías perderla.',
                    diagnostico: 'No has descargado el diagnóstico de esta consulta. Si avanzas ahora, podrías perderlo.'
                }
            };
            if (this.$refs.notaPsoapp) {
                this.$refs.notaPsoapp.validarSalida(() => this._avanzarSiguientePaciente(), opcionesModal);
            } else {
                await this._avanzarSiguientePaciente();
            }
        },

        async _avanzarSiguientePaciente() {
            try {
                const hoy = this.obtenerFechaHoyISO()
                const respListaEspera = await ApiService.get('/lista-espera', { params: { fecha: hoy } })
                const listaHoy = respListaEspera.data.lista || respListaEspera.data
                const registroActual = listaHoy.find(
                    r => r.paciente && r.paciente.id == this.pacienteId
                )
                if (registroActual) {
                    await ApiService.patch(`/lista-espera/${registroActual.id}/estado`, {
                        estado: 'Finalizada'
                    })
                    eventBus.emit('consulta-finalizada')
                } else {
                    console.warn('No se encontró un registro de lista_espera de hoy para este paciente; no se pudo marcar como Finalizada.')
                }
                const pendientes = listaHoy
                    .filter(r => r.id !== registroActual?.id)
                    .filter(r => !['Finalizada', 'Cancelada'].includes(r.estado))
                    .sort((a, b) => (a.numero_turno ?? 999999) - (b.numero_turno ?? 999999))

                const siguiente = pendientes[0]
                if (siguiente && siguiente.paciente) {
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

        // NUEVO: busca la fila de lista_espera de HOY para este paciente,
        // para saber cuál triage de paciente.triages corresponde a esta visita.
        async obtenerListaEsperaIdHoy() {
            try {
                const hoy = this.obtenerFechaHoyISO()
                const response = await ApiService.get('/lista-espera', { params: { fecha: hoy } })
                const lista = response.data.lista || response.data
                const registro = lista.find(item => item.paciente_id == this.pacienteId)
                this.listaEsperaIdHoy = registro?.id ?? null
            } catch (error) {
                console.error('Error al obtener lista_espera de hoy:', error)
                this.listaEsperaIdHoy = null
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
                // Antes navegaba a link.href (el link que el usuario tocó).
                // Ahora siempre manda a la lista de consultas al confirmar,
                // sin importar cuál link del sidebar se haya clickeado.
                this.navegarFuera(this.rutaListaConsultas)
            })
        }
    }
}
</script>