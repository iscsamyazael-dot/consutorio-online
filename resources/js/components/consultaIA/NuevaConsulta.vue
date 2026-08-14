<template>
  <div class="container-fluid content-container">
    <div class="row g-4">

      <div class="col-12">
        <div class="premium-header">
          <div>
            <h1>
              <i class="fas fa-heartbeat heartbeat-icon"></i>
              Nueva Consulta Médica
            </h1>
            <p>Plataforma clínica inteligente • Sistema Hospitalario Premium</p>
          </div>
          <div class="live-status">
            <span class="pulse-dot"></span>
            Consulta Activa
          </div>
        </div>
      </div>

      <div class="col-xl-4">
        <div class="glass-card profile-card">
          <div class="profile-bg"></div>
          <div class="card-body text-center position-relative">
            <div class="patient-avatar mx-auto">
              <i class="fas fa-user-injured"></i>
            </div>
            <h3 class="mt-4 fw-bold">Consulta General</h3>
            <p class="profile-subtitle">
              Registro médico inteligente y monitoreo clínico
            </p>

            <div class="status-container">
              <div class="status-item">
                <div class="status-icon green">
                  <i class="fas fa-check"></i>
                </div>
                <div class="text-start">
                  <span>Estado</span>
                  <h6>Consulta activa</h6>
                </div>
              </div>
              <div class="status-item">
                <div class="status-icon orange">
                  <i class="fas fa-exclamation"></i>
                </div>
                <div class="text-start">
                  <span>Prioridad</span>
                  <h6>Moderada</h6>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="glass-card info-card mt-4">
          <div class="card-body">
            <div class="info-title">
              <i class="fas fa-chart-line"></i>
              Información Clínica
            </div>
            <div class="info-box">
              <div class="info-item">
                <div class="info-icon blue">
                  <i class="fas fa-user-md"></i>
                </div>
                <div>
                  <small>Médico</small>
                  <h6>Dr. Martínez</h6>
                </div>
              </div>
              <div class="info-item">
                <div class="info-icon green">
                  <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                  <small>Fecha</small>
                  <h6>{{ fechaActual }}</h6>
                </div>
              </div>
              <div class="info-item">
                <div class="info-icon red">
                  <i class="fas fa-heartbeat"></i>
                </div>
                <div>
                  <small>Estado</small>
                  <h6>En evaluación</h6>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-8">
        <div class="glass-card form-card">
          <div class="card-body p-5">
            <form @submit.prevent="guardarConsulta">
              
              <div class="section-header">
                <div>
                  <h3>
                    <i class="fas fa-notes-medical"></i>
                    Información del Paciente
                  </h3>
                  <p>Complete los datos clínicos de la consulta</p>
                </div>
              </div>

              <div class="row g-4 mt-2">
                <div class="col-md-6">
                  <label class="form-label">Paciente</label>
                  <div class="input-wrapper">
                    <i class="fas fa-user input-icon"></i>
                    <input 
                      type="text" 
                      v-model="form.paciente"
                      class="form-control premium-input"
                      placeholder="Nombre del paciente"
                    >
                  </div>
                </div>

                <div class="col-md-3">
                  <label class="form-label">Sexo</label>
                  <div class="input-wrapper">
                    <i class="fas fa-venus-mars input-icon"></i>
                    <select 
                      v-model="form.sexo" 
                      class="form-control premium-input select-custom"
                    >
                      <option value="" disabled>Seleccionar...</option>
                      <option value="Masculino">Masculino</option>
                      <option value="Femenino">Femenino</option>
                      <option value="Otro">Otro</option>
                    </select>
                  </div>
                </div>

                <div class="col-md-3">
                  <label class="form-label">Edad</label>
                  <div class="input-wrapper">
                    <i class="fas fa-baby-carriage input-icon"></i>
                    <input 
                      type="number" 
                      v-model.number="form.edad" 
                      min="0"
                      class="form-control premium-input"
                      placeholder="Ej. 25"
                    >
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Fecha de Nacimiento</label>
                  <div class="input-wrapper">
                    <i class="fas fa-birthday-cake input-icon"></i>
                    <input 
                      type="date" 
                      v-model="form.fecha_nacimiento"
                      class="form-control premium-input"
                    >
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Número de Teléfono</label>
                  <div class="input-wrapper">
                    <i class="fas fa-phone input-icon"></i>
                    <input 
                      type="tel" 
                      v-model="form.telefono"
                      class="form-control premium-input"
                      placeholder="9999999999"
                    >
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Dirección</label>
                  <div class="input-wrapper">
                    <i class="fas fa-map-marker-alt input-icon"></i>
                    <input 
                      type="text" 
                      v-model="form.direccion"
                      class="form-control premium-input"
                      placeholder="Calle, Número, Colonia"
                    >
                  </div>
                </div>
              </div>

              <div class="premium-divider"></div>

              <div class="section-header">
                <div>
                  <h3>
                    <i class="fas fa-file-medical-alt text-danger"></i>
                    Evaluación Clínica
                  </h3>
                  <p>Información médica detallada del paciente</p>
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label">Síntomas</label>
                <textarea 
                  v-model="form.sintomas"
                  class="form-control premium-textarea"
                  rows="4"
                  placeholder="Ingrese síntomas del paciente"
                ></textarea>
              </div>

              <div class="mb-4">
                <label class="form-label">Diagnóstico</label>
                <textarea 
                  v-model="form.diagnostico"
                  class="form-control premium-textarea"
                  rows="4"
                  placeholder="Diagnóstico médico"
                ></textarea>
              </div>

              <div class="mb-4">
                <label class="form-label">Tratamiento</label>
                <textarea 
                  v-model="form.tratamiento"
                  class="form-control premium-textarea"
                  rows="4"
                  placeholder="Tratamiento médico"
                ></textarea>
              </div>

              <div class="d-flex justify-content-end gap-3 mt-5 flex-wrap">
                <button type="button" @click="cancelar" class="btn cancel-btn">
                  Cancelar
                </button>
                <button type="submit" class="btn save-btn">
                  <i class="fas fa-save me-2"></i>
                  Guardar Consulta
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// Estado reactivo para el formulario
const form = ref({
  paciente: '',
  sexo: '',
  edad: null,
  fecha_nacimiento: '',
  telefono: '',
  direccion: '',
  sintomas: '',
  diagnostico: '',
  tratamiento: ''
});

// Fecha dinámica para mostrar en la tarjeta lateral
const fechaActual = ref(new Date().toLocaleDateString('es-ES', {
  day: 'numeric',
  month: 'long',
  year: 'numeric'
}));

// Función para manejar el envío de la consulta
const guardarConsulta = () => {
  console.log('Datos enviados de la consulta:', form.value);
  // Aquí puedes integrar tu llamada API con Axios o el submit de Inertia:
  // router.post('/consultas', form.value);
};

// Función para limpiar o salir
const cancelar = () => {
  if (confirm('¿Seguro que deseas cancelar? Los cambios se perderán.')) {
    form.value = {
      paciente: '', sexo: '', edad: null, fecha_nacimiento: '',
      telefono: '', direccion: '', sintomas: '', diagnostico: '', tratamiento: ''
    };
  }
};
</script>

<style scoped>
/* =================================================== */
/* COMPORTAMIENTO GENERAL Y CONTENEDOR */
/* =================================================== */
.content-container {
  padding-top: 15px;
}

/* =================================================== */
/* HEADER PRINCIPAL */
/* =================================================== */
.premium-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  margin-bottom: 15px;
}

.premium-header h1 {
  font-size: 38px;
  font-weight: 900;
  color: #111827;
  margin-bottom: 8px;
}

.premium-header p {
  color: #6b7280;
  font-size: 15px;
}

/* =================================================== */
/* ESTADOS Y PULSOS */
/* =================================================== */
.live-status {
  background: white;
  padding: 14px 22px;
  border-radius: 50px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 10px 30px rgba(0,0,0,.08);
}

.pulse-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #22c55e;
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.4); opacity: .5; }
  100% { transform: scale(1); opacity: 1; }
}

/* =================================================== */
/* GLASS CARD (TARJETAS PREMIUM) */
/* =================================================== */
.glass-card {
  background: rgba(255,255,255,.72);
  backdrop-filter: blur(18px);
  border-radius: 35px;
  border: 1px solid rgba(255,255,255,.5);
  box-shadow: 0 20px 50px rgba(0,0,0,.08);
  overflow: hidden;
  transition: .4s;
}

.glass-card:hover {
  transform: translateY(-5px);
}

/* Tarjeta Perfil */
.profile-card {
  position: relative;
}

.profile-bg {
  height: 160px;
  background: linear-gradient(135deg, #2563eb, #38bdf8);
}

/* Avatar Paciente */
.patient-avatar {
  width: 130px;
  height: 130px;
  border-radius: 35px;
  background: white;
  margin-top: -65px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 55px;
  color: #2563eb;
  box-shadow: 0 20px 40px rgba(37,99,235,.25);
  position: relative;
  z-index: 1;
}

.profile-subtitle {
  color: #6b7280;
  margin-top: 10px;
}

/* Contenedores de Estado Internos */
.status-container {
  margin-top: 30px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.status-item {
  background: #f8fafc;
  border-radius: 22px;
  padding: 18px;
  display: flex;
  align-items: center;
  gap: 16px;
}

.status-icon {
  width: 50px;
  height: 50px;
  border-radius: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.green { background: #22c55e; }
.orange { background: #f59e0b; }

/* =================================================== */
/* SECCIÓN INFORMACIÓN CLÍNICA (SIDEBAR) */
/* =================================================== */
.info-title {
  font-size: 22px;
  font-weight: 800;
  margin-bottom: 25px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.info-box {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 15px;
}

.info-icon {
  width: 55px;
  height: 55px;
  border-radius: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 20px;
  flex-shrink: 0;
}

.blue { background: #2563eb; }
.red { background: #ef4444; }

/* =================================================== */
/* CABECERAS DE SECCIÓN EN FORMULARIO */
/* =================================================== */
.section-header {
  margin-bottom: 25px;
}

.section-header h3 {
  font-weight: 900;
  color: #111827;
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 0;
}

.section-header p {
  color: #6b7280;
  margin-top: 8px;
  margin-bottom: 0;
}

/* =================================================== */
/* CAMPOS DE ENTRADA (INPUTS / SELECTS / TEXTAREAS) */
/* =================================================== */
.form-label {
  font-weight: 700;
  color: #374151;
  margin-bottom: 12px;
}

.input-wrapper {
  position: relative;
}

.input-icon {
  position: absolute;
  top: 50%;
  left: 20px;
  transform: translateY(-50%);
  color: #9ca3af;
  z-index: 2;
}

.premium-input {
  height: 62px;
  border: none;
  border-radius: 20px;
  padding-left: 55px;
  background: #f8fafc;
  box-shadow: inset 0 0 0 1px #e5e7eb;
  transition: .3s;
}

.premium-input:focus {
  background: white;
  box-shadow: 0 0 0 5px rgba(37,99,235,.12);
  outline: none;
}

.select-custom {
  padding-right: 15px;
}

.premium-textarea {
  border: none;
  border-radius: 25px;
  padding: 22px;
  background: #f8fafc;
  resize: none;
  box-shadow: inset 0 0 0 1px #e5e7eb;
  transition: .3s;
}

.premium-textarea:focus {
  background: white;
  box-shadow: 0 0 0 5px rgba(37,99,235,.12);
  outline: none;
}

/* Línea de separación Premium */
.premium-divider {
  height: 2px;
  margin: 45px 0;
  background: linear-gradient(to right, transparent, #2563eb, transparent);
  opacity: .2;
}

/* =================================================== */
/* BOTONES ACCIÓN */
/* =================================================== */
.btn {
  border: none;
  border-radius: 18px;
  padding: 16px 28px;
  font-weight: 700;
  transition: .3s;
}

.btn:hover {
  transform: translateY(-4px);
}

.cancel-btn {
  background: white;
  color: #374151;
  box-shadow: 0 10px 20px rgba(0,0,0,.06);
}

.save-btn {
  background: linear-gradient(135deg, #2563eb, #38bdf8);
  color: white;
  box-shadow: 0 15px 30px rgba(37,99,235,.25);
}

/* Animación del Corazón Clínico */
.heartbeat-icon {
  color: #2563eb;
  animation: heartbeat 1.5s infinite;
}

@keyframes heartbeat {
  0% { transform: scale(1); }
  25% { transform: scale(1.12); }
  50% { transform: scale(1); }
  75% { transform: scale(1.12); }
  100% { transform: scale(1); }
}
</style>