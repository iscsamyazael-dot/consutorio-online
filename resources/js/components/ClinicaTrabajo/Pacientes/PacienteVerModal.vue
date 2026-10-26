<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content border-0 rounded-4 overflow-hidden">
        <!-- HEADER -->
        <div class="modal-header modal-header-custom border-0">
          <h5 class="modal-title fw-bold">
            <i class="fas fa-id-card me-2"></i>Expediente Clínico Laboral
          </h5>
          <button type="button" class="btn-close btn-close-white" @click="$emit('close')"></button>
        </div>
        <div class="modal-body p-4" v-if="paciente">
          <!-- 1. PERFIL DEL PACIENTE -->
          <div class="patient-profile mb-4 p-4 rounded-3 d-flex align-items-center gap-3">
            <div class="avatar-large" :style="{ background: obtenerColor(paciente.nombre) }">
              {{ paciente.nombre?.charAt(0) || 'P' }}
            </div>
            <div>
              <h4 class="fw-bold mb-2">{{ paciente.nombre }}</h4>
              <div class="d-flex gap-2 flex-wrap">
                <span class="badge-custom badge-primary">Folio: {{ paciente.folio || 'N/A' }}</span>
                <span class="badge-custom badge-info">{{ paciente.edad_formateada || calcularEdad(paciente.fecha_nacimiento) }}</span>
                <span class="badge-custom badge-secondary">{{ paciente.genero || 'No especificado' }}</span>
                <span class="badge-custom" :class="estaActivoLaboralmente ? 'badge-success' : 'badge-warning'">
                  {{ estaActivoLaboralmente ? 'Activo Laboralmente' : 'Sin asignación activa' }}
                </span>
              </div>
            </div>
          </div>

          <!-- 2. INFORMACIÓN LABORAL -->
          <h6 class="section-title text-primary mb-3">
            <i class="fas fa-briefcase me-2"></i>Información Laboral Actual
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <div class="info-card">
                <label><i class="fas fa-building me-1 text-muted"></i>Empresa</label>
                <h6>{{ obtenerNombreEmpresa() }}</h6>
              </div>
            </div>
            <div class="col-md-6">
              <div class="info-card">
                <label><i class="fas fa-id-badge me-1 text-muted"></i>Nº Empleado</label>
                <h6>{{ datosLaborales.numero_empleado || 'No registrado' }}</h6>
              </div>
            </div>
            <div class="col-md-4">
              <div class="info-card">
                <label><i class="fas fa-user-tie me-1 text-muted"></i>Puesto</label>
                <h6>{{ obtenerNombrePuesto() }}</h6>
              </div>
            </div>
            <div class="col-md-4">
              <div class="info-card">
                <label><i class="fas fa-calendar-alt me-1 text-muted"></i>Fecha Ingreso</label>
                <h6>{{ formatearFecha(datosLaborales.fecha_ingreso_empresa) }}</h6>
              </div>
            </div>
            <div class="col-md-4">
              <div class="info-card">
                <label><i class="fas fa-clock me-1 text-muted"></i>Turno / Contrato</label>
                <h6>{{ capitalize(datosLaborales.turno) }} / {{ datosLaborales.tipo_contrato || 'N/A' }}</h6>
              </div>
            </div>
          </div>

          <!-- 3. ALERTAS MÉDICAS CRÍTICAS -->
          <h6 class="section-title text-danger mb-3">
            <i class="fas fa-exclamation-triangle me-2"></i>Alertas Médicas
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-3">
              <div class="info-card border-start-custom border-danger">
                <label>Tipo de Sangre</label>
                <h6 class="text-danger fw-bold">
                  {{ paciente.tipo_sangre && paciente.rh ? paciente.tipo_sangre + paciente.rh : 'No registrado' }}
                </h6>
              </div>
            </div>
            <div class="col-md-9">
              <div class="info-card border-start-custom border-warning">
                <label>Alergias (General y Medicamentos)</label>
                <h6 class="text-warning fw-bold">
                  <span v-if="paciente.alergias?.trim()">{{ paciente.alergias }}</span>
                  <span v-if="paciente.alergia_medicamentos?.trim()" class="d-block text-muted small mt-1">
                    <i class="fas fa-pills me-1"></i>Medicamentos: {{ paciente.alergia_medicamentos }}
                  </span>
                  <span v-if="!paciente.alergias?.trim() && !paciente.alergia_medicamentos?.trim()">Sin alergias registradas</span>
                </h6>
              </div>
            </div>
            <div class="col-md-6">
              <div class="info-card">
                <label>Enfermedades Crónicas</label>
                <h6>{{ paciente.enfermedades_cronicas?.trim() ? paciente.enfermedades_cronicas : 'Ninguna registrada' }}</h6>
              </div>
            </div>
            <div class="col-md-6">
              <div class="info-card">
                <label>Antecedentes Quirúrgicos</label>
                <h6>{{ paciente.antecedentes_quirurgicos?.trim() ? paciente.antecedentes_quirurgicos : 'Ninguno registrado' }}</h6>
              </div>
            </div>
          </div>

          <!-- 4. CONTACTO Y EMERGENCIA -->
          <h6 class="section-title text-info mb-3">
            <i class="fas fa-address-book me-2"></i>Contacto y Emergencia
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <div class="info-card">
                <label><i class="fas fa-phone me-1"></i>Teléfono / Celular</label>
                <h6>{{ paciente.telefono || paciente.celular || 'No registrado' }}</h6>
              </div>
            </div>
            <div class="col-md-6">
              <div class="info-card">
                <label><i class="fas fa-envelope me-1"></i>Email</label>
                <h6>{{ paciente.email || 'No registrado' }}</h6>
              </div>
            </div>
            <div class="col-md-12">
              <div class="info-card emergency-card">
                <label class="text-danger fw-bold"><i class="fas fa-ambulance me-1"></i>Contacto de Emergencia</label>
                <h6 class="mb-0">
                  {{ paciente.contacto_emergencia || 'No registrado' }}
                  <span class="badge-custom badge-secondary ms-2">{{ paciente.relacion_contacto_emergencia || 'N/A' }}</span>
                </h6>
                <small class="text-muted"><i class="fas fa-phone-alt me-1"></i>{{ paciente.telefono_emergencia || 'No registrado' }}</small>
              </div>
            </div>
          </div>

          <!-- 5. NOTAS Y ÚLTIMA CONSULTA -->
          <div class="info-card notes-card">
            <label class="fw-bold"><i class="fas fa-sticky-note me-1"></i>Última Consulta: {{ formatearFecha(paciente.fecha_ultima_consulta_medica) }}</label>
            <p class="mb-1 fw-bold">{{ paciente.motivo_ultima_consulta_medica || 'Sin motivo registrado' }}</p>
            <p class="mb-0 text-muted small">{{ paciente.notas_generales || 'Sin notas generales.' }}</p>
          </div>
        </div>

        <!-- FOOTER -->
        <div class="modal-footer border-0 bg-light-custom">
          <button type="button" class="btn btn-secondary-custom rounded-pill px-4" @click="$emit('close')">
            <i class="fas fa-times me-1"></i> Cerrar
          </button>
          <a :href="'/ActualizarPaciente/' + paciente.id" class="btn btn-primary-custom rounded-pill px-4">
            <i class="fas fa-edit me-1"></i> Editar Expediente
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  show: { type: Boolean, default: false },
  paciente: { type: Object, default: () => ({}) }
})
defineEmits(['close'])

const datosLaborales = computed(() => {
  if (props.paciente?.datos_laborales && props.paciente.datos_laborales.length > 0) {
    return props.paciente.datos_laborales[0]
  }
  return {}
})

const estaActivoLaboralmente = computed(() => {
  return Object.keys(datosLaborales.value).length > 0
})

const obtenerColor = (nombre) => {
  const colores = ["#1976d2", "#43a047", "#7b1fa2", "#ef6c00", "#00897b", "#c2185b", "#5d4037", "#546e7a"]
  if (!nombre) return "#6c757d"
  let suma = 0
  for (let i = 0; i < String(nombre).length; i++) suma += String(nombre).charCodeAt(i)
  return colores[suma % colores.length]
}

const formatearFecha = (fecha) => {
  if (!fecha) return 'No registrada'
  return new Date(fecha).toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' })
}

const capitalize = (text) => {
  if (!text) return ''
  return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase()
}

const obtenerNombreEmpresa = () => {
  return datosLaborales.value.empresa_cliente?.razon_social || `ID Empresa: ${datosLaborales.value.empresa_cliente_id || 'N/A'}`
}

const obtenerNombrePuesto = () => {
  return datosLaborales.value.puesto_trabajo?.nombre || `ID Puesto: ${datosLaborales.value.puesto_trabajo_id || 'N/A'}`
}

const calcularEdad = (fechaNacimiento) => {
  if (!fechaNacimiento) return 'N/A'
  const hoy = new Date()
  const fechaNac = new Date(fechaNacimiento)
  let edad = hoy.getFullYear() - fechaNac.getFullYear()
  const mes = hoy.getMonth() - fechaNac.getMonth()
  if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) edad--
  return `${edad} años`
}
</script>

<style scoped>
.modal-header-custom {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 20px 24px;
}

.patient-profile {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  border: 1px solid #e9ecef;
}

.avatar-large {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 32px;
  font-weight: bold;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.section-title {
  font-weight: 700;
  font-size: 1rem;
  padding-bottom: 8px;
  border-bottom: 2px solid #e9ecef;
}

.info-card {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 16px 18px;
  height: 100%;
  border: 1px solid #e9ecef;
  transition: all 0.2s ease;
}

.info-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.info-card label {
  font-size: 11px;
  color: #6c757d;
  margin-bottom: 6px;
  display: block;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

.info-card h6 {
  margin: 0;
  font-weight: 600;
  font-size: 15px;
  color: #212529;
}

.border-start-custom {
  border-left-width: 4px !important;
  border-left-style: solid !important;
}

.emergency-card {
  background: #fff5f5;
  border: 1px solid #ffcdd2;
}

.notes-card {
  background: #f8f9fa;
  border: 1px solid #e9ecef;
}

.badge-custom {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 500;
}

.badge-primary {
  background: #e3f2fd;
  color: #1976d2;
}

.badge-info {
  background: #e0f7fa;
  color: #00838f;
}

.badge-secondary {
  background: #f5f5f5;
  color: #616161;
}

.badge-success {
  background: #e8f5e9;
  color: #2e7d32;
}

.badge-warning {
  background: #fff3e0;
  color: #ef6c00;
}

.bg-light-custom {
  background: #f8f9fa !important;
}

.btn-secondary-custom {
  background: #6c757d;
  color: white;
  border: none;
  transition: all 0.2s ease;
}

.btn-secondary-custom:hover {
  background: #5a6268;
  color: white;
}

.btn-primary-custom {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  transition: all 0.2s ease;
}

.btn-primary-custom:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
  color: white;
}
</style>