<template>
    <!-- BREADCRUMB -->
    <nav class="mb-4 small text-muted">
        <i class="fas fa-home me-1"></i>
        Pacientes /
        <strong class="text-dark">
            {{ infoPacientes.nombre }}
        </strong>
    </nav>

    <!-- HERO PACIENTE -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <!-- AVATAR -->
                <div class="col-lg-1 col-md-3 col-12 text-center mb-3 mb-md-0">
                    <div class="avatar-xl mx-auto">
                        {{ infoPacientes.nombre?.substring(0, 2) }}
                    </div>
                </div>

                <!-- INFORMACIÓN PACIENTE -->
                <div class="col-lg-11 col-md-9 col-12">
                    <h3 class="fw-bold mb-3 nombre-paciente">
                        {{ infoPacientes.nombre }}
                        {{ infoPacientes.apellido_paterno }}
                        {{ infoPacientes.apellido_materno }}

                        <!-- Ícono con datos de contacto en tooltip nativo -->
                        <i
                            v-if="infoPacientes.telefono || infoPacientes.email"
                            class="fas fa-info-circle text-muted ms-2 info-contacto"
                            style="font-size:1rem; cursor:help;"
                            :title="datosContacto">
                        </i>
                    </h3>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-primary rounded-pill px-3 py-2">
                            Folio:  {{ infoPacientes.paciente_id }}
                        </span>
                        <span class="badge bg-info rounded-pill px-3 py-2">
                             {{ infoPacientes.edad_formateada }}
                        </span>
                        <span class="badge bg-secondary rounded-pill px-3 py-2">
                            {{ infoPacientes.sexo }}
                        </span>
                        <span class="badge bg-success rounded-pill px-3 py-2">
                            Consulta: {{ infoPacientes.estado }}
                        </span>

                        <span class="badge bg-warning rounded-pill px-3 py-2 text-white">
                            IA Activa
                        </span>

                        <!-- TIPO DE SANGRE -->
                        <span
                            v-if="infoPacientes.tipo_sangre"
                            class="badge bg-danger rounded-pill px-3 py-2">
                            <i class="fas fa-tint me-1"></i>{{ infoPacientes.tipo_sangre }}
                        </span>

                        <!-- ALERGIAS -->
                        <span
                            v-if="infoPacientes.alergias"
                            class="badge bg-danger rounded-pill px-3 py-2">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Alergias: {{ infoPacientes.alergias }}
                        </span>
                        <span
                            v-else-if="infoPacientes.id"
                            class="badge bg-light text-muted border rounded-pill px-3 py-2">
                            Sin alergias registradas
                        </span>

                        <!-- ALERGIA A MEDICAMENTOS -->
                        <span
                            v-if="hayAlergiaMedicamentos"
                            class="badge badge-alergia-medicamentos rounded-pill px-3 py-2">
                            <i class="fas fa-pills me-1"></i>
                            Alergia a medicamentos: {{ infoPacientes.alergia_medicamentos }}
                        </span>

                        <span
                            v-else-if="infoPacientes.id"
                            class="badge bg-light text-muted border rounded-pill px-3 py-2">
                            Sin alergia a medicamentos registrada
                        </span>

                        <!-- HISTORIA CLÍNICA: ahora en porcentaje, clic abre modal -->
                        <span
                            v-if="progresoHistoriaClinica"
                            class="badge rounded-pill px-3 py-2 badge-historia-clinica"
                            :class="progresoHistoriaClinica.completa ? 'bg-success' : 'bg-warning text-dark'"
                            style="cursor:pointer;"
                            :title="tooltipHistoriaClinica"
                            @click="abrirModalHistoriaClinica">
                            <i class="fas fa-notes-medical me-1"></i>
                            Historia Clínica: {{ porcentajeHistoriaClinica }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- MODAL: vista rápida de Historia Clínica -->
    <div class="modal fade" id="modalHistoriaClinica" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 rounded-4">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title">
                <i class="fas fa-notes-medical me-2"></i>
                Historia Clínica
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">

                <div v-if="cargandoHistoriaClinica" class="text-center text-muted py-4">
                <span class="spinner-border spinner-border-sm me-2"></span>
                Cargando historia clínica...
                </div>

                <template v-else>

                <div class="d-flex justify-content-between flex-wrap gap-2 mb-4">
                    <h6 class="fw-bold mb-0">
                    <i class="fas fa-clipboard-check text-primary me-2"></i>
                    Progreso: {{ progresoHistoriaClinica?.completados }}/{{ progresoHistoriaClinica?.total }}
                    ({{ porcentajeHistoriaClinica }}%)
                    </h6>
                    <span
                    class="badge rounded-pill px-3 py-2"
                    :class="expedienteModal?.revisado_medico ? 'bg-success' : 'bg-warning text-dark'">
                    {{ expedienteModal?.revisado_medico ? 'Revisado por el médico' : 'Pendiente de revisión' }}
                    </span>
                </div>

                <!-- FICHA DE IDENTIFICACIÓN -->
                <div class="mb-4">
                    <h6 class="fw-bold text-muted mb-2" style="font-size:13px; letter-spacing:0.5px;">
                    FICHA DE IDENTIFICACIÓN
                    </h6>
                    <div class="row g-2">
                    <div class="col-md-4 col-6">
                        <small class="text-muted d-block">Nombre completo</small>
                        <strong>{{ infoPacientes.nombre || '—' }} {{ infoPacientes.apellido_paterno }} {{ infoPacientes.apellido_materno }}</strong>
                    </div>
                    <div class="col-md-2 col-6">
                        <small class="text-muted d-block">Edad</small>
                        <strong>{{ infoPacientes.edad_formateada || 'N/D' }}</strong>
                    </div>
                    <div class="col-md-2 col-6">
                        <small class="text-muted d-block">Sexo</small>
                        <strong>{{ infoPacientes.sexo || 'N/D' }}</strong>
                    </div>
                    <div class="col-md-2 col-6">
                        <small class="text-muted d-block">Tipo de sangre</small>
                        <strong>{{ infoPacientes.tipo_sangre || 'N/D' }}</strong>
                    </div>
                    <div class="col-md-2 col-6">
                        <small class="text-muted d-block">Teléfono</small>
                        <strong>{{ infoPacientes.telefono || 'N/D' }}</strong>
                    </div>
                    <div class="col-md-5 col-6">
                        <small class="text-muted d-block">Correo</small>
                        <strong>{{ infoPacientes.email || 'N/D' }}</strong>
                    </div>
                    <div class="col-md-3 col-6">
                        <small class="text-muted d-block">Fecha de nacimiento</small>
                        <strong>{{ formatearFecha(infoPacientes.fecha_nacimiento) || 'N/D' }}</strong>
                    </div>
                    <div class="col-md-4 col-6">
                        <small class="text-muted d-block">CURP</small>
                        <strong>{{ infoPacientes.curp || 'N/D' }}</strong>
                    </div>
                    <div class="col-md-6 col-12">
                        <small class="text-muted d-block">Dirección</small>
                        <strong>{{ infoPacientes.direccion || 'N/D' }}</strong>
                    </div>
                    <div class="col-md-3 col-6">
                        <small class="text-muted d-block">Alergias</small>
                        <strong :class="infoPacientes.alergias ? 'text-danger' : ''">
                        {{ infoPacientes.alergias || 'Ninguna registrada' }}
                        </strong>
                    </div>
                    <div class="col-md-3 col-6">
                        <small class="text-muted d-block">Alergia a medicamentos</small>
                        <strong :class="hayAlergiaMedicamentos ? 'text-danger' : ''">
                        {{ hayAlergiaMedicamentos ? infoPacientes.alergia_medicamentos : 'Ninguna' }}
                        </strong>
                    </div>
                    </div>
                </div>

                <!-- SIGNOS VITALES DE REFERENCIA -->
                <div class="mb-4" v-if="triageModal">
                    <h6 class="fw-bold text-muted mb-2" style="font-size:13px; letter-spacing:0.5px;">
                    SIGNOS VITALES DE REFERENCIA
                    <small class="text-muted fw-normal">(último registro: {{ formatearFecha(triageModal.created_at) }})</small>
                    </h6>
                    <div class="row g-2">
                    <div class="col-4 col-md-2" v-for="dato in datosVitalesModal" :key="dato.etiqueta">
                        <div class="text-center p-2 border rounded-3 bg-light">
                        <small class="text-muted d-block">{{ dato.etiqueta }}</small>
                        <strong>{{ dato.valor || 'N/D' }}</strong>
                        </div>
                    </div>
                    </div>
                </div>
                <div class="alert alert-light border text-muted small mb-4" v-else>
                    <i class="fas fa-info-circle me-1"></i> Este paciente aún no tiene signos vitales registrados.
                </div>

                <!-- INTERROGATORIO Y EXPLORACIÓN -->
                <h6 class="fw-bold text-muted mb-3" style="font-size:13px; letter-spacing:0.5px;">
                    INTERROGATORIO Y EXPLORACIÓN
                </h6>
                <div class="psoapp-item" v-for="campo in camposExpedienteModal" :key="campo.clave">
                    <span class="psoapp-letra bg-info">{{ campo.letra }}</span>
                    <div>
                    <strong>{{ campo.etiqueta }}</strong>
                    <p class="mb-0 text-muted">
                        {{ expedienteModal?.[campo.clave] || 'Sin datos capturados todavía.' }}
                    </p>
                    </div>
                </div>

                <div class="psoapp-item" v-if="expedienteModal?.enfermedades_cronicas">
                    <span class="psoapp-letra bg-secondary">G</span>
                    <div>
                    <strong>Enfermedades crónicas</strong>
                    <p class="mb-0 text-muted">{{ expedienteModal.enfermedades_cronicas }}</p>
                    </div>
                </div>

                <!-- EVOLUCIÓN DEL PADECIMIENTO -->
                <hr class="my-4">
                <h6 class="fw-bold text-muted mb-1" style="font-size:13px; letter-spacing:0.5px;">
                    EVOLUCIÓN DEL PADECIMIENTO
                </h6>
                <small class="text-muted d-block mb-3">
                    Comparación cronológica de cómo llegó el paciente en cada consulta.
                </small>

                <div v-if="notasPsoappModal.length === 0" class="alert alert-light border text-muted small">
                    <i class="fas fa-info-circle me-1"></i> Este paciente aún no tiene notas de evolución registradas.
                </div>

                <div v-else class="evolucion-scroll">
                    <div
                    class="evolucion-card"
                    v-for="nota in notasPsoappModal"
                    :key="'evo-' + nota.id">

                    <h6 class="fw-bold mb-2 small">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                        {{ formatearFecha(nota.fecha) }}
                        <span class="text-muted fw-normal">&middot; {{ formatearHora(nota.fecha) }}</span>
                    </h6>

                    <p class="mb-1" v-if="nota.subjetivo">
                        <strong>Padecimiento referido:</strong> {{ nota.subjetivo }}
                    </p>
                    <p class="mb-1" v-if="nota.objetivo">
                        <strong>Exploración:</strong> {{ nota.objetivo }}
                    </p>
                    <p class="mb-0" v-if="nota.plan">
                        <strong>Plan:</strong> {{ nota.plan }}
                    </p>

                    <p v-if="!nota.subjetivo && !nota.objetivo && !nota.plan" class="text-muted small mb-0">
                        Esta consulta todavía no tiene nota de evolución capturada.
                    </p>
                    </div>
                </div>
                </template>
            </div>
            </div>
        </div>
    </div>
    <!-- fin modal Historia Clínica -->
</template>

<style scoped>
.nombre-paciente{
    font-size:1.6rem;
    margin-left:5px;
    position:relative;
    top:12px;

}
.avatar-xl{
    width:90px;
    height:90px;
    border-radius:50%;
    background:linear-gradient(135deg,#0d6efd,#0dcaf0);
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:2rem;
    font-weight:bold;
}
/* CONTENEDOR DE BOTONES */
.botones-consulta{
    display:flex;
    justify-content:flex-end;
    align-items:center;
    gap:10px;
    flex-wrap:nowrap;
}
/* BOTONES */
.botones-consulta .btn{
    white-space:nowrap;
    font-size:0.85rem;
    padding:10px 15px;
}

.badge-alergia-medicamentos {
    background-color: #7c3aed; /* morado, distinto a los rojos ya usados */
    color: #fff;
}

/* RESPONSIVE */
@media(max-width:768px){
    .botones-consulta{
        justify-content:center;
        flex-wrap:wrap;
    }
}

.psoapp-item {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    margin-bottom: 12px;
}
.psoapp-item:last-of-type {
    margin-bottom: 4px;
}
.psoapp-letra {
    flex-shrink: 0;
    width: 26px;
    height: 26px;
    border-radius: 8px;
    color: #fff;
    font-weight: 800;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.evolucion-scroll {
    max-height: 400px;
    overflow-y: auto;
    padding-right: 8px;
}
.evolucion-card {
    background: #f9fafb;
    border: 1px solid #edf0f4;
    border-left: 3px solid #0d6efd;
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 12px;
}

</style>

<script>
    import ApiService from '../../services/ApiService.js'

    // Traduce la clave interna del campo (igual que ExpedienteClinico::CAMPOS_CLINICOS)
    // a una etiqueta legible para el tooltip del badge.
    const ETIQUETAS_CAMPOS_CLINICOS = {
        antecedentes_heredofamiliares: 'Antecedentes heredofamiliares',
        antecedentes_medicos: 'Antecedentes personales patológicos',
        antecedentes_quirurgicos: 'Antecedentes quirúrgicos',
        medicamentos_actuales: 'Medicamentos actuales',
        antecedentes_no_patologicos: 'Antecedentes personales no patológicos',
        padecimiento_actual: 'Padecimiento actual',
        interrogatorio_aparatos_sistemas: 'Interrogatorio por aparatos y sistemas',
        exploracion_fisica: 'Exploración física',
        plan_tratamiento_inicial: 'Plan de tratamiento inicial',
    }

    export default {
        data(){
            return {
                infoPacientes:{},
                // { completados, total, completa, campos_faltantes }
                progresoHistoriaClinica: null,

                 // Modal de Historia Clínica
                expedienteModal: null,
                triageModal: null,
                notasPsoappModal: [],
                cargandoHistoriaClinica: false,
                camposExpedienteModal: [
                    { clave: 'antecedentes_heredofamiliares', letra: 'H', etiqueta: 'Antecedentes heredofamiliares' },
                    { clave: 'antecedentes_medicos', letra: 'P', etiqueta: 'Antecedentes personales patológicos' },
                    { clave: 'antecedentes_quirurgicos', letra: 'Q', etiqueta: 'Antecedentes quirúrgicos' },
                    { clave: 'medicamentos_actuales', letra: 'M', etiqueta: 'Medicamentos actuales' },
                    { clave: 'antecedentes_no_patologicos', letra: 'N', etiqueta: 'Antecedentes personales no patológicos' },
                    { clave: 'padecimiento_actual', letra: 'A', etiqueta: 'Padecimiento actual' },
                    { clave: 'interrogatorio_aparatos_sistemas', letra: 'I', etiqueta: 'Interrogatorio por aparatos y sistemas' },
                    { clave: 'exploracion_fisica', letra: 'E', etiqueta: 'Exploración física' },
                    { clave: 'plan_tratamiento_inicial', letra: 'T', etiqueta: 'Plan de tratamiento inicial' },
                ]
            }
        },
        computed: {
            // Texto del tooltip del ícono ℹ️: arma teléfono/correo solo
            // con los campos que sí vengan capturados.
            datosContacto() {
                const partes = [];
                if (this.infoPacientes.telefono) {
                    partes.push('Tel: ' + this.infoPacientes.telefono);
                }
                if (this.infoPacientes.email) {
                    partes.push('Correo: ' + this.infoPacientes.email);
                }
                return partes.join(' | ');
            },
            // Tooltip del badge de Historia Clínica: lista qué falta,
            // o confirma que ya está completa.
            tooltipHistoriaClinica() {
                if (!this.progresoHistoriaClinica) return '';
                if (this.progresoHistoriaClinica.completa) {
                    return 'Historia clínica completa';
                }
                const faltantes = (this.progresoHistoriaClinica.campos_faltantes || [])
                    .map(clave => ETIQUETAS_CAMPOS_CLINICOS[clave] || clave);
                return faltantes.length
                    ? 'Falta: ' + faltantes.join(', ')
                    : '';
            },
             // Redondeado a entero; evita división por cero si total viene en 0
            porcentajeHistoriaClinica() {
                if (!this.progresoHistoriaClinica || !this.progresoHistoriaClinica.total) return 0;
                return Math.round(
                    (this.progresoHistoriaClinica.completados / this.progresoHistoriaClinica.total) * 100
                );
            },
            hayAlergiaMedicamentos() {
                const valor = this.infoPacientes.alergia_medicamentos
                return !!(valor && valor.trim() && valor.trim().toLowerCase() !== 'ninguna')
            },
            datosVitalesModal() {
                if (!this.triageModal) return []
                const t = this.triageModal
                return [
                    { etiqueta: 'Presión', valor: t.presion ? t.presion + ' mmHg' : null },
                    { etiqueta: 'Saturación', valor: t.saturacion ? t.saturacion + '%' : null },
                    { etiqueta: 'Temperatura', valor: t.temperatura ? t.temperatura + '°C' : null },
                    { etiqueta: 'F. Cardiaca', valor: t.frecuencia_cardiaca ? t.frecuencia_cardiaca + ' lpm' : null },
                    { etiqueta: 'F. Respiratoria', valor: t.frecuencia_respiratoria ? t.frecuencia_respiratoria + ' rpm' : null },
                    { etiqueta: 'Peso', valor: t.peso ? t.peso + ' kg' : null },
                    { etiqueta: 'Talla', valor: t.talla ? t.talla + ' cm' : null },
                    { etiqueta: 'IMC', valor: t.imc || null },
                ]
            }
        },
        mounted(){
            console.log(
                'Paciente recibido:',
                this.pacienteId
            );
        },

        methods:{
            async obtenerPacientes(){
                try{
                    const response = await ApiService.get(
                        '/ExpedienteDetalle/' + this.pacienteId
                    );
                    this.infoPacientes = response.data;
                    console.log(
                        'Expediente cargado:',
                        this.infoPacientes
                    );
                }catch(error){
                    console.error(
                        'Error al obtener paciente:',
                        error
                    );
                }
            },



            // Carga inicial del progreso al entrar a la consulta (usa el
            // mismo endpoint que ya consume el tab de Historia Clínica en
            // ExpedienteTabs.vue). Después, mientras la consulta esté en
            // vivo, el progreso se actualiza vía el prop `progresoIA`
            // (ver watcher más abajo) sin volver a llamar a este endpoint.
            async obtenerProgresoHistoriaClinica(){
                try{
                    const response = await ApiService.get(
                        '/expedienteClinico/' + this.pacienteId
                    );
                    this.progresoHistoriaClinica = response.data.progreso;
                }catch(error){
                    console.error(
                        'Error al obtener progreso de historia clínica:',
                        error
                    );
                }
            },
            formatearFecha(fecha){
                if(!fecha) return ''
                const f = new Date(fecha)
                return f.toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' })
            },
            formatearHora(fecha){
                if(!fecha) return ''
                const f = new Date(fecha)
                return f.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit', hour12: true })
            },
            
            // Trae interrogatorio/exploración + evolución (notas PSOAPP) y
            // abre el modal. infoPacientes (ficha de identificación + triage)
            // ya viene cargado desde obtenerPacientes(), así que no se vuelve
            // a pedir aquí.
            async abrirModalHistoriaClinica(){
                this.cargandoHistoriaClinica = true;
                try{
                    const [respExpediente, respPsoapp] = await Promise.all([
                        ApiService.get('/expedienteClinico/' + this.pacienteId),
                        ApiService.get('/consultaIA/paciente/' + this.pacienteId + '/psoapp')
                    ]);

                    this.expedienteModal = respExpediente.data.expediente;
                    this.progresoHistoriaClinica = respExpediente.data.progreso;
                    this.triageModal = (this.infoPacientes.triages && this.infoPacientes.triages[0]) || null;
                    this.notasPsoappModal = respPsoapp.data.notas_psoapp || [];
                }catch(error){
                    console.error('Error al obtener la historia clínica para el modal:', error);
                }finally{
                    this.cargandoHistoriaClinica = false;
                }
                $('#modalHistoriaClinica').modal('show');
            }
        },

        props:{
            pacienteId:{
                type:[Number,String],
                required:true
            },
            // Progreso EN VIVO de historia clínica, que el padre
            // (ConsultaInteligente.vue) le pasa cada vez que llega
            // ia_data.historia_clinica_progreso en la respuesta de la IA.
            // Mientras no haya nada nuevo, se queda con lo que ya trajo
            // obtenerProgresoHistoriaClinica() al montar el componente.
            progresoIa:{
                type: Object,
                default: null
            }
        },
        watch:{
            pacienteId:{
                immediate:true,
                handler(id){
                    if(id){
                        this.obtenerPacientes();
                        this.obtenerProgresoHistoriaClinica();
                    }
                }
            },
            // Cada vez que el padre reciba una nueva respuesta de la IA
            // con progreso de historia clínica, se refleja aquí de inmediato.
            progresoIa(nuevoValor){
                if (nuevoValor) {
                    this.progresoHistoriaClinica = nuevoValor;
                }
            }
        }
    }
</script>