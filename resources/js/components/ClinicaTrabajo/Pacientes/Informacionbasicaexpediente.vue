<template>
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
                <strong>{{ infoPacientes.telefono || 'N/D' }}</strong>
            </div>
            <div class="info-item">
                <span>Correo</span>
                <strong>{{ infoPacientes.email || 'N/D' }}</strong>
            </div>
            <div class="info-item">
                <span>Alergias</span>
                <strong class="text-danger">{{ infoPacientes.alergias || 'Ninguna' }}</strong>
            </div>
            <div class="info-item">
                <span>Fecha nacimiento</span>
                <strong>{{ formatearFecha(infoPacientes.fecha_nacimiento) }}</strong>
            </div>
            <div class="info-item">
                <span>Escolaridad</span>
                <strong>{{ infoPacientes.escolaridad || 'N/D' }}</strong>
            </div>
            <div class="info-item">
                <span>Estado civil</span>
                <strong>{{ infoPacientes.estado_civil || 'N/D' }}</strong>
            </div>
            <div class="info-item mb-0">
                <span>Lugar nacimiento</span>
                <strong>{{ infoPacientes.lugar_nacimiento || 'N/D' }}</strong>
            </div>
        </div>
    </div>
</template>

<script>
import ApiServiceClinicaTrabajo from '@/services/ApiServiceClinicaTrabajo'

export default {
    data() {
        return {  
            infoPacientes: {},
        }
    },
    mounted() {
        console.log('PROP PacienteId (Info Básica):', this.pacienteId);
    },
    methods: {
        async obtenerPacientes(){
            try {
                const response = await ApiServiceClinicaTrabajo.get('/ExpedienteDetalleClinicaTrabajo/' + this.pacienteId)
                this.infoPacientes = response.data.data
                console.log('Pacientes cargados (Info Básica):', this.infoPacientes)
            } catch(error) {
                console.error("Error al obtener pacientes:", error)
            }
        },
        formatearFecha(fecha) {
            if (!fecha) return 'N/D'
            const f = new Date(fecha)
            if (isNaN(f.getTime())) return 'N/D'
            return f.toLocaleDateString('es-MX', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            })
        }
    },
    props: {
        pacienteId: {
            type: [Number, String],
            required: true
        }
    },
    watch: {
        pacienteId: {
            immediate: true,
            handler(id){
                if(id){
                    this.obtenerPacientes();
                }
            }
        }
    }
}
</script>

<style scoped>
.info-item {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #edf0f4;
}
.info-item span {
    color: #6c757d;
    font-size: 13px;
}
.info-item strong {
    color: #1f2937;
    font-size: 13px;
    text-align: right;
}
</style>