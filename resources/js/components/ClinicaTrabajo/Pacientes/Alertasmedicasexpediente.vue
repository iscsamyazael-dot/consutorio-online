<template>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 py-3 rounded-top-4">
            <h6 class="fw-bold mb-0 d-flex align-items-center">
                <i class="fas fa-triangle-exclamation text-danger me-2"></i>
                Alertas médicas
            </h6>
        </div>
        <div class="card-body">
            <div class="medical-alert d-flex align-items-start gap-3" v-if="infoPacientes.alergias">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div>
                    <strong class="d-block text-dark mb-1">
                        {{ textoAlergiaMedicamentos }}
                    </strong>
                    <small class="text-muted">
                        Evitar medicamentos relacionados.
                    </small>
                </div>
            </div>

            <div class="medical-alert-empty d-flex align-items-start gap-3" v-else>
                <div class="alert-icon-empty">
                    <i class="fas fa-circle-info"></i>
                </div>
                <div>
                    <strong class="d-block text-muted mb-1">
                        Sin alergias registradas
                    </strong>
                    <small v-if="hayAlergiaMedicamentos" class="text-muted">
                        Revisar el expediente, datos no disponibles.
                    </small>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ApiServiceClinicaTrabajo from '@/services/ApiServiceClinicaTrabajo'

export default {
    name: 'Alertasmedicas',
    data() {
        return {
            infoPacientes: {}
        }
    },
    mounted() {
        console.log('PROP PacienteId (Alertas):', this.pacienteId);
    },
    methods: {
        async obtenerPacientes(){
            try {
                const response = await ApiServiceClinicaTrabajo.get('/ExpedienteDetalleClinicaTrabajo/' + this.pacienteId)
                this.infoPacientes = response.data.data
                console.log('Pacientes cargados (Alertas médicas):', this.infoPacientes)
            } catch(error){
                console.error("Error al obtener pacientes:", error)
            }
        }
    },
    props: {
        pacienteId: {
            type: [Number, String],
            required: true
        }
    },
    computed: {
        textoAlergiaMedicamentos() {
            const valor = this.infoPacientes?.alergia_medicamentos
            if (!valor || !valor.trim()) return 'No se encontraron alergias a medicamentos'
            if (valor.trim().toLowerCase() === 'ninguna') return 'Ninguna'
            return 'Alergia a ' + valor
        },
        hayAlergiaMedicamentos() {
            const valor = this.infoPacientes?.alergia_medicamentos
            return !!(valor && valor.trim() && valor.trim().toLowerCase() !== 'ninguna')
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
.medical-alert{ background: #fff8f8; border: 1px solid #ffd6d6; border-radius: 16px; padding: 16px; }
.alert-icon{ width: 45px; height: 45px; min-width: 45px; border-radius: 50%; background: #ffe5e5; color: #dc3545; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.medical-alert-empty{ background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 16px; padding: 16px; }
.alert-icon-empty{ width: 45px; height: 45px; min-width: 45px; border-radius: 50%; background: #e9ecef; color: #6c757d; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
</style>