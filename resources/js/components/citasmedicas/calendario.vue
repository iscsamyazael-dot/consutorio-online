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
              <div style="display:flex; align-items:center; gap:8px;">
                <span
                  class="estado-badge"
                  :style="{ background: colorPorEstado(cita.estado) + '22', color: colorPorEstado(cita.estado) }"
                  @click="abrirModalEstado(cita)"
                  style="cursor:pointer"
                  title="Clic para cambiar estado"
                >
                  {{ cita.estado }}
                  <i class="fas fa-chevron-down" style="font-size:.6rem; margin-left:4px;"></i>
                </span>

                <button
                  v-if="datosPacienteIncompletos(cita)"
                  class="alerta-incompleto"
                  title="Datos del paciente incompletos. Clic para completar."
                  @click="irACompletarPaciente(cita)"
                >
                  <i class="fas fa-exclamation-triangle"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ── MODAL CAMBIAR ESTADO ── -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="mostrarModalEstado" class="modal-overlay" @click.self="cerrarModal">
          <div class="modal-card">

            <!-- Cabecera -->
            <div class="modal-header">
              <div class="modal-icon">
                <i class="fas fa-stethoscope"></i>
              </div>
              <div>
                <h4>Cambiar estado de la cita</h4>
                <p class="modal-subtitulo">{{ citaSeleccionada?.paciente?.nombre ?? 'Paciente' }}</p>
              </div>
              <button class="modal-close" @click="cerrarModal">
                <i class="fas fa-times"></i>
              </button>
            </div>

            <!-- Opciones de estado -->
            <div class="modal-body">
              <p class="modal-label">Selecciona el nuevo estado:</p>

              <div class="estado-opciones">
                <button
                  v-for="opcion in estadosDisponibles"
                  :key="opcion.valor"
                  class="estado-opcion"
                  :class="{ activo: nuevoEstado === opcion.valor }"
                  :style="nuevoEstado === opcion.valor
                    ? { background: opcion.color + '18', borderColor: opcion.color, color: opcion.color }
                    : {}"
                  @click="nuevoEstado = opcion.valor"
                >
                  <span class="opcion-dot" :style="{ background: opcion.color }">
                    <i :class="opcion.icono"></i>
                  </span>
                  <span class="opcion-label">{{ opcion.valor }}</span>
                  <i
                    v-if="nuevoEstado === opcion.valor"
                    class="fas fa-check opcion-check"
                    :style="{ color: opcion.color }"
                  ></i>
                </button>
              </div>
            </div>

            <!-- Pie del modal -->
            <div class="modal-footer">
              <button class="btn-cancelar" @click="cerrarModal">
                Cancelar
              </button>
              <button
                class="btn-confirmar"
                :disabled="nuevoEstado === citaSeleccionada?.estado || guardando"
                :style="{ background: colorPorEstado(nuevoEstado) }"
                @click="confirmarCambioEstado"
              >
                <i v-if="guardando" class="fas fa-spinner fa-spin"></i>
                <i v-else class="fas fa-check"></i>
                {{ guardando ? 'Guardando…' : 'Confirmar cambio' }}
              </button>
            </div>

          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ── TOAST DE CONFIRMACIÓN ── -->
    <Teleport to="body">
      <Transition name="toast-slide">
        <div v-if="toast" class="toast-notificacion" :class="toast.tipo">
          <i :class="toast.icono"></i>
          <span>{{ toast.mensaje }}</span>
        </div>
      </Transition>
    </Teleport>

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
      busqueda:           '',
      vistaDetalle:       false,
      fechaSeleccionada:  null,
      citas:              [],

      // Modal
      mostrarModalEstado: false,
      citaSeleccionada:   null,
      nuevoEstado:        '',
      guardando:          false,

      // Toast
      toast:              null,
      toastTimer:         null,
// Estados disponibles para cambiar
      estadosDisponibles: [
        { valor: 'Agendado',     color: '#3b82f6', icono: 'fas fa-calendar-alt' },
        { valor: 'Finalizada',   color: '#10b981', icono: 'fas fa-check' },
        { valor: 'Cancelada',    color: '#ef4444', icono: 'fas fa-times' },
        { valor: 'Inasistencia', color: '#f59e0b', icono: 'fas fa-user-slash' },
      ],
    }
  },

  mounted() {
    fetch('/api/citas')
      .then(res => res.json())
      .then(data => { this.citas = data })
      .catch(err => console.error('Error cargando citas:', err))
  },
//colorPorEstado, iconoPorEstado, normalizarEstado, formatearHora, inicialesPaciente, filtrarEventos, datosPacienteIncompletos, irACompletarPaciente, mostrarToast, abrirModalEstado, cerrarModal, confirmarCambioEstado
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
// obtiene las citas del día seleccionado, ordenadas por hora
    citasDelDia() {
      if (!this.fechaSeleccionada) return []
      return this.citas
        .filter(c => c.fecha === this.fechaSeleccionada)
        .sort((a, b) => a.hora.localeCompare(b.hora))
    },
// formatea la fecha seleccionada a un formato legible
    fechaSeleccionadaFormateada() {
      if (!this.fechaSeleccionada) return ''
      const [y, m, d] = this.fechaSeleccionada.split('-')
      const fecha = new Date(y, m - 1, d)
      return fecha.toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
    },
//opcionesCalendario, colorPorEstado, iconoPorEstado, normalizarEstado, formatearHora, inicialesPaciente, filtrarEventos, datosPacienteIncompletos, irACompletarPaciente, mostrarToast, abrirModalEstado, cerrarModal, confirmarCambioEstado
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
//eventContent, eventDidMount, eventMouseEnter, eventMouseLeave
        eventContent: (arg) => {
          const estado = arg.event.extendedProps.estado
          const color  = this.colorPorEstado(estado)
          const icono  = this.iconoPorEstado(estado)
          const hora   = arg.timeText ? `${arg.timeText} ` : ''

          const wrapper = document.createElement('div')
          wrapper.style.display = 'flex'
          wrapper.style.alignItems = 'center'
          wrapper.style.gap = '4px'
          wrapper.style.overflow = 'hidden'

          const iconEl = document.createElement('i')
          iconEl.className = `fas ${icono}`
          iconEl.style.color = color
          iconEl.style.fontSize = '.62rem'
          iconEl.style.flexShrink = '0'

          const textEl = document.createElement('span')
          textEl.style.overflow = 'hidden'
          textEl.style.textOverflow = 'ellipsis'
          textEl.style.whiteSpace = 'nowrap'
          textEl.textContent = `${hora}${arg.event.title}`

          wrapper.appendChild(iconEl)
          wrapper.appendChild(textEl)

          return { domNodes: [wrapper] }
        },
//eventContent, eventDidMount, eventMouseEnter, eventMouseLeave
        dateClick: (info) => {
          this.fechaSeleccionada = info.dateStr
          this.vistaDetalle      = true
        },
//fechaClick, eventContent, eventDidMount, eventMouseEnter, eventMouseLeave
        eventClick: (info) => {
          this.fechaSeleccionada = info.event.startStr.split('T')[0]
          this.vistaDetalle      = true
        },
      }
    },
  },
//colorPorEstado, iconoPorEstado, normalizarEstado, formatearHora, inicialesPaciente, filtrarEventos, datosPacienteIncompletos, irACompletarPaciente, mostrarToast, abrirModalEstado, cerrarModal, confirmarCambioEstado
  methods: {
    colorPorEstado(estado) {
      const clave = this.normalizarEstado(estado)
      const colores = {
        agendado:     '#3b82f6',
        programada:   '#3b82f6',
        programado:   '#3b82f6',
        finalizada:   '#10b981',
        finalizado:   '#10b981',
        completada:   '#10b981',
        completado:   '#10b981',
        atendida:     '#10b981',
        atendido:     '#10b981',
        cancelada:    '#ef4444',
        cancelado:    '#ef4444',
        inasistencia: '#f59e0b',
        ausente:      '#f59e0b',
        'no asistio': '#f59e0b',
      }
      return colores[clave] ?? '#94a3b8'
    },
// obtiene el icono correspondiente al estado
    iconoPorEstado(estado) {
      const clave = this.normalizarEstado(estado)
      const iconos = {
        agendado:     'fa-calendar-alt',
        programada:   'fa-calendar-alt',
        programado:   'fa-calendar-alt',
        finalizada:   'fa-check',
        finalizado:   'fa-check',
        completada:   'fa-check',
        completado:   'fa-check',
        atendida:     'fa-check',
        atendido:     'fa-check',
        cancelada:    'fa-times',
        cancelado:    'fa-times',
        inasistencia: 'fa-user-slash',
        ausente:      'fa-user-slash',
        'no asistio': 'fa-user-slash',
      }
      return iconos[clave] ?? 'fa-calendar-alt'
    },
// normaliza el estado para comparación
    normalizarEstado(estado) {
      if (!estado) return ''
      return estado
        .toString()
        .trim()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '') // quita acentos para comparar mejor
    },
// formatea la hora de 24h a 12h con AM/PM
    formatearHora(hora) {
      if (!hora) return ''
      const [h, m] = hora.split(':')
      const hr   = parseInt(h)
      const ampm = hr >= 12 ? 'PM' : 'AM'
      const h12  = hr % 12 || 12
      return `${h12}:${m} ${ampm}`
    },
// obtiene las iniciales del paciente
    inicialesPaciente(cita) {
      const nombre = cita.paciente?.nombre ?? 'P'
      return nombre.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase()
    },
// filtra los eventos según el término de búsqueda
    filtrarEventos() {
      const api = this.$refs.calendarRef?.getApi()
      if (!api) return
      const termino = this.busqueda.toLowerCase().trim()
      api.getEvents().forEach(event => {
        const coincide = event.title.toLowerCase().includes(termino)
        event.setProp('display', coincide || termino === '' ? 'auto' : 'none')
      })
    },
// verifica si los datos del paciente están incompletos
    datosPacienteIncompletos(cita) {
      const p = cita.paciente
      if (!p) return true
      const camposRequeridos = ['nombre', 'sexo', 'telefono', 'email', 'direccion', 'curp', 'tipo_sangre', 'contacto_emergencia', 'alergias']
      return camposRequeridos.some(campo => !p[campo] || p[campo].toString().trim() === '')
    },
// redirige a la página de completar datos del paciente
    irACompletarPaciente(cita) {
      const p = cita.paciente
      if (!p) return
      localStorage.setItem('pacientePrecargar', JSON.stringify(p))
      window.location.href = '/PacienteNuevo'
    },

    // ── TOAST ──
    mostrarToast(mensaje, tipo = 'exito') {
      // Cancela el timer anterior si hay uno activo
      if (this.toastTimer) clearTimeout(this.toastTimer)

      this.toast = {
        mensaje,
        tipo,
        icono: tipo === 'exito' ? 'fas fa-check-circle' : 'fas fa-times-circle',
      }

      this.toastTimer = setTimeout(() => {
        this.toast = null
      }, 3000)
    },

    // ── MODAL ──
    abrirModalEstado(cita) {
      this.citaSeleccionada   = cita
      this.nuevoEstado        = cita.estado
      this.mostrarModalEstado = true
    },

    cerrarModal() {
      if (this.guardando) return
      this.mostrarModalEstado = false
      this.citaSeleccionada   = null
      this.nuevoEstado        = ''
    },
// confirma el cambio de estado y actualiza la cita
    async confirmarCambioEstado() {
      if (!this.citaSeleccionada || this.nuevoEstado === this.citaSeleccionada.estado) return

      this.guardando = true
      try {
        const res = await fetch(`/citas/${this.citaSeleccionada.id}/estado`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          },
          body: JSON.stringify({ estado: this.nuevoEstado }),
        })

        if (!res.ok) throw new Error('Error al actualizar')

        // Actualizar localmente sin recargar
        const idx = this.citas.findIndex(c => c.id === this.citaSeleccionada.id)
        if (idx !== -1) {
          this.citas[idx] = { ...this.citas[idx], estado: this.nuevoEstado }
        }

        this.cerrarModal()
        this.mostrarToast(`Estado actualizado a "${this.nuevoEstado}" correctamente.`, 'exito')

      } catch (err) {
        console.error('Error actualizando estado:', err)
        this.mostrarToast('No se pudo actualizar el estado. Intenta nuevamente.', 'error')
      } finally {
        this.guardando = false
      }
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
.tabla-citas thead tr { background: #f8fafc; }
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
  display: inline-flex;
  align-items: center;
  border-radius: 8px;
  padding: 4px 10px;
  font-size: .75rem;
  font-weight: 700;
  transition: opacity .15s, transform .15s;
  user-select: none;
}
.estado-badge:hover {
  opacity: .85;
  transform: translateY(-1px);
}

.alerta-incompleto {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #fff7ed;
  color: #ea580c;
  border: 1px solid #fed7aa;
  border-radius: 8px;
  padding: 5px 8px;
  font-size: .78rem;
  cursor: pointer;
  transition: background .2s, transform .15s, box-shadow .2s;
}
.alerta-incompleto:hover {
  background: #ffedd5;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(234, 88, 12, .2);
}
.alerta-incompleto i { font-size: .78rem; }

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

/* ── MODAL ── */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, .45);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 16px;
}

.modal-card {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 24px 60px rgba(15, 23, 42, .18);
  overflow: hidden;
}

.modal-header {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 20px 20px 16px;
  border-bottom: 1px solid #f1f5f9;
  position: relative;
}

.modal-icon {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: linear-gradient(135deg, #3b82f6, #6366f1);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1rem;
  flex-shrink: 0;
}

.modal-header h4 {
  margin: 0;
  font-size: .95rem;
  font-weight: 800;
  color: #0f172a;
}

.modal-subtitulo {
  margin: 3px 0 0;
  font-size: .78rem;
  color: #64748b;
}

.modal-close {
  position: absolute;
  top: 16px;
  right: 16px;
  width: 30px;
  height: 30px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  color: #64748b;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .8rem;
  transition: background .2s;
}
.modal-close:hover { background: #e2e8f0; color: #0f172a; }

.modal-body {
  padding: 20px;
}

.modal-label {
  font-size: .78rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: .04em;
  margin: 0 0 12px;
}

.estado-opciones {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.estado-opcion {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  border-radius: 12px;
  border: 1.5px solid #e2e8f0;
  background: #f8fafc;
  cursor: pointer;
  font-size: .85rem;
  font-weight: 600;
  color: #334155;
  transition: border-color .2s, background .2s, color .2s, transform .15s;
  text-align: left;
}
.estado-opcion:hover {
  border-color: #cbd5e1;
  background: #f1f5f9;
  transform: translateX(3px);
}
.estado-opcion.activo {
  font-weight: 800;
}

.opcion-dot {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: .68rem;
}

.opcion-label { flex: 1; }

.opcion-check {
  font-size: .8rem;
  flex-shrink: 0;
}

.modal-footer {
  display: flex;
  gap: 10px;
  padding: 16px 20px 20px;
  border-top: 1px solid #f1f5f9;
}

.btn-cancelar {
  flex: 1;
  height: 42px;
  border-radius: 12px;
  border: 1.5px solid #e2e8f0;
  background: white;
  color: #64748b;
  font-size: .84rem;
  font-weight: 700;
  cursor: pointer;
  transition: background .2s;
}
.btn-cancelar:hover { background: #f1f5f9; }

.btn-confirmar {
  flex: 2;
  height: 42px;
  border-radius: 12px;
  border: none;
  color: white;
  font-size: .84rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: opacity .2s, transform .15s;
}
.btn-confirmar:hover:not(:disabled) {
  opacity: .88;
  transform: translateY(-1px);
}
.btn-confirmar:disabled {
  opacity: .4;
  cursor: not-allowed;
}

/* ── TOAST ── */
.toast-notificacion {
  position: fixed;
  bottom: 28px;
  right: 28px;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 20px;
  border-radius: 14px;
  font-size: .85rem;
  font-weight: 700;
  color: white;
  z-index: 99999;
  box-shadow: 0 8px 28px rgba(0, 0, 0, .15);
  pointer-events: none;
}
.toast-notificacion.exito {
  background: #10b981;
  box-shadow: 0 8px 28px rgba(16, 185, 129, .35);
}
.toast-notificacion.error {
  background: #ef4444;
  box-shadow: 0 8px 28px rgba(239, 68, 68, .35);
}
.toast-notificacion i { font-size: 1rem; }

/* ── TRANSICIÓN MODAL ── */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity .22s ease;
}
.modal-fade-enter-active .modal-card,
.modal-fade-leave-active .modal-card {
  transition: transform .22s ease, opacity .22s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
.modal-fade-enter-from .modal-card {
  transform: translateY(16px) scale(.97);
  opacity: 0;
}
.modal-fade-leave-to .modal-card {
  transform: translateY(8px) scale(.98);
  opacity: 0;
}

/* ── TRANSICIÓN TOAST ── */
.toast-slide-enter-active,
.toast-slide-leave-active {
  transition: opacity .3s ease, transform .3s ease;
}
.toast-slide-enter-from {
  opacity: 0;
  transform: translateY(16px);
}
.toast-slide-leave-to {
  opacity: 0;
  transform: translateY(16px);
}

/* ── FULLCALENDAR ── */
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