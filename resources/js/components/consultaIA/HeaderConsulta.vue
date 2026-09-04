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
                <div class="col-lg-1 col-md-2 col-12 text-center mb-3 mb-md-0">
                    <div class="avatar-xl mx-auto">
                        {{ infoPacientes.nombre?.substring(0, 2) }}
                    </div>
                </div>

                <!-- INFORMACIÓN PACIENTE -->
                <div class="col-lg-8 col-md-7 col-12">
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
                            {{ infoPacientes.edad }} Años
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
    export default {
        data(){
            return {
                infoPacientes:{}
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
            }
        },

        props:{
            pacienteId:{
                type:[Number,String],
                required:true
            }
        },
        watch:{
            pacienteId:{
                immediate:true,
                handler(id){
                    if(id){
                        this.obtenerPacientes();
                    }
                }
            }
        }
    }
</script>