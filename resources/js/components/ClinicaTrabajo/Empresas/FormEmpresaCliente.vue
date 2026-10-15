<template>
  <div class="form-container">
    <!-- Header del formulario -->
    <div class="form-header">
      <div class="row align-items-center">
        <div class="col-auto">
          <div class="d-flex align-items-center">
            <div class="form-header-icon">
              <i class="fas fa-hospital-alt"></i>
            </div>
            <div class="ml-3">
              <h2 class="mb-1 font-weight-bold">
                {{ empresa === null ? 'Registrar Nueva Empresa' : 'Editar Empresa' }}
              </h2>
              <p class="text-muted mb-0 small">
                {{ empresa === null ? 'Complete los datos de la nueva empresa cliente' : 'Modifique los datos de la empresa' }}
              </p>
            </div>
          </div>
        </div>
        <div class="col"></div>
        <div class="col-auto">
          <button @click="$emit('cancelar')" class="btn btn-outline-secondary btn-sm rounded-pill px-4">
            <i class="fas fa-arrow-left mr-2"></i>
            Regresar
          </button>
        </div>
      </div>
    </div>

    <form @submit.prevent="guardarEmpresa">
      <div class="row">
        <!-- COLUMNA IZQUIERDA - Formulario -->
        <div class="col-lg-8">
          <!-- DATOS GENERALES -->
          <div class="modern-card mb-4">
            <div class="card-header-gradient bg-primary-gradient">
              <div class="d-flex align-items-center">
                <div class="card-icon bg-white text-primary">
                  <i class="fas fa-building"></i>
                </div>
                <h5 class="mb-0 text-white font-weight-bold ml-3">Datos Generales</h5>
              </div>
            </div>
            <div class="card-body-custom">
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label class="modern-label">
                    Razón Social <span class="text-danger">*</span>
                  </label>
                  <div class="input-modern">
                    <i class="fas fa-building input-icon"></i>
                    <input
                      type="text"
                      class="form-input"
                      placeholder="Ej. Clínica Médica del Norte S.A. de C.V."
                      v-model="form.razon_social"
                      required
                    >
                  </div>
                </div>
                
                <div class="col-md-6 mb-3">
                  <label class="modern-label">RFC</label>
                  <div class="input-modern">
                    <i class="fas fa-file-invoice input-icon"></i>
                    <input
                      type="text"
                      class="form-input"
                      placeholder="Ej. AAA010101AAA"
                      v-model="form.rfc"
                      maxlength="13"
                    >
                  </div>
                </div>
                
                <div class="col-md-6 mb-3">
                  <label class="modern-label">Giro de Actividad</label>
                  <div class="input-modern">
                    <i class="fas fa-industry input-icon"></i>
                    <input
                      type="text"
                      class="form-input"
                      placeholder="Ej. Servicios médicos especializados"
                      v-model="form.giro_actividad"
                    >
                  </div>
                </div>
                
                <div class="col-md-12 mb-3">
                  <label class="modern-label">Dirección</label>
                  <div class="input-modern">
                    <i class="fas fa-map-marker-alt input-icon"></i>
                    <textarea
                      class="form-input"
                      placeholder="Calle, número, colonia, ciudad, estado, código postal"
                      v-model="form.direccion"
                      rows="2"
                    ></textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- INFORMACIÓN DE CONTACTO -->
          <div class="modern-card mb-4">
            <div class="card-header-gradient bg-success-gradient">
              <div class="d-flex align-items-center">
                <div class="card-icon bg-white text-success">
                  <i class="fas fa-address-book"></i>
                </div>
                <h5 class="mb-0 text-white font-weight-bold ml-3">Información de Contacto</h5>
              </div>
            </div>
            <div class="card-body-custom">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="modern-label">Nombre de Contacto</label>
                  <div class="input-modern">
                    <i class="fas fa-user-tie input-icon"></i>
                    <input
                      type="text"
                      class="form-input"
                      placeholder="Ej. María González López"
                      v-model="form.contacto_nombre"
                    >
                  </div>
                </div>
                
                <div class="col-md-6 mb-3">
                  <label class="modern-label">Teléfono de Contacto</label>
                  <div class="input-modern">
                    <i class="fas fa-phone input-icon"></i>
                    <input
                      type="tel"
                      class="form-input"
                      placeholder="Ej. 55 1234 5678"
                      v-model="form.contacto_telefono"
                      maxlength="20"
                    >
                  </div>
                </div>
                
                <div class="col-md-6 mb-3">
                  <label class="modern-label">Email de Contacto</label>
                  <div class="input-modern">
                    <i class="fas fa-envelope input-icon"></i>
                    <input
                      type="email"
                      class="form-input"
                      placeholder="ejemplo@empresa.com"
                      v-model="form.contacto_email"
                    >
                  </div>
                </div>
                
                <div class="col-md-6 mb-3">
                  <label class="modern-label">Estado</label>
                  <div class="switch-container">
                    <div class="modern-switch">
                      <input 
                        type="checkbox" 
                        id="estadoActivo" 
                        v-model="form.activo"
                        class="switch-input"
                      >
                      <label for="estadoActivo" class="switch-label">
                        <span class="switch-indicator"></span>
                        <span class="switch-text">
                          {{ form.activo ? 'Empresa activa' : 'Empresa inactiva' }}
                        </span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- COLUMNA DERECHA - Resumen y Acción -->
        <div class="col-lg-4">
          <div class="modern-card sticky-top" style="top: 20px;">
            <div class="card-header-gradient bg-warning-gradient">
              <div class="d-flex align-items-center">
                <div class="card-icon bg-white text-warning">
                  <i class="fas fa-clipboard-check"></i>
                </div>
                <h5 class="mb-0 text-white font-weight-bold ml-3">Resumen</h5>
              </div>
            </div>
            <div class="card-body-custom">
              <!-- Vista previa de la empresa -->
              <div v-if="empresa !== null" class="preview-container mb-4">
                <div class="preview-item">
                  <i class="fas fa-building text-primary"></i>
                  <div>
                    <small class="text-muted d-block">Razón Social</small>
                    <strong class="text-dark">{{ empresa.razon_social }}</strong>
                  </div>
                </div>
                <div class="preview-item">
                  <i class="fas fa-file-invoice text-info"></i>
                  <div>
                    <small class="text-muted d-block">RFC</small>
                    <span class="text-dark">{{ empresa.rfc || 'Sin RFC' }}</span>
                  </div>
                </div>
                <div class="preview-item">
                  <i class="fas fa-industry text-success"></i>
                  <div>
                    <small class="text-muted d-block">Giro</small>
                    <span class="text-dark">{{ empresa.giro_actividad || 'Sin giro' }}</span>
                  </div>
                </div>
                <div class="preview-item">
                  <i class="fas fa-toggle-on" :class="empresa.activo == 1 ? 'text-success' : 'text-secondary'"></i>
                  <div>
                    <small class="text-muted d-block">Estado</small>
                    <span class="badge" :class="empresa.activo == 1 ? 'badge-success' : 'badge-secondary'">
                      {{ empresa.activo == 1 ? 'Activa' : 'Inactiva' }}
                    </span>
                  </div>
                </div>
              </div>
              
              <div v-else class="empty-preview mb-4">
                <i class="fas fa-hospital fa-3x text-primary mb-3"></i>
                <p class="text-muted small mb-0">
                  Complete los datos para registrar una nueva empresa cliente
                </p>
              </div>

              <!-- Botón de guardar -->
              <button
                type="submit"
                :disabled="cargando"
                class="btn-guardar"
              >
                <i class="fas fa-save mr-2"></i>
                {{ cargando ? 'Guardando...' : 'Guardar Empresa' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </form>

    <!-- TOAST SUCCESS -->
    <div v-if="toastSuccess" class="toast-modern toast-success">
      <div class="toast-content">
        <div class="toast-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="toast-message">
          {{ mensajeSuccess }}
        </div>
        <button @click="toastSuccess = false" class="toast-close">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>

    <!-- TOAST ERROR -->
    <div v-if="toastError" class="toast-modern toast-error">
      <div class="toast-content">
        <div class="toast-icon">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="toast-message">
          {{ mensajeError }}
        </div>
        <button @click="toastError = false" class="toast-close">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import ApiServiceClinicaTrabajo from '@/services/ApiServiceClinicaTrabajo.js'

export default {
  props: {
    empresa: {
      type: Object,
      default: null
    }
  },
  emits: ['guardado', 'cancelar'],
  data() {
    return {
      form: {
        razon_social: '',
        rfc: '',
        giro_actividad: '',
        direccion: '',
        contacto_nombre: '',
        contacto_telefono: '',
        contacto_email: '',
        activo: true
      },
      cargando: false,
      toastSuccess: false,
      mensajeSuccess: '',
      toastError: false,
      mensajeError: ''
    }
  },
  watch: {
    empresa: {
      immediate: true,
      handler(empresa) {
        if (empresa !== null) {
          this.form = {
            razon_social: empresa.razon_social || '',
            rfc: empresa.rfc || '',
            giro_actividad: empresa.giro_actividad || '',
            direccion: empresa.direccion || '',
            contacto_nombre: empresa.contacto_nombre || '',
            contacto_telefono: empresa.contacto_telefono || '',
            contacto_email: empresa.contacto_email || '',
            activo: empresa.activo == 1
          };
        } else {
          this.form = {
            razon_social: '',
            rfc: '',
            giro_actividad: '',
            direccion: '',
            contacto_nombre: '',
            contacto_telefono: '',
            contacto_email: '',
            activo: true
          };
        }
      }
    }
  },
  methods: {
    async guardarEmpresa() {
      this.cargando = true;
      this.toastError = false;
      this.mensajeError = '';

      try {
        let response;
        if (this.empresa === null) {
          // Alta nueva empresa (POST)
          response = await ApiServiceClinicaTrabajo.post('/EmpresaCliente', this.form);
        } else {
          // Edición existente (PUT)
          response = await ApiServiceClinicaTrabajo.put('/EmpresaCliente/' + this.empresa.id, this.form);
        }

        this.mensajeSuccess = response.data.message || 'Empresa registrado ó actualizada correctamente';
        this.toastSuccess = true;

        this.$emit('guardado', response.data);
        if (this.empresa === null) {
          this.form = {
            razon_social: '',
            rfc: '',
            giro_actividad: '',
            direccion: '',
            contacto_nombre: '',
            contacto_telefono: '',
            contacto_email: '',
            activo: true
          };
        }

        setTimeout(() => {
          this.toastSuccess = false;
        }, 4000);

      } catch (error) {
        console.error('Error al guardar empresa:', error);

        if (error.response && error.response.data) {
          if (error.response.status === 422 && error.response.data.errors) {
            this.mensajeError = 'Por favor corrija los errores indicados';
          } else {
            this.mensajeError = error.response.data.message || 'Error al guardar la empresa';
          }
        } else {
          this.mensajeError = 'Error de conexión al guardar la empresa';
        }
        this.toastError = true;
        
        setTimeout(() => {
          this.toastError = false;
        }, 4000);
      } finally {
        this.cargando = false;
      }
    }
  }
}
</script>

<style scoped>
.form-container {
  padding: 20px 0;
}

/* Header del formulario */
.form-header {
  background: #ffffff;
  padding: 18px 24px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  margin-bottom: 24px;
}

.form-header-icon {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #0061f2 0%, #00ba94 100%);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.4rem;
  box-shadow: 0 4px 12px rgba(0, 97, 242, 0.25);
}

h2 {
  font-size: 1.3rem;
  letter-spacing: -0.3px;
  color: #1e293b;
}

/* Cards modernos */
.modern-card {
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  border: none;
}

.card-header-gradient {
  padding: 16px 20px;
  display: flex;
  align-items: center;
}

.bg-primary-gradient {
  background: linear-gradient(135deg, #0061f2 0%, #00ba94 100%);
}

.bg-success-gradient {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.bg-warning-gradient {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.card-icon {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
}

.card-body-custom {
  padding: 24px;
}

/* Labels */
.modern-label {
  display: block;
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #64748b;
  margin-bottom: 8px;
}

/* Inputs */
.input-modern {
  position: relative;
}

.form-input {
  width: 100%;
  padding: 11px 14px 11px 40px;
  font-size: 0.92rem;
  background-color: #f8fafc;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  color: #334155;
  transition: all 0.25s ease;
}

.form-input:focus {
  background-color: #ffffff;
  border-color: #0061f2;
  box-shadow: 0 0 0 3px rgba(0, 97, 242, 0.1);
  outline: none;
}

.input-icon {
  position: absolute;
  left: 13px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 0.95rem;
  transition: color 0.25s ease;
}

.input-modern:focus-within .input-icon {
  color: #0061f2;
}

/* Switch personalizado */
.switch-container {
  padding: 8px 0;
}

.modern-switch {
  position: relative;
}

.switch-input {
  display: none;
}

.switch-label {
  display: flex;
  align-items: center;
  cursor: pointer;
  gap: 10px;
}

.switch-indicator {
  width: 46px;
  height: 24px;
  background: #cbd5e1;
  border-radius: 12px;
  position: relative;
  transition: all 0.3s ease;
}

.switch-indicator::before {
  content: '';
  position: absolute;
  width: 20px;
  height: 20px;
  background: white;
  border-radius: 50%;
  top: 2px;
  left: 2px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.switch-input:checked + .switch-label .switch-indicator {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.switch-input:checked + .switch-label .switch-indicator::before {
  transform: translateX(22px);
}

.switch-text {
  font-size: 0.88rem;
  color: #475569;
  font-weight: 500;
}

/* Preview box */
.preview-container {
  background: #f8fafc;
  border-radius: 10px;
  padding: 14px;
}

.preview-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 9px 0;
  border-bottom: 1px solid #e2e8f0;
}

.preview-item:last-child {
  border-bottom: none;
}

.preview-item i {
  font-size: 1.1rem;
  margin-top: 2px;
}

.empty-preview {
  text-align: center;
  padding: 18px;
  background: #f8fafc;
  border-radius: 10px;
}

/* Botón guardar */
.btn-guardar {
  width: 100%;
  background: linear-gradient(135deg, #0061f2 0%, #00ba94 100%);
  color: white;
  border: none;
  border-radius: 10px;
  padding: 13px;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(0, 97, 242, 0.3);
}

.btn-guardar:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 97, 242, 0.4);
}

.btn-guardar:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Toasts modernos */
.toast-modern {
  position: fixed;
  right: 20px;
  z-index: 9999;
  min-width: 300px;
  animation: slideIn 0.3s ease;
}

.toast-success {
  top: 20px;
}

.toast-error {
  top: 90px;
}

.toast-content {
  background: white;
  border-radius: 10px;
  padding: 14px 18px;
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

.toast-success .toast-content {
  border-left: 4px solid #10b981;
}

.toast-error .toast-content {
  border-left: 4px solid #ef4444;
}

.toast-icon {
  font-size: 1.4rem;
}

.toast-success .toast-icon {
  color: #10b981;
}

.toast-error .toast-icon {
  color: #ef4444;
}

.toast-message {
  flex: 1;
  font-weight: 600;
  color: #1e293b;
  font-size: 0.9rem;
}

.toast-close {
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 4px;
  transition: color 0.2s ease;
}

.toast-close:hover {
  color: #475569;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}
</style>