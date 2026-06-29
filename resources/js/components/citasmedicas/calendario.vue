<template>
  <div class="calendar-container">

    <!-- TOPBAR -->
    <div class="calendar-topbar">
      <div>
        <h3>{{ vistaDetalle ? fechaSeleccionadaFormateada : 'Calendario' }}</h3>
        <p>{{ vistaDetalle ? 'Consultas del día' : 'Planificación inteligente de citas médicas' }}</p>
      </div>

      <div style="display:flex; gap:10px; align-items:center;">
        <button v-if="vistaDetalle" class="btn-volver" @click="vistaDetalle = false">
          <i class="fas fa-arrow-left"></i> Volver
        </button>

        <div class="calendar-search" v-if="!vistaDetalle">
          <i class="fas fa-search"></i>
          <input
            v-model="busqueda"
            type="text"
            placeholder="Buscar paciente..."
            @input="filtrarEventos"
          />
        </div>
      </div>
    </div>

    <!-- VISTA CALENDARIO -->
    <div v-show="!vistaDetalle">
      <FullCalendar :options="opcionesCalendario" ref="calendarRef" />
    </div>

    <!-- VISTA DETALLE DÍA -->
    <div v-if="vistaDetalle" class="detalle-dia">

      <div v-if="citasDelDia.length === 0" class="sin-citas">
        <i class="fas fa-calendar-times"></i>
        <p>No hay consultas programadas para este día.</p>
      </div>

      <table v-else class="tabla-citas">
        <thead>
          <tr>
            <th>Folio</th>
            <th>Hora</th>
            <th>Paciente</th>
            <th>Médico</th>
            <th>Especialidad</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cita in citasDelDia" :key="cita.id">
            <td>
              <span class="folio-badge">{{ cita.folio ?? '—' }}</span>
            </td>
            <td>
              <span class="hora-badge">
                <i class="fas fa-clock"></i>
                {{ formatearHora(cita.hora) }}
              </span>
            </td>
            <td>
              <div class="paciente-info">
                <div class="paciente-avatar">{{ inicialesPaciente(cita) }}</div>
                <span>{{ cita.paciente?.nombre ?? 'Paciente' }}</span>
              </div>
            </td>
            <td>{{ cita.medico?.nombre ?? '—' }}</td>
            <td>{{ cita.especialidad?.nombre ?? '—' }}</td>
            <td>
              <span class="estado-badge" :style="{ background: colorPorEstado(cita.estado) + '22', color: colorPorEstado(cita.estado) }">
                {{ cita.estado }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>

    </div>

  </div>
</template>

<script>
import FullCalendar      from '@fullcalendar/vue3'
import dayGridPlugin     from '@fullcalendar/daygrid'
import timeGridPlugin    from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import esLocale          from '@fullcalendar/core/locales/es'

export default {
  name: 'Calendario',
  components: { FullCalendar },

  data() {
    return {
      busqueda:          '',
      vistaDetalle:      false,
      fechaSeleccionada: null,
      citas:             [],
    }
  },

  mounted() {
    axios.get('/api/citas')
      .then(res => { this.citas = res.data })
      .catch(err => console.error('Error cargando citas:', err))
  },

  computed: {
    eventos() {
      return this.citas.map(cita => ({
        id:    cita.id,
        title: (cita.paciente && cita.paciente.nombre) ? cita.paciente.nombre : 'Paciente',
        start: `${cita.fecha}T${cita.hora}`,
        color: this.colorPorEstado(cita.estado),
        extendedProps: { estado: cita.estado, hora: cita.hora },
      }))
    },

    citasDelDia() {
      if (!this.fechaSeleccionada) return []
      return this.citas
        .filter(c => c.fecha === this.fechaSeleccionada)
        .sort((a, b) => a.hora.localeCompare(b.hora))
    },

    fechaSeleccionadaFormateada() {
      if (!this.fechaSeleccionada) return ''
      const [y, m, d] = this.fechaSeleccionada.split('-')
      const fecha = new Date(y, m - 1, d)
      return fecha.toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
    },

    opcionesCalendario() {
      return {
        plugins:     [dayGridPlugin, timeGridPlugin, interactionPlugin],
        locale:      esLocale,
        initialView: 'dayGridMonth',
        height:      'auto',
        headerToolbar: {
          left:   'prev,next today',
          center: 'title',
          right:  'dayGridMonth,timeGridWeek,timeGridDay',
        },
        buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', day: 'Día' },
        events: this.eventos,

        dateClick: (info) => {
          this.fechaSeleccionada = info.dateStr
          this.vistaDetalle      = true
        },

        eventClick: (info) => {
          this.fechaSeleccionada = info.event.startStr.split('T')[0]
          this.vistaDetalle      = true
        },
      }
    },
  },

  methods: {
    colorPorEstado(estado) {
      const colores = {
        programada:  '#3b82f6',
        Completada:  '#10b981',
        Cancelada:   '#ef4444',
      }
      return colores[estado] ?? '#94a3b8'
    },

    formatearHora(hora) {
      if (!hora) return ''
      const [h, m] = hora.split(':')
      const hr   = parseInt(h)
      const ampm = hr >= 12 ? 'PM' : 'AM'
      const h12  = hr % 12 || 12
      return `${h12}:${m} ${ampm}`
    },

    inicialesPaciente(cita) {
      const nombre = cita.paciente?.nombre ?? 'P'
      return nombre.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase()
    },

    filtrarEventos() {
      const api = this.$refs.calendarRef?.getApi()
      if (!api) return
      const termino = this.busqueda.toLowerCase().trim()
      api.getEvents().forEach(event => {
        const coincide = event.title.toLowerCase().includes(termino)
        event.setProp('display', coincide || termino === '' ? 'auto' : 'none')
      })
    },
  },
}
</script>

<style scoped>
.calendar-container {
  background: rgba(255, 255, 255, .88);
  backdrop-filter: blur(14px);
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, .8);
  padding: 16px;
  overflow: hidden;
  box-shadow: 0 8px 24px rgba(15, 23, 42, .05);
}

.calendar-topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
  margin-bottom: 14px;
}

.calendar-topbar h3 {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 800;
  color: #0f172a;
  text-transform: capitalize;
}

.calendar-topbar p {
  margin: 4px 0 0;
  color: #64748b;
  font-size: .82rem;
}

.btn-volver {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 8px 14px;
  font-size: .82rem;
  font-weight: 700;
  color: #334155;
  cursor: pointer;
  transition: background .2s;
}
.btn-volver:hover { background: #e2e8f0; }

.calendar-search {
  position: relative;
  width: 220px;
}
.calendar-search i {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
}
.calendar-search input {
  width: 100%;
  height: 40px;
  border-radius: 12px;
  border: 1px solid #dbe4ee;
  background: white;
  padding: 0 16px 0 40px;
  outline: none;
  font-size: .82rem;
}

.tabla-citas {
  width: 100%;
  border-collapse: collapse;
}
.tabla-citas thead tr {
  background: #f8fafc;
}
.tabla-citas th {
  padding: 12px 16px;
  text-align: left;
  font-size: .78rem;
  font-weight: 700;
  color: #64748b;
  border-bottom: 2px solid #e2e8f0;
}
.tabla-citas td {
  padding: 14px 16px;
  font-size: .82rem;
  color: #0f172a;
  border-bottom: 1px solid #f1f5f9;
}
.tabla-citas tbody tr:hover { background: #f8fafc; }

.folio-badge {
  background: #f1f5f9;
  color: #475569;
  border-radius: 8px;
  padding: 4px 10px;
  font-size: .75rem;
  font-weight: 700;
}

.hora-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #eef2ff;
  color: #4f46e5;
  border-radius: 8px;
  padding: 4px 10px;
  font-weight: 700;
  font-size: .78rem;
}

.paciente-info {
  display: flex;
  align-items: center;
  gap: 10px;
}
.paciente-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6, #6366f1);
  color: white;
  font-size: .72rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
}

.estado-badge {
  display: inline-block;
  border-radius: 8px;
  padding: 4px 10px;
  font-size: .75rem;
  font-weight: 700;
}

.sin-citas {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: #94a3b8;
  gap: 12px;
}
.sin-citas i { font-size: 2.5rem; }
.sin-citas p { font-size: .9rem; margin: 0; }

:deep(.fc) { font-family: inherit; }
:deep(.fc-toolbar) { margin-bottom: 14px; }
:deep(.fc-toolbar-title) { font-size: 1.1rem; font-weight: 800; }
:deep(.fc .fc-button) {
  background: white;
  border: 1px solid #e2e8f0;
  color: #334155;
  border-radius: 10px;
  padding: 6px 12px;
  font-size: .78rem;
  font-weight: 700;
  box-shadow: none;
}
:deep(.fc .fc-button-primary:not(:disabled).fc-button-active) {
  background: linear-gradient(135deg, #3b82f6, #6366f1);
  border: none;
  color: white;
}
:deep(.fc-scrollgrid) { border: none; }
:deep(.fc-col-header-cell) { background: #f8fafc; border: none; padding: 12px 0; }
:deep(.fc-col-header-cell-cushion) { color: #64748b; font-size: .82rem; font-weight: 700; text-decoration: none; }
:deep(.fc-daygrid-day-frame) { min-height: 75px; padding: 4px; cursor: pointer; }
:deep(.fc-daygrid-day-number) { color: #0f172a; font-size: .78rem; font-weight: 700; text-decoration: none; }
:deep(.fc-day-today) { background: #eef2ff; }
:deep(.fc-daygrid-event) { border: none; border-radius: 8px; padding: 3px 6px; font-size: .65rem; font-weight: 700; }

@media (max-width: 768px) {
  .calendar-search { width: 100%; }
}
</style>