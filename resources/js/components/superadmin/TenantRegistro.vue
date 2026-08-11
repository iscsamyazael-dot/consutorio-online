<template>
  <div class="form-view active">
    <div class="toast" :class="{ show: showToast }">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg>
      <span>Cliente registrado. Creando su base de datos...</span>
    </div>

    <!-- Stepper Navigation -->
    <div class="stepper">
      <div class="step" :class="{ active: currentStep === 1, done: currentStep > 1 }">
        <div class="num">1</div><div><span class="label">Datos del consultorio</span><span class="sub">nombre_consultorio, dominio_correo</span></div>
      </div>
      <div class="step" :class="{ active: currentStep === 2, done: currentStep > 2 }">
        <div class="num">2</div><div><span class="label">Base de datos</span><span class="sub">db_name, estatus</span></div>
      </div>
      <div class="step" :class="{ active: currentStep === 3 }">
        <div class="num">3</div><div><span class="label">Confirmar</span><span class="sub">Crear registro</span></div>
      </div>
    </div>

    <!-- Step 1 -->
    <div class="fieldset" :class="{ active: currentStep === 1 }">
      <div class="grid-2">
        <div class="field"><label>Nombre del consultorio</label><input type="text" v-model="form.name" placeholder="Ej. Consultorio Vida"></div>
        <div class="field"><label>Dominio / correo</label><input type="text" v-model="form.domain" placeholder="ejemplo.com"></div>
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
          <div class="hint">db_name se genera con el patrón medico_online_{nombre_consultorio}</div>
        </div>
        <div class="field">
          <label>Estatus</label>
          <select v-model="form.status">
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
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
        <div class="summary-row"><span>nombre_consultorio</span><span>{{ form.name || 'Sin nombre' }}</span></div>
        <div class="summary-row"><span>dominio_correo</span><span>{{ form.domain || 'sindominio.com' }}</span></div>
        <div class="summary-row"><span>db_name</span><span>{{ generatedDbName }}</span></div>
        <div class="summary-row"><span>estatus</span><span>{{ form.status }}</span></div>
      </div>
      <div class="note">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
        Al confirmar se creará el registro en tenants y la base de datos física del consultorio.
      </div>
      <div class="form-actions">
        <button class="btn btn-ghost" @click="currentStep = 2">Atrás</button>
        <div class="right"><button class="btn btn-primary" @click="submitForm">Confirmar y registrar</button></div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  name: 'TenantForm',
  data() {
    return {
      currentStep: 1,
      showToast: false,
      form: {
        name: 'UltraConsultorio Norte',
        domain: '',
        status: 'activo'
      }
    }
  },
  computed: {
    generatedDbName() {
      const cleanName = (this.form.name || 'Cliente').trim().replace(/\s+/g, '_');
      return `medico_online_${cleanName}`;
    }
  },
  methods: {
    submitForm() {
      this.showToast = true;
      setTimeout(() => {
        this.$emit('tenant-created', {
          name: this.form.name || 'Cliente nuevo',
          db: this.generatedDbName,
          domain: this.form.domain || 'sindominio.com',
          status: this.form.status
        });
      }, 1000);
    }
  }
}
</script>