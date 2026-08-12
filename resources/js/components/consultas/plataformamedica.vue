<template>
  <div class="consultas-wrap">
    <!-- HEADER -->
    <div class="header-row">
      <div>
        <div class="brand-chip">
          <span class="brand-dot"></span>
          <span class="brand-label">Sistema activo</span>
        </div>
        <h1 class="main-title">Centro de Consultas</h1>
        <p class="main-sub">Plataforma médica de monitoreo clínico y flujos en tiempo real.</p>
      </div>
      <div class="right-actions">
        <button class="btn-ghost">
          <i class="ti ti-adjustments-horizontal"></i> Filtros
        </button>

        <!-- ▼ Botón "Nueva consulta" convertido en select/dropdown ▼ -->
        <div class="new-consulta-dropdown" ref="dropdownRef">
          <button
            type="button"
            class="btn-primary"
            @click="toggleDropdown"
            :aria-expanded="showDropdown"
          >
            <i class="ti ti-plus"></i> Nueva consulta
            <i class="ti ti-chevron-down dropdown-caret" :class="{ 'is-open': showDropdown }"></i>
          </button>

          <transition name="fade-slide">
            <div v-if="showDropdown" class="dropdown-menu-custom">
              <a :href="url('NuevaConsulta', 'consultaNormal')" class="dropdown-item" @click="closeDropdown">
                <span class="dropdown-item-icon dropdown-item-icon--blue">
                  <i class="ti ti-file-plus"></i>
                </span>
                <span class="dropdown-item-text">
                  <span class="dropdown-item-title">Nueva consulta</span>
                  <span class="dropdown-item-sub">Registro manual estándar</span>
                </span>
              </a>

              <a :href="url('ConsultaInteligenteNueva', 'ConsultaInteligente')" class="dropdown-item" @click="closeDropdown">
                <span class="dropdown-item-icon dropdown-item-icon--purple">
                  <i class="ti ti-brain"></i>
                </span>
                <span class="dropdown-item-text">
                  <span class="dropdown-item-title">Consulta inteligente</span>
                  <span class="dropdown-item-sub">Asistida con IA</span>
                </span>
              </a>
            </div>
          </transition>
        </div>
        <!-- ▲ fin dropdown ▲ -->
      </div>
    </div>

    <!-- STAT CARDS -->
    <!-- Los números ya NO vienen de un prop estático: se calculan a partir
         de /api/citas y /pacientes, filtrando solo lo del día de hoy
         (ver statsFinal más abajo). Mientras carga, se muestra "—". -->
    <div class="stat-grid">
      <div class="scard scard-blue">
        <div class="scard-text">
          <p class="scard-num">{{ cargandoStats ? '—' : statsFinal.hoy }}</p>
          <p class="scard-label">Consultas hoy</p>
        </div>
        <span class="scard-icon"><i class="ti ti-stethoscope"></i></span>
      </div>

      <div class="scard scard-green">
        <div class="scard-text">
          <p class="scard-num">{{ cargandoStats ? '—' : statsFinal.activos }}</p>
          <p class="scard-label">Pacientes activos</p>
        </div>
        <span class="scard-icon"><i class="ti ti-user-heart"></i></span>
      </div>

      <div class="scard scard-amber">
        <div class="scard-text">
          <p class="scard-num">{{ cargandoStats ? '—' : statsFinal.pendientes }}</p>
          <p class="scard-label">Pendientes</p>
        </div>
        <span class="scard-icon"><i class="ti ti-clock-hour-4"></i></span>
      </div>

      <div class="scard scard-red">
        <div class="scard-text">
          <p class="scard-num">{{ cargandoStats ? '—' : statsFinal.urgencias }}</p>
          <p class="scard-label">Urgencias</p>
        </div>
        <span class="scard-icon"><i class="ti ti-ambulance"></i></span>
      </div>
    </div>

    <!-- FOOTER ROW -->
    <div class="bottom-row">
      <span class="time-label">
        <i class="ti ti-refresh"></i>
        Actualizado hace {{ minutosDesdeActualizacion }} min
      </span>
      <a href="/consultas/reporte" class="btn-ghost btn-ghost--sm">
        Ver reporte completo <i class="ti ti-arrow-right"></i>
      </a>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import ApiService from '../../services/ApiService.js'

// Misma llave usada en ConsultaClinica.vue al hacer doble clic en un
// paciente de la tabla de espera (seleccionarParaConsulta()).
const CLAVE_PACIENTE_SELECCIONADO = 'pacienteSeleccionado'

// Fecha de hoy en YYYY-MM-DD, en hora local (mismo helper que usan
// ConsultaClinica.vue y ConsultaInteligente.vue, para que "hoy" sea
// consistente en todo el sistema).
function obtenerFechaHoyISO() {
  const hoy = new Date()
  const y = hoy.getFullYear()
  const m = String(hoy.getMonth() + 1).padStart(2, '0')
  const d = String(hoy.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

export default {
  name: 'CentroConsultas',

  props: {
    // Si el padre pasa "stats" explícitamente, esos valores tienen
    // prioridad y se usan tal cual (por si en algún lugar se quiere
    // forzar un valor). Si no se pasa nada (caso normal), el
    // componente calcula sus propios números reales desde la API.
    stats: {
      type: Object,
      default: null,
    },
  },

  data() {
    return {
      citas: [],
      pacientes: [],
      cargandoStats: true,
      ultimaActualizacion: null, // Date de la última carga exitosa
      minutosDesdeActualizacion: 0,
      intervaloReloj: null,

      showDropdown: false,
      pacienteSeleccionadoId: null,
    }
  },

  computed: {
    // Índice paciente.id -> paciente completo (para poder leer sus
    // triages, que /api/citas no trae anidados dentro de cada cita).
    pacientesPorId() {
      const mapa = new Map()
      this.pacientes.forEach(p => mapa.set(p.id, p))
      return mapa
    },

    // Citas cuya fecha cae en el día de hoy. Se normaliza con slice(0,10)
    // porque el backend a veces devuelve la fecha como datetime completo
    // (ej. "2026-08-11T00:00:00.000000Z") en vez de solo "YYYY-MM-DD".
    citasHoy() {
      const hoy = obtenerFechaHoyISO()
      return this.citas.filter(c => String(c.fecha).slice(0, 10) === hoy)
    },

    // Estadísticas calculadas a partir de las citas reales de hoy.
    statsCalculados() {
      const citasHoy = this.citasHoy

      // Consultas hoy: todas las de hoy, excepto las canceladas
      // (una cita cancelada no cuenta como "consulta" real del día).
      const hoy = citasHoy.filter(c => c.estado !== 'Cancelada').length

      // Pacientes activos: pacientes distintos con cita hoy cuyo
      // estado general (tabla pacientes) es 'Activo'.
      const activosIds = new Set(
        citasHoy
          .filter(c => c.paciente && c.paciente.estado === 'Activo')
          .map(c => c.paciente.id)
      )
      const activos = activosIds.size

      // Pendientes: citas de hoy que siguen en 'Agendado' (aún no
      // atendidas ni finalizadas ni canceladas).
      const pendientes = citasHoy.filter(c => c.estado === 'Agendado').length

      // Urgencias: citas de hoy cuyo paciente tiene su último triage
      // marcado como 'Rojo' (grave). El triage vive en /pacientes,
      // no viene anidado en /api/citas, por eso se cruza con
      // pacientesPorId.
      const urgencias = citasHoy.filter(c => {
        if (!c.paciente) return false
        const pacienteCompleto = this.pacientesPorId.get(c.paciente.id)
        const triage = this.ultimoTriage(pacienteCompleto)
        return triage && triage.estado === 'Rojo'
      }).length

      return { hoy, activos, pendientes, urgencias }
    },

    // Si el padre forzó un objeto "stats", se respeta; si no, se usan
    // los valores calculados en tiempo real.
    statsFinal() {
      return this.stats || this.statsCalculados
    },
  },

  mounted() {
    this.cargarDatos()
    document.addEventListener('click', this.handleClickOutside)

    // Actualiza el texto "Actualizado hace X min" cada 30s sin volver
    // a pedir datos al servidor.
    this.intervaloReloj = setInterval(this.actualizarMinutosTranscurridos, 30000)
  },

  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside)
    if (this.intervaloReloj) clearInterval(this.intervaloReloj)
  },

  methods: {
    // Trae citas (con paciente/medico/especialidad anidados) y la
    // lista completa de pacientes (para poder leer sus triages).
    async cargarDatos() {
      this.cargandoStats = true
      try {
        const [respCitas, respPacientes] = await Promise.all([
          axios.get('/api/citas'),
          ApiService.get('/pacientes'),
        ])

        this.citas = respCitas.data || []
        this.pacientes = respPacientes.data || []
        this.ultimaActualizacion = new Date()
        this.actualizarMinutosTranscurridos()
      } catch (error) {
        console.error('Error al cargar las estadísticas de consultas:', error)
      } finally {
        this.cargandoStats = false
      }
    },

    actualizarMinutosTranscurridos() {
      if (!this.ultimaActualizacion) {
        this.minutosDesdeActualizacion = 0
        return
      }
      const diffMs = Date.now() - this.ultimaActualizacion.getTime()
      this.minutosDesdeActualizacion = Math.max(0, Math.floor(diffMs / 60000))
    },

    // Igual que en ConsultaClinica.vue: el triage más reciente del
    // arreglo `triages` del paciente.
    ultimoTriage(paciente) {
      if (!paciente?.triages?.length) return null
      return paciente.triages[paciente.triages.length - 1]
    },

    // Arma la URL del link del dropdown.
    url(pathSinPaciente, pathConPaciente) {
      if (this.pacienteSeleccionadoId) {
        return `/${pathConPaciente}/${this.pacienteSeleccionadoId}`
      }
      return `/${pathSinPaciente}`
    },

    toggleDropdown() {
      if (!this.showDropdown) {
        this.actualizarPacienteSeleccionado()
      }
      this.showDropdown = !this.showDropdown
    },

    actualizarPacienteSeleccionado() {
      try {
        const guardado = localStorage.getItem(CLAVE_PACIENTE_SELECCIONADO)
        this.pacienteSeleccionadoId = guardado ? (JSON.parse(guardado).id || null) : null
      } catch (error) {
        console.error('No se pudo leer el paciente seleccionado:', error)
        this.pacienteSeleccionadoId = null
      }
    },

    closeDropdown() {
      this.showDropdown = false
    },
    handleClickOutside(event) {
      const el = this.$refs.dropdownRef
      if (el && !el.contains(event.target)) {
        this.showDropdown = false
      }
    },
  },
}
</script>

<style scoped>
/* ── Layout ── */
.consultas-wrap {
  padding: 1.5rem 0 1rem;
  font-family: inherit;
}

.header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  flex-wrap: wrap;
  margin-bottom: 1.5rem;
}

/* ── Brand chip ── */
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
/* ── Titles ── */
.main-title {
  font-size: 26px;
  font-weight: 500;
  color: #1a1a1a;
  margin: 0 0 4px;
  line-height: 1.2;
}
.main-sub {
  font-size: 13px;
  color: #6b7280;
  margin: 0;
  line-height: 1.6;
  max-width: 400px;
}
/* ── Buttons ── */
.right-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
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
.btn-ghost--sm {
  font-size: 12px;
  padding: 6px 12px;
}
.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: #185fa5;
  border: none;
  border-radius: 8px;
  padding: 9px 18px;
  font-size: 13px;
  font-weight: 500;
  color: #e6f1fb;
  cursor: pointer;
  text-decoration: none;
  transition: background 0.15s;
}
.btn-primary:hover {
  background: #0c447c;
  color: #e6f1fb;
}

/* ── Dropdown "Nueva consulta" ── */
.new-consulta-dropdown {
  position: relative;
  display: inline-block;
}

.dropdown-caret {
  font-size: 13px;
  transition: transform 0.15s ease;
}
.dropdown-caret.is-open {
  transform: rotate(180deg);
}

.dropdown-menu-custom {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  min-width: 240px;
  background: #ffffff;
  border: 0.5px solid #e5e7eb;
  border-radius: 12px;
  box-shadow: 0 12px 28px -8px rgba(0, 0, 0, 0.18);
  overflow: hidden;
  z-index: 30;
  padding: 6px;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 10px;
  border-radius: 8px;
  text-decoration: none;
  color: inherit;
  transition: background 0.15s;
}
.dropdown-item:hover {
  background: #f3f6fb;
}

.dropdown-item-icon {
  width: 34px;
  height: 34px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  flex-shrink: 0;
}
.dropdown-item-icon--blue {
  background: #e6f1fb;
  color: #185fa5;
}
.dropdown-item-icon--purple {
  background: #f1e9fb;
  color: #7c3aed;
}

.dropdown-item-text {
  display: flex;
  flex-direction: column;
  gap: 1px;
}
.dropdown-item-title {
  font-size: 13px;
  font-weight: 600;
  color: #1a1a1a;
}
.dropdown-item-sub {
  font-size: 11.5px;
  color: #6b7280;
}

/* Transición del dropdown */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

/* ── Stat grid ── */
.stat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 14px;
  margin-bottom: 1.25rem;
}

.scard {
  position: relative;
  overflow: hidden;
  border-radius: 18px;
  padding: 22px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  min-height: 96px;
  box-shadow: 0 6px 16px -8px rgba(0, 0, 0, 0.2);
  transition: transform 0.15s, box-shadow 0.15s;
}
.scard:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px -8px rgba(0, 0, 0, 0.28);
}

.scard-blue  { background: #185fa5; }
.scard-green { background: #059669; }
.scard-amber { background: #eab308; }
.scard-red   { background: #dc2626; }

.scard-text {
  display: flex;
  flex-direction: column;
  gap: 4px;
  z-index: 1;
}

.scard-num {
  font-size: 34px;
  font-weight: 700;
  color: #fff;
  line-height: 1;
  letter-spacing: -0.02em;
  margin: 0;
}

.scard-label {
  font-size: 13px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.92);
  margin: 0;
}

.scard-icon {
  font-size: 46px;
  line-height: 1;
  color: rgba(255, 255, 255, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.scard-amber .scard-num,
.scard-amber .scard-label {
  color: #1f2937;
}
.scard-amber .scard-icon {
  color: rgba(31, 41, 55, 0.28);
}

/* ── Bottom row ── */
.bottom-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
  border-top: 0.5px solid #e5e7eb;
  padding-top: 1rem;
}
.time-label {
  font-size: 12px;
  color: #9ca3af;
  display: flex;
  align-items: center;
  gap: 6px;
}
</style>