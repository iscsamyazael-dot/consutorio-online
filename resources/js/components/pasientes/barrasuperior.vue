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

                    </h3>


                    <div class="d-flex flex-wrap gap-2">


                        <span class="badge bg-primary rounded-pill px-3 py-2">
                            Expediente #0001
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


                    </div>

                </div>




                <!-- BOTONES -->
                <div class="col-lg-3 col-md-3 col-12 mt-3 mt-md-0">


                    <div class="botones-consulta">


                        <!-- NUEVA CONSULTA -->
                        <a 
                            :href="'/consultaNormal/' + infoPacientes.id"
                            class="btn btn-success rounded-pill shadow-sm">


                            <i class="fas fa-stethoscope me-2"></i>

                            Nueva consulta


                        </a>



                        <!-- CONSULTA INTELIGENTE -->
                        <a 
                            :href="'/ConsultaInteligente/' + infoPacientes.id"
                            class="btn btn-primary rounded-pill shadow-sm">


                            <i class="fas fa-robot me-2"></i>

                            Consulta Inteligente


                        </a>



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