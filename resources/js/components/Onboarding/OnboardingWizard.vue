<template>
  <div class="onboarding">
    <!-- Stepper -->
    <div class="stepper" v-if="step > 0">
      <div class="stepper-track">
        <div class="stepper-fill" :style="{ width: fillWidth }"></div>
      </div>
      <div class="stepper-items">
        <div
          v-for="(s, i) in steps"
          :key="s.key"
          class="stepper-item"
          :class="{ 'is-active': step === i + 1, 'is-done': step > i + 1 }"
        >
          <div class="stepper-dot">
            <svg v-if="step > i + 1" viewBox="0 0 24 24" class="check-icon">
              <path d="M5 13l4 4L19 7" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <component :is="s.icon" v-else />
          </div>
          <span class="stepper-label">{{ s.label }}</span>
        </div>
      </div>
    </div>

    <transition name="slide" mode="out-in">
      <!-- Paso 0: Bienvenida -->
      <div v-if="step === 0" key="paso0" class="panel panel--welcome">
        <div class="welcome-icon">
          <svg viewBox="0 0 24 24" width="40" height="40">
            <path d="M12 2a4 4 0 100 8 4 4 0 000-8zM4 20a8 8 0 0116 0" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <h2>¡Bienvenido a Consultorio Online!</h2>
        <p>Antes de empezar, vamos a configurar los datos de tu empresa, tu primera sucursal, tu primer médico y su especialidad.</p>
        <button class="btn btn--primary" @click="step = 1">
          Comenzar
          <svg viewBox="0 0 24 24" width="18" height="18"><path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
      </div>

      <!-- Paso 1: Empresa -->
      <div v-else-if="step === 1" key="paso1" class="panel">
        <h4>Datos de la empresa</h4>

        <div class="field">
          <label>Nombre de la empresa</label>
          <input v-model="empresa.nombre_empresa" class="input" placeholder="Ej. Consultorio Online SA" required>
        </div>
        <div class="field">
          <label>Razón social</label>
          <input v-model="empresa.razon_social" class="input" placeholder="Ej. Consultorio Online S.A. de C.V." required>
        </div>
        <div class="field-row">
          <div class="field">
            <label>RFC</label>
            <input v-model="empresa.rfc" class="input" placeholder="Ej. COS010101AB1" required>
          </div>
          <div class="field">
            <label>Teléfono</label>
            <input v-model="empresa.telefono" class="input" placeholder="999 123 4567">
          </div>
        </div>
        <div class="field">
          <label>Email</label>
          <input v-model="empresa.email" type="email" class="input" placeholder="contacto@empresa.com">
        </div>
        <div class="field">
          <label>Dirección</label>
          <input v-model="empresa.direccion" class="input" placeholder="Calle, número, colonia, ciudad" required>
        </div>

        <div class="field">
          <label>Logo de la empresa</label>
          <label class="file-drop" :class="{ 'has-file': logoEmpresaNombre }">
            <input type="file" @change="onLogoEmpresaChange" accept=".jpg,.jpeg,.png,.webp" hidden>
            <svg viewBox="0 0 24 24" width="20" height="20">
              <path d="M12 16V4m0 0L7 9m5-5l5 5M5 20h14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>{{ logoEmpresaNombre || 'Seleccionar archivo' }}</span>
          </label>
        </div>

        <div class="actions">
          <button class="btn btn--ghost" @click="step = 0">Atrás</button>
          <button class="btn btn--primary" :disabled="!empresaValida" @click="step = 2">Siguiente</button>
        </div>
      </div>

      <!-- Paso 2: Ubicación -->
      <div v-else-if="step === 2" key="paso2" class="panel">
        <h4>Registrar sucursal</h4>

        <div class="field">
          <label>Nombre</label>
          <input v-model="ubicacion.nombre" class="input" placeholder="Ej. Consultorio Centro" required>
        </div>
        <div class="field">
          <label>Dirección</label>
          <input v-model="ubicacion.direccion" class="input" placeholder="Calle, número, colonia, ciudad" required>
        </div>
        <div class="field">
          <label>Teléfono</label>
          <input v-model="ubicacion.telefono" class="input" placeholder="999 123 4567">
        </div>

        <div class="field">
          <label>Logo de la sede</label>
          <label class="file-drop" :class="{ 'has-file': logoNombre }">
            <input type="file" @change="onLogoChange" accept=".jpg,.jpeg,.png,.webp" hidden>
            <svg viewBox="0 0 24 24" width="20" height="20">
              <path d="M12 16V4m0 0L7 9m5-5l5 5M5 20h14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>{{ logoNombre || 'Seleccionar archivo' }}</span>
          </label>
        </div>

        <div class="field-row">
          <div class="field">
            <label>Hora de apertura</label>
            <input v-model="ubicacion.horario_apertura" type="time" class="input" required>
          </div>
          <div class="field">
            <label>Hora de cierre</label>
            <input v-model="ubicacion.horario_cierre" type="time" class="input" required>
          </div>
        </div>

        <div class="actions">
          <button class="btn btn--ghost" @click="step = 1">Atrás</button>
          <button class="btn btn--primary" :disabled="!ubicacionValida" @click="step = 3">Siguiente</button>
        </div>
      </div>

      <!-- Paso 3: Médico -->
      <div v-else-if="step === 3" key="paso3" class="panel">
        <h4>Registrar nuevo médico</h4>

        <div class="field">
          <label>Nombre completo</label>
          <input v-model="medico.nombre" class="input" required>
        </div>
        <div class="field">
          <label>Cédula profesional</label>
          <input v-model="medico.cedula_profesional" class="input" required>
        </div>
        <div class="field">
          <label>Costo de consulta</label>
          <input v-model.number="medico.costo_consulta" type="number" step="0.01" min="0" class="input" required>
        </div>

        <div class="field">
          <label>Días de atención</label>
          <div class="dias-grid">
            <label
              v-for="dia in dias"
              :key="dia.valor"
              class="dia-chip"
              :class="{ 'is-selected': diasSeleccionados.includes(dia.valor) }"
            >
              <input type="checkbox" :value="dia.valor" v-model="diasSeleccionados" hidden>
              {{ dia.nombre }}
            </label>
          </div>
        </div>

        <div class="field-row field-row--three">
          <div class="field">
            <label>Hora de inicio</label>
            <input v-model="medico.hora_inicio" type="time" class="input" required>
          </div>
          <div class="field">
            <label>Hora de fin</label>
            <input v-model="medico.hora_fin" type="time" class="input" required>
          </div>
          <div class="field">
            <label>Duración (min)</label>
            <input v-model.number="medico.duracion_consulta" type="number" min="5" class="input" required>
          </div>
        </div>

        <div class="actions">
          <button class="btn btn--ghost" @click="step = 2">Atrás</button>
          <button class="btn btn--primary" :disabled="!medicoValido" @click="step = 4">Siguiente</button>
        </div>
      </div>

      <!-- Paso 4: Especialidad -->
      <div v-else-if="step === 4" key="paso4" class="panel">
        <h4>Registrar nueva especialidad</h4>

        <div class="field">
          <label>Nombre de la especialidad</label>
          <input v-model="especialidad.name" class="input" required>
        </div>
        <div class="field">
          <label>Descripción</label>
          <textarea v-model="especialidad.description" class="input input--textarea"></textarea>
        </div>

        <transition name="fade">
          <div v-if="error" class="banner banner--error">
            <svg viewBox="0 0 24 24" width="18" height="18"><path d="M12 9v4m0 4h.01M10.29 3.86l-8.18 14A2 2 0 004 21h16a2 2 0 001.89-3.14l-8.18-14a2 2 0 00-3.42 0z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span>{{ error }}</span>
          </div>
        </transition>

        <div class="actions">
          <button class="btn btn--ghost" :disabled="guardando" @click="step = 3">Atrás</button>
          <button class="btn btn--success" :disabled="!especialidadValida || guardando" @click="step = 5">
            Siguiente
          </button>
        </div>
      </div>
      
      <!-- Paso 5: Correo Electrónico (Notificaciones QR) -->
      <div v-else-if="step === 5" key="paso5" class="panel">
        <h4>Configurar correo de notificaciones</h4>
        <p class="panel-subtitle" style="font-size: 0.9rem; color: #64748b; margin-bottom: 20px;">
          Configura tu cuenta para enviar automáticamente el código QR a los pacientes cuando se registren.
        </p>

        <!-- Opción A: Conexión rápida (Recomendada para Médicos) -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; text-align: center; margin-bottom: 20px;">
          <svg viewBox="0 0 24 24" width="36" height="36" style="margin-bottom: 10px; color: #ea4335;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" fill="currentColor"/></svg>
          <h5 style="margin: 0 0 8px 0; font-size: 1rem; color: #1e293b;">Conexión inteligente</h5>
          <p style="font-size: 0.85krem; color: #64748b; margin-bottom: 15px;">Conecta tu cuenta institucional de Google de forma segura con un solo clic.</p>
          
          <!-- Botón simulado para la ruta de OAuth de Google -->
          <a href="/auth/google/redirect" class="btn btn--secondary" style="display: inline-flex; align-items: center; gap: 8px; background: #fff; border: 1px solid #cbd5e1; color: #334155; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 500;">
            <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/><path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.13 0-5.78-2.11-6.73-4.96H1.18v3.14C3.15 21.32 7.22 24 12 24z"/><path fill="#FBBC05" d="M5.27 14.24c-.25-.72-.38-1.49-.38-2.24s.13-1.52.38-2.24V6.62H1.18C.43 8.14 0 9.87 0 12s.43 3.86 1.18 5.38l4.09-3.14z"/><path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.22 0 3.15 2.68 1.18 6.62l4.09 3.14c.95-2.85 3.6-4.96 6.73-4.96z"/></svg>
            Conectar con Google
          </a>
        </div>

        <!-- Mensaje de error si aplica -->
        <transition name="fade">
          <div v-if="error" class="banner banner--error" style="margin-bottom: 15px;">
            <svg viewBox="0 0 24 24" width="18" height="18"><path d="M12 9v4m0 4h.01M10.29 3.86l-8.18 14A2 2 0 004 21h16a2 2 0 001.89-3.14l-8.18-14a2 2 0 00-3.42 0z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span>{{ error }}</span>
          </div>
        </transition>

        <div class="actions">
          <button class="btn btn--ghost" :disabled="guardando" @click="step = 4">Atrás</button>
          
          <!-- Botón para finalizar guardando todo (ahora pasa del paso 4 al final) -->
          <button class="btn btn--success" :disabled="guardando" @click="guardarTodo">
            <span v-if="guardando" class="spinner"></span>
            {{ guardando ? 'Guardando...' : 'Finalizar y Guardar' }}
          </button>
        </div>
      </div>

    </transition>

    <!-- Toast de notificación -->
    <transition name="toast">
      <div v-if="toast.visible" class="toast" :class="`toast--${toast.type}`">
        <svg v-if="toast.type === 'success'" viewBox="0 0 24 24" width="20" height="20"><path d="M5 13l4 4L19 7" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <svg v-else viewBox="0 0 24 24" width="20" height="20"><path d="M12 9v4m0 4h.01M10.29 3.86l-8.18 14A2 2 0 004 21h16a2 2 0 001.89-3.14l-8.18-14a2 2 0 00-3.42 0z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span>{{ toast.message }}</span>
      </div>
    </transition>
  </div>
</template>

<script>
export default {
  name: 'OnboardingWizard',
  components: {
    IconEmpresa: {
      template: `<svg viewBox="0 0 24 24" width="16" height="16"><path d="M3 21h18M6 21V4a1 1 0 011-1h6a1 1 0 011 1v17M9 8h.01M9 12h.01M9 16h.01M13 8h.01M13 12h.01M13 16h.01M18 21v-8a1 1 0 00-1-1h-3v9" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
    },
    IconSucursal: {
      template: `<svg viewBox="0 0 24 24" width="16" height="16"><path d="M4 21V7l8-4 8 4v14M9 21v-6h6v6M4 21h16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
    },
    IconMedico: {
      template: `<svg viewBox="0 0 24 24" width="16" height="16"><path d="M9 3v5a3 3 0 006 0V3M9 3H7v6a5 5 0 0010 0V3h-2M12 14v7m5-3.5a5 5 0 01-10 0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
    },
    IconEspecialidad: {
      template: `<svg viewBox="0 0 24 24" width="16" height="16"><path d="M4 6h16M4 12h16M4 18h9" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
    },
    IconCorreo: {
      template: `<svg viewBox="0 0 24 24" width="16" height="16"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 6l-10 7L2 6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
    },
  },
  data() {
    return {
      step: 0,
      guardando: false,
      error: null,

      toast: {
        visible: false,
        type: 'success',
        message: '',
      },

      steps: [
        { key: 'empresa', label: 'Empresa', icon: 'IconEmpresa' },
        { key: 'sucursal', label: 'Sucursal', icon: 'IconSucursal' },
        { key: 'medico', label: 'Médico', icon: 'IconMedico' },
        { key: 'especialidad', label: 'Especialidad', icon: 'IconEspecialidad' },
        { key: 'correo', label: 'Correo', icon: 'IconCorreo' },
      ],

      dias: [
        { valor: 1, nombre: 'Lun' },
        { valor: 2, nombre: 'Mar' },
        { valor: 3, nombre: 'Mié' },
        { valor: 4, nombre: 'Jue' },
        { valor: 5, nombre: 'Vie' },
        { valor: 6, nombre: 'Sáb' },
        { valor: 7, nombre: 'Dom' },
      ],
      diasSeleccionados: [],

      empresa: {
        nombre_empresa: '',
        razon_social: '',
        rfc: '',
        telefono: '',
        email: '',
        direccion: '',
        logo: null,
      },
      logoEmpresaNombre: '',

      ubicacion: {
        nombre: '',
        direccion: '',
        telefono: '',
        horario_apertura: '',
        horario_cierre: '',
        logo: null,
      },
      logoNombre: '',

      medico: {
        nombre: '',
        cedula_profesional: '',
        costo_consulta: null,
        hora_inicio: '',
        hora_fin: '',
        duracion_consulta: 30,
      },

      especialidad: {
        name: '',
        description: '',
      },
      correo:{
        tipo: 'oauth', // o 'smtp'
        mail_host: '',
        mail_port: 587,
        mail_username: '',
        mail_password: '',
        mail_encryption: 'tls',
      },
    };
  },
  computed: {
    fillWidth() {
      const pct = ((this.step - 1) / (this.steps.length - 1)) * 100;
      return `${Math.max(0, Math.min(100, pct))}%`;
    },
    empresaValida() {
      return this.empresa.nombre_empresa && this.empresa.razon_social
        && this.empresa.rfc && this.empresa.direccion;
    },
    ubicacionValida() {
      return this.ubicacion.nombre && this.ubicacion.direccion
        && this.ubicacion.horario_apertura && this.ubicacion.horario_cierre;
    },
    medicoValido() {
      return this.medico.nombre && this.medico.cedula_profesional
        && this.medico.costo_consulta !== null
        && this.diasSeleccionados.length > 0
        && this.medico.hora_inicio && this.medico.hora_fin
        && this.medico.duracion_consulta;
    },
    especialidadValida() {
      return !!this.especialidad.name;
    },
  },
  methods: {
    onLogoEmpresaChange(e) {
      const archivo = e.target.files[0] || null;
      this.empresa.logo = archivo;
      this.logoEmpresaNombre = archivo ? archivo.name : '';
    },
    onLogoChange(e) {
      const archivo = e.target.files[0] || null;
      this.ubicacion.logo = archivo;
      this.logoNombre = archivo ? archivo.name : '';
    },
    mostrarToast(message, type = 'success') {
      this.toast = { visible: true, type, message };
      setTimeout(() => {
        this.toast.visible = false;
      }, 3200);
    },
    async guardarTodo() {
      this.guardando = true;
      this.error = null;

      const horarios = this.diasSeleccionados.map((dia) => ({
        dia_semana: dia,
        hora_inicio: this.medico.hora_inicio,
        hora_fin: this.medico.hora_fin,
        duracion_consulta: this.medico.duracion_consulta,
      }));

      const formData = new FormData();
      formData.append('correo[tipo]', this.correo.tipo);
      formData.append('correo[mail_username]', this.correo.mail_username || '');
      formData.append('empresa[nombre_empresa]', this.empresa.nombre_empresa);
      formData.append('empresa[razon_social]', this.empresa.razon_social);
      formData.append('empresa[rfc]', this.empresa.rfc);
      formData.append('empresa[telefono]', this.empresa.telefono || '');
      formData.append('empresa[email]', this.empresa.email || '');
      formData.append('empresa[direccion]', this.empresa.direccion);
      if (this.empresa.logo) {
        formData.append('empresa[logo]', this.empresa.logo);
      }

      formData.append('ubicacion[nombre]', this.ubicacion.nombre);
      formData.append('ubicacion[direccion]', this.ubicacion.direccion);
      formData.append('ubicacion[telefono]', this.ubicacion.telefono || '');
      formData.append('ubicacion[horario_apertura]', this.ubicacion.horario_apertura);
      formData.append('ubicacion[horario_cierre]', this.ubicacion.horario_cierre);
      if (this.ubicacion.logo) {
        formData.append('ubicacion[logo]', this.ubicacion.logo);
      }

      formData.append('medico[nombre]', this.medico.nombre);
      formData.append('medico[cedula_profesional]', this.medico.cedula_profesional);
      formData.append('medico[costo_consulta]', this.medico.costo_consulta);
      horarios.forEach((h, i) => {
        formData.append(`medico[horarios][${i}][dia_semana]`, h.dia_semana);
        formData.append(`medico[horarios][${i}][hora_inicio]`, h.hora_inicio);
        formData.append(`medico[horarios][${i}][hora_fin]`, h.hora_fin);
        formData.append(`medico[horarios][${i}][duracion_consulta]`, h.duracion_consulta);
      });

      formData.append('especialidad[name]', this.especialidad.name);
      formData.append('especialidad[description]', this.especialidad.description || '');

      try {
        const response = await window.axios.post('/onboarding/completar', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });
        this.mostrarToast('Configuración guardada correctamente', 'success');
        setTimeout(() => {
          window.location.href = response.data.redirect || '/dashboard';
        }, 900);
      } catch (e) {
        const msg = e.response?.data?.message || 'Ocurrió un error al guardar. Revisa los datos.';
        this.error = msg;
        this.mostrarToast(msg, 'error');
      } finally {
        this.guardando = false;
      }
    },
  },
};
</script>

<style scoped>
.onboarding {
  --teal-900: #0b4f4a;
  --teal-700: #0f766e;
  --teal-500: #14b8a6;
  --teal-100: #e6f7f5;
  --ink-900: #16221f;
  --ink-500: #5b6b68;
  --paper: #ffffff;
  --canvas: #f4faf9;
  --border: #e1ece9;
  --error-700: #b3261e;
  --error-100: #fdecec;

  max-width: 640px;
  margin: 0 auto;
  padding: 8px 4px 32px;
  font-family: -apple-system, "Segoe UI", Roboto, sans-serif;
  color: var(--ink-900);
  position: relative;
}

/* ---------- Stepper ---------- */
.stepper { margin-bottom: 28px; }
.stepper-track {
  position: relative;
  height: 3px;
  background: var(--border);
  border-radius: 4px;
  margin: 0 22px 14px;
}
.stepper-fill {
  position: absolute;
  top: 0; left: 0;
  height: 100%;
  background: linear-gradient(90deg, var(--teal-700), var(--teal-500));
  border-radius: 4px;
  transition: width 0.45s cubic-bezier(.65,0,.35,1);
}
.stepper-items { display: flex; justify-content: space-between; }
.stepper-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  flex: 1;
}
.stepper-dot {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--paper);
  border: 2px solid var(--border);
  color: var(--ink-500);
  transition: all 0.3s ease;
}
.stepper-item.is-active .stepper-dot {
  border-color: var(--teal-700);
  color: var(--teal-700);
  box-shadow: 0 0 0 4px var(--teal-100);
  transform: scale(1.08);
}
.stepper-item.is-done .stepper-dot {
  background: var(--teal-700);
  border-color: var(--teal-700);
  color: #fff;
}
.check-icon { width: 16px; height: 16px; }
.stepper-label {
  font-size: 12px;
  font-weight: 600;
  color: var(--ink-500);
  letter-spacing: 0.2px;
}
.stepper-item.is-active .stepper-label,
.stepper-item.is-done .stepper-label { color: var(--teal-900); }

/* ---------- Panels ---------- */
.panel {
  background: var(--paper);
  border: 1px solid var(--border);
  border-radius: 18px;
  padding: 28px 26px 24px;
  box-shadow: 0 1px 2px rgba(16, 40, 34, 0.03), 0 10px 30px -14px rgba(16, 40, 34, 0.12);
}
.panel h4 {
  margin: 0 0 20px;
  font-size: 18px;
  font-weight: 700;
  color: var(--teal-900);
}
.panel--welcome { text-align: center; padding: 44px 30px; }
.welcome-icon {
  width: 72px;
  height: 72px;
  margin: 0 auto 18px;
  border-radius: 50%;
  background: var(--teal-100);
  color: var(--teal-700);
  display: flex;
  align-items: center;
  justify-content: center;
  animation: pop 0.5s cubic-bezier(.34,1.56,.64,1);
}
.panel--welcome h2 { margin: 0 0 10px; font-size: 22px; color: var(--teal-900); }
.panel--welcome p { color: var(--ink-500); margin: 0 0 24px; line-height: 1.5; }
@keyframes pop {
  0% { transform: scale(0.6); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}

/* ---------- Fields ---------- */
.field { margin-bottom: 16px; }
.field label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: var(--ink-900);
  margin-bottom: 6px;
}
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.field-row--three { grid-template-columns: 1fr 1fr 1fr; }
.input {
  width: 100%;
  box-sizing: border-box;
  padding: 11px 14px;
  border-radius: 10px;
  border: 1.5px solid var(--border);
  background: var(--canvas);
  font-size: 14px;
  color: var(--ink-900);
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}
.input:focus {
  outline: none;
  border-color: var(--teal-500);
  background: var(--paper);
  box-shadow: 0 0 0 4px var(--teal-100);
}
.input--textarea { min-height: 90px; resize: vertical; }

/* File input */
.file-drop {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 11px 14px;
  border-radius: 10px;
  border: 1.5px dashed var(--border);
  background: var(--canvas);
  color: var(--ink-500);
  font-size: 13.5px;
  cursor: pointer;
  transition: border-color 0.2s ease, color 0.2s ease, background 0.2s ease;
}
.file-drop:hover { border-color: var(--teal-500); color: var(--teal-700); }
.file-drop.has-file {
  border-style: solid;
  border-color: var(--teal-500);
  color: var(--teal-900);
  background: var(--teal-100);
  font-weight: 600;
}

/* Días chips */
.dias-grid { display: flex; flex-wrap: wrap; gap: 8px; }
.dia-chip {
  padding: 8px 14px;
  border-radius: 999px;
  border: 1.5px solid var(--border);
  background: var(--canvas);
  font-size: 13px;
  font-weight: 600;
  color: var(--ink-500);
  cursor: pointer;
  user-select: none;
  transition: all 0.18s ease;
}
.dia-chip:hover { border-color: var(--teal-500); }
.dia-chip.is-selected {
  background: var(--teal-700);
  border-color: var(--teal-700);
  color: #fff;
  transform: translateY(-1px);
}

/* ---------- Buttons ---------- */
.actions { display: flex; justify-content: space-between; margin-top: 24px; }
.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 11px 22px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 700;
  border: none;
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.2s ease, background 0.2s ease, opacity 0.2s ease;
}
.btn:active { transform: scale(0.97); }
.btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
.btn--primary {
  background: linear-gradient(135deg, var(--teal-700), var(--teal-500));
  color: #fff;
  box-shadow: 0 6px 16px -6px rgba(15, 118, 110, 0.55);
}
.btn--primary:not(:disabled):hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 22px -8px rgba(15, 118, 110, 0.6);
}
.btn--success {
  background: linear-gradient(135deg, #147a52, #22c55e);
  color: #fff;
  box-shadow: 0 6px 16px -6px rgba(20, 122, 82, 0.5);
}
.btn--success:not(:disabled):hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 22px -8px rgba(20, 122, 82, 0.55);
}
.btn--ghost { background: transparent; color: var(--ink-500); border: 1.5px solid var(--border); }
.btn--ghost:hover { border-color: var(--ink-500); color: var(--ink-900); }

.spinner {
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ---------- Banner (error inline) ---------- */
.banner {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 12px 14px;
  border-radius: 10px;
  font-size: 13.5px;
  margin-top: 6px;
}
.banner--error { background: var(--error-100); color: var(--error-700); }

/* ---------- Toast ---------- */
.toast {
  position: fixed;
  bottom: 28px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 13px 20px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  box-shadow: 0 12px 30px -10px rgba(0,0,0,0.3);
  z-index: 100;
}
.toast--success { background: #103f2e; color: #d7f7e6; }
.toast--error { background: #4a1414; color: #ffdada; }

/* ---------- Transitions ---------- */
.slide-enter-active, .slide-leave-active {
  transition: opacity 0.25s ease, transform 0.3s cubic-bezier(.4,0,.2,1);
}
.slide-enter-from { opacity: 0; transform: translateX(16px); }
.slide-leave-to { opacity: 0; transform: translateX(-16px); }

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.toast-enter-active { transition: all 0.35s cubic-bezier(.34,1.56,.64,1); }
.toast-leave-active { transition: all 0.25s ease; }
.toast-enter-from { opacity: 0; transform: translate(-50%, 16px); }
.toast-leave-to { opacity: 0; transform: translate(-50%, 10px); }

/* ---------- Responsive ---------- */
@media (max-width: 480px) {
  .field-row, .field-row--three { grid-template-columns: 1fr; }
  .panel { padding: 22px 18px; }
}
</style>