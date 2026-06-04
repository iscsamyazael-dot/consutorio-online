<template>
  <div>
    <div>
      <h1 class="planner-title">
        Agenda Médica
      </h1>
      <p class="planner-subtitle">
        Sistema profesional de planificación de citas
      </p>
    </div>

    <div class="planner-actions">
      <div class="live-date">{{ fechaHoy }}</div>
      <a :href="createUrl" class="btn-create">
        <i class="fas fa-plus"></i>
        Nueva Cita
      </a>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AgendaMedica',
  props: {
    createUrl: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      fechaHoy: ''
    }
  },
  mounted() {
    this.updateDate();
    this.timer = setInterval(this.updateDate, 60000);
  },
  beforeUnmount() {
    clearInterval(this.timer);
  },
  methods: {
    updateDate() {
      this.fechaHoy = new Date().toLocaleDateString('es-MX', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    }
  }
}
</script>

<style scoped>
.planner-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1a3c5e;
  margin: 0 0 6px 0;
  letter-spacing: -0.5px;
}

.planner-subtitle {
  font-size: 0.95rem;
  color: #6b7280;
  margin: 0;
  font-weight: 400;
}

.planner-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 1.5rem;
  padding: 1rem 1.25rem;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
}

.live-date {
  font-size: 0.9rem;
  color: #6b7280;
  font-weight: 500;
  text-transform: capitalize;
}

.btn-create {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 18px;
  background: #1a3c5e;
  color: #ffffff;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  text-decoration: none;
  transition: background 0.2s ease;
}

.btn-create:hover {
  background: #15304d;
  color: #ffffff;
}

.btn-create i {
  font-size: 14px;
}
</style>