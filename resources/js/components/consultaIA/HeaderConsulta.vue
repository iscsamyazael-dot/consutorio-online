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

                        <!-- HISTORIA CLÍNICA: progreso X/7 -->
                        <!-- Verde cuando ya está completa (7/7), amarillo mientras
                             falten campos. El tooltip lista qué apartados faltan,
                             igual que el ícono ℹ️ de contacto de arriba. -->
                        <span
                            v-if="progresoHistoriaClinica"
                            class="badge rounded-pill px-3 py-2"
                            :class="progresoHistoriaClinica.completa ? 'bg-success' : 'bg-warning text-dark'"
                            style="cursor:help;"
                            :title="tooltipHistoriaClinica">
                            <i class="fas fa-notes-medical me-1"></i>
                            Historia Clínica: {{ progresoHistoriaClinica.completados }}/{{ progresoHistoriaClinica.total }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
</style>

<script>
    import ApiService from '../../services/ApiService.js'

    // Traduce la clave interna del campo (igual que ExpedienteClinico::CAMPOS_CLINICOS)
    // a una etiqueta legible para el tooltip del badge.
    const ETIQUETAS_CAMPOS_CLINICOS = {
        antecedentes_heredofamiliares: 'Antecedentes heredofamiliares',
        antecedentes_medicos: 'Antecedentes personales patológicos',
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
                progresoHistoriaClinica: null
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
            hayAlergiaMedicamentos() {
                const valor = this.infoPacientes.alergia_medicamentos
                return !!(valor && valor.trim() && valor.trim().toLowerCase() !== 'ninguna')
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