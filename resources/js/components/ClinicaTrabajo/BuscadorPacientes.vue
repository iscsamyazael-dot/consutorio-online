<template>
  <div class="buscador-pacientes-container">
    <!-- HEADER -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-primary text-white">
        <h3 class="mb-0">
          <i class="fas fa-search mr-2"></i>
          Buscador Inteligente de Pacientes
        </h3>
        <p class="mb-0 mt-2 small opacity-75">
          Seleccione un paciente y el tipo de evaluación para iniciar la consulta
        </p>
      </div>
      <div class="card-body">
        <!-- FILTROS -->
        <div class="row mb-4">
          <!-- Filtro: Empresa -->
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold d-block mb-1">
              <i class="fas fa-building mr-1"></i> Empresa
            </label>
            <select 
              v-model="filtroEmpresa" 
              class="form-control" 
              @change="buscarPacientes"
              :disabled="cargandoEmpresas"
            >
              <option value="">Todas las empresas</option>
              <option v-for="empresa in empresas" :key="empresa.id" :value="empresa.id">
                {{ empresa.razon_social }}
              </option>
            </select>
          </div>

          <!-- Filtro: Búsqueda libre -->
          <div class="col-md-5 mb-3">
            <label class="font-weight-bold d-block mb-1">
              <i class="fas fa-search mr-1"></i> Buscar por nombre o No. de Empleado
            </label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
              </div>
              <input 
                type="text" 
                v-model="busqueda" 
                class="form-control" 
                placeholder="Ej: Juan Pérez o EMP-2024-001"
                @input="debounceBuscar"
              />
            </div>
          </div>

          <!-- Botón: Limpiar -->
          <div class="col-md-3 mb-3 d-flex align-items-end">
            <button class="btn btn-secondary w-100" @click="limpiarFiltros" :disabled="cargando">
              <i class="fas fa-eraser mr-1"></i> Limpiar Filtros
            </button>
          </div>
        </div>

        <!-- SELECCIÓN DE TIPO DE EVALUACIÓN -->
        <div class="row mb-3">
          <div class="col-12">
            <label class="font-weight-bold d-block mb-2">
              <i class="fas fa-clipboard-list mr-1"></i> Tipo de Evaluación a Realizar:
            </label>
            <div class="row">
              <div class="col-md-2 col-sm-4 mb-2" v-for="tipo in tiposEvaluacion" :key="tipo.value">
                <button 
                  class="btn w-100"
                  :class="tipoEvaluacion === tipo.value ? 'btn-success' : 'btn-outline-success'"
                  @click="tipoEvaluacion = tipo.value"
                  style="min-height: 60px; display: flex; flex-direction: column; align-items: center; justify-content: center;"
                >
                  <i :class="tipo.icono" style="font-size: 1.5rem; margin-bottom: 5px;"></i>
                  <small>{{ tipo.label }}</small>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- RESULTADOS -->
    <div class="card shadow-sm">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="fas fa-users mr-2"></i>
          Pacientes Encontrados ({{ totalPacientes }})
        </h5>
        <div>
          <span v-if="cargando" class="badge badge-info mr-2">
            <i class="fas fa-spinner fa-spin mr-1"></i> Buscando...
          </span>
          <span v-if="totalPacientes > 0" class="badge badge-secondary">
            Página {{ paginaActual }} de {{ totalPaginas }}
          </span>
        </div>
      </div>
      
      <div class="card-body p-0">
        <!-- Estado: Cargando -->
        <div v-if="cargando && pacientes.length === 0" class="text-center p-5">
          <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
          <p class="text-muted">Buscando pacientes en la base de datos...</p>
        </div>

        <!-- Estado: Sin resultados -->
        <div v-else-if="!cargando && pacientes.length === 0" class="text-center p-5 text-muted">
          <i class="fas fa-user-slash fa-3x mb-3"></i>
          <p>No se encontraron pacientes con los filtros seleccionados.</p>
          <small>Intenta ampliar la búsqueda o seleccionar "Todas las empresas".</small>
        </div>

        <!-- Estado: Tabla de resultados -->
        <div v-else class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead class="bg-light">
              <tr>
                <th class="p-3">Paciente</th>
                <th class="p-3">No. Empleado</th>
                <th class="p-3">Empresa</th>
                <th class="p-3">Puesto</th>
                <th class="p-3">Última Evaluación</th>
                <th class="p-3 text-center">Acción</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="paciente in pacientes" :key="paciente.id" class="border-bottom">
                <td class="p-3">
                  <strong class="d-block">{{ paciente.nombre || 'Sin nombre' }}</strong>
                  <small class="text-muted">{{ paciente.folio || 'N/A' }} • {{ paciente.edad || 'N/A' }} años</small>
                </td>
                <td class="p-3">
                  <span class="badge badge-secondary">{{ paciente.numero_empleado || 'N/A' }}</span>
                </td>
                <td class="p-3">
                  {{ paciente.empresa?.razon_social || 'Sin empresa' }}
                </td>
                <td class="p-3">
                  {{ paciente.puesto?.nombre || 'Sin puesto' }}
                </td>
                <td class="p-3">
                  <div v-if="paciente.ultima_evaluacion">
                    <span class="badge" :class="getBadgeClass(paciente.ultima_evaluacion.tipo)">
                      {{ paciente.ultima_evaluacion.tipo }}
                    </span>
                    <br>
                    <small class="text-muted">{{ formatearFecha(paciente.ultima_evaluacion.fecha) }}</small>
                  </div>
                  <span v-else class="text-muted small">Sin evaluaciones previas</span>
                </td>
                <td class="p-3 text-center">
                  <button
                    class="btn btn-success btn-sm"
                    @click="iniciarEvaluacion(paciente)"
                    :disabled="!tipoEvaluacion"
                    :title="tipoEvaluacion ? 'Iniciar evaluación' : 'Seleccione un tipo de evaluación arriba'"
                  >
                    <i class="fas fa-play mr-1"></i>
                    Iniciar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- PAGINACIÓN -->
        <div v-if="totalPaginas > 1" class="card-footer bg-light">
          <nav aria-label="Navegación de páginas">
            <ul class="pagination justify-content-center mb-0">
              <li class="page-item" :class="{ disabled: paginaActual === 1 }">
                <button class="page-link" @click="cambiarPagina(paginaActual - 1)">
                  <i class="fas fa-chevron-left"></i> Anterior
                </button>
              </li>
              
              <li 
                v-for="pagina in paginasVisibles" 
                :key="pagina"
                class="page-item"
                :class="{ active: pagina === paginaActual }"
              >
                <button class="page-link" @click="cambiarPagina(pagina)">
                  {{ pagina }}
                </button>
              </li>
              
              <li class="page-item" :class="{ disabled: paginaActual === totalPaginas }">
                <button class="page-link" @click="cambiarPagina(paginaActual + 1)">
                  Siguiente <i class="fas fa-chevron-right"></i>
                </button>
              </li>
            </ul>
          </nav>
          <div class="text-center mt-2">
            <small class="text-muted">
              Mostrando {{ (paginaActual - 1) * porPagina + 1 }} - {{ Math.min(paginaActual * porPagina, totalPacientes) }} de {{ totalPacientes }} pacientes
            </small>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import clinicaTrabajo from '@/services/ApiServiceClinicaTrabajo'

export default {
  name: 'BuscadorPacientes',
  data() {
    return {
      busqueda: '',
      filtroEmpresa: '',
      tipoEvaluacion: '',
      pacientes: [],
      empresas: [],
      cargando: false,
      cargandoEmpresas: false,
      debounceTimer: null,
      
      // Paginación
      paginaActual: 1,
      porPagina: 20,
      totalPacientes: 0,
      
      tiposEvaluacion: [
        { value: 'nutricion', label: 'Nutrición', icono: 'fas fa-apple-alt', ruta: '/ficha-nutricion' },
        { value: 'ergonomia', label: 'Ergonomía', icono: 'fas fa-chair', ruta: '/clinica/ergonomia/nueva' },
        { value: 'psicologia', label: 'Psicología', icono: 'fas fa-brain', ruta: '/ficha-psicologia' },
        { value: 'audiologia_hc', label: 'Audio. Historia Clínica', icono: 'fas fa-file-medical', ruta: '/FichaDelliAudiologia' },
        { value: 'audiologia_ar', label: 'Audio. Alto Riesgo', icono: 'fas fa-exclamation-triangle', ruta: '/FichaTrabajoAltoRiesgo' },
        { value: 'espirometria', label: 'Espirometría', icono: 'fas fa-lungs', ruta: '/ficha-espirometria' },
        { value: 'ocupacional', label: 'Ocupacional', icono: 'fas fa-briefcase-medical', ruta: '/ficha-ocupacional' }
      ]
    }
  },
  computed: {
    totalPaginas() {
      return Math.ceil(this.totalPacientes / this.porPagina)
    },
    paginasVisibles() {
      const total = this.totalPaginas
      const actual = this.paginaActual
      const rango = 2
      
      let inicio = Math.max(1, actual - rango)
      let fin = Math.min(total, actual + rango)
      
      if (actual <= rango) {
        fin = Math.min(total, rango * 2 + 1)
      }
      if (actual + rango >= total) {
        inicio = Math.max(1, total - rango * 2)
      }
      
      const paginas = []
      for (let i = inicio; i <= fin; i++) {
        paginas.push(i)
      }
      return paginas
    }
  },
  methods: {
    debounceBuscar() {
      clearTimeout(this.debounceTimer)
      this.debounceTimer = setTimeout(() => {
        this.paginaActual = 1
        this.buscarPacientes()
      }, 500)
    },

    async buscarPacientes() {
      this.cargando = true
      try {
        const params = {
          busqueda: this.busqueda.trim(),
          empresa_id: this.filtroEmpresa || undefined,
          pagina: this.paginaActual,
          por_pagina: this.porPagina
        }

        const response = await clinicaTrabajo.get('/clinica-trabajo-buscador/pacientes', { params })
        
        if (response.data.success) {
          this.pacientes = response.data.data || []
          this.totalPacientes = response.data.total || 0
        }
      } catch (error) {
        console.error('Error buscando pacientes:', error)
        this.$toast?.error('Error al cargar los pacientes')
        this.pacientes = []
        this.totalPacientes = 0
      } finally {
        this.cargando = false
      }
    },

    async cargarEmpresas() {
      this.cargandoEmpresas = true
      try {
        const response = await clinicaTrabajo.get('/clinica-trabajo-buscador/empresas')
        
        if (response.data.success) {
          this.empresas = response.data.data || []
        }
      } catch (error) {
        console.error('Error cargando empresas:', error)
        this.empresas = []
      } finally {
        this.cargandoEmpresas = false
      }
    },

    cambiarPagina(pagina) {
      if (pagina >= 1 && pagina <= this.totalPaginas) {
        this.paginaActual = pagina
        this.buscarPacientes()
        window.scrollTo({ top: 0, behavior: 'smooth' })
      }
    },

    iniciarEvaluacion(paciente) {
  // 1. Validar que haya un tipo seleccionado
  if (!this.tipoEvaluacion) {
    alert('⚠️ Por favor, seleccione un tipo de evaluación antes de continuar.')
    return
  }

  // 2. Buscar la configuración del tipo seleccionado
  const tipo = this.tiposEvaluacion.find(t => t.value === this.tipoEvaluacion)
  if (!tipo) return

  // 3. Construir la URL de forma segura (asegura que empiece con /)
  const rutaBase = tipo.ruta.startsWith('/') ? tipo.ruta : `/${tipo.ruta}`
  const urlCompleta = `${rutaBase}/${paciente.id}`
  
  console.log('🚀 Redirigiendo a:', urlCompleta)
  
  // 4. Redirigir
  window.location.href = urlCompleta
},

    limpiarFiltros() {
      this.busqueda = ''
      this.filtroEmpresa = ''
      this.tipoEvaluacion = ''
      this.paginaActual = 1
      this.pacientes = []
      this.totalPacientes = 0
      this.buscarPacientes()
    },

    getBadgeClass(tipo) {
      const clases = {
        ocupacional: 'badge-primary',
        nutricion: 'badge-success',
        ergonomia: 'badge-info',
        psicologia: 'badge-warning',
        audiologia: 'badge-secondary',
        audiologia_hc: 'badge-dark',
        audiologia_ar: 'badge-danger',
        espirometria: 'badge-purple'
      }
      return clases[tipo] || 'badge-secondary'
    },

    formatearFecha(fecha) {
      if (!fecha) return '—'
      return new Date(fecha).toLocaleDateString('es-MX', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
      })
    }
  },
  mounted() {
    this.cargarEmpresas()
    this.buscarPacientes()
  }
}
</script>

<style scoped>
.buscador-pacientes-container {
  padding: 20px;
  background: #f4f6f9;
  min-height: 100vh;
}

.btn-group .btn {
  border-radius: 4px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-group .btn:hover {
  transform: translateY(-1px);
}

.table th {
  border-top: none;
  font-weight: 600;
  color: #495057;
  font-size: 0.875rem;
}

.table td {
  vertical-align: middle;
}

.badge {
  font-size: 0.75rem;
  padding: 0.35rem 0.65rem;
  font-weight: 600;
}

.opacity-75 {
  opacity: 0.75;
}

.pagination .page-link {
  color: #007bff;
  border: 1px solid #dee2e6;
}

.pagination .page-item.active .page-link {
  background-color: #007bff;
  border-color: #007bff;
  color: white;
}

.pagination .page-item.disabled .page-link {
  color: #6c757d;
  background-color: #fff;
  border-color: #dee2e6;
}
</style>