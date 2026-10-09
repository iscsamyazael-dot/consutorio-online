<template>
  <div class="form-view active">
    <div class="toast" :class="{ show: showToast }">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg>
      <span>{{ toastMessage }}</span>
    </div>

    <!-- ============================================= -->
    <!-- MODO CREAR: wizard de 3 pasos                  -->
    <!-- ============================================= -->
    <template v-if="modo === 'crear'">
      <div class="stepper">
        <div class="step" :class="{ active: currentStep === 1, done: currentStep > 1 }">
          <div class="num">1</div>
          <div><span class="label">Datos y Módulos</span><span class="sub">nombre, dominio, módulos</span></div>
        </div>
        <div class="step" :class="{ active: currentStep === 2, done: currentStep > 2 }">
          <div class="num">2</div>
          <div><span class="label">Base de datos</span><span class="sub">db_name, estatus</span></div>
        </div>
        <div class="step" :class="{ active: currentStep === 3 }">
          <div class="num">3</div>
          <div><span class="label">Confirmar</span><span class="sub">Crear registro</span></div>
        </div>
      </div>

      <!-- Step 1: Datos + Módulos -->
      <div class="fieldset" :class="{ active: currentStep === 1 }">
        <div class="grid-2">
          <div class="field">
            <label>Nombre del consultorio</label>
            <input type="text" v-model="guardarRegistro.nombre_consultorio" placeholder="Ej. Consultorio Vida">
          </div>
          <div class="field">
            <label>Dominio / correo</label>
            <input type="text" v-model="guardarRegistro.dominio_correo" placeholder="ejemplo.com">
          </div>
        </div>

        <!-- MÓDULOS (CREAR) - Diseño mejorado -->
        <div class="field" style="grid-column: 1 / -1; margin-top: 1.5rem;">
          <label class="section-label">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="7" height="7" rx="1"/>
              <rect x="14" y="3" width="7" height="7" rx="1"/>
              <rect x="3" y="14" width="7" height="7" rx="1"/>
              <rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            Módulos a asignar
          </label>
          <div class="modulos-grid">
            <label v-for="modulo in modulosDisponibles" :key="modulo.id" class="modulo-card" :class="{ selected: guardarRegistro.modulos.includes(modulo.id) }">
              <input type="checkbox" :value="modulo.id" v-model="guardarRegistro.modulos" class="modulo-checkbox">
              <div class="modulo-content">
                <div class="modulo-icon">
                  <svg v-if="modulo.clave === 'medicina_trabajo'" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                  </svg>
                  <svg v-else-if="modulo.clave === 'expediente_clinico'" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14,2 14,8 20,8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10,9 9,9 8,9"/>
                  </svg>
                  <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <path d="M3 9h18"/>
                    <path d="M9 21V9"/>
                  </svg>
                </div>
                <div class="modulo-info">
                  <span class="modulo-name">{{ modulo.nombre }}</span>
                  <small class="modulo-desc" v-if="modulo.descripcion">{{ modulo.descripcion }}</small>
                </div>
              </div>
              <div class="modulo-check">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                  <polyline points="20,6 9,17 4,12"/>
                </svg>
              </div>
            </label>
          </div>
        </div>

        <div class="form-actions">
          <button class="btn btn-ghost" @click="$emit('cancel')">Cancelar</button>
          <div class="right"><button class="btn btn-primary" @click="currentStep = 2">Siguiente</button></div>
        </div>
      </div>

      <!-- Step 2 -->
      <div class="fieldset" :class="{ active: currentStep === 2 }">
        <div class="grid-2">
          <div class="field">
            <label>Base de datos (autogenerada)</label>
            <input type="text" :value="generatedDbName" readonly>
            <div class="hint">db_name se genera con el patrón medico_online_{nombre}</div>
          </div>
          <div class="field">
            <label>Estatus</label>
            <select v-model="guardarRegistro.estatus">
              <option value="activo">Activo</option>
              <option value="suspendido">Suspendido</option>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn btn-ghost" @click="currentStep = 1">Atrás</button>
          <div class="right"><button class="btn btn-primary" @click="currentStep = 3">Siguiente</button></div>
        </div>
      </div>

      <!-- Step 3 -->
      <div class="fieldset" :class="{ active: currentStep === 3 }">
        <div class="summary">
          <div class="summary-row"><span>nombre_consultorio</span><span>{{ guardarRegistro.nombre_consultorio || 'Sin nombre' }}</span></div>
          <div class="summary-row"><span>dominio_correo</span><span>{{ guardarRegistro.dominio_correo || 'sindominio.com' }}</span></div>
          <div class="summary-row"><span>db_name</span><span>{{ generatedDbName }}</span></div>
          <div class="summary-row"><span>estatus</span><span>{{ guardarRegistro.estatus }}</span></div>
          <div class="summary-row"><span>módulos</span><span class="badge">{{ guardarRegistro.modulos.length }} seleccionados</span></div>
        </div>
        <div class="note">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
          Al confirmar se creará el registro, la base de datos física y se asignarán los módulos al administrador.
        </div>
        <div class="form-actions">
          <button class="btn btn-ghost" @click="currentStep = 2" :disabled="cargando">Atrás</button>
          <div class="right">
            <button class="btn btn-primary" @click="guardarCliente()" :disabled="cargando">
              <span v-if="cargando" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              {{ mensajeCarga }}
            </button>
          </div>
        </div>
      </div>
    </template>

    <!-- ============================================= -->
    <!-- MODO EDITAR: pantalla única                    -->
    <!-- ============================================= -->
    <template v-else>
      <div class="grid-2">
        <div class="field"><label>Folio</label><input type="text" :value="form.folio" readonly></div>
        <div class="field"><label>Nombre del consultorio</label><input type="text" v-model="form.nombre_consultorio" placeholder="Ej. Consultorio Vida"></div>
      </div>
      <div class="grid-2">
        <div class="field"><label>Base de datos</label><input type="text" :value="form.db_name" readonly></div>
        <div class="field"><label>Dominio / correo</label><input type="text" :value="form.dominio_correo" readonly></div>
      </div>
      <div class="grid-2">
        <div class="field">
          <label>Estatus</label>
          <select v-model="form.estatus">
            <option value="activo">Activo</option>
            <option value="suspendido">Suspendido</option>
          </select>
        </div>
      </div>

      <!-- MÓDULOS (EDITAR) - Diseño mejorado -->
      <div class="field" style="grid-column: 1 / -1; margin-top: 1.5rem;">
        <label class="section-label">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7" rx="1"/>
            <rect x="14" y="3" width="7" height="7" rx="1"/>
            <rect x="3" y="14" width="7" height="7" rx="1"/>
            <rect x="14" y="14" width="7" height="7" rx="1"/>
          </svg>
          Módulos asignados
        </label>
        <div class="modulos-grid">
          <label v-for="modulo in modulosDisponibles" :key="modulo.id" class="modulo-card" :class="{ selected: form.modulos.includes(modulo.id) }">
            <input type="checkbox" :value="modulo.id" v-model="form.modulos" class="modulo-checkbox">
            <div class="modulo-content">
              <div class="modulo-icon">
                <svg v-if="modulo.clave === 'medicina_trabajo'" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                  <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <svg v-else-if="modulo.clave === 'expediente_clinico'" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14,2 14,8 20,8"/>
                  <line x1="16" y1="13" x2="8" y2="13"/>
                  <line x1="16" y1="17" x2="8" y2="17"/>
                  <polyline points="10,9 9,9 8,9"/>
                </svg>
                <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="3" width="18" height="18" rx="2"/>
                  <path d="M3 9h18"/>
                  <path d="M9 21V9"/>
                </svg>
              </div>
              <div class="modulo-info">
                <span class="modulo-name">{{ modulo.nombre }}</span>
              </div>
            </div>
            <div class="modulo-check">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <polyline points="20,6 9,17 4,12"/>
              </svg>
            </div>
          </label>
        </div>
      </div>

      <div class="form-actions">
        <button class="btn btn-ghost" @click="handleCancelEdit">Cancelar</button>
        <div class="right"><button class="btn btn-primary" @click="actualizarCliente(form.id)">Guardar cambios</button></div>
      </div>
    </template>
  </div>
</template>

<script>
import ApiService from '../../services/ApiService.js'

export default {
  name: 'TenantForm',
  props: {
    modo: {
      type: String,
      default: 'crear',
      validator: (v) => ['crear', 'editar'].includes(v)
    },
    tenant: {
      type: [Object, Number, String],
      default: () => ({})
    }
  },
  data() {
    return {
      cliente: null,
      cargando: false,
      mensajeCarga: 'Confirmar y registrar',
      currentStep: 1,
      showToast: false,
      toastMessage: '',
      initialForm: null,
      modulosDisponibles: [],
      form: {
        id: '',
        folio: '',
        nombre_consultorio: '',
        db_name: '',
        dominio_correo: '',
        estatus: '',
        modulos: [] // Array de IDs
      },
      guardarRegistro: {
        nombre_consultorio: '',
        db_name: '',
        dominio_correo: '',
        estatus: '',
        modulos: [] // Array de IDs
      }
    }
  },
  mounted() {
    this.cargarModulosDisponibles();
    if (this.modo === 'editar') {
      this.clienteIndividual(this.tenant);
    }
  },
  created() {
    if (this.modo === 'editar') {
      this.initialForm = { ...this.form };
    }
  },
  computed: {
    generatedDbName() {
      const nombre = this.guardarRegistro?.nombre_consultorio || this.form?.nombre_consultorio || 'Cliente';
      const cleanName = nombre.trim().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, " ").replace(/\s+/g, '_');
      return `medico_online_${cleanName}`;
    },
    hasUnsavedChanges() {
      if (this.modo !== 'editar' || !this.initialForm) return false;
      return JSON.stringify(this.form) !== JSON.stringify(this.initialForm);
    }
  },
  methods: {
    async cargarModulosDisponibles() {
      try {
        const response = await ApiService.get('modulos');
        this.modulosDisponibles = response.data;
        console.log('Módulos cargados:', this.modulosDisponibles);
      } catch (error) {
        console.error('Error al cargar módulos:', error);
      }
    },

    async clienteIndividual(id) {
      try {
        const response = await ApiService.get(`inquilinos/${id}`);
        this.cliente = response.data;
        
        // CORRECCIÓN: Asegurar que los módulos se carguen correctamente
        const modulosAsignados = this.cliente.modulos || [];
        const modulosIds = modulosAsignados.map(m => m.id);
        
        console.log('Tenant cargado:', this.cliente);
        console.log('Módulos asignados (IDs):', modulosIds);
        
        this.form = {
          id: this.cliente.id,
          folio: this.cliente.folio,
          nombre_consultorio: this.cliente.nombre_consultorio,
          db_name: this.cliente.db_name,
          dominio_correo: this.cliente.dominio_correo,
          estatus: this.cliente.estatus,
          modulos: modulosIds // ← Aquí se asignan los IDs
        };
      } catch (error) {
        console.error('Error al obtener cliente:', error);
      }
    },

    async actualizarCliente(id) {
      if (!id) return;
      try {
        const payload = { ...this.form, modulos: this.form.modulos };
        const response = await ApiService.put(`inquilinos/${id}`, payload);
        this.cliente = response.data.data;
        
        this.toastMessage = '¡Cliente actualizado con éxito!';
        this.showToast = true;
        setTimeout(() => {
          this.showToast = false;
          this.$emit('tenant-updated', this.cliente);
        }, 1200);
      } catch (error) {
        console.error('Error al actualizar el cliente:', error);
        this.toastMessage = 'Error al actualizar el cliente.';
        this.showToast = true;
        setTimeout(() => { this.showToast = false; }, 2000);
      }
    },

    async guardarCliente() {
      try {
        this.cargando = true;
        this.mensajeCarga = 'Creando la base de datos, espere...';
        this.guardarRegistro.db_name = this.generatedDbName;

        const payload = { ...this.guardarRegistro, modulos: this.guardarRegistro.modulos };
        const response = await ApiService.post('inquilinos', payload);
        
        this.mensajeCarga = '¡Registro completado!';
        this.toastMessage = '¡Cliente Registrado y Base de Datos creada con éxito!';
        this.showToast = true;

        setTimeout(() => {
          this.showToast = false;
          this.cargando = false;
          this.mensajeCarga = 'Confirmar y registrar';
          this.$emit('tenant-created', response.data.data.cliente);
        }, 1500);
      } catch (error) {
        console.error('Error al guardar el cliente:', error);
        this.toastMessage = 'Error al registrar el cliente.';
        this.showToast = true;
        this.cargando = false;
        setTimeout(() => { this.showToast = false; }, 2000);
      }
    },

    handleCancelEdit() {
      if (this.hasUnsavedChanges) {
        if (!window.confirm('Tienes cambios sin guardar. ¿Deseas descartarlos?')) return;
      }
      this.$emit('cancel');
    }
  }
}
</script>

<style scoped>
/* SECCIÓN LABEL */
.section-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  font-size: 0.9rem;
  color: #1e293b;
  margin-bottom: 0.75rem;
}

.section-label svg {
  color: #0d9488;
}

/* GRID DE MÓDULOS */
.modulos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 1rem;
}

/* TARJETA DE MÓDULO */
.modulo-card {
  position: relative;
  display: flex;
  align-items: center;
  padding: 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 0.2s ease;
  background: #fff;
  min-height: 80px;
}

.modulo-card:hover {
  border-color: #0d9488;
  box-shadow: 0 4px 12px rgba(13, 148, 136, 0.1);
  transform: translateY(-2px);
}

.modulo-card.selected {
  border-color: #0d9488;
  background: linear-gradient(135deg, #f0fdfa 0%, #fff 100%);
  box-shadow: 0 4px 12px rgba(13, 148, 136, 0.15);
}

/* CHECKBOX OCULTO */
.modulo-checkbox {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

/* CONTENIDO DEL MÓDULO */
.modulo-content {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex: 1;
}

/* ICONO DEL MÓDULO */
.modulo-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 0.5rem;
  background: #f1f5f9;
  color: #64748b;
  transition: all 0.2s;
}

.modulo-card.selected .modulo-icon {
  background: #0d9488;
  color: #fff;
}

/* INFO DEL MÓDULO */
.modulo-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.modulo-name {
  font-weight: 600;
  font-size: 0.95rem;
  color: #1e293b;
}

.modulo-desc {
  font-size: 0.8rem;
  color: #64748b;
  line-height: 1.3;
}

/* CHECK VISIBLE */
.modulo-check {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #e2e8f0;
  color: #fff;
  opacity: 0;
  transform: scale(0.8);
  transition: all 0.2s;
}

.modulo-card.selected .modulo-check {
  background: #0d9488;
  opacity: 1;
  transform: scale(1);
}

/* BADGE */
.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  background: #0d9488;
  color: #fff;
  border-radius: 9999px;
  font-size: 0.85rem;
  font-weight: 600;
}
</style>