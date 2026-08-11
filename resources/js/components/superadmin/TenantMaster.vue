<template>
  <div class="app">
    <!-- Topbar fijo -->
    <header-bar></header-bar>

    <!-- Cabecera de página y botón de acción -->
    <div class="page-head">
      <div>
        <h1>{{ isFormView ? 'Registrar cliente' : 'Gestión de clientes' }}</h1>
        <p>{{ isFormView ? 'Complete los pasos para dar de alta un nuevo inquilino' : 'Administración de consultorios registrados en la plataforma' }}</p>
      </div>
      <button v-if="!isFormView" class="btn btn-primary" @click="currentView = 'form'">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 5v14M5 12h14"/></svg>
        Registrar cliente
      </button>
      <button v-else class="btn btn-ghost" @click="currentView = 'list'">
        Cancelar
      </button>
    </div>

    <!-- Estadísticas superiores (Componente hijo opcional o sección directa) -->
    <stats-counters :total="totalCount" :active="activeCount" :inactive="inactiveCount"></stats-counters>

    <!-- Contenedor Principal (Cambia dinámicamente entre Lista y Formulario) -->
    <div class="card">
      <tenant-list 
        v-if="!isFormView" 
        :tenants="tenants" 
        @go-to-form="currentView = 'form'"
      ></tenant-list>

      <tenant-form 
        v-else 
        @tenant-created="handleTenantCreated"
        @cancel="currentView = 'list'"
      ></tenant-form>
    </div>
  </div>
</template>

<script>
import HeaderBar from './HeaderBar.vue';
import StatsCounters from './TenantEstadistica.vue';
import TenantList from './TenantLista.vue';
import TenantForm from './TenantRegistro.vue';

export default {
  name: 'tenant-master-contenedor',
  components: {
    HeaderBar,
    StatsCounters,
    TenantList,
    TenantForm
  },
  data() {
    return {
      currentView: 'list', // 'list' o 'form'
      tenants: [
        { folio: 'CONSULTORIO-2026-001', name: 'UltraConsultorio', db: 'medico_online_Ultra_Consultorio', domain: 'ultraconsultorio.com', status: 'activo' },
        { folio: 'CONSULTORIO-2026-002', name: 'Clínica San Rafael', db: 'medico_online_Clinica_San_Rafael', domain: 'sanrafael.com', status: 'activo' },
        { folio: 'CONSULTORIO-2026-003', name: 'Consultorio Médico Vida', db: 'medico_online_Consultorio_Vida', domain: 'vida.com', status: 'inactivo' }
      ]
    }
  },
  computed: {
    isFormView() {
      return this.currentView === 'form';
    },
    totalCount() {
      return this.tenants.length + 11; // Simulado con la base estática original
    },
    activeCount() {
      return this.tenants.filter(t => t.status === 'activo').length + 11;
    },
    inactiveCount() {
      return this.tenants.filter(t => t.status === 'inactivo').length;
    }
  },
  methods: {
    handleTenantCreated(newTenant) {
      const folioNum = String(this.tenants.length + 4).padStart(3, '0');
      this.tenants.unshift({
        folio: `CONSULTORIO-2026-${folioNum}`,
        ...newTenant
      });
      this.currentView = 'list';
    }
  }
}
</script>