<template>
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="fw-bold mb-0">
                <i class="fas fa-briefcase text-primary"></i>
                Información Laboral
            </h6>
        </div>
        <div class="card-body">

            <!-- ENCABEZADO CONTEXTUAL (empresa y puesto resumidos) -->
            <div v-if="datosLaboralesActuales" class="mb-3 p-2 bg-light rounded">
                <small class="text-muted d-block">
                    <i class="fas fa-building me-1"></i>
                    {{ datosLaboralesActuales.empresa_cliente?.razon_social || 'Empresa no registrada' }}
                </small>
                <strong class="text-dark" style="font-size: 14px;">
                    <i class="fas fa-briefcase me-1"></i>
                    {{ datosLaboralesActuales.puesto_trabajo?.nombre || 'Puesto no registrado' }}
                </strong>
            </div>

            <div v-else class="text-center text-muted py-3">
                <i class="fas fa-info-circle me-2"></i>
                Sin datos laborales registrados
            </div>

            <!-- DETALLES LABORALES (sin repetir empresa/puesto) -->
            <div v-if="datosLaboralesActuales" class="mb-3">
                
                <div class="info-item" v-if="datosLaboralesActuales.empresa_cliente?.giro_actividad">
                    <span>Giro / Actividad</span>
                    <strong class="text-muted" style="font-size: 11px;">{{ datosLaboralesActuales.empresa_cliente.giro_actividad }}</strong>
                </div>

                <div class="info-item">
                    <span>Área / Departamento</span>
                    <strong>{{ datosLaboralesActuales.puesto_trabajo?.departamento || 'No registrada' }}</strong>
                </div>

                <div class="info-item" v-if="datosLaboralesActuales.numero_empleado">
                    <span>N° Empleado</span>
                    <strong class="text-primary">{{ datosLaboralesActuales.numero_empleado }}</strong>
                </div>

                <div class="info-item">
                    <span>Fecha de ingreso</span>
                    <strong>{{ formatearFecha(datosLaboralesActuales.fecha_ingreso_empresa) }}</strong>
                </div>

                <div class="info-item">
                    <span>Antigüedad</span>
                    <strong>{{ calcularAntiguedad(datosLaboralesActuales.fecha_ingreso_empresa) }}</strong>
                </div>

                <div class="info-item" v-if="datosLaboralesActuales.modalidad_trabajo">
                    <span>Modalidad</span>
                    <strong>{{ datosLaboralesActuales.modalidad_trabajo }}</strong>
                </div>

            </div>

            <!-- CARACTERÍSTICAS DEL PUESTO -->
            <div v-if="datosLaboralesActuales?.puesto_trabajo" class="mb-3">
                <hr class="my-3">
                <h6 class="fw-bold text-muted mb-2" style="font-size:13px;">
                    <i class="fas fa-tasks text-info me-1"></i>
                    Características del Puesto
                </h6>

                <div class="caracteristicas-grid">
                    <span 
                        v-if="datosLaboralesActuales.puesto_trabajo.conduce_vehiculos" 
                        class="badge bg-info text-dark me-1 mb-1"
                        style="font-size: 11px; padding: 5px 8px;"
                    >
                        <i class="fas fa-truck me-1"></i> Conduce vehículos
                    </span>

                    <span 
                        v-if="datosLaboralesActuales.puesto_trabajo.manejo_alimentos" 
                        class="badge bg-success me-1 mb-1"
                        style="font-size: 11px; padding: 5px 8px;"
                    >
                        <i class="fas fa-utensils me-1"></i> Manejo de alimentos
                    </span>

                    <span 
                        v-if="datosLaboralesActuales.puesto_trabajo.requiere_epp" 
                        class="badge bg-warning text-dark me-1 mb-1"
                        style="font-size: 11px; padding: 5px 8px;"
                    >
                        <i class="fas fa-hard-hat me-1"></i> Requiere EPP
                    </span>

                    <span 
                        v-if="datosLaboralesActuales.puesto_trabajo.riesgo_alturas" 
                        class="badge bg-danger me-1 mb-1"
                        style="font-size: 11px; padding: 5px 8px;"
                    >
                        <i class="fas fa-mountain me-1"></i> Trabajo en alturas
                    </span>

                    <span 
                        v-if="datosLaboralesActuales.puesto_trabajo.riesgo_electrico" 
                        class="badge bg-danger me-1 mb-1"
                        style="font-size: 11px; padding: 5px 8px;"
                    >
                        <i class="fas fa-bolt me-1"></i> Riesgo eléctrico
                    </span>

                    <span 
                        v-if="datosLaboralesActuales.puesto_trabajo.riesgo_biologico" 
                        class="badge bg-danger me-1 mb-1"
                        style="font-size: 11px; padding: 5px 8px;"
                    >
                        <i class="fas fa-virus me-1"></i> Riesgo biológico
                    </span>

                    <span 
                        v-if="datosLaboralesActuales.puesto_trabajo.riesgo_ergonomico" 
                        class="badge bg-warning text-dark me-1 mb-1"
                        style="font-size: 11px; padding: 5px 8px;"
                    >
                        <i class="fas fa-chair me-1"></i> Riesgo ergonómico
                    </span>

                    <span 
                        v-if="datosLaboralesActuales.puesto_trabajo.riesgo_espacios_confinados" 
                        class="badge bg-danger me-1 mb-1"
                        style="font-size: 11px; padding: 5px 8px;"
                    >
                        <i class="fas fa-compress-alt me-1"></i> Espacios confinados
                    </span>
                </div>

                <div v-if="!tieneCaracteristicasActivas" class="text-muted small">
                    <i class="fas fa-check-circle text-success me-1"></i>
                    Puesto sin riesgos especiales identificados
                </div>
            </div>

            <!-- FACTORES DE RIESGO ERGONÓMICO -->
            <div v-if="tieneRiesgosErgonomicos" class="mb-3">
                <hr class="my-3">
                <h6 class="fw-bold text-muted mb-2" style="font-size:13px;">
                    <i class="fas fa-chart-bar text-warning me-1"></i>
                    Factores de Riesgo Ergonómico
                </h6>
                <div class="riesgos-ergonomicos">
                    <div v-if="datosLaboralesActuales.puesto_trabajo.riesgo_ergonomico_estatico > 0" class="info-item">
                        <span>Estático</span>
                        <strong>{{ datosLaboralesActuales.puesto_trabajo.riesgo_ergonomico_estatico }}</strong>
                    </div>
                    <div v-if="datosLaboralesActuales.puesto_trabajo.riesgo_ergonomico_forzado > 0" class="info-item">
                        <span>Forzado</span>
                        <strong>{{ datosLaboralesActuales.puesto_trabajo.riesgo_ergonomico_forzado }}</strong>
                    </div>
                    <div v-if="datosLaboralesActuales.puesto_trabajo.riesgo_ergonomico_repetitivo > 0" class="info-item">
                        <span>Repetitivo</span>
                        <strong>{{ datosLaboralesActuales.puesto_trabajo.riesgo_ergonomico_repetitivo }}</strong>
                    </div>
                    <div v-if="datosLaboralesActuales.puesto_trabajo.riesgo_ergonomico_ritmo > 0" class="info-item">
                        <span>Ritmo</span>
                        <strong>{{ datosLaboralesActuales.puesto_trabajo.riesgo_ergonomico_ritmo }}</strong>
                    </div>
                </div>
            </div>

            <!-- CONTACTO DE EMERGENCIA -->
            <hr class="my-3">
            <h6 class="fw-bold text-muted mb-2" style="font-size:13px;">
                <i class="fas fa-phone-alt text-danger me-1"></i>
                Contacto de Emergencia
            </h6>
            <div class="info-item">
                <span>Nombre</span>
                <strong>{{ infoPacientes.contacto_emergencia || 'No registrado' }}</strong>
            </div>
            <div class="info-item">
                <span>Teléfono</span>
                <strong>{{ infoPacientes.telefono_emergencia || 'No registrado' }}</strong>
            </div>
            <div class="info-item mb-0">
                <span>Parentesco</span>
                <strong>{{ infoPacientes.relacion_contacto_emergencia || 'No especificado' }}</strong>
            </div>

        </div>
    </div>
</template>

<script>
import ApiServiceClinicaTrabajo from '@/services/ApiServiceClinicaTrabajo'

export default {
    name: 'InformacionLaboral',
    data() {
        return {  
            infoPacientes: {},
            datosLaboralesActuales: null,
        }
    },
    mounted() {
        console.log('PROP PacienteId (Info Laboral):', this.pacienteId);
    },
    computed: {
        tieneCaracteristicasActivas() {
            if (!this.datosLaboralesActuales?.puesto_trabajo) return false
            const p = this.datosLaboralesActuales.puesto_trabajo
            return p.conduce_vehiculos || p.manejo_alimentos || p.requiere_epp || 
                   p.riesgo_alturas || p.riesgo_electrico || p.riesgo_biologico || 
                   p.riesgo_ergonomico || p.riesgo_espacios_confinados
        },
        tieneRiesgosErgonomicos() {
            if (!this.datosLaboralesActuales?.puesto_trabajo) return false
            const p = this.datosLaboralesActuales.puesto_trabajo
            return p.riesgo_ergonomico_estatico > 0 || p.riesgo_ergonomico_forzado > 0 || 
                   p.riesgo_ergonomico_repetitivo > 0 || p.riesgo_ergonomico_ritmo > 0
        }
    },
    methods: {
        async obtenerDatosLaborales(){
            try {
                const response = await ApiServiceClinicaTrabajo.get('/ExpedienteDetalleClinicaTrabajo/' + this.pacienteId)
                this.infoPacientes = response.data.data
                
                const datosLaborales = response.data.data.datos_laborales || []
                this.datosLaboralesActuales = datosLaborales.length > 0 
                    ? datosLaborales[datosLaborales.length - 1]
                    : null
                
                console.log('Datos laborales cargados:', this.datosLaboralesActuales)
            } catch(error) {
                console.error("Error al obtener datos laborales:", error)
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
        },
        
        calcularAntiguedad(fechaIngreso) {
            if (!fechaIngreso) return 'N/D'
            
            const ingreso = new Date(fechaIngreso)
            const hoy = new Date()
            
            let años = hoy.getFullYear() - ingreso.getFullYear()
            let meses = hoy.getMonth() - ingreso.getMonth()
            
            if (meses < 0 || (meses === 0 && hoy.getDate() < ingreso.getDate())) {
                años--
                meses += 12
            }
            
            if (años === 0) return `${meses} mes(es)`
            if (meses === 0) return `${años} año(s)`
            return `${años} año(s) y ${meses} mes(es)`
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
                    this.obtenerDatosLaborales();
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

.caracteristicas-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.riesgos-ergonomicos {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
</style>