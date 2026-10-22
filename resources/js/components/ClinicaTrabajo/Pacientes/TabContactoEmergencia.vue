<template>
  <div class="tab-section">
    <!-- Sección Contacto Principal -->
    <div class="section-header">
      <div class="section-icon bg-primary">
        <i class="fas fa-phone-alt"></i>
      </div>
      <div class="section-title">
        <h3>Información de Contacto</h3>
        <p>Datos para comunicarse con el paciente</p>
      </div>
    </div>

    <div class="form-grid-3">
      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-phone"></i>
          Teléfono
        </label>
        <div class="input-wrapper">
          <input type="tel" v-model="form.telefono" class="form-input" placeholder="Ej: 555-123-4567">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-mobile-alt"></i>
          Celular
        </label>
        <div class="input-wrapper">
          <input type="tel" v-model="form.celular" class="form-input" placeholder="Ej: 555-123-4567">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">
          <i class="fab fa-whatsapp"></i>
          WhatsApp ID
        </label>
        <div class="input-wrapper">
          <input type="text" v-model="form.whatsapp_id" class="form-input" placeholder="ID o número WhatsApp">
        </div>
      </div>

      <!-- ✅ Email en 1 columna -->
      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-envelope"></i>
          Email
        </label>
        <div class="input-wrapper">
          <input type="email" v-model="form.email" class="form-input" placeholder="correo@ejemplo.com">
        </div>
      </div>

      <!-- ✅ Dirección en 2 columnas -->
      <div class="form-group col-span-2">
        <label class="form-label">
          <i class="fas fa-map-marker-alt"></i>
          Dirección
        </label>
        <div class="input-wrapper">
          <textarea v-model="form.direccion" rows="3" class="form-input" placeholder="Calle, número, colonia, ciudad"></textarea>
        </div>
      </div>
    </div>

    <!-- Sección Contacto de Emergencia -->
    <div class="section-header">
      <div class="section-icon bg-danger">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      <div class="section-title">
        <h3>Contacto de Emergencia</h3>
        <p>Persona a contactar en caso de emergencia</p>
      </div>
    </div>

    <div class="form-grid-3">
      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-user"></i>
          Nombre completo
        </label>
        <div class="input-wrapper">
          <input type="text" v-model="form.contacto_emergencia" class="form-input" placeholder="Nombre completo">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-users"></i>
          Relación
        </label>
        <div class="input-wrapper">
          <input type="text" v-model="form.relacion_contacto_emergencia" class="form-input" placeholder="Ej: Esposo, Madre">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-phone-alt"></i>
          Tel. de emergencia
        </label>
        <div class="input-wrapper">
          <input type="tel" v-model="form.telefono_emergencia" class="form-input" placeholder="Ej: 555-123-4567">
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

// Solo los campos que este tab edita
const CAMPOS = [
  'telefono', 'celular', 'whatsapp_id', 'email', 'direccion',
  'contacto_emergencia', 'relacion_contacto_emergencia', 'telefono_emergencia'
]

const form = ref({
  telefono: '', celular: '', email: '', direccion: '',
  contacto_emergencia: '', relacion_contacto_emergencia: '',
  telefono_emergencia: '', whatsapp_id: ''
})

watch(() => props.initialData, (newData) => {
  if (!newData || Object.keys(newData).length === 0) return
  CAMPOS.forEach(campo => {
    if (newData[campo] !== undefined) form.value[campo] = newData[campo]
  })
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
  flex-shrink: 0;
}

.bg-primary { background: linear-gradient(135deg, #0061f2 0%, #00ba94 100%); }
.bg-danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }

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

/* Grid de 3 columnas */
.form-grid-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 30px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group.col-span-2 {
  grid-column: span 2;
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

/* Responsive */
@media (max-width: 1024px) {
  .form-grid-3 {
    grid-template-columns: repeat(2, 1fr);
  }
  .form-group.col-span-2 {
    grid-column: 1 / -1;
  }
}

@media (max-width: 640px) {
  .form-grid-3 {
    grid-template-columns: 1fr;
  }
  .form-group.col-span-2 {
    grid-column: 1;
  }
}
</style>