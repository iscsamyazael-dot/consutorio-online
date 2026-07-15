<template>
        <!-- COLUMNA IZQUIERDA -->
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-id-card text-primary"></i>
                        Información básica
                    </h6>
                </div>
                <div class="card-body">

                    <div class="info-item">
                        <span>Teléfono</span>
                        <strong>{{ infoPacientes.telefono }}</strong>
                    </div>
                    <div class="info-item">
                        <span>Correo</span>
                        <strong>{{ infoPacientes.email }}</strong>
                    </div>
                    <div class="info-item">
                        <span>Tipo sangre</span>
                        <strong>{{ infoPacientes.tipo_sangre }}</strong>
                    </div>
                    <div class="info-item">
                        <span>Alergias</span>
                        <strong>{{ infoPacientes.alergias }}</strong>
                    </div>
                    <div class="info-item mb-0">
                        <span>Fecha nacimiento</span>
                        <strong>{{ infoPacientes.fecha_nacimiento }}</strong>
                    </div>
                </div>
            </div>
</template>
<script>
    import ApiService from '../../services/ApiService.js'
    export default {
        data() {
            return {  
                infoPacientes: [],
            }
        },
        mounted() {
            //this.obtenerPacientes();
            console.log('PROP PacienteId:', this.pacienteId);
        },
        methods: {
            async obtenerPacientes(){
                try {
                    const response = await ApiService.get('/ExpedienteDetalle/' + this.pacienteId)
                    this.infoPacientes = response.data
                    console.log('Pacientes cargados:',this.infoPacientes)
                }catch(error){
                        console.error("Error al obtener pacientes:", error)
                }
            }  
        },
        props:{
            //Esto guarda la el id que se trajo mediante la ruta parametrizada que el master hereda a los componentes hijos
            pacienteId:{
                type: [Number, String],
                required: true
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