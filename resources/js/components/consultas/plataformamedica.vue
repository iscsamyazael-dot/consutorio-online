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
              <!--
                Si hay un paciente seleccionado en la tabla (doble clic en
                ConsultaClinica.vue, guardado en localStorage bajo la llave
                "pacienteSeleccionado"), el link arma la URL con su id:
                /consultaNormal/{id} y /ConsultaInteligente/{id}.
                Si no hay paciente seleccionado, cae a las rutas "en blanco"
                de siempre: /NuevaConsulta y /ConsultaInteligenteNueva.
              -->
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
    <div class="stat-grid">
      <div class="scard scard-blue">
        <div class="scard-text">
          <p class="scard-num">{{ stats.hoy }}</p>
          <p class="scard-label">Consultas hoy</p>
        </div>
        <span class="scard-icon"><i class="ti ti-stethoscope"></i></span>
      </div>

      <div class="scard scard-green">
        <div class="scard-text">
          <p class="scard-num">{{ stats.activos }}</p>
          <p class="scard-label">Pacientes activos</p>
        </div>
        <span class="scard-icon"><i class="ti ti-user-heart"></i></span>
      </div>

      <div class="scard scard-amber">
        <div class="scard-text">
          <p class="scard-num">{{ stats.pendientes }}</p>
          <p class="scard-label">Pendientes</p>
        </div>
        <span class="scard-icon"><i class="ti ti-clock-hour-4"></i></span>
      </div>

      <div class="scard scard-red">
        <div class="scard-text">
          <p class="scard-num">{{ stats.urgencias }}</p>
          <p class="scard-label">Urgencias</p>
        </div>
        <span class="scard-icon"><i class="ti ti-ambulance"></i></span>
      </div>
    </div>

    <!-- FOOTER ROW -->
    <div class="bottom-row">
      <span class="time-label">
        <i class="ti ti-refresh"></i>
        Actualizado hace {{ minutosActualizacion }} min
      </span>
      <a href="/consultas/reporte" class="btn-ghost btn-ghost--sm">
        Ver reporte completo <i class="ti ti-arrow-right"></i>
      </a>
    </div>
  </div>
</template>

<script>
// Misma llave usada en ConsultaClinica.vue al hacer doble clic en un
// paciente de la tabla de espera (seleccionarParaConsulta()).
const CLAVE_PACIENTE_SELECCIONADO = 'pacienteSeleccionado'

export default {
  name: 'CentroConsultas',

  props: {
    stats: {
      type: Object,
      default: () => ({
        hoy: 24,
        activos: 12,
        pendientes: 2,
        urgencias: 3,
      }),
    },
    minutosActualizacion: {
      type: Number,
      default: 2,
    },
  },

  data() {
    return {
      showDropdown: false,
      // Id del paciente seleccionado en la tabla (si existe), leído de
      // localStorage cada vez que se abre el dropdown.
      pacienteSeleccionadoId: null,
    }
  },

  mounted() {
    document.addEventListener('click', this.handleClickOutside)
  },

  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside)
  },

  methods: {
    // Arma la URL del link del dropdown.
    // - pathSinPaciente: ruta a usar cuando NO hay paciente seleccionado (ej. 'NuevaConsulta')
    // - pathConPaciente: prefijo de ruta a usar cuando SÍ hay paciente seleccionado (ej. 'consultaNormal')
    //   el resultado será /pathConPaciente/{id}
    url(pathSinPaciente, pathConPaciente) {
      if (this.pacienteSeleccionadoId) {
        return `/${pathConPaciente}/${this.pacienteSeleccionadoId}`
      }
      return `/${pathSinPaciente}`
    },

    toggleDropdown() {
      // Cada vez que se abre el menú, refresca el paciente seleccionado
      // por si cambió desde la última vez que se abrió
      if (!this.showDropdown) {
        this.actualizarPacienteSeleccionado()
      }
      this.showDropdown = !this.showDropdown
    },

    // Lee el paciente seleccionado en la tabla desde localStorage
    // (guardado por ConsultaClinica.vue al hacer doble clic en una fila)
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

/* ── Stat grid (tarjetas de color sólido, estilo imagen de referencia) ── */
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

/* La tarjeta ámbar usa texto e ícono oscuros para mantener buen contraste sobre el amarillo */
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