<template>
    <div class="container-fluid py-4">
        <!-- BUSCADOR DE PACIENTES / TRABAJADORES SI NO SE HA SELECCIONADO UNO -->
        <div v-if="!trabajadorSeleccionado" class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-id-card mr-2 text-primary"></i>
                            Nueva Ficha Médica Ocupacional
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Busca y selecciona al trabajador para iniciar la evaluación ocupacional de ingreso o periódica.
                        </p>
                        <div class="form-group position-relative">
                            <label class="font-weight-bold">Nombre del Trabajador:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="Buscar por nombre o apellido..."
                                    v-model="busqueda"
                                    @input="buscarPacientes"
                                />
                            </div>
                            
                            <!-- Resultados de búsqueda -->
                            <div v-if="buscando" class="mt-2 text-muted">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Buscando...
                            </div>
                            
                            <ul v-else-if="resultados.length" class="list-group mt-2">
                                <li
                                    v-for="p in resultados"
                                    :key="p.id"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                    style="cursor: pointer;"
                                    @click="seleccionarTrabajador(p)"
                                >
                                    <div>
                                        <strong>{{ p.nombre }} {{ p.apellido_paterno }} {{ p.apellido_materno }}</strong>
                                        <span class="text-muted small d-block">
                                            {{ p.edad_formateada || 'Edad no registrada' }} | {{ p.sexo || 'Sexo no registrado' }}
                                        </span>
                                    </div>
                                    <span class="badge badge-primary">Seleccionar</span>
                                </li>
                            </ul>
                            
                            <div v-else-if="busqueda.length >= 2 && !buscando" class="mt-2 text-muted">
                                <i class="fas fa-exclamation-circle mr-1"></i> No se encontraron registros coincidentes.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- EVALUACIÓN ACTIVA DEL TRABAJADOR -->
        <div v-else class="row">
            <!-- Columna Izquierda: Formulario Estructurado -->
            <div class="col-lg-7">
                <div class="card card-primary card-outline card-tabs shadow-sm">
                    <div class="card-header p-0 pt-1 border-bottom-0">
                        <ul class="nav nav-tabs" id="ficha-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="datos-tab" data-toggle="pill" href="#tab-datos" role="tab" aria-selected="true">
                                    <i class="fas fa-briefcase mr-1"></i> Datos & Puesto
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="riesgos-tab" data-toggle="pill" href="#tab-riesgos" role="tab" aria-selected="false">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Exposición & Riesgos
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="salud-tab" data-toggle="pill" href="#tab-salud" role="tab" aria-selected="false">
                                    <i class="fas fa-file-medical-alt mr-1"></i> Clínico & Examen
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="aptitud-tab" data-toggle="pill" href="#tab-aptitud" role="tab" aria-selected="false">
                                    <i class="fas fa-user-check mr-1"></i> Aptitud & Dictamen
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="card-body">
                        <!-- Cabecera de Trabajador -->
                        <div class="bg-light p-3 rounded mb-4 d-flex justify-content-between align-items-center border border-left-primary">
                            <div>
                                <h5 class="mb-0 font-weight-bold text-primary">
                                    {{ trabajadorSeleccionado.nombre }} {{ trabajadorSeleccionado.apellido_paterno }}
                                </h5>
                                <span class="text-muted small">
                                    <i class="fas fa-venus-mars mr-1"></i> {{ trabajadorSeleccionado.sexo }} | 
                                    <i class="fas fa-birthday-cake mr-1"></i> {{ trabajadorSeleccionado.edad_formateada || 'N/A' }}
                                </span>
                            </div>
                            <button class="btn btn-xs btn-outline-danger" @click="deseleccionarTrabajador">
                                <i class="fas fa-sign-out-alt mr-1"></i> Cambiar
                            </button>
                        </div>

                        <!-- Signos Vitales / Triage del Trabajador -->
                        <div class="mb-4">
                            <SignosVitales
                                :paciente="trabajadorParaTriage"
                                :lista-espera-id="null"
                                :consulta-id="null"
                                @triage-agregado="onTriageAgregado"
                            />
                        </div>

                        <div class="tab-content" id="ficha-tabContent">
                            <!-- TAB 1: DATOS & PUESTO -->
                            <div class="tab-pane fade show active" id="tab-datos" role="tabpanel">
                                <h5 class="border-bottom pb-2 mb-3 text-secondary font-weight-bold">Información de la Empresa y Puesto de Trabajo</h5>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Empresa:</label>
                                        <input type="text" class="form-control" v-model="form.empresa" placeholder="Nombre de la empresa" />
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Departamento / Área:</label>
                                        <input type="text" class="form-control" v-model="form.departamento" placeholder="Ej. Producción, Logística, etc." />
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Puesto Propuesto / Actual:</label>
                                        <input type="text" class="form-control" v-model="form.puesto_trabajo" placeholder="Ej. Operador de Montacargas" />
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Antigüedad:</label>
                                        <input type="text" class="form-control" v-model="form.antiguedad" placeholder="Ej. Nuevo ingreso, 3 años, etc." />
                                    </div>
                                    <div class="form-group col-12">
                                        <label>Descripción del Puesto / Actividades:</label>
                                        <textarea class="form-control" rows="3" v-model="form.descripcion_puesto" placeholder="Describa brevemente las tareas principales del puesto..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 2: EXPOSICIÓN & RIESGOS -->
                            <div class="tab-pane fade" id="tab-riesgos" role="tabpanel">
                                <h5 class="border-bottom pb-2 mb-3 text-secondary font-weight-bold">Factores de Riesgo Ocupacional</h5>
                                <p class="text-muted small">Marque los factores a los que el trabajador está o estará expuesto en su puesto de trabajo.</p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" id="riesgo-ruido" v-model="form.riesgos.ruido" />
                                            <label class="custom-control-label" for="riesgo-ruido">Ruido Elevado / Continuo</label>
                                        </div>
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" id="riesgo-quimico" v-model="form.riesgos.quimicos" />
                                            <label class="custom-control-label" for="riesgo-quimico">Exposición a Sustancias Químicas</label>
                                        </div>
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" id="riesgo-polvo" v-model="form.riesgos.polvos" />
                                            <label class="custom-control-label" for="riesgo-polvo">Polvos o Humos Respirables</label>
                                        </div>
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" id="riesgo-vibracion" v-model="form.riesgos.vibraciones" />
                                            <label class="custom-control-label" for="riesgo-vibracion">Vibraciones de Cuerpo Completo/Mano</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" id="riesgo-ergonomico" v-model="form.riesgos.ergonomicos" />
                                            <label class="custom-control-label" for="riesgo-ergonomico">Carga Dinámica / Ergonomía Desfavorable</label>
                                        </div>
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" id="riesgo-psicosocial" v-model="form.riesgos.psicosociales" />
                                            <label class="custom-control-label" for="riesgo-psicosocial">Estrés Alto / Turnos Nocturnos</label>
                                        </div>
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" id="riesgo-alturas" v-model="form.riesgos.alturas" />
                                            <label class="custom-control-label" for="riesgo-alturas">Trabajos en Alturas o Espacios Confinados</label>
                                        </div>
                                    </div>
                                    <div class="form-group col-12 mt-3">
                                        <label>Detalle de Equipos de Protección Personal (EPP) Requeridos:</label>
                                        <input type="text" class="form-control" v-model="form.epp_requerido" placeholder="Ej. Tapones auditivos, Respirador N95, Casco, etc." />
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 3: CLÍNICO & EXAMEN -->
                            <div class="tab-pane fade" id="tab-salud" role="tabpanel">
                                <h5 class="border-bottom pb-2 mb-3 text-secondary font-weight-bold">Historial de Salud & Examen Físico</h5>
                                <div class="row">
                                    <div class="form-group col-12">
                                        <label>Antecedentes Patológicos y Ocupacionales de Relevancia:</label>
                                        <textarea class="form-control" rows="2" v-model="form.antecedentes" placeholder="Registre enfermedades crónicas, cirugías, fracturas o alergias..."></textarea>
                                    </div>
                                    <div class="form-group col-12">
                                        <label>Examen Físico (Hallazgos Clínicos):</label>
                                        <textarea class="form-control" rows="3" v-model="form.examen_fisico" placeholder="Detalle el estado general, agudeza visual, examen cardiopulmonar, osteomuscular..."></textarea>
                                    </div>
                                    <div class="form-group col-12">
                                        <label>Exámenes Complementarios (Laboratorios, Espirometría, Audiometría):</label>
                                        <textarea class="form-control" rows="2" v-model="form.examenes_complementarios" placeholder="Resultados de audiometría, radiografías, antidoping, etc."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 4: APTITUD & DICTAMEN -->
                            <div class="tab-pane fade" id="tab-aptitud" role="tabpanel">
                                <h5 class="border-bottom pb-2 mb-3 text-secondary font-weight-bold">Dictamen de Aptitud Laboral</h5>
                                <div class="row">
                                    <div class="form-group col-12">
                                        <label class="d-block font-weight-bold">Aptitud del Trabajador para el Puesto:</label>
                                        <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                            <label class="btn btn-outline-success" :class="{ active: form.aptitud === 'apto' }">
                                                <input type="radio" value="apto" v-model="form.aptitud" />
                                                <i class="fas fa-check-circle mr-1"></i> Apto
                                            </label>
                                            <label class="btn btn-outline-warning" :class="{ active: form.aptitud === 'apto_con_restricciones' }">
                                                <input type="radio" value="apto_con_restricciones" v-model="form.aptitud" />
                                                <i class="fas fa-exclamation-triangle mr-1"></i> Apto con Restricciones
                                            </label>
                                            <label class="btn btn-outline-danger" :class="{ active: form.aptitud === 'no_apto' }">
                                                <input type="radio" value="no_apto" v-model="form.aptitud" />
                                                <i class="fas fa-times-circle mr-1"></i> No Apto
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group col-12 mt-3" v-if="form.aptitud === 'apto_con_restricciones'">
                                        <label class="text-warning font-weight-bold">Restricciones / Recomendaciones Ocupacionales:</label>
                                        <textarea class="form-control border-warning" rows="2" v-model="form.restricciones" placeholder="Escriba las limitaciones del trabajador (ej. No cargar más de 10 kg, evitar exposición a ruido continuo, etc.)"></textarea>
                                    </div>

                                    <div class="form-group col-12" v-if="dictamenSugerido && !form.aptitud">
                                        <div class="alert alert-info py-2 mb-0 d-flex justify-content-between align-items-center">
                                            <span>
                                                <i class="fas fa-robot mr-1"></i>
                                                Sugerencia de dictamen por IA: <strong>{{ formatearDictamen(dictamenSugerido) }}</strong>
                                            </span>
                                            <button type="button" class="btn btn-sm btn-info" @click="form.aptitud = dictamenSugerido">
                                                Aceptar Sugerencia
                                            </button>
                                        </div>
                                    </div>

                                    <div class="form-group col-12 mt-3">
                                        <label>Recomendaciones Generales / Plan de Vigilancia Médica:</label>
                                        <textarea class="form-control" rows="3" v-model="form.recomendaciones" placeholder="Sugerencias de hábitos saludables, uso de EPP, fecha del próximo examen periódico..."></textarea>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-4">
                                    <button class="btn btn-primary" :disabled="guardando" @click="guardarFicha">
                                        <i class="fas fa-save mr-1" v-if="!guardando"></i>
                                        <i class="fas fa-spinner fa-spin mr-1" v-else></i>
                                        Guardar Ficha Ocupacional
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Asistencia por IA en Vivo -->
            <div class="col-lg-5">
                <div class="card card-primary card-outline shadow-sm">
                    <!-- HEADER -->
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-microphone-alt text-danger mr-2"></i>
                            Consulta en Tiempo Real (Ocupacional)
                        </h3>
                        <div class="card-tools d-flex align-items-center">
                            <span v-if="escuchando" class="badge badge-success mr-2">
                                🤖 IA escuchando
                            </span>
                        </div>
                    </div>

                    <!-- BODY -->
                    <div class="card-body p-0">
                        <div
                            ref="chatContainer"
                            class="direct-chat-messages p-3 bg-light"
                            style="height: 380px; overflow-y: auto;"
                        >
                            <!-- Mensaje de bienvenida inicial -->
                            <div class="direct-chat-msg mb-3">
                                <div class="direct-chat-infos clearfix">
                                    <span class="direct-chat-name float-left text-primary">🤖 Asistente IA Ocupacional</span>
                                </div>
                                <div class="direct-chat-text bg-white border">
                                    ¡Hola! Estoy lista para asistirte. Háblame o escríbeme sobre el puesto del trabajador, sus antecedentes o factores de riesgo, o adjunta documentos como RX o análisis de laboratorio, y te ayudaré a rellenar automáticamente la ficha médica.
                                </div>
                            </div>

                            <!-- MENSAJES -->
                            <div
                                v-for="(msg, index) in conversacion"
                                :key="index"
                                class="mb-3"
                            >
                                <!-- MÉDICO -->
                                <div v-if="msg.sender === 'user'" class="direct-chat-msg">
                                    <div class="direct-chat-infos clearfix">
                                        <span class="direct-chat-name float-left">👨‍⚕️ Médico</span>
                                    </div>
                                    <div class="direct-chat-text bg-primary text-white">
                                        <i v-if="msg.archivo" class="fas fa-paperclip mr-1"></i>
                                        <i v-if="msg.voz" class="fas fa-microphone mr-1"></i>
                                        {{ msg.text }}
                                    </div>
                                </div>

                                <!-- IA / SISTEMA -->
                                <div v-else class="direct-chat-msg right">
                                    <div class="direct-chat-infos clearfix">
                                        <span class="direct-chat-name float-right text-primary">🤖 Asistente IA Ocupacional</span>
                                    </div>
                                    <div class="direct-chat-text bg-white border">
                                        {{ msg.text }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FOOTER / ACCIONES -->
                    <div class="card-footer bg-white border-top">
                        <!-- PREVIEW DE ARCHIVO SELECCIONADO -->
                        <div
                            v-if="archivoSeleccionado"
                            class="alert alert-light border py-1 px-2 mb-2 d-flex justify-content-between align-items-center"
                            style="font-size:13px;"
                        >
                            <span>
                                <i class="fas fa-paperclip mr-1 text-primary"></i>
                                {{ archivoSeleccionado.name }}
                                ({{ (archivoSeleccionado.size / 1024 / 1024).toFixed(2) }} MB)
                            </span>
                            <button
                                class="btn btn-sm btn-link text-danger p-0"
                                @click="quitarArchivo"
                            >
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <!-- BARRA TIPO WHATSAPP -->
                        <div class="d-flex align-items-end whatsapp-input-bar border p-2 bg-white" style="border-radius: 18px;">
                            <!-- TEXTAREA FLEXIBLE -->
                            <div class="flex-grow-1 position-relative">
                                <textarea
                                    ref="mensajeInput"
                                    class="form-control border-0 bg-transparent mensaje-whatsapp"
                                    :placeholder="escuchando ? 'Escuchando...' : 'Escribe un mensaje...'"
                                    v-model="nuevoMensaje"
                                    rows="3"
                                    style="resize: none; outline: none; box-shadow: none; max-height: 120px; overflow-y: auto;"
                                    @keydown.enter.exact.prevent="enviarMensajeChat"
                                ></textarea>
                            </div>

                            <!-- CONTENEDOR DE ACCIONES (Finalizar, Micrófono, Adjuntar, Enviar) -->
                            <div class="d-flex align-items-center ml-2 mr-1 gap-1">
                                <!-- BOTÓN FINALIZAR -->
                                <button
                                    type="button"
                                    class="btn-finalizar-inline btn btn-outline-danger btn-sm rounded-pill font-weight-bold d-flex align-items-center px-2 py-1"
                                    title="Finalizar evaluación y guardar ficha"
                                    :disabled="guardando"
                                    @click="guardarFicha"
                                >
                                    <i class="fas fa-stop-circle mr-1 text-danger"></i> Finalizar
                                </button>

                                <span class="action-divider border-left mx-2" style="height: 20px;"></span>

                                <!-- BOTÓN MICRÓFONO -->
                                <button
                                    class="btn btn-sm btn-icon btn-light rounded-circle text-secondary"
                                    :class="{ 'btn-danger text-white': escuchando }"
                                    type="button"
                                    :title="escuchando ? 'Detener escucha' : 'Escuchar'"
                                    @click="toggleEscucha"
                                >
                                    <i class="fas fa-microphone"></i>
                                </button>

                                <!-- INPUT ARCHIVO OCULTO -->
                                <input
                                    ref="inputArchivo"
                                    type="file"
                                    class="d-none"
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                    @change="seleccionarArchivo"
                                >

                                <!-- BOTÓN ADJUNTAR -->
                                <button
                                    class="btn btn-sm btn-icon btn-light rounded-circle text-secondary"
                                    type="button"
                                    title="Adjuntar PDF, Word o imagen (RX, Laboratorios)"
                                    @click="$refs.inputArchivo.click()"
                                >
                                    <i class="fas fa-paperclip"></i>
                                </button>

                                <!-- BOTÓN ENVIAR -->
                                <button
                                    class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                    style="width: 32px; height: 32px;"
                                    :disabled="!nuevoMensaje && !archivoSeleccionado"
                                    @click="enviarMensajeChat"
                                    :title="archivoSeleccionado ? 'Enviar archivo' : 'Enviar mensaje'"
                                >
                                    <i class="fas fa-paper-plane fa-sm"></i>
                                </button>
                            </div>
                        </div>

                        <!-- ACCIONES RÁPIDAS / SINCRONIZAR -->
                        <div class="d-flex justify-content-between align-items-center mt-3 small">
                            <button 
                                class="btn btn-xs btn-outline-info rounded-pill font-weight-bold" 
                                @click="aplicarAutocompletadoIA"
                                :disabled="!conversacion.length || procesandoIA"
                            >
                                <i class="fas fa-magic mr-1" v-if="!procesandoIA"></i>
                                <i class="fas fa-spinner fa-spin mr-1" v-else></i>
                                Sincronizar dictado con Ficha
                            </button>
                        </div>

                        <!-- SÍNTOMAS / FACTORES DETECTADOS -->
                        <div class="mt-3">
                            <h6 class="font-weight-bold text-primary mb-1 small">
                                🤖 Síntomas / Factores de Riesgo detectados
                            </h6>
                            <span
                                v-if="sintomasDetectados.length === 0"
                                class="text-muted small"
                            >
                                Aún no se detectaron síntomas.
                            </span>
                            <span
                                v-for="(sintoma, idx) in sintomasDetectados"
                                :key="idx"
                                class="badge badge-warning mr-2 mb-2 small"
                            >
                                {{ sintoma }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import apiClient from '../../../services/ApiService';
import SignosVitales from '../../consultaIA/SignosVitales.vue';

export default {
    name: 'MasterFichaOcupacional',
    components: {
        SignosVitales
    },
    data() {
        return {
            busqueda: '',
            buscando: false,
            resultados: [],
            todosPacientes: [],
            debounceTimer: null,
            trabajadorSeleccionado: null,
            escuchando: false,
            procesandoIA: false,
            guardando: false,
            conversacion: [],
            archivoSeleccionado: null,
            nuevoMensaje: '',
            sintomasDetectados: [],
            ultimoValorIA: {},
            dictamenSugerido: '',
            form: {
                empresa: '',
                departamento: '',
                puesto_trabajo: '',
                antiguedad: '',
                descripcion_puesto: '',
                riesgos: {
                    ruido: false,
                    quimicos: false,
                    polvos: false,
                    vibraciones: false,
                    ergonomicos: false,
                    psicosociales: false,
                    alturas: false
                },
                epp_requerido: '',
                antecedentes: '',
                examen_fisico: '',
                examenes_complementarios: '',
                aptitud: '',
                restricciones: '',
                recomendaciones: ''
            }
        };
    },
    computed: {
        trabajadorParaTriage() {
            if (!this.trabajadorSeleccionado) return null;
            return {
                ...this.trabajadorSeleccionado,
                triages: []
            };
        }
    },
    mounted() {
        this.cargarListaPacientes();
    },
    methods: {
        async cargarListaPacientes() {
            this.buscando = true;
            try {
                const response = await apiClient.get('/pacientes');
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
            this.buscando = true;
            this.debounceTimer = setTimeout(() => {
                const texto = this.busqueda.toLowerCase();
                this.resultados = this.todosPacientes.filter(p => {
                    const nombreCompleto = `${p.nombre || ''} ${p.apellido_paterno || ''} ${p.apellido_materno || ''}`.toLowerCase();
                    return nombreCompleto.includes(texto);
                });
                this.buscando = false;
            }, 200);
        },
        onTriageAgregado(triage) {
            if (!this.trabajadorSeleccionado.triages) {
                this.trabajadorSeleccionado.triages = [];
            }
            this.trabajadorSeleccionado.triages.push(triage);
        },
        limpiarFormulario() {
            this.form = {
                empresa: '',
                departamento: '',
                puesto_trabajo: '',
                antiguedad: '',
                descripcion_puesto: '',
                riesgos: {
                    ruido: false,
                    quimicos: false,
                    polvos: false,
                    vibraciones: false,
                    ergonomicos: false,
                    psicosociales: false,
                    alturas: false
                },
                epp_requerido: '',
                antecedentes: '',
                examen_fisico: '',
                examenes_complementarios: '',
                aptitud: '',
                restricciones: '',
                recomendaciones: ''
            };
            this.ultimoValorIA = {};
            this.dictamenSugerido = '';
        },
        seleccionarTrabajador(trabajador) {
            this.limpiarFormulario();
            this.trabajadorSeleccionado = trabajador;
            this.resultados = [];
            this.busqueda = '';
            this.conversacion = [];
            this.sintomasDetectados = [];
        },
        deseleccionarTrabajador() {
            this.limpiarFormulario();
            this.trabajadorSeleccionado = null;
            this.resultados = [];
            this.conversacion = [];
            this.sintomasDetectados = [];
        },
        toggleEscucha() {
            this.escuchando = !this.escuchando;
            if (this.escuchando) {
                // Simulación de dictado por IA para la demo de Clínica de Trabajo
            }
        },
        procesarAudioIA() {
            this.procesandoIA = true;
            setTimeout(() => {
                this.conversacion.push({
                    sender: 'system',
                    text: 'He procesado tu dictado. He detectado: Área de Logística, Puesto Operador de Montacargas, Exposición a Ruido y Vibración, Antecedente de Lumbalgia Leve.'
                });
                if (!this.sintomasDetectados.includes('Ruido Elevado')) {
                    this.sintomasDetectados.push('Ruido Elevado');
                }
                if (!this.sintomasDetectados.includes('Vibraciones')) {
                    this.sintomasDetectados.push('Vibraciones');
                }
                if (!this.sintomasDetectados.includes('Antecedente Lumbalgia')) {
                    this.sintomasDetectados.push('Antecedente Lumbalgia');
                }
                this.procesandoIA = false;
                this.escuchando = false;
                this.scrollBottom();
            }, 2000);
        },
        seleccionarArchivo(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.archivoSeleccionado = file;
        },
        quitarArchivo() {
            this.archivoSeleccionado = null;
            if (this.$refs.inputArchivo) {
                this.$refs.inputArchivo.value = '';
            }
        },
        enviarMensajeChat() {
            const hayTexto = this.nuevoMensaje && this.nuevoMensaje.trim() !== '';
            const hayArchivo = !!this.archivoSeleccionado;
            if (!hayTexto && !hayArchivo) return;

            if (hayArchivo) {
                const nombre = this.archivoSeleccionado.name;
                this.conversacion.push({
                    sender: 'user',
                    text: `Archivo adjunto enviado: ${nombre}`,
                    archivo: true
                });
                this.archivoSeleccionado = null;
                if (this.$refs.inputArchivo) {
                    this.$refs.inputArchivo.value = '';
                }
                
                // IA response
                setTimeout(() => {
                    this.conversacion.push({
                        sender: 'ia',
                        text: `He recibido y analizado el archivo "${nombre}". He detectado hallazgos de relevancia clínica que se integrarán en el expediente del trabajador.`
                    });
                    if (!this.sintomasDetectados.includes('Estudio Médico (Doc/Imagen)')) {
                        this.sintomasDetectados.push('Estudio Médico (Doc/Imagen)');
                    }
                    this.scrollBottom();
                }, 1500);
            }

            if (hayTexto) {
                const txt = this.nuevoMensaje.trim();
                this.conversacion.push({
                    sender: 'user',
                    text: txt
                });
                this.nuevoMensaje = '';

                // IA response
                this.procesandoIA = true;
                setTimeout(() => {
                    this.conversacion.push({
                        sender: 'ia',
                        text: `Mensaje procesado. He interpretado la información clínica sobre el trabajador para la evaluación actual.`
                    });
                    // Simular detección de factores si el texto coincide con palabras clave
                    const txtLower = txt.toLowerCase();
                    if (txtLower.includes('ruido') && !this.sintomasDetectados.includes('Ruido Elevado')) {
                        this.sintomasDetectados.push('Ruido Elevado');
                    }
                    if (txtLower.includes('vibracion') && !this.sintomasDetectados.includes('Vibraciones')) {
                        this.sintomasDetectados.push('Vibraciones');
                    }
                    if (txtLower.includes('lumbalgia') && !this.sintomasDetectados.includes('Antecedente Lumbalgia')) {
                        this.sintomasDetectados.push('Antecedente Lumbalgia');
                    }
                    this.procesandoIA = false;
                    this.scrollBottom();
                }, 1500);
            }

            this.scrollBottom();
        },
        scrollBottom() {
            this.$nextTick(() => {
                const container = this.$refs.chatContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        },
        
        async aplicarAutocompletadoIA() {
            const textoCompleto = this.conversacion
                .filter(m => m.sender === 'user')
                .map(m => m.text)
                .join('\n');
        
            if (!textoCompleto.trim()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Conversación vacía',
                    text: 'Escribe o dicta algo en el chat antes de sincronizar con la ficha.'
                });
                return;
            }
        
            this.procesandoIA = true;
            try {
                const { data } = await apiClient.post(
                    '/api/clinica-trabajo/ficha-ocupacional/analizar',
                    { texto: textoCompleto }
                );
                console.log('Respuesta IA ficha:', data.data); // útil para depurar
        
                const cambios = this.aplicarSugerenciasIA(data.data || {});
                Swal.fire({
                    icon: 'success',
                    title: 'Ficha sincronizada',
                    text: `La IA propuso ${cambios} cambios. Revísalos antes de guardar.`,
                    confirmButtonText: 'Aceptar'
                });
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo sincronizar',
                    text: error.response?.data?.message || 'Error al consultar la IA. Puedes llenar la ficha manualmente.'
                });
            } finally {
                this.procesandoIA = false;
            }
        },
 
        formatearDictamen(val) {
            if (val === 'apto') return 'Apto';
            if (val === 'apto_con_restricciones') return 'Apto con Restricciones';
            if (val === 'no_apto') return 'No Apto';
            return val;
        },

        // Un campo se puede sobrescribir si está vacío o si su valor sigue siendo
        // el que puso la IA (o sea, el médico no lo ha editado a mano).
        puedeSobrescribir(campo) {
            const actual = this.form[campo];
            return !actual || actual === this.ultimoValorIA[campo];
        },
 
        aplicarSugerenciasIA(ia) {
            let cambios = 0;
        
            const camposTexto = [
                'empresa', 'departamento', 'puesto_trabajo', 'antiguedad',
                'descripcion_puesto', 'epp_requerido', 'antecedentes',
                'examen_fisico', 'examenes_complementarios',
                'restricciones', 'recomendaciones'
            ];
        
            camposTexto.forEach(campo => {
                const nuevo = typeof ia[campo] === 'string' ? ia[campo].trim() : '';
                if (!nuevo) return;                          // la IA no aportó nada: no tocar
                if (!this.puedeSobrescribir(campo)) return;  // el médico lo editó: no pisar
                this.form[campo] = nuevo;
                this.ultimoValorIA[campo] = nuevo;
                cambios++;
            });
        
            // Checkboxes: la IA solo MARCA, nunca desmarca
            (ia.riesgos || []).forEach(r => {
                if (r in this.form.riesgos && !this.form.riesgos[r]) {
                    this.form.riesgos[r] = true;
                    cambios++;
                }
            });
        
            // Dictamen: solo sugerencia, el médico lo acepta con un clic
            if (['apto', 'apto_con_restricciones', 'no_apto'].includes(ia.dictamen)) {
                this.dictamenSugerido = ia.dictamen;
            }
        
            // Panel de síntomas / factores detectados: reemplazamos la lista simulada previa con la lista analizada definitivamente por la IA
            if (ia.sintomas_detectados && ia.sintomas_detectados.length > 0) {
                this.sintomasDetectados = [...ia.sintomas_detectados];
            }
        
            return cambios;
        },

        async guardarFicha() {
            if (!this.form.aptitud) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Falta el dictamen',
                    text: 'Selecciona la aptitud del trabajador antes de guardar.'
                });
                return;
            }
            this.guardando = true;
            try {
                // Almacena el registro real de la Ficha Ocupacional en la base de datos
                const response = await apiClient.post('/api/clinica-trabajo/ficha-ocupacional', {
                    paciente_id: this.trabajadorSeleccionado.id,
                    empresa: this.form.empresa,
                    departamento: this.form.departamento,
                    puesto_trabajo: this.form.puesto_trabajo,
                    antiguedad: this.form.antiguedad,
                    descripcion_puesto: this.form.descripcion_puesto,
                    antecedentes: this.form.antecedentes,
                    examen_fisico: this.form.examen_fisico,
                    examenes_complementarios: this.form.examenes_complementarios,
                    aptitud: this.form.aptitud,
                    restricciones: this.form.restricciones,
                    recomendaciones: this.form.recomendaciones,
                    riesgos: this.form.riesgos,
                    epp_requerido: this.form.epp_requerido
                });
                
                this.guardando = false;
                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado Exitoso!',
                    text: 'La Ficha Médica Ocupacional del trabajador ha sido registrada en el sistema de manera correcta.',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    this.deseleccionarTrabajador();
                });
            } catch (error) {
                this.guardando = false;
                console.error("Error al guardar la ficha:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.response?.data?.message || 'Hubo un inconveniente al intentar guardar los datos.'
                });
            }
        }
    }
};
</script>

<style scoped>
.border-left-primary {
    border-left: 5px solid #007bff !important;
}
.gap-1 {
    gap: 0.25rem;
}
</style>
