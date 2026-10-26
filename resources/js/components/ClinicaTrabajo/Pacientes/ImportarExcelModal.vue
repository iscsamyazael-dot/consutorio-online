<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 overflow-hidden">
        <div class="modal-header text-white border-0" :class="headerColor">
          <h5 class="modal-title fw-bold">
            <i class="fas fa-file-excel me-2"></i>Importar {{ titulo }}
          </h5>
          <button type="button" class="btn-close btn-close-white" @click="$emit('close')"></button>
        </div>
        
        <div class="modal-body p-4">
          <div class="alert alert-info d-flex align-items-start mb-4" role="alert">
            <i class="fas fa-info-circle me-2 fs-5 mt-1"></i>
            <div>
              <strong>Antes de subir:</strong> Descarga la plantilla oficial para asegurar que las columnas sean correctas.
              <br>
              <!-- ✅ CORREGIDO: Botón con mejor contraste -->
              <a :href="urlPlantilla" download class="btn btn-sm btn-primary mt-2 rounded-pill text-white">
                <i class="fas fa-download me-1"></i> Descargar Plantilla Excel
              </a>
            </div>
          </div>

          <div class="upload-zone text-center p-4 border border-2 border-dashed rounded-4" 
               :class="{ 'border-success bg-success-subtle': archivo }">
            <input type="file" id="fileInput" class="d-none" accept=".xlsx, .xls, .csv" @change="handleFileChange" />
            <label for="fileInput" class="cursor-pointer d-block">
              <i class="fas fa-cloud-upload-alt fs-1 text-success mb-2"></i>
              <h6 class="fw-bold mb-1">Haz clic para seleccionar el archivo</h6>
              <p class="text-muted small mb-0">Formatos: .xlsx, .xls, .csv (Máx. 5MB)</p>
            </label>
            <div v-if="archivo" class="mt-3 p-2 bg-white rounded-3 border d-flex align-items-center justify-content-center">
              <i class="fas fa-file-excel text-success me-2 fs-5"></i>
              <span class="fw-bold text-truncate">{{ archivo.name }}</span>
              <button class="btn btn-sm btn-link text-danger ms-2" @click.prevent="limpiarArchivo">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>

          <div v-if="mensajeError" class="alert alert-danger mt-3 mb-0">
            <i class="fas fa-exclamation-circle me-2"></i>{{ mensajeError }}
          </div>
          <div v-if="mensajeExito" class="alert alert-success mt-3 mb-0">
            <i class="fas fa-check-circle me-2"></i>{{ mensajeExito }}
          </div>
        </div>

        <div class="modal-footer border-0 bg-light">
          <button type="button" class="btn btn-secondary rounded-pill px-4" @click="$emit('close')" :disabled="cargando">
            Cancelar
          </button>
          <button type="button" class="btn text-white rounded-pill px-4" :class="btnColor" @click="subirArchivo" :disabled="!archivo || cargando">
            <span v-if="cargando" class="spinner-border spinner-border-sm me-2" role="status"></span>
            <i v-else class="fas fa-upload me-1"></i>
            {{ cargando ? 'Procesando...' : 'Importar Datos' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import ApiServiceClinicaTrabajo from '@/services/ApiServiceClinicaTrabajo'

const props = defineProps({
  show: { type: Boolean, default: false },
  tipo: { type: String, default: 'paciente' } // 'paciente', 'empresa', 'puesto'
})
const emit = defineEmits(['close', 'success'])

const archivo = ref(null)
const cargando = ref(false)
const mensajeError = ref('')
const mensajeExito = ref('')

// 1. Títulos y colores dinámicos
const titulo = computed(() => {
  return props.tipo === 'paciente' ? 'Pacientes' : props.tipo === 'empresa' ? 'Empresas' : 'Puestos de Trabajo'
})

const headerColor = computed(() => {
  return props.tipo === 'paciente' ? 'bg-primary' : props.tipo === 'empresa' ? 'bg-success' : 'bg-info'
})

const btnColor = computed(() => {
  return props.tipo === 'paciente' ? 'bg-primary' : props.tipo === 'empresa' ? 'bg-success' : 'bg-info'
})

// 2. ✅ CORREGIDO: URL de la plantilla (Agregada la 's' en /plantillas/ y extensiones .xlsx)
const urlPlantilla = computed(() => {
  const map = {
    'paciente': 'plantilla_pacientes.xlsx',      // ️ Si lo dejaste como .csv, cámbialo aquí a 'plantilla_pacientes.csv'
    'empresa': 'plantilla_empresas_puestos.xlsx', // ✅ Archivo unificado
    'puesto': 'plantilla_puestos.xlsx'           // ️ Si lo dejaste como .csv, cámbialo aquí a 'plantilla_puestos.csv'
  }
  return `/plantillas/${map[props.tipo]}` // ✅ Corregido: ahora dice /plantillas/ (con 's')
})

// 3. ✅ Mapeo exacto a tus rutas de Laravel
const endpoint = computed(() => {
  switch (props.tipo) {
    case 'paciente': return 'ImportarExcelInformacionPacientes'
    case 'empresa': return 'ImportarExcelInformacionEmpresas'
    case 'puesto': return 'ImportarExcelInformacionPuestoTrabajo'
    default: return ''
  }
})

const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 5 * 1024 * 1024) {
      mensajeError.value = 'El archivo es demasiado grande. Máximo 5MB.'
      archivo.value = null
      return
    }
    archivo.value = file
    mensajeError.value = ''
    mensajeExito.value = ''
  }
}

const limpiarArchivo = () => {
  archivo.value = null
  const input = document.getElementById('fileInput')
  if (input) input.value = '' // ✅ Agregado validación por si el elemento no existe
}

const subirArchivo = async () => {
  if (!archivo.value) return
  
  cargando.value = true
  mensajeError.value = ''
  mensajeExito.value = ''

  const formData = new FormData()
  formData.append('archivo', archivo.value)

  try {
    const response = await ApiServiceClinicaTrabajo.post(endpoint.value, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    if (response.data.success) {
      mensajeExito.value = response.data.message || `¡Importación completada con éxito!`
      setTimeout(() => {
        emit('success')
        emit('close')
      }, 1500)
    } else {
      mensajeError.value = response.data.message || 'Error al procesar el archivo.'
    }
  } catch (error) {
    console.error('Error en la importación:', error)
    mensajeError.value = error.response?.data?.message || 'Ocurrió un error inesperado al subir el archivo.'
  } finally {
    cargando.value = false
  }
}
</script>

<style scoped>
.border-dashed { border-style: dashed !important; transition: all 0.3s ease; }
.cursor-pointer { cursor: pointer; }
.upload-zone:hover { background-color: #f8f9fa; border-color: #198754 !important; }

/* ✅ NUEVO: Estilo personalizado para el botón de descarga dentro del alert */
.alert a.btn-primary {
  background-color: #0d6efd !important;
  border-color: #0d6efd !important;
  color: #ffffff !important;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
}

.alert a.btn-primary:hover {
  background-color: #0b5ed7 !important;
  border-color: #0a58ca !important;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
}
</style>