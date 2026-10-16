<template>
  <div class="tab-section">
    <!-- Sección Tipo de Sangre -->
    <div class="section-header">
      <div class="section-icon bg-danger">
        <i class="fas fa-tint"></i>
      </div>
      <div class="section-title">
        <h3>Tipo de Sangre</h3>
        <p>Información hemática del paciente</p>
      </div>
    </div>

    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-tint"></i>
          Tipo de sangre
        </label>
        <div class="input-wrapper">
          <select v-model="form.tipo_sangre" class="form-input">
            <option value="">Seleccionar...</option>
            <option value="O">O</option>
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="AB">AB</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-plus-minus"></i>
          RH
        </label>
        <div class="input-wrapper">
          <select v-model="form.rh" class="form-input">
            <option value="">Seleccionar...</option>
            <option value="+">+</option>
            <option value="-">-</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Sección Alergias -->
    <div class="section-header">
      <div class="section-icon bg-warning">
        <i class="fas fa-allergies"></i>
      </div>
      <div class="section-title">
        <h3>Alergias</h3>
        <p>Reacciones alérgicas conocidas</p>
      </div>
    </div>

    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-exclamation-circle"></i>
          Alergias
        </label>
        <div class="input-wrapper">
          <textarea v-model="form.alergias" rows="2" class="form-input" placeholder="Ej: Penicilina, mariscos"></textarea>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-pills"></i>
          Alergia a medicamentos
        </label>
        <div class="input-wrapper">
          <textarea v-model="form.alergia_medicamentos" rows="2" class="form-input" placeholder="Ej: Amoxicilina"></textarea>
        </div>
      </div>
    </div>

    <!-- Sección Transfusiones -->
    <div class="section-header">
      <div class="section-icon bg-info">
        <i class="fas fa-hand-holding-medical"></i>
      </div>
      <div class="section-title">
        <h3>Transfusiones</h3>
        <p>Historial de transfusiones sanguíneas</p>
      </div>
    </div>

    <div class="form-grid">
      <div class="form-group full-width">
        <label class="form-label">
          <i class="fas fa-question-circle"></i>
          ¿Ha recibido transfusión sanguínea?
        </label>
        <div class="radio-group">
          <label class="radio-option">
            <!-- ✅ CORREGIDO: Usar :value para mantener el tipo booleano -->
            <input type="radio" v-model="form.transfusion_sanguinea" :value="true">
            <span class="radio-label">Sí</span>
          </label>
          <label class="radio-option">
            <input type="radio" v-model="form.transfusion_sanguinea" :value="false">
            <span class="radio-label">No</span>
          </label>
        </div>
      </div>

      <!-- ✅ CORREGIDO: Comparación con booleano true en lugar de string 'true' -->
      <div v-if="form.transfusion_sanguinea === true" class="form-group full-width">
        <label class="form-label">
          <i class="fas fa-info-circle"></i>
          Detalle de transfusión
        </label>
        <div class="input-wrapper">
          <input type="text" v-model="form.transfusion_detalle" class="form-input" placeholder="Ej: Hemotransfusión 2018">
        </div>
      </div>
    </div>

    <!-- Sección Antecedentes -->
    <div class="section-header">
      <div class="section-icon bg-success">
        <i class="fas fa-notes-medical"></i>
      </div>
      <div class="section-title">
        <h3>Antecedentes Médicos</h3>
        <p>Historial clínico del paciente</p>
      </div>
    </div>

    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-file-medical"></i>
          Antecedentes médicos
        </label>
        <div class="input-wrapper">
          <textarea v-model="form.antecedentes_medicos" rows="2" class="form-input" placeholder="Ej: Hipertensión, diabetes"></textarea>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-procedures"></i>
          Antecedentes quirúrgicos
        </label>
        <div class="input-wrapper">
          <textarea v-model="form.antecedentes_quirurgicos" rows="2" class="form-input" placeholder="Ej: Apendicectomía 2015"></textarea>
        </div>
      </div>

      <div class="form-group full-width">
        <label class="form-label">
          <i class="fas fa-heartbeat"></i>
          Enfermedades crónicas
        </label>
        <div class="input-wrapper">
          <textarea v-model="form.enfermedades_cronicas" rows="2" class="form-input" placeholder="Ej: Hipertensión arterial"></textarea>
        </div>
      </div>

      <div class="form-group full-width">
        <label class="form-label">
          <i class="fas fa-capsules"></i>
          Medicamentos actuales
        </label>
        <div class="input-wrapper">
          <textarea v-model="form.medicamentos_actuales" rows="2" class="form-input" placeholder="Ej: Lisinopril 20mg"></textarea>
        </div>
      </div>
    </div>

    <!-- Sección Última Consulta -->
    <div class="section-header">
      <div class="section-icon bg-primary">
        <i class="fas fa-calendar-check"></i>
      </div>
      <div class="section-title">
        <h3>Última Consulta Médica</h3>
        <p>Información de la consulta más reciente</p>
      </div>
    </div>

    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-calendar-day"></i>
          Fecha última consulta
        </label>
        <div class="input-wrapper">
          <input type="date" v-model="form.fecha_ultima_consulta_medica" class="form-input">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-stethoscope"></i>
          Motivo última consulta
        </label>
        <div class="input-wrapper">
          <input type="text" v-model="form.motivo_ultima_consulta_medica" class="form-input" placeholder="Motivo de la consulta">
        </div>
      </div>

      <div class="form-group full-width">
        <label class="form-label">
          <i class="fas fa-clipboard-list"></i>
          Notas generales
        </label>
        <div class="input-wrapper">
          <textarea v-model="form.notas_generales" rows="3" class="form-input" placeholder="Notas adicionales"></textarea>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  initialData: { type: Object, default: () => ({}) }
})

const form = ref({
  tipo_sangre: '', rh: '', alergias: '', alergia_medicamentos: '',
  transfusion_sanguinea: false, transfusion_detalle: '',
  antecedentes_medicos: '', antecedentes_quirurgicos: '',
  enfermedades_cronicas: '', medicamentos_actuales: '',
  fecha_ultima_consulta_medica: '', motivo_ultima_consulta_medica: '',
  notas_generales: ''
})

watch(() => props.initialData, (newData) => {
  if (newData && Object.keys(newData).length > 0) {
    form.value = { ...form.value, ...newData }
  }
}, { immediate: true })

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
}

.bg-primary { background: linear-gradient(135deg, #0061f2 0%, #00ba94 100%); }
.bg-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
.bg-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.bg-danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
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
  resize: vertical;
}

.form-input:focus {
  outline: none;
  border-color: #0061f2;
  background: white;
  box-shadow: 0 0 0 4px rgba(0, 97, 242, 0.1);
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
</style>