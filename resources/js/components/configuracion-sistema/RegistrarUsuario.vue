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
                @click.prevent="irTabla" 
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
          class="text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
          style="width: 48px; height: 48px; font-size: 1.1rem; background: #f0f2fe; border: 1px solid #e0e4fd;"
        >
          <i class="fas fa-id-card-alt text-primary"></i>
        </div>
        <div>
          <h5 class="fw-bold m-1 text-dark" style="letter-spacing: -0.3px;">Credenciales de Acceso</h5>
          <p class="text-muted m-0 small">Introduce los datos del usuario y selecciona su nivel de privilegios en el sistema.</p>
        </div>
      </div>

      <form @submit.prevent="handleSubmit">
        
        <div class="row g-4 mb-4">
          <div class="col-12 col-md-6">
            <label class="form-label fw-bold text-secondary small mb-2" for="name">Nombre completo</label>
            <div class="input-group">
              <span class="input-group-text border-end-0 rounded-start-pill px-3" style="background: #f8fafc;"><i class="fas fa-signature text-primary"></i></span>
              <input 
                type="text" 
                class="form-control border-start-0 ps-2 rounded-end-pill" 
                style="background: #f8fafc; font-size: 0.9rem; padding: 0.65rem 1rem;"
                id="name" 
                v-model="form.name"
                placeholder="Ej. Dra. María López García"
                required
              >
            </div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label fw-bold text-secondary small mb-2" for="email">Dirección de correo electrónico</label>
            <div class="input-group">
              <span class="input-group-text border-end-0 rounded-start-pill px-3" style="background: #f8fafc;"><i class="fas fa-envelope-open text-info"></i></span>
              <input 
                type="email" 
                class="form-control border-start-0 ps-2 rounded-end-pill" 
                style="background: #f8fafc; font-size: 0.9rem; padding: 0.65rem 1rem;"
                id="email" 
                v-model="form.email"
                placeholder="usuario@tuclinica.com"
                required
              >
            </div>
          </div>
        </div>

        <div class="row g-4 mb-5">
          <div class="col-12 col-md-6">
            <label class="form-label fw-bold text-secondary small mb-2" for="password">Contraseña de ingreso</label>
            <div class="input-group">
              <span class="input-group-text border-end-0 rounded-start-pill px-3" style="background: #f8fafc;"><i class="fas fa-key text-warning"></i></span>
              <input 
                :type="mostrarPassword ? 'text' : 'password'" 
                class="form-control border-start-0 border-end-0 ps-2" 
                style="background: #f8fafc; font-size: 0.9rem;"
                id="password" 
                v-model="form.password"
                placeholder="Mínimo 8 caracteres"
                required
              >
              <span 
                class="input-group-text border-start-0 text-muted rounded-end-pill px-3" 
                @click="mostrarPassword = !mostrarPassword" 
                style="cursor: pointer; background: #f8fafc; font-size: 0.9rem;"
              >
                <i :class="mostrarPassword ? 'fas fa-eye-slash text-primary' : 'fas fa-eye'"></i>
              </span>
            </div>
          </div>
          
          <div class="col-12 col-md-6">
            <label class="form-label fw-bold text-secondary small mb-2" for="password_confirmation">Confirmar contraseña</label>
            <div class="input-group">
              <span class="input-group-text border-end-0 rounded-start-pill px-3" style="background: #f8fafc;"><i class="fas fa-shield-alt text-success"></i></span>
              <input 
                :type="mostrarConfirmacion ? 'text' : 'password'" 
                class="form-control border-start-0 border-end-0 ps-2" 
                style="background: #f8fafc; font-size: 0.9rem;"
                id="password_confirmation"
                v-model="form.password_confirmation" 
                placeholder="Repite la contraseña exactamente"
                required
              >
              <span 
                class="input-group-text border-start-0 text-muted rounded-end-pill px-3" 
                @click="mostrarConfirmacion = !mostrarConfirmacion" 
                style="cursor: pointer; background: #f8fafc; font-size: 0.9rem;"
              >
                <i :class="mostrarConfirmacion ? 'fas fa-eye-slash text-primary' : 'fas fa-eye'"></i>
              </span>
            </div>
          </div>
        </div>

        <div class="mb-5">
          <label class="form-label fw-bold text-dark small mb-1">Nivel de Acceso (Rol)</label>
          <p class="text-muted small mb-3">Escoge el tipo de perfil idóneo para configurar sus permisos de navegación.</p>
          
          <div class="row g-3">
            <div class="col-12 col-lg-4">
              <label 
                class="card h-100 p-3 border rounded-3 position-relative d-flex flex-row align-items-center gap-3 shadow-sm" 
                :style="form.rol === 'admin' ? 'border-color: #4f46e5 !important; background: #f5f3ff; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.08) !important;' : 'border-color: #e2e8f0; background: #ffffff;'"
                style="cursor: pointer; transition: all 0.2s ease-in-out;"
                @mouseover="$el => $el.target.closest('.card').style.transform = 'translateY(-2px)'"
                @mouseleave="$el => $el.target.closest('.card').style.transform = 'translateY(0)'"
              >
                <input type="radio" class="d-none" value="admin" v-model="form.rol">
                <div 
                  class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                  :style="form.rol === 'admin' ? 'background: #4f46e5; color: #ffffff;' : 'background: #f1f5f9; color: #64748b;'"
                  style="width: 44px; height: 44px; font-size: 1rem; transition: all 0.2s;"
                >
                  <i class="fas fa-crown" :style="form.rol === 'admin' ? 'color: #ffffff;' : 'color: #eab308;'"></i>
                </div>
                <div class="flex-grow-1">
                  <h6 class="m-0 fw-bold small" :class="form.rol === 'admin' ? 'text-primary' : 'text-dark'">Administrador</h6>
                  <span class="text-muted d-block" style="font-size: 0.72rem; margin-top: 1px;">Gestión completa</span>
                </div>
                <div 
                  class="rounded-circle border d-flex align-items-center justify-content-center flex-shrink-0 ms-auto" 
                  :style="form.rol === 'admin' ? 'background: #4f46e5; border-color: #4f46e5; color: white;' : 'background: #fff; border-color: #cbd5e1;'"
                  style="width: 18px; height: 18px; font-size: 0.55rem;"
                >
                  <i v-if="form.rol === 'admin'" class="fas fa-check"></i>
                </div>
              </label>
            </div>

            <div class="col-12 col-lg-4">
              <label 
                class="card h-100 p-3 border rounded-3 position-relative d-flex flex-row align-items-center gap-3 shadow-sm" 
                :style="form.rol === 'medico' ? 'border-color: #06b6d4 !important; background: #ecfeff; box-shadow: 0 4px 12px rgba(6, 182, 212, 0.08) !important;' : 'border-color: #e2e8f0; background: #ffffff;'"
                style="cursor: pointer; transition: all 0.2s ease-in-out;"
                @mouseover="$el => $el.target.closest('.card').style.transform = 'translateY(-2px)'"
                @mouseleave="$el => $el.target.closest('.card').style.transform = 'translateY(0)'"
              >
                <input type="radio" class="d-none" value="medico" v-model="form.rol">
                <div 
                  class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                  :style="form.rol === 'medico' ? 'background: #06b6d4; color: #ffffff;' : 'background: #f1f5f9; color: #64748b;'"
                  style="width: 44px; height: 44px; font-size: 1rem; transition: all 0.2s;"
                >
                  <i class="fas fa-user-md" :style="form.rol === 'medico' ? 'color: #ffffff;' : 'color: #06b6d4;'"></i>
                </div>
                <div class="flex-grow-1">
                  <h6 class="m-0 fw-bold small" :class="form.rol === 'medico' ? 'text-info' : 'text-dark'">Médico Especialista</h6>
                  <span class="text-muted d-block" style="font-size: 0.72rem; margin-top: 1px;">Control de pacientes</span>
                </div>
                <div 
                  class="rounded-circle border d-flex align-items-center justify-content-center flex-shrink-0 ms-auto" 
                  :style="form.rol === 'medico' ? 'background: #06b6d4; border-color: #06b6d4; color: white;' : 'background: #fff; border-color: #cbd5e1;'"
                  style="width: 18px; height: 18px; font-size: 0.55rem;"
                >
                  <i v-if="form.rol === 'medico'" class="fas fa-check"></i>
                </div>
              </label>
            </div>

            <div class="col-12 col-lg-4">
              <label 
                class="card h-100 p-3 border rounded-3 position-relative d-flex flex-row align-items-center gap-3 shadow-sm" 
                :style="form.rol === 'recepcion' ? 'border-color: #10b981 !important; background: #ecfdf5; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.08) !important;' : 'border-color: #e2e8f0; background: #ffffff;'"
                style="cursor: pointer; transition: all 0.2s ease-in-out;"
                @mouseover="$el => $el.target.closest('.card').style.transform = 'translateY(-2px)'"
                @mouseleave="$el => $el.target.closest('.card').style.transform = 'translateY(0)'"
              >
                <input type="radio" class="d-none" value="recepcion" v-model="form.rol">
                <div 
                  class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                  :style="form.rol === 'recepcion' ? 'background: #10b981; color: #ffffff;' : 'background: #f1f5f9; color: #64748b;'"
                  style="width: 44px; height: 44px; font-size: 1rem; transition: all 0.2s;"
                >
                  <i class="fas fa-calendar-alt" :style="form.rol === 'recepcion' ? 'color: #ffffff;' : 'color: #10b981;'"></i>
                </div>
                <div class="flex-grow-1">
                  <h6 class="m-0 fw-bold small" :class="form.rol === 'recepcion' ? 'text-success' : 'text-dark'">Recepción / Citas</h6>
                  <span class="text-muted d-block" style="font-size: 0.72rem; margin-top: 1px;">Gestión de agendas</span>
                </div>
                <div 
                  class="rounded-circle border d-flex align-items-center justify-content-center flex-shrink-0 ms-auto" 
                  :style="form.rol === 'recepcion' ? 'background: #10b981; border-color: #10b981; color: white;' : 'background: #fff; border-color: #cbd5e1;'"
                  style="width: 18px; height: 18px; font-size: 0.55rem;"
                >
                  <i v-if="form.rol === 'recepcion'" class="fas fa-check"></i>
                </div>
              </label>
            </div>
          </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 pt-4 border-top border-light">
          <button 
            type="button" 
            @click="cancelar" 
            class="btn btn-light rounded-pill px-4 py-2 text-secondary fw-bold border border-light shadow-sm"
            style="transition: all 0.2s ease; font-size: 0.85rem;"
            @mouseover="$el => { $el.target.style.background = '#f1f5f9'; }"
            @mouseleave="$el => { $el.target.style.background = '#f8fafc'; }"
          >
            Cancelar
          </button>
          
          <button 
            type="submit" 
            class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow"
            style="background: #4f46e5; border-color: #4f46e5; transition: all 0.2s ease; font-size: 0.85rem;"
            @mouseover="$el => { $el.target.style.background = '#3730a3'; $el.target.style.transform = 'translateY(-2px)'; $el.target.style.boxShadow = '0 6px 20px rgba(79, 70, 229, 0.3)'; }"
            @mouseleave="$el => { $el.target.style.background = '#4f46e5'; $el.target.style.transform = 'translateY(0)'; $el.target.style.boxShadow = 'none'; }"
          >
            <i class="fas fa-check-circle me-2"></i>Guardar Nuevo Usuario
          </button>
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'

const mostrarPassword = ref(false)
const mostrarConfirmacion = ref(false)

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  rol: 'medico' // Manteniendo tu campo "rol" nativo
})

const emit = defineEmits(['submit', 'volver'])

const handleSubmit = () => {
  if (form.password !== form.password_confirmation) {
    alert('Las contraseñas no coinciden.')
    return
  }
  emit('submit', { ...form })
}

const irTabla = () => {
  emit('volver')
}

const cancelar = () => {
  form.name = ''
  form.email = ''
  form.password = ''
  form.password_confirmation = ''
  form.rol = 'medico'
  emit('volver')
}
</script>