<template>
  <div class="d-flex align-items-center justify-content-between mt-3 mb-2 pb-4 border-bottom border-light">
    <div>
      <h1 class="h4 fw-black text-dark m-2 d-flex align-items-center">
        <i class="fas fa-user-plus me-2 text-primary fs-5"></i>Registrar Nuevo Usuario
      </h1>
    </div>
    <div class="my-auto">
      <a
        href="#"
        @click.prevent="cancelarRegistro"
        class="btn btn-light border rounded-pill px-4 py-2 shadow-sm d-inline-flex align-items-center gap-2 text-secondary fw-semibold"
      >
        <i class="fas fa-arrow-right m-2"></i>
        <span>Ver tabla de usuarios</span>
      </a>
    </div>
  </div>

  <div class="card border-0 rounded-4 shadow-sm overflow-hidden" style="background: #ffffff; border: 1px solid #f1f5f9 !important;">
    <div class="card-body p-4 p-lg-5">

      <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom border-light">
        <div
          class="rounded-circle d-flex align-items-center justify-content-center shadow-sm"
          style="width: 48px; height: 48px; font-size: 1.1rem; background: #f0f2fe; border: 1px solid #e0e4fd;"
        >
          <i class="fas fa-id-card-alt text-primary"></i>
        </div>
        <div>
          <h5 class="fw-bold m-1 text-dark" style="letter-spacing: -0.3px;">Credenciales de Acceso</h5>
          <p class="text-muted m-0 small">Introduce los datos del usuario y selecciona su nivel de privilegios en el sistema.</p>
        </div>
      </div>

      <form @submit.prevent="guardarUsuario">

        <!-- Campos de texto -->
        <div class="row g-4 mb-4">
          <div class="col-12 col-md-6">
            <label class="fi-label" for="name">Nombre completo</label>
            <div :class="['fi-group', errores.name ? 'fi-group--error' : '']">
              <span class="fi-icon"><i class="fas fa-signature"></i></span>
              <input
                type="text"
                class="fi-input"
                id="name"
                v-model="form.name"
                placeholder="Dra. María López García"
              >
            </div>
            <span v-if="errores.name" class="fi-error">
              <i class="fas fa-exclamation-circle me-1"></i>{{ errores.name }}
            </span>
          </div>

          <div class="col-12 col-md-6">
            <label class="fi-label" for="email">Correo electrónico</label>
            <div :class="['fi-group', errores.email ? 'fi-group--error' : '']">
              <span class="fi-icon"><i class="fas fa-envelope-open"></i></span>
              <input
                type="email"
                class="fi-input"
                id="email"
                v-model="form.email"
                placeholder="usuario@tuclinica.com"
              >
            </div>
            <span v-if="errores.email" class="fi-error">
              <i class="fas fa-exclamation-circle me-1"></i>{{ errores.email }}
            </span>
          </div>
        </div>

        <div class="row g-4 mb-5">
          <div class="col-12 col-md-6">
            <label class="fi-label" for="password">Contraseña</label>
            <div :class="['fi-group', errores.password ? 'fi-group--error' : '']">
              <span class="fi-icon"><i class="fas fa-key"></i></span>
              <input
                :type="mostrarPassword ? 'text' : 'password'"
                class="fi-input"
                id="password"
                v-model="form.password"
                placeholder="Mínimo 8 caracteres"
              >
              <span class="fi-eye" @click="mostrarPassword = !mostrarPassword">
                <i :class="mostrarPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </span>
            </div>
            <span v-if="errores.password" class="fi-error">
              <i class="fas fa-exclamation-circle me-1"></i>{{ errores.password }}
            </span>
          </div>

          <div class="col-12 col-md-6">
            <label class="fi-label" for="password_confirmation">Confirmar contraseña</label>
            <div :class="['fi-group', errores.password_confirmation ? 'fi-group--error' : '']">
              <span class="fi-icon"><i class="fas fa-shield-alt"></i></span>
              <input
                :type="mostrarConfirmacion ? 'text' : 'password'"
                class="fi-input"
                id="password_confirmation"
                v-model="form.password_confirmation"
                placeholder="Repite la contraseña"
              >
              <span class="fi-eye" @click="mostrarConfirmacion = !mostrarConfirmacion">
                <i :class="mostrarConfirmacion ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </span>
            </div>
            <span v-if="errores.password_confirmation" class="fi-error">
              <i class="fas fa-exclamation-circle me-1"></i>{{ errores.password_confirmation }}
            </span>
          </div>
        </div>

        <!-- Role cards -->
        <div class="mb-5">
          <p class="fw-bold text-dark small mb-1">Nivel de acceso</p>
          <p class="text-muted small mb-3">Define los permisos de navegación del nuevo usuario.</p>

          <div class="row g-3">
            <div class="col-12 col-lg-4">
              <label :class="['role-card', form.rol === 'admin' ? 'is-admin' : '']">
                <input type="radio" class="d-none" value="admin" v-model="form.rol">
                <div
                  class="role-icon"
                  :style="form.rol === 'admin' ? 'background:#ede9fe; color:#4f46e5;' : 'background:#f1f5f9; color:#94a3b8;'"
                >
                  <i class="fas fa-crown" :style="form.rol !== 'admin' ? 'color:#eab308' : ''"></i>
                </div>
                <div>
                  <h6 class="m-0 fw-bold small" :style="form.rol === 'admin' ? 'color:#4f46e5' : 'color:#1e293b'">
                    Administrador
                  </h6>
                  <span class="text-muted" style="font-size:0.7rem;">Gestión completa</span>
                </div>
                <div class="role-radio">
                  <div class="dot"></div>
                </div>
              </label>
            </div>

            <div class="col-12 col-lg-4">
              <label :class="['role-card', form.rol === 'medico' ? 'is-medico' : '']">
                <input type="radio" class="d-none" value="medico" v-model="form.rol">
                <div
                  class="role-icon"
                  :style="form.rol === 'medico' ? 'background:#cffafe; color:#06b6d4;' : 'background:#f1f5f9; color:#94a3b8;'"
                >
                  <i class="fas fa-user-md"></i>
                </div>
                <div>
                  <h6 class="m-0 fw-bold small" :style="form.rol === 'medico' ? 'color:#06b6d4' : 'color:#1e293b'">
                    Médico Especialista
                  </h6>
                  <span class="text-muted" style="font-size:0.7rem;">Control de pacientes</span>
                </div>
                <div class="role-radio">
                  <div class="dot"></div>
                </div>
              </label>
            </div>

            <div class="col-12 col-lg-4">
              <label :class="['role-card', form.rol === 'recepcion' ? 'is-recepcion' : '']">
                <input type="radio" class="d-none" value="recepcion" v-model="form.rol">
                <div
                  class="role-icon"
                  :style="form.rol === 'recepcion' ? 'background:#d1fae5; color:#10b981;' : 'background:#f1f5f9; color:#94a3b8;'"
                >
                  <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                  <h6 class="m-0 fw-bold small" :style="form.rol === 'recepcion' ? 'color:#10b981' : 'color:#1e293b'">
                    Recepción / Citas
                  </h6>
                  <span class="text-muted" style="font-size:0.7rem;">Gestión de agendas</span>
                </div>
                <div class="role-radio">
                  <div class="dot"></div>
                </div>
              </label>
            </div>
          </div>

          <span v-if="errores.rol" class="fi-error mt-2 d-block">
            <i class="fas fa-exclamation-circle me-1"></i>{{ errores.rol }}
          </span>
        </div>

        <!-- Footer -->
        <div class="d-flex align-items-center justify-content-end gap-3 pt-4 border-top">
          <button
            type="button"
            @click="cancelarRegistro"
            class="btn btn-light rounded-pill px-4 py-2 fw-semibold border"
            style="font-size:0.85rem;"
            :disabled="guardando"
          >
            Cancelar
          </button>
          <button
            type="submit"
            class="btn rounded-pill px-4 py-2 fw-bold text-white d-inline-flex align-items-center gap-2"
            style="background:#4f46e5; border:none; font-size:0.85rem; min-width: 160px;"
            :disabled="guardando"
          >
            <span v-if="guardando">
              <span class="spinner-border spinner-border-sm me-1" role="status"></span>
              Guardando...
            </span>
            <span v-else>
              <i class="fas fa-check-circle me-2"></i>Guardar usuario
            </span>
          </button>
        </div>

      </form>
    </div>
  </div>
</template>

<script>
import ApiService from '../../services/ApiService.js'

export default {

  emits: ['volver'],

  data() {
    return {
      mostrarPassword: false,
      mostrarConfirmacion: false,
      guardando: false,
      form: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        rol: 'medico'
      },
      errores: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        rol: ''
      }
    }
  },

  mounted() {
    console.log('Componente de registro de usuario listo.')
  },

  methods: {
    validarFormulario() {
      this.errores = { name: '', email: '', password: '', password_confirmation: '', rol: '' }
      let valido = true

      if (!this.form.name.trim()) {
        this.errores.name = 'El nombre completo es obligatorio.'
        valido = false
      }

      if (!this.form.email.trim()) {
        this.errores.email = 'El correo electrónico es obligatorio.'
        valido = false
      } else if (!/\S+@\S+\.\S+/.test(this.form.email)) {
        this.errores.email = 'El formato del correo no es válido.'
        valido = false
      }

      if (!this.form.password) {
        this.errores.password = 'La contraseña es obligatoria.'
        valido = false
      } else if (this.form.password.length < 8) {
        this.errores.password = 'La contraseña debe tener mínimo 8 caracteres.'
        valido = false
      }

      if (this.form.password !== this.form.password_confirmation) {
        this.errores.password_confirmation = 'Las contraseñas no coinciden.'
        valido = false
      }

      if (!this.form.rol) {
        this.errores.rol = 'Debe seleccionar un rol.'
        valido = false
      }

      return valido
    },

    limpiarFormulario() {
      this.form = { name: '', email: '', password: '', password_confirmation: '', rol: 'medico' }
      this.errores = { name: '', email: '', password: '', password_confirmation: '', rol: '' }
    },

    async guardarUsuario() {
      if (!this.validarFormulario()) return

      this.guardando = true
      try {
        const response = await ApiService.post('usuarios/registro', { ...this.form })

        Swal.fire({
          icon: 'success',
          title: 'Usuario registrado',
          text: response.data?.message || 'El usuario fue creado exitosamente.',
          confirmButtonText: 'Aceptar'
        })

        this.limpiarFormulario()
        this.$emit('volver')

      } catch (error) {
        console.error(error)

        if (error.response?.status === 422) {
          // Errores de validación de Laravel → mostrarlos inline
          const erroresBackend = error.response.data.errors
          Object.keys(erroresBackend).forEach(campo => {
            if (this.errores.hasOwnProperty(campo)) {
              this.errores[campo] = erroresBackend[campo][0]
            }
          })
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un error al guardar el usuario. Intenta de nuevo.',
            confirmButtonText: 'Aceptar'
          })
        }
      } finally {
        this.guardando = false
      }
    },

    cancelarRegistro() {
      this.limpiarFormulario()
      this.$emit('volver')
    }
  }
}
</script>

<style>
/* ── Inputs ─────────────────────────────────────── */
.fi-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  margin-bottom: 6px;
  display: block;
}
.fi-group {
  display: flex;
  align-items: stretch;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
  transition: border-color 0.18s ease, box-shadow 0.18s ease;
}
.fi-group:hover {
  border-color: #cbd5e1;
}
.fi-group:focus-within {
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
  background: #ffffff;
}
.fi-group:focus-within .fi-icon {
  color: #4f46e5;
  border-right-color: rgba(79, 70, 229, 0.15);
}
.fi-group--error {
  border-color: #ef4444 !important;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.08) !important;
}
.fi-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 14px;
  color: #94a3b8;
  border-right: 1.5px solid #e2e8f0;
  flex-shrink: 0;
  transition: color 0.18s ease, border-color 0.18s ease;
}
.fi-input {
  flex: 1;
  border: none;
  outline: none;
  background: transparent;
  padding: 11px 14px;
  font-size: 0.875rem;
  color: #1e293b;
}
.fi-input::placeholder {
  color: #94a3b8;
}
.fi-eye {
  padding: 0 14px;
  display: flex;
  align-items: center;
  cursor: pointer;
  color: #94a3b8;
  border-left: 1.5px solid #e2e8f0;
  transition: color 0.15s ease;
  flex-shrink: 0;
}
.fi-eye:hover {
  color: #4f46e5;
}
.fi-error {
  font-size: 0.75rem;
  color: #ef4444;
  margin-top: 5px;
  display: block;
}

/* ── Role cards ──────────────────────────────────── */
.role-card {
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  padding: 14px 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  transition: all 0.18s ease;
  background: #ffffff;
  margin: 0;
}
.role-card:hover {
  border-color: #cbd5e1;
  background: #f8fafc;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}
.role-card.is-admin    { border-color: #4f46e5; background: #f5f3ff; box-shadow: 0 0 0 3px rgba(79,70,229,0.08); }
.role-card.is-medico   { border-color: #06b6d4; background: #ecfeff; box-shadow: 0 0 0 3px rgba(6,182,212,0.08); }
.role-card.is-recepcion{ border-color: #10b981; background: #ecfdf5; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
.role-icon {
  width: 42px; height: 42px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem; flex-shrink: 0;
  transition: all 0.18s ease;
}
.role-radio {
  width: 18px; height: 18px;
  border-radius: 50%;
  border: 1.5px solid #cbd5e1;
  display: flex; align-items: center; justify-content: center;
  margin-left: auto; flex-shrink: 0;
  transition: all 0.18s ease;
}
.role-radio .dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  transform: scale(0);
  transition: transform 0.15s ease;
}
.role-card.is-admin .role-radio,
.role-card.is-medico .role-radio,
.role-card.is-recepcion .role-radio { border-color: currentColor; }
.role-card.is-admin .role-radio .dot,
.role-card.is-medico .role-radio .dot,
.role-card.is-recepcion .role-radio .dot { transform: scale(1); }
.is-admin    .role-radio { color: #4f46e5; } .is-admin    .role-radio .dot { background: #4f46e5; }
.is-medico   .role-radio { color: #06b6d4; } .is-medico   .role-radio .dot { background: #06b6d4; }
.is-recepcion .role-radio{ color: #10b981; } .is-recepcion .role-radio .dot{ background: #10b981; }
</style>