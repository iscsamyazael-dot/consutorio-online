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
                            — {{ p.edad_formateada }} | {{ p.sexo }}
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
        <!-- VENTANA FLOTANTE DINÁMICA (Réplica compacta del HeaderConsulta) -->
        <!-- VENTANA FLOTANTE DINÁMICA -->
        <div :class="['barra-flotante-clinica', { 'visible': mostrarFlotante }]">
            <div class="d-flex align-items-center justify-content-between w-100 px-3">
                
                <!-- 1. HEADER / DATOS DEL PACIENTE (Reutilizando la lógica de HeaderConsulta) -->
                <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                    <span class="d-flex align-items-center mr-2" v-if="paciente">
                        <span class="avatar-flotante mr-2">{{ paciente.nombre?.substring(0, 2) }}</span>
                        <span class="font-weight-bold text-dark">{{ paciente.nombre }} {{ paciente.apellido_paterno }}</span>
                    </span>

                    <span class="badge badge-primary rounded-pill px-2 py-1" v-if="paciente && paciente.paciente_id">
                        Folio: {{ paciente.paciente_id }}
                    </span>
                    <span class="badge badge-info rounded-pill px-2 py-1" v-if="paciente && paciente.edad_formateada">
                        {{ paciente.edad_formateada }}
                    </span>
                    <span class="badge badge-secondary rounded-pill px-2 py-1" v-if="paciente && paciente.sexo">
                        {{ paciente.sexo }}
                    </span>

                    <!-- Estado de la consulta (igual que el hero) -->
                    <span class="badge badge-success rounded-pill px-2 py-1 d-none d-md-inline-block" v-if="paciente && paciente.estado">
                        Consulta: {{ paciente.estado }}
                    </span>

                    <!-- IA Activa (igual que el hero) -->
                    <span class="badge badge-warning text-dark rounded-pill px-2 py-1 d-none d-md-inline-block">
                        IA Activa
                    </span>

                    <!-- Tipo de sangre -->
                    <span class="badge badge-danger rounded-pill px-2 py-1" v-if="paciente && paciente.tipo_sangre">
                        <i class="fas fa-tint mr-1"></i>{{ paciente.tipo_sangre }}
                    </span>

                    <!-- Alergias: ahora con el valor real, no solo la etiqueta -->
                    <span class="badge badge-danger rounded-pill px-2 py-1" v-if="paciente && paciente.alergias">
                        <i class="fas fa-exclamation-triangle mr-1"></i> {{ paciente.alergias }}
                    </span>
                    <span class="badge badge-light text-muted border rounded-pill px-2 py-1 d-none d-lg-inline-block" v-else-if="paciente && paciente.id">
                        Sin alergias
                    </span>

                    <!-- Alergia a medicamentos (mismo morado que usa el hero) -->
                    <span class="badge rounded-pill px-2 py-1 d-none d-lg-inline-block" style="background-color:#7c3aed;color:#fff;" v-if="hayAlergiaMedicamentos">
                        <i class="fas fa-pills mr-1"></i> {{ paciente.alergia_medicamentos }}
                    </span>
                    <span class="badge badge-light text-muted border rounded-pill px-2 py-1 d-none d-lg-inline-block" v-else-if="paciente && paciente.id">
                        Sin alergia a medicamentos registrada
                    </span>

                    <span class="badge rounded-pill px-2 py-1"
                        :class="progresoHistoriaClinica && progresoHistoriaClinica.completa ? 'badge-success' : 'badge-warning text-dark'"
                        v-if="progresoHistoriaClinica">
                        H.C: {{ progresoHistoriaClinica.completados }}/{{ progresoHistoriaClinica.total }}
                    </span>
                </div>

                <!-- 2. SIGNOS VITALES (Mini resumen en la barra flotante) -->
                <!-- 2. SIGNOS VITALES (mismo lenguaje visual que SignosVitales.vue, en formato chip) -->
                <div class="d-none d-xl-flex align-items-center mx-2" v-if="ultimoTriage">
                    <span class="vital-chip-flotante" :class="'vital-chip-flotante--' + estadoPresion(ultimoTriage.presion)">
                        <span class="vital-chip-flotante-label">PA</span>
                        <span class="vital-chip-flotante-value">{{ ultimoTriage.presion || '--/--' }}</span>
                    </span>
                    <span class="vital-chip-flotante" :class="'vital-chip-flotante--' + estadoSaturacion(ultimoTriage.saturacion)">
                        <span class="vital-chip-flotante-label">SpO₂</span>
                        <span class="vital-chip-flotante-value">{{ ultimoTriage.saturacion || '--' }}%</span>
                    </span>
                    <span class="vital-chip-flotante" :class="'vital-chip-flotante--' + estadoTemperatura(ultimoTriage.temperatura)">
                        <span class="vital-chip-flotante-label">Temp</span>
                        <span class="vital-chip-flotante-value">{{ ultimoTriage.temperatura || '--' }}°C</span>
                    </span>
                    <span class="vital-chip-flotante" :class="'vital-chip-flotante--' + estadoFrecuenciaCardiaca(ultimoTriage.frecuencia_cardiaca)">
                        <span class="vital-chip-flotante-label">FC</span>
                        <span class="vital-chip-flotante-value">{{ ultimoTriage.frecuencia_cardiaca || '--' }} lpm</span>
                    </span>
                    <span class="vital-chip-flotante" :class="'vital-chip-flotante--' + estadoFrecuenciaRespiratoria(ultimoTriage.frecuencia_respiratoria)">
                        <span class="vital-chip-flotante-label">FR</span>
                        <span class="vital-chip-flotante-value">{{ ultimoTriage.frecuencia_respiratoria || '--' }} rpm</span>
                    </span>
                    <span class="vital-chip-flotante vital-chip-flotante--neutro">
                        <span class="vital-chip-flotante-label">Peso</span>
                        <span class="vital-chip-flotante-value">{{ ultimoTriage.peso || '--' }} kg</span>
                    </span>
                    <span class="vital-chip-flotante vital-chip-flotante--neutro">
                        <span class="vital-chip-flotante-label">Talla</span>
                        <span class="vital-chip-flotante-value">{{ ultimoTriage.talla || '--' }} cm</span>
                    </span>
                    <span class="vital-chip-flotante vital-chip-flotante--neutro" v-if="imcUltimoTriage">
                        <span class="vital-chip-flotante-label">
                            IMC
                            <span v-if="imcUltimoTriage.tipo === 'pediatrico'">P{{ imcUltimoTriage.percentil }}</span>
                        </span>
                        <span class="vital-chip-flotante-value">
                            {{ imcUltimoTriage.bmi }}
                            <span class="vital-chip-imc-badge" :class="claseImcFlotante(imcUltimoTriage)">{{ imcUltimoTriage.clasificacion }}</span>
                        </span>
                    </span>
                </div>
                <div v-else class="d-none d-xl-flex align-items-center bg-light border rounded px-3 py-1 mx-2 text-warning small">
                    <i class="fas fa-exclamation-circle mr-1"></i> Sin signos vitales registrados
                </div>

                <!-- 3. BOTONES DE ACCIÓN RÁPIDA -->
                <div class="d-flex align-items-center flex-shrink-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="salirConsulta">
                        <i class="fas fa-sign-out-alt"></i> Salir
                    </button>
                </div>
            </div>
        </div>
        <HeaderConsulta 
            :pacienteId="pacienteId"
            :progreso-ia="progresoHistoriaClinica" />
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
            @triage-agregado="onTriageAgregado"
        />

        <!-- FILA PRINCIPAL -->
        <div class="row">
            <div class="col-lg-3">
                <HistorialClinico
                    :paciente-id="pacienteId"
                    :ia-data="iaData"
                    :recetas="paciente.recetas || []"
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
                    :consulta-id="consultaId"
                    @diagnostico-guardado="onDiagnosticoGuardado"
                />
            </div>
            <div class="col-lg-3">
                <RecetaInteligente
                    :sintomas="sintomasDetectados"
                    :consulta-id="consultaId"
                    :diagnostico-confirmado="diagnosticoConfirmado"
                />
                <DerivacionClinica
                    :sintomas="sintomasDetectados"
                    :consulta-id="consultaId"
                    :diagnostico-confirmado="diagnosticoConfirmado"
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
import { evaluarIMC } from '@/utils/bmiPercentile.js'
import lmsTable from '@/data/bmi-lms-cdc.json'

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
            todosPacientes: [],
            diagnosticoConfirmado: null,
            progresoHistoriaClinica: null,
            
            mostrarFlotante: false,
            ultimoScrollY: 0,

            triageLocalConsulta: null
        }
    },
    computed: {
        hasPaciente() {
            return this.pacienteId !== null &&
                   this.pacienteId !== undefined &&
                   this.pacienteId !== '';
        },
        hayAlergiaMedicamentos() {
            return this.paciente && 
                this.paciente.alergia_medicamentos && 
                this.paciente.alergia_medicamentos.trim() !== '' &&
                this.paciente.alergia_medicamentos.toLowerCase() !== 'ninguna' &&
                this.paciente.alergia_medicamentos.toLowerCase() !== 'no';
        },
        ultimoTriage() {
            if (this.triageLocalConsulta) return this.triageLocalConsulta;
            if (!this.listaEsperaIdHoy) return null;
            const triages = this.paciente?.triages || [];
            return triages.find(t => t.lista_espera_id == this.listaEsperaIdHoy) || null;
        },
        edadPacienteMesesTriage() {
            if (this.paciente?.fecha_nacimiento) {
                const [anioN, mesN, diaN] = this.paciente.fecha_nacimiento.split('-').map(Number)
                const nacimiento = new Date(anioN, mesN - 1, diaN)
                if (!isNaN(nacimiento.getTime())) {
                    const hoy = new Date()
                    let meses = (hoy.getFullYear() - nacimiento.getFullYear()) * 12
                    meses += hoy.getMonth() - nacimiento.getMonth()
                    if (hoy.getDate() < nacimiento.getDate()) meses -= 1
                    return Math.max(meses, 0)
                }
            }
            if (this.paciente?.edad !== null && this.paciente?.edad !== undefined && this.paciente?.edad !== '') {
                const valor = Number(this.paciente.edad)
                const unidad = this.paciente?.edad_unidad || 'anios'
                if (unidad === 'dias')  return Math.floor(valor / 30)
                if (unidad === 'meses') return valor
                return valor * 12
            }
            return null
        },

        sexoPacienteNormalizadoTriage() {
            const s = (this.paciente?.sexo || '').toString().trim().toLowerCase()
            if (!s) return null
            if (s.startsWith('m')) return 'M'
            if (s.startsWith('f')) return 'F'
            return null
        },

        imcUltimoTriage() {
            if (!this.ultimoTriage) return null
            const pesoKg = Number(this.ultimoTriage.peso)
            const tallaCm = Number(this.ultimoTriage.talla)
            if (!pesoKg || !tallaCm) return null
            const agemos = this.edadPacienteMesesTriage
            const sexo = this.sexoPacienteNormalizadoTriage
            if (agemos === null || !sexo) {
                const bmi = pesoKg / Math.pow(tallaCm / 100, 2)
                return { bmi: Number(bmi.toFixed(2)), tipo: 'sin_clasificar', clasificacion: 'Sin clasificar' }
            }
            return evaluarIMC({ pesoKg, tallaCm, edadAnios: Math.floor(agemos / 12), edadMeses: agemos % 12, sexo }, lmsTable)
        }

    },
    mounted() {
        window.addEventListener('scroll', this.manejarScroll);

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
        window.removeEventListener('scroll', this.manejarScroll);
        window.removeEventListener('beforeunload', this.confirmarSalidaNativa)
        document.removeEventListener('click', this.interceptarNavegacion, true)
        window.removeEventListener('resize', this.ajustarAlturaHeader);
    },
    methods: {
        manejarScroll() {
            const scrollActual = window.pageYOffset || document.documentElement.scrollTop;
            
            // Si bajamos más de 150 píxeles y vamos hacia abajo, se muestra.
            // Si regresamos hacia arriba o estamos cerca del tope, se oculta.
            if (scrollActual > 150 && scrollActual > this.ultimoScrollY) {
                this.mostrarFlotante = true;
            } else if (scrollActual < 50 || scrollActual < this.ultimoScrollY) {
                this.mostrarFlotante = false;
            }
            
            this.ultimoScrollY = scrollActual <= 0 ? 0 : scrollActual;
        },
        
        estadoPresion(presion) {
            if (!presion || typeof presion !== 'string' || !presion.includes('/')) return ''
            const [sis, dia] = presion.split('/').map(parseFloat)
            if (isNaN(sis) || isNaN(dia)) return ''
            if (sis >= 140 || sis < 90 || dia >= 90 || dia < 60) return 'critico'
            if (sis >= 121 || dia >= 81) return 'alerta'
            return 'normal'
        },

        estadoSaturacion(valor) {
            const n = parseFloat(valor)
            if (isNaN(n)) return ''
            if (n < 90) return 'critico'
            if (n < 95) return 'alerta'
            return 'normal'
        },

        estadoTemperatura(valor) {
            const n = parseFloat(valor)
            if (isNaN(n)) return ''
            if (n >= 38.5 || n <= 35) return 'critico'
            if (n >= 37.6 || n < 36.1) return 'alerta'
            return 'normal'
        },

        estadoFrecuenciaCardiaca(valor) {
            const n = parseFloat(valor)
            if (isNaN(n)) return ''
            if (n >= 120 || n < 50) return 'critico'
            if (n >= 101 || n < 60) return 'alerta'
            return 'normal'
        },

        estadoFrecuenciaRespiratoria(valor) {
            const n = parseFloat(valor)
            if (isNaN(n)) return ''
            if (n >= 25 || n < 8) return 'critico'
            if (n >= 21 || n < 12) return 'alerta'
            return 'normal'
        },

        claseImcFlotante(info) {
            if (!info) return ''
            if (info.tipo === 'sin_clasificar') return 'vital-chip-imc-badge--neutro'
            const c = (info.clasificacion || '').toLowerCase()
            if (c.includes('normal')) return 'vital-chip-imc-badge--normal'
            if (c.includes('bajo')) return 'vital-chip-imc-badge--warning'
            if (c.includes('sobrepeso')) return 'vital-chip-imc-badge--warning'
            if (c.includes('obesidad')) return 'vital-chip-imc-badge--critical'
            return 'vital-chip-imc-badge--neutro'
        },

        onTriageAgregado(triage) {
            this.triageLocalConsulta = triage;
            this.obtenerPaciente();
        },

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
                        (this.paciente.edad_formateada || '') +
                        ' | ' +
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

            // NUEVO: progreso de Historia Clínica (NOM-004), para el badge
            // del hero header. Se mantiene el valor anterior si esta
            // respuesta puntual no trajera el campo (no debería pasar, pero
            // así el badge nunca "retrocede" a null por un evento aislado).
            if (iaData && iaData.historia_clinica_progreso) {
                this.progresoHistoriaClinica = iaData.historia_clinica_progreso;
            }

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
        },

        onDiagnosticoGuardado({ diagnostico, recomendaciones }) {
            this.diagnosticoConfirmado = diagnostico 
            if (this.$refs.notaPsoapp) {
                this.$refs.notaPsoapp.sobrescribirSeccion('A', diagnostico)
                if (recomendaciones) {
                    this.$refs.notaPsoapp.sobrescribirSeccion('P2', recomendaciones)
                }
                this.$refs.notaPsoapp.guardar('borrador')
            }
        }
    }
}
</script>

<style scoped>
    .barra-flotante-clinica {
        position: fixed;
        top: 0;
        left: 0; /* O left: 260px si tu menú lateral es fijo */
        right: 0;
        height: 62px;
        background-color: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(8px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        display: flex;
        align-items: center;
        z-index: 1050;
        
        transform: translateY(-100%);
        opacity: 0;
        transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
    }

    .barra-flotante-clinica.visible {
        transform: translateY(0);
        opacity: 1;
    }

    .vital-chip-flotante {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        line-height: 1.05;
        padding: 3px 8px;
        margin-right: 6px;
        border-radius: 8px;
        border: 1px solid #E3E8EF;
        background: #F5F7FA;
    }
    .vital-chip-flotante-label {
        font-family: 'Inter', system-ui, sans-serif;
        font-size: .5rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #51607A;
        letter-spacing: .2px;
    }
    .vital-chip-flotante-value {
        font-family: 'IBM Plex Mono', monospace;
        font-size: .74rem;
        font-weight: 600;
        color: #0F172A;
    }
    .vital-chip-flotante--normal {
        background: #E4F7EF;
        border-color: rgba(14,159,110,.35);
    }
    .vital-chip-flotante--normal .vital-chip-flotante-value { color: #0E9F6E; }

    .vital-chip-flotante--alerta {
        background: #FDF1DF;
        border-color: rgba(217,119,6,.35);
    }
    .vital-chip-flotante--alerta .vital-chip-flotante-value { color: #D97706; }

    .vital-chip-flotante--critico {
        background: #FCE8E8;
        border-color: rgba(220,38,38,.4);
    }
    .vital-chip-flotante--critico .vital-chip-flotante-value { color: #DC2626; }

    .vital-chip-flotante--neutro {
        background: #F5F7FA;
        border-color: #E3E8EF;
    }
    .vital-chip-flotante--neutro .vital-chip-flotante-value { color: #0F172A; }

    .vital-chip-imc-badge {
        font-size: .58rem;
        font-weight: 700;
        padding: 1px 6px;
        border-radius: 999px;
        margin-left: 4px;
    }
    .avatar-flotante {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0d6efd, #0dcaf0);
        color: #fff;
        font-size: .85rem;
        font-weight: bold;
        flex-shrink: 0;
        text-transform: uppercase;
    }
    .vital-chip-imc-badge--normal  { background: #E4F7EF; color: #0E9F6E; }
    .vital-chip-imc-badge--warning { background: #FDF1DF; color: #D97706; }
    .vital-chip-imc-badge--critical{ background: #FCE8E8; color: #DC2626; }
    .vital-chip-imc-badge--neutro  { background: #F5F7FA; color: #94A3B8; }
</style>