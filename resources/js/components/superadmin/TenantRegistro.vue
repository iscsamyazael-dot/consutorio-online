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
          <div class="field"><label>Nombre del consultorio</label><input type="text" v-model="guardarRegistro.nombre_consultorio" placeholder="Ej. Consultorio Vida"></div>
          <div class="field"><label>Dominio / correo</label><input type="text" v-model="guardarRegistro.dominio_correo" placeholder="ejemplo.com"></div>
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
            <div class="hint">db_name se genera con el patrón consultorio_online_{nombre_consultorio}</div>
          </div>
          <div class="field">
            <label>Estatus</label>
            <select v-model="guardarRegistro.estatus">
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
          <div class="summary-row"><span>nombre_consultorio</span><span>{{ guardarRegistro.nombre_consultorio || 'Sin nombre' }}</span></div>
          <div class="summary-row"><span>dominio_correo</span><span>{{ guardarRegistro.dominio_correo || 'sindominio.com' }}</span></div>
          <div class="summary-row"><span>db_name</span><span>{{ generatedDbName }}</span></div>
          <div class="summary-row"><span>estatus</span><span>{{ guardarRegistro.estatus }}</span></div>
        </div>
        <div class="note">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
          Al confirmar se creará el registro en tenants y la base de datos física del consultorio.
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
        <!-- Toast de Notificación -->
        <div v-if="showToast" class="toast-notification">
          {{ toastMessage }}
        </div>
      </div>
    </template>

    <!-- ============================================= -->
    <!-- MODO EDITAR: pantalla única, sin stepper       -->
    <!-- ============================================= -->
    <template v-else>
      <div class="grid-2">
        <div class="field">
          <label>Folio</label>
          <input type="text" :value="form.folio" readonly>
        </div>
        <div class="field">
          <label>Nombre del consultorio</label>
          <input type="text" v-model="form.nombre_consultorio" placeholder="Ej. Consultorio Vida">
        </div>
      </div>
      <div class="grid-2">
        <div class="field">
          <label>Base de datos</label>
          <input type="text" :value="form.db_name" readonly>
        </div>
        <div class="field">
          <label>Dominio / correo</label>
          <input type="text" :value="form.dominio_correo" readonly>
        </div>
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
    // 'crear' -> wizard de 3 pasos. 'editar' -> pantalla única.
    modo: {
      type: String,
      default: 'crear',
      validator: (v) => ['crear', 'editar'].includes(v)
    },
    // Solo se usa en modo 'editar'. Se precarga el formulario con estos datos.
    tenant: {
      type: [Object, Number, String],
      default: () => ({})
    }
  },
  data() {
    return {
      cliente:null,
      cargando:false,
      mensajeCarga: 'Confirmar y registrar',
      currentStep: 1,
      showToast: false,
      toastMessage: '',
      // snapshot inicial para poder detectar cambios sin guardar en modo editar
      initialForm: null,
      form: {
        id: '',
        folio: '',
        nombre_consultorio: '',
        db_name: '',
        dominio_correo: '',
        estatus: ''
      },
      guardarRegistro:{
        nombre_consultorio:'',
        db_name:'',
        dominio_correo:'',
        estatus:''
      }
    }
  },
  mounted(){
    if (this.modo === 'editar') {
      this.clienteIndividual(this.tenant); // <-- ¡Aquí le pasas el ID que viene de la prop!
    }
  },
  created() {
    if (this.modo === 'editar') {
      this.initialForm = { ...this.form };
    }
  },
  computed: {
    generatedDbName() {
      // Tomamos el campo correcto dependiendo de si estás creando o editando
      const nombre = this.guardarRegistro?.nombre_consultorio || this.form?.nombre_consultorio || 'Cliente';
      const cleanName = nombre.trim().toLowerCase()
        .normalize("NFD").replace(/[\u0300-\u036f]/g, "") // Limpia acentos por si acaso
        .replace(/\s+/g, '_');                           // Cambia espacios por guiones bajos
      return `medico_online_${cleanName}`;
    },
    hasUnsavedChanges() {
      if (this.modo !== 'editar' || !this.initialForm) return false;
      return this.form.name !== this.initialForm.name || this.form.status !== this.initialForm.status;
    }
  },
  methods: {
    submitCreate() {
      this.toastMessage = 'Cliente registrado. Creando su base de datos...';
      this.showToast = true;
      setTimeout(() => {
        this.$emit('tenant-created', {
          name: this.form.name || 'Cliente nuevo',
          db: this.generatedDbName,
          domain: this.form.domain || 'sindominio.com',
          status: this.form.status
        });
      }, 1000);
    },
    submitEdit() {
      this.toastMessage = 'Cambios guardados.';
      this.showToast = true;
      setTimeout(() => {
        this.$emit('tenant-updated', {
          folio: this.form.folio,
          name: this.form.name,
          db: this.form.db,
          domain: this.form.domain,
          status: this.form.status
        });
      }, 800);
    },
    handleCancelEdit() {
      if (this.hasUnsavedChanges) {
        const confirmar = window.confirm('Tienes cambios sin guardar. ¿Deseas descartarlos?');
        if (!confirmar) return;
      }
      this.$emit('cancel');
    },

    async clienteIndividual(id){
      try {
          const response = await ApiService.get(`/inquilinos/${id}`);
          // Asignas directamente la lista que te da el servidor
          this.cliente = response.data;
          this.form = {
            id:this.cliente.id,
            folio:this.cliente.folio,
            nombre_consultorio: this.cliente.nombre_consultorio,
            db_name:this.cliente.db_name,
            dominio_correo: this.cliente.dominio_correo,
            estatus: this.cliente.estatus
          };
          console.log('Cliente individual encontrado:', this.cliente);
      } catch (error) {
          console.error('Error al obtener lista de clientes:', error);
      }
    },

    async actualizarCliente(id){
      console.log("ID recibido para actualizar:", id); // <-- Mira esto en la consola
      if (!id) {
        console.error("¡El ID está undefined!");
        return;
      }
      try {
          const response = await ApiService.put(`/inquilinos/${id}`, this.form);
        // Asignas directamente los datos que te da el servidor
          this.cliente = response.data;
          console.log('Cliente actualizado:', this.cliente);

          // Activamos el Toast de éxito
          this.toastMessage = '¡Cliente actualizado con éxito!';
          this.showToast = true;

          // Opcional: Esperar un momento para que se alcance a ver el toast y regresar o limpiar
          setTimeout(() => {
              this.showToast = false;
              this.$emit('tenant-updated', this.cliente); // O el evento que uses para volver a la lista
          }, 1200);
      } catch (error) {
          console.error('Error al actualizar el cliente:', error);
          // Toast de error por si algo falla
          this.toastMessage = 'Error al actualizar el cliente.';
          this.showToast = true;
          setTimeout(() => {
              this.showToast = false;
          }, 2000);
      }
    },

    async guardarCliente(){
      try {
          //Mensaje de espera mientras se crea la base de datos
          this.cargando = true;
          this.mensajeCarga = 'Creando la base de datos, espere...'
          // Aseguramos que el db_name calculado se vaya en el objeto que mandamos al backend
          this.guardarRegistro.db_name = this.generatedDbName;

          const response = await ApiService.post('inquilinos', this.guardarRegistro);
          console.log('Cliente guardado:', response.data);

          // Activamos el Toast de éxito
          this.mensajeCarga = '¡Registro completado!'
          this.toastMessage = '¡Cliente Registrado y Base de Datos creada con éxito!';
          this.showToast = true;

          // Opcional: Esperar un momento para que se alcance a ver el toast y regresar o limpiar
          setTimeout(() => {
              this.showToast = false;
              this.cargando = false;
              this.mensajeCarga = 'Confirmar y registrar';
              this.$emit('tenant-updated', response.data.data.cliente); // O el evento que uses para volver a la lista
          }, 1500);

      } catch (error) {
          console.error('Error al guardar el cliente:', error);
          // Toast de error por si algo falla
          this.toastMessage = 'Error al actualizar el cliente.';
          this.showToast = true;
          setTimeout(() => {
              this.showToast = false;
          }, 2000);
      }
    }

  }
  
}
</script>