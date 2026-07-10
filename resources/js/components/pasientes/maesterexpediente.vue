<template>  
<div class="container-fluid"> 
    
        <Barrasuperior
            :paciente-id="pacienteId">
        </Barrasuperior>
    
    <div class="row">
        <div class="col-lg-3">
            <Informacionbasica
                :paciente-id="pacienteId">
            </Informacionbasica>
            <Alertasmedicas
                :paciente-id="pacienteId">
            </Alertasmedicas>
          </div>
        <Historialconsulta :paciente-id="pacienteId">
        </Historialconsulta>
    </div>
    
</div>     
     
</template>
<style>
    body{
        background:#f4f6f9;
    }

    .patient-hero{
        background:linear-gradient(135deg,#ffffff,#eef6ff);
        border-radius:24px;
        padding:24px;
        border:1px solid #e5edf7;
    }

    .avatar-xl{
        width:90px;
        height:90px;
        border-radius:50%;
        background:linear-gradient(135deg,#0d6efd,#00c6ff);
        color:white;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:30px;
        font-weight:800;
        box-shadow:0 10px 25px rgba(13,110,253,.25);
    }

    .rounded-4{
        border-radius:24px !important;
    }

    .info-item{
        display:flex;
        justify-content:space-between;
        gap:12px;
        padding:12px 0;
        border-bottom:1px solid #edf0f4;
    }

    .info-item span{
        color:#6c757d;
        font-size:13px;
    }

    .info-item strong{
        color:#1f2937;
        font-size:13px;
        text-align:right;
    }

    .medical-alert{
        background:#fff5f5;
        color:#dc3545;
        border:1px solid #ffd2d2;
        border-radius:16px;
        padding:14px;
        display:flex;
        gap:12px;
        align-items:flex-start;
    }

    .medical-alert i{
        font-size:22px;
        margin-top:3px;
    }

    .medical-alert small{
        display:block;
        color:#8a3a3a;
    }

    .custom-tabs .nav-link{
        border:none;
        color:#6c757d;
        font-weight:700;
        padding:14px 18px;
        border-radius:16px 16px 0 0;
    }

    .custom-tabs .nav-link.active{
        background:#f4f8ff;
        color:#0d6efd;
    }

    .section-title{
        margin-bottom:20px;
    }

    .section-title h5{
        font-weight:800;
        margin-bottom:2px;
    }

    .section-title small{
        color:#6c757d;
    }

    .timeline-card,
    .record-card,
    .note-card{
        background:#fff;
        border:1px solid #edf0f4;
        border-radius:18px;
        padding:18px;
        margin-bottom:16px;
        box-shadow:0 8px 20px rgba(0,0,0,.04);
    }

    .timeline-card{
        display:flex;
        gap:16px;
    }

    .timeline-dot{
        width:14px;
        height:14px;
        border-radius:50%;
        margin-top:5px;
        flex-shrink:0;
    }

    .record-card{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:16px;
    }

    .file-card{
        border:1px solid #edf0f4;
        border-radius:20px;
        padding:22px;
        text-align:center;
        box-shadow:0 8px 20px rgba(0,0,0,.04);
        margin-bottom:16px;
    }

    .file-card i{
        font-size:42px;
        margin-bottom:14px;
    }

    .file-card h6{
        font-weight:700;
        word-break:break-word;
    }

    @media(max-width:768px){
        .record-card{
            flex-direction:column;
            align-items:flex-start;
        }

        .custom-tabs .nav-link{
            padding:10px 12px;
            font-size:13px;
        }

        .patient-hero{
            padding:18px;
        }
    }
</style>

<script>
import Alertasmedicas from './alertasmedicas.vue';
import Barrasuperior from './barrasuperior.vue';
import Historialconsulta from './historialconsulta.vue';
import Informacionbasica from './informacionbasica.vue';

export default{
    components:{
        Alertasmedicas,
        Barrasuperior,
        Historialconsulta,
        Informacionbasica
    },
    data (){
        return{
            pacienteId: ''
        }
    },
    methods:{
        //Funcion que recibe el ID del paciente a traves de la ruta paramaetrizada //
        obtenerPacienteId() {
            this.pacienteId = window.location.pathname.split('/').pop();
            console.log('Paciente ID:',this.pacienteId
            );
        }
    },
    mounted() {
        this.obtenerPacienteId()
    }
}

</script>