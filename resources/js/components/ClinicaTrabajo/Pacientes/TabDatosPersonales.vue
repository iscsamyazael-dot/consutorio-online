<template>
  <div class="tab-section">
    <!-- Sección Identificación -->
    <div class="section-header">
      <div class="section-icon bg-primary">
        <i class="fas fa-id-card"></i>
      </div>
      <div class="section-title">
        <h3>Datos de Identificación</h3>
        <p>Información personal del paciente</p>
      </div>
    </div>

    <div class="form-grid">
      <div class="form-group full-width">
        <label class="form-label">
          <i class="fas fa-user"></i>
          Nombre completo <span class="required">*</span>
        </label>
        <div class="input-wrapper">
          <input type="text" v-model="form.nombre" class="form-input" placeholder="Ej: Juan Pérez" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-calendar"></i>
          Fecha de nacimiento <span class="required">*</span>
        </label>
        <div class="input-wrapper">
          <input type="date" v-model="form.fecha_nacimiento" class="form-input" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-hourglass-half"></i>
          Edad
        </label>
        <div class="input-wrapper">
          <input type="text" readonly class="form-input readonly" :placeholder="edadCalculada">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-venus-mars"></i>
          Género
        </label>
        <div class="radio-group">
          <label class="radio-option">
            <input type="radio" v-model="form.genero" value="Mujer">
            <span class="radio-label">Mujer</span>
          </label>
          <label class="radio-option">
            <input type="radio" v-model="form.genero" value="Hombre">
            <span class="radio-label">Hombre</span>
          </label>
          <label class="radio-option">
            <input type="radio" v-model="form.genero" value="No binario">
            <span class="radio-label">No binario</span>
          </label>
          <label class="radio-option">
            <input type="radio" v-model="form.genero" value="Otro">
            <span class="radio-label">Otro</span>
          </label>
          <label class="radio-option">
            <input type="radio" v-model="form.genero" value="Prefiero no decir">
            <span class="radio-label">Prefiero no decir</span>
          </label>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-heart"></i>
          Estado civil
        </label>
        <div class="input-wrapper">
          <select v-model="form.estado_civil" class="form-input">
            <option value="">Seleccionar...</option>
            <option value="Soltero">Soltero</option>
            <option value="Casado">Casado</option>
            <option value="Viudo">Viudo</option>
            <option value="Divorciado">Divorciado</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-graduation-cap"></i>
          Escolaridad
        </label>
        <div class="input-wrapper">
          <select v-model="form.escolaridad" class="form-input">
            <option value="">Seleccionar...</option>
            <option value="Primaria">Primaria</option>
            <option value="Secundaria">Secundaria</option>
            <option value="Preparatoria">Preparatoria</option>
            <option value="Universidad">Universidad</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-map-marker-alt"></i>
          Lugar de nacimiento
        </label>
        <div class="input-wrapper">
          <input type="text" v-model="form.lugar_nacimiento" class="form-input" placeholder="Ej: Ciudad de México">
        </div>
      </div>
    </div>

    <!-- ✅ Sección Identificación Oficial (CURP y Cédula) -->
    <div class="section-header">
      <div class="section-icon bg-info">
        <i class="fas fa-id-badge"></i>
      </div>
      <div class="section-title">
        <h3>Identificación Oficial</h3>
        <p>Documentos de identificación del paciente</p>
      </div>
    </div>

    <div class="form-grid">
      <!-- ✅ Ahora en 2 columnas -->
      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-fingerprint"></i>
          CURP
        </label>
        <div class="input-wrapper">
          <input type="text" v-model="form.curp" class="form-input" maxlength="18" placeholder="XXXXXXXXXXXXXXXXXX" style="text-transform: uppercase;">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-certificate"></i>
          Cédula profesional
        </label>
        <div class="input-wrapper">
          <input type="text" v-model="form.cedula_profesional" class="form-input" placeholder="Opcional">
        </div>
      </div>
    </div>

    <!-- ✅ Sección Fotografía -->
    <div class="section-header">
      <div class="section-icon bg-success">
        <i class="fas fa-camera"></i>
      </div>
      <div class="section-title">
        <h3>Fotografía</h3>
        <p>Imagen del paciente</p>
      </div>
    </div>

    <div class="form-grid">
      <div class="form-group full-width">
        <div class="file-upload-wrapper">
          <input type="file" @change="handleFotoChange" class="hidden" ref="fileInput"/>
          <button type="button" class="btn-upload" @click="fotoClick">
            <i class="fas fa-upload"></i>
            <span>Seleccionar foto</span>
          </button>
          <div v-if="fotoUrl" class="foto-preview">
            <img :src="fotoUrl" alt="Foto paciente">
          </div>
        </div>
      </div>
    </div>

    <!-- ✅ Sección Consentimiento -->
    <div class="section-header">
      <div class="section-icon bg-warning">
        <i class="fas fa-shield-alt"></i>
      </div>
      <div class="section-title">
        <h3>Consentimiento</h3>
        <p>Autorización de datos personales</p>
      </div>
    </div>

    <div class="form-grid">
      <div class="form-group full-width">
        <div class="checkbox-wrapper">
          <input type="checkbox" id="consentimiento" v-model="form.consentimiento_datos" class="checkbox-input">
          <label for="consentimiento" class="checkbox-label">
            <i class="fas fa-check-circle"></i>
            Acepto el tratamiento de mis datos personales según la política de privacidad
          </label>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  initialData: { type: Object, default: () => ({}) }
})

const form = ref({
  nombre: '', fecha_nacimiento: '', genero: '', estado_civil: '',
  escolaridad: '', lugar_nacimiento: '', curp: '', cedula_profesional: '',
  consentimiento_datos: false, foto: null
})

watch(() => props.initialData, (newData) => {
  if (newData && Object.keys(newData).length > 0) {
    form.value = { ...form.value, ...newData }
  }
}, { immediate: true })

const edadCalculada = computed(() => {
  if (!form.value.fecha_nacimiento) return ''
  const hoy = new Date()
  const nacimiento = new Date(form.value.fecha_nacimiento)
  let edad = hoy.getFullYear() - nacimiento.getFullYear()
  const mes = hoy.getMonth() - nacimiento.getMonth()
  if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) edad--
  return edad > 0 ? `${edad} años` : 'Menor de 1 año'
})

const fotoUrl = ref('')
const fileInput = ref(null)

// La foto se guarda aparte: el File no viaja en el JSON. La subida se define después.
const fotoArchivo = ref(null)

const handleFotoChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    fotoArchivo.value = file
    const reader = new FileReader()
    reader.onload = (e) => { fotoUrl.value = e.target.result }
    reader.readAsDataURL(file)
  }
}

const fotoClick = () => {
  fileInput.value?.click()
}

defineExpose({ getFormData: () => form.value })
</script>

<style scoped>
.tab-section {
  padding: 10px;
}

.section-header {
  display: flex;
  align-items: center;
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 2px solid #e2e8f0;
}

.section-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
  margin-right: 15px;
  flex-shrink: 0;
}

.bg-primary { background: linear-gradient(135deg, #0061f2 0%, #00ba94 100%); }
.bg-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
.bg-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.bg-info { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }

.section-title h3 {
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 5px 0;
}

.section-title p {
  font-size: 14px;
  color: #64748b;
  margin: 0;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
  margin-bottom: 30px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
  color: #475569;
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-label i {
  color: #0061f2;
  font-size: 16px;
}

.required {
  color: #ef4444;
}

.input-wrapper {
  position: relative;
}

.form-input {
  width: 100%;
  padding: 12px 16px;
  font-size: 15px;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  background: #f8fafc;
  transition: all 0.3s ease;
  color: #334155;
}

.form-input:focus {
  outline: none;
  border-color: #0061f2;
  background: white;
  box-shadow: 0 0 0 4px rgba(0, 97, 242, 0.1);
}

.form-input.readonly {
  background: #f1f5f9;
  color: #64748b;
}

.radio-group {
  display: flex;
  gap: 15px;
}

.radio-option {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 10px 15px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.radio-option:hover {
  border-color: #0061f2;
  background: #f0f9ff;
}

.radio-option input[type="radio"] {
  accent-color: #0061f2;
}

.radio-label {
  font-size: 14px;
  font-weight: 500;
  color: #475569;
}

.file-upload-wrapper {
  display: flex;
  align-items: center;
  gap: 15px;
}

.btn-upload {
  background: linear-gradient(135deg, #0061f2 0%, #00ba94 100%);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-upload:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 97, 242, 0.3);
}

.foto-preview {
  width: 100px;
  height: 100px;
  border-radius: 10px;
  overflow: hidden;
  border: 2px solid #e2e8f0;
}

.foto-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.checkbox-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 15px;
  background: #f0f9ff;
  border-radius: 10px;
  border: 2px solid #e0f2fe;
}

.checkbox-input {
  width: 20px;
  height: 20px;
  accent-color: #0061f2;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #475569;
  cursor: pointer;
}

.checkbox-label i {
  color: #10b981;
  font-size: 18px;
}

.hidden {
  display: none;
}
</style>