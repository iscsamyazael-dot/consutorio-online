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
        <a :href="url('consultas/create')" class="btn-primary">
          <i class="ti ti-plus"></i> Nueva consulta
        </a>
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
  methods: {
    url(path) {
      return `/${path}`
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