<template>
  <div class="hist-page">
    <!-- HEADER -->
    <div class="hist-page__header">
      <div>
        <div class="brand-chip">
          <span class="brand-dot"></span>
          <span class="brand-label">Sistema activo</span>
        </div>
        <h1 class="main-title"><i class="ti ti-history"></i> Historial de consulta</h1>
        <p class="main-sub">Consulta y filtra el histórico de citas registradas en el sistema.</p>
      </div>
      <a href="/ListaConsultas" class="btn-ghost">
        <i class="ti ti-arrow-left"></i> Volver
      </a>
    </div>

    <!-- CARD contenedora (mismo look que el modal, ahora como página) -->
    <div class="hist-card">

      <!-- Filtros: especialidad, médico y fecha (calendario) -->
      <div class="hist-filtros">
        <select v-model="historialEspecialidad" class="hist-select" @change="onHistorialEspecialidadChange">
          <option value="">Todas las especialidades</option>
          <option v-for="esp in especialidadesDisponiblesHistorial" :key="esp.id" :value="esp.id">
            {{ esp.nombre }}
          </option>
        </select>

        <select v-model="historialMedico" class="hist-select">
          <option value="">Todos los médicos</option>
          <option v-for="med in medicosFiltradosHistorial" :key="med.id" :value="med.id">
            {{ med.nombre }}
          </option>
        </select>

        <label class="hist-fecha">
          <i class="ti ti-calendar" aria-hidden="true"></i>
          <input type="date" v-model="historialFecha" class="hist-fecha-input" />
        </label>

        <button
          v-if="historialFecha"
          type="button"
          class="hist-toggle"
          @click="historialFecha = ''"
        >
          <i class="ti ti-x" aria-hidden="true"></i> Ver todas las fechas
        </button>

        <button type="button" class="hist-refresh" title="Actualizar" @click="cargarDatos">
          <i class="ti ti-refresh" aria-hidden="true"></i>
        </button>
      </div>

      <div class="hist-hint">
        <i class="ti ti-info-circle" aria-hidden="true"></i>
        <span>
          {{ cargandoStats ? 'Cargando…' : `${historialFiltrado.length} consulta(s)` }}
          <template v-if="historialFecha"> · {{ formatearFechaHistorial(historialFecha) }}</template>
          <template v-else> · todas las fechas</template>
          · Usa el botón "Finalizar" para cerrar una consulta en proceso.
        </span>
      </div>

      <!-- Tabla: paciente, hora, estado (editable) -->
      <div class="hist-table-wrap">
        <table class="hist-table">
          <thead>
            <tr>
              <th>Paciente</th>
              <th>Hora</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in historialFiltrado" :key="c.id">
              <td>
                <p class="hist-patient-name">{{ nombrePacienteCita(c) }}</p>
                <p class="hist-patient-sub" v-if="c.especialidad || c.medico">
                  {{ c.especialidad?.nombre }}<template v-if="c.especialidad && c.medico"> · </template>{{ c.medico?.nombre }}
                </p>
              </td>
              <td class="hist-mono">{{ c.hora || '—' }}</td>
              <td>
                <div class="hist-estado-cell">
                  <!-- Chip de estado (ya no es editable con clic; el único
                       cambio de estado disponible desde aquí es el botón
                       "Finalizar" que aparece cuando está 'En proceso'). -->
                  <span
                    class="hist-chip"
                    :class="estadoCitaClass(c.estado)"
                  >
                    {{ c.estado || 'Sin estado' }}
                  </span>

                  <!-- Botón rápido: solo visible mientras el estado es 'En proceso'.
                       Al hacer clic, cambia el estado a 'Finalizada' directamente
                       y el botón desaparece. -->
                  <button
                    v-if="c.estado === 'En proceso'"
                    type="button"
                    class="hist-finalizar-btn"
                    :disabled="guardandoEstadoId === c.id"
                    @click="cambiarEstadoCita(c, 'Finalizada')"
                  >
                    Finalizar
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!cargandoStats && historialFiltrado.length === 0">
              <td colspan="3" class="hist-empty">No hay consultas con ese criterio.</td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</template>

<script>
import axios from 'axios'
import ApiService from '../../services/ApiService.js'

function obtenerFechaHoyISO() {
  const hoy = new Date()
  const y = hoy.getFullYear()
  const m = String(hoy.getMonth() + 1).padStart(2, '0')
  const d = String(hoy.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

export default {
  name: 'HistorialConsulta',

  data() {
    return {
      citas: [],
      pacientes: [],
      cargandoStats: true,

      // Filtros del historial: fecha empieza en hoy por defecto.
      historialFecha: obtenerFechaHoyISO(),
      historialEspecialidad: '',
      historialMedico: '',

      // id de la cita cuyo cambio de estado está en vuelo (deshabilita el botón)
      guardandoEstadoId: null,
    }
  },

  computed: {
    // Lista única de especialidades a partir de todas las citas cargadas
    especialidadesDisponiblesHistorial() {
      const mapa = new Map()
      this.citas.forEach(c => {
        if (!c.especialidad) return
        const id = c.especialidad.id ?? c.especialidad.nombre
        if (!mapa.has(id)) mapa.set(id, { id, nombre: c.especialidad.nombre })
      })
      return Array.from(mapa.values())
    },

    // Lista única de médicos, con las especialidades que se les ha visto atender
    medicosDisponiblesHistorial() {
      const mapa = new Map()
      this.citas.forEach(c => {
        if (!c.medico) return
        const id = c.medico.id ?? c.medico.nombre
        const espId = c.especialidad ? (c.especialidad.id ?? c.especialidad.nombre) : ''
        if (!mapa.has(id)) {
          mapa.set(id, { id, nombre: c.medico.nombre, especialidadIds: new Set(espId ? [espId] : []) })
        } else if (espId) {
          mapa.get(id).especialidadIds.add(espId)
        }
      })
      return Array.from(mapa.values()).map(m => ({ ...m, especialidadIds: Array.from(m.especialidadIds) }))
    },

    // Si hay especialidad elegida, solo muestra médicos que la atienden
    medicosFiltradosHistorial() {
      if (!this.historialEspecialidad) return this.medicosDisponiblesHistorial
      return this.medicosDisponiblesHistorial.filter(m =>
        Array.isArray(m.especialidadIds) && m.especialidadIds.includes(this.historialEspecialidad)
      )
    },

    // Citas filtradas por fecha / especialidad / médico, ordenadas por hora.
    // Si historialFecha está vacío, muestra todas las fechas.
    historialFiltrado() {
      let lista = this.citas

      if (this.historialFecha) {
        lista = lista.filter(c => String(c.fecha).slice(0, 10) === this.historialFecha)
      }
      if (this.historialEspecialidad) {
        lista = lista.filter(c =>
          c.especialidad && (c.especialidad.id ?? c.especialidad.nombre) === this.historialEspecialidad
        )
      }
      if (this.historialMedico) {
        lista = lista.filter(c =>
          c.medico && (c.medico.id ?? c.medico.nombre) === this.historialMedico
        )
      }

      return lista.slice().sort((a, b) => (a.hora || '').localeCompare(b.hora || ''))
    },
  },

  mounted() {
    this.cargarDatos()
  },

  methods: {
    async cargarDatos() {
      this.cargandoStats = true
      try {
        const [respCitas, respPacientes] = await Promise.all([
          axios.get('/api/citas'),
          ApiService.get('/pacientes'),
        ])

        this.citas = respCitas.data || []
        this.pacientes = respPacientes.data || []
      } catch (error) {
        console.error('Error al cargar el historial de consultas:', error)
      } finally {
        this.cargandoStats = false
      }
    },

    onHistorialEspecialidadChange() {
      // Si el médico elegido no atiende la nueva especialidad, se limpia
      if (this.historialMedico) {
        const sigueSiendoValido = this.medicosFiltradosHistorial.some(m => m.id === this.historialMedico)
        if (!sigueSiendoValido) this.historialMedico = ''
      }
    },

    nombrePacienteCita(c) {
      const p = c.paciente
      if (!p) return '—'
      return [p.nombre, p.apellido_paterno, p.apellido_materno].filter(Boolean).join(' ')
    },

    formatearFechaHistorial(fecha) {
      if (!fecha) return '—'
      const [anio, mes, dia] = fecha.split('-')
      const meses = [
        'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
      ]
      return `${parseInt(dia)} de ${meses[parseInt(mes) - 1]} de ${anio}`
    },

    estadoCitaClass(estado) {
      switch (estado) {
        case 'Finalizada': return 'hist-chip--green'
        case 'En proceso': return 'hist-chip--blue'
        case 'Cancelada':  return 'hist-chip--red'
        case 'Agendado':   return 'hist-chip--amber'
        default:           return 'hist-chip--gray'
      }
    },

    // Devuelve { endpoint, idReal } según de dónde viene el registro.
    // Desde que /api/citas combina 'citas' y 'consultas' tradicionales,
    // el backend prefija los ids como 'cita-123' o 'consulta-456' para
    // que no choquen entre sí (ver CitaController@getCitas). Aquí se
    // quita el prefijo y se decide a qué endpoint mandar el PATCH.
    endpointEstadoCita(cita) {
      const idStr = String(cita.id)

      if (cita.origen === 'consulta' || idStr.startsWith('consulta-')) {
        return {
          url: `/api/consultas/${idStr.replace('consulta-', '')}/estado`,
        }
      }

      return {
        url: `/api/citas/${idStr.replace('cita-', '')}/estado`,
      }
    },

    // Cambia el estado de una cita o de una consulta tradicional.
    // Usa PATCH /api/citas/{id}/estado para las citas de Agenda, y
    // PATCH /api/consultas/{id}/estado para las consultas tradicionales
    // (ver ConsultaController@actualizarEstado).
    async cambiarEstadoCita(cita, nuevoEstado) {
      if (!nuevoEstado || nuevoEstado === cita.estado) {
        return
      }

      this.guardandoEstadoId = cita.id
      try {
        const { url } = this.endpointEstadoCita(cita)
        await axios.patch(url, { estado: nuevoEstado })
        cita.estado = nuevoEstado

        if (window.Swal) {
          window.Swal.fire({
            toast: true, position: 'top-end', icon: 'success',
            title: 'Estado actualizado', showConfirmButton: false,
            timer: 1200, timerProgressBar: true,
          })
        }
      } catch (error) {
        console.error('Error al cambiar el estado de la cita:', error)
        if (window.Swal) {
          window.Swal.fire({
            icon: 'error',
            title: 'No se pudo cambiar el estado',
            text: error.response?.data?.message || 'Intenta de nuevo.',
          })
        }
      } finally {
        this.guardandoEstadoId = null
      }
    },
  },
}
</script>

<style scoped>
.hist-page {
  padding: 1.5rem 0 1rem;
  font-family: inherit;
}

.hist-page__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  flex-wrap: wrap;
  margin-bottom: 1.5rem;
}

/* ── Brand chip / titles (mismo estilo que Centro de Consultas) ── */
.brand-chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #e6f1fb;
  border: 0.5px solid #b5d4f4;
  border-radius: 99px;
  padding: 5px 12px 5px 8px;
  margin-bottom: 10px;
}
.brand-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #185fa5;
  animation: blink 1.6s ease-in-out infinite;
  flex-shrink: 0;
}
@keyframes blink {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0.25; }
}
.brand-label {
  font-size: 11px;
  font-weight: 500;
  color: #185fa5;
  letter-spacing: 0.05em;
}
.main-title {
  font-size: 26px;
  font-weight: 500;
  color: #1a1a1a;
  margin: 0 0 4px;
  line-height: 1.2;
  display: flex;
  align-items: center;
  gap: 10px;
}
.main-sub {
  font-size: 13px;
  color: #6b7280;
  margin: 0;
  line-height: 1.6;
  max-width: 460px;
}

.btn-ghost {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #ffffff;
  border: 0.5px solid #d1d5db;
  border-radius: 8px;
  padding: 8px 14px;
  font-size: 13px;
  color: #6b7280;
  cursor: pointer;
  text-decoration: none;
  transition: background 0.15s;
}
.btn-ghost:hover {
  background: #f9fafb;
}

/* ═══════════════════════════════════════
   CARD: mismo estilo hist-* del modal, ahora como página
   ═══════════════════════════════════════ */
.hist-card {
  background: #fff;
  border-radius: 18px;
  border: 0.5px solid #e5e7eb;
  overflow: hidden;
  box-shadow: 0 6px 16px -8px rgba(0, 0, 0, 0.12);
}

/* Filtros */
.hist-filtros {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  padding: 16px 22px;
  border-bottom: 1px solid #f0f2f5;
  background: #fafbfc;
}
.hist-select {
  height: 36px;
  padding: 0 10px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #fff;
  color: #185fa5;
  font-size: 12.5px;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  outline: none;
}
.hist-select:hover,
.hist-select:focus {
  border-color: #185fa5;
}
.hist-fecha {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  height: 36px;
  padding: 0 10px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #fff;
  color: #185fa5;
  cursor: pointer;
}
.hist-fecha-input {
  border: none;
  outline: none;
  background: transparent;
  font-size: 12.5px;
  font-weight: 600;
  font-family: inherit;
  color: #185fa5;
  cursor: pointer;
}
.hist-toggle {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  height: 36px;
  padding: 0 14px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #fff;
  color: #6b7280;
  font-size: 12.5px;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
}
.hist-toggle:hover {
  border-color: #185fa5;
  color: #185fa5;
}
.hist-refresh {
  margin-left: auto;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #fff;
  color: #6b7280;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}
.hist-refresh:hover {
  border-color: #185fa5;
  color: #185fa5;
}

.hist-hint {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 22px;
  font-size: 12px;
  color: #185fa5;
  background: #eff6ff;
  border-bottom: 1px solid #dbeafe;
}

.hist-table-wrap {
  overflow-x: auto;
}
.hist-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.hist-table thead th {
  padding: 10px 22px;
  text-align: left;
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  background: #fafafa;
  border-bottom: 1px solid #f0f2f5;
}
.hist-table td {
  padding: 12px 22px;
  border-bottom: 1px solid #f3f4f6;
  vertical-align: middle;
  color: #111827;
}
.hist-table tbody tr:last-child td {
  border-bottom: none;
}
.hist-patient-name {
  font-weight: 700;
  font-size: 13.5px;
  color: #111827;
}
.hist-patient-sub {
  font-size: 11.5px;
  color: #185fa5;
  font-weight: 600;
  margin-top: 2px;
}
.hist-mono {
  font-family: 'IBM Plex Mono', monospace;
  font-size: 12.5px;
  color: #374151;
}
.hist-empty {
  text-align: center;
  padding: 32px 16px;
  color: #9ca3af;
  font-size: 13px;
}

/* Chip de estado (clickeable) */
.hist-chip {
  display: inline-block;
  font-size: 11px;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 99px;
  white-space: nowrap;
  cursor: pointer;
  letter-spacing: 0.02em;
}
.hist-chip--green { background: #d1fae5; color: #065f46; }
.hist-chip--blue  { background: #dbeafe; color: #1e40af; }
.hist-chip--red   { background: #fee2e2; color: #991b1b; }
.hist-chip--amber { background: #fef3c7; color: #92400e; }
.hist-chip--gray  { background: #f3f4f6; color: #374151; }

/* Contenedor del chip de estado + botón rápido de finalizar */
.hist-estado-cell {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

/* Botón rápido "Finalizar", visible solo cuando el estado es 'En proceso' */
.hist-finalizar-btn {
  display: inline-flex;
  align-items: center;
  height: 26px;
  padding: 0 12px;
  border-radius: 99px;
  border: 1px solid #dc2626;
  background: #fff;
  color: #dc2626;
  font-size: 11px;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  white-space: nowrap;
  transition: background 0.15s, color 0.15s;
}
.hist-finalizar-btn:hover:not(:disabled) {
  background: #dc2626;
  color: #fff;
}
.hist-finalizar-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Select inline para cambiar el estado */
.hist-estado-select {
  font-size: 12px;
  font-weight: 600;
  font-family: inherit;
  color: #111827;
  border: 1px solid #185fa5;
  border-radius: 7px;
  padding: 5px 8px;
  outline: none;
  background: #fff;
}
</style>