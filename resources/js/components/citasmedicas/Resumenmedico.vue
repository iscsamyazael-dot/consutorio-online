<template>
  <div class="sidebar-card">

    <div class="sidebar-title">
      Resumen Médico
    </div>

    <div class="stats-box">

      <div class="stat-item stat-blue">
        <div class="stat-icon">
          <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-info">
          <h5>{{ totalCitas }}</h5>
          <span>Total Citas</span>
        </div>
      </div>

      <div class="stat-item stat-green">
        <div class="stat-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
          <h5>{{ citasCompletadas }}</h5>
          <span>Completadas</span>
        </div>
      </div>

      <div class="stat-item stat-orange">
        <div class="stat-icon">
          <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
          <h5>{{ citasPendientes }}</h5>
          <span>Pendientes</span>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
export default {
  name: 'ResumenMedico',
  props: {
    totalCitas:       { type: Number, default: 0 },
    citasCompletadas: { type: Number, default: 0 },
    citasPendientes:  { type: Number, default: 0 },
  }
}
</script>

<style scoped>
.sidebar-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.25rem;
}

.sidebar-title {
  font-size: 1rem;
  font-weight: 700;
  color: #1a3c5e;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #e5e7eb;
}

.stats-box {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 12px 14px;
  border-radius: 10px;
}

.stat-icon {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}

.stat-info h5 {
  margin: 0 0 2px;
  font-size: 1.4rem;
  font-weight: 700;
  line-height: 1;
}

.stat-info span {
  font-size: 0.8rem;
  font-weight: 500;
}

/* Azul */
.stat-blue {
  background: #eff6ff;
}
.stat-blue .stat-icon {
  background: #dbeafe;
  color: #1d4ed8;
}
.stat-blue .stat-info h5  { color: #1e40af; }
.stat-blue .stat-info span { color: #3b82f6; }

/* Verde */
.stat-green {
  background: #f0fdf4;
}
.stat-green .stat-icon {
  background: #dcfce7;
  color: #16a34a;
}
.stat-green .stat-info h5  { color: #15803d; }
.stat-green .stat-info span { color: #22c55e; }

/* Naranja */
.stat-orange {
  background: #fff7ed;
}
.stat-orange .stat-icon {
  background: #ffedd5;
  color: #ea580c;
}
.stat-orange .stat-info h5  { color: #c2410c; }
.stat-orange .stat-info span { color: #f97316; }
</style>