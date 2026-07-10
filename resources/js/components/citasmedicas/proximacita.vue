<template>
  <div class="sidebar-card">

    <div class="sidebar-title">Próximas Citas</div>

    <!-- v-for reemplaza el @foreach de Blade -->
    <div
      v-for="cita in proximasCitas"
      :key="cita.id"
      class="appointment-card"
    >

      <!-- :class reemplaza {{ strtolower($cita->estado) }} -->
      <div
        class="appointment-color"
        :class="cita.estado?.toLowerCase()"
      ></div>

      <div class="appointment-content">

        <!-- ?? 'Paciente' reemplaza el operador null coalescing de PHP -->
        <strong>{{ cita.paciente?.nombre ?? 'Paciente' }}</strong>

        <small>{{ cita.fecha_cita }}</small>

      </div>

      <!-- horaFormateada reemplaza Carbon::parse()->format('H:i') -->
      <div class="appointment-hour">
        {{ horaFormateada(cita.hora_cita) }}
      </div>

    </div>

    <!-- Mensaje si no hay citas -->
    <p v-if="proximasCitas.length === 0" class="sin-citas">
      Sin citas próximas
    </p>

  </div>
</template>


<script>
export default {
  name: 'ProximasCitas',

  props: {
    // Se recibe desde PlannerSidebar que ya tiene el array completo
    citas: {
      type: Array,
      required: true,
      default: () => [],
    },
  },

  computed: {
    // slice(0, 5) reemplaza $citas->take(5)
    proximasCitas() {
      return this.citas.slice(0, 5)
    },
  },

  methods: {
    // Reemplaza Carbon::parse($cita->hora_cita)->format('H:i')
    horaFormateada(hora) {
      if (!hora) return ''
      const [hh, mm] = hora.split(':')
      return `${hh}:${mm}`
    },
  },
}
</script>


<style scoped>
.sidebar-card {
  background: rgba(255, 255, 255, .88);
  backdrop-filter: blur(12px);
  border-radius: 18px;
  border: 1px solid rgba(255, 255, 255, .8);
  padding: 16px;
  box-shadow: 0 4px 16px rgba(15, 23, 42, .04);
}

.sidebar-title {
  font-size: .95rem;
  font-weight: 700;
  margin-bottom: 12px;
}

.appointment-card {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px;
  border-radius: 12px;
  margin-bottom: 8px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
}

.appointment-color {
  width: 5px;
  height: 38px;
  border-radius: 10px;
}

.appointment-color.pendiente  { background: #f59e0b; }
.appointment-color.completada { background: #10b981; }
.appointment-color.cancelada  { background: #ef4444; }

.appointment-content {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.appointment-content strong {
  color: #0f172a;
  font-size: .80rem;
}

.appointment-content small {
  color: #64748b;
  font-size: .70rem;
}

.appointment-hour {
  font-size: .72rem;
  font-weight: 700;
  color: #334155;
}

.sin-citas {
  color: #94a3b8;
  font-size: .82rem;
  text-align: center;
  padding: 8px 0;
}
</style>