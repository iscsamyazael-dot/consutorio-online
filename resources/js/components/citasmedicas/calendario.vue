<template>
  <div class="calendar-container">

    <!-- TOPBAR -->
    <div class="calendar-topbar">
      <div>
        <h3>Calendario</h3>
        <p>Planificación inteligente de citas médicas</p>
      </div>

      <!-- BUSCADOR -->
      <div class="calendar-search">
        <i class="fas fa-search"></i>
        <input
          v-model="busqueda"
          type="text"
          placeholder="Buscar paciente..."
          @input="filtrarEventos"
        />
      </div>
    </div>

    <!-- FULLCALENDAR -->
    <FullCalendar :options="opcionesCalendario" ref="calendarRef" />

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

  props: {
    // Recibe las citas desde mastercitas.vue que las obtiene del blade
    citas: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      busqueda: '',
    }
  },

  computed: {
    // Reemplaza el @foreach($citas as $cita) del blade
    eventos() {
      return this.citas.map(cita => ({
        id:    cita.id,
        title: cita.paciente?.nombre ?? 'Paciente',
        start: `${cita.fecha_cita}T${cita.hora_cita}`,
        color: this.colorPorEstado(cita.estado),
        extendedProps: {
          estado: cita.estado,
          hora:   cita.hora_cita,
        },
      }))
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

        buttonText: {
          today: 'Hoy',
          month: 'Mes',
          week:  'Semana',
          day:   'Día',
        },

        events: this.eventos,
      }
    },
  },

  methods: {
    // Reemplaza el ternario de PHP para el color
    colorPorEstado(estado) {
      const colores = {
        Pendiente:  '#f59e0b',
        Completada: '#10b981',
        Cancelada:  '#ef4444',
      }
      return colores[estado] ?? '#94a3b8'
    },

    // Reemplaza el addEventListener('keyup') del JS original
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
}

.calendar-topbar p {
  margin: 4px 0 0;
  color: #64748b;
  font-size: .82rem;
}

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

/* FullCalendar overrides */
:deep(.fc) { font-family: inherit; }

:deep(.fc-toolbar) { margin-bottom: 14px; }

:deep(.fc-toolbar-title) {
  font-size: 1.1rem;
  font-weight: 800;
}

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

:deep(.fc-col-header-cell) {
  background: #f8fafc;
  border: none;
  padding: 12px 0;
}

:deep(.fc-col-header-cell-cushion) {
  color: #64748b;
  font-size: .82rem;
  font-weight: 700;
  text-decoration: none;
}

:deep(.fc-daygrid-day-frame) {
  min-height: 75px;
  padding: 4px;
}

:deep(.fc-daygrid-day-number) {
  color: #0f172a;
  font-size: .78rem;
  font-weight: 700;
  text-decoration: none;
}

:deep(.fc-day-today) { background: #eef2ff; }

:deep(.fc-daygrid-event) {
  border: none;
  border-radius: 8px;
  padding: 3px 6px;
  font-size: .65rem;
  font-weight: 700;
}

@media (max-width: 768px) {
  .calendar-search { width: 100%; }
}
</style>