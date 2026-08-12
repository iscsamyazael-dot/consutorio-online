<template>
  <div class="app">
    <!-- Topbar fijo -->
    <header-bar></header-bar>

    <!-- Cabecera de página y botón de acción -->
    <div class="page-head">
      <div>
        <h1>{{ headTitle }}</h1>
        <p>{{ headSubtitle }}</p>
      </div>
      <button v-if="currentView === 'list'" class="btn btn-primary" @click="abrirCrear">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 5v14M5 12h14"/></svg>
        Registrar cliente
      </button>
      <button v-else class="btn btn-ghost" @click="volverALista">
        Cancelar
      </button>
    </div>

    <!-- Estadísticas superiores -->
    <stats-counters ref="stats" :total="totalCount" :active="activeCount" :inactive="inactiveCount"></stats-counters>

    <!-- Contenedor Principal (Cambia dinámicamente entre Lista y Formulario) -->
    <div class="card">
      <tenant-list
        v-if="currentView === 'list'"
        ref="tenantList"
        @go-to-form="abrirCrear"
        @editar-cliente="abrirEditar"
      ></tenant-list>

      <tenant-form
        v-else
        :modo="currentView === 'editar' ? 'editar' : 'crear'"
        :tenant="selectedTenant"
        @tenant-created="handleTenantCreated"
        @tenant-updated="handleTenantUpdated"
        @cancel="volverALista"
      ></tenant-form>
    </div>
  </div>
</template>

<script>
import ApiService from '../../services/ApiService.js'
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
      currentView: 'list', // 'list' | 'crear' | 'editar'
      selectedTenant: null
    }
  },
  computed: {
    headTitle() {
      if (this.currentView === 'crear') return 'Registrar cliente';
      if (this.currentView === 'editar') return 'Editar cliente';
      return 'Gestión de clientes';
    },
    headSubtitle() {
      if (this.currentView === 'crear') return 'Complete los pasos para dar de alta un nuevo inquilino';
      if (this.currentView === 'editar') return 'Modifique los datos editables del consultorio';
      return 'Administración de consultorios registrados en la plataforma';
    },
    // TODO: estos 3 conteos hoy dependen de la lista real que carga
    // TenantList internamente, no de un array local. Los dejo en 0 por
    // ahora -- ver mi pregunta al final sobre cómo quieres alimentar
    // stats-counters con datos reales (endpoint separado, o exponer la
    // lista de TenantList hacia arriba).
    totalCount() {
      return this.$refs.tenantList?.clientes?.length || 0;
    },
    activeCount() {
      return this.$refs.tenantList?.clientes?.filter(c => c.estatus === 'activo').length || 0;
    },
    inactiveCount() {
      return this.$refs.tenantList?.clientes?.filter(c => c.estatus === 'inactivo').length || 0;
    }
  },
  methods: {
    abrirCrear() {
      this.selectedTenant = null;
      this.currentView = 'crear';
    },
    abrirEditar(id) {
      // Si la tabla le mandó el objeto completo, extraemos el id, si le mandó directo el id, lo usamos
      this.selectedTenant = id;
      this.currentView = 'editar';
    },
    volverALista() {
      this.currentView = 'list';
      this.selectedTenant = null;
    },
    async handleTenantCreated(nuevoCliente) {
      try {
        // Nota: ya no necesitas hacer el post aquí si el hijo ya lo hizo, 
        // solo refrescamos.
        this.volverALista();
        
        // 1. Refrescamos la lista
        await this.$nextTick();
        this.$refs.tenantList?.obtenerClientes();
        
        // 2. REFRESCO DE ESTADÍSTICAS (¡Aquí está la magia!)
        this.$refs.stats?.cargarEstadisticas();
        
      } catch (error) {
        console.error('Error al registrar cliente:', error);
      }
    },
    async handleTenantUpdated(clienteActualizado) {
      try {
      // Como el hijo ya guardó en la base de datos, 
      // solo regresamos a la lista y refrescamos los datos:
      this.volverALista();
      await this.$nextTick();
      this.$refs.tenantList?.obtenerClientes();
      // 2. REFRESCO DE ESTADÍSTICAS
      this.$refs.stats?.cargarEstadisticas();

      } catch (error) {
        console.error('Error al actualizar la vista:', error);
      }
    }
  }
}
</script>