<template>
    <nav class="mb-4 small text-muted">
        <i class="fas fa-home me-1"></i>
        Pacientes /
        <strong class="text-dark">
            {{ infoPacientes.nombre }} {{ infoPacientes.apellido_paterno }} {{ infoPacientes.apellido_materno }}
        </strong>
    </nav>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center g-4">
                
                <!-- COLUMNA 1: AVATAR -->
                <div class="col-lg-1 col-md-2 col-12 text-center">
                    <div class="avatar-xl mx-auto">
                        {{ (infoPacientes.nombre || '?').substring(0, 2).toUpperCase() }}
                    </div>
                </div>

                <!-- COLUMNA 2: INFORMACIÓN PRINCIPAL -->
                <div class="col-lg-5 col-md-5 col-12">
                    <h2 class="fw-bold mb-3" style="font-size: 1.8rem; line-height: 1.2;">
                        {{ infoPacientes.nombre }} {{ infoPacientes.apellido_paterno }} {{ infoPacientes.apellido_materno }}
                    </h2>
                    
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-primary rounded-pill px-3 py-2" style="font-size: 0.85rem;">
                            <i class="fas fa-folder-open me-1"></i>
                            Expediente #{{ infoPacientes.id || '0000' }}
                        </span>
                        <span class="badge bg-info rounded-pill px-3 py-2" style="font-size: 0.85rem;">
                            <i class="fas fa-birthday-cake me-1"></i>
                            {{ infoPacientes.edad_formateada }}
                        </span>
                        <span class="badge bg-secondary rounded-pill px-3 py-2" style="font-size: 0.85rem;">
                            <i class="fas fa-{{ infoPacientes.genero === 'Hombre' ? 'mars' : 'venus' }} me-1"></i>
                            {{ infoPacientes.genero }}
                        </span>
                        <span v-if="infoPacientes.tipo_sangre" class="badge bg-danger rounded-pill px-3 py-2" style="font-size: 0.85rem;">
                            <i class="fas fa-tint me-1"></i>
                            {{ infoPacientes.tipo_sangre }}{{ infoPacientes.rh || '' }}
                        </span>
                    </div>

                    <div v-if="infoPacientes.curp" class="d-flex align-items-center">
                        <i class="fas fa-id-card text-muted me-2" style="font-size: 1rem;"></i>
                        <div>
                            <small class="text-muted d-block" style="font-size: 0.7rem; line-height: 1;">CURP</small>
                            <strong class="text-dark" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                                {{ infoPacientes.curp }}
                            </strong>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA 3: SEPARADOR VERTICAL -->
                <div class="col-lg-1 d-none d-lg-flex justify-content-center">
                    <div class="vertical-divider"></div>
                </div>

                <!-- COLUMNA 4: INFORMACIÓN LABORAL -->
                <div class="col-lg-3 col-md-5 col-12">
                    <div v-if="datosLaboralesActuales" class="info-laboral-container">
                        <div class="mb-3">
                            <div class="d-flex align-items-start mb-1">
                                <i class="fas fa-building text-primary me-2 mt-1" style="font-size: 0.9rem;"></i>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block mb-1" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                        Empresa
                                    </small>
                                    <strong class="text-dark d-block" style="font-size: 0.9rem; line-height: 1.3;">
                                        {{ datosLaboralesActuales.empresa_cliente?.razon_social || 'No registrada' }}
                                    </strong>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex align-items-start">
                                <i class="fas fa-briefcase text-success me-2 mt-1" style="font-size: 0.9rem;"></i>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block mb-1" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                        Puesto
                                    </small>
                                    <strong class="text-dark d-block" style="font-size: 0.9rem; line-height: 1.3;">
                                        {{ datosLaboralesActuales.puesto_trabajo?.nombre || 'No registrado' }}
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-muted small text-center py-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Sin datos laborales
                    </div>
                </div>

                <!-- COLUMNA 5: BOTÓN NUEVA VALORACIÓN -->
                <div class="col-lg-2 col-md-12 col-12">
                    <button 
                        class="btn btn-success rounded-pill shadow-sm w-100" 
                        type="button" 
                        @click="abrirModalValoraciones"
                        style="font-size: 0.9rem; padding: 14px 20px;"
                    >
                        <i class="fas fa-plus-circle me-2"></i>
                        Nueva Valoración
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- MODAL DE SELECCIÓN DE VALORACIÓN             -->
    <!-- ============================================ -->
    <div class="modal fade" id="modalValoraciones" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4">
                
                <!-- Header -->
                <div class="modal-header bg-primary text-white border-0 rounded-top-4">
                    <div>
                        <h5 class="modal-title fw-bold mb-1">
                            <i class="fas fa-clipboard-list me-2"></i>
                            Seleccionar Tipo de Valoración
                        </h5>
                        <small class="text-white-50">
                            Paciente: {{ infoPacientes.nombre }} {{ infoPacientes.apellido_paterno }}
                        </small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Body -->
                <div class="modal-body p-4">
                    <div class="row g-3">
                        
                        <!-- Ocupacional -->
                        <div class="col-md-6">
                            <a :href="'/ficha-ocupacional/' + infoPacientes.id" class="valoracion-card text-decoration-none">
                                <div class="d-flex align-items-start p-3 border rounded-3 h-100 hover-shadow">
                                    <div class="valoracion-icon bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                                        <i class="fas fa-briefcase-medical fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1 text-dark">Ocupacional</h6>
                                        <small class="text-muted d-block mb-2">Ingreso, periódicos, egreso y cambio de puesto</small>
                                        <span class="badge bg-primary bg-opacity-10 text-primary">NOM-031-STPS</span>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Psicológica -->
                        <div class="col-md-6">
                            <a :href="'/ficha-psicologia/' + infoPacientes.id" class="valoracion-card text-decoration-none">
                                <div class="d-flex align-items-start p-3 border rounded-3 h-100 hover-shadow">
                                    <div class="valoracion-icon bg-info bg-opacity-10 text-info rounded-3 p-3 me-3">
                                        <i class="fas fa-brain fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1 text-dark">Psicológica</h6>
                                        <small class="text-muted d-block mb-2">Evaluación de riesgo psicosocial</small>
                                        <span class="badge bg-info bg-opacity-10 text-info">NOM-035-STPS</span>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Nutricional -->
                        <div class="col-md-6">
                            <a :href="'/ficha-nutricion/' + infoPacientes.id" class="valoracion-card text-decoration-none">
                                <div class="d-flex align-items-start p-3 border rounded-3 h-100 hover-shadow">
                                    <div class="valoracion-icon bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                                        <i class="fas fa-apple-alt fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1 text-dark">Nutricional</h6>
                                        <small class="text-muted d-block mb-2">Estado nutricional y hábitos alimenticios</small>
                                        <span class="badge bg-success bg-opacity-10 text-success">Evaluación clínica</span>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Audiología - Historia Clínica -->
                        <div class="col-md-6">
                            <a :href="'/FichaDelliAudiologia/' + infoPacientes.id" class="valoracion-card text-decoration-none">
                                <div class="d-flex align-items-start p-3 border rounded-3 h-100 hover-shadow">
                                    <div class="valoracion-icon bg-warning bg-opacity-10 text-warning rounded-3 p-3 me-3">
                                        <i class="fas fa-stethoscope fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1 text-dark">Audiología - Historia Clínica</h6>
                                        <small class="text-muted d-block mb-2">Antecedentes audiológicos y exposición a ruido</small>
                                        <span class="badge bg-warning bg-opacity-10 text-warning">Evaluación preventiva</span>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Audiología - Alto Riesgo -->
                        <div class="col-md-6">
                            <a :href="'/FichaTrabajoAltoRiesgo/' + infoPacientes.id" class="valoracion-card text-decoration-none">
                                <div class="d-flex align-items-start p-3 border rounded-3 h-100 hover-shadow">
                                    <div class="valoracion-icon bg-danger bg-opacity-10 text-danger rounded-3 p-3 me-3">
                                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1 text-dark">Audiología - Alto Riesgo</h6>
                                        <small class="text-muted d-block mb-2">Evaluación audiométrica para puestos con ruido</small>
                                        <span class="badge bg-danger bg-opacity-10 text-danger">NOM-011-STPS</span>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Espirometría -->
                        <div class="col-md-6">
                            <a :href="'/ficha-espirometria/' + infoPacientes.id" class="valoracion-card text-decoration-none">
                                <div class="d-flex align-items-start p-3 border rounded-3 h-100 hover-shadow">
                                    <div class="valoracion-icon bg-danger bg-opacity-10 text-danger rounded-3 p-3 me-3">
                                        <i class="fas fa-lungs fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1 text-dark">Espirometría</h6>
                                        <small class="text-muted d-block mb-2">Pruebas de función pulmonar</small>
                                        <span class="badge bg-danger bg-opacity-10 text-danger">NOM-010-STPS</span>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Ergonómica -->
                        <div class="col-md-6">
                            <a :href="'/clinica/ergonomia/nueva/' + infoPacientes.id" class="valoracion-card text-decoration-none">
                                <div class="d-flex align-items-start p-3 border rounded-3 h-100 hover-shadow">
                                    <div class="valoracion-icon bg-secondary bg-opacity-10 text-secondary rounded-3 p-3 me-3">
                                        <i class="fas fa-chair fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1 text-dark">Ergonómica</h6>
                                        <small class="text-muted d-block mb-2">Evaluación de factores de riesgo ergonómico</small>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">NOM-036-STPS</span>
                                    </div>
                                </div>
                            </a>
                        </div>

                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>
                        Cancelar
                    </button>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
/* Avatar */
.avatar-xl { 
    width: 100px; 
    height: 100px; 
    border-radius: 50%; 
    background: linear-gradient(135deg, #0d6efd, #0dcaf0); 
    color: white; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-size: 2.2rem; 
    font-weight: bold;
    box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
}

/* Separador vertical */
.vertical-divider {
    width: 2px;
    height: 120px;
    background: linear-gradient(to bottom, transparent, #e9ecef 20%, #e9ecef 80%, transparent);
}

/* Contenedor de información laboral */
.info-laboral-container {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 12px;
    border-left: 4px solid #0d6efd;
}

/* Tarjetas de valoración en el modal */
.valoracion-card {
    transition: all 0.3s ease;
    display: block;
}

.valoracion-card:hover {
    transform: translateY(-4px);
}

.hover-shadow:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
    border-color: #0d6efd !important;
}

.valoracion-icon {
    flex-shrink: 0;
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Responsive */
@media (max-width: 991px) { 
    .vertical-divider {
        display: none;
    }
    
    .info-laboral-container {
        margin-top: 20px;
        border-left: none;
        border-top: 4px solid #0d6efd;
    }
}

@media (max-width: 767px) {
    .avatar-xl {
        width: 80px;
        height: 80px;
        font-size: 1.8rem;
    }
    
    h2 {
        font-size: 1.4rem !important;
        text-align: center;
    }
    
    .d-flex.flex-wrap.gap-2 {
        justify-content: center;
    }

    .valoracion-icon {
        width: 60px;
        height: 60px;
    }
    
    .valoracion-icon i {
        font-size: 1.5rem !important;
    }
}
</style>

<!-- Estilos globales para el modal -->
<style>
.modal-content {
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
    padding: 1.5rem;
}

.modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

@media (max-width: 767px) {
    .modal-dialog {
        margin: 1rem;
    }
}
</style>

<script>
import ApiServiceClinicaTrabajo from '@/services/ApiServiceClinicaTrabajo'

export default {
    data() {
        return {
            infoPacientes: {},
            datosLaboralesActuales: null
        }
    },
    mounted() {
        console.log('Paciente recibido (Barra Superior):', this.pacienteId);
    },
    methods: {
        async obtenerPacientes() {
            try {
                const response = await ApiServiceClinicaTrabajo.get('/ExpedienteDetalleClinicaTrabajo/' + this.pacienteId);
                this.infoPacientes = response.data.data;
                
                const datosLaborales = response.data.data.datos_laborales || [];
                this.datosLaboralesActuales = datosLaborales.length > 0 
                    ? datosLaborales[datosLaborales.length - 1]
                    : null;
                
                console.log('Expediente cargado (Barra Superior):', this.infoPacientes);
                console.log('Datos laborales (Barra Superior):', this.datosLaboralesActuales);
            } catch (error) {
                console.error('Error al obtener paciente:', error);
            }
        },
        
        abrirModalValoraciones() {
            const modalEl = document.getElementById('modalValoraciones');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
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
            handler(id) {
                if (id) {
                    this.obtenerPacientes();
                }
            }
        }
    }
}
</script>