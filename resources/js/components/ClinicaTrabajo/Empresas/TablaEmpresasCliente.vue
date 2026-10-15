<template>
  <div class="container-fluid mt-4">
    <!-- Tarjetas Resumen -->
    <div class="row mb-4">
      <div class="col-md-4">
        <div class="small-box bg-info" @click="filtrarPorEstado('')" style="cursor: pointer;">
          <div class="inner">
            <h3>{{ totalEmpresas }}</h3>
            <p>Total Empresas</p>
          </div>
          <div class="icon">
            <i class="fas fa-hospital"></i>
          </div>
        </div>
      </div>
      
      <!-- Tarjeta Activas -->
      <div class="col-md-4">
        <div class="small-box bg-success" @click="filtrarPorEstado('1')" style="cursor: pointer;">
          <div class="inner">
            <h3>{{ empresasActivas }}</h3>
            <p>Activas</p>
          </div>
          <div class="icon">
            <i class="fas fa-check-circle"></i>
          </div>
        </div>
      </div>
      
      <!-- Tarjeta Inactivas -->
      <div class="col-md-4">
        <div class="small-box bg-warning" @click="filtrarPorEstado('0')" style="cursor: pointer;">
          <div class="inner">
            <h3>{{ empresasInactivas }}</h3>
            <p>Inactivas</p>
          </div>
          <div class="icon">
            <i class="fas fa-user-clock"></i>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Tabla -->
    <div class="card shadow">
      <div class="card-header bg-white border-bottom">
        <div class="row align-items-center">
          <div class="col-md-6">
            <h3 class="card-title m-0 text-dark">
              <i class="fas fa-list text-primary mr-2"></i>
              Lista de Empresas
            </h3>
          </div>
          <div class="col-md-6">
            <div class="input-group">
              <input
                v-model="busqueda"
                type="text"
                class="form-control"
                placeholder="Buscar por razón social, RFC o folio..."
                @keyup.enter="buscarEmpresas"
              />
              <div class="input-group-append">
                <button class="btn btn-outline-primary" @click="buscarEmpresas">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div v-if="cargando" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="text-muted mt-2">Cargando empresas...</p>
      </div>
      
      <div v-else-if="!cargando && empresas.length === 0" class="text-center py-5">
        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
        <p class="text-muted mb-0">No hay empresas registradas</p>
        <small class="text-muted">Haga clic en "Registrar Empresa" para agregar una nueva</small>
      </div>
      
      <div v-else-if="!cargando" class="table-responsive-custom card-body p-0">
        <table class="table table-hover mb-0">
          <thead class="thead-light">
            <tr>
              <th class="d-none d-md-table-cell">Folio</th>
              <th>Razón social</th>
              <th class="d-none d-lg-table-cell">RFC</th>
              <th class="d-none d-xl-table-cell">Giro de actividad</th>
              <th>Contacto</th>
              <th class="d-none d-sm-table-cell">Estado</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="empresa in empresasFiltradas" :key="empresa.id">
              <td class="d-none d-md-table-cell"><code class="text-monospace">{{ empresa.folio }}</code></td>
              <td>
                <div class="table-cell-content">
                  <strong class="d-block">{{ empresa.razon_social }}</strong>
                  <small class="text-muted d-lg-none d-block">{{ empresa.rfc || '' }}</small>
                </div>
              </td>
              <td class="d-none d-lg-table-cell">{{ empresa.rfc || '-' }}</td>
              <td class="d-none d-xl-table-cell text-wrap">{{ empresa.giro_actividad || '-' }}</td>
              <td>
                <div class="contacto-cell">
                  <div v-if="empresa.contacto_nombre || empresa.contacto_telefono">
                    <div v-if="empresa.contacto_nombre" class="contacto-nombre text-wrap" :title="empresa.contacto_nombre">
                      <i class="fas fa-user-tie text-primary mr-1"></i>
                      <strong>{{ empresa.contacto_nombre }}</strong>
                    </div>
                    <div v-if="empresa.contacto_telefono" class="contacto-telefono small text-muted">
                      <i class="fas fa-phone mr-1"></i>{{ empresa.contacto_telefono }}
                    </div>
                  </div>
                  <div v-else class="text-muted">-</div>
                </div>
              </td>
              <td class="d-none d-sm-table-cell">
                <span
                  class="badge px-2 py-1"
                  :class="empresa.activo == 1 ? 'badge-success' : 'badge-secondary'"
                >
                  <i :class="empresa.activo == 1 ? 'fas fa-check-circle' : 'fas fa-ban'" class="mr-1"></i>
                  {{ empresa.activo == 1 ? 'Activa' : 'Inactiva' }}
                </span>
              </td>
              <td class="text-center">
                <div class="btn-group-actions">
                  <button
                    v-if="empresa.activo == 1"
                    class="btn btn-sm btn-outline-danger"
                    @click="eliminarEmpresa(empresa.id)"
                    title="Dar de baja"
                  >
                    <i class="fas fa-ban d-none d-sm-inline"></i>
                    <i class="fas fa-ban d-sm-none"></i>
                  </button>
                  <button
                    v-else
                    class="btn btn-sm btn-outline-success"
                    @click="reactivarEmpresa(empresa.id)"
                    title="Reactivar"
                  >
                    <i class="fas fa-undo d-none d-sm-inline"></i>
                    <i class="fas fa-undo d-sm-none"></i>
                  </button>
                  <button
                    class="btn btn-sm btn-warning"
                    @click="editarEmpresa(empresa.id)"
                    title="Editar"
                  >
                    <i class="fas fa-edit d-none d-sm-inline"></i>
                    <i class="fas fa-edit d-sm-none"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Paginación -->
      <div v-if="!cargando && lista.last_page > 1" class="card-footer d-flex justify-content-between align-items-center bg-white flex-wrap">
        <div class="mb-2 mb-md-0">
          <small class="text-muted">
            Mostrando {{ empresas.length }} de {{ lista.total }} empresas
          </small>
        </div>
        <nav aria-label="Page navigation">
          <ul class="pagination mb-0">
            <li class="page-item" :class="{'disabled': lista.current_page <= 1}">
              <a class="page-link" href="#" @click.prevent="paginaAnterior">«</a>
            </li>
            <li class="page-item" v-for="pagina in paginas" :key="pagina"
               :class="{'active': pagina === lista.current_page}">
              <a class="page-link" href="#" @click.prevent="irAPagina(pagina)">{{ pagina }}</a>
            </li>
            <li class="page-item" :class="{'disabled': lista.current_page >= lista.last_page}">
              <a class="page-link" href="#" @click.prevent="paginaSiguiente">»</a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    empresas: {
      type: Array,
      default: () => []
    },
    cargando: {
      type: Boolean,
      default: false
    },
    lista: {
      type: Object,
      default: () => ({
        data: [],
        current_page: 1,
        last_page: 1,
        total: 0
      })
    }
  },
  data() {
    return {
      busqueda: ''
    }
  },
  computed: { 
    empresasFiltradas() {
      if (!this.busqueda.trim()) {
        return this.empresas;
      }

      const query = this.busqueda.toLowerCase();
      return this.empresas.filter(empresa =>
        (empresa.razon_social && empresa.razon_social.toLowerCase().includes(query)) ||
        (empresa.rfc && empresa.rfc.toLowerCase().includes(query)) ||
        (empresa.folio && empresa.folio.toLowerCase().includes(query))
      );
    },

    totalEmpresas() {
      return this.lista.total || 0;
    },

    empresasActivas() {
      return this.lista.data.filter(emp => emp.activo == 1).length || 0;
    },

    empresasInactivas() {
      return this.lista.data.filter(emp => emp.activo == 0).length || 0;
    },

    paginas() {
      const paginas = [];
      for (let i = 1; i <= this.lista.last_page; i++) {
        paginas.push(i);
      }
      return paginas;
    }
  },
  methods: {
    filtrarPorEstado(estado) {
      this.$emit('filtrar-por-estado', estado);
    },

    buscarEmpresas() {
      this.$emit('buscar-empresas', this.busqueda);
    },

    paginaAnterior() {
      if (this.lista.current_page > 1) {
        this.$emit('pagina-cambiada', this.lista.current_page - 1);
      }
    },

    paginaSiguiente() {
      if (this.lista.current_page < this.lista.last_page) {
        this.$emit('pagina-cambiada', this.lista.current_page + 1);
      }
    },

    irAPagina(pagina) {
      this.$emit('pagina-cambiada', pagina);
    },

    editarEmpresa(id) {
      this.$emit('editar', id);
    },

    eliminarEmpresa(id) {
      this.$emit('eliminar', id);
    },

    reactivarEmpresa(id) {
      this.$emit('editar', id);
    }
  }
}
</script>

<style scoped>
.small-box {
  border-radius: 10px;
  padding: 20px;
  position: relative;
  overflow: hidden;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  color: white;
}

.small-box:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.small-box .inner {
  flex: 1;
}

.small-box .inner h3 {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0;
}

.small-box .inner p {
  font-size: 1rem;
  margin: 5px 0 0 0;
  opacity: 0.9;
}

.small-box .icon {
  font-size: 4rem;
  opacity: 0.3;
}

.bg-info {
  background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
}

.bg-success {
  background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
}

.bg-warning {
  background: linear-gradient(135deg, #ffc107 0%, #d97706 100%);
}

.card-header {
  border-bottom: 2px solid #e9ecef;
}

.card-title i {
  color: #0061f2;
}

.btn-outline-primary {
  border-color: #0061f2;
  color: #0061f2;
}

.btn-outline-primary:hover {
  background-color: #0061f2;
  border-color: #0061f2;
  color: white;
}

.table thead th {
  border-top: none;
  font-weight: 600;
  color: #495057;
  background-color: #f8f9fa;
  white-space: nowrap;
  font-size: 0.85rem;
}

/* Mejoras de responsividad */
.table-responsive-custom {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.table-responsive-custom table {
  min-width: 100%;
  width: 100%;
}

.table-responsive-custom td {
  vertical-align: middle;
  font-size: 0.9rem;
  white-space: normal;
  word-wrap: break-word;
  overflow-wrap: break-word;
  max-width: 300px;
  padding: 0.75rem;
}

.table-cell-content {
  max-width: 250px;
  overflow: hidden;
}

/* Estilos específicos para la columna Contacto */
.contacto-cell {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  min-width: 150px;
}

.contacto-nombre {
  white-space: normal;
  word-wrap: break-word;
  overflow-wrap: break-word;
  line-height: 1.4;
}

.contacto-telefono {
  white-space: normal;
  word-wrap: break-word;
  overflow-wrap: break-word;
  line-height: 1.3;
}

.text-wrap {
  white-space: normal !important;
  word-wrap: break-word;
  max-width: 200px;
}

.badge-success {
  background-color: #28a745;
}

.badge-secondary {
  background-color: #6c757d;
}

code.text-monospace {
  background-color: #e9ecef;
  padding: 3px 6px;
  border-radius: 4px;
  font-size: 0.85rem;
  white-space: nowrap;
}

/* Ajustes para botones en móvil */
.btn-group-actions {
  display: inline-flex;
  gap: 2px;
}

.btn-group-actions .btn {
  padding: 0.25rem 0.4rem;
  font-size: 0.8rem;
}

/* Media queries para mejorar responsividad */
@media (max-width: 768px) {
  .table-responsive-custom td,
  .table-responsive-custom th {
    padding: 0.5rem;
    font-size: 0.85rem;
  }
  
  .btn-group-actions .btn {
    padding: 0.4rem;
    min-width: 36px;
  }
  
  .contacto-cell {
    min-width: 120px;
  }
}

@media (max-width: 576px) {
  .card-header .row {
    flex-direction: column;
    gap: 1rem;
  }
  
  .card-header .col-md-6 {
    width: 100%;
  }
  
  .table-responsive-custom td,
  .table-responsive-custom th {
    padding: 0.4rem;
    font-size: 0.8rem;
  }
  
  .table-cell-content {
    max-width: 150px;
  }
  
  .contacto-nombre,
  .contacto-telefono {
    font-size: 0.85rem;
  }
}
</style>