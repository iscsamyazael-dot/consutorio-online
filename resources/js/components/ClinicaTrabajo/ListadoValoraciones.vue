<template>
  <div class="listado-valoraciones">
    <!-- HEADER -->
    <div class="header-section">
      <h2>Listado de Valoraciones {{ tituloPorSubmódulo }}</h2>
      <button @click="abrirNueva" class="btn-primary">
        + Nueva Valoración
      </button>
    </div>

    <!-- FILTROS -->
    <div class="filtros mb-4">
      <div class="grid grid-cols-4 gap-3">
        <div>
          <label class="block text-sm font-semibold mb-1">Empresa</label>
          <select v-model="filtroEmpresa" class="input-field w-full">
            <option value="">Todas las empresas</option>
            <option v-for="e in empresas" :key="e.id" :value="e.id">
              {{ e.nombre || e.razon_social }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">Tipo de Evaluación</label>
          <select v-model="filtroTipo" class="input-field w-full">
            <option value="">Todos los tipos</option>
            <option value="ingreso">Ingreso</option>
            <option value="periodica">Periódica</option>
            <option value="seguimiento">Seguimiento</option>
            <option value="extraordinaria">Extraordinaria</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">Desde</label>
          <input v-model="filtroFechaDesde" type="date" class="input-field w-full" />
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">Hasta</label>
          <input v-model="filtroFechaHasta" type="date" class="input-field w-full" />
        </div>
      </div>
    </div>

    <!-- CARGANDO -->
    <div v-if="cargando" class="py-8 text-center text-gray-500">
      ⏳ Cargando valoraciones...
    </div>

    <!-- TABLA -->
    <div v-else class="tabla-container">
      <table class="w-full border-collapse">
        <thead class="bg-gray-100 border-b-2 border-gray-300">
          <tr>
            <th class="text-left p-3">Folio</th>
            <th class="text-left p-3">Paciente</th>
            <th class="text-left p-3">Empresa</th>
            <th class="text-left p-3">Tipo</th>
            <th class="text-left p-3">Fecha</th>
            <th class="text-left p-3">Profesional</th>
            <th class="text-left p-3">Aptitud</th>
            <th class="text-center p-3">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="valoracion in valoracionesFiltradas"
            :key="valoracion.id"
            @click="abrir(valoracion)"
            class="border-b border-gray-200 hover:bg-blue-50 cursor-pointer transition"
          >
            <td class="p-3 font-semibold text-blue-600">{{ valoracion.folio }}</td>
            <td class="p-3">{{ valoracion.paciente?.nombre || valoracion.paciente_id }}</td>
            <td class="p-3">{{ valoracion.empresa_cliente?.nombre || valoracion.empresa_cliente?.razon_social || valoracion.empresa_cliente_id }}</td>
            <td class="p-3">
              <span class="badge" :class="`badge-${valoracion.tipo_evaluacion}`">
                {{ valoracion.tipo_evaluacion }}
              </span>
            </td>
            <td class="p-3">{{ formatearFecha(valoracion.fecha_valoracion) }}</td>
            <td class="p-3">
              {{ nombreProfesionalPorSubmódulo(valoracion) }}
            </td>
            <td class="p-3">
              <span class="badge" :class="`aptitud-${valoracion.aptitud}`">
                {{ valoracion.aptitud }}
              </span>
            </td>
            <td class="p-3 text-center space-x-2">
              <button @click.stop="abrir(valoracion)" class="btn-xs btn-primary">
                Abrir
              </button>
              <button @click.stop="eliminar(valoracion)" class="btn-xs btn-danger">
                Eliminar
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="valoracionesFiltradas.length === 0" class="py-8 text-center text-gray-500">
        No hay valoraciones que coincidan con los filtros
      </div>
    </div>
  </div>
</template>

<script>
import ApiService from '../../services/ApiService.js'

export default {
  name: 'ListadoValoraciones',
  props: {
    submódulo: {
      type: String,
      required: true,
      validator: v => ['medicina', 'psicologia', 'nutricion', 'audiologia', 'ergonomia'].includes(v)
    }
  },
  data() {
    return {
      valoraciones: [],
      empresas: [],
      filtroEmpresa: '',
      filtroTipo: '',
      filtroFechaDesde: '',
      filtroFechaHasta: '',
      cargando: false,
    }
  },
  computed: {
    tituloPorSubmódulo() {
      const titulos = {
        medicina: 'Médicas/Ocupacionales',
        psicologia: 'Psicológicas',
        nutricion: 'Nutricionales',
        audiologia: 'Audiológicas',
        ergonomia: 'Ergonómicas',
      }
      return titulos[this.submódulo] || ''
    },
    valoracionesFiltradas() {
      return this.valoraciones.filter(v => {
        if (this.filtroEmpresa && v.empresa_cliente_id !== parseInt(this.filtroEmpresa)) return false
        if (this.filtroTipo && v.tipo_evaluacion !== this.filtroTipo) return false
        if (this.filtroFechaDesde && v.fecha_valoracion < this.filtroFechaDesde) return false
        if (this.filtroFechaHasta && v.fecha_valoracion > this.filtroFechaHasta) return false
        return true
      })
    }
  },
  methods: {
    async cargar() {
      this.cargando = true
      try {
        const response = await ApiService.clinicaTrabajo[this.submódulo].lista()
        this.valoraciones = response.data || response
      } catch (error) {
        console.error('Error cargando valoraciones:', error)
        this.$toast?.error('Error cargando valoraciones')
      } finally {
        this.cargando = false
      }
    },

    abrir(valoracion) {
      this.$router.push({
        name: `${this.submódulo}.show`,
        params: { id: valoracion.id }
      })
    },

    abrirNueva() {
      this.$router.push({
        name: `${this.submódulo}.create`
      })
    },

    async eliminar(valoracion) {
      if (!confirm(`¿Eliminar valoración ${valoracion.folio}?`)) return

      try {
        await ApiService.clinicaTrabajo[this.submódulo].eliminar(valoracion.id)
        this.$toast?.success('Eliminada')
        await this.cargar()
      } catch (error) {
        console.error('Error eliminando:', error)
        this.$toast?.error('Error eliminando valoración')
      }
    },

    formatearFecha(fecha) {
      return new Date(fecha).toLocaleDateString('es-MX')
    },

    nombreProfesionalPorSubmódulo(valoracion) {
      const claves = {
        medicina: 'medico',
        psicologia: 'psicologo',
        nutricion: 'nutriologo',
        audiologia: 'audiologo',
        ergonomia: 'ergonomo',
      }
      const clave = claves[this.submódulo]
      return valoracion[clave]?.nombre || '—'
    },

    async cargarEmpresas() {
      try {
        const response = await ApiService.clinicaTrabajo.empresas.lista()
        this.empresas = response.data || response
      } catch (error) {
        console.error('Error cargando empresas:', error)
      }
    }
  },
  mounted() {
    this.cargar()
    this.cargarEmpresas()
  }
}
</script>

<style scoped>
.listado-valoraciones {
  padding: 1.5rem;
}
.header-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}
.header-section h2 {
  font-size: 1.5rem;
  font-weight: bold;
}
.filtros {
  background: #f9fafb;
  padding: 1rem;
  border-radius: 4px;
}
.input-field {
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 4px;
}
.tabla-container {
  overflow-x: auto;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
}
table {
  background: white;
  width: 100%;
}
th {
  background: #f3f4f6;
  font-weight: 600;
  text-align: left;
  padding: 0.75rem;
}
td {
  padding: 0.75rem;
  border-bottom: 1px solid #e5e7eb;
}
.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}
.badge-ingreso {
  background: #dbeafe;
  color: #1e40af;
}
.badge-periodica {
  background: #dcfce7;
  color: #166534;
}
.badge-seguimiento {
  background: #fef3c7;
  color: #92400e;
}
.badge-extraordinaria {
  background: #fce7f3;
  color: #9d174d;
}
.aptitud-apto {
  background: #dcfce7;
  color: #166534;
}
.aptitud-apto_con_restricciones {
  background: #fef3c7;
  color: #92400e;
}
.aptitud-no_apto {
  background: #fee2e2;
  color: #991b1b;
}
.btn-xs {
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
  border-radius: 3px;
  border: none;
  cursor: pointer;
}
.btn-primary {
  background: #3b82f6;
  color: white;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}
.btn-danger {
  background: #ef4444;
  color: white;
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
  border-radius: 3px;
  border: none;
  cursor: pointer;
}
</style>